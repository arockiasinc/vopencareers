<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('vopen_admin');
    session_start();
}

const VOPEN_ADMIN_SESSION_KEY = 'vopen_admin_user';
const VOPEN_ADMIN_LAST_ACTIVE_KEY = 'vopen_admin_last_active';
const VOPEN_ADMIN_IDLE_TIMEOUT = 28800;

function adminCredentials(): array
{
    $envUsername = trim((string) getenv('VOPEN_ADMIN_USERNAME'));
    $envPassword = (string) getenv('VOPEN_ADMIN_PASSWORD');
    $envPasswordHash = trim((string) getenv('VOPEN_ADMIN_PASSWORD_HASH'));

    if ($envUsername !== '' && $envPasswordHash !== '') {
        return [
            'username' => $envUsername,
            'password_hash' => $envPasswordHash,
            'password' => null,
        ];
    }

    if ($envUsername !== '' && $envPassword !== '') {
        return [
            'username' => $envUsername,
            'password_hash' => null,
            'password' => $envPassword,
        ];
    }

    return [
        'username' => 'admin',
        'password_hash' => '$2y$10$qIE5.vVJ5FsoHiiniVB/LulNyJrpX.GeoGIIxLN5Ht7In0QpeYboK',
        'password' => null,
    ];
}

function redirectTo(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function logoutAdmin(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if ((bool) ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function isAdminAuthenticated(): bool
{
    $user = (string) ($_SESSION[VOPEN_ADMIN_SESSION_KEY] ?? '');

    if ($user === '') {
        return false;
    }

    $lastActive = (int) ($_SESSION[VOPEN_ADMIN_LAST_ACTIVE_KEY] ?? 0);

    if ($lastActive > 0 && (time() - $lastActive) > VOPEN_ADMIN_IDLE_TIMEOUT) {
        logoutAdmin();
        return false;
    }

    $_SESSION[VOPEN_ADMIN_LAST_ACTIVE_KEY] = time();

    return true;
}

function adminAuthenticatedUser(): string
{
    return (string) ($_SESSION[VOPEN_ADMIN_SESSION_KEY] ?? '');
}

function attemptAdminLogin(string $username, string $password): bool
{
    $credentials = adminCredentials();
    $storedUsername = (string) $credentials['username'];

    if ($storedUsername === '' || !hash_equals($storedUsername, $username)) {
        return false;
    }

    $passwordMatches = false;
    $passwordHash = $credentials['password_hash'];

    if (is_string($passwordHash) && $passwordHash !== '') {
        $passwordMatches = password_verify($password, $passwordHash);
    } else {
        $storedPassword = (string) ($credentials['password'] ?? '');
        $passwordMatches = $storedPassword !== '' && hash_equals($storedPassword, $password);
    }

    if (!$passwordMatches) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[VOPEN_ADMIN_SESSION_KEY] = $storedUsername;
    $_SESSION[VOPEN_ADMIN_LAST_ACTIVE_KEY] = time();

    return true;
}

function requireAdminAuth(): void
{
    if (!isAdminAuthenticated()) {
        redirectTo('login.php');
    }
}
