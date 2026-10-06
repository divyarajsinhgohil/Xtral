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

// Compute front-end base URL so deep routes (e.g. /products/OH-222566) always resolve assets correctly
$__docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$__frontDir = str_replace('\\', '/', realpath(dirname(__DIR__)));
$frontBasePath = '';
if ($__docRoot !== '' && stripos($__frontDir, $__docRoot) === 0) {
    $frontBasePath = rtrim(substr($__frontDir, strlen($__docRoot)), '/');
}
$siteBaseUrl = ($frontBasePath !== '' ? $frontBasePath : '') . '/';
unset($__docRoot, $__frontDir, $frontBasePath);

// Link-preview (Open Graph) tags — WhatsApp/Facebook/etc. need absolute URLs.
// Pages may set $ogImage (path relative to the site root) and $ogDescription.
$__isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$siteOrigin = ($__isHttps ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? 'localhost');
unset($__isHttps);
$ogUrl = $siteOrigin . strtok($_SERVER['REQUEST_URI'] ?? '/', '#');
$ogImageUrl = $siteOrigin . $siteBaseUrl . ltrim($ogImage ?? 'assets/img/xtral_favicon_256.png', '/');
$ogDescription = $ogDescription ?? $pageDescription;
// Declaring size/type lets WhatsApp render the large preview without guessing
$ogImageInfo = @getimagesize(dirname(__DIR__) . '/' . ltrim($ogImage ?? 'assets/img/xtral_favicon_256.png', '/'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <base href="<?= htmlspecialchars($siteBaseUrl) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
  <meta property="og:site_name" content="X-Tral">
  <meta property="og:type" content="<?= isset($ogImage) ? 'product' : 'website' ?>">
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
  <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
  <meta property="og:image" content="<?= htmlspecialchars($ogImageUrl) ?>">
  <meta property="og:image:secure_url" content="<?= htmlspecialchars($ogImageUrl) ?>">
  <meta property="og:image:alt" content="<?= htmlspecialchars($pageTitle) ?>">
<?php if ($ogImageInfo): ?>
  <meta property="og:image:type" content="<?= htmlspecialchars($ogImageInfo['mime']) ?>">
  <meta property="og:image:width" content="<?= (int)$ogImageInfo[0] ?>">
  <meta property="og:image:height" content="<?= (int)$ogImageInfo[1] ?>">
<?php endif; ?>
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl) ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/xtral_favicon_32.png">
  <link rel="icon" type="image/png" sizes="256x256" href="assets/img/xtral_favicon_256.png">
  <link rel="apple-touch-icon" href="assets/img/xtral_favicon_256.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" media="print" onload="this.media='all'">
  <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"></noscript>
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/style.css') ?>">
  <?php
  if (!function_exists('getPriceLabel1')) {
      @require_once dirname(__DIR__) . '/xadmin/config/db.php';
  }
  $headerPriceLabel1 = function_exists('getPriceLabel1') ? getPriceLabel1() : 'Zone 1';
  $headerPriceLabel2 = function_exists('getPriceLabel2') ? getPriceLabel2() : 'Zone 2';
  ?>
  <script>
    window.XTRAL_PRICE_LABEL_1 = <?= json_encode($headerPriceLabel1) ?>;
    window.XTRAL_PRICE_LABEL_2 = <?= json_encode($headerPriceLabel2) ?>;
  </script>
</head>
<body>

  <!-- ============ HEADER ============ -->
  <header class="site-header">
    <div class="container nav">
      <a class="brand" href="index">
        <span class="brand-mark"></span>
        <span class="brand-text">
          <strong>X-TRAL</strong>
          <span>Premium Bathware</span>
        </span>
      </a>
      <nav class="nav-links" id="site-nav">
        <a href="index">Home</a>
        <a href="collections">Collections</a>
        <a href="products">Products</a>
        <a href="catalogue">Catalogue</a>
        <div class="nav-dropdown-wrapper">
          <a class="nav-dropdown-trigger" href="#">Brand <span class="arrow">▾</span></a>
          <div class="nav-dropdown-menu">
            <a href="profile">Company Profile</a>
            <a href="export">Export</a>
          </div>
        </div>
        <a href="about">About</a>
        <a href="contact">Contact</a>
      </nav>
      <div class="nav-cta">
        <form class="nav-inline-search" data-search-panel data-header-search hidden>
          <input type="text" id="header-search" placeholder="Search by name, code, series..." aria-label="Search products">
        </form>
        <button class="nav-search-toggle" type="button" aria-label="Search" aria-expanded="false" data-search-toggle>
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <a href="catalogue" class="btn btn-outline-dark">Download Catalogue</a>
        <button class="nav-toggle" type="button" aria-label="Menu" aria-controls="site-nav" aria-expanded="false"><span></span><span></span><span></span></button>
      </div>
    </div>
  </header>
