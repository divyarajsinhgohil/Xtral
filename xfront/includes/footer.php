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
          <a href="index.php" class="brand">
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
            <a href="products.php">View all</a>
          </div>
        </div>
        <div>
          <h4>Company</h4>
          <div class="footer-links">
            <a href="about.php" style="display: block; margin-bottom: 12px;">About Us</a>
            <a href="profile.php" style="display: block; margin-bottom: 12px;">Company Profile</a>
            <a href="export.php" style="display: block; margin-bottom: 12px;">Export</a>
            <a href="catalogue.php" style="display: block; margin-bottom: 12px;">Catalogue</a>
            <a href="contact.php" style="display: block; margin-bottom: 12px;">Contact</a>
            <a href="contact.php" style="display: block; margin-bottom: 12px;">Become a Dealer</a>
          </div>
        </div>
        <div class="footer-contact">
          <h4>Get in touch</h4>
          <div class="footer-links">
            <a href="mailto:xtralcare@gmail.com" style="display: block; margin-bottom: 12px;" data-site-email>xtralcare@gmail.com</a>
            <a href="tel:+917200536353" style="display: block; margin-bottom: 12px;" data-site-phone>+91 72005 36353</a>
            <p data-site-address>Your address line here,<br>City, State – PIN.</p>
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
