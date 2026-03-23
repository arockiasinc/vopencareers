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

function updateAdminUserCredentials(int $adminUserId, string $username, ?string $passwordHash = null): void
{
    if ($passwordHash === null) {
        $statement = adminDatabaseConnection()->prepare(
            'UPDATE admin_users
             SET username = ?
             WHERE id = ?
             LIMIT 1'
        );
        $statement->bind_param('si', $username, $adminUserId);
    } else {
        $statement = adminDatabaseConnection()->prepare(
            'UPDATE admin_users
             SET username = ?, password_hash = ?
             WHERE id = ?
             LIMIT 1'
        );
        $statement->bind_param('ssi', $username, $passwordHash, $adminUserId);
    }

    $statement->execute();
    $statement->close();
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

function ensureScrollingCityTable(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    adminDatabaseConnection()->query(
        'CREATE TABLE IF NOT EXISTS scrolling_cities (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_scrolling_cities_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function fetchScrollingCityRecordByName(string $name): ?array
{
    ensureScrollingCityTable();

    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, name, created_at
         FROM scrolling_cities
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

    $city = [
        'id' => (int) $id,
        'name' => (string) $storedName,
        'created_at' => (string) $createdAt,
    ];

    $statement->close();

    return $city;
}

function insertScrollingCityRecord(string $name): int
{
    ensureScrollingCityTable();

    $statement = adminDatabaseConnection()->prepare('INSERT INTO scrolling_cities (name) VALUES (?)');
    $statement->bind_param('s', $name);
    $statement->execute();

    $insertId = (int) $statement->insert_id;
    $statement->close();

    return $insertId;
}

function deleteScrollingCityRecord(int $cityId): void
{
    ensureScrollingCityTable();

    $statement = adminDatabaseConnection()->prepare('DELETE FROM scrolling_cities WHERE id = ?');
    $statement->bind_param('i', $cityId);
    $statement->execute();
    $statement->close();
}

function fetchScrollingCityRecords(): array
{
    ensureScrollingCityTable();

    $result = adminDatabaseConnection()->query(
        'SELECT id, name, created_at
         FROM scrolling_cities
         ORDER BY created_at ASC, id ASC'
    );

    $cities = [];

    while ($row = $result->fetch_assoc()) {
        $cities[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    $result->free();

    return $cities;
}

function ensureScrollingCategoryTable(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    adminDatabaseConnection()->query(
        'CREATE TABLE IF NOT EXISTS scrolling_categories (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_scrolling_categories_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function fetchScrollingCategoryRecordByName(string $name): ?array
{
    ensureScrollingCategoryTable();

    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, name, created_at
         FROM scrolling_categories
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

function insertScrollingCategoryRecord(string $name): int
{
    ensureScrollingCategoryTable();

    $statement = adminDatabaseConnection()->prepare('INSERT INTO scrolling_categories (name) VALUES (?)');
    $statement->bind_param('s', $name);
    $statement->execute();

    $insertId = (int) $statement->insert_id;
    $statement->close();

    return $insertId;
}

function deleteScrollingCategoryRecord(int $categoryId): void
{
    ensureScrollingCategoryTable();

    $statement = adminDatabaseConnection()->prepare('DELETE FROM scrolling_categories WHERE id = ?');
    $statement->bind_param('i', $categoryId);
    $statement->execute();
    $statement->close();
}

function fetchScrollingCategoryRecords(): array
{
    ensureScrollingCategoryTable();

    $result = adminDatabaseConnection()->query(
        'SELECT id, name, created_at
         FROM scrolling_categories
         ORDER BY created_at ASC, id ASC'
    );

    $categories = [];

    while ($row = $result->fetch_assoc()) {
        $categories[] = [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['name'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    $result->free();

    return $categories;
}

function ensurePhraseRotatorTable(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    adminDatabaseConnection()->query(
        'CREATE TABLE IF NOT EXISTS phrase_rotator_phrases (
            id INT NOT NULL AUTO_INCREMENT,
            phrase VARCHAR(255) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_phrase_rotator_phrases_phrase (phrase)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function fetchPhraseRotatorRecordByPhrase(string $phrase): ?array
{
    ensurePhraseRotatorTable();

    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, phrase, created_at
         FROM phrase_rotator_phrases
         WHERE phrase = ?
         LIMIT 1'
    );

    $statement->bind_param('s', $phrase);
    $statement->execute();
    $statement->bind_result($id, $storedPhrase, $createdAt);

    if (!$statement->fetch()) {
        $statement->close();
        return null;
    }

    $record = [
        'id' => (int) $id,
        'phrase' => (string) $storedPhrase,
        'created_at' => (string) $createdAt,
    ];

    $statement->close();

    return $record;
}

function insertPhraseRotatorRecord(string $phrase): int
{
    ensurePhraseRotatorTable();

    $statement = adminDatabaseConnection()->prepare('INSERT INTO phrase_rotator_phrases (phrase) VALUES (?)');
    $statement->bind_param('s', $phrase);
    $statement->execute();

    $insertId = (int) $statement->insert_id;
    $statement->close();

    return $insertId;
}

function deletePhraseRotatorRecord(int $phraseId): void
{
    ensurePhraseRotatorTable();

    $statement = adminDatabaseConnection()->prepare('DELETE FROM phrase_rotator_phrases WHERE id = ?');
    $statement->bind_param('i', $phraseId);
    $statement->execute();
    $statement->close();
}

function fetchPhraseRotatorRecords(): array
{
    ensurePhraseRotatorTable();

    $result = adminDatabaseConnection()->query(
        'SELECT id, phrase, created_at
         FROM phrase_rotator_phrases
         ORDER BY created_at ASC, id ASC'
    );

    $phrases = [];

    while ($row = $result->fetch_assoc()) {
        $phrases[] = [
            'id' => (int) ($row['id'] ?? 0),
            'phrase' => (string) ($row['phrase'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }

    $result->free();

    return $phrases;
}

function ensureHomepageCmsTable(): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    adminDatabaseConnection()->query(
        'CREATE TABLE IF NOT EXISTS homepage_section_cms (
            id INT NOT NULL AUTO_INCREMENT,
            section_key VARCHAR(120) NOT NULL,
            heading VARCHAR(255) NOT NULL DEFAULT \'\',
            content TEXT NULL,
            image_path VARCHAR(255) NULL,
            image_path_secondary VARCHAR(255) NULL,
            image_path_tertiary VARCHAR(255) NULL,
            payload_json LONGTEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_homepage_section_cms_section_key (section_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $ensured = true;
}

function fetchHomepageCmsRecordBySectionKey(string $sectionKey): ?array
{
    ensureHomepageCmsTable();

    $statement = adminDatabaseConnection()->prepare(
        'SELECT id, section_key, heading, content, image_path, image_path_secondary, image_path_tertiary, payload_json, created_at, updated_at
         FROM homepage_section_cms
         WHERE section_key = ?
         LIMIT 1'
    );

    $statement->bind_param('s', $sectionKey);
    $statement->execute();
    $statement->bind_result(
        $id,
        $storedSectionKey,
        $heading,
        $content,
        $imagePath,
        $imagePathSecondary,
        $imagePathTertiary,
        $payloadJson,
        $createdAt,
        $updatedAt
    );

    if (!$statement->fetch()) {
        $statement->close();
        return null;
    }

    $record = [
        'id' => (int) $id,
        'section_key' => (string) $storedSectionKey,
        'heading' => (string) $heading,
        'content' => (string) $content,
        'image_path' => (string) $imagePath,
        'image_path_secondary' => (string) $imagePathSecondary,
        'image_path_tertiary' => (string) $imagePathTertiary,
        'payload_json' => (string) $payloadJson,
        'created_at' => (string) $createdAt,
        'updated_at' => (string) $updatedAt,
    ];

    $statement->close();

    return $record;
}

function fetchHomepageCmsRecords(): array
{
    ensureHomepageCmsTable();

    $result = adminDatabaseConnection()->query(
        'SELECT id, section_key, heading, content, image_path, image_path_secondary, image_path_tertiary, payload_json, created_at, updated_at
         FROM homepage_section_cms
         ORDER BY section_key ASC, id ASC'
    );

    $records = [];

    while ($row = $result->fetch_assoc()) {
        $sectionKey = (string) ($row['section_key'] ?? '');

        if ($sectionKey === '') {
            continue;
        }

        $records[$sectionKey] = [
            'id' => (int) ($row['id'] ?? 0),
            'section_key' => $sectionKey,
            'heading' => (string) ($row['heading'] ?? ''),
            'content' => (string) ($row['content'] ?? ''),
            'image_path' => (string) ($row['image_path'] ?? ''),
            'image_path_secondary' => (string) ($row['image_path_secondary'] ?? ''),
            'image_path_tertiary' => (string) ($row['image_path_tertiary'] ?? ''),
            'payload_json' => (string) ($row['payload_json'] ?? ''),
            'created_at' => (string) ($row['created_at'] ?? ''),
            'updated_at' => (string) ($row['updated_at'] ?? ''),
        ];
    }

    $result->free();

    return $records;
}

function upsertHomepageCmsRecord(
    string $sectionKey,
    string $heading,
    string $content,
    string $imagePath,
    string $imagePathSecondary,
    string $imagePathTertiary,
    string $payloadJson
): void {
    ensureHomepageCmsTable();

    $statement = adminDatabaseConnection()->prepare(
        'INSERT INTO homepage_section_cms (
            section_key,
            heading,
            content,
            image_path,
            image_path_secondary,
            image_path_tertiary,
            payload_json
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            heading = VALUES(heading),
            content = VALUES(content),
            image_path = VALUES(image_path),
            image_path_secondary = VALUES(image_path_secondary),
            image_path_tertiary = VALUES(image_path_tertiary),
            payload_json = VALUES(payload_json),
            updated_at = CURRENT_TIMESTAMP'
    );

    $statement->bind_param(
        'sssssss',
        $sectionKey,
        $heading,
        $content,
        $imagePath,
        $imagePathSecondary,
        $imagePathTertiary,
        $payloadJson
    );
    $statement->execute();
    $statement->close();
}
