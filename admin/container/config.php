<?php
declare(strict_types=1);

// Update these values if your local MySQL setup uses different credentials.
return [
    'host' => getenv('VOPEN_DB_HOST') ?: '127.0.0.1',
    'port' => (int) (getenv('VOPEN_DB_PORT') ?: 3306),
    'database' => getenv('VOPEN_DB_NAME') ?: 'vopencareers',
    'username' => getenv('VOPEN_DB_USER') ?: 'root',
    'password' => getenv('VOPEN_DB_PASS') ?: '',
    'charset' => getenv('VOPEN_DB_CHARSET') ?: 'utf8mb4',
];
