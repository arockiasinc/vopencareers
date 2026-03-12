<?php
declare(strict_types=1);

function appBasePath(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $directory = trim(dirname($scriptName), '/');

    if ($directory === '' || $directory === '.') {
        return '/';
    }

    return '/' . $directory . '/';
}

function buildAppPath(string $path = ''): string
{
    $basePath = appBasePath();
    $path = ltrim($path, '/');

    if ($path === '') {
        return $basePath;
    }

    return $basePath . $path;
}

function sanitizeJobSlugPreservingCase(?string $value): string
{
    $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\r\n", "\r", "\n", '/', '\\'], ' ', $value);
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    $value = (string) preg_replace('/[^\pL\pN]+/u', '-', $value);
    $value = trim($value, '-');

    return $value;
}

function buildJobSlugFromTitle(?string $title): string
{
    return sanitizeJobSlugPreservingCase($title);
}

function normalizeJobSlugForLookup(?string $slug): string
{
    $slug = sanitizeJobSlugPreservingCase(rawurldecode((string) $slug));

    if ($slug === '') {
        return '';
    }

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($slug, 'UTF-8');
    }

    return strtolower($slug);
}

function readJobSlugValue(mixed $value): ?string
{
    if (!is_string($value) && !is_int($value)) {
        return null;
    }

    $slug = sanitizeJobSlugPreservingCase((string) $value);

    return $slug !== '' ? $slug : null;
}

function buildJobPublicPath(array $job): string
{
    $slug = buildJobSlugFromTitle((string) ($job['title'] ?? ''));

    if ($slug === '') {
        return buildAppPath('search-results.php');
    }

    return buildAppPath('jobs/' . rawurlencode($slug));
}
