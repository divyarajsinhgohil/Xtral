<?php
/**
 * X-tral Front — shared site footer + scripts
 * The category links under "Products" are filled live from the
 * admin API by main.js (data-footer-cats).
 */
?>
  <!-- ============ FOOTER ============ -->
  <footer>
    <div class="container">
      <div class="footer-grid">
        <div class="footer-about">
          <a href="index" class="brand">
            <span class="brand-mark"></span>
            <span class="brand-text">
              <strong>X-TRAL</strong>
              <span>Premium Bathware</span>
            </span>
          </a>
          <p style="margin-top: 20px;">Premium bathware &amp; sanitary ware engineered for modern living. Crafted with precision, designed to last.</p>
          <div class="socials" data-site-socials></div>
        </div>
        <div>
          <h4>Products</h4>
          <div class="footer-links" data-footer-cats>
            <a href="products">View all</a>
          </div>
        </div>
        <div>
          <h4>Company</h4>
          <div class="footer-links">
            <a href="about" style="display: block; margin-bottom: 12px;">About Us</a>
            <a href="profile" style="display: block; margin-bottom: 12px;">Company Profile</a>
            <a href="export" style="display: block; margin-bottom: 12px;">Export</a>
            <a href="catalogue" style="display: block; margin-bottom: 12px;">Catalogue</a>
            <a href="contact" style="display: block; margin-bottom: 12px;">Contact</a>
            <a href="contact" style="display: block; margin-bottom: 12px;">Become a Dealer</a>
          </div>
        </div>

      </div>
      <div class="copyright">
        <span>© <span data-year>2026</span> X-Tral. All rights reserved.</span>
        <span>Premium Bathware · Made in India</span>
      </div>
    </div>
  </footer>

  <!-- Deploy configuration (edit config.js when going live) -->
  <!-- ?v= uses the file's modified time so browsers never keep stale copies -->
  <script src="config.js?v=<?= filemtime(dirname(__DIR__) . '/config.js') ?>"></script>
  <!-- Live catalogue data from the xadmin Web API -->
  <script src="assets/js/data.js?v=<?= filemtime(dirname(__DIR__) . '/assets/js/data.js') ?>"></script>
  <script src="assets/js/main.js?v=<?= filemtime(dirname(__DIR__) . '/assets/js/main.js') ?>"></script>
</body>
</html>
