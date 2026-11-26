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
      <div class="preloader-brand-name" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; margin-bottom: 12px;">
        <svg class="logo-svg-emblem" width="24" height="50" viewBox="0 0 147 307" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M59 41L42 53L32 63L20 80L11 98L5 115L2 133L1 134L1 140L0 141L0 176L1 177L1 184L2 185L6 207L13 227L24 249L32 262L45 279L67 302L42 268L28 243L28 241L24 234L21 224L19 221L15 208L14 200L11 190L11 185L10 184L10 176L9 175L9 146L10 145L10 137L11 136L11 131L13 126L14 118L19 102L32 75L45 57L60 42ZM72 40L71 64L70 65L70 89L69 90L69 208L70 209L70 243L71 244L71 277L72 278L72 299L71 300L60 273L50 240L50 236L48 231L45 211L44 210L44 202L43 201L43 193L42 192L42 177L41 176L41 150L42 149L42 135L43 134L45 113L53 81L68 41L65 44L57 57L49 73L44 86L44 89L42 92L36 118L35 132L34 133L33 174L34 175L34 186L35 187L37 209L45 241L61 283L72 304L72 306L74 306L77 301L79 302L95 286L111 266L121 251L133 227L135 219L139 210L144 189L144 184L145 183L145 176L146 175L146 142L145 141L143 124L139 112L139 109L127 82L119 70L109 58L96 47L86 41L101 57L110 69L120 86L120 88L125 97L133 122L135 138L136 139L137 173L136 174L135 190L134 191L131 208L120 239L106 265L90 288L78 302L77 300L95 259L103 235L104 227L107 219L109 202L111 195L111 187L112 186L113 149L112 148L111 126L110 125L109 114L104 93L94 67L86 52L78 41L81 51L89 69L99 104L101 119L102 120L102 127L103 128L103 137L104 138L105 173L104 174L103 202L102 203L102 210L101 211L98 231L94 244L94 248L89 262L89 265L87 268L85 277L76 299L75 298L75 259L76 258L76 211L77 210L76 209L77 208L77 101L76 100L75 54L74 53L74 43L73 40ZM71 0L67 2L68 15L69 16L68 37L78 37L77 35L78 2L75 0Z" fill="#D4AF37" fill-rule="evenodd" />
        </svg>
        <span class="logo-text"><span class="logo-rt" style="font-family:'Cormorant Garamond',serif;font-size:20px;color:#FFFFFF;letter-spacing:0.34em;margin-right:-0.34em;">RT CHOCOS</span></span>
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
  var navEntries = (performance && performance.getEntriesByType) ? performance.getEntriesByType('navigation') : [];
  var navType = navEntries.length > 0 ? navEntries[0].type : '';
  var isReload = (navType === 'reload') || (window.performance && window.performance.navigation && window.performance.navigation.type === 1);
  var hasUnwrappedSession = sessionStorage.getItem('rtchocos_unwrapped_session');
  var overlay = document.getElementById('cacao-preloader');

  // Trigger ONLY on first visit or manual browser reload, NOT internal page navigation!
  if (hasUnwrappedSession && !isReload) {
    if (overlay) overlay.style.display = 'none';
    return;
  }

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
    sessionStorage.setItem('rtchocos_unwrapped_session', 'true');

    if (typeof anime !== 'undefined') {
      // 1. Particle Radial Explosion from Seal Center
      var seal = document.getElementById('choc-seal-btn');
      var rect = seal ? seal.getBoundingClientRect() : { left: window.innerWidth / 2, top: window.innerHeight / 2, width: 0, height: 0 };
      var centerX = rect.left + rect.width / 2;
      var centerY = rect.top + rect.height / 2;

      for (var p = 0; p < 32; p++) {
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

        var angle = (p / 32) * 360;
        var distance = 160 + Math.random() * 220;
        var rad = (angle * Math.PI) / 180;

        anime({
          targets: dot,
          translateX: Math.cos(rad) * distance,
          translateY: Math.sin(rad) * distance,
          scale: [1.2, 0],
          opacity: [1, 0],
          duration: 1100 + Math.random() * 400,
          easing: 'easeOutExpo',
          complete: function(anim) {
            if (anim.animatables[0].target.parentNode) {
              anim.animatables[0].target.parentNode.removeChild(anim.animatables[0].target);
            }
          }
        });
      }

      // 2. Anime.js Luscious Foil Peeling Animation (1100ms)
      anime({
        targets: '#foil-top-flap',
        translateY: '-150%',
        rotateZ: -18,
        opacity: 0,
        duration: 1100,
        easing: 'cubicBezier(0.25, 1, 0.5, 1)'
      });

      anime({
        targets: '#foil-bottom-flap',
        translateY: '150%',
        rotateZ: 18,
        opacity: 0,
        duration: 1100,
        easing: 'cubicBezier(0.25, 1, 0.5, 1)'
      });

      anime({
        targets: '#choc-seal-btn',
        scale: [1, 1.35, 0],
        rotate: [0, 180],
        opacity: 0,
        duration: 650,
        easing: 'easeOutBack'
      });

      anime({
        targets: '.bar-tile',
        scale: [0.82, 1],
        rotateY: [90, 0],
        opacity: [0, 1],
        delay: anime.stagger(90, { start: 250 }),
        duration: 850,
        easing: 'easeOutCubic'
      });

      // 3. Smooth Preloader Overlay Fadeout
      anime({
        targets: '#cacao-preloader',
        opacity: [1, 0],
        duration: 1000,
        delay: 800,
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
        overlay.style.transition = 'opacity 0.8s ease';
        setTimeout(function() {
          if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }, 800);
      }
    }
  };

  // Auto-unwrap after 5.5s if not clicked so it NEVER freezes
  setTimeout(function() {
    if (!isUnwrapped) {
      window.perform3DUnwrapAnimation();
    }
  }, 5500);
})();
</script>
