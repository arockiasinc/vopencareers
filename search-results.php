<?php
declare(strict_types=1);

$pageTitle = 'Search Jobs | VOpen Market Careers';
$pageDescription = 'Browse current career opportunities at VOpen Market and explore roles published from the careers admin panel.';
$currentPage = 'search';
$bodyClass = 'bg-jet-cream';
$headerClass = 'sticky top-0 z-50 border-b border-black/5 bg-white site-header-elevated';

require_once __DIR__ . '/admin/container/db.php';
require_once __DIR__ . '/container/job-links.php';

function escapeSearchResultsValue(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatSearchResultsDate(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return 'Recently posted';
    }

    try {
        return 'Posted ' . (new DateTimeImmutable($value))->format('M j, Y');
    } catch (Throwable $exception) {
        return 'Recently posted';
    }
}

function formatSearchResultsMonth(?string $value): string
{
    if ($value === null || trim($value) === '') {
        return 'Recently added';
    }

    try {
        return (new DateTimeImmutable($value))->format('M Y');
    } catch (Throwable $exception) {
        return 'Recently added';
    }
}

function buildSearchResultsFilters(array $counts, array $selectedValues = []): array
{
    foreach ($selectedValues as $selectedValue) {
        if (!array_key_exists($selectedValue, $counts)) {
            $counts[$selectedValue] = 0;
        }
    }

    arsort($counts);

    if ($counts === []) {
        return [];
    }

    $filters = [];

    foreach ($counts as $label => $count) {
        $filters[] = [
            'label' => (string) $label,
            'count' => (int) $count,
            'checked' => in_array((string) $label, $selectedValues, true),
        ];
    }

    return $filters;
}

function normalizeSearchResultsFilterInput(mixed $value): array
{
    if ($value === null) {
        return [];
    }

    $values = is_array($value) ? $value : [$value];
    $normalized = [];

    foreach ($values as $item) {
        if (!is_string($item)) {
            continue;
        }

        $item = trim($item);

        if ($item === '') {
            continue;
        }

        $normalized[$item] = true;
    }

    return array_keys($normalized);
}

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

function normalizeSearchResultsLocationFilterInput(mixed $value): array
{
    $normalized = [];

    foreach (normalizeSearchResultsFilterInput($value) as $item) {
        foreach (parseSearchResultsLocations($item) as $location) {
            $normalized[$location] = true;
        }
    }

    return array_keys($normalized);
}

function getSearchResultsJobLocations(array $job): array
{
    $locations = parseSearchResultsLocations((string) ($job['location'] ?? ''));

    return $locations === [] ? ['Unspecified'] : $locations;
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

function searchResultsJobMatchesKeyword(array $job, string $searchTerm): bool
{
    $haystack = implode(' ', array_filter([
        trim((string) ($job['title'] ?? '')),
        trim((string) ($job['location'] ?? '')),
        trim((string) ($job['description'] ?? '')),
        trim((string) ($job['created_at'] ?? '')),
    ], static fn(string $value): bool => $value !== ''));

    return stripos($haystack, $searchTerm) !== false;
}

function searchResultsJobMatchesFilters(array $job, array $selectedLocations, array $selectedPublishedMonths): bool
{
    if ($selectedLocations !== []) {
        $jobLocations = getSearchResultsJobLocations($job);

        if (array_intersect($jobLocations, $selectedLocations) === []) {
            return false;
        }
    }

    if (
        $selectedPublishedMonths !== []
        && !in_array(formatSearchResultsMonth((string) ($job['created_at'] ?? '')), $selectedPublishedMonths, true)
    ) {
        return false;
    }

    return true;
}

function summarizeSearchResultsDescription(?string $value, int $limit = 280): string
{
    $plainText = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plainText = trim((string) preg_replace('/\s+/', ' ', $plainText));

    if ($plainText === '') {
        return 'More details are available for this role in the careers admin panel.';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($plainText) <= $limit) {
            return $plainText;
        }

        return rtrim(mb_substr($plainText, 0, $limit - 3)) . '...';
    }

    if (strlen($plainText) <= $limit) {
        return $plainText;
    }

    return rtrim(substr($plainText, 0, $limit - 3)) . '...';
}

function buildSearchResultsJobAnchor(array $job, int $index): string
{
    $jobId = (int) ($job['id'] ?? 0);

    if ($jobId > 0) {
        return 'job-' . $jobId;
    }

    return 'job-item-' . $index;
}

function buildSearchResultsJobDetailUrl(array $job): string
{
    return buildJobPublicPath($job);
}

function readSearchResultsPositiveInt(mixed $value): ?int
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

function buildSearchResultsPaginationUrl(
    int $page,
    string $searchTerm,
    array $selectedLocations,
    array $selectedPublishedMonths
): string {
    $parameters = [];

    if ($searchTerm !== '') {
        $parameters['keywords'] = $searchTerm;
    }

    if ($selectedLocations !== []) {
        $parameters['location'] = array_values($selectedLocations);
    }

    if ($selectedPublishedMonths !== []) {
        $parameters['published'] = array_values($selectedPublishedMonths);
    }

    if ($page > 1) {
        $parameters['page'] = $page;
    }

    $query = http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

    return 'search-results.php' . ($query !== '' ? '?' . $query : '') . '#job-results';
}

$searchTerm = trim((string) ($_GET['keywords'] ?? ''));
$selectedLocations = normalizeSearchResultsLocationFilterInput($_GET['location'] ?? null);
$selectedPublishedMonths = normalizeSearchResultsFilterInput($_GET['published'] ?? null);
$currentResultsPage = readSearchResultsPositiveInt($_GET['page'] ?? null) ?? 1;
$pageError = '';
$jobRecords = [];

try {
    $jobRecords = fetchJobRecords();
} catch (Throwable $exception) {
    $pageError = 'The jobs list could not be loaded right now. Please try again later.';
}

$allJobs = array_values(array_filter(
    $jobRecords,
    static fn(array $job): bool => (int) ($job['status'] ?? 0) === 1
));
$searchSuggestions = buildSearchResultsSuggestions($allJobs);
$searchSuggestionsJson = json_encode(
    $searchSuggestions,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
);

$searchScopedJobs = $allJobs;

if ($searchTerm !== '') {
    $searchScopedJobs = array_values(array_filter(
        $allJobs,
        static fn(array $job): bool => searchResultsJobMatchesKeyword($job, $searchTerm)
    ));
}

$jobs = array_values(array_filter(
    $searchScopedJobs,
    static fn(array $job): bool => searchResultsJobMatchesFilters($job, $selectedLocations, $selectedPublishedMonths)
));

$locationCounts = [];
$publishedCounts = [];

foreach ($searchScopedJobs as $job) {
    foreach (getSearchResultsJobLocations($job) as $locationLabel) {
        $locationCounts[$locationLabel] = ($locationCounts[$locationLabel] ?? 0) + 1;
    }

    $publishedLabel = formatSearchResultsMonth((string) ($job['created_at'] ?? ''));
    $publishedCounts[$publishedLabel] = ($publishedCounts[$publishedLabel] ?? 0) + 1;
}

$locationFilters = buildSearchResultsFilters($locationCounts, $selectedLocations);
$publishedFilters = buildSearchResultsFilters($publishedCounts, $selectedPublishedMonths);

$jobsPerPage = 7;
$jobCount = count($jobs);
$totalResultsPages = $jobCount > 0 ? (int) ceil($jobCount / $jobsPerPage) : 1;

if ($currentResultsPage > $totalResultsPages) {
    $currentResultsPage = $totalResultsPages;
}

$jobsOffset = ($currentResultsPage - 1) * $jobsPerPage;
$visibleJobs = array_slice($jobs, $jobsOffset, $jobsPerPage);
$hasPagination = $jobCount > $jobsPerPage;
$jobLabel = $jobCount === 1 ? 'job' : 'jobs';
$searchValue = escapeSearchResultsValue($searchTerm);
$hasActiveFilters = $selectedLocations !== [] || $selectedPublishedMonths !== [];
$isPublishedSectionOpen = $selectedPublishedMonths !== [];

$emptyStateTitle = 'No jobs have been published yet';
$emptyStateCopy = 'Add jobs from the admin panel to show them in this listing.';

if ($searchTerm !== '' && $hasActiveFilters) {
    $emptyStateTitle = 'No roles matched your search and filters';
    $emptyStateCopy = 'Try clearing one or more filters or broadening the keyword search.';
} elseif ($searchTerm !== '') {
    $emptyStateTitle = 'No roles matched your search';
    $emptyStateCopy = 'Try a broader keyword or clear the search to view all published openings.';
} elseif ($hasActiveFilters) {
    $emptyStateTitle = 'No roles matched your filters';
    $emptyStateCopy = 'Try clearing one or more filters to view more published openings.';
}

include 'container/header.php';
?>



<section class="bg-jet-cream">
  <div class="grid overflow-hidden lg:grid-cols-[1.03fr_0.97fr]">
    <div class="bg-jet-orange px-5 py-7 sm:px-8 sm:py-10 md:px-10 md:py-12 lg:px-14 lg:py-16 xl:px-16 xl:py-20">
      <div class="mx-auto max-w-[680px]">
        <p class="jobs-search-kicker">Find the role that fits</p>
        <p class="jobs-search-copy">
          Search by title, location, or keyword to scan the openings published from the admin panel.
        </p>

        <form id="job-search-form" action="search-results.php" method="get" class="mt-8 flex flex-col gap-3 sm:flex-row" role="search" aria-label="Search jobs">
          <label for="job-search" class="sr-only">Search for job title</label>
          <div class="search-autocomplete relative flex-1" data-search-autocomplete>
            <svg class="pointer-events-none absolute left-6 top-1/2 h-7 w-7 -translate-y-1/2 text-jet-charcoal" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.25-3.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input id="job-search" name="keywords" type="search" value="<?php echo $searchValue; ?>" placeholder="Search for job title" class="h-16 w-full rounded-full border-0 bg-white px-16 text-lg font-semibold text-jet-charcoal shadow-soft outline-none ring-0 placeholder:text-jet-charcoal/75 focus:outline-none focus:ring-2 focus:ring-white/70" autocomplete="off" aria-autocomplete="list" aria-controls="job-search-suggestions" aria-expanded="false" data-search-autocomplete-input>
            <div id="job-search-suggestions" class="search-autocomplete-list" data-search-autocomplete-list role="listbox" hidden></div>
            <p class="sr-only" data-search-autocomplete-status aria-live="polite"></p>
            <script type="application/json" data-search-autocomplete-source><?php echo is_string($searchSuggestionsJson) ? $searchSuggestionsJson : '[]'; ?></script>
          </div>
          <?php foreach ($selectedLocations as $selectedLocation): ?>
            <input type="hidden" name="location[]" value="<?php echo escapeSearchResultsValue($selectedLocation); ?>">
          <?php endforeach; ?>
          <?php foreach ($selectedPublishedMonths as $selectedPublishedMonth): ?>
            <input type="hidden" name="published[]" value="<?php echo escapeSearchResultsValue($selectedPublishedMonth); ?>">
          <?php endforeach; ?>
          <button type="submit" class="search-submit-button h-16 rounded-full bg-jet-charcoal px-9 text-xl font-bold text-white transition hover:bg-black sm:min-w-[165px] lg:min-w-[190px]">
            Search
          </button>
        </form>
      </div>
    </div>

    <div class="min-h-[260px] bg-white sm:min-h-[320px] lg:min-h-0">
      <img src="images/about-us.webp" alt="VOpen Market team in discussion" class="h-full w-full object-cover object-center">
    </div>
  </div>
</section>

<section class="bg-jet-cream py-10 sm:py-12 lg:py-14">
  <div class="mx-auto grid max-w-[1440px] gap-8 px-4 sm:px-6 lg:grid-cols-[minmax(0,0.92fr)_minmax(0,1.08fr)] lg:items-start lg:px-10">
    <div>
      <h2 class="jet-heading text-[2.9rem] leading-[0.9] tracking-[-0.05em] text-jet-charcoal sm:text-[4rem] lg:text-[4.6rem]">
        whatever your ambition, find your place at VOpen
      </h2>
    </div>

    <div class="max-w-[42rem] text-base font-semibold leading-8 text-jet-charcoal/80 sm:text-lg">
      <p>
        We&rsquo;re building the operating system for modern construction sourcing. That means faster supplier discovery, better buying decisions, and smarter logistics for teams across Canada.
      </p>
      <p class="mt-4">
        From commercial growth and product strategy to marketplace operations and supplier success, every team at VOpen helps turn complexity into a better customer experience across every order, supplier touchpoint, and customer project.
      </p>
    </div>
  </div>
</section>

<section id="job-results" class="bg-jet-cream pb-14 sm:pb-16 lg:pb-20">
  <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
    <div class="search-results-layout">
      <aside class="search-results-sidebar">
        <div class="search-results-sidebar-panel">
          <div class="flex items-center justify-between gap-4">
            <h2 class="search-results-sidebar-title">Published jobs</h2>
            <a href="search-results.php" class="search-reset-pill">Reset search</a>
          </div>

          <form action="search-results.php" method="get" data-search-filter-form>
            <?php if ($searchTerm !== ''): ?>
              <input type="hidden" name="keywords" value="<?php echo $searchValue; ?>">
            <?php endif; ?>

            <div class="search-filter-block">
              <p class="search-filter-label">Locations</p>

              <ul class="search-filter-list">
                <?php if ($locationFilters === []): ?>
                  <li class="search-filter-option">
                    <label>
                      <input type="checkbox" disabled>
                      <span>No published locations yet</span>
                      <span class="search-filter-count">0</span>
                    </label>
                  </li>
                <?php else: ?>
                  <?php foreach ($locationFilters as $filter): ?>
                    <li class="search-filter-option">
                      <label>
                        <input type="checkbox" name="location[]" value="<?php echo escapeSearchResultsValue($filter['label']); ?>"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                        <span><?php echo escapeSearchResultsValue($filter['label']); ?></span>
                        <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                      </label>
                    </li>
                  <?php endforeach; ?>
                <?php endif; ?>
              </ul>
            </div>

            <div class="search-filter-accordion">
              <div class="search-filter-section<?php echo $isPublishedSectionOpen ? ' is-open' : ''; ?>" data-filter-section>
                <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="<?php echo $isPublishedSectionOpen ? 'true' : 'false'; ?>" aria-controls="published-filter-panel">
                  <span>Published</span>
                  <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                    <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                  </svg>
                </button>

                <div id="published-filter-panel" class="search-filter-panel"<?php echo $isPublishedSectionOpen ? '' : ' hidden'; ?>>
                  <ul class="search-filter-list search-filter-list-compact">
                    <?php if ($publishedFilters === []): ?>
                      <li class="search-filter-option">
                        <label>
                          <input type="checkbox" disabled>
                          <span>No publish dates yet</span>
                          <span class="search-filter-count">0</span>
                        </label>
                      </li>
                    <?php else: ?>
                      <?php foreach ($publishedFilters as $filter): ?>
                        <li class="search-filter-option">
                          <label>
                            <input type="checkbox" name="published[]" value="<?php echo escapeSearchResultsValue($filter['label']); ?>"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                            <span><?php echo escapeSearchResultsValue($filter['label']); ?></span>
                            <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                          </label>
                        </li>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </ul>
                </div>
              </div>
            </div>

            <div class="search-filter-block">
              <p class="search-filter-label">Availability</p>
              <p class="search-filter-note">Only open, published roles are shown in this listing.</p>
            </div>

            <noscript>
              <div class="search-filter-actions">
                <button type="submit" class="search-reset-pill">Apply filters</button>
              </div>
            </noscript>
          </form>
        </div>
      </aside>

      <div class="search-results-main">
        <div class="search-results-toolbar">
          <p class="search-results-count">
            <span><?php echo $jobCount; ?></span> <?php echo $jobLabel; ?>
            <?php if ($searchTerm !== ''): ?>
              <span class="search-results-query">for &ldquo;<?php echo $searchValue; ?>&rdquo;</span>
            <?php endif; ?>
          </p>
          <div class="search-sort-button" aria-hidden="true">
            <span>Newest published roles</span>
          </div>
        </div>

        <div class="search-results-list-shell" role="list" aria-label="Job results">
          <?php if ($pageError !== ''): ?>
            <article class="search-no-results">
              <h3 class="text-[1.8rem] font-black leading-none tracking-[-0.04em] text-jet-charcoal">
                Jobs are unavailable right now
              </h3>
              <p class="mt-3 max-w-[40rem] text-base font-semibold leading-7 text-jet-charcoal/75">
                <?php echo escapeSearchResultsValue($pageError); ?>
              </p>
            </article>
          <?php elseif ($jobCount === 0): ?>
            <article class="search-no-results">
              <h3 class="text-[1.8rem] font-black leading-none tracking-[-0.04em] text-jet-charcoal">
                <?php echo escapeSearchResultsValue($emptyStateTitle); ?>
              </h3>
              <p class="mt-3 max-w-[40rem] text-base font-semibold leading-7 text-jet-charcoal/75">
                <?php echo escapeSearchResultsValue($emptyStateCopy); ?>
              </p>
            </article>
          <?php else: ?>
            <?php foreach ($visibleJobs as $index => $job): ?>
              <?php $jobAnchor = buildSearchResultsJobAnchor($job, $jobsOffset + $index); ?>
              <?php $jobDetailUrl = buildSearchResultsJobDetailUrl($job); ?>
              <article id="<?php echo escapeSearchResultsValue($jobAnchor); ?>" class="search-job-card" role="listitem">
                <div class="min-w-0 flex-1">
                  <h3 class="search-job-title">
                    <a href="<?php echo escapeSearchResultsValue($jobDetailUrl); ?>">
                      <?php echo escapeSearchResultsValue((string) ($job['title'] ?? '')); ?>
                    </a>
                  </h3>
                  <div class="search-job-meta">
                    <?php if (trim((string) ($job['location'] ?? '')) !== ''): ?>
                      <span class="search-job-pill"><?php echo escapeSearchResultsValue((string) $job['location']); ?></span>
                    <?php endif; ?>
                    <span class="search-job-pill">Open role</span>
                    <span class="search-job-pill"><?php echo escapeSearchResultsValue(formatSearchResultsDate((string) ($job['created_at'] ?? ''))); ?></span>
                  </div>

                  <p class="search-job-copy">
                    <?php echo escapeSearchResultsValue(summarizeSearchResultsDescription((string) ($job['description'] ?? ''))); ?>
                  </p>
                </div>

                <a href="<?php echo escapeSearchResultsValue($jobDetailUrl); ?>" class="search-job-link" aria-label="View details for <?php echo escapeSearchResultsValue((string) ($job['title'] ?? '')); ?>">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M7 17 17 7M8.5 7H17v8.5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </a>
              </article>
            <?php endforeach; ?>

            <?php if ($hasPagination): ?>
              <nav class="search-pagination" aria-label="Search result pages">
                <?php if ($currentResultsPage > 1): ?>
                  <a href="<?php echo escapeSearchResultsValue(buildSearchResultsPaginationUrl($currentResultsPage - 1, $searchTerm, $selectedLocations, $selectedPublishedMonths)); ?>" class="search-pagination-link" aria-label="Go to previous results page">
                    Previous
                  </a>
                <?php else: ?>
                  <span class="search-pagination-link is-disabled" aria-disabled="true">Previous</span>
                <?php endif; ?>

                <?php for ($page = 1; $page <= $totalResultsPages; $page++): ?>
                  <a
                    href="<?php echo escapeSearchResultsValue(buildSearchResultsPaginationUrl($page, $searchTerm, $selectedLocations, $selectedPublishedMonths)); ?>"
                    class="search-pagination-link<?php echo $page === $currentResultsPage ? ' is-active' : ''; ?>"
                    <?php echo $page === $currentResultsPage ? ' aria-current="page"' : ''; ?>
                    aria-label="Go to results page <?php echo $page; ?>"
                  >
                    <?php echo $page; ?>
                  </a>
                <?php endfor; ?>

                <?php if ($currentResultsPage < $totalResultsPages): ?>
                  <a href="<?php echo escapeSearchResultsValue(buildSearchResultsPaginationUrl($currentResultsPage + 1, $searchTerm, $selectedLocations, $selectedPublishedMonths)); ?>" class="search-pagination-link" aria-label="Go to next results page">
                    Next
                  </a>
                <?php else: ?>
                  <span class="search-pagination-link is-disabled" aria-disabled="true">Next</span>
                <?php endif; ?>
              </nav>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>



<?php include 'container/footer.php'; ?>
