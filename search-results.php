<?php
$pageTitle = 'Search Jobs | VOpen Market Careers';
$pageDescription = 'Browse static career opportunities at VOpen Market and explore the teams building the future of construction sourcing across Canada.';
$currentPage = 'search';

$searchTerm = trim((string) ($_GET['keywords'] ?? ''));

$allJobs = [
    [
        'title' => 'Sales Operations Project Manager',
        'location' => 'Toronto, ON',
        'category' => 'Sales',
        'type' => 'Full-time / Hybrid',
        'description' => 'Drive CRM integrity, reporting, and commercial planning across our supplier growth teams as VOpen scales nationally.',
    ],
    [
        'title' => 'Integration Account Manager',
        'location' => 'Vancouver, BC',
        'category' => 'Independent Contractor',
        'type' => 'Full-time / Hybrid',
        'description' => 'Own strategic supplier onboarding, coordinate implementation milestones, and turn complex launches into repeatable playbooks.',
    ],
    [
        'title' => 'Junior Recruiter',
        'location' => 'Toronto, ON',
        'category' => 'Corporate',
        'type' => 'Full-time / On-site',
        'description' => 'Support hiring across operations, product, and customer teams while shaping a fast, candidate-friendly recruiting process.',
    ],
    [
        'title' => 'Financial Accountant',
        'location' => 'Remote, Canada',
        'category' => 'Finance',
        'type' => 'Full-time / Remote',
        'description' => 'Lead month-end close work, partner with operations leaders, and help build the financial controls behind a growing marketplace.',
    ],
    [
        'title' => 'Account Executive, Commercial Growth',
        'location' => 'Calgary, AB',
        'category' => 'Sales',
        'type' => 'Full-time / Field',
        'description' => 'Win new contractor accounts, grow regional demand, and translate market signals into pipeline for our commercial team.',
    ],
    [
        'title' => 'Network Optimisation Analyst',
        'location' => 'Montreal, QC',
        'category' => 'Data & Analytics',
        'type' => 'Full-time / Hybrid',
        'description' => 'Model delivery coverage, analyze supplier density, and recommend smarter routing and fulfillment decisions across Canada.',
    ],
    [
        'title' => 'Senior Product Manager, Operations Research',
        'location' => 'Remote, Canada',
        'category' => 'Tech & Product',
        'type' => 'Full-time / Remote',
        'description' => 'Turn forecasting, routing, and inventory challenges into product bets that improve availability, speed, and margin.',
    ],
    [
        'title' => 'Supplier Success Specialist',
        'location' => 'Edmonton, AB',
        'category' => 'Customer Service',
        'type' => 'Full-time / Hybrid',
        'description' => 'Help suppliers launch smoothly, remove operational friction, and keep partner performance high through proactive support.',
    ],
    [
        'title' => 'Principal Compliance Manager',
        'location' => 'Toronto, ON',
        'category' => 'Other',
        'type' => '12-month contract / Hybrid',
        'description' => 'Own policy execution, strengthen internal controls, and guide teams through regulatory and commercial compliance requirements.',
    ],
    [
        'title' => 'Health and Safety Specialist',
        'location' => 'Mississauga, ON',
        'category' => 'Operations & Logistics',
        'type' => 'Full-time / On-site',
        'description' => 'Build practical health and safety programs across warehousing and logistics environments as we expand our network footprint.',
    ],
];

$jobs = $allJobs;

if ($searchTerm !== '') {
    $jobs = array_values(array_filter(
        $allJobs,
        static function (array $job) use ($searchTerm): bool {
            $haystack = implode(' ', [
                $job['title'],
                $job['location'],
                $job['category'],
                $job['type'],
                $job['description'],
            ]);

            return stripos($haystack, $searchTerm) !== false;
        }
    ));
}

$categoryLabels = [
    'Sales',
    'Corporate',
    'Finance',
    'Tech & Product',
    'Data & Analytics',
    'Customer Service',
    'Operations & Logistics',
    'Other',
    'Independent Contractor',
];

$categoryCounts = array_count_values(array_map(
    static fn(array $job): string => $job['category'],
    $allJobs
));

$categoryFilters = array_map(
    static fn(string $label): array => [
        'label' => $label,
        'count' => $categoryCounts[$label] ?? 0,
        'checked' => false,
    ],
    $categoryLabels
);

$countryFilters = [
    [
        'label' => 'Canada',
        'count' => count($allJobs),
        'checked' => false,
    ],
];

$cityCounts = [];
$typeCounts = [];

foreach ($allJobs as $job) {
    $locationParts = array_map('trim', explode(',', $job['location']));
    $cityLabel = $locationParts[0] !== '' ? $locationParts[0] : $job['location'];

    $cityCounts[$cityLabel] = ($cityCounts[$cityLabel] ?? 0) + 1;
    $typeCounts[$job['type']] = ($typeCounts[$job['type']] ?? 0) + 1;
}

$cityFilters = array_map(
    static fn(string $label): array => [
        'label' => $label,
        'count' => $cityCounts[$label],
        'checked' => false,
    ],
    array_keys($cityCounts)
);

$typeFilters = array_map(
    static fn(string $label): array => [
        'label' => $label,
        'count' => $typeCounts[$label],
        'checked' => false,
    ],
    array_keys($typeCounts)
);

$promoCards = [
    ['src' => 'images/gallery-1.png', 'alt' => 'Team members collaborating around a table'],
    ['src' => 'images/gallery-2.png', 'alt' => 'Fresh ingredients and packaging on a kitchen counter'],
    ['src' => 'images/gallery-3.jpg', 'alt' => 'A team member smiling in a bright office environment'],
    ['src' => 'images/story-jess.png', 'alt' => 'A VOpen Market team member portrait'],
];

$jobCount = count($jobs);
$jobLabel = $jobCount === 1 ? 'job' : 'jobs';
$searchValue = htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8');

include 'container/header.php';
?>

<section class="bg-jet-cream">
  <div class="grid overflow-hidden lg:grid-cols-[1.03fr_0.97fr]">
    <div class="bg-jet-orange px-5 py-7 sm:px-8 sm:py-10 md:px-10 md:py-12 lg:px-14 lg:py-16 xl:px-16 xl:py-20">
      <div class="mx-auto max-w-[680px]">
        <h1 class="jet-heading max-w-[10ch] text-[3.1rem] leading-[0.9] tracking-[-0.06em] text-white sm:text-[4rem] lg:text-[5.1rem]">
          search jobs
        </h1>

        <form id="job-search-form" action="search-results.php" method="get" class="mt-8 flex flex-col gap-3 sm:flex-row" role="search" aria-label="Search jobs">
          <label for="job-search" class="sr-only">Search for job title</label>
          <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-6 top-1/2 h-7 w-7 -translate-y-1/2 text-jet-charcoal" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.25-3.25" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <input id="job-search" name="keywords" type="search" value="<?php echo $searchValue; ?>" placeholder="Search for job title" class="h-16 w-full rounded-full border-0 bg-white px-16 text-lg font-semibold text-jet-charcoal shadow-soft outline-none ring-0 placeholder:text-jet-charcoal/75 focus:outline-none focus:ring-2 focus:ring-white/70" autocomplete="off">
          </div>
          <button type="submit" class="h-16 rounded-full bg-jet-charcoal px-9 text-xl font-bold text-white transition hover:bg-black sm:min-w-[165px] lg:min-w-[190px]">
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
            <h2 class="search-results-sidebar-title">Refine your search</h2>
            <button type="button" class="search-reset-pill">Reset filters</button>
          </div>

          <div class="search-filter-block">
            <p class="search-filter-label">Job Categories</p>

            <div class="search-filter-search">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M11 5a6 6 0 1 0 0 12a6 6 0 0 0 0-12Zm8 14l-3.25-3.25" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
              <input type="text" class="search-filter-input" value="" placeholder="Search job category">
            </div>

            <ul class="search-filter-list">
              <?php foreach ($categoryFilters as $filter): ?>
                <li class="search-filter-option">
                  <label>
                    <input type="checkbox"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                    <span><?php echo htmlspecialchars($filter['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                  </label>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

          <div class="search-filter-accordion">
            <div class="search-filter-section" data-filter-section>
              <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="country-filter-panel">
                <span>Country</span>
                <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                  <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                </svg>
              </button>

              <div id="country-filter-panel" class="search-filter-panel" hidden>
                <ul class="search-filter-list search-filter-list-compact">
                  <?php foreach ($countryFilters as $filter): ?>
                    <li class="search-filter-option">
                      <label>
                        <input type="checkbox"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($filter['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                      </label>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

            <div class="search-filter-section" data-filter-section>
              <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="city-filter-panel">
                <span>City</span>
                <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                  <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                </svg>
              </button>

              <div id="city-filter-panel" class="search-filter-panel" hidden>
                <ul class="search-filter-list search-filter-list-compact">
                  <?php foreach ($cityFilters as $filter): ?>
                    <li class="search-filter-option">
                      <label>
                        <input type="checkbox"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($filter['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                      </label>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>

            <div class="search-filter-section" data-filter-section>
              <button type="button" class="search-filter-toggle" data-filter-toggle aria-expanded="false" aria-controls="type-filter-panel">
                <span>Type</span>
                <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
                  <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
                </svg>
              </button>

              <div id="type-filter-panel" class="search-filter-panel" hidden>
                <ul class="search-filter-list search-filter-list-compact">
                  <?php foreach ($typeFilters as $filter): ?>
                    <li class="search-filter-option">
                      <label>
                        <input type="checkbox"<?php echo $filter['checked'] ? ' checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($filter['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="search-filter-count"><?php echo (int) $filter['count']; ?></span>
                      </label>
                    </li>
                  <?php endforeach; ?>
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

          <button type="button" class="search-sort-button">
            <span>Most relevant</span>
            <svg viewBox="0 0 12 8" fill="currentColor" aria-hidden="true">
              <path d="M1.41.59 6 5.17 10.59.59 12 2l-6 6-6-6L1.41.59Z"></path>
            </svg>
          </button>
        </div>

        <div class="search-results-list-shell" role="list" aria-label="Job results">
          <?php if ($jobCount === 0): ?>
            <article class="search-no-results">
              <h3 class="text-[1.8rem] font-black leading-none tracking-[-0.04em] text-jet-charcoal">
                No roles matched your search
              </h3>
              <p class="mt-3 max-w-[40rem] text-base font-semibold leading-7 text-jet-charcoal/75">
                Try a broader keyword like <span class="font-black text-jet-charcoal">sales</span>, <span class="font-black text-jet-charcoal">product</span>, or <span class="font-black text-jet-charcoal">operations</span> to preview more openings in this static layout.
              </p>
            </article>
          <?php else: ?>
            <?php foreach ($jobs as $job): ?>
              <article class="search-job-card" role="listitem">
                <div class="min-w-0 flex-1">
                  <div class="search-job-meta">
                    <span class="search-job-pill"><?php echo htmlspecialchars($job['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="search-job-pill"><?php echo htmlspecialchars($job['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="search-job-pill"><?php echo htmlspecialchars($job['type'], ENT_QUOTES, 'UTF-8'); ?></span>
                  </div>

                  <h3 class="search-job-title">
                    <a href="#"><?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?></a>
                  </h3>

                  <p class="search-job-copy">
                    <?php echo htmlspecialchars($job['description'], ENT_QUOTES, 'UTF-8'); ?>
                  </p>
                </div>

                <a href="#" class="search-job-link" aria-label="View <?php echo htmlspecialchars($job['title'], ENT_QUOTES, 'UTF-8'); ?>">
                  <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M8 16 16 8M10 8h6v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                </a>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if ($jobCount > 0): ?>
          <nav class="search-pagination" aria-label="Pagination">
            <a href="#" class="search-pagination-link is-disabled" aria-disabled="true">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m14.5 6-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
            <a href="#" class="search-pagination-link is-active" aria-current="page">1</a>
            <a href="#" class="search-pagination-link">2</a>
            <a href="#" class="search-pagination-link">3</a>
            <a href="#" class="search-pagination-link">4</a>
            <a href="#" class="search-pagination-link">5</a>
            <a href="#" class="search-pagination-link">
              <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="m9.5 6 6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </a>
          </nav>
        <?php endif; ?>
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
