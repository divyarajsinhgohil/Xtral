<?php
/**
 * X-tral Front — home banner slider
 * Banners are managed in the admin panel: Catalogue → Home Screen Banners.
 * They load live from the webapi (banners.php); if no banner is active,
 * this whole section stays hidden automatically.
 *
 * Reposition on the page by moving this line in index.php:
 *   <?php include __DIR__ . '/includes/banner.php'; ?>
 */
?>
  <!-- ============ HOME BANNERS (from admin) ============ -->
  <section class="banner-slider banner-slider--hero" data-banner-section hidden>
    <div class="banner-track" data-banner-slider></div>
  </section>
