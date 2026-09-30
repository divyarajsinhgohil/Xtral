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

  <section class="section section--tight" style="background:var(--paper-2);">
    <div class="container">
      <div class="sec-head center" data-reveal>
        <span class="eyebrow">Registered details</span>
        <h2>Get in touch with us directly</h2>
      </div>
      <div class="contact-info" data-reveal style="max-width:640px;margin:0 auto;">
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
          <div><h4>Phone</h4><p><a href="tel:+917200536353">+91 72005 36353</a></p></div>
        </div>
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></div>
          <div><h4>Email</h4><p><a href="mailto:xtralcare@gmail.com">xtralcare@gmail.com</a></p></div>
        </div>
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
          <div><h4>Address</h4><p>Your office address line,<br>City, State – PIN, India.</p></div>
        </div>
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
          <a href="catalogue.php" class="btn btn--gold">View Catalogue <span class="ar">→</span></a>
          <a href="contact.php" class="btn btn--ghost">Contact Us</a>
        </div>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
