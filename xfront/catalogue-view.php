<?php
/**
 * X-tral Front — catalogue viewer
 * Opens a catalogue PDF directly in the browser's native PDF viewer
 * (plain <iframe>) — scroll, zoom and search all work exactly like
 * opening the PDF anywhere else. Standalone page (no site nav/footer)
 * so the reader gets full-height space.
 *
 *   catalogue-view.php?pdf=<uploads path or url>&title=<display name>
 */

$pdfParam   = $_GET['pdf'] ?? '';
$titleParam = trim($_GET['title'] ?? 'X-Tral Catalogue');

/* Only allow PDFs that live under xadmin's catalogue uploads folder —
   this page is a client-side viewer, not a server-side fetch, but we
   still don't want it usable as an arbitrary "open any URL" page.
   Match on the uploads path segment (not a hardcoded base) so it works
   both locally (/Xtral/xadmin/...) and on the live server (/xadmin/...). */
$allowedSegment = '/xadmin/uploads/catalogue/pdfs/';
$pdfPath = parse_url($pdfParam, PHP_URL_PATH) ?: '';
$segmentPos = stripos($pdfPath, $allowedSegment);
$isValid = $pdfParam !== '' && $segmentPos !== false && strpos($pdfPath, '..') === false;

/* Cache-bust with the file's real mtime so replacing a catalogue PDF
   (same filename, new content) shows up immediately instead of a
   browser serving a stale cached copy of the old file. */
$pdfSrc = $pdfParam;
if ($isValid) {
    $localPath = dirname(__DIR__) . '/xadmin/' . ltrim(substr($pdfPath, $segmentPos + strlen('/xadmin/')), '/');
    if (is_file($localPath)) {
        $pdfSrc .= (strpos($pdfParam, '?') === false ? '?' : '&') . 'v=' . filemtime($localPath);
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
  <link rel="icon" type="image/png" sizes="32x32" href="assets/img/xtral_favicon_32.png">
  <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/assets/css/style.css') ?>">
</head>
<body class="flip-page-body">

<?php if (!$isValid): ?>
  <div class="flip-error">
    <p>This catalogue couldn't be opened.</p>
    <a href="catalogue.php" class="btn btn--gold">Back to Catalogue</a>
  </div>
<?php else: ?>

  <main class="pdf-stage">
    <iframe class="pdf-frame" src="<?= htmlspecialchars($pdfSrc) ?>" title="<?= htmlspecialchars($titleParam) ?>"></iframe>
  </main>

<?php endif; ?>

</body>
</html>
