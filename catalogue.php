<?php
$pageTitle = 'Catalogue — X-Tral';
$pageDescription = 'View and download the X-Tral product catalogue.';

include __DIR__ . '/includes/header.php';
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
      <div class="cat-cards-grid" data-catalogue-list>
        <p class="cat-cards-loading">Loading catalogues…</p>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
