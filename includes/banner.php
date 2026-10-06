<?php
/**
 * X-tral Front — home banner slider
 * Banners are managed in the admin panel: Catalogue → Home Screen Banners.
 * Pre-rendered in PHP for instant loading, with JS handling sliding and aspect sync.
 */
$banners = [];
$dbFile = dirname(__DIR__) . '/xadmin/config/db.php';
if (file_exists($dbFile)) {
    try {
        require_once $dbFile;
        $pdo = getDBConnection();
        $stmt = $pdo->query("
            SELECT id, title, banner_type, image_url, video_url, link_url
            FROM catalogue_banners
            WHERE is_active = 1
            ORDER BY display_order, id
        ");
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        $banners = [];
    }
}
?>
  <!-- ============ HOME BANNERS (from admin) ============ -->
  <section class="banner-slider banner-slider--hero" data-banner-section <?= empty($banners) ? 'hidden' : '' ?>>
    <div class="banner-track" data-banner-slider>
      <?php foreach ($banners as $i => $b): ?>
        <?php 
          $link = !empty($b['link_url']) ? $b['link_url'] : null;
          $isVid = ($b['banner_type'] ?? '') === 'video';
          $file = $isVid ? ($b['video_url'] ?? '') : ($b['image_url'] ?? '');
          $src = 'xadmin/uploads/catalogue/banners/' . rawurlencode($file);
        ?>
        <?php if ($link): ?>
          <a class="banner-slide <?= $i === 0 ? 'active' : '' ?>" href="<?= htmlspecialchars($link) ?>">
        <?php else: ?>
          <div class="banner-slide <?= $i === 0 ? 'active' : '' ?>">
        <?php endif; ?>
            <?php if ($isVid): ?>
              <video src="<?= htmlspecialchars($src) ?>" <?= $i === 0 ? 'autoplay loop muted playsinline' : 'loop muted playsinline preload="none"' ?> style="width: 100%; height: 100%; object-fit: cover;"></video>
            <?php else: ?>
              <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($b['title'] ?? 'Banner') ?>" <?= $i === 0 ? '' : 'loading="lazy"' ?>>
            <?php endif; ?>
        <?php if ($link): ?>
          </a>
        <?php else: ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>
