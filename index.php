<?php
$pageTitle = 'X-Tral — Premium Bathware & Sanitary Ware';
$pageDescription = 'X-Tral crafts premium faucets, showers, sanitary ware and bath solutions engineered for modern living.';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/banner.php';
?>

  <!-- ============ TICKER ============ -->
  <div class="ticker">
    <div class="ticker-track">
      <span>Sanitary Ware</span><span>Bath Fittings</span><span>Kitchen Sinks</span><span>Wellness</span><span>PTMT Faucets</span>
      <span>Sanitary Ware</span><span>Bath Fittings</span><span>Kitchen Sinks</span><span>Wellness</span><span>PTMT Faucets</span>
    </div>
  </div>

  <!-- ============ NEW ARRIVALS / COLLECTIONS ============ -->
  <section class="categories" id="new-arrivals-section" style="background:var(--sand); border-bottom:1px solid rgba(60,120,127,.12); padding-top: 60px; padding-bottom: 60px; display:none;">
    <style>
      /* Spacing & size override block specifically for New Arrivals cards */
      #new-arrivals-section .cat-card {
        min-height: 420px; /* Adjust this to change the height of the cards */
      }
      #new-arrivals-section .cat-card img {
        min-height: 420px;
        object-fit: cover; /* Ensures images crop and scale cleanly */
      }
    </style>
    <div class="container">
      <div class="section-head" data-reveal>
        <div class="mini">New Arrivals</div>
        <h2>New Collections</h2>
        <p>Explore X-TRAL's latest collections, thoughtfully crafted for modern living spaces.</p>
      </div>

      <div class="cat-slider-container">
        <button class="cat-arrow cat-arrow--left" data-new-arrow-left aria-label="Previous collections">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>

        <div class="cat-grid" data-new-arrivals data-reveal>
          <!-- Dynamically populated via JS -->
        </div>

        <button class="cat-arrow cat-arrow--right" data-new-arrow-right aria-label="Next collections">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>
  </section>

  <!-- ============ CATEGORIES ============ -->
  <section class="categories" id="categories">
    <div class="container">
      <div class="section-head" data-reveal>
        <div class="mini">Design categories</div>
        <h2>Premium solutions for every space</h2>
        <p>Explore X-TRAL’s thoughtfully designed categories created for modern bathrooms, kitchens and lifestyle-focused interiors.</p>
      </div>

      <div class="cat-slider-container">
        <button class="cat-arrow cat-arrow--left" data-cat-arrow-left aria-label="Previous categories">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>

        <div class="cat-grid" data-categories data-reveal>
          <!-- Dynamically populated via admin categories -->
        </div>

        <button class="cat-arrow cat-arrow--right" data-cat-arrow-right aria-label="Next categories">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>
    </div>
  </section>

  <!-- ============ EDITORIAL / LIFESTYLE GALLERY ============ -->
  <section class="editorial">
    <div class="container editorial-wrap">
      <div class="editorial-copy" data-reveal>
        <div class="mini">Live beautifully</div>
        <h2>Bathware in its element.</h2>
        <p>Create interiors that feel calm, complete and purposeful. X-TRAL brings together contemporary forms, premium finishes and everyday practicality for modern living spaces.</p>
        <div class="stats">
          <div class="stat"><strong>05+</strong><span>Core categories</span></div>
          <div class="stat"><strong>500k</strong><span>Cycle-tested fittings</span></div>
          <div class="stat"><strong>24/7</strong><span>Daily performance</span></div>
        </div>
      </div>

      <div class="mosaic" data-reveal>
        <img class="tall" src="assets/img/wellness_premium.webp" alt="Premium basin setting" loading="lazy" decoding="async">
        <div class="mosaic-stack">
          <img src="assets/img/shower_luxury.webp?v=<?= filemtime(__DIR__ . '/assets/img/shower_luxury.webp') ?>" alt="Modern luxury shower" loading="lazy" decoding="async">
          <img src="assets/img/kitchen_sinks_premium.webp" alt="Kitchen sink" loading="lazy" decoding="async">
        </div>
      </div>
    </div>
  </section>

  <!-- ============ PRODUCT RANGES ============ -->
  <section class="range" id="range">
    <div class="container">
      <div class="section-head" data-reveal>
        <div class="mini">Product range</div>
        <h2>Designed for complete bath spaces</h2>
        <p>Premium fixtures crafted with durable materials and refined finishes to bring perfect utility and visual balance.</p>
      </div>

      <div class="range-grid" data-reveal>
        <article class="range-card">
          <div class="num">01</div>
          <h3>Sanitary Ware</h3>
          <p>Wall-hung closets, one-piece closets, basins and bathroom ceramics with clean modern styling.</p>
        </article>
        <article class="range-card">
          <div class="num">02</div>
          <h3>Bath Fittings</h3>
          <p>Faucets, mixers, pillar cocks and functional fittings with a premium chrome finish.</p>
        </article>
        <article class="range-card">
          <div class="num">03</div>
          <h3>Kitchen Sinks</h3>
          <p>Durable kitchen sink solutions for stylish and practical contemporary kitchens.</p>
        </article>
        <article class="range-card">
          <div class="num">04</div>
          <h3>Wellness</h3>
          <p>Comfort-led bath spaces built around relaxing routines and refined materials.</p>
        </article>
        <article class="range-card">
          <div class="num">05</div>
          <h3>PTMT Faucets</h3>
          <p>Lightweight, practical faucet options for everyday functional water use.</p>
        </article>
        <article class="range-card">
          <div class="num">06</div>
          <h3>Accessories</h3>
          <p>Thoughtful accessories that complete the bathroom with utility and visual balance.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ FEATURED PRODUCTS ============ -->
  <section class="range" style="background:var(--sand); border-top:1px solid rgba(60,120,127,.12);">
    <div class="container">
      <div class="section-head" data-reveal>
        <div class="mini">Signature Picks</div>
        <h2>Featured products</h2>
      </div>
      <div class="prod-grid" data-featured data-reveal></div>
      <div style="text-align:center; margin-top: 40px;" data-reveal>
        <a href="products" class="btn btn-primary">View all products</a>
      </div>
    </div>
  </section>

  <!-- ============ QUALITY ============ -->
  <section class="quality" id="why">
    <div class="container quality-inner">
      <div data-reveal>
        <div class="eyebrow">Why X-TRAL</div>
        <h2>Engineered for precision. Designed for life.</h2>
        <p>Every X-TRAL product is built around smart design, durable materials and refined finishes to deliver reliable performance year after year.</p>
      </div>
      <div class="feature-list" data-reveal>
        <article class="feature">
          <span>01</span>
          <h3>Quality</h3>
          <p>Premium materials tested for reliable everyday performance.</p>
        </article>
        <article class="feature">
          <span>02</span>
          <h3>Design</h3>
          <p>Minimal modern forms that elevate bathrooms and kitchens.</p>
        </article>
        <article class="feature">
          <span>03</span>
          <h3>Trust</h3>
          <p>Built for durability, support and long-term confidence.</p>
        </article>
      </div>
    </div>
  </section>

  <!-- ============ SUSTAINABILITY ============ -->
  <section class="sustain">
    <div class="container sustain-wrap">
      <div data-reveal>
        <div class="section-head" style="text-align:left; margin:0; max-width:520px;">
          <div class="mini">Sustainability</div>
          <h2>Every drop, engineered to matter.</h2>
          <p>Water efficiency is built into the X-TRAL experience, reducing consumption while keeping comfort, pressure and performance at the center.</p>
        </div>
        <div class="bullets">
          <div class="bullet"><i>&#10003;</i> Aerated Flow Technology</div>
          <div class="bullet"><i>&#10003;</i> Reduced Water Wastage</div>
          <div class="bullet"><i>&#10003;</i> Long-Lasting, Leak-Free Design</div>
        </div>
      </div>
      <div class="sustain-img" data-reveal>
        <img src="assets/img/shower_luxury.webp?v=<?= filemtime(__DIR__ . '/assets/img/shower_luxury.webp') ?>" alt="Water efficient luxury shower" loading="lazy" decoding="async">
      </div>
    </div>
  </section>

  <!-- ============ CTA ============ -->
  <section class="cta" id="contact">
    <div class="container">
      <div class="cta-box" data-reveal>
        <div>
          <h2>Get the complete X-TRAL catalogue</h2>
          <p>Browse categories, finishes, specifications and product details in one place. A premium catalogue experience for dealers, designers and modern projects.</p>
        </div>
        <div class="cta-actions">
          <a href="catalogue" class="btn btn-light">Download PDF</a>
          <a href="contact" class="btn btn-outline">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
