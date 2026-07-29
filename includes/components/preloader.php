<?php
// Cacao Preloader Overlay Component
$pathPrefix = isset($pathPrefix) ? $pathPrefix : '';
?>
<!-- 3D Interactive Chocolate Bar Unwrap Preloader Overlay -->
<div id="cacao-preloader" class="cacao-preloader-overlay" aria-label="Loading RT Chocos">
  <!-- Full-screen video background -->
  <video class="preloader-video-bg" id="preloader-video" autoplay muted loop playsinline preload="auto" src="<?php echo $pathPrefix; ?>assets/loading.mp4">
    <source src="<?php echo $pathPrefix; ?>assets/loading.mp4" type="video/mp4">
  </video>
  <div class="preloader-video-scrim"></div>
  <div class="preloader-ambient-glow"></div>

  <!-- Main Luxury Content Frame -->
  <div class="preloader-luxury-frame">
    <!-- Brand Crest -->
    <div class="preloader-brand">
      <div class="preloader-brand-name" style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 8px;">
        <svg class="logo-svg-emblem" width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="40" height="40" rx="10" fill="rgba(212,175,55,0.12)" stroke="rgba(212,175,55,0.3)" stroke-width="1.2"/>
          <path d="M20 8C24 12.5 28 15.5 28 21.5C28 26 24.5 30 20 32C15.5 30 12 26 12 21.5C12 15.5 16 12.5 20 8Z" stroke="#D4AF37" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="20" cy="20" r="2" fill="#D4AF37"/>
        </svg>
        <span class="logo-text"><span class="logo-rt" style="font-size:26px;color:#FFFFFF;">RT</span><span class="logo-chocos" style="font-size:13px;color:rgba(255,255,255,0.65);"> CHOCOS</span></span>
      </div>
      <div class="preloader-tagline" style="color: rgba(255,255,255,0.85);">Artisanal Cacao Science · Craft · Passion</div>
    </div>

    <!-- 3D Interactive Chocolate Bar Wrapper Stage -->
    <div class="unwrap-chocolate-stage" id="unwrap-stage" onclick="perform3DUnwrapAnimation()">
      <div class="chocolate-bar-wrapper" id="choc-wrapper-box">
        <!-- Top Foil Flap -->
        <div class="foil-half foil-top" id="foil-top-flap">
          <div class="foil-shine"></div>
        </div>

        <!-- Bottom Foil Flap -->
        <div class="foil-half foil-bottom" id="foil-bottom-flap">
          <div class="foil-shine"></div>
        </div>

        <!-- Inner Artisan Chocolate Bar (Revealed on Peeling) -->
        <div class="artisan-chocolate-bar" id="artisan-bar">
          <div class="bar-tile"><span>RT</span></div>
          <div class="bar-tile"><span>72%</span></div>
          <div class="bar-tile"><span>CACAO</span></div>
          <div class="bar-tile"><span>CRAFT</span></div>
        </div>

        <!-- Center Gold Seal Button -->
        <div class="chocolate-seal-button" id="choc-seal-btn">
          <div class="seal-content">
            <span class="seal-icon">✨ 🍫 ✨</span>
            <span class="seal-title">UNWRAP CHOCOLATE</span>
            <span class="seal-subtitle">Click to Peel &amp; Enter Website</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Skip / Quick Action -->
    <button class="preloader-enter-btn" id="preloader-enter-btn" onclick="perform3DUnwrapAnimation()">
      Unwrap &amp; Enter Website&nbsp;&nbsp;→
    </button>
  </div>
</div>

<script>
(function() {
  var overlay = document.getElementById('cacao-preloader');
  var isUnwrapped = false;

  // Trigger Video Autoplay
  var preVid = document.getElementById('preloader-video');
  if (preVid) {
    preVid.muted = true;
    var playPromise = preVid.play();
    if (playPromise && playPromise.catch) {
      playPromise.catch(function(err) {});
    }
  }

  // 3D Anime.js Chocolate Unwrap Physics Engine
  window.perform3DUnwrapAnimation = function() {
    if (isUnwrapped) return;
    isUnwrapped = true;

    if (typeof anime !== 'undefined') {
      // 1. Particle Radial Explosion from Seal Center
      var seal = document.getElementById('choc-seal-btn');
      var rect = seal ? seal.getBoundingClientRect() : { left: window.innerWidth / 2, top: window.innerHeight / 2, width: 0, height: 0 };
      var centerX = rect.left + rect.width / 2;
      var centerY = rect.top + rect.height / 2;

      for (var p = 0; p < 28; p++) {
        var dot = document.createElement('div');
        dot.style.position = 'fixed';
        dot.style.left = centerX + 'px';
        dot.style.top = centerY + 'px';
        dot.style.width = '8px';
        dot.style.height = '8px';
        dot.style.borderRadius = '50%';
        dot.style.background = p % 2 === 0 ? 'linear-gradient(135deg, #D4AF37, #F0D060)' : 'linear-gradient(135deg, #256139, #388250)';
        dot.style.zIndex = '9999999';
        dot.style.pointerEvents = 'none';
        document.body.appendChild(dot);

        var angle = (p / 28) * 360;
        var distance = 140 + Math.random() * 180;
        var rad = (angle * Math.PI) / 180;

        anime({
          targets: dot,
          translateX: Math.cos(rad) * distance,
          translateY: Math.sin(rad) * distance,
          scale: [1, 0],
          opacity: [1, 0],
          duration: 900 + Math.random() * 400,
          easing: 'easeOutExpo',
          complete: function(anim) {
            if (anim.animatables[0].target.parentNode) {
              anim.animatables[0].target.parentNode.removeChild(anim.animatables[0].target);
            }
          }
        });
      }

      // 2. Anime.js Foil Peeling Animation
      anime({
        targets: '#foil-top-flap',
        translateY: '-140%',
        rotateZ: -15,
        opacity: 0,
        duration: 800,
        easing: 'easeOutCubic'
      });

      anime({
        targets: '#foil-bottom-flap',
        translateY: '140%',
        rotateZ: 15,
        opacity: 0,
        duration: 800,
        easing: 'easeOutCubic'
      });

      anime({
        targets: '#choc-seal-btn',
        scale: [1, 1.3, 0],
        rotate: [0, 180],
        opacity: 0,
        duration: 550,
        easing: 'easeOutBack'
      });

      anime({
        targets: '.bar-tile',
        scale: [0.85, 1],
        rotateY: [90, 0],
        opacity: [0, 1],
        delay: anime.stagger(70, { start: 200 }),
        duration: 700,
        easing: 'easeOutCubic'
      });

      // 3. Smooth Preloader Overlay Fadeout
      anime({
        targets: '#cacao-preloader',
        opacity: [1, 0],
        duration: 900,
        delay: 600,
        easing: 'easeOutQuad',
        complete: function() {
          if (overlay && overlay.parentNode) {
            overlay.parentNode.removeChild(overlay);
          }
        }
      });
    } else {
      // Fallback if anime.js is not loaded
      if (overlay) {
        overlay.style.opacity = '0';
        overlay.style.transition = 'opacity 0.6s ease';
        setTimeout(function() {
          if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 600);
      }
    }
  };

  // Auto-unwrap after 3.5s if not clicked so it NEVER freezes
  setTimeout(function() {
    if (!isUnwrapped) {
      window.perform3DUnwrapAnimation();
    }
  }, 3500);
})();
</script>
