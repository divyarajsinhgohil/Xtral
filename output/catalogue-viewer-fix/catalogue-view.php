<?php
/**
 * X-Tral Front — 3D Interactive Flipbook Catalogue Viewer
 * Realistic open catalogue with 3D page flip animation, audio effects,
 * high-res progressive PDF rendering, zoom, and thumbnails drawer.
 */

require_once __DIR__ . '/xadmin/includes/catalogue_assets.php';
header('Cache-Control: no-store, max-age=0');
header('Expires: 0');

// Compute front-end base URL
$__docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$__frontDir = str_replace('\\', '/', realpath(__DIR__));
$frontBasePath = '';
if ($__docRoot !== '' && stripos($__frontDir, $__docRoot) === 0) {
    $frontBasePath = rtrim(substr($__frontDir, strlen($__docRoot)), '/');
}
$siteBaseUrl = ($frontBasePath !== '' ? $frontBasePath : '') . '/';
unset($__docRoot, $__frontDir, $frontBasePath);

$pdfParam   = trim($_GET['pdf'] ?? '');
$titleParam = trim($_GET['title'] ?? 'X-Tral Look Book');
$modeParam  = trim($_GET['mode'] ?? 'flipbook'); // 'flipbook' (default) or 'classic'

$pdfDir = __DIR__ . '/xadmin/uploads/catalogue/pdfs/';
$pdfSrc = '';
$isValid = false;

// 1. Check specified PDF parameter
if (!empty($pdfParam) && strpos($pdfParam, '..') === false) {
    $parsedPath = parse_url($pdfParam, PHP_URL_PATH) ?: $pdfParam;
    $rawFilename = basename($parsedPath);
    $decodedFilename = rawurldecode($rawFilename);
    
    $localFileInUploads = $pdfDir . $decodedFilename;
    $localFileInRoot    = __DIR__ . '/' . $decodedFilename;

    if (is_file($localFileInUploads)) {
        $isValid = true;
        $webPath = 'xadmin/uploads/catalogue/pdfs/' . rawurlencode($decodedFilename);
        $pdfSrc = $webPath . '?v=' . filemtime($localFileInUploads);
    } elseif (is_file($localFileInRoot)) {
        $isValid = true;
        $webPath = rawurlencode($decodedFilename);
        $pdfSrc = $webPath . '?v=' . filemtime($localFileInRoot);
    } elseif (filter_var($pdfParam, FILTER_VALIDATE_URL) || strpos($pdfParam, 'uploads/catalogue/pdfs/') !== false) {
        $isValid = true;
        $pdfSrc = $pdfParam;
    }
}

// 2. Fallback if no PDF param specified or not found: find first available catalogue PDF
if (!$isValid) {
    $defaultFile1 = $pdfDir . 'X-Tral Catalogue.pdf';
    $defaultFile2 = __DIR__ . '/X_TRAL_CATALOGUE.pdf';
    if (is_file($defaultFile1)) {
        $isValid = true;
        $pdfSrc = 'xadmin/uploads/catalogue/pdfs/' . rawurlencode('X-Tral Catalogue.pdf') . '?v=' . filemtime($defaultFile1);
        $titleParam = 'X-Tral Catalogue';
    } elseif (is_file($defaultFile2)) {
        $isValid = true;
        $pdfSrc = 'X_TRAL_CATALOGUE.pdf?v=' . filemtime($defaultFile2);
        $titleParam = 'X-Tral Catalogue';
    }
}

$thumbSrc = '';
if (!empty($pdfSrc)) {
    $pdfRaw = parse_url($pdfSrc, PHP_URL_PATH) ?: $pdfSrc;
    $pdfBase = rawurldecode(pathinfo($pdfRaw, PATHINFO_FILENAME));
    $catalogueRow = null;
    try {
        require_once __DIR__ . '/xadmin/config/db.php';
        $catalogueRow = fetchOne(
            'SELECT thumb_filename FROM whatsapp_catalogues WHERE pdf_filename = ? LIMIT 1',
            [rawurldecode(basename($pdfRaw))]
        );
    } catch (Throwable $e) {
        error_log('Catalogue cover lookup failed: ' . $e->getMessage());
    }
    // Use the saved admin filename, including WebP/JPEG and nonmatching names.
    // Only legacy PDFs without a database row use the filename convention.
    $thumbCandidates = $catalogueRow
        ? array_filter([$catalogueRow['thumb_filename'] ?? ''])
        : array_map(fn($ext) => $pdfBase . '.' . $ext, ['jpg', 'png', 'webp', 'jpeg']);
    foreach ($thumbCandidates as $thumbFilename) {
        $thumbPath = $pdfDir . 'Thumb/' . $thumbFilename;
        if (is_file($thumbPath)) {
            $thumbSrc = catalogueImageUrl(
                'xadmin/uploads/catalogue/pdfs/Thumb/' . rawurlencode($thumbFilename),
                $thumbPath
            );
            break;
        }
    }
}

$isClassicMode = ($modeParam === 'classic');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <base href="<?= htmlspecialchars($siteBaseUrl) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($titleParam) ?> — 3D Interactive Catalogue · X-Tral</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/xtral_favicon_32.png">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
  <link rel="stylesheet" href="assets/css/flipbook.css?v=<?= file_exists(__DIR__ . '/assets/css/flipbook.css') ? filemtime(__DIR__ . '/assets/css/flipbook.css') : time() ?>">
  <script>
    // Absolute base URL injected server-side — critical for PDF.js Worker path resolution on live server
    window.XTRAL_BASE_URL = <?= json_encode(rtrim(
      ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' .
      ($_SERVER['HTTP_HOST'] ?? 'localhost') . $siteBaseUrl
    , '/') . '/') ?>;
  </script>
</head>
<body class="flipbook-page-body" data-pdf-url="<?= htmlspecialchars($pdfSrc) ?>" data-pdf-title="<?= htmlspecialchars($titleParam) ?>" data-thumb-url="<?= htmlspecialchars($thumbSrc) ?>">


<?php if (!$isValid): ?>
  <div class="flip-error" style="height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:20px;text-align:center;padding:20px;">
    <i class="fa-solid fa-book-open" style="font-size:48px;color:#dfb15b;opacity:0.8;"></i>
    <h2 style="font-family:'Cinzel',serif;color:#fff;font-size:28px;margin:0;">Catalogue Not Found</h2>
    <p style="color:#a1b2b5;max-width:420px;margin:0;">We could not locate this catalogue publication. It may have been relocated or updated.</p>
    <a href="catalogue" class="btn btn--gold" style="padding:10px 24px;border-radius:8px;font-weight:600;text-decoration:none;">Back to Catalogue List</a>
  </div>
<?php elseif ($isClassicMode): ?>

  <!-- Classic standard PDF Iframe mode -->
  <header class="fb-header">
    <div class="fb-brand">
      <a href="catalogue" class="fb-back-btn">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Catalogue</span>
      </a>
      <div class="fb-title-wrap">
        <span class="fb-title"><?= htmlspecialchars($titleParam) ?></span>
        <span class="fb-badge"><i class="fa-solid fa-file-pdf"></i> PDF</span>
      </div>
    </div>
    <div class="fb-header-actions">
      <a href="catalogue-view?pdf=<?= rawurlencode($pdfParam ?: $pdfSrc) ?>&title=<?= rawurlencode($titleParam) ?>&mode=flipbook" class="fb-header-btn">
        <i class="fa-solid fa-book-open"></i> <span>3D Flipbook</span>
      </a>
      <a href="<?= htmlspecialchars($pdfSrc) ?>" download class="fb-header-btn fb-header-btn--primary">
        <i class="fa-solid fa-download"></i> <span>Download</span>
      </a>
    </div>
  </header>
  <main style="flex:1;width:100%;height:calc(100vh - 58px);overflow:hidden;">
    <iframe src="<?= htmlspecialchars($pdfSrc) ?>" style="width:100%;height:100%;border:none;"></iframe>
  </main>

<?php else: ?>

  <!-- ==================== INITIAL LOADING SCREEN ==================== -->
  <div class="fb-loader-screen" id="fb-loader-screen">
    <div class="fb-loader-brand">
      <img src="assets/img/xtral_logo_gold.svg" onerror="this.onerror=null;this.src='assets/img/xtral_favicon_32.png';" alt="X-Tral" class="fb-loader-logo">
      <h3 style="font-family:'Cinzel',serif;letter-spacing:0.12em;font-size:18px;margin:6px 0 0;color:#dfb15b;"><?= htmlspecialchars($titleParam) ?></h3>
    </div>

    <!-- 3D flipping mini-book loader animation (perfectly centered spread) -->
    <div class="fb-loader-book-anim">
      <div class="fb-loader-base --left"></div>
      <div class="fb-loader-base --right"></div>
      <div class="fb-loader-page"></div>
      <div class="fb-loader-page"></div>
      <div class="fb-loader-page"></div>
    </div>

    <div class="fb-loader-bar-wrap">
      <div class="fb-loader-bar" id="fb-loader-bar"></div>
    </div>
    <div class="fb-loader-text" id="fb-loader-text">Opening 3D Interactive Catalogue…</div>
  </div>

  <!-- ==================== TOP NAVIGATION HEADER (HINDWARE LOOK) ==================== -->
  <header class="fb-header">
    <div class="fb-brand">
      <a href="catalogue" class="fb-back-btn" title="Back to Catalogues">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Back</span>
      </a>
      <div class="fb-title-wrap">
        <h1 class="fb-title"><?= htmlspecialchars($titleParam) ?></h1>
      </div>
    </div>

    <div class="fb-header-actions">
      <button type="button" class="fb-header-btn" id="fb-share-btn" title="Share Catalogue">
        <i class="fa-solid fa-arrow-up-from-bracket"></i>
        <span>Share</span>
      </button>
      <a href="<?= htmlspecialchars($pdfSrc) ?>" download class="fb-header-btn fb-header-btn--primary" title="Download High-Resolution PDF">
        <i class="fa-solid fa-download"></i>
        <span>Download</span>
      </a>
    </div>
  </header>

  <!-- Toast for Share Feedback -->
  <div class="fb-toast" id="fb-toast">
    <i class="fa-solid fa-circle-check"></i>
    <span id="fb-toast-msg">Catalogue link copied to clipboard!</span>
  </div>

  <!-- ==================== MAIN 3D FLIPBOOK STAGE ==================== -->
  <main class="fb-stage" id="fb-stage">
    <!-- Ambient Lighting & Ground Soft Shadow -->
    <div class="fb-ambient-glow"></div>
    <div class="fb-stage-ground-shadow"></div>

    <!-- Floating Navigation Arrows -->
    <button type="button" class="fb-side-btn fb-side-btn--prev" title="Previous Page (Left Arrow)" aria-label="Previous Page">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button type="button" class="fb-side-btn fb-side-btn--next" title="Next Page (Right Arrow)" aria-label="Next Page">
      <i class="fa-solid fa-chevron-right"></i>
    </button>

    <!-- 3D Book Container -->
    <div class="fb-book-wrapper" id="fb-book-wrapper">
      <div class="fb-book-container" id="fb-book-container">
        <!-- Rendered Pages will be dynamically injected here -->
      </div>
    </div>
  </main>

  <!-- ==================== FLOATING BOTTOM CONTROL DOCK ==================== -->
  <nav class="fb-dock" aria-label="Flipbook Controls">
    <div class="fb-dock-group">
      <button type="button" class="fb-dock-btn" data-action="first" data-tip="First Page (Home)" aria-label="First Page">
        <i class="fa-solid fa-backward-step"></i>
      </button>
      <button type="button" class="fb-dock-btn" data-action="prev" data-tip="Previous Page (←)" aria-label="Previous Page">
        <i class="fa-solid fa-chevron-left"></i>
      </button>
    </div>

    <!-- Page Number & Direct Jump Indicator -->
    <div class="fb-page-counter" title="Click to jump to any page number">
      <span>Page</span>
      <input type="text" id="fb-page-input" class="fb-page-input" value="1" aria-label="Current Page">
      <span id="fb-page-total" class="fb-page-total">/ --</span>
    </div>

    <div class="fb-dock-group">
      <button type="button" class="fb-dock-btn" data-action="next" data-tip="Next Page (→)" aria-label="Next Page">
        <i class="fa-solid fa-chevron-right"></i>
      </button>
      <button type="button" class="fb-dock-btn" data-action="last" data-tip="Last Page (End)" aria-label="Last Page">
        <i class="fa-solid fa-forward-step"></i>
      </button>
    </div>

    <div class="fb-dock-divider"></div>

    <div class="fb-dock-group">
      <!-- Thumbnail Strip Drawer Toggle -->
      <button type="button" class="fb-dock-btn" data-action="thumbs" data-tip="Page Thumbnails" aria-label="Page Thumbnails">
        <i class="fa-solid fa-table-cells"></i>
      </button>
      <!-- Zoom & Inspect Mode -->
      <button type="button" class="fb-dock-btn" data-action="zoom" data-tip="Zoom In Details" aria-label="Zoom Details">
        <i class="fa-solid fa-magnifying-glass-plus"></i>
      </button>
      <!-- Auto Slideshow -->
      <button type="button" class="fb-dock-btn" data-action="autoplay" data-tip="Auto-play Slideshow" aria-label="Auto-play">
        <i class="fa-solid fa-play"></i>
      </button>
      <!-- Paper Sound Toggle -->
      <button type="button" class="fb-dock-btn" data-action="sound" data-tip="Flip Sound" aria-label="Flip Sound">
        <i class="fa-solid fa-volume-high"></i>
      </button>
      <!-- Fullscreen Toggle -->
      <button type="button" class="fb-dock-btn" data-action="fullscreen" data-tip="Toggle Fullscreen (F)" aria-label="Toggle Fullscreen">
        <i class="fa-solid fa-expand"></i>
      </button>
    </div>
  </nav>

  <!-- ==================== THUMBNAIL DRAWER ==================== -->
  <div class="fb-drawer" id="fb-drawer">
    <div class="fb-drawer-header">
      <div class="fb-drawer-title">
        <i class="fa-solid fa-grid-2"></i>
        <span>All Catalogue Pages</span>
      </div>
      <button type="button" class="fb-drawer-close" id="fb-drawer-close" aria-label="Close thumbnails">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="fb-thumb-strip" id="fb-thumb-strip">
      <!-- Populated dynamically -->
    </div>
  </div>

  <!-- ==================== HIGH-RES ZOOM INSPECTION MODAL ==================== -->
  <div class="fb-zoom-overlay" id="fb-zoom-overlay">
    <div class="fb-zoom-bar">
      <div style="display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-magnifying-glass" style="color:#dfb15b;"></i>
        <span id="fb-zoom-title" style="font-weight:600;font-size:14px;">High-Resolution Inspection</span>
      </div>
      <button type="button" id="fb-zoom-close" class="fb-header-btn" style="padding:6px 12px;font-size:13px;">
        <i class="fa-solid fa-xmark"></i> Close (Esc)
      </button>
    </div>
    <div class="fb-zoom-stage" id="fb-zoom-stage">
      <img src="" id="fb-zoom-img" class="fb-zoom-img" alt="Zoomed Page">
    </div>
  </div>

  <!-- Vendor Scripts -->
  <script src="assets/vendor/pdf.min.js?v=3.11.174"></script>
  <script src="assets/vendor/page-flip.browser.js?v=2.0.7"></script>
  <!-- Flipbook Engine -->
  <script src="assets/js/flipbook.js?v=<?= file_exists(__DIR__ . '/assets/js/flipbook.js') ? filemtime(__DIR__ . '/assets/js/flipbook.js') : time() ?>"></script>

<?php endif; ?>

</body>
</html>
