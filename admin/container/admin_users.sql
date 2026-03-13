CREATE TABLE admin_users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admin_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Replace REPLACE_WITH_PASSWORD_HASH with a PHP password_hash() value.
-- Example:
-- php -r "echo password_hash('ChangeMe123!', PASSWORD_DEFAULT), PHP_EOL;"
INSERT INTO admin_users (username, full_name, password_hash, is_active)
VALUES ('admin', 'Admin User', 'REPLACE_WITH_PASSWORD_HASH', 1);
