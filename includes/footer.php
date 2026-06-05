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
        <a class="social-icon" href="https://www.instagram.com/rt.chocos/?hl=en" target="_blank" rel="noopener" aria-label="Instagram">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
          </svg>
        </a>
        <a class="social-icon" href="https://www.youtube.com/@RTCHOCOS" target="_blank" rel="noopener" aria-label="YouTube">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
          </svg>
        </a>
        <a class="social-icon" href="https://www.linkedin.com/in/aarti-saluja-sahni-8304637/" target="_blank" rel="noopener" aria-label="LinkedIn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
            <rect x="2" y="9" width="4" height="12"></rect>
            <circle cx="4" cy="4" r="2"></circle>
          </svg>
        </a>
        <a class="social-icon" href="https://www.facebook.com/rtchocos" target="_blank" rel="noopener" aria-label="Facebook">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
          </svg>
        </a>
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
      <button class="btn-primary" style="padding:12px 20px;" onclick="closePopup()">Subscribe</button>
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
