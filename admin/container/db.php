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

function insertJobRecord(string $title, string $description, ?string $location = null, int $status = 1): int
{
    $statement = adminDatabaseConnection()->prepare(
        'INSERT INTO jobs (title, description, location, status) VALUES (?, ?, ?, ?)'
    );

    $statement->bind_param('sssi', $title, $description, $location, $status);
    $statement->execute();

    $insertId = (int) $statement->insert_id;
    $statement->close();

    return $insertId;
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

    return $job;
}

function updateJobRecord(int $jobId, string $title, string $description, ?string $location = null, int $status = 1): void
{
    $statement = adminDatabaseConnection()->prepare(
        'UPDATE jobs
         SET title = ?, description = ?, location = ?, status = ?
         WHERE id = ?'
    );

    $statement->bind_param('sssii', $title, $description, $location, $status, $jobId);
    $statement->execute();
    $statement->close();
}

function deleteJobRecord(int $jobId): void
{
    $statement = adminDatabaseConnection()->prepare('DELETE FROM jobs WHERE id = ?');
    $statement->bind_param('i', $jobId);
    $statement->execute();
    $statement->close();
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

    return $jobs;
}
