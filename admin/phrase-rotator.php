<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/container/db.php';

requireAdminAuth();

$adminName = adminAuthenticatedUser();
$phraseRotatorItemsPerPage = 10;
$currentPhraseRotatorPage = readPositiveInt($_GET['phrase_rotator_page'] ?? $_POST['phrase_rotator_page'] ?? null) ?? 1;
$phraseRotatorFormValues = [
    'phrase' => '',
];
$phraseRotatorFieldErrors = [
    'phrase' => '',
];
$pageError = '';
$successMessage = '';
$phraseRotatorItems = [];

if (isset($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flash = $_SESSION['admin_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['admin_flash']);
} elseif (isset($_SESSION['job_flash']) && is_array($_SESSION['job_flash'])) {
    $flash = $_SESSION['job_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['job_flash']);
}

try {
    $phraseRotatorItems = fetchPhraseRotatorRecords();
} catch (Throwable $exception) {
    $pageError = 'The phrase rotator list could not be loaded. Check the database connection and try again.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phraseRotatorAction = (string) ($_POST['phrase_rotator_action'] ?? 'create');

    if (!in_array($phraseRotatorAction, ['create', 'delete'], true)) {
        $phraseRotatorAction = 'create';
    }

    if ($phraseRotatorAction === 'delete') {
        $deletePhraseRotatorId = readPositiveInt($_POST['phrase_rotator_id'] ?? null);

        if ($deletePhraseRotatorId === null) {
            $pageError = 'Invalid phrase selected for deletion.';
        } else {
            try {
                $phraseRotatorItemToDelete = findPhraseRotatorRecordById($phraseRotatorItems, $deletePhraseRotatorId);

                if ($phraseRotatorItemToDelete === null) {
                    $pageError = 'The selected phrase was not found.';
                } else {
                    deletePhraseRotatorRecord($deletePhraseRotatorId);

                    $_SESSION['admin_flash'] = [
                        'message' => sprintf('"%s" phrase was deleted.', (string) ($phraseRotatorItemToDelete['phrase'] ?? '')),
                    ];

                    $remainingPhraseCount = max(count($phraseRotatorItems) - 1, 0);
                    $targetPhraseRotatorPage = min(
                        $currentPhraseRotatorPage,
                        calculatePaginationTotalPages($remainingPhraseCount, $phraseRotatorItemsPerPage)
                    );

                    redirectTo(buildAdminPhraseRotatorSectionUrl($targetPhraseRotatorPage));
                }
            } catch (Throwable $exception) {
                $pageError = 'The phrase could not be deleted. Check the database connection and try again.';
            }
        }
    } else {
        $phraseRotatorFormValues['phrase'] = trim((string) ($_POST['phrase'] ?? ''));

        if ($phraseRotatorFormValues['phrase'] === '') {
            $phraseRotatorFieldErrors['phrase'] = 'Please enter a phrase.';
        } elseif (textLength($phraseRotatorFormValues['phrase']) > 255) {
            $phraseRotatorFieldErrors['phrase'] = 'Phrase must be 255 characters or fewer.';
        } else {
            try {
                if (fetchPhraseRotatorRecordByPhrase($phraseRotatorFormValues['phrase']) !== null) {
                    $phraseRotatorFieldErrors['phrase'] = 'This phrase already exists.';
                }
            } catch (Throwable $exception) {
                if ($pageError === '') {
                    $pageError = 'The phrase could not be validated. Check the database connection and try again.';
                }
            }
        }

        if ($pageError === '' && !array_filter($phraseRotatorFieldErrors)) {
            try {
                insertPhraseRotatorRecord($phraseRotatorFormValues['phrase']);

                $_SESSION['admin_flash'] = [
                    'message' => sprintf('"%s" phrase was saved.', $phraseRotatorFormValues['phrase']),
                ];

                $nextPhraseCount = count($phraseRotatorItems) + 1;
                $targetPhraseRotatorPage = calculatePaginationTotalPages($nextPhraseCount, $phraseRotatorItemsPerPage);

                redirectTo(buildAdminPhraseRotatorSectionUrl($targetPhraseRotatorPage));
            } catch (Throwable $exception) {
                $pageError = 'The phrase could not be saved. Check the database connection and try again.';
            }
        }
    }
}

$totalPhraseRotatorItemsCount = count($phraseRotatorItems);
$totalPhraseRotatorPages = calculatePaginationTotalPages($totalPhraseRotatorItemsCount, $phraseRotatorItemsPerPage);

if ($currentPhraseRotatorPage > $totalPhraseRotatorPages) {
    $currentPhraseRotatorPage = $totalPhraseRotatorPages;
}

$phraseRotatorOffset = ($currentPhraseRotatorPage - 1) * $phraseRotatorItemsPerPage;
$visiblePhraseRotatorItems = array_slice($phraseRotatorItems, $phraseRotatorOffset, $phraseRotatorItemsPerPage);
$hasPhraseRotatorPagination = $totalPhraseRotatorItemsCount > $phraseRotatorItemsPerPage;

function escapeValue(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function readPositiveInt(mixed $value): ?int
{
    if (is_int($value)) {
        return $value > 0 ? $value : null;
    }

    if (!is_string($value) || $value === '' || !ctype_digit($value)) {
        return null;
    }

    $intValue = (int) $value;

    return $intValue > 0 ? $intValue : null;
}

function textLength(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
}

function calculatePaginationTotalPages(int $totalItems, int $itemsPerPage): int
{
    if ($itemsPerPage <= 0) {
        return 1;
    }

    return $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 1;
}

function formatCreatedAt(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Just now';
    }

    try {
        return (new DateTimeImmutable($value))->format('M j, Y g:i A');
    } catch (Throwable $exception) {
        return $value;
    }
}

function findPhraseRotatorRecordById(array $phraseRotatorItems, int $phraseRotatorId): ?array
{
    foreach ($phraseRotatorItems as $phraseRotatorItem) {
        if ((int) ($phraseRotatorItem['id'] ?? 0) === $phraseRotatorId) {
            return $phraseRotatorItem;
        }
    }

    return null;
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

function buildAdminPhraseRotatorSectionUrl(int $page = 1): string
{
    $parameters = [];

    if ($page > 1) {
        $parameters['phrase_rotator_page'] = $page;
    }

    $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

    return 'phrase-rotator.php' . ($query !== '' ? '?' . $query : '');
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>VOpen Market Admin | Phrase Rotator</title>
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
      input,
      textarea {
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

      .sidebar-note {
        padding: 16px;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.08);
        color: rgba(255, 255, 255, 0.76);
        font-size: 0.95rem;
        line-height: 1.5;
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

      .grid {
        display: grid;
        grid-template-columns: minmax(0, 0.9fr) minmax(320px, 1.1fr);
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
        display: none;
        margin-top: 8px;
        color: var(--error-text);
        font-size: 0.95rem;
      }

      .field.invalid .input {
        border-color: #d92d20;
        background: #fff6f5;
      }

      .field.invalid .field-error {
        display: block;
      }

      .actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 16px;
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

      .jobs-empty {
        padding: 24px;
        border: 1px dashed var(--line);
        border-radius: 18px;
        background: var(--panel-soft);
        color: var(--muted);
        text-align: center;
      }

      .category-list {
        display: grid;
      }

      .category-item + .category-item {
        border-top: 1px solid rgba(23, 36, 51, 0.08);
      }

      .category-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 0;
      }

      .category-item:first-child {
        padding-top: 0;
      }

      .category-item:last-child {
        padding-bottom: 0;
      }

      .category-head {
        display: grid;
        gap: 8px;
      }

      .category-name {
        margin: 0;
        font-size: 1.05rem;
      }

      .category-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .category-meta span {
        display: inline-flex;
        align-items: center;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--panel-soft);
        color: var(--muted);
        font-size: 0.84rem;
        font-weight: 700;
      }

      .category-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
      }

      .job-action-form {
        margin: 0;
      }

      .jobs-pagination {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
      }

      .jobs-pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        height: 44px;
        padding: 0 14px;
        border: 1px solid rgba(23, 36, 51, 0.12);
        border-radius: 999px;
        background: var(--panel-soft);
        color: var(--text);
        font-size: 0.95rem;
        font-weight: 700;
        transition: border-color 0.18s ease, background-color 0.18s ease, color 0.18s ease;
      }

      .jobs-pagination-link:hover,
      .jobs-pagination-link:focus-visible {
        border-color: rgba(var(--accent-rgb), 0.55);
        background: #fff;
        color: var(--accent);
        outline: none;
      }

      .jobs-pagination-link.is-active {
        border-color: var(--accent);
        background: var(--accent);
        color: #fff;
      }

      .jobs-pagination-link.is-disabled {
        opacity: 0.45;
        pointer-events: none;
      }

      .overlay {
        display: none;
      }

      @media (max-width: 960px) {
        .grid {
          grid-template-columns: 1fr;
        }

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
        .category-item {
          align-items: flex-start;
          flex-direction: column;
        }

        .badge-group,
        .category-actions {
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
          <a href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl()); ?>" class="nav-link active" aria-current="page">Phrase Rotator</a>
          <a href="emails.php" class="nav-link">Email</a>
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
            <h1 class="page-title">Phrase Rotator</h1>
            <p class="page-copy">Add rotating homepage phrases here with a text input and save button. Saved items will appear automatically in the frontend phrase rotator.</p>
          </div>

          <div class="badge-group">
            <div class="badge">Protected login</div>
            <div class="badge"><?php echo $totalPhraseRotatorItemsCount; ?> phrases</div>
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

        <section class="grid">
          <article class="card">
            <div class="card-head">
              <h3>Add Phrase</h3>
              <div class="badge">Text input save</div>
            </div>
            <div class="card-body">
              <form method="post" action="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl($currentPhraseRotatorPage)); ?>" novalidate>
                <input type="hidden" name="phrase_rotator_action" value="create">
                <input type="hidden" name="phrase_rotator_page" value="<?php echo $currentPhraseRotatorPage; ?>">

                <div class="field <?php echo $phraseRotatorFieldErrors['phrase'] !== '' ? 'invalid' : ''; ?>">
                  <label for="phrase-rotator-phrase">Phrase text</label>
                  <input
                    id="phrase-rotator-phrase"
                    name="phrase"
                    class="input"
                    type="text"
                    placeholder="Join forces with us"
                    value="<?php echo escapeValue($phraseRotatorFormValues['phrase']); ?>"
                  >
                  <div class="field-help">Each saved phrase appears in the homepage rotating text section automatically.</div>
                  <div class="field-error"><?php echo escapeValue($phraseRotatorFieldErrors['phrase'] !== '' ? $phraseRotatorFieldErrors['phrase'] : 'Please enter a phrase.'); ?></div>
                </div>

                <div class="actions">
                  <button type="submit" class="button button-primary">Save Phrase</button>
                </div>
              </form>
            </div>
          </article>

          <article class="card">
            <div class="card-head">
              <h3>Saved Phrases</h3>
              <div class="badge"><?php echo $totalPhraseRotatorItemsCount; ?> saved</div>
            </div>
            <div class="card-body">
              <?php if ($phraseRotatorItems === []): ?>
                <div class="jobs-empty">No phrase rotator items have been saved yet.</div>
              <?php else: ?>
                <div class="category-list">
                  <?php foreach ($visiblePhraseRotatorItems as $phraseRotatorItem): ?>
                    <article class="category-item">
                      <div class="category-head">
                        <h4 class="category-name"><?php echo escapeValue((string) ($phraseRotatorItem['phrase'] ?? '')); ?></h4>
                        <div class="category-meta">
                          <span><?php echo escapeValue(formatCreatedAt((string) ($phraseRotatorItem['created_at'] ?? ''))); ?></span>
                        </div>
                      </div>

                      <div class="category-actions">
                        <form method="post" action="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl($currentPhraseRotatorPage)); ?>" class="job-action-form" onsubmit="return confirm('Delete this phrase?');">
                          <input type="hidden" name="phrase_rotator_action" value="delete">
                          <input type="hidden" name="phrase_rotator_id" value="<?php echo (int) ($phraseRotatorItem['id'] ?? 0); ?>">
                          <input type="hidden" name="phrase_rotator_page" value="<?php echo $currentPhraseRotatorPage; ?>">
                          <button type="submit" class="button button-danger button-small">Delete</button>
                        </form>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>

                <?php if ($hasPhraseRotatorPagination): ?>
                  <nav class="jobs-pagination" aria-label="Admin phrase rotator pages">
                    <?php if ($currentPhraseRotatorPage > 1): ?>
                      <a href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl($currentPhraseRotatorPage - 1)); ?>" class="jobs-pagination-link" aria-label="Go to previous phrase page">Previous</a>
                    <?php else: ?>
                      <span class="jobs-pagination-link is-disabled" aria-disabled="true">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalPhraseRotatorPages; $page++): ?>
                      <a
                        href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl($page)); ?>"
                        class="jobs-pagination-link<?php echo $page === $currentPhraseRotatorPage ? ' is-active' : ''; ?>"
                        <?php echo $page === $currentPhraseRotatorPage ? ' aria-current="page"' : ''; ?>
                        aria-label="Go to phrase page <?php echo $page; ?>"
                      >
                        <?php echo $page; ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($currentPhraseRotatorPage < $totalPhraseRotatorPages): ?>
                      <a href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl($currentPhraseRotatorPage + 1)); ?>" class="jobs-pagination-link" aria-label="Go to next phrase page">Next</a>
                    <?php else: ?>
                      <span class="jobs-pagination-link is-disabled" aria-disabled="true">Next</span>
                    <?php endif; ?>
                  </nav>
                <?php endif; ?>
              <?php endif; ?>
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
