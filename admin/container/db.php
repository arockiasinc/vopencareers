<?php
declare(strict_types=1);

if (extension_loaded('mysqli')) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
}

function adminDatabaseConfig(): array
{
    static $config = null;

    if (is_array($config)) {
        return $config;
    }

    $config = require __DIR__ . '/config.php';

    return is_array($config) ? $config : [];
}

function adminDatabaseConnection(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    if (!extension_loaded('mysqli')) {
        throw new RuntimeException('The mysqli extension is not enabled.');
    }

    $config = adminDatabaseConfig();
    $connection = mysqli_init();

    if (!$connection instanceof mysqli) {
        throw new RuntimeException('Unable to initialize the database connection.');
    }

    $host = (string) ($config['host'] ?? '127.0.0.1');
    $port = (int) ($config['port'] ?? 3306);
    $database = (string) ($config['database'] ?? '');
    $username = (string) ($config['username'] ?? '');
    $password = (string) ($config['password'] ?? '');
    $charset = (string) ($config['charset'] ?? 'utf8mb4');

    $connection->real_connect($host, $username, $password, $database, $port);
    $connection->set_charset($charset);

    return $connection;
}

function fetchAdminUserRecordByUsername(string $username): ?array
{
    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, username, password_hash, full_name, is_active, created_at, updated_at
         FROM admin_users
         WHERE username = ? AND is_active = 1
         LIMIT 1'
    );

    $statement->bind_param('s', $username);
    $statement->execute();
    $statement->bind_result($id, $storedUsername, $passwordHash, $fullName, $isActive, $createdAt, $updatedAt);

    if (!$statement->fetch()) {
        $statement->close();
        return null;
    }

    $adminUser = [
        'id' => (int) $id,
        'username' => $storedUsername,
        'password_hash' => $passwordHash,
        'full_name' => $fullName,
        'is_active' => (int) $isActive,
        'created_at' => $createdAt,
        'updated_at' => $updatedAt,
    ];

    $statement->close();

    return $adminUser;
}

function adminTableExists(string $tableName): bool
{
    $statement = adminDatabaseConnection()->prepare(
        'SELECT 1
         FROM information_schema.tables
         WHERE table_schema = DATABASE() AND table_name = ?
         LIMIT 1'
    );

    $statement->bind_param('s', $tableName);
    $statement->execute();
    $statement->store_result();
    $exists = $statement->num_rows > 0;
    $statement->close();

    return $exists;
}

function ensureCategoryTables(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $connection = adminDatabaseConnection();

    $connection->query(
        'CREATE TABLE IF NOT EXISTS categories (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_categories_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $connection->query(
        'CREATE TABLE IF NOT EXISTS job_categories (
            job_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (job_id, category_id),
            KEY idx_job_categories_category_id (category_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function categoryTablesAvailable(): bool
{
    return adminTableExists('categories') && adminTableExists('job_categories');
}

function normalizePositiveIntegerList(array $values): array
{
    $normalized = [];

    foreach ($values as $value) {
        if (is_int($value)) {
            $intValue = $value;
        } elseif (is_string($value) && ctype_digit($value)) {
            $intValue = (int) $value;
        } else {
            continue;
        }

        if ($intValue <= 0) {
            continue;
        }

        $normalized[$intValue] = $intValue;
    }

    return array_values($normalized);
}

function fetchJobCategoryMap(array $jobIds): array
{
    $jobIds = normalizePositiveIntegerList($jobIds);

    if ($jobIds === [] || !categoryTablesAvailable()) {
        return [];
    }

    $jobIdList = implode(', ', $jobIds);
    $result = adminDatabaseConnection()->query(
        'SELECT jc.job_id, c.id, c.name
         FROM job_categories jc
         INNER JOIN categories c ON c.id = jc.category_id
         WHERE jc.job_id IN (' . $jobIdList . ')
         ORDER BY c.name ASC, c.id ASC'
    );

    $categoryMap = [];

    while ($row = $result->fetch_assoc()) {
        $jobId = (int) ($row['job_id'] ?? 0);

        if ($jobId <= 0) {
            continue;
        }

        $categoryMap[$jobId][] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
        ];
    }

    $result->free();

    return $categoryMap;
}

function formatJobCategoryNames(array $categories): string
{
    $names = [];

    foreach ($categories as $category) {
        $name = trim((string) ($category['name'] ?? ''));

        if ($name === '') {
            continue;
        }

        $names[] = $name;
    }

    return implode(', ', $names);
}

function attachCategoriesToJobs(array $jobs): array
{
    if ($jobs === []) {
        return [];
    }

    $jobIds = [];

    foreach ($jobs as $job) {
        $jobIds[] = (int) ($job['id'] ?? 0);
    }

    $categoryMap = fetchJobCategoryMap($jobIds);

    foreach ($jobs as $index => $job) {
        $jobId = (int) ($job['id'] ?? 0);
        $categories = $categoryMap[$jobId] ?? [];

        $jobs[$index]['categories'] = $categories;
        $jobs[$index]['category_ids'] = array_map(
            static fn(array $category): int => (int) ($category['id'] ?? 0),
            $categories
        );
        $jobs[$index]['categories_label'] = formatJobCategoryNames($categories);
    }

    return $jobs;
}

function syncJobCategoryAssignments(int $jobId, array $categoryIds): void
{
    ensureCategoryTables();

    $categoryIds = normalizePositiveIntegerList($categoryIds);
    $deleteStatement = adminDatabaseConnection()->prepare('DELETE FROM job_categories WHERE job_id = ?');
    $deleteStatement->bind_param('i', $jobId);
    $deleteStatement->execute();
    $deleteStatement->close();

    if ($categoryIds === []) {
        return;
    }

    $insertStatement = adminDatabaseConnection()->prepare(
        'INSERT INTO job_categories (job_id, category_id) VALUES (?, ?)'
    );

    foreach ($categoryIds as $categoryId) {
        $insertStatement->bind_param('ii', $jobId, $categoryId);
        $insertStatement->execute();
    }

    $insertStatement->close();
}

function insertJobRecord(
    string $title,
    string $description,
    ?string $location = null,
    int $status = 1,
    array $categoryIds = []
): int
{
    $connection = adminDatabaseConnection();
    $connection->begin_transaction();

    try {
        $statement = $connection->prepare(
            'INSERT INTO jobs (title, description, location, status) VALUES (?, ?, ?, ?)'
        );

        $statement->bind_param('sssi', $title, $description, $location, $status);
        $statement->execute();

        $insertId = (int) $statement->insert_id;
        $statement->close();

        syncJobCategoryAssignments($insertId, $categoryIds);
        $connection->commit();

        return $insertId;
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function fetchJobRecordById(int $jobId): ?array
{
    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, title, description, location, status, created_at
         FROM jobs
         WHERE id = ?
         LIMIT 1'
    );

    $statement->bind_param('i', $jobId);
    $statement->execute();
    $statement->bind_result($id, $title, $description, $location, $status, $createdAt);

    if (!$statement->fetch()) {
        $statement->close();
        return null;
    }

    $job = [
        'id' => $id,
        'title' => $title,
        'description' => $description,
        'location' => $location,
        'status' => $status,
        'created_at' => $createdAt,
    ];

    $statement->close();

    $jobs = attachCategoriesToJobs([$job]);

    return $jobs[0] ?? null;
}

function updateJobRecord(
    int $jobId,
    string $title,
    string $description,
    ?string $location = null,
    int $status = 1,
    array $categoryIds = []
): void
{
    $connection = adminDatabaseConnection();
    $connection->begin_transaction();

    try {
        $statement = $connection->prepare(
            'UPDATE jobs
             SET title = ?, description = ?, location = ?, status = ?
             WHERE id = ?'
        );

        $statement->bind_param('sssii', $title, $description, $location, $status, $jobId);
        $statement->execute();
        $statement->close();

        syncJobCategoryAssignments($jobId, $categoryIds);
        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function deleteJobRecord(int $jobId): void
{
    $connection = adminDatabaseConnection();
    $connection->begin_transaction();

    try {
        if (categoryTablesAvailable()) {
            $deleteCategoriesStatement = $connection->prepare('DELETE FROM job_categories WHERE job_id = ?');
            $deleteCategoriesStatement->bind_param('i', $jobId);
            $deleteCategoriesStatement->execute();
            $deleteCategoriesStatement->close();
        }

        $statement = $connection->prepare('DELETE FROM jobs WHERE id = ?');
        $statement->bind_param('i', $jobId);
        $statement->execute();
        $statement->close();

        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function fetchJobRecords(): array
{
    $result = adminDatabaseConnection()->query(
        'SELECT id, title, description, location, status, created_at
         FROM jobs
         ORDER BY created_at DESC, id DESC'
    );

    $jobs = [];

    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }

    $result->free();

    return attachCategoriesToJobs($jobs);
}

function fetchCategoryRecordByName(string $name): ?array
{
    ensureCategoryTables();

    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, name, created_at
         FROM categories
         WHERE name = ?
         LIMIT 1'
    );

    $statement->bind_param('s', $name);
    $statement->execute();
    $statement->bind_result($id, $storedName, $createdAt);

    if (!$statement->fetch()) {
        $statement->close();
        return null;
    }

    $category = [
        'id' => (int) $id,
        'name' => (string) $storedName,
        'created_at' => (string) $createdAt,
    ];

    $statement->close();

    return $category;
}

function insertCategoryRecord(string $name): int
{
    ensureCategoryTables();

    $statement = adminDatabaseConnection()->prepare('INSERT INTO categories (name) VALUES (?)');
    $statement->bind_param('s', $name);
    $statement->execute();

    $insertId = (int) $statement->insert_id;
    $statement->close();

    return $insertId;
}

function deleteCategoryRecord(int $categoryId): void
{
    ensureCategoryTables();

    $connection = adminDatabaseConnection();
    $connection->begin_transaction();

    try {
        $deleteAssignmentsStatement = $connection->prepare('DELETE FROM job_categories WHERE category_id = ?');
        $deleteAssignmentsStatement->bind_param('i', $categoryId);
        $deleteAssignmentsStatement->execute();
        $deleteAssignmentsStatement->close();

        $deleteCategoryStatement = $connection->prepare('DELETE FROM categories WHERE id = ?');
        $deleteCategoryStatement->bind_param('i', $categoryId);
        $deleteCategoryStatement->execute();
        $deleteCategoryStatement->close();

        $connection->commit();
    } catch (Throwable $exception) {
        $connection->rollback();
        throw $exception;
    }
}

function fetchCategoryRecords(): array
{
    ensureCategoryTables();

    $result = adminDatabaseConnection()->query(
        'SELECT c.id, c.name, c.created_at, COUNT(jc.job_id) AS jobs_count
         FROM categories c
         LEFT JOIN job_categories jc ON jc.category_id = c.id
         GROUP BY c.id, c.name, c.created_at
         ORDER BY c.name ASC, c.id ASC'
    );

    $categories = [];

    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'jobs_count' => (int) ($row['jobs_count'] ?? 0),
        ];
    }

    $result->free();

    return $categories;
}
