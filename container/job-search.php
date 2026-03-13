<?php
declare(strict_types=1);

function parseSearchResultsLocations(string $value): array
{
    $value = trim($value);

    if ($value === '') {
        return [];
    }

    $parts = preg_split('/\s*,\s*/', $value) ?: [];
    $locations = [];

    foreach ($parts as $part) {
        $part = trim((string) $part);

        if ($part === '') {
            continue;
        }

        $locations[$part] = true;
    }

    return array_keys($locations);
}

function getSearchResultsJobLocations(array $job): array
{
    $locations = parseSearchResultsLocations((string) ($job['location'] ?? ''));

    return $locations === [] ? ['Unspecified'] : $locations;
}

function getSearchResultsJobCategories(array $job): array
{
    $labels = [];

    foreach (($job['categories'] ?? []) as $category) {
        $label = trim((string) ($category['name'] ?? ''));

        if ($label === '') {
            continue;
        }

        $labels[$label] = true;
    }

    return array_keys($labels);
}

function normalizeSearchResultsSuggestionKey(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

function buildSearchResultsSuggestions(array $jobs): array
{
    $suggestions = [];

    foreach ($jobs as $job) {
        $values = [
            trim((string) ($job['title'] ?? '')),
        ];

        foreach (getSearchResultsJobLocations($job) as $location) {
            if ($location !== 'Unspecified') {
                $values[] = $location;
            }
        }

        foreach (getSearchResultsJobCategories($job) as $categoryLabel) {
            $values[] = $categoryLabel;
        }

        foreach ($values as $value) {
            $key = normalizeSearchResultsSuggestionKey($value);

            if ($key === '' || array_key_exists($key, $suggestions)) {
                continue;
            }

            $suggestions[$key] = $value;
        }
    }

    return array_values($suggestions);
}
