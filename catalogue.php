<?php
$pageTitle = 'Catalogue — X-Tral';
$pageDescription = 'View and download the X-Tral product catalogue.';
header('Cache-Control: no-store, max-age=0');
header('Expires: 0');
require_once __DIR__ . '/xadmin/includes/catalogue_assets.php';

include __DIR__ . '/includes/header.php';

// Server-side pre-rendering of catalogues
$catalogues = [];
$dbFile = __DIR__ . '/xadmin/config/db.php';
if (file_exists($dbFile)) {
    try {
        require_once $dbFile;
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT id, name, pdf_filename, thumb_filename
            FROM whatsapp_catalogues
            WHERE is_active = 1
            ORDER BY display_order ASC, id ASC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdfDir = __DIR__ . '/xadmin/uploads/catalogue/pdfs/';
        foreach ($rows as $r) {
            $pFilename = $r['pdf_filename'] ?? '';
            $tFilename = $r['thumb_filename'] ?? '';
            $fPath = $pdfDir . $pFilename;
            $catalogues[] = [
                'id' => (int)$r['id'],
                'category' => $r['name'],
                'title' => $r['name'],
                'url' => 'xadmin/uploads/catalogue/pdfs/' . rawurlencode($pFilename),
                'thumb_url' => !empty($tFilename) ? catalogueImageUrl(
                    'xadmin/uploads/catalogue/pdfs/Thumb/' . rawurlencode($tFilename),
                    $pdfDir . 'Thumb/' . $tFilename
                ) : null,
                'size_mb' => file_exists($fPath) ? round(filesize($fPath) / 1048576, 1) : null,
            ];
        }
    } catch (Throwable $e) {
        $catalogues = [];
    }
}
$isSingle = count($catalogues) === 1;
?>

  <section class="page-banner">
    <div class="container">
      <span class="eyebrow">Look Book</span>
      <h1>The X-Tral Catalogue</h1>
      <p>Explore the complete range — view online or download the PDF for each category.</p>
    </div>
  </section>

  <!-- ============ ALL CATALOGUES, SIDE BY SIDE ============ -->
  <section class="section">
    <div class="container">
      <div class="cat-cards-grid <?= $isSingle ? 'cat-cards-grid--single' : '' ?>" data-catalogue-list>
        <?php if (!empty($catalogues)): ?>
          <?php foreach ($catalogues as $c): ?>
            <?php 
              $catName = !empty($c['category']) ? $c['category'] : str_replace(' Catalogue', '', $c['title']);
              $coverStyle = !empty($c['thumb_url'])
                ? ' style="background-image:url(\'' . htmlspecialchars($c['thumb_url']) . '\');background-size:cover;background-position:center;"'
                : '';
              $sizeText = !empty($c['size_mb']) ? ('PDF · ' . $c['size_mb'] . ' MB') : 'PDF Brochure';
            ?>
            <div class="cat-mini-item">
              <a href="catalogue-view?pdf=<?= rawurlencode($c['url']) ?>&title=<?= rawurlencode($c['title']) ?>" class="cat-mini-cover-link">
                <div class="cat-mini-cover"<?= $coverStyle ?>>
                  <?php if (empty($c['thumb_url'])): ?>
                    <div class="cat-mini-top">
                      <span>X-Tral · Look Book</span>
                      <h3><?= htmlspecialchars($catName) ?></h3>
                      <div class="cat-mini-bot"><?= htmlspecialchars($sizeText) ?></div>
                    </div>
                  <?php endif; ?>
                </div>
              </a>
              <div class="cat-mini-details">
                <h3 class="cat-mini-title"><?= htmlspecialchars($catName) ?></h3>
                <span class="cat-mini-size"><?= htmlspecialchars($sizeText) ?></span>
              </div>
              <div class="cat-mini-actions">
                <a class="btn btn--gold btn--sm" href="catalogue-view?pdf=<?= rawurlencode($c['url']) ?>&title=<?= rawurlencode($c['title']) ?>"><i class="fa-solid fa-book-open"></i> Open 3D Lookbook</a>
                <a class="btn btn--outline btn--sm" href="<?= htmlspecialchars($c['url']) ?>" download>PDF <span class="ar">↓</span></a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="cat-cards-loading">Loading catalogues…</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
