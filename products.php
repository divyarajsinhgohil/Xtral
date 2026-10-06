<?php
$pageTitle = 'Products — X-Tral';
$pageDescription = 'Explore the complete X-Tral range of premium bathware, faucets and sanitaryware.';
include __DIR__ . '/includes/header.php';
?>

  <section class="page-banner">
    <div class="container">
      <span class="eyebrow" data-cat-eyebrow>Our Products</span>
      <h1 data-cat-title>Our Products</h1>
      <p data-cat-sub>Browse the complete X-Tral range across every category.</p>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <div class="toolbar">
        <button id="toggle-filter-btn" class="btn-filter-toggle">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
          Filter &amp; Sort
        </button>
        <div class="search-box">
          <input type="text" id="catalog-search" placeholder="Search by name, code, series..." aria-label="Search products">
          <span class="search-icon" aria-hidden="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          </span>
        </div>
      </div>

      <div class="filter-drawer" id="filter-drawer" style="display: none; margin-bottom: 40px;">
        <div class="filter-drawer-grid">
          <div class="filter-group" id="filter-group-category">
            <span class="filter-label">Category</span>
            <div class="custom-select" id="select-category">
              <div class="custom-select-trigger">
                <span data-selected-label>All Categories</span>
                <span class="arrow">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </span>
              </div>
              <div class="custom-options" data-options-container>
                <div class="custom-option selected" data-value="all">All Categories</div>
              </div>
            </div>
          </div>
          <div class="filter-group" id="filter-group-series">
            <span class="filter-label">Series</span>
            <div class="custom-select" id="select-series">
              <div class="custom-select-trigger">
                <span data-selected-label>All Series</span>
                <span class="arrow">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </span>
              </div>
              <div class="custom-options" data-options-container>
                <div class="custom-option selected" data-value="all">All Series</div>
              </div>
            </div>
          </div>
          <div class="filter-group">
            <span class="filter-label">Price Range (₹)</span>
            <div class="price-inputs">
              <input type="number" id="filter-price-min" placeholder="Min" class="filter-input" min="0">
              <span class="price-sep">to</span>
              <input type="number" id="filter-price-max" placeholder="Max" class="filter-input" min="0">
            </div>
          </div>
          <div class="filter-group">
            <span class="filter-label">Sort By</span>
            <div class="custom-select" id="select-sort">
              <div class="custom-select-trigger">
                <span data-selected-label>Default / Featured</span>
                <span class="arrow">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </span>
              </div>
              <div class="custom-options">
                <div class="custom-option selected" data-value="default">Default / Featured</div>
                <div class="custom-option" data-value="price-low">Price: Low to High</div>
                <div class="custom-option" data-value="price-high">Price: High to Low</div>
                <div class="custom-option" data-value="newest">Newest Arrivals</div>
              </div>
            </div>
          </div>
        </div>
        <div class="filter-drawer-actions">
          <span class="match-count" data-match-count></span>
          <button id="filter-reset" class="btn-filter-reset">Reset</button>
          <button id="filter-apply" class="btn-filter-apply">Done</button>
        </div>
      </div>

      <div class="prod-grid" data-product-grid></div>
      <div class="pagination" data-pagination></div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
