<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/container/db.php';

requireAdminAuth();

$adminName = adminAuthenticatedUser();
$pageError = '';
$successMessage = '';
$fieldErrors = [
    'username' => '',
    'current_password' => '',
    'new_password' => '',
    'confirm_password' => '',
];
$formValues = [
    'username' => adminAuthenticatedUsername(),
];
$currentAdmin = null;

if (isset($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flash = $_SESSION['admin_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['admin_flash']);
}

try {
    $currentAdmin = fetchAdminUserRecordByUsername(adminAuthenticatedUsername());

    if ($currentAdmin === null) {
        logoutAdmin();
        redirectTo('login.php');
    }

    $formValues['username'] = trim((string) ($currentAdmin['username'] ?? ''));
} catch (Throwable $exception) {
    $pageError = 'The admin account could not be loaded. Check the database connection and try again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentAdmin !== null) {
    $formValues['username'] = trim((string) ($_POST['username'] ?? ''));
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $passwordChangeRequested = $newPassword !== '' || $confirmPassword !== '';
    $currentUsername = trim((string) ($currentAdmin['username'] ?? ''));
    $usernameChanged = strcasecmp($formValues['username'], $currentUsername) !== 0;
    $storedPasswordHash = trim((string) ($currentAdmin['password_hash'] ?? ''));

    if ($formValues['username'] === '') {
        $fieldErrors['username'] = 'Enter a username.';
    } elseif (textLength($formValues['username']) > 100) {
        $fieldErrors['username'] = 'Username must be 100 characters or fewer.';
    }

    if ($currentPassword === '') {
        $fieldErrors['current_password'] = 'Enter your current password to save changes.';
    } elseif ($storedPasswordHash === '' || !password_verify($currentPassword, $storedPasswordHash)) {
        $fieldErrors['current_password'] = 'Current password is incorrect.';
    }

    if ($passwordChangeRequested) {
        if ($newPassword === '') {
            $fieldErrors['new_password'] = 'Enter a new password.';
        } elseif (textLength($newPassword) < 8) {
            $fieldErrors['new_password'] = 'New password must be at least 8 characters.';
        } elseif (textLength($newPassword) > 255) {
            $fieldErrors['new_password'] = 'New password must be 255 characters or fewer.';
        }

        if ($confirmPassword === '') {
            $fieldErrors['confirm_password'] = 'Confirm the new password.';
        } elseif ($newPassword !== $confirmPassword) {
            $fieldErrors['confirm_password'] = 'New password and confirmation do not match.';
        }
    }

    if ($pageError === '' && $fieldErrors['username'] === '' && $usernameChanged) {
        try {
            $existingAdmin = fetchAdminUserRecordByUsername($formValues['username']);

            if ($existingAdmin !== null && (int) ($existingAdmin['id'] ?? 0) !== (int) ($currentAdmin['id'] ?? 0)) {
                $fieldErrors['username'] = 'That username is already in use.';
            }
        } catch (Throwable $exception) {
            $pageError = 'The username could not be validated right now. Please try again.';
        }
    }

    if ($pageError === '' && !array_filter($fieldErrors)) {
        if (!$usernameChanged && !$passwordChangeRequested) {
            $_SESSION['admin_flash'] = [
                'message' => 'No credential changes were made.',
            ];

            redirectTo(buildAdminSettingsUrl());
        }

        $passwordHashToSave = $passwordChangeRequested ? password_hash($newPassword, PASSWORD_DEFAULT) : null;

        try {
            updateAdminUserCredentials((int) ($currentAdmin['id'] ?? 0), $formValues['username'], $passwordHashToSave);

            $updatedAdmin = fetchAdminUserRecordByUsername($formValues['username']);

            if ($updatedAdmin === null) {
                throw new RuntimeException('Updated admin record could not be reloaded.');
            }

            session_regenerate_id(true);
            storeAdminSessionUser($updatedAdmin);

            $_SESSION['admin_flash'] = [
                'message' => $usernameChanged && $passwordChangeRequested
                    ? 'Username and password were updated successfully.'
                    : ($passwordChangeRequested ? 'Password was updated successfully.' : 'Username was updated successfully.'),
            ];

            redirectTo(buildAdminSettingsUrl());
        } catch (Throwable $exception) {
            if ($exception instanceof mysqli_sql_exception && (int) $exception->getCode() === 1062) {
                $fieldErrors['username'] = 'That username is already in use.';
            } else {
                $pageError = 'The account settings could not be saved right now. Please try again.';
            }
        }
    }
}

$currentUsernameValue = is_array($currentAdmin) ? trim((string) ($currentAdmin['username'] ?? '')) : '';
$currentFullNameValue = is_array($currentAdmin) ? trim((string) ($currentAdmin['full_name'] ?? '')) : '';
$accountCreatedAt = is_array($currentAdmin) ? formatDateTime($currentAdmin['created_at'] ?? null) : 'Not available';
$accountUpdatedAt = is_array($currentAdmin) ? formatDateTime($currentAdmin['updated_at'] ?? null) : 'Not available';

function escapeValue(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function formatDateTime(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Not available';
    }

    try {
        return (new DateTimeImmutable($value))->format('M j, Y g:i A');
    } catch (Throwable $exception) {
        return $value;
    }
}

function buildAdminJobsSectionUrl(): string
{
    return 'index.php#jobs-section';
}

function buildAdminCategoriesSectionUrl(): string
{
    return 'categories.php';
}

function buildAdminScrollingCitiesSectionUrl(): string
{
    return 'scrolling-cities.php';
}

function buildAdminScrollingCategoriesSectionUrl(): string
{
    return 'scrolling-categories.php';
}

function buildAdminPhraseRotatorSectionUrl(): string
{
    return 'phrase-rotator.php';
}

function buildAdminEmailsUrl(): string
{
    return 'emails.php';
}

function buildAdminSettingsUrl(): string
{
    return 'settings.php';
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VOpen Market Admin | Settings</title>
    <style>
      :root {
        --bg: #eef2f6;
        --panel: #ffffff;
        --panel-soft: #f6f8fb;
        --text: #172433;
        --muted: #607085;
        --line: #d7dee8;
        --neon-orange: #ff5f1f;
        --admin-shell: #252423;
        --accent-rgb: 255, 95, 31;
        --accent: var(--neon-orange);
        --accent-strong: #d84f18;
        --accent-soft: #ffe6db;
        --sidebar: var(--admin-shell);
        --sidebar-text: #fff7f2;
        --success-bg: #eaf8ef;
        --success-text: #14532d;
        --error-bg: #fff1ef;
        --error-text: #b42318;
        --shadow: 0 18px 45px rgba(23, 36, 51, 0.08);
        --radius-lg: 24px;
        --sidebar-width: 260px;
      }

      * {
        box-sizing: border-box;
      }

      html {
        scroll-behavior: smooth;
      }

      body {
        margin: 0;
        font-family: "Trebuchet MS", "Gill Sans", sans-serif;
        background: linear-gradient(180deg, #f7f9fc 0%, var(--bg) 100%);
        color: var(--text);
      }

      a {
        color: inherit;
        text-decoration: none;
      }

      button,
      input {
        font: inherit;
      }

      .layout {
        min-height: 100vh;
        display: flex;
      }

      .sidebar {
        width: var(--sidebar-width);
        background: var(--sidebar);
        color: var(--sidebar-text);
        padding: 28px 20px;
        position: fixed;
        inset: 0 auto 0 0;
        display: flex;
        flex-direction: column;
        gap: 28px;
        box-shadow: 12px 0 30px rgba(16, 35, 59, 0.14);
      }

      .brand {
        display: block;
        padding: 14px 16px;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.07);
      }

      .brand-logo {
        display: block;
        width: min(172px, 100%);
        height: auto;
      }

      .brand-copy {
        display: block;
        margin-top: 10px;
        font-size: 0.95rem;
        color: rgba(255, 255, 255, 0.72);
      }

      .nav {
        display: grid;
        gap: 10px;
      }

      .nav-link {
        display: block;
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.08);
        color: #fff;
        font-weight: 700;
      }

      .nav-link.active {
        background: #fff;
        color: var(--sidebar);
      }

      .sidebar-footer {
        margin-top: auto;
        display: grid;
        gap: 14px;
      }

      .sidebar-user {
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.12);
      }

      .sidebar-user-label {
        display: block;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.68);
      }

      .sidebar-user-name {
        display: block;
        margin-top: 6px;
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
      }

      .logout-button {
        width: 100%;
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 999px;
        background: transparent;
        color: #fff;
        padding: 13px 18px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease;
      }

      .logout-button:hover {
        background: #fff;
        color: var(--sidebar);
      }

      .content {
        flex: 1;
        margin-left: var(--sidebar-width);
        padding: 28px;
      }

      .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
      }

      .menu-toggle {
        display: none;
        border: 1px solid var(--line);
        border-radius: 999px;
        background: #fff;
        color: var(--text);
        padding: 10px 16px;
        cursor: pointer;
      }

      .page-title {
        margin: 0;
        font-size: clamp(2rem, 4vw, 3rem);
        line-height: 0.95;
      }

      .page-copy {
        margin: 8px 0 0;
        color: var(--muted);
        max-width: 48rem;
      }

      .badge-group {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
      }

      .badge {
        display: inline-flex;
        align-items: center;
        padding: 10px 14px;
        border-radius: 999px;
        background: var(--accent-soft);
        color: var(--accent);
        font-weight: 700;
        white-space: nowrap;
      }

      .stack {
        display: grid;
        gap: 16px;
      }

      .notice {
        padding: 14px 16px;
        border-radius: 16px;
        font-weight: 700;
      }

      .notice-success {
        background: var(--success-bg);
        color: var(--success-text);
      }

      .notice-error {
        background: var(--error-bg);
        color: var(--error-text);
      }

      .settings-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        gap: 24px;
      }

      .card {
        background: var(--panel);
        border: 1px solid rgba(23, 36, 51, 0.06);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
      }

      .card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 22px 24px 0;
      }

      .card-head h3 {
        margin: 0;
        font-size: 1.3rem;
      }

      .card-body {
        padding: 24px;
      }

      .field + .field {
        margin-top: 18px;
      }

      .field > label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
      }

      .input {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--panel-soft);
        color: var(--text);
        padding: 14px 16px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      }

      .input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.12);
        background: #fff;
      }

      .field-help {
        margin-top: 8px;
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.5;
      }

      .field-error {
        margin-top: 8px;
        color: var(--error-text);
        font-size: 0.94rem;
        font-weight: 700;
      }

      .actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
        flex-wrap: wrap;
      }

      .button {
        border: 0;
        border-radius: 999px;
        padding: 13px 22px;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease, color 0.15s ease;
      }

      .button:hover {
        transform: translateY(-1px);
      }

      .button-primary {
        background: var(--accent);
        color: #fff;
      }

      .button-primary:hover {
        background: var(--accent-strong);
      }

      .account-list {
        display: grid;
        gap: 14px;
      }

      .account-item {
        padding: 16px 18px;
        border: 1px solid rgba(23, 36, 51, 0.08);
        border-radius: 18px;
        background: var(--panel-soft);
      }

      .account-label {
        display: block;
        color: var(--muted);
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
      }

      .account-value {
        display: block;
        margin-top: 6px;
        font-size: 1.02rem;
        font-weight: 700;
        word-break: break-word;
      }

      .tip-box {
        margin-top: 18px;
        padding: 18px;
        border-radius: 18px;
        background: var(--accent-soft);
        color: var(--text);
        line-height: 1.6;
      }

      .tip-box strong {
        display: block;
        margin-bottom: 6px;
      }

      .overlay {
        display: none;
      }

      @media (max-width: 1100px) {
        .settings-layout {
          grid-template-columns: 1fr;
        }
      }

      @media (max-width: 960px) {
        .content {
          padding: 22px 16px;
          margin-left: 0;
        }

        .menu-toggle {
          display: inline-flex;
        }

        .sidebar {
          transform: translateX(-100%);
          transition: transform 0.22s ease;
          z-index: 20;
        }

        body.sidebar-open .sidebar {
          transform: translateX(0);
        }

        .overlay {
          position: fixed;
          inset: 0;
          background: rgba(var(--accent-rgb), 0.36);
          z-index: 10;
        }

        body.sidebar-open .overlay {
          display: block;
        }

        .topbar {
          align-items: flex-start;
          flex-direction: column;
        }

        .badge-group {
          justify-content: flex-start;
        }
      }

      @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
          animation: none !important;
          transition: none !important;
          scroll-behavior: auto !important;
        }
      }
    </style>
  </head>
  <body>
    <div class="layout">
      <aside class="sidebar">
        <a href="./index.php" class="brand" aria-label="VOpen Market admin">
          <img src="./images/logo.webp" alt="VOpen Market" class="brand-logo">
          <span class="brand-copy">Admin panel</span>
        </a>

        <nav class="nav" aria-label="Sidebar navigation">
          <a href="<?php echo escapeValue(buildAdminJobsSectionUrl()); ?>" class="nav-link">Jobs</a>
          <a href="<?php echo escapeValue(buildAdminCategoriesSectionUrl()); ?>" class="nav-link">Categories</a>
          <a href="<?php echo escapeValue(buildAdminScrollingCitiesSectionUrl()); ?>" class="nav-link">Scrolling Cities</a>
          <a href="<?php echo escapeValue(buildAdminScrollingCategoriesSectionUrl()); ?>" class="nav-link">Scrolling Categories</a>
          <a href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl()); ?>" class="nav-link">Phrase Rotator</a>
          <a href="<?php echo escapeValue(buildAdminEmailsUrl()); ?>" class="nav-link">Email</a>
          <a href="<?php echo escapeValue(buildAdminSettingsUrl()); ?>" class="nav-link active" aria-current="page">Settings</a>
        </nav>

        <div class="sidebar-footer">
          <div class="sidebar-user">
            <span class="sidebar-user-label">Signed in as</span>
            <span class="sidebar-user-name"><?php echo escapeValue($adminName); ?></span>
          </div>

          <form action="logout.php" method="post">
            <button type="submit" class="logout-button">Log out</button>
          </form>
        </div>
      </aside>

      <div class="overlay" id="sidebar-overlay"></div>

      <main class="content">
        <header class="topbar">
          <div>
            <button type="button" class="menu-toggle" id="menu-toggle">Menu</button>
            <h1 class="page-title">Settings</h1>
            <p class="page-copy">Update the admin login username and password from the backend. Enter your current password to confirm any change.</p>
          </div>

          <div class="badge-group">
            <div class="badge">Protected login</div>
            <div class="badge">Credential management</div>
          </div>
        </header>

        <section class="stack">
          <?php if ($successMessage !== ''): ?>
            <div class="notice notice-success"><?php echo escapeValue($successMessage); ?></div>
          <?php endif; ?>

          <?php if ($pageError !== ''): ?>
            <div class="notice notice-error"><?php echo escapeValue($pageError); ?></div>
          <?php endif; ?>
        </section>

        <section class="settings-layout">
          <article class="card">
            <div class="card-head">
              <h3>Login Credentials</h3>
              <div class="badge">Admin account</div>
            </div>

            <div class="card-body">
              <form method="post" action="<?php echo escapeValue(buildAdminSettingsUrl()); ?>" novalidate>
                <div class="field">
                  <label for="username">Username</label>
                  <input
                    id="username"
                    name="username"
                    class="input"
                    type="text"
                    value="<?php echo escapeValue($formValues['username']); ?>"
                    autocomplete="username"
                    required
                  >
                  <div class="field-help">This username will be used the next time the admin signs in.</div>
                  <?php if ($fieldErrors['username'] !== ''): ?>
                    <div class="field-error"><?php echo escapeValue($fieldErrors['username']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="field">
                  <label for="current-password">Current Password</label>
                  <input
                    id="current-password"
                    name="current_password"
                    class="input"
                    type="password"
                    autocomplete="current-password"
                    required
                  >
                  <div class="field-help">Required to save a new username or password.</div>
                  <?php if ($fieldErrors['current_password'] !== ''): ?>
                    <div class="field-error"><?php echo escapeValue($fieldErrors['current_password']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="field">
                  <label for="new-password">New Password</label>
                  <input
                    id="new-password"
                    name="new_password"
                    class="input"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                  >
                  <div class="field-help">Leave this blank if you only want to change the username.</div>
                  <?php if ($fieldErrors['new_password'] !== ''): ?>
                    <div class="field-error"><?php echo escapeValue($fieldErrors['new_password']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="field">
                  <label for="confirm-password">Confirm New Password</label>
                  <input
                    id="confirm-password"
                    name="confirm_password"
                    class="input"
                    type="password"
                    autocomplete="new-password"
                    minlength="8"
                  >
                  <div class="field-help">Re-enter the new password to avoid typing mistakes.</div>
                  <?php if ($fieldErrors['confirm_password'] !== ''): ?>
                    <div class="field-error"><?php echo escapeValue($fieldErrors['confirm_password']); ?></div>
                  <?php endif; ?>
                </div>

                <div class="actions">
                  <button type="submit" class="button button-primary">Save Settings</button>
                </div>
              </form>
            </div>
          </article>

          <article class="card">
            <div class="card-head">
              <h3>Account Overview</h3>
              <div class="badge">Live details</div>
            </div>

            <div class="card-body">
              <div class="account-list">
                <div class="account-item">
                  <span class="account-label">Current Username</span>
                  <span class="account-value"><?php echo escapeValue($currentUsernameValue); ?></span>
                </div>

                <div class="account-item">
                  <span class="account-label">Display Name</span>
                  <span class="account-value"><?php echo escapeValue($currentFullNameValue !== '' ? $currentFullNameValue : 'Not set'); ?></span>
                </div>

                <div class="account-item">
                  <span class="account-label">Created</span>
                  <span class="account-value"><?php echo escapeValue($accountCreatedAt); ?></span>
                </div>

                <div class="account-item">
                  <span class="account-label">Last Updated</span>
                  <span class="account-value"><?php echo escapeValue($accountUpdatedAt); ?></span>
                </div>
              </div>

             
            </div>
          </article>
        </section>
      </main>
    </div>

    <script>
      const menuToggle = document.getElementById('menu-toggle');
      const overlay = document.getElementById('sidebar-overlay');

      const openSidebar = () => {
        document.body.classList.add('sidebar-open');
      };

      const closeSidebar = () => {
        document.body.classList.remove('sidebar-open');
      };

      if (menuToggle) {
        menuToggle.addEventListener('click', () => {
          if (document.body.classList.contains('sidebar-open')) {
            closeSidebar();
            return;
          }

          openSidebar();
        });
      }

      if (overlay) {
        overlay.addEventListener('click', closeSidebar);
      }

      window.addEventListener('resize', () => {
        if (window.innerWidth > 960) {
          closeSidebar();
        }
      });
    </script>
  </body>
</html>
