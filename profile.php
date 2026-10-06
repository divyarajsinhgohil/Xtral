<?php
$pageTitle = 'Company Profile — X-Tral';
$pageDescription = 'X-Tral company profile — manufacturing capability, product range and quality commitments.';
include __DIR__ . '/includes/header.php';
?>

  <section class="page-banner">
    <div class="container">
      <span class="eyebrow">Company Profile</span>
      <h1>Who we are</h1>
      <p>X-Tral is a bathware manufacturer engineering premium faucets, showers, sanitary ware and kitchen sinks for modern homes and projects.</p>
    </div>
  </section>

  <section class="section">
    <div class="container split">
      <div class="visual" data-reveal><img src="assets/img/about-hero-vanity.jpg" alt="Double basin vanity with backlit mirrors in a modern teal bathroom"></div>
      <div data-reveal>
        <span class="eyebrow">Manufacturing capability</span>
        <h2 style="font-size:clamp(2rem,4vw,2.8rem);">Built to a single standard</h2>
        <p class="muted" style="font-size:1.05rem;margin:18px 0 16px;">X-Tral designs and manufactures a complete range of bathware — faucets, showers, sanitary ware, wash basins, kitchen sinks and allied fittings — engineered for durability and finished for modern interiors.</p>
        <p class="muted">Every product moves through the same quality discipline before it reaches a dealer or project site: precision tooling, cycle-tested cartridges and fittings, and a finish built to resist daily wear.</p>

        <h3 style="font-family:var(--sans);font-size:0.95rem;letter-spacing:0.06em;text-transform:uppercase;color:var(--teal-900);margin-top:28px;">What we bring to every order</h3>
        <ul class="why-list">
          <li>Full in-house product range across 7+ categories</li>
          <li>Cycle-tested cartridges and fittings</li>
          <li>Consistent finish and quality control</li>
          <li>Support for dealers, projects and bulk orders</li>
        </ul>
      </div>
    </div>
  </section>

  <section class="stat-band">
    <div class="container">
      <div class="hero-stats" style="justify-content:center;gap:64px;flex-wrap:wrap;border-top:none;margin-top:0;padding-top:0;">
        <div class="center"><div class="num">7+</div><div class="lbl">Product Categories</div></div>
        <div class="center"><div class="num">200K</div><div class="lbl">Cycle Tested Fittings</div></div>
        <div class="center"><div class="num">100%</div><div class="lbl">Made in India</div></div>
      </div>
    </div>
  </section>


  <section class="section section--tight">
    <div class="container">
      <div class="cta-band" data-reveal>
        <div>
          <h2>Want the full product range?</h2>
          <p>Download our catalogue or speak to our team about dealership and project requirements.</p>
        </div>
        <div style="display:flex;gap:14px;flex-wrap:wrap;justify-content:flex-end;">
          <a href="catalogue" class="btn btn--gold">View Catalogue <span class="ar">→</span></a>
          <a href="contact" class="btn btn--ghost">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
