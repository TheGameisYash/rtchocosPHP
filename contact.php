<?php
  $pageTitle = "Contact | RT Chocos — India's Chocolate Blog";
  $pageDescription = "Get in touch with RT Chocos for recipe development, consulting, and training.";
  $pathPrefix = "";
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-contact" class="page active" style="padding-top:72px;">
  <div class="page-hero contact-page-hero">
    <div class="page-hero-content">
      <h1 class="fade-up">Get in Touch</h1>
      <p class="fade-up-d1">Questions about workshops, wholesale enquiries, or just want to talk chocolate? We'd love to hear from you.</p>
    </div>
  </div>
  <div class="section">
    <div class="contact-grid">
      <div>
        <div class="section-label">Reach Out</div>
        <h2 style="font-size:32px;margin-bottom:16px;">Contact Details</h2>
        <div class="divider" style="margin-bottom:24px;"></div>
        <div class="contact-item"><div><div class="contact-label">Location</div><div class="contact-value">Mumbai, Maharashtra, India</div></div></div>
        <div class="contact-item"><div><div class="contact-label">Email</div><div class="contact-value">hello@rtchocos.com</div></div></div>
        <div class="contact-item"><div><div class="contact-label">WhatsApp</div><div class="contact-value">+91 9140238741</div></div></div>
        <div class="social-pills">
          <button class="social-pill" onclick="window.open('https://www.instagram.com/rt.chocos/?hl=en', '_blank', 'noopener')" aria-label="Instagram" style="padding:6px;border:none;"><img src="assets/instalogo.png" alt="Instagram" style="width:90px;height:66px;display:block;" /></button>
          <button class="social-pill" onclick="window.open('https://www.youtube.com/@RTCHOCOS', '_blank', 'noopener')" aria-label="YouTube" style="padding:6px;border:none;"><img src="assets/youtubelogo.webp" alt="YouTube" style="width:56px;height:56px;display:block;" /></button>
          <button class="social-pill" onclick="window.open('https://www.linkedin.com/in/aarti-saluja-sahni-8304637/', '_blank', 'noopener')">LinkedIn</button>
          <button class="social-pill" onclick="window.open('https://www.facebook.com/rtchocos/', '_blank', 'noopener')">Facebook</button>
        </div>
      </div>
      <div class="contact-form">
        <h3>Send Us a Message</h3>
        <div class="form-group"><label class="form-label">Name</label><input class="form-input" type="text" /></div>
        <div class="form-group"><label class="form-label">Email</label><input class="form-input" type="email" /></div>
        <div class="form-group"><label class="form-label">Phone</label><input class="form-input" type="tel" /></div>
        <div class="form-group"><label class="form-label">Subject</label><input class="form-input" type="text" /></div>
        <div class="form-group"><label class="form-label">Message</label><textarea class="form-input form-textarea" rows="4"></textarea></div>
        <button class="btn-primary" style="width:100%;justify-content:center;">Send Message</button>
      </div>
    </div>
  </div>
</div>


<?php
  include $pathPrefix . 'includes/footer.php';
?>
