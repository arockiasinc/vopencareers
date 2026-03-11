<?php
declare(strict_types=1);

$pageTitle = 'Search Jobs | VOpen Market Careers';
$pageDescription = 'Browse current career opportunities at VOpen Market and explore roles published from the careers admin panel.';
$currentPage = 'search';
$bodyClass = 'bg-jet-cream';
$headerClass = 'sticky top-0 z-50 border-b border-black/5 bg-white site-header-elevated';

require_once __DIR__ . '/admin/container/db.php';

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

function buildSearchResultsFilters(array $counts): array
{
    if ($counts === []) {
        return [];
    }

    arsort($counts);

    $filters = [];

    foreach ($counts as $label => $count) {
        $filters[] = [
            'label' => (string) $label,
            'count' => (int) $count,
            'checked' => false,
        ];
    }

    return $filters;
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

$searchTerm = trim((string) ($_GET['keywords'] ?? ''));
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

$jobs = $allJobs;

if ($searchTerm !== '') {
    $jobs = array_values(array_filter(
        $allJobs,
        static function (array $job) use ($searchTerm): bool {
            $haystack = implode(' ', array_filter([
                trim((string) ($job['title'] ?? '')),
                trim((string) ($job['location'] ?? '')),
                trim((string) ($job['description'] ?? '')),
                trim((string) ($job['created_at'] ?? '')),
            ], static fn(string $value): bool => $value !== ''));

            return stripos($haystack, $searchTerm) !== false;
        }
    ));
}

$locationCounts = [];
$publishedCounts = [];

foreach ($allJobs as $job) {
    $locationLabel = trim((string) ($job['location'] ?? ''));

    if ($locationLabel === '') {
        $locationLabel = 'Unspecified';
    }

    $locationCounts[$locationLabel] = ($locationCounts[$locationLabel] ?? 0) + 1;

    $publishedLabel = formatSearchResultsMonth((string) ($job['created_at'] ?? ''));
    $publishedCounts[$publishedLabel] = ($publishedCounts[$publishedLabel] ?? 0) + 1;
}

$locationFilters = buildSearchResultsFilters($locationCounts);
$publishedFilters = buildSearchResultsFilters($publishedCounts);
$availabilityFilters = $allJobs === []
    ? []
    : [
        [
            'label' => 'Open roles',
            'count' => count($allJobs),
            'checked' => false,
        ],
    ];

$promoCards = [
    ['src' => 'images/gallery-1.png', 'alt' => 'Team members collaborating around a table'],
    ['src' => 'images/gallery-2.png', 'alt' => 'Fresh ingredients and packaging on a kitchen counter'],
    ['src' => 'images/gallery-3.jpg', 'alt' => 'A team member smiling in a bright office environment'],
    ['src' => 'images/story-jess.png', 'alt' => 'A VOpen Market team member portrait'],
];

$jobCount = count($jobs);
$jobLabel = $jobCount === 1 ? 'job' : 'jobs';
$searchValue = escapeSearchResultsValue($searchTerm);

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
          <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-6 top-1/2 h-7 w-7 -translate-y-1/2 text-jet-charcoal" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.25-3.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input id="job-search" name="keywords" type="search" value="<?php echo $searchValue; ?>" placeholder="Search for job title" class="h-16 w-full rounded-full border-0 bg-white px-16 text-lg font-semibold text-jet-charcoal shadow-soft outline-none ring-0 placeholder:text-jet-charcoal/75 focus:outline-none focus:ring-2 focus:ring-white/70" autocomplete="off">
          </div>
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

<section class="bg-jet-cream pb-14 sm:pb-16 lg:pb-20">
  <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
    <div class="search-results-layout">
      <aside class="search-results-sidebar">
        <div class="search-results-sidebar-panel">
          <div class="flex items-center justify-between gap-4">
            <h2 class="search-results-sidebar-title">Published jobs</h2>
            <a href="search-results.php" class="search-reset-pill">Reset search</a>
          </div>

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
                      <input type="checkbox" disabled<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                      <span><?php echo escapeSearchResultsValue($filter['label']); ?></span>
                      <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                    </label>
                  </li>
                <?php endforeach; ?>
              <?php endif; ?>
            </ul>
          </div>

          <div class="search-filter-accordion">
            <div class="search-filter-section" data-filter-section>
              <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="published-filter-panel">
                <span>Published</span>
                <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                  <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                </svg>
              </button>

              <div id="published-filter-panel" class="search-filter-panel" hidden>
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
                          <input type="checkbox" disabled<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                          <span><?php echo escapeSearchResultsValue($filter['label']); ?></span>
                          <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                        </label>
                      </li>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </ul>
              </div>
            </div>

            <div class="search-filter-section" data-filter-section>
              <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="availability-filter-panel">
                <span>Availability</span>
                <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                  <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                </svg>
              </button>

              <div id="availability-filter-panel" class="search-filter-panel" hidden>
                <ul class="search-filter-list search-filter-list-compact">
                  <?php if ($availabilityFilters === []): ?>
                    <li class="search-filter-option">
                      <label>
                        <input type="checkbox" disabled>
                        <span>No open roles</span>
                        <span class="search-filter-count">0</span>
                      </label>
                    </li>
                  <?php else: ?>
                    <?php foreach ($availabilityFilters as $filter): ?>
                      <li class="search-filter-option">
                        <label>
                          <input type="checkbox" disabled<?php echo $filter['checked'] ? ' checked' : ''; ?>>
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
                <?php echo $searchTerm !== '' ? 'No roles matched your search' : 'No jobs have been published yet'; ?>
              </h3>
              <p class="mt-3 max-w-[40rem] text-base font-semibold leading-7 text-jet-charcoal/75">
                <?php if ($searchTerm !== ''): ?>
                  Try a broader keyword or clear the search to view all published openings.
                <?php else: ?>
                  Add jobs from the admin panel to show them in this listing.
                <?php endif; ?>
              </p>
            </article>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
              <article class="search-job-card" role="listitem">
                <div class="min-w-0 flex-1">
                  <div class="search-job-meta">
                    <?php if (trim((string) ($job['location'] ?? '')) !== ''): ?>
                      <span class="search-job-pill"><?php echo escapeSearchResultsValue((string) $job['location']); ?></span>
                    <?php endif; ?>
                    <span class="search-job-pill">Open role</span>
                    <span class="search-job-pill"><?php echo escapeSearchResultsValue(formatSearchResultsDate((string) ($job['created_at'] ?? ''))); ?></span>
                  </div>

                  <h3 class="search-job-title">
                    <span><?php echo escapeSearchResultsValue((string) ($job['title'] ?? '')); ?></span>
                  </h3>

                  <p class="search-job-copy">
                    <?php echo escapeSearchResultsValue(summarizeSearchResultsDescription((string) ($job['description'] ?? ''))); ?>
                  </p>
                </div>

                <div class="search-job-link" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M8 16 16 8M10 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="bg-jet-cream pb-14 sm:pb-16 lg:pb-20">
  <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
    <h2 class="jet-heading text-center text-[2.7rem] leading-none tracking-[-0.05em] text-jet-charcoal sm:text-[3.7rem] lg:text-[4.2rem]">
      hungry for more?
    </h2>

    <div class="mx-auto search-results-link-stack">
      <a href="about-us.php" class="search-results-link-pill">
        <span>Visit our company site</span>
        <span class="search-results-link-icon">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 16 16 8M10 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </span>
      </a>
      <a href="index.php#culture" class="search-results-link-pill">
        <span>Discover more about our culture &amp; values</span>
        <span class="search-results-link-icon">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 16 16 8M10 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </span>
      </a>
      <a href="about-us.php" class="search-results-link-pill">
        <span>Explore life at VOpen Market</span>
        <span class="search-results-link-icon">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M8 16 16 8M10 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </span>
      </a>
    </div>

    <div class="search-results-media-grid">
      <?php foreach ($promoCards as $card): ?>
        <article class="search-results-media-card">
          <img src="<?php echo htmlspecialchars($card['src'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($card['alt'], ENT_QUOTES, 'UTF-8'); ?>">
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'container/footer.php'; ?>
