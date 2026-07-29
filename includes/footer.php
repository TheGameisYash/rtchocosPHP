<!-- --- FOOTER --- -->
<footer id="site-footer" class="footer-links-section">
  <div class="footer-grid">
    <div>
      <a href="<?php echo $pathPrefix ?: './'; ?>" class="logo" style="text-decoration:none;display:inline-flex;align-items:center;gap:12px;margin-bottom:16px;">
        <svg class="logo-svg-emblem" width="36" height="36" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="40" height="40" rx="10" fill="var(--accent-glow)" stroke="var(--border-accent)" stroke-width="1.2"/>
          <path d="M20 8C24 12.5 28 15.5 28 21.5C28 26 24.5 30 20 32C15.5 30 12 26 12 21.5C12 15.5 16 12.5 20 8Z" stroke="var(--accent)" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="20" cy="20" r="2" fill="var(--accent)"/>
        </svg>
        <span class="logo-text"><span class="footer-logo-rt" style="font-family:'DM Serif Display',serif;font-size:24px;color:var(--text-heading);font-weight:400;">RT</span><span class="footer-logo-chocos" style="font-family:'Inter',sans-serif;font-size:11px;font-weight:700;letter-spacing:0.25em;color:var(--accent);"> CHOCOS</span></span>
      </a>
      <p style="color:var(--text-secondary);font-size:13.5px;line-height:1.75;font-weight:400;max-width:260px;">India's chocolate learning platform for makers, learners, and enthusiasts. Bean-to-bar science, craft, and education.</p>
    </div>
    <div>
      <div class="footer-heading">Navigation</div>
      <a class="footer-link" href="<?php echo $pathPrefix ?: 'index.php'; ?>">Home</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>about.php">About Aarti Saluja Sahni</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>workshops.php" title="Chocolate Academy India">Chocolate Academy India</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>contact.php">Contact Us</a>
    </div>
    <div>
      <div class="footer-heading">Explore</div>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>blog.php" title="Chocolate Blog India">Chocolate Blog India</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>indian-chocolate-brands.php" title="Indian Chocolate Brands Directory">Indian Chocolate Brands</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>chocopedia.php">Chocopedia</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>gallery.php" title="Chocolate Recipes India">Recipes & Formulations</a>
    </div>
    <div>
      <?php if (empty($isHome)): ?>
        <div class="footer-heading">Newsletter</div>
        <p style="margin-bottom:12px; color: rgba(246,242,234,0.6); font-size:13px;">Recipes, science &amp; exclusive workshop updates.</p>
        <form class="footer-newsletter-row" id="newsletter-footer-form" novalidate>
          <input class="footer-newsletter-input" type="email" placeholder="Email" required />
          <button class="footer-newsletter-btn" type="submit">→</button>
        </form>
        <div id="newsletter-footer-feedback" style="margin-top: 10px; display: none; font-size: 13px; font-weight: 300; line-height: 1.5; color: rgba(246,242,234,0.85); animation: fadeIn 0.3s ease;"></div>
        <div class="footer-heading" style="margin-top: 24px;">Connect With Us</div>
      <?php else: ?>
        <div class="footer-heading">Connect With Us</div>
      <?php endif; ?>
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
  <div class="footer-bottom">
    <span>&copy; <?php echo date('Y'); ?> RT Chocos. All rights reserved.</span>
    <span>Crafted with ♥ from Mumbai</span>
  </div>
</footer>

<!-- Newsletter Popup -->
<div id="newsletter-popup">
  <div class="popup-inner" onclick="event.stopPropagation()">
    <button class="popup-close" onclick="closePopup()">×</button>
    <div class="section-label" style="margin-bottom:8px;">Join Our Community</div>
    <h3>The Chocolate Letter</h3>
    <p>Recipes, science, workshop announcements and exclusive offers — delivered to your inbox weekly.</p>
    <form class="popup-row" id="newsletter-popup-form" novalidate>
      <input class="popup-input" type="email" placeholder="Your email" required />
      <button class="btn-primary" type="submit" style="padding:12px 20px;">Subscribe</button>
    </form>
    <div id="newsletter-popup-feedback" style="margin-top: 15px; display: none; font-size: 14.5px; font-weight: 400; line-height: 1.5; color: var(--green-900); animation: fadeIn 0.3s ease;"></div>
  </div>
</div>

<?php if (get_site_setting('show_theme_tester', '0') === '1'): ?>
<!-- Theme Switcher Widget -->
<div id="theme-tester-widget" class="theme-tester">
  <button class="theme-tester-trigger" onclick="toggleThemeMenu()" aria-label="Toggle Color Palette Tester">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="palette-icon">
      <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 14.7255 3.09032 17.1962 4.85857 19C5.3442 19.4856 5.3442 20.2709 4.85857 20.7565L4.54289 21.0722C4.16786 21.4472 3.59374 21.4925 3.16709 21.1764C1.94236 20.269 1.1415 18.8475 1.02534 17.2458C0.843644 14.7401 1.77662 12.2155 3.52513 10.467C5.86828 8.12383 9.66728 8.12383 12.0104 10.467C14.3536 12.8102 14.3536 16.6092 12.0104 18.9523C11.1633 19.7994 10 20.4 9 21.4C8 22.4 9 22 12 22Z"/>
      <circle cx="7.5" cy="10.5" r="1.5" fill="currentColor"/>
      <circle cx="11.5" cy="7.5" r="1.5" fill="currentColor"/>
      <circle cx="16.5" cy="9.5" r="1.5" fill="currentColor"/>
      <circle cx="15.5" cy="14.5" r="1.5" fill="currentColor"/>
    </svg>
    <span>Theme Tester</span>
  </button>
  <div class="theme-tester-menu" id="theme-tester-menu">
    <div class="theme-menu-header">
      <h4>Select Color Palette</h4>
      <button class="theme-menu-close" onclick="toggleThemeMenu()">&times;</button>
    </div>
    <div class="theme-options">
      <!-- Option 1: Signature Roasted Cacao (Default) -->
      <div class="theme-option" onclick="selectTheme('theme-cream-forest')" data-theme-id="theme-cream-forest">
        <div class="theme-info">
          <span class="theme-name">1. Signature Roasted Cacao (Default)</span>
          <div class="theme-color-preview">
            <span style="background-color: #A8521D;"></span>
            <span style="background-color: #EEE6DA;"></span>
            <span style="background-color: #FAF6F0;"></span>
            <span style="background-color: #1F1209;"></span>
          </div>
        </div>
      </div>
      <!-- Option 2: Botanical Cacao &amp; Olive -->
      <div class="theme-option" onclick="selectTheme('theme-teal-sage')" data-theme-id="theme-teal-sage">
        <div class="theme-info">
          <span class="theme-name">2. Botanical Cacao &amp; Olive</span>
          <div class="theme-color-preview">
            <span style="background-color: #2B5838;"></span>
            <span style="background-color: #E2ECE2;"></span>
            <span style="background-color: #F4F7F4;"></span>
            <span style="background-color: #122116;"></span>
          </div>
        </div>
      </div>
      <!-- Option 3: Velvet Plum Cocoa -->
      <div class="theme-option" onclick="selectTheme('theme-lavender-mint')" data-theme-id="theme-lavender-mint">
        <div class="theme-info">
          <span class="theme-name">3. Velvet Plum Cocoa</span>
          <div class="theme-color-preview">
            <span style="background-color: #663A75;"></span>
            <span style="background-color: #E7DFEC;"></span>
            <span style="background-color: #F6F3F8;"></span>
            <span style="background-color: #1F0E29;"></span>
          </div>
        </div>
      </div>
      <!-- Option 4: Dark Chocolate Espresso -->
      <div class="theme-option" onclick="selectTheme('theme-peach-mint')" data-theme-id="theme-peach-mint">
        <div class="theme-info">
          <span class="theme-name">4. Dark Chocolate Espresso</span>
          <div class="theme-color-preview">
            <span style="background-color: #8B3E14;"></span>
            <span style="background-color: #ECE0D2;"></span>
            <span style="background-color: #F8F2EB;"></span>
            <span style="background-color: #1C0B03;"></span>
          </div>
        </div>
      </div>
      <!-- Option 5: Raw Cacao &amp; Pistachio -->
      <div class="theme-option" onclick="selectTheme('theme-original')" data-theme-id="theme-original">
        <div class="theme-info">
          <span class="theme-name">5. Raw Cacao &amp; Pistachio</span>
          <div class="theme-color-preview">
            <span style="background-color: #346142;"></span>
            <span style="background-color: #E0E9E0;"></span>
            <span style="background-color: #F3F6F3;"></span>
            <span style="background-color: #122417;"></span>
          </div>
        </div>
      </div>
      <!-- Option 6: Midnight Cacao &amp; Liquid Gold -->
      <div class="theme-option" onclick="selectTheme('theme-midnight-gold')" data-theme-id="theme-midnight-gold">
        <div class="theme-info">
          <span class="theme-name">6. Midnight Cacao &amp; Liquid Gold</span>
          <div class="theme-color-preview">
            <span style="background-color: #D4AF37;"></span>
            <span style="background-color: #171412;"></span>
            <span style="background-color: #0E0C0A;"></span>
            <span style="background-color: #F5ECE3;"></span>
          </div>
        </div>
      </div>
      <!-- Option 7: Vintage Ruby Cocoa -->
      <div class="theme-option" onclick="selectTheme('theme-royal-ruby')" data-theme-id="theme-royal-ruby">
        <div class="theme-info">
          <span class="theme-name">7. Vintage Ruby Cocoa</span>
          <div class="theme-color-preview">
            <span style="background-color: #8A2234;"></span>
            <span style="background-color: #ECE0E3;"></span>
            <span style="background-color: #F8F3F4;"></span>
            <span style="background-color: #21060B;"></span>
          </div>
        </div>
      </div>
      <!-- Option 8: Royal Sapphire Cacao -->
      <div class="theme-option" onclick="selectTheme('theme-sapphire-gold')" data-theme-id="theme-sapphire-gold">
        <div class="theme-info">
          <span class="theme-name">8. Royal Sapphire Cacao</span>
          <div class="theme-color-preview">
            <span style="background-color: #1C4170;"></span>
            <span style="background-color: #E0E8F4;"></span>
            <span style="background-color: #F3F6FA;"></span>
            <span style="background-color: #0A182E;"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function toggleThemeMenu() {
  const menu = document.getElementById('theme-tester-menu');
  if (menu) {
    menu.classList.toggle('open');
  }
}

function selectTheme(themeClass) {
  // 1. Remove all existing theme classes from HTML element
  const themes = [
    'theme-cream-forest',
    'theme-teal-sage',
    'theme-lavender-mint',
    'theme-peach-mint',
    'theme-original',
    'theme-midnight-gold',
    'theme-royal-ruby',
    'theme-sapphire-gold'
  ];
  themes.forEach(t => document.documentElement.classList.remove(t));

  // 2. Add the selected theme class
  if (themeClass) {
    document.documentElement.classList.add(themeClass);
    localStorage.setItem('rtchocos-color-theme', themeClass);
  } else {
    localStorage.removeItem('rtchocos-color-theme');
  }

  // 3. Update the active status in the tester menu UI
  document.querySelectorAll('.theme-option').forEach(opt => {
    if (opt.getAttribute('data-theme-id') === themeClass) {
      opt.classList.add('active');
    } else {
      opt.classList.remove('active');
    }
  });
}

// Set initial active state in the switcher UI on page load
document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('rtchocos-color-theme') || '';
  document.querySelectorAll('.theme-option').forEach(opt => {
    if (opt.getAttribute('data-theme-id') === savedTheme) {
      opt.classList.add('active');
    } else {
      opt.classList.remove('active');
    }
  });

  // Close menu if clicking outside
  document.addEventListener('click', (e) => {
    const widget = document.getElementById('theme-tester-widget');
    const menu = document.getElementById('theme-tester-menu');
    if (widget && !widget.contains(e.target) && menu && menu.classList.contains('open')) {
      menu.classList.remove('open');
    }
  });
});
</script>

<script src="<?php echo $pathPrefix; ?>script.js?v=<?php echo filemtime(__DIR__ . '/../script.js'); ?>"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-6W5XE5DRJG"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-6W5XE5DRJG');
</script>
</body>
</html>
