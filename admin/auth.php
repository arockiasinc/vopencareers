<?php
declare(strict_types=1);

require_once __DIR__ . '/container/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('vopen_admin');
    session_start();
}

const VOPEN_ADMIN_SESSION_KEY = 'vopen_admin_user';
const VOPEN_ADMIN_SESSION_NAME_KEY = 'vopen_admin_name';
const VOPEN_ADMIN_LAST_ACTIVE_KEY = 'vopen_admin_last_active';
const VOPEN_ADMIN_IDLE_TIMEOUT = 28800;

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
    $displayName = trim((string) ($_SESSION[VOPEN_ADMIN_SESSION_NAME_KEY] ?? ''));

    if ($displayName !== '') {
        return $displayName;
    }

    return (string) ($_SESSION[VOPEN_ADMIN_SESSION_KEY] ?? '');
}

function attemptAdminLogin(string $username, string $password): bool
{
    $adminUser = fetchAdminUserRecordByUsername($username);

    if ($adminUser === null) {
        return false;
    }

    $storedUsername = (string) ($adminUser['username'] ?? '');
    $passwordHash = trim((string) ($adminUser['password_hash'] ?? ''));

    if ($storedUsername === '' || $passwordHash === '' || !password_verify($password, $passwordHash)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION[VOPEN_ADMIN_SESSION_KEY] = $storedUsername;
    $_SESSION[VOPEN_ADMIN_SESSION_NAME_KEY] = trim((string) ($adminUser['full_name'] ?? '')) ?: $storedUsername;
    $_SESSION[VOPEN_ADMIN_LAST_ACTIVE_KEY] = time();

    return true;
}

function requireAdminAuth(): void
{
    if (!isAdminAuthenticated()) {
        redirectTo('login.php');
    }
}
