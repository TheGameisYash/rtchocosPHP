<!-- --- FOOTER --- -->
<footer id="site-footer" class="footer-links-section">
  <div class="footer-grid">
    <div>
      <div class="footer-heading" style="color: var(--cream);">Quick Links</div>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>index.php">Home</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>about.php">About</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
    </div>
    <div>
      <div class="footer-heading" style="color: var(--cream);">Workshops</div>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>workshops.php">Beginner Workshops</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>workshops.php">Advanced Workshops</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>workshops.php">Kids Workshops</a>
      <a class="footer-link" style="color: var(--cream);" href="<?php echo $pathPrefix; ?>workshops.php">Professional Workshops</a>
      <button class="footer-link" style="color: var(--cream);" onclick="navigate('workshops')">Advanced Workshops</button>
      <button class="footer-link" style="color: var(--cream);" onclick="navigate('workshops')">Kids Workshops</button>
      <button class="footer-link" style="color: var(--cream);" onclick="navigate('workshops')">Professional Workshops</button>
    </div>
    <div>
      <div class="footer-heading" style="color: var(--cream);">Newsletter</div>
      <p style="margin-bottom:12px; color: var(--cream);">Recipes, science &amp; exclusive workshop updates.</p>
      <div class="footer-newsletter-row">
        <input class="footer-newsletter-input" type="email" placeholder="Email" />
        <button class="footer-newsletter-btn">→</button>
      </div>
      <div class="social-icons">
        <a class="social-icon" href="https://www.instagram.com/rt.chocos/?hl=en" target="_blank" rel="noopener" aria-label="Instagram" style="overflow:visible;"><img src="<?php echo $pathPrefix; ?>assets/instalogo.png" alt="Instagram" style="width:90px;height:66px;display:block;" /></a>
        <a class="social-icon" href="https://www.youtube.com/@RTCHOCOS" target="_blank" rel="noopener" aria-label="YouTube"><img src="<?php echo $pathPrefix; ?>assets/youtubelogo.webp" alt="YouTube" style="width:56px;height:56px;display:block;" /></a>
        <a class="social-icon" href="https://www.linkedin.com/in/aarti-saluja-sahni-8304637/" target="_blank" rel="noopener" aria-label="LinkedIn"><img src="<?php echo $pathPrefix; ?>assets/linkedinlogo.webp" alt="LinkedIn" style="width:70px;height:70px;display:block;" /></a>
        <a class="social-icon" href="https://www.facebook.com/rtchocos" target="_blank" rel="noopener" aria-label="Facebook"><img src="<?php echo $pathPrefix; ?>assets/facebooklogo.webp" alt="Facebook" style="width:70px;height:70px;display:block;" /></a>
      </div>
    </div>
  </div>
</footer>

<!-- WhatsApp -->
<button class="whatsapp-float" title="Chat with us on WhatsApp">&#128172;</button>

<!-- Newsletter Popup -->
<div id="newsletter-popup">
  <div class="popup-inner" onclick="event.stopPropagation()">
    <button class="popup-close" onclick="closePopup()">×</button>
    <div class="section-label" style="margin-bottom:8px;">Join Our Community</div>
    <h3>The Chocolate Letter</h3>
    <p>Recipes, science, workshop announcements and exclusive offers — delivered to your inbox weekly.</p>
    <div class="popup-row">
      <input class="popup-input" type="email" placeholder="Your email" />
      <button class="btn-primary" style="padding:12px 20px;">Subscribe</button>
    </div>
  </div>
</div>

<script src="<?php echo $pathPrefix; ?>script.js"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6W5XE5DRJG"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-6W5XE5DRJG');
</script>
</body>
</html>
