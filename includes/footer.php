<!-- --- FOOTER --- -->
<footer id="site-footer" class="footer-links-section">
  <div class="footer-grid">
    <div>
      <a href="<?php echo $pathPrefix; ?>index.php" style="text-decoration:none;display:inline-block;margin-bottom:16px;">
        <span class="logo-rt" style="font-size: 28px;">RT</span><span class="logo-chocos" style="font-size: 28px;"> Chocos</span>
      </a>
      <p style="color:rgba(245,237,230,0.5);font-size:13px;line-height:1.7;font-weight:300;max-width:260px;">India's chocolate learning platform for makers, learners, and enthusiasts. Bean-to-bar science, craft, and education.</p>
    </div>
    <div>
      <div class="footer-heading">Navigation</div>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>index.php">Home</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>about.php">About</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>workshops.php">Workshops</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>contact.php">Contact</a>
    </div>
    <div>
      <div class="footer-heading">Explore</div>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>blog.php">Blog</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>chocopedia.php">Chocopedia</a>
      <a class="footer-link" href="<?php echo $pathPrefix; ?>gallery.php">Recipes</a>
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
      <!-- Option 1: Forest Green & Cream -->
      <div class="theme-option" onclick="selectTheme('')" data-theme-id="">
        <div class="theme-info">
          <span class="theme-name">1. Forest Green & Cream (Default)</span>
          <div class="theme-color-preview">
            <span style="background-color: #1a3d2c;"></span>
            <span style="background-color: #476652;"></span>
            <span style="background-color: #e5dec9;"></span>
            <span style="background-color: #f7f4eb;"></span>
          </div>
        </div>
      </div>
      <!-- Option 2: Teal Green & Sage -->
      <div class="theme-option" onclick="selectTheme('theme-teal-sage')" data-theme-id="theme-teal-sage">
        <div class="theme-info">
          <span class="theme-name">2. Teal Green & Sage</span>
          <div class="theme-color-preview">
            <span style="background-color: #173e35;"></span>
            <span style="background-color: #699684;"></span>
            <span style="background-color: #c1dcd2;"></span>
            <span style="background-color: #ebf1ee;"></span>
          </div>
        </div>
      </div>
      <!-- Option 3: Lavender & Mint Sage -->
      <div class="theme-option" onclick="selectTheme('theme-lavender-mint')" data-theme-id="theme-lavender-mint">
        <div class="theme-info">
          <span class="theme-name">3. Lavender & Mint Sage</span>
          <div class="theme-color-preview">
            <span style="background-color: #8d75af;"></span>
            <span style="background-color: #addabf;"></span>
            <span style="background-color: #e2ebd5;"></span>
            <span style="background-color: #faf7f0;"></span>
          </div>
        </div>
      </div>
      <!-- Option 4: Peach Orange & Mint -->
      <div class="theme-option" onclick="selectTheme('theme-peach-mint')" data-theme-id="theme-peach-mint">
        <div class="theme-info">
          <span class="theme-name">4. Peach Orange & Mint</span>
          <div class="theme-color-preview">
            <span style="background-color: #ebb06a;"></span>
            <span style="background-color: #addabf;"></span>
            <span style="background-color: #e2ebd5;"></span>
            <span style="background-color: #fbf4ea;"></span>
          </div>
        </div>
      </div>
      <!-- Option 5: Forest Green & Warm Ivory -->
      <div class="theme-option" onclick="selectTheme('theme-cream-forest')" data-theme-id="theme-cream-forest">
        <div class="theme-info">
          <span class="theme-name">5. Forest Green & Warm Ivory</span>
          <div class="theme-color-preview">
            <span style="background-color: #2b7a37;"></span>
            <span style="background-color: #e8e5cc;"></span>
            <span style="background-color: #FAF9F0;"></span>
            <span style="background-color: #094616;"></span>
          </div>
        </div>
      </div>
      <!-- Option 6: Original theme -->
      <div class="theme-option" onclick="selectTheme('theme-original')" data-theme-id="theme-original">
        <div class="theme-info">
          <span class="theme-name">6. Eucalyptus Sage (Original)</span>
          <div class="theme-color-preview">
            <span style="background-color: #124F27;"></span>
            <span style="background-color: #599A6E;"></span>
            <span style="background-color: #E2ECE0;"></span>
            <span style="background-color: #C2D0C0;"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.theme-tester {
  position: fixed;
  left: 20px;
  bottom: 20px;
  z-index: 99999;
  font-family: 'Inter', sans-serif;
}
.theme-tester-trigger {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(26, 61, 44, 0.85);
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 10px 16px;
  border-radius: 30px;
  cursor: pointer;
  font-size: 13.5px;
  font-weight: 500;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(8px);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.theme-tester-trigger:hover {
  background: rgba(26, 61, 44, 1);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}
.theme-tester-trigger svg {
  width: 16px;
  height: 16px;
}
.theme-tester-menu {
  display: none;
  position: absolute;
  bottom: 50px;
  left: 0;
  width: 280px;
  background: rgba(255, 255, 255, 0.95);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(10px);
  overflow: hidden;
  animation: themeSlideUp 0.3s ease-out;
}
.theme-tester-menu.open {
  display: block;
}
.theme-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  background: rgba(0, 0, 0, 0.02);
}
.theme-menu-header h4 {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #1a3d2c;
}
.theme-menu-close {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  color: #666;
  line-height: 1;
}
.theme-options {
  padding: 8px;
  max-height: 320px;
  overflow-y: auto;
}
.theme-option {
  padding: 8px 12px;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
  margin-bottom: 4px;
  border-left: 3px solid transparent;
}
.theme-option:hover {
  background: rgba(0, 0, 0, 0.04);
}
.theme-option.active {
  background: rgba(26, 61, 44, 0.06);
  border-left-color: #1a3d2c;
}
.theme-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.theme-name {
  font-size: 12.5px;
  font-weight: 500;
  color: #333;
}
.theme-color-preview {
  display: flex;
  gap: 4px;
}
.theme-color-preview span {
  display: inline-block;
  width: 24px;
  height: 10px;
  border-radius: 4px;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

@keyframes themeSlideUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .theme-tester {
    left: 12px;
    bottom: 12px;
  }
  .theme-tester-trigger {
    padding: 8px 12px;
    font-size: 12px;
  }
  .theme-tester-menu {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    border-radius: 16px 16px 0 0;
    border: none;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 -8px 32px rgba(0, 0, 0, 0.15);
    animation: themeSlideUpMobile 0.25s ease-out;
  }
}
@keyframes themeSlideUpMobile {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}
</style>

<script>
function toggleThemeMenu() {
  const menu = document.getElementById('theme-tester-menu');
  if (menu) {
    menu.classList.toggle('open');
  }
}

function selectTheme(themeClass) {
  // 1. Remove all existing theme classes from HTML element
  const themes = ['theme-teal-sage', 'theme-lavender-mint', 'theme-peach-mint', 'theme-cream-forest', 'theme-original'];
  themes.forEach(t => document.documentElement.classList.remove(t));

  // 2. Add the selected theme class if it's not the default
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
