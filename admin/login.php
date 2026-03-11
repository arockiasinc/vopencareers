<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';

if (isAdminAuthenticated()) {
    redirectTo('index.php');
}

$errorMessage = '';
$statusMessage = '';
$usernameValue = '';

if (isset($_GET['logged_out'])) {
    $statusMessage = 'You have been signed out.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameValue = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($usernameValue === '' || $password === '') {
        $errorMessage = 'Enter both username and password.';
    } elseif (attemptAdminLogin($usernameValue, $password)) {
        redirectTo('index.php');
    } else {
        $errorMessage = 'Invalid login details.';
    }
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VOpen Careers Admin | Login</title>
    <style>
      :root {
        color-scheme: light;
        --bg: #f3f1ec;
        --panel: rgba(255, 255, 255, 0.94);
        --line: rgba(36, 46, 48, 0.12);
        --text: #172433;
        --muted: #607085;
        --accent: #ff5f1f;
        --accent-strong: #dc4f19;
        --accent-soft: rgba(255, 95, 31, 0.12);
        --shadow: 0 28px 70px rgba(23, 36, 51, 0.12);
        --success-bg: #eaf8ef;
        --success-text: #14532d;
        --error-bg: #fff1ef;
        --error-text: #b42318;
      }

      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 24px;
        font-family: "Trebuchet MS", "Gill Sans", sans-serif;
        color: var(--text);
        background:
          radial-gradient(circle at top right, rgba(37, 133, 168, 0.18), transparent 28%),
          radial-gradient(circle at left bottom, rgba(255, 95, 31, 0.18), transparent 36%),
          linear-gradient(180deg, #faf8f4 0%, var(--bg) 100%);
      }

      a {
        color: inherit;
      }

      .shell {
        width: min(100%, 1040px);
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
        border-radius: 32px;
        overflow: hidden;
        background: var(--panel);
        box-shadow: var(--shadow);
      }

      .intro {
        padding: 48px;
        background: linear-gradient(180deg, #242e30 0%, #172433 100%);
        color: #ffffff;
      }

      .eyebrow {
        display: inline-flex;
        padding: 10px 14px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
      }

      .intro h1 {
        margin: 18px 0 0;
        font-size: clamp(2.6rem, 6vw, 4.6rem);
        line-height: 0.9;
      }

      .intro p {
        margin: 18px 0 0;
        max-width: 28rem;
        color: rgba(255, 255, 255, 0.76);
        font-size: 1.05rem;
        line-height: 1.75;
      }

      .panel {
        padding: 40px 36px;
        background:
          linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(255, 255, 255, 0.88)),
          linear-gradient(135deg, rgba(255, 95, 31, 0.04), rgba(37, 133, 168, 0.08));
      }

      .brand {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.02em;
      }

      .brand-mark {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: grid;
        place-items: center;
        background: var(--accent-soft);
        color: var(--accent);
        font-weight: 800;
      }

      .panel h2 {
        margin: 0;
        font-size: 1.85rem;
      }

      .panel-copy {
        margin: 10px 0 0;
        color: var(--muted);
        line-height: 1.6;
      }

      .notice {
        margin-top: 18px;
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

      form {
        margin-top: 26px;
      }

      .field + .field {
        margin-top: 18px;
      }

      label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
      }

      input {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: #f7f8fa;
        color: var(--text);
        padding: 15px 16px;
        font: inherit;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      }

      input:focus {
        border-color: var(--accent);
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(255, 95, 31, 0.12);
      }

      .actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-top: 24px;
        flex-wrap: wrap;
      }

      .button {
        border: 0;
        border-radius: 999px;
        background: var(--accent);
        color: #ffffff;
        padding: 14px 22px;
        font: inherit;
        font-weight: 700;
        cursor: pointer;
        transition: transform 0.15s ease, background 0.15s ease;
      }

      .button:hover {
        background: var(--accent-strong);
        transform: translateY(-1px);
      }

      .helper {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
      }

      .helper a {
        color: var(--accent);
        text-decoration: none;
      }

      .helper a:hover,
      .helper a:focus-visible {
        text-decoration: underline;
        outline: none;
      }

      @media (max-width: 860px) {
        .shell {
          grid-template-columns: 1fr;
        }

        .intro,
        .panel {
          padding: 32px 24px;
        }
      }
    </style>
  </head>
  <body>
    <div class="shell">
      <section class="intro">
        <span class="eyebrow">Admin Access</span>
        <h1>Manage VOpen job posts.</h1>
        <p>
          Sign in to access the jobs dashboard, create new listings, and review the roles stored in this browser session.
        </p>
      </section>

      <section class="panel">
        <div class="brand">
          <span class="brand-mark">VO</span>
          <span>VOpen Careers Admin</span>
        </div>

        <h2>Login</h2>
        <p class="panel-copy">Use your admin credentials to continue.</p>

        <?php if ($statusMessage !== ''): ?>
          <div class="notice notice-success" role="status"><?php echo htmlspecialchars($statusMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
          <div class="notice notice-error" role="alert"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
          <div class="field">
            <label for="username">Username</label>
            <input
              id="username"
              name="username"
              type="text"
              value="<?php echo htmlspecialchars($usernameValue, ENT_QUOTES, 'UTF-8'); ?>"
              autocomplete="username"
              required
            >
          </div>

          <div class="field">
            <label for="password">Password</label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
            >
          </div>

          <div class="actions">
            <button type="submit" class="button">Sign in</button>
            <div class="helper">
              Back to <a href="../index.php">careers site</a>
            </div>
          </div>
        </form>
      </section>
    </div>
  </body>
</html>
