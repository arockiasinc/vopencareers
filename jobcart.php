<?php
declare(strict_types=1);

function escapeJobCartValue(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$pageTitle = 'Saved Jobs | VOpen Market Careers';
$pageDescription = 'Review the jobs you saved at VOpen Market, compare openings, and start your application when ready.';
$currentPage = 'jobcart';
$bodyClass = 'bg-jet-cream';
$headerClass = 'sticky top-0 z-50 border-b border-black/5 bg-white site-header-elevated';

include 'container/header.php';
?>

<section class="jobcart-page-section">
  <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10">
    <section class="jobcart-shell jobcart-shell-single" aria-labelledby="jobcart-list-title">
      <div class="jobcart-section-head">
        <h1 id="jobcart-list-title" class="jet-heading jobcart-section-title">Your saved jobs</h1>
        <button type="button" class="jobcart-clear-button" data-clear-saved-jobs hidden>Clear all</button>
      </div>

      <div class="jobcart-empty" data-jobcart-empty>
        <p class="jobcart-empty-kicker">Nothing saved yet</p>
        <h2 class="jet-heading jobcart-empty-title">Start building your job cart.</h2>
        <p class="jobcart-empty-copy">
          Open any job detail page, click <strong>Save Job</strong>, and the role will be added here automatically.
        </p>
        <a href="<?php echo escapeJobCartValue(buildAppPath('search-results.php')); ?>" class="job-detail-secondary-link">Browse jobs</a>
      </div>

      <div class="jobcart-list" data-jobcart-list hidden></div>
    </section>
  </div>
</section>

<?php include 'container/footer.php'; ?>
