<?php
/**
 * X-tral Front — shared site header
 * Set $pageTitle and $pageDescription BEFORE including this file:
 *   $pageTitle = 'Products — X-tral';
 *   include __DIR__ . '/includes/header.php';
 */
$pageTitle = $pageTitle ?? 'X-Tral — Premium Bathware & Sanitary Ware';
$pageDescription = $pageDescription ?? 'X-Tral crafts premium faucets, showers, sanitary ware and bath solutions engineered for modern living.';
$pageKeywords = $pageKeywords ?? 'X-Tral, bathware, sanitary ware, bath fittings, faucets, taps, showers, shower mixer, diverters, kitchen sinks, wash basin, wellness, PTMT faucets, bathroom accessories, premium bathware India, bathroom fittings manufacturer, sanitaryware supplier, X-Tral bathware';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/xtral_favicon_32.png">
  <link rel="icon" type="image/png" sizes="256x256" href="assets/img/xtral_favicon_256.png">
  <link rel="apple-touch-icon" href="assets/img/xtral_favicon_256.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/style.css') ?>">
</head>
<body>


  <!-- ============ HEADER ============ -->
  <header class="site-header">
    <div class="container nav">
      <a class="brand" href="index.php">
        <span class="brand-mark"></span>
        <span class="brand-text">
          <strong>X-TRAL</strong>
          <span>Premium Bathware</span>
        </span>
      </a>
      <nav class="nav-links" id="site-nav">
        <a href="index.php">Home</a>
        <a href="products.php">Collections</a>
        <a href="catalogue.php">Catalogue</a>
        <div class="nav-dropdown-wrapper">
          <a class="nav-dropdown-trigger" href="#">Brand <span class="arrow">▾</span></a>
          <div class="nav-dropdown-menu">
            <a href="profile.php">Company Profile</a>
            <a href="export.php">Export</a>
          </div>
        </div>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </nav>
      <div class="nav-cta">
        <form class="nav-inline-search" data-search-panel data-header-search hidden>
          <input type="text" id="header-search" placeholder="Search by name, code, series..." aria-label="Search products">
        </form>
        <button class="nav-search-toggle" type="button" aria-label="Search" aria-expanded="false" data-search-toggle>
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <a href="catalogue.php" class="btn btn-outline-dark">Download Catalogue</a>
        <button class="nav-toggle" type="button" aria-label="Menu" aria-controls="site-nav" aria-expanded="false"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>
