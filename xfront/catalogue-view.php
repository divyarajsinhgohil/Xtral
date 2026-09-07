<?php
/**
 * X-tral Front — catalogue viewer
 * Opens a catalogue PDF directly in the browser's native PDF viewer
 * (plain <iframe>) — scroll, zoom and search all work exactly like
 * opening the PDF anywhere else.
 *
 *   catalogue-view.php?pdf=<uploads path or url>&title=<display name>
 */

$pdfParam   = trim($_GET['pdf'] ?? '');
$titleParam = trim($_GET['title'] ?? 'X-Tral Catalogue');

$pdfDir = dirname(__DIR__) . '/xadmin/uploads/catalogue/pdfs/';
$pdfSrc = '';
$isValid = false;

if (!empty($pdfParam) && strpos($pdfParam, '..') === false) {
    // Extract actual filename (e.g. "X-Tral Catalogue.pdf")
    $parsedPath = parse_url($pdfParam, PHP_URL_PATH) ?: $pdfParam;
    $rawFilename = basename($parsedPath);
    $decodedFilename = rawurldecode($rawFilename);
    
    // Check if the PDF file exists in xadmin/uploads/catalogue/pdfs/
    $localFile = $pdfDir . $decodedFilename;
    
    if (is_file($localFile)) {
        $isValid = true;
        // Build relative web path to the PDF file
        $webPath = '../xadmin/uploads/catalogue/pdfs/' . rawurlencode($decodedFilename);
        $pdfSrc = $webPath . '?v=' . filemtime($localFile);
    } elseif (filter_var($pdfParam, FILTER_VALIDATE_URL) || strpos($pdfParam, '/uploads/catalogue/pdfs/') !== false) {
        // Fallback: If it's a valid direct URL or path containing uploads
        $isValid = true;
        $pdfSrc = $pdfParam;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($titleParam) ?> — X-Tral</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/xtral_favicon_32.png">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="flip-page-body" style="margin:0;padding:0;overflow:hidden;">

<?php if (!$isValid): ?>
  <div class="flip-error" style="text-align:center;padding:80px 20px;">
    <h2>Catalogue Not Found</h2>
    <p class="muted" style="margin-bottom:24px;">This catalogue file could not be loaded or was removed.</p>
    <a href="catalogue.php" class="btn btn--gold">Back to Catalogue</a>
  </div>
<?php else: ?>

  <!-- Top bar with Back button, Title, and Download button -->
  <header class="pdf-viewer-bar" style="background:#114449;color:#fff;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.1);font-family:'Inter',sans-serif;">
    <a href="catalogue.php" style="color:#dfb15b;text-decoration:none;font-weight:600;font-size:14px;display:flex;align-items:center;gap:8px;">
      <i class="fa-solid fa-arrow-left"></i> Back to Catalogue
    </a>
    <div style="font-weight:600;font-size:16px;color:#fff;letter-spacing:0.02em;"><?= htmlspecialchars($titleParam) ?></div>
    <a href="<?= htmlspecialchars($pdfSrc) ?>" download style="color:#114449;background:#dfb15b;padding:7px 16px;border-radius:6px;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;transition:opacity 0.2s;">
      <i class="fa-solid fa-download"></i> Download PDF
    </a>
  </header>

  <main class="pdf-stage" style="height: calc(100vh - 52px);width:100vw;margin:0;padding:0;overflow:hidden;">
    <iframe class="pdf-frame" src="<?= htmlspecialchars($pdfSrc) ?>" title="<?= htmlspecialchars($titleParam) ?>" style="width:100%;height:100%;border:none;"></iframe>
  </main>

<?php endif; ?>

</body>
</html>
