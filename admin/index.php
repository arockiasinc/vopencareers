<?php
declare(strict_types=1);

require __DIR__ . '/auth.php';
require_once __DIR__ . '/container/db.php';

requireAdminAuth();

$adminName = adminAuthenticatedUser();
$allowedLocations = ['Canada', 'India'];
$formValues = [
    'title' => '',
    'location' => '',
    'description' => '',
    'category_ids' => [],
];
$fieldErrors = [
    'title' => '',
    'location' => '',
    'description' => '',
    'categories' => '',
];
$pageError = '';
$successMessage = '';
$jobAction = 'create';
$editJob = null;
$editJobId = readPositiveInt($_GET['edit'] ?? null);
$currentJobsPage = readPositiveInt($_GET['jobs_page'] ?? $_POST['jobs_page'] ?? null) ?? 1;

if (isset($_SESSION['admin_flash']) && is_array($_SESSION['admin_flash'])) {
    $flash = $_SESSION['admin_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['admin_flash']);
} elseif (isset($_SESSION['job_flash']) && is_array($_SESSION['job_flash'])) {
    $flash = $_SESSION['job_flash'];
    $successMessage = (string) ($flash['message'] ?? '');
    unset($_SESSION['job_flash']);
}

$categories = [];
$categoryIdLookup = [];

try {
    $categories = fetchCategoryRecords();

    foreach ($categories as $category) {
        $categoryId = (int) ($category['id'] ?? 0);

        if ($categoryId <= 0) {
            continue;
        }

        $categoryIdLookup[$categoryId] = (string) ($category['name'] ?? '');
    }
} catch (Throwable $exception) {
    $pageError = 'The categories list could not be loaded. Check the database connection and confirm category tables can be created.';
}

$allowedCategoryIds = array_keys($categoryIdLookup);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobAction = (string) ($_POST['job_action'] ?? 'create');

    if (!in_array($jobAction, ['create', 'update', 'delete'], true)) {
        $jobAction = 'create';
    }

    if ($jobAction === 'delete') {
        $deleteJobId = readPositiveInt($_POST['job_id'] ?? null);

        if ($deleteJobId === null) {
            $pageError = 'Invalid job selected for deletion.';
        } else {
            try {
                $jobToDelete = fetchJobRecordById($deleteJobId);

                if ($jobToDelete === null) {
                    $pageError = 'The selected job was not found.';
                } else {
                    deleteJobRecord($deleteJobId);

                    $_SESSION['admin_flash'] = [
                        'message' => sprintf('"%s" was deleted.', (string) $jobToDelete['title']),
                    ];

                    redirectTo(buildAdminJobsSectionUrl($currentJobsPage));
                }
            } catch (Throwable $exception) {
                $pageError = 'The job could not be deleted. Check the database connection and try again.';
            }
        }
    } else {
        if ($jobAction === 'update') {
            $editJobId = readPositiveInt($_POST['job_id'] ?? null);

            if ($editJobId === null) {
                $pageError = 'Invalid job selected for editing.';
            } else {
                try {
                    $editJob = fetchJobRecordById($editJobId);

                    if ($editJob === null) {
                        $pageError = 'The selected job was not found.';
                    }
                } catch (Throwable $exception) {
                    $pageError = 'The selected job could not be loaded for editing.';
                }
            }
        }

        $selectedLocations = normalizeLocationSelection($_POST['location'] ?? null, $allowedLocations);
        $selectedCategoryIds = normalizeCategorySelection($_POST['category_id'] ?? null, $allowedCategoryIds);

        $formValues['title'] = trim((string) ($_POST['title'] ?? ''));
        $formValues['location'] = formatLocationSelection($selectedLocations);
        $formValues['description'] = trim((string) ($_POST['description'] ?? ''));
        $formValues['category_ids'] = $selectedCategoryIds;

        if ($formValues['title'] === '') {
            $fieldErrors['title'] = 'Please enter a job title.';
        } elseif (textLength($formValues['title']) > 255) {
            $fieldErrors['title'] = 'Title must be 255 characters or fewer.';
        }

        if ($selectedLocations === []) {
            $fieldErrors['location'] = 'Please choose at least one location.';
        } elseif (hasInvalidLocationSelection($_POST['location'] ?? null, $allowedLocations)) {
            $fieldErrors['location'] = 'Please choose only Canada or India.';
        }

        if (hasInvalidCategorySelection($_POST['category_id'] ?? null, $allowedCategoryIds)) {
            $fieldErrors['categories'] = 'Please choose only saved categories.';
        }

        if ($formValues['description'] === '') {
            $fieldErrors['description'] = 'Please enter a job description.';
        }

        if ($pageError === '' && !array_filter($fieldErrors)) {
            try {
                if ($jobAction === 'update' && $editJobId !== null) {
                    updateJobRecord(
                        $editJobId,
                        $formValues['title'],
                        $formValues['description'],
                        $formValues['location'] === '' ? null : $formValues['location'],
                        1,
                        $selectedCategoryIds
                    );

                    $_SESSION['admin_flash'] = [
                        'message' => sprintf('"%s" was updated.', $formValues['title']),
                    ];
                } else {
                    insertJobRecord(
                        $formValues['title'],
                        $formValues['description'],
                        $formValues['location'] === '' ? null : $formValues['location'],
                        1,
                        $selectedCategoryIds
                    );

                    $_SESSION['admin_flash'] = [
                        'message' => sprintf('"%s" was saved to the jobs table.', $formValues['title']),
                    ];
                }

                if ($jobAction === 'update') {
                    redirectTo(buildAdminJobsSectionUrl($currentJobsPage));
                }

                redirectTo(buildAdminJobsSectionUrl());
            } catch (Throwable $exception) {
                $pageError = $jobAction === 'update'
                    ? 'The job could not be updated. Check the database connection and try again.'
                    : 'The job could not be saved. Check admin/container/config.php and confirm the jobs table exists.';
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $editJobId !== null) {
    try {
        $editJob = fetchJobRecordById($editJobId);

        if ($editJob === null) {
            $pageError = 'The selected job was not found.';
            $editJobId = null;
        } else {
            $jobAction = 'update';
            $formValues['title'] = trim((string) ($editJob['title'] ?? ''));
            $formValues['location'] = formatLocationSelection(
                normalizeLocationSelection((string) ($editJob['location'] ?? ''), $allowedLocations)
            );
            $formValues['description'] = trim((string) ($editJob['description'] ?? ''));
            $formValues['category_ids'] = normalizeCategorySelection($editJob['category_ids'] ?? [], $allowedCategoryIds);
        }
    } catch (Throwable $exception) {
        $pageError = 'The selected job could not be loaded for editing.';
        $editJobId = null;
    }
}

$isEditMode = $jobAction === 'update' && $editJobId !== null;

$jobs = [];

try {
    $jobs = fetchJobRecords();
} catch (Throwable $exception) {
    if ($pageError === '') {
        $pageError = 'The jobs list could not be loaded. Check the database connection in admin/container/config.php.';
    }
}

$jobsPerPage = 7;
$totalJobsCount = count($jobs);
$totalJobsPages = $totalJobsCount > 0 ? (int) ceil($totalJobsCount / $jobsPerPage) : 1;

if ($currentJobsPage > $totalJobsPages) {
    $currentJobsPage = $totalJobsPages;
}

$jobsOffset = ($currentJobsPage - 1) * $jobsPerPage;
$visibleJobs = array_slice($jobs, $jobsOffset, $jobsPerPage);
$hasJobsPagination = $totalJobsCount > $jobsPerPage;

function escapeValue(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function summarizeJobDescriptionPreview(?string $value): string
{
    $plainText = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return trim((string) preg_replace('/\s+/u', ' ', $plainText));
}

function normalizeLocationSelection(mixed $value, array $allowedLocations): array
{
    if (is_string($value)) {
        $parts = preg_split('/\s*(?:,|\band\b)\s*/i', $value) ?: [];
    } elseif (is_array($value)) {
        $parts = $value;
    } else {
        $parts = [];
    }

    $selected = [];

    foreach ($parts as $part) {
        if (!is_string($part)) {
            continue;
        }

        $part = trim($part);

        if ($part === '' || !in_array($part, $allowedLocations, true)) {
            continue;
        }

        $selected[$part] = true;
    }

    return array_values(array_filter(
        $allowedLocations,
        static fn(string $allowedLocation): bool => isset($selected[$allowedLocation])
    ));
}

function hasInvalidLocationSelection(mixed $value, array $allowedLocations): bool
{
    if ($value === null || $value === '') {
        return false;
    }

    if (is_string($value)) {
        $parts = preg_split('/\s*(?:,|\band\b)\s*/i', $value) ?: [];
    } elseif (is_array($value)) {
        $parts = $value;
    } else {
        return true;
    }

    foreach ($parts as $part) {
        if (!is_string($part)) {
            return true;
        }

        $part = trim($part);

        if ($part === '') {
            continue;
        }

        if (!in_array($part, $allowedLocations, true)) {
            return true;
        }
    }

    return false;
}

function formatLocationSelection(array $locations): string
{
    return implode(', ', $locations);
}

function normalizeCategorySelection(mixed $value, array $allowedCategoryIds): array
{
    $values = is_array($value) ? $value : [$value];
    $selected = [];

    foreach ($values as $item) {
        if (is_int($item)) {
            $categoryId = $item;
        } elseif (is_string($item) && ctype_digit($item)) {
            $categoryId = (int) $item;
        } else {
            continue;
        }

        if ($categoryId <= 0 || !in_array($categoryId, $allowedCategoryIds, true)) {
            continue;
        }

        $selected[$categoryId] = $categoryId;
    }

    return array_values($selected);
}

function hasInvalidCategorySelection(mixed $value, array $allowedCategoryIds): bool
{
    if ($value === null || $value === '') {
        return false;
    }

    $values = is_array($value) ? $value : [$value];

    foreach ($values as $item) {
        if (is_int($item)) {
            $categoryId = $item;
        } elseif (is_string($item) && ctype_digit($item)) {
            $categoryId = (int) $item;
        } else {
            return true;
        }

        if ($categoryId <= 0 || !in_array($categoryId, $allowedCategoryIds, true)) {
            return true;
        }
    }

    return false;
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

function buildAdminJobsSectionUrl(int $page = 1, ?int $editJobId = null): string
{
    $parameters = [];

    if ($editJobId !== null && $editJobId > 0) {
        $parameters['edit'] = $editJobId;
    }

    if ($page > 1) {
        $parameters['jobs_page'] = $page;
    }

    $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

    return 'index.php' . ($query !== '' ? '?' . $query : '') . '#jobs-section';
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
    <title>VOpen Market Admin | Jobs</title>
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
      select,
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
        max-width: 52rem;
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
        grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
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

      .field > label,
      .field > .field-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
      }

      .input,
      .textarea {
        width: 100%;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--panel-soft);
        color: var(--text);
        padding: 14px 16px;
        outline: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      }

      .input:focus,
      .textarea:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.12);
        background: #fff;
      }

      .textarea {
        min-height: 230px;
        resize: vertical;
      }

      .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        padding: 14px 16px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--panel-soft);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
      }

      .checkbox-group:focus-within {
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(var(--accent-rgb), 0.12);
        background: #fff;
      }

      .checkbox-option {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        border: 1px solid rgba(23, 36, 51, 0.1);
        border-radius: 999px;
        background: #fff;
        font-weight: 700;
        cursor: pointer;
      }

      .checkbox-input {
        width: 18px;
        height: 18px;
        margin: 0;
        accent-color: var(--accent);
      }

      .field-error {
        display: none;
        margin-top: 8px;
        color: var(--error-text);
        font-size: 0.95rem;
      }

      .field.invalid .input,
      .field.invalid .textarea {
        border-color: #d92d20;
        background: #fff6f5;
      }

      .field.invalid .checkbox-group {
        border-color: #d92d20;
        background: #fff6f5;
      }

      .field.invalid .field-error {
        display: block;
      }

      .field-help {
        margin-top: 8px;
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.5;
      }

      .selection-note {
        padding: 14px 16px;
        border: 1px dashed var(--line);
        border-radius: 16px;
        background: var(--panel-soft);
        color: var(--muted);
        line-height: 1.5;
      }

      .selection-note a {
        color: var(--accent);
        font-weight: 700;
      }

      .field.invalid .selection-note {
        border-color: #d92d20;
        background: #fff6f5;
        color: var(--error-text);
      }

      .actions {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 16px;
        margin-top: 22px;
        flex-wrap: wrap;
      }

      .actions-group {
        display: flex;
        align-items: center;
        gap: 12px;
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

      .button-secondary {
        background: transparent;
        color: var(--muted);
        border: 1px solid var(--line);
      }

      .button-secondary:hover {
        background: var(--panel-soft);
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

      .jobs-list {
        display: grid;
      }

      .job-item + .job-item {
        border-top: 1px solid rgba(23, 36, 51, 0.08);
      }

      .job-item {
        padding: 18px 0;
      }

      .job-item:first-child {
        padding-top: 0;
      }

      .job-item:last-child {
        padding-bottom: 0;
      }

      .job-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
      }

      .job-side {
        display: grid;
        gap: 10px;
        justify-items: end;
      }

      .job-meta {
        display: grid;
        gap: 8px;
      }

      .job-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
      }

      .job-title {
        margin: 0;
        font-size: 1.08rem;
      }

      .job-status {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 6px 10px;
        border-radius: 999px;
        background: #e8f7ee;
        color: #18794e;
        font-size: 0.8rem;
        font-weight: 700;
      }

      .job-location {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--accent-soft);
        color: var(--accent);
        font-size: 0.84rem;
        font-weight: 700;
      }

      .job-date {
        padding: 7px 10px;
        border-radius: 999px;
        background: var(--panel-soft);
        color: var(--muted);
        font-size: 0.84rem;
        white-space: nowrap;
      }

      .job-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
      }

      .job-action-form {
        margin: 0;
      }

      .job-copy {
        margin: 0;
        color: var(--muted);
        line-height: 1.6;
        display: -webkit-box;
        overflow: hidden;
        max-height: calc(1.6em * 2);
        text-overflow: ellipsis;
        white-space: normal;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
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

      .section-block {
        margin-top: 30px;
      }

      .section-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
      }

      .section-head h2 {
        margin: 0;
        font-size: clamp(1.6rem, 3vw, 2.15rem);
        line-height: 1;
      }

      .section-head p {
        margin: 8px 0 0;
        color: var(--muted);
        max-width: 42rem;
      }

      .job-label-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
      }

      .job-label-pill {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        padding: 7px 10px;
        border-radius: 999px;
        background: #edf3ff;
        color: #2757a5;
        font-size: 0.84rem;
        font-weight: 700;
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

        .topbar {
          align-items: flex-start;
          flex-direction: column;
        }

        .badge-group {
          justify-content: flex-start;
        }

        .job-head {
          flex-direction: column;
        }

        .section-head,
        .category-item {
          flex-direction: column;
          align-items: flex-start;
        }

        .job-side,
        .category-actions,
        .job-actions {
          justify-items: start;
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
          <a href="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage, $isEditMode ? $editJobId : null)); ?>" class="nav-link active" aria-current="page">Jobs</a>
          <a href="<?php echo escapeValue(buildAdminCategoriesSectionUrl()); ?>" class="nav-link">Categories</a>
          <a href="<?php echo escapeValue(buildAdminScrollingCitiesSectionUrl()); ?>" class="nav-link">Scrolling Cities</a>
          <a href="<?php echo escapeValue(buildAdminScrollingCategoriesSectionUrl()); ?>" class="nav-link">Scrolling Categories</a>
          <a href="<?php echo escapeValue(buildAdminPhraseRotatorSectionUrl()); ?>" class="nav-link">Phrase Rotator</a>
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
            <h1 class="page-title">Jobs</h1>
            <p class="page-copy">Create job posts here and assign saved categories from the separate Categories page.</p>
          </div>

          <div class="badge-group">
            <div class="badge">Protected login</div>
            <div class="badge"><?php echo count($jobs); ?> jobs</div>
            <div class="badge"><?php echo count($categories); ?> categories</div>
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

        <section class="grid" id="jobs-section">
          <article class="card">
            <div class="card-head">
              <h3><?php echo $isEditMode ? 'Edit Job' : 'Create Job'; ?></h3>
              <?php if ($isEditMode): ?>
                <div class="badge">Editing #<?php echo $editJobId; ?></div>
              <?php endif; ?>
            </div>
            <div class="card-body">
              <form method="post" action="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage)); ?>" novalidate>
                <input type="hidden" name="job_action" value="<?php echo $isEditMode ? 'update' : 'create'; ?>">
                <?php if ($isEditMode): ?>
                  <input type="hidden" name="job_id" value="<?php echo $editJobId; ?>">
                <?php endif; ?>
                <input type="hidden" name="jobs_page" value="<?php echo $currentJobsPage; ?>">

                <div class="field <?php echo $fieldErrors['title'] !== '' ? 'invalid' : ''; ?>">
                  <label for="job-title">Title</label>
                  <input
                    id="job-title"
                    name="title"
                    class="input"
                    type="text"
                    placeholder="Senior Project Manager"
                    value="<?php echo escapeValue($formValues['title']); ?>"
                  >
                  <div class="field-error"><?php echo escapeValue($fieldErrors['title'] !== '' ? $fieldErrors['title'] : 'Please enter a job title.'); ?></div>
                </div>

                <?php $selectedLocations = normalizeLocationSelection($formValues['location'], $allowedLocations); ?>
                <div class="field <?php echo $fieldErrors['location'] !== '' ? 'invalid' : ''; ?>">
                  <span class="field-label" id="job-location-label">Location</span>
                  <div class="checkbox-group" role="group" aria-labelledby="job-location-label">
                    <?php foreach ($allowedLocations as $allowedLocation): ?>
                      <label class="checkbox-option">
                        <input
                          type="checkbox"
                          name="location[]"
                          value="<?php echo escapeValue($allowedLocation); ?>"
                          class="checkbox-input"
                          <?php echo in_array($allowedLocation, $selectedLocations, true) ? 'checked' : ''; ?>
                        >
                        <span><?php echo escapeValue($allowedLocation); ?></span>
                      </label>
                    <?php endforeach; ?>
                  </div>
                  <div class="field-error"><?php echo escapeValue($fieldErrors['location'] !== '' ? $fieldErrors['location'] : 'Please choose Canada, India, or both.'); ?></div>
                </div>

                <div class="field <?php echo $fieldErrors['categories'] !== '' ? 'invalid' : ''; ?>">
                  <span class="field-label" id="job-category-label">Categories</span>
                  <?php if ($categories === []): ?>
                    <div class="selection-note">
                      No categories are saved yet. Add them from the <a href="<?php echo escapeValue(buildAdminCategoriesSectionUrl()); ?>">Categories</a> page first.
                    </div>
                  <?php else: ?>
                    <div class="checkbox-group" role="group" aria-labelledby="job-category-label">
                      <?php foreach ($categories as $category): ?>
                        <?php $categoryId = (int) ($category['id'] ?? 0); ?>
                        <label class="checkbox-option">
                          <input
                            type="checkbox"
                            name="category_id[]"
                            value="<?php echo $categoryId; ?>"
                            class="checkbox-input"
                            <?php echo in_array($categoryId, $formValues['category_ids'], true) ? 'checked' : ''; ?>
                          >
                          <span><?php echo escapeValue((string) ($category['name'] ?? '')); ?></span>
                        </label>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                  <div class="field-error"><?php echo escapeValue($fieldErrors['categories'] !== '' ? $fieldErrors['categories'] : 'Select any saved categories that apply to this role.'); ?></div>
                </div>

                <div class="field <?php echo $fieldErrors['description'] !== '' ? 'invalid' : ''; ?>">
                  <label for="job-description">Description</label>
                  <textarea
                    id="job-description"
                    name="description"
                    class="textarea"
                    placeholder="Write the job description here..."
                  ><?php echo escapeValue($formValues['description']); ?></textarea>
                  <div class="field-error"><?php echo escapeValue($fieldErrors['description'] !== '' ? $fieldErrors['description'] : 'Please enter a job description.'); ?></div>
                </div>

                <div class="actions">
                  <div class="actions-group">
                    <button type="submit" class="button button-primary"><?php echo $isEditMode ? 'Update Job' : 'Post'; ?></button>
                    <?php if ($isEditMode): ?>
                      <a href="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage)); ?>" class="button button-secondary">Cancel</a>
                    <?php endif; ?>
                  </div>
                </div>
              </form>
            </div>
          </article>

          <article class="card">
            <div class="card-head">
              <h3>Posted Jobs</h3>
              <div class="badge"><?php echo count($jobs); ?> saved</div>
            </div>
            <div class="card-body">
              <?php if ($jobs === []): ?>
                <div class="jobs-empty">No jobs found in the database yet.</div>
              <?php else: ?>
                <div class="jobs-list">
                  <?php foreach ($visibleJobs as $job): ?>
                    <article class="job-item">
                      <div class="job-head">
                        <div class="job-meta">
                          <div class="job-title-row">
                            <h4 class="job-title"><?php echo escapeValue((string) ($job['title'] ?? '')); ?></h4>
                            <span class="job-status"><?php echo ((int) ($job['status'] ?? 0) === 1) ? 'Active' : 'Inactive'; ?></span>
                          </div>

                          <?php if (trim((string) ($job['location'] ?? '')) !== ''): ?>
                            <span class="job-location"><?php echo escapeValue((string) $job['location']); ?></span>
                          <?php endif; ?>

                          <?php if (($job['categories'] ?? []) !== []): ?>
                            <div class="job-label-list" aria-label="Job categories">
                              <?php foreach (($job['categories'] ?? []) as $category): ?>
                                <span class="job-label-pill"><?php echo escapeValue((string) ($category['name'] ?? '')); ?></span>
                              <?php endforeach; ?>
                            </div>
                          <?php endif; ?>
                        </div>

                        <div class="job-side">
                          <span class="job-date"><?php echo escapeValue(formatCreatedAt((string) ($job['created_at'] ?? ''))); ?></span>

                          <div class="job-actions">
                            <a href="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage, (int) ($job['id'] ?? 0))); ?>" class="button button-secondary button-small">Edit</a>

                            <form method="post" action="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage)); ?>" class="job-action-form" onsubmit="return confirm('Delete this job post?');">
                              <input type="hidden" name="job_action" value="delete">
                              <input type="hidden" name="job_id" value="<?php echo (int) ($job['id'] ?? 0); ?>">
                              <input type="hidden" name="jobs_page" value="<?php echo $currentJobsPage; ?>">
                              <button type="submit" class="button button-danger button-small">Delete</button>
                            </form>
                          </div>
                        </div>
                      </div>

                      <p class="job-copy"><?php echo escapeValue(summarizeJobDescriptionPreview((string) ($job['description'] ?? ''))); ?></p>
                    </article>
                  <?php endforeach; ?>
                </div>

                <?php if ($hasJobsPagination): ?>
                  <nav class="jobs-pagination" aria-label="Admin job pages">
                    <?php if ($currentJobsPage > 1): ?>
                      <a href="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage - 1)); ?>" class="jobs-pagination-link" aria-label="Go to previous jobs page">Previous</a>
                    <?php else: ?>
                      <span class="jobs-pagination-link is-disabled" aria-disabled="true">Previous</span>
                    <?php endif; ?>

                    <?php for ($page = 1; $page <= $totalJobsPages; $page++): ?>
                      <a
                        href="<?php echo escapeValue(buildAdminJobsSectionUrl($page)); ?>"
                        class="jobs-pagination-link<?php echo $page === $currentJobsPage ? ' is-active' : ''; ?>"
                        <?php echo $page === $currentJobsPage ? ' aria-current="page"' : ''; ?>
                        aria-label="Go to jobs page <?php echo $page; ?>"
                      >
                        <?php echo $page; ?>
                      </a>
                    <?php endfor; ?>

                    <?php if ($currentJobsPage < $totalJobsPages): ?>
                      <a href="<?php echo escapeValue(buildAdminJobsSectionUrl($currentJobsPage + 1)); ?>" class="jobs-pagination-link" aria-label="Go to next jobs page">Next</a>
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
