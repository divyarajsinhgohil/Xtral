<?php
$pageTitle = 'Contact — X-Tral';
$pageDescription = 'Contact X-Tral for product enquiries, dealership and project requirements.';
include __DIR__ . '/includes/header.php';
?>

  <section class="page-banner">
    <div class="container">
      <span class="eyebrow">Get in touch</span>
      <h1>Let's talk</h1>
      <p>Product enquiries, dealership or project requirements — our team is here to help.</p>
    </div>
  </section>

  <section class="section">
    <div class="container contact-grid">
      <div class="contact-info" data-reveal>
        <span class="eyebrow">Reach us</span>
        <h2 style="font-size:clamp(1.8rem,4vw,2.4rem);margin-bottom:8px;">Contact details</h2>
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg></div>
          <div><h4>Phone</h4><p><a href="tel:+917200536353" data-site-phone>+91 72005 36353</a></p></div>
        </div>
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg></div>
          <div><h4>Email</h4><p><a href="mailto:xtralcare@gmail.com" data-site-email>xtralcare@gmail.com</a></p></div>
        </div>
        <div class="item">
          <div class="ic"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></div>
          <div><h4>Address</h4><p data-site-address>Your office address line,<br>City, State – PIN, India.</p></div>
        </div>
      </div>

      <div data-reveal>
        <div class="form-success" data-form-success>✓ Thank you! Your enquiry has been received. Our team will get back to you shortly.</div>
        <div class="form-error" data-form-error></div>
        <form data-contact-form>
          <div class="row">
            <div class="field">
              <label for="name">Full Name</label>
              <input id="name" name="name" type="text" placeholder="Your name" required>
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" pattern="[6-9][0-9]{9}" maxlength="10" placeholder="e.g. 9876543210" required>
            </div>
          </div>
          <div class="row">
            <div class="field">
              <label for="email">Email</label>
              <input id="email" name="email" type="email" placeholder="you@email.com" required>
            </div>
            <div class="field">
              <label for="subject">I'm interested in</label>
              <select id="subject" name="subject">
                <option>Product enquiry</option>
                <option>Becoming a dealer</option>
                <option>Project / bulk order</option>
                <option>Other</option>
              </select>
            </div>
          </div>
          <div class="field">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Tell us what you need…" required></textarea>
          </div>
          <button type="submit" class="btn btn--gold" data-submit-btn>Send Enquiry <span class="ar">→</span></button>
        </form>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
