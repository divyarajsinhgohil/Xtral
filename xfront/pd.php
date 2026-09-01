<?php
$pageTitle = 'Product Details — X-Tral';
$pageDescription = 'X-Tral product details, specifications and model information.';
include __DIR__ . '/includes/header.php';
?>

  <div class="container">
    <div class="crumbs">
      <a href="index.php">Home</a><span class="sep">/</span>
      <a href="products.php">Products</a><span class="sep">/</span>
      <a data-pd-crumb href="products.php">Category</a><span class="sep">/</span>
      <span data-pd-name>Product</span>
    </div>
  </div>

  <section class="section" style="padding-top:20px;" data-product-detail>
    <div class="container">
      <div class="pd">
        <div class="pd-media-gallery">
          <div class="pd-media" data-pd-media-container>
            <button type="button" class="pd-media-arrow pd-media-arrow--prev" aria-label="Previous media" style="display: none;">
              <svg width="24" height="24" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <img data-pd-img src="" alt="Product">
            <div data-pd-video-container style="display:none; width: 100%; height: 100%;"></div>
            <button type="button" class="pd-media-arrow pd-media-arrow--next" aria-label="Next media" style="display: none;">
              <svg width="24" height="24" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </button>
          </div>
          <div class="pd-thumbs-wrapper">
            <button type="button" class="pd-thumbs-arrow pd-thumbs-arrow--prev" aria-label="Previous thumbnails" style="display: none;">
              <svg width="18" height="18" viewBox="0 0 24 24"><path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
            </button>
            <div class="pd-thumbs" data-pd-thumbs></div>
            <button type="button" class="pd-thumbs-arrow pd-thumbs-arrow--next" aria-label="Next thumbnails" style="display: none;">
              <svg width="18" height="18" viewBox="0 0 24 24"><path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>
            </button>
          </div>
        </div>
        <div>
          <span class="pd-cat" data-pd-cat>Category</span>
          <h1 data-pd-name>Product Name</h1>
          <p class="code" data-pd-code>Model</p>
          <div class="pd-dimensions" data-pd-dimensions style="display: none; margin-top: -12px; margin-bottom: 24px; color: var(--muted); font-size: 0.9rem; font-weight: 500;"></div>
          <div class="price" data-pd-price>On request</div>

          <div class="pd-variants" data-pd-variants></div>

          <div class="pd-desc-block" data-pd-desc-block hidden>
            <span class="pd-desc-label">Description</span>
            <div class="pd-short-desc" data-pd-short-desc title="Click to read the full description"></div>
            <button type="button" class="pd-desc-toggle" data-pd-desc-toggle hidden>Read more</button>
          </div>

          <div class="features-wrap" data-pd-features-wrap style="display:none">
            <p class="features-heading">Features</p>
            <div class="features" data-pd-features></div>
          </div>

          <div class="pd-actions" style="margin-top: 30px;">
            <a href="contact.php" class="btn btn--dark">Enquire Now <span class="ar">→</span></a>
            <a href="catalogue.php" class="btn btn--outline">Download Catalogue</a>
          </div>

        </div>
      </div>
    </div>
  </section>

  <section class="section section--tight" style="background:var(--paper-2);">
    <div class="container">
      <div class="sec-head" data-reveal>
        <span class="eyebrow">You may also like</span>
        <h2>Related products</h2>
      </div>
      <div class="prod-grid" data-pd-related></div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
