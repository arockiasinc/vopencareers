<?php
declare(strict_types=1);

require_once __DIR__ . '/admin/container/db.php';
require_once __DIR__ . '/container/job-links.php';

function escapeJobDetailValue(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function readJobDetailId(mixed $value): ?int
{
    if (is_int($value)) {
        return $value > 0 ? $value : null;
    }

    if (!is_string($value)) {
        return null;
    }

    $value = trim($value);

    if ($value === '' || !ctype_digit($value)) {
        return null;
    }

    $jobId = (int) $value;

    return $jobId > 0 ? $jobId : null;
}

function formatJobDetailDate(?string $value, string $format = 'M j, Y', string $fallback = 'Recently posted'): string
{
    if ($value === null || trim($value) === '') {
        return $fallback;
    }

    try {
        return (new DateTimeImmutable($value))->format($format);
    } catch (Throwable $exception) {
        return $fallback;
    }
}

function getJobDetailCategories(array $job): array
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

function buildJobDetailUrl(array $job): string
{
    return buildJobPublicPath($job);
}

function buildJobDetailAbsoluteUrl(array $job): string
{
    $path = buildJobDetailUrl($job);
    $host = trim((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';

    if ($host === '') {
        return $path;
    }

    return ($isHttps ? 'https://' : 'http://') . $host . $path;
}

function findJobDetailRecordBySlug(string $slug, array $jobRecords): ?array
{
    $lookupSlug = normalizeJobSlugForLookup($slug);

    if ($lookupSlug === '') {
        return null;
    }

    foreach ($jobRecords as $jobRecord) {
        if (normalizeJobSlugForLookup((string) ($jobRecord['title'] ?? '')) === $lookupSlug) {
            return $jobRecord;
        }
    }

    return null;
}

function buildJobDetailPlainText(?string $value): string
{
    $plainText = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $plainText = str_replace(["\r\n", "\r"], "\n", $plainText);
    $plainText = str_replace("\xc2\xa0", ' ', $plainText);
    $plainText = (string) preg_replace('/[ \t]+/u', ' ', $plainText);
    $plainText = (string) preg_replace('/ *\n */u', "\n", $plainText);
    $plainText = (string) preg_replace('/\n{3,}/u', "\n\n", $plainText);

    return trim($plainText);
}

function summarizeJobDetailDescription(?string $value, int $limit = 220): string
{
    $plainText = buildJobDetailPlainText($value);

    if ($plainText === '') {
        return 'Explore the role, the team context, and the responsibilities for this opening at VOpen Market.';
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

function isJobDetailBulletLine(string $line): bool
{
    return preg_match('/^(?:[-*•]|\d+[.)])\s+/u', $line) === 1;
}

function isJobDetailOrderedBulletLine(string $line): bool
{
    return preg_match('/^\d+[.)]\s+/u', $line) === 1;
}

function stripJobDetailBulletPrefix(string $line): string
{
    return trim((string) preg_replace('/^(?:[-*•]|\d+[.)])\s+/u', '', $line));
}

function isLikelyJobDetailHeading(string $line, array $followingLines): bool
{
    $trimmed = trim($line);

    if ($trimmed === '') {
        return false;
    }

    if (substr($trimmed, -1) === ':') {
        return true;
    }

    if ($followingLines === []) {
        return false;
    }

    foreach ($followingLines as $followingLine) {
        if (!isJobDetailBulletLine($followingLine)) {
            return false;
        }
    }

    $length = function_exists('mb_strlen') ? mb_strlen($trimmed) : strlen($trimmed);

    return $length <= 90;
}

function buildJobDetailListHtml(array $lines): string
{
    $isOrderedList = true;

    foreach ($lines as $line) {
        if (!isJobDetailOrderedBulletLine($line)) {
            $isOrderedList = false;
            break;
        }
    }

    $tag = $isOrderedList ? 'ol' : 'ul';
    $items = [];

    foreach ($lines as $line) {
        $items[] = '<li>' . escapeJobDetailValue(stripJobDetailBulletPrefix($line)) . '</li>';
    }

    return '<' . $tag . '>' . implode('', $items) . '</' . $tag . '>';
}

function buildJobDetailLineBreakHtml(array $lines): string
{
    return implode('<br>', array_map(
        static fn(string $line): string => escapeJobDetailValue($line),
        $lines
    ));
}

function buildJobDetailPlainTextHtml(string $text): string
{
    $blocks = preg_split('/\n{2,}/u', trim($text)) ?: [];
    $htmlBlocks = [];

    foreach ($blocks as $block) {
        $lines = array_values(array_filter(
            array_map('trim', explode("\n", $block)),
            static fn(string $line): bool => $line !== ''
        ));

        if ($lines === []) {
            continue;
        }

        $allBullets = true;

        foreach ($lines as $line) {
            if (!isJobDetailBulletLine($line)) {
                $allBullets = false;
                break;
            }
        }

        if ($allBullets) {
            $htmlBlocks[] = buildJobDetailListHtml($lines);
            continue;
        }

        $heading = $lines[0];
        $bodyLines = array_slice($lines, 1);

        if (isLikelyJobDetailHeading($heading, $bodyLines)) {
            $htmlBlocks[] = '<h2>' . escapeJobDetailValue(rtrim($heading, ':')) . '</h2>';

            if ($bodyLines !== []) {
                $remainingBullets = true;

                foreach ($bodyLines as $line) {
                    if (!isJobDetailBulletLine($line)) {
                        $remainingBullets = false;
                        break;
                    }
                }

                $htmlBlocks[] = $remainingBullets
                    ? buildJobDetailListHtml($bodyLines)
                    : '<p>' . buildJobDetailLineBreakHtml($bodyLines) . '</p>';
            }

            continue;
        }

        $htmlBlocks[] = '<p>' . buildJobDetailLineBreakHtml($lines) . '</p>';
    }

    if ($htmlBlocks === []) {
        return '<p>More details for this role will be shared soon.</p>';
    }

    return implode("\n", $htmlBlocks);
}

function sanitizeJobDetailHtml(string $html): string
{
    if (!class_exists('DOMDocument')) {
        return '';
    }

    $libxmlState = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $fragment = function_exists('mb_convert_encoding')
        ? mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8')
        : $html;

    $loaded = $document->loadHTML(
        '<!DOCTYPE html><html><body><div data-job-detail-root="1">' . $fragment . '</div></body></html>'
    );

    if ($loaded === false) {
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        return '';
    }

    $body = $document->getElementsByTagName('body')->item(0);
    $root = $body?->firstChild;

    while ($root !== null && !($root instanceof DOMElement)) {
        $root = $root->nextSibling;
    }

    if (!$root instanceof DOMElement) {
        libxml_clear_errors();
        libxml_use_internal_errors($libxmlState);

        return '';
    }

    $sanitized = '';

    foreach ($root->childNodes as $childNode) {
        $sanitized .= sanitizeJobDetailNode($childNode);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($libxmlState);

    return trim($sanitized);
}

function sanitizeJobDetailNode($node): string
{
    if ($node instanceof DOMText) {
        return escapeJobDetailValue($node->wholeText);
    }

    if (!($node instanceof DOMElement)) {
        return '';
    }

    $tagName = strtolower($node->tagName);

    if (in_array($tagName, ['script', 'style', 'iframe', 'object', 'embed'], true)) {
        return '';
    }

    $children = '';

    foreach ($node->childNodes as $childNode) {
        $children .= sanitizeJobDetailNode($childNode);
    }

    if (!in_array($tagName, ['p', 'br', 'strong', 'em', 'b', 'i', 'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'a'], true)) {
        return $children;
    }

    if ($tagName === 'br') {
        return '<br>';
    }

    if ($tagName === 'a') {
        $href = trim((string) $node->getAttribute('href'));

        if ($href === '' || preg_match('#^(https?://|mailto:)#i', $href) !== 1) {
            return $children;
        }

        return '<a href="' . escapeJobDetailValue($href) . '" target="_blank" rel="noreferrer">' . $children . '</a>';
    }

    return '<' . $tagName . '>' . $children . '</' . $tagName . '>';
}

function buildJobDetailDescriptionHtml(?string $value): string
{
    $raw = trim((string) $value);

    if ($raw === '') {
        return '<p>More details for this role will be shared soon.</p>';
    }

    if ($raw !== strip_tags($raw)) {
        $sanitized = sanitizeJobDetailHtml($raw);

        if ($sanitized !== '') {
            return $sanitized;
        }
    }

    return buildJobDetailPlainTextHtml(buildJobDetailPlainText($raw));
}

function buildJobDetailApplicationLink(array $job): string
{
    $jobTitle = trim((string) ($job['title'] ?? 'Open role'));
    $jobLocation = trim((string) ($job['location'] ?? ''));
    $jobUrl = buildJobDetailAbsoluteUrl($job);
    $bodyLines = [
        'Hello,',
        '',
        'I would like to apply for the ' . $jobTitle . ' role at VOpen Market.',
    ];

    if ($jobLocation !== '') {
        $bodyLines[] = 'Location: ' . $jobLocation;
    }

    $bodyLines[] = 'Job link: ' . $jobUrl;
    $bodyLines[] = '';
    $bodyLines[] = 'Thank you.';

    return 'mailto:?subject=' . rawurlencode('Application for ' . $jobTitle . ' | VOpen Market')
        . '&body=' . rawurlencode(implode("\n", $bodyLines));
}

function buildJobDetailSavedJobPayload(array $job): array
{
    return [
        'id' => (int) ($job['id'] ?? 0),
        'title' => trim((string) ($job['title'] ?? '')),
        'location' => trim((string) ($job['location'] ?? '')),
        'categories' => getJobDetailCategories($job),
        'postedLabel' => 'Posted ' . formatJobDetailDate((string) ($job['created_at'] ?? '')),
        'summary' => summarizeJobDetailDescription((string) ($job['description'] ?? ''), 260),
        'url' => buildJobDetailUrl($job),
    ];
}

function buildRelatedJobCards(array $currentJob, array $jobRecords, int $limit = 3): array
{
    $currentJobId = (int) ($currentJob['id'] ?? 0);
    $currentLocation = strtolower(trim((string) ($currentJob['location'] ?? '')));

    $relatedJobs = array_values(array_filter(
        $jobRecords,
        static fn(array $job): bool => (int) ($job['status'] ?? 0) === 1 && (int) ($job['id'] ?? 0) !== $currentJobId
    ));

    usort($relatedJobs, static function (array $leftJob, array $rightJob) use ($currentLocation): int {
        $leftLocation = strtolower(trim((string) ($leftJob['location'] ?? '')));
        $rightLocation = strtolower(trim((string) ($rightJob['location'] ?? '')));
        $leftMatchesLocation = $currentLocation !== '' && $leftLocation === $currentLocation;
        $rightMatchesLocation = $currentLocation !== '' && $rightLocation === $currentLocation;

        if ($leftMatchesLocation !== $rightMatchesLocation) {
            return $leftMatchesLocation ? -1 : 1;
        }

        return strcmp((string) ($rightJob['created_at'] ?? ''), (string) ($leftJob['created_at'] ?? ''));
    });

    return array_slice($relatedJobs, 0, $limit);
}

$jobId = readJobDetailId($_GET['id'] ?? null);
$jobSlug = readJobSlugValue($_GET['slug'] ?? null);
$job = null;
$relatedJobs = [];
$allJobRecords = [];
$pageError = '';
$pageStatusCode = 200;

if ($jobSlug === null && $jobId === null) {
    $pageError = 'Choose a role from Search Jobs to open the dedicated detail page.';
    $pageStatusCode = 404;
} elseif ($jobSlug !== null) {
    try {
        $allJobRecords = fetchJobRecords();
        $job = findJobDetailRecordBySlug($jobSlug, $allJobRecords);
    } catch (Throwable $exception) {
        $pageError = 'The job details could not be loaded right now. Please try again shortly.';
        $pageStatusCode = 500;
    }

    if ($job === null && $pageStatusCode === 200) {
        $pageError = 'This job detail page is not available.';
        $pageStatusCode = 404;
    }
} else {
    try {
        $job = fetchJobRecordById($jobId);
    } catch (Throwable $exception) {
        $pageError = 'The job details could not be loaded right now. Please try again shortly.';
        $pageStatusCode = 500;
    }
}

if ($job !== null && (int) ($job['status'] ?? 0) !== 1) {
    $job = null;
    $pageError = 'This role is no longer available in the public listings.';
    $pageStatusCode = 404;
}

if ($job !== null) {
    $canonicalUrl = buildJobDetailUrl($job);
    $canonicalSlug = normalizeJobSlugForLookup(buildJobSlugFromTitle((string) ($job['title'] ?? '')));
    $requestedSlug = normalizeJobSlugForLookup($jobSlug);

    if (($jobId !== null && $jobSlug === null) || ($requestedSlug !== '' && $requestedSlug !== $canonicalSlug)) {
        header('Location: ' . $canonicalUrl, true, 301);
        exit;
    }

    try {
        if ($allJobRecords === []) {
            $allJobRecords = fetchJobRecords();
        }

        $relatedJobs = buildRelatedJobCards($job, $allJobRecords);
    } catch (Throwable $exception) {
        $relatedJobs = [];
    }
}

http_response_code($pageStatusCode);

$pageTitle = $job !== null
    ? trim((string) ($job['title'] ?? 'Role Details')) . ' | VOpen Market Careers'
    : 'Job Details | VOpen Market Careers';
$pageDescription = $job !== null
    ? summarizeJobDetailDescription((string) ($job['description'] ?? ''))
    : 'Review the role details and published openings at VOpen Market.';
$currentPage = 'search';
$bodyClass = 'bg-jet-cream';
$headerClass = 'sticky top-0 z-50 border-b border-black/5 bg-white site-header-elevated';
$jobApplyUrl = $job !== null ? buildJobDetailApplicationLink($job) : '#';
$jobSavedPayloadJson = $job !== null
    ? json_encode(buildJobDetailSavedJobPayload($job), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
    : null;

include 'container/header.php';
?>

<section class="job-detail-page-section">
  <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
    <?php if ($job === null): ?>
      <div class="job-detail-empty-card">
        <p class="job-detail-kicker">Role unavailable</p>
        <h1 class="jet-heading job-detail-empty-title">This job detail page is not available.</h1>
        <p class="job-detail-empty-copy"><?php echo escapeJobDetailValue($pageError); ?></p>
        <div class="job-detail-empty-actions">
          <a href="<?php echo escapeJobDetailValue(buildAppPath('search-results.php')); ?>" class="job-detail-primary-link">Back to Search Jobs</a>
          <a href="<?php echo escapeJobDetailValue(buildAppPath('about-us.php')); ?>" class="job-detail-secondary-link">Learn about VOpen</a>
        </div>
      </div>
    <?php else: ?>
      <nav class="job-detail-breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo escapeJobDetailValue(buildAppPath('search-results.php')); ?>">Search Jobs</a>
        <span aria-hidden="true">/</span>
        <span><?php echo escapeJobDetailValue((string) ($job['title'] ?? '')); ?></span>
      </nav>

      <section class="job-detail-hero">
        <div class="job-detail-hero-main mb-2">
          <p class="job-detail-kicker">Open position</p>
          <h1 class="jet-heading job-detail-title"><?php echo escapeJobDetailValue((string) ($job['title'] ?? '')); ?></h1>
          <p class="job-detail-summary">
            <?php echo escapeJobDetailValue(summarizeJobDetailDescription((string) ($job['description'] ?? ''), 260)); ?>
          </p>

          <div class="job-detail-meta">
            <?php if (trim((string) ($job['location'] ?? '')) !== ''): ?>
              <span class="job-detail-pill job-detail-pill-location"><?php echo escapeJobDetailValue((string) $job['location']); ?></span>
            <?php endif; ?>
            <?php foreach (getJobDetailCategories($job) as $categoryLabel): ?>
              <span class="job-detail-pill"><?php echo escapeJobDetailValue($categoryLabel); ?></span>
            <?php endforeach; ?>
            <span class="job-detail-pill">Open role</span>
            <span class="job-detail-pill">Posted <?php echo escapeJobDetailValue(formatJobDetailDate((string) ($job['created_at'] ?? ''))); ?></span>
          </div>
        </div>

    
      </section>

      <div class="job-detail-layout">
        <div class="job-detail-main">
          <article class="job-detail-card">
            <div class="job-detail-card-head">
              <p class="job-detail-section-kicker">Job description</p>
              
            </div>

            <div class="job-detail-description">
              <?php echo buildJobDetailDescriptionHtml((string) ($job['description'] ?? '')); ?>
            </div>

            <div class="job-detail-actions">
              <button
                type="button"
                class="job-detail-save-button"
                data-save-job
                data-save-job-label="Save Job"
                data-saved-label="Saved"
                <?php if (is_string($jobSavedPayloadJson)): ?>
                  data-job="<?php echo escapeJobDetailValue($jobSavedPayloadJson); ?>"
                <?php endif; ?>
              >
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                  <path d="M6.75 4.75h10.5a1.5 1.5 0 0 1 1.5 1.5v13l-6.75-3.4-6.75 3.4v-13a1.5 1.5 0 0 1 1.5-1.5Z" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span data-save-job-text>Save Job</span>
              </button>

              <a
                href="<?php echo escapeJobDetailValue($jobApplyUrl); ?>"
                class="job-detail-apply-button"
                data-apply-job
                <?php if (is_string($jobSavedPayloadJson)): ?>
                  data-job="<?php echo escapeJobDetailValue($jobSavedPayloadJson); ?>"
                <?php endif; ?>
              >
                Apply
              </a>
            </div>
          </article>
        </div>

           <aside class="job-detail-hero-panel job-detail-hero-panel-visual" aria-label="Opportunity at VOpen Market">
          <img
            src="<?php echo escapeJobDetailValue(buildAppPath('images/opportunity.webp')); ?>"
            alt="Opportunity at VOpen Market"
            class=""
          >
        </aside>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'container/footer.php'; ?>
