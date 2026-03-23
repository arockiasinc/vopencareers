<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/container/email-settings.php';

requireAdminAuth();

$adminName = adminAuthenticatedUser();
$pageError = '';
$successMessage = '';
$emailSettings = loadJobApplicationEmailSettings();
$emailInputValues = [
    'to' => '',
    'cc' => '',
    'bcc' => '',
];

if (isset($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flash = $_SESSION['admin_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['admin_flash']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailAction = trim((string) ($_POST['email_action'] ?? ''));
    $recipientGroup = strtolower(trim((string) ($_POST['recipient_group'] ?? '')));
    $recipientEmail = trim((string) ($_POST['email_address'] ?? ''));

    if (!in_array($recipientGroup, JOB_APPLICATION_EMAIL_GROUPS, true)) {
        $pageError = 'Choose a valid recipient group.';
    } elseif ($emailAction === 'add') {
        $emailInputValues[$recipientGroup] = $recipientEmail;
        $validatedEmail = filter_var($recipientEmail, FILTER_VALIDATE_EMAIL);

        if ($validatedEmail === false) {
            $pageError = 'Enter a valid email address.';
        } else {
            $alreadyExists = false;

            foreach ((array) ($emailSettings[$recipientGroup] ?? []) as $savedRecipient) {
                $savedEmail = strtolower(trim((string) ($savedRecipient['email'] ?? '')));

                if ($savedEmail === strtolower($validatedEmail)) {
                    $alreadyExists = true;
                    break;
                }
            }

            if ($alreadyExists) {
                $pageError = 'That email address is already listed in this section.';
            } else {
                $emailSettings[$recipientGroup][] = [
                    'email' => $validatedEmail,
                    'name' => '',
                ];

                try {
                    saveJobApplicationEmailSettings($emailSettings);
                    $_SESSION['admin_flash'] = [
                        'message' => sprintf('%s recipient %s was added.', strtoupper($recipientGroup), $validatedEmail),
                    ];

                    redirectTo(buildAdminEmailsUrl());
                } catch (Throwable $exception) {
                    $pageError = 'The email address could not be saved right now. Please try again.';
                }
            }
        }
    } elseif ($emailAction === 'delete') {
        $recipientIndex = null;

        foreach ((array) ($emailSettings[$recipientGroup] ?? []) as $index => $savedRecipient) {
            $savedEmail = strtolower(trim((string) ($savedRecipient['email'] ?? '')));

            if ($savedEmail === strtolower($recipientEmail)) {
                $recipientIndex = $index;
                break;
            }
        }

        if ($recipientIndex === null) {
            $pageError = 'The selected email address was not found.';
        } elseif ($recipientGroup === 'to' && count((array) ($emailSettings['to'] ?? [])) <= 1) {
            $pageError = 'At least one To recipient is required.';
        } else {
            unset($emailSettings[$recipientGroup][$recipientIndex]);
            $emailSettings[$recipientGroup] = array_values((array) $emailSettings[$recipientGroup]);

            try {
                saveJobApplicationEmailSettings($emailSettings);
                $_SESSION['admin_flash'] = [
                    'message' => sprintf('%s recipient %s was removed.', strtoupper($recipientGroup), $recipientEmail),
                ];

                redirectTo(buildAdminEmailsUrl());
            } catch (Throwable $exception) {
                $pageError = 'The email address could not be removed right now. Please try again.';
            }
        }
    } else {
        $pageError = 'Invalid action requested.';
    }
}

function escapeValue(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

function recipientGroupLabel(string $group): string
{
    return match ($group) {
        'to' => 'To',
        'cc' => 'CC',
        'bcc' => 'BCC',
        default => strtoupper($group),
    };
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VOpen Market Admin | Email</title>
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

      .email-sections {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
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

      .actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 18px;
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

      .button-danger {
        background: #b42318;
        color: #fff;
      }

      .button-danger:hover {
        background: #8e1c13;
      }

      .button-small {
        padding: 10px 14px;
        font-size: 0.92rem;
      }

      .recipient-list {
        display: grid;
        gap: 12px;
        margin-top: 22px;
      }

      .recipient-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid rgba(23, 36, 51, 0.08);
        border-radius: 18px;
        background: var(--panel-soft);
      }

      .recipient-email {
        font-weight: 700;
        word-break: break-word;
      }

      .empty-state {
        padding: 18px;
        border: 1px dashed var(--line);
        border-radius: 18px;
        background: var(--panel-soft);
        color: var(--muted);
        text-align: center;
      }

      .job-action-form {
        margin: 0;
      }

      .overlay {
        display: none;
      }

      @media (max-width: 1100px) {
        .email-sections {
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

        .topbar,
        .recipient-item {
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
          <a href="<?php echo escapeValue(buildAdminEmailsUrl()); ?>" class="nav-link active" aria-current="page">Email</a>
          <a href="settings.php" class="nav-link">Settings</a>
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
            <h1 class="page-title">Email</h1>
            <p class="page-copy">Add the job application notification mail IDs here. Every new application will be sent to the saved To, CC, and BCC recipients.</p>
          </div>

          <div class="badge-group">
            <div class="badge">Protected login</div>
            <div class="badge"><?php echo count((array) ($emailSettings['to'] ?? [])); ?> To</div>
            <div class="badge"><?php echo count((array) ($emailSettings['cc'] ?? [])); ?> CC</div>
            <div class="badge"><?php echo count((array) ($emailSettings['bcc'] ?? [])); ?> BCC</div>
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

        <section class="email-sections">
          <?php foreach (JOB_APPLICATION_EMAIL_GROUPS as $group): ?>
            <article class="card">
              <div class="card-head">
                <h3><?php echo escapeValue(recipientGroupLabel($group)); ?> Mail IDs</h3>
                <div class="badge"><?php echo count((array) ($emailSettings[$group] ?? [])); ?> saved</div>
              </div>

              <div class="card-body">
                <form method="post" action="<?php echo escapeValue(buildAdminEmailsUrl()); ?>" novalidate>
                  <input type="hidden" name="email_action" value="add">
                  <input type="hidden" name="recipient_group" value="<?php echo escapeValue($group); ?>">

                  <div class="field">
                    <label for="email-<?php echo escapeValue($group); ?>"><?php echo escapeValue(recipientGroupLabel($group)); ?> mail ID</label>
                    <input
                      id="email-<?php echo escapeValue($group); ?>"
                      name="email_address"
                      class="input"
                      type="email"
                      placeholder="<?php echo escapeValue($group); ?>@example.com"
                      value="<?php echo escapeValue($emailInputValues[$group]); ?>"
                    >
                    <div class="field-help">Add one email ID at a time. You can save multiple addresses in this section.</div>
                  </div>

                  <div class="actions">
                    <button type="submit" class="button button-primary">Add <?php echo escapeValue(recipientGroupLabel($group)); ?></button>
                  </div>
                </form>

                <div class="recipient-list">
                  <?php if ((array) ($emailSettings[$group] ?? []) === []): ?>
                    <div class="empty-state">No <?php echo escapeValue(recipientGroupLabel($group)); ?> mail IDs added yet.</div>
                  <?php else: ?>
                    <?php foreach ((array) ($emailSettings[$group] ?? []) as $recipient): ?>
                      <?php $savedEmail = trim((string) ($recipient['email'] ?? '')); ?>
                      <div class="recipient-item">
                        <div class="recipient-email"><?php echo escapeValue($savedEmail); ?></div>

                        <form method="post" action="<?php echo escapeValue(buildAdminEmailsUrl()); ?>" class="job-action-form" onsubmit="return confirm('Delete this email ID?');">
                          <input type="hidden" name="email_action" value="delete">
                          <input type="hidden" name="recipient_group" value="<?php echo escapeValue($group); ?>">
                          <input type="hidden" name="email_address" value="<?php echo escapeValue($savedEmail); ?>">
                          <button type="submit" class="button button-danger button-small">Delete</button>
                        </form>
                      </div>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
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
