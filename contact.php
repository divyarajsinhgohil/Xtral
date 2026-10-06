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
    <div class="container" style="max-width:760px;">
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
