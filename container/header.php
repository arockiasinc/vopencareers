<?php
require_once __DIR__ . '/job-links.php';

$pageTitle = $pageTitle ?? 'Jobs & Careers at VOpen Market | Join Our Team';
$pageDescription = $pageDescription ?? 'Discover career opportunities at VOpen Market. Join a team transforming how construction materials and products are sourced across Canada.';
$currentPage = $currentPage ?? 'home';
$bodyClass = trim('font-jet text-jet-charcoal antialiased ' . ($bodyClass ?? ''));
$headerClass = $headerClass ?? 'sticky top-0 z-50 border-b border-black/5 bg-white';
$savedJobsHref = buildAppPath('jobcart.php');
$savedJobsButtonClass = 'saved-jobs-button rounded-full bg-jet-charcoal px-6 py-3 text-[17px] font-semibold text-white transition hover:bg-black';

if ($currentPage === 'jobcart') {
  $savedJobsButtonClass .= ' is-current';
}
$assetRoot = dirname(__DIR__);
$tailwindVersion = file_exists($assetRoot . '/css/tailwind.css') ? filemtime($assetRoot . '/css/tailwind.css') : time();
$styleVersion = file_exists($assetRoot . '/css/style.css') ? filemtime($assetRoot . '/css/style.css') : time();
$mainJsVersion = file_exists($assetRoot . '/js/main.js') ? filemtime($assetRoot . '/js/main.js') : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?php echo htmlspecialchars($pageDescription, ENT_QUOTES, 'UTF-8'); ?>">
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="icon" href="<?php echo htmlspecialchars(buildAppPath('images/logo.webp'), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(buildAppPath('css/tailwind.css?v=' . $tailwindVersion), ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars(buildAppPath('css/style.css?v=' . $styleVersion), ENT_QUOTES, 'UTF-8'); ?>">
  <script src="<?php echo htmlspecialchars(buildAppPath('js/main.js?v=' . $mainJsVersion), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
</head>
<body class="<?php echo htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8'); ?>">
  <header class="<?php echo htmlspecialchars($headerClass, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="mx-auto flex max-w-[1440px] items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-10">
      <a href="<?php echo htmlspecialchars(buildAppPath('index.php#top'), ENT_QUOTES, 'UTF-8'); ?>" class="shrink-0" aria-label="VOpen Market home">
        <img src="<?php echo htmlspecialchars(buildAppPath('images/logo.webp'), ENT_QUOTES, 'UTF-8'); ?>" alt="VOpen Market" class="hidden h-10 w-auto lg:block">
        <img src="<?php echo htmlspecialchars(buildAppPath('images/logo.webp'), ENT_QUOTES, 'UTF-8'); ?>" alt="VOpen Market" class="h-8 w-auto lg:hidden">
      </a>

      <nav class="hidden items-center gap-1 text-[17px] font-semibold text-jet-charcoal lg:flex" aria-label="Primary">
        <a class="nav-link" href="<?php echo htmlspecialchars(buildAppPath('index.php#top'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'home' ? ' aria-current="page"' : ''; ?>>Home</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(buildAppPath('search-results.php'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'search' ? ' aria-current="page"' : ''; ?>>Search Jobs</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(buildAppPath('index.php#culture'), ENT_QUOTES, 'UTF-8'); ?>">Teams</a>
        <a class="nav-link" href="<?php echo htmlspecialchars(buildAppPath('about-us.php'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'about' ? ' aria-current="page"' : ''; ?>>Our Company</a>
      </nav>

      <div class="hidden items-center gap-3 md:flex">
        <div class="inline-flex items-center gap-3 rounded-full border border-jet-charcoal/50 bg-white px-7 py-3 text-[17px] font-semibold text-jet-charcoal">
          <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
            <path d="M3.75 12h16.5M12 3.75c2.22 2.12 3.5 5.07 3.5 8.25S14.22 18.13 12 20.25M12 3.75c-2.22 2.12-3.5 5.07-3.5 8.25S9.78 18.13 12 20.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
          <span>English</span>
        </div>
        <a href="<?php echo htmlspecialchars($savedJobsHref, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars($savedJobsButtonClass, ENT_QUOTES, 'UTF-8'); ?>" data-saved-jobs-link aria-label="Saved Jobs (0)">
          <span>Saved Jobs</span>
          <span class="saved-jobs-count-badge" data-saved-jobs-count>0</span>
        </a>
      </div>

      <button id="menu-toggle" type="button" class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-jet-charcoal/20 bg-white text-jet-charcoal lg:hidden" aria-expanded="false" aria-controls="mobile-menu" aria-label="Toggle menu">
        <span class="menu-icon">
          <span class="menu-line"></span>
          <span class="menu-line"></span>
          <span class="menu-line"></span>
        </span>
      </button>
    </div>

    <div id="mobile-menu" class="hidden border-t border-black/5 bg-white lg:hidden">
      <nav class="mx-auto flex max-w-[1440px] flex-col gap-2 px-4 py-5 text-lg font-semibold sm:px-6" aria-label="Mobile">
        <a class="mobile-nav-link rounded-2xl px-4 py-3 transition hover:bg-white" href="<?php echo htmlspecialchars(buildAppPath('index.php#top'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'home' ? ' aria-current="page"' : ''; ?>>Home</a>
        <a class="mobile-nav-link rounded-2xl px-4 py-3 transition hover:bg-white" href="<?php echo htmlspecialchars(buildAppPath('search-results.php'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'search' ? ' aria-current="page"' : ''; ?>>Search Jobs</a>
        <a class="mobile-nav-link rounded-2xl px-4 py-3 transition hover:bg-white" href="<?php echo htmlspecialchars(buildAppPath('index.php#culture'), ENT_QUOTES, 'UTF-8'); ?>">Teams</a>
        <a class="mobile-nav-link rounded-2xl px-4 py-3 transition hover:bg-white" href="<?php echo htmlspecialchars(buildAppPath('about-us.php'), ENT_QUOTES, 'UTF-8'); ?>"<?php echo $currentPage === 'about' ? ' aria-current="page"' : ''; ?>>Our Company</a>

        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
          <div class="inline-flex items-center justify-center gap-3 rounded-full border border-jet-charcoal/50 bg-white px-6 py-3 text-base font-semibold text-jet-charcoal">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"></circle>
              <path d="M3.75 12h16.5M12 3.75c2.22 2.12 3.5 5.07 3.5 8.25S14.22 18.13 12 20.25M12 3.75c-2.22 2.12-3.5 5.07-3.5 8.25S9.78 18.13 12 20.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>English</span>
          </div>
          <a href="<?php echo htmlspecialchars($savedJobsHref, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo htmlspecialchars($savedJobsButtonClass, ENT_QUOTES, 'UTF-8'); ?> text-center text-base" data-saved-jobs-link aria-label="Saved Jobs (0)">
            <span>Saved Jobs</span>
            <span class="saved-jobs-count-badge" data-saved-jobs-count>0</span>
          </a>
        </div>
      </nav>
    </div>
  </header>

  <main id="top">
