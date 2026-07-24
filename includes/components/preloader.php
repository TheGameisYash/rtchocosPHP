<?php
// Cacao Preloader Overlay Component
$pathPrefix = isset($pathPrefix) ? $pathPrefix : '';
?>
<!-- Ultra-Luxury Interactive Cacao Preloader Overlay -->
<div id="cacao-preloader" class="cacao-preloader-overlay" aria-label="Loading RT Chocos">
  <!-- Full-screen video background -->
  <video class="preloader-video-bg" id="preloader-video" autoplay muted loop playsinline preload="auto" src="<?php echo $pathPrefix; ?>assets/loading.mp4">
    <source src="<?php echo $pathPrefix; ?>assets/loading.mp4" type="video/mp4">
  </video>
  <div class="preloader-video-scrim"></div>
  <div class="preloader-ambient-glow"></div>
  <div class="preloader-particles" id="preloader-particles"></div>

  <!-- Main Luxury Content Frame -->
  <div class="preloader-luxury-frame">
    <!-- Brand Crest -->
    <div class="preloader-brand">
      <div class="preloader-brand-name">
        <span class="logo-rt">RT</span><span class="logo-chocos"> CHOCOS</span>
      </div>
      <div class="preloader-tagline">Artisanal Cacao Science · Craft · Passion</div>
    </div>

    <!-- Artisanal Phase Timeline -->
    <div class="preloader-phase-timeline">
      <div class="phase-step active" id="phase-step-1">
        <div class="phase-icon">🌿</div>
        <span class="phase-name">FERMENT</span>
      </div>
      <div class="phase-line" id="phase-line-1"></div>
      <div class="phase-step" id="phase-step-2">
        <div class="phase-icon">🔥</div>
        <span class="phase-name">ROAST</span>
      </div>
      <div class="phase-line" id="phase-line-2"></div>
      <div class="phase-step" id="phase-step-3">
        <div class="phase-icon">⚙️</div>
        <span class="phase-name">CONCH</span>
      </div>
      <div class="phase-line" id="phase-line-3"></div>
      <div class="phase-step" id="phase-step-4">
        <div class="phase-icon">💎</div>
        <span class="phase-name">TEMPER</span>
      </div>
    </div>

    <!-- Center Progress Ring & Emblem -->
    <div class="preloader-meter-wrapper" id="preloader-meter" onclick="triggerCacaoEmblemPulse(event)">
      <svg class="preloader-meter-svg" viewBox="0 0 120 120">
        <defs>
          <linearGradient id="themeGradient" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:#8AA895"/>
            <stop offset="50%" style="stop-color:#4A6B58"/>
            <stop offset="100%" style="stop-color:#233D2F"/>
          </linearGradient>
        </defs>
        <circle class="meter-track" cx="60" cy="60" r="52"/>
        <circle class="meter-dashed-accent" cx="60" cy="60" r="46"/>
        <circle class="meter-fill" id="meter-fill" cx="60" cy="60" r="52"/>
      </svg>
      <div class="preloader-meter-center">
        <div class="meter-icon-emoji" id="meter-icon-emoji">🍫</div>
        <div class="meter-percent-display" id="meter-percent-display">0%</div>
      </div>
    </div>

    <!-- Status Headline -->
    <div class="preloader-status-headline" id="preloader-status-headline">
      Selecting Single-Origin Cacao Beans...
    </div>

    <!-- Interactive Fact Card -->
    <div class="preloader-fact-container" id="preloader-fact-container" onclick="nextCacaoFact()">
      <div class="fact-card-header">
        <span class="fact-category-tag" id="fact-category-tag">🔬 CRYSTAL PHYSICS</span>
        <span class="fact-counter" id="fact-counter">1 / 8</span>
      </div>
      <p class="fact-body-text" id="fact-body-text">Loading cacao science...</p>
      <div class="fact-tap-footer">
        <span class="tap-icon">🔄</span> Tap card to reveal next cacao secret
      </div>
    </div>

    <!-- Skip Action -->
    <button class="preloader-enter-btn" id="preloader-enter-btn" onclick="dismissCacaoPreloader()">
      Explore Collection&nbsp;&nbsp;→
    </button>
  </div>
</div>

<script>
(function() {
  var navEntries = (performance && performance.getEntriesByType) ? performance.getEntriesByType('navigation') : [];
  var navType = navEntries.length > 0 ? navEntries[0].type : '';
  var isReload = (navType === 'reload') || (window.performance && window.performance.navigation && window.performance.navigation.type === 1);
  var isFirstVisit = !sessionStorage.getItem('rtchocos_visited');
  var overlay = document.getElementById('cacao-preloader');

  if (!isFirstVisit && !isReload) {
    if (overlay) overlay.style.display = 'none';
    return;
  }
  sessionStorage.setItem('rtchocos_visited', 'true');

  // Trigger Video Autoplay Explicitly for Browser Compatibility
  var preVid = document.getElementById('preloader-video');
  if (preVid) {
    preVid.muted = true;
    var playPromise = preVid.play();
    if (playPromise && playPromise.catch) {
      playPromise.catch(function(err) { console.log('Preloader video notice:', err); });
    }
  }

  // Spawn Golden Sparkle Particles
  var pc = document.getElementById('preloader-particles');
  if (pc) {
    for (var i = 0; i < 30; i++) {
      var d = document.createElement('div');
      d.className = 'preloader-particle';
      d.style.left = (Math.random() * 100) + '%';
      d.style.width = (2 + Math.random() * 4) + 'px';
      d.style.height = d.style.width;
      d.style.animationDuration = (6 + Math.random() * 9) + 's';
      d.style.animationDelay = (Math.random() * 6) + 's';
      pc.appendChild(d);
    }
  }

  // Curated Luxury Cacao Science Trivia
  var cacaoFacts = [
    { cat: "🔬 CRYSTAL PHYSICS", text: "Form V cocoa butter crystals produce that signature mirror gloss shine and crisp, acoustic snap in high-end chocolate." },
    { cat: "🌿 FERMENTATION", text: "Post-harvest fermentation unlocks over 600 complex aromatic volatile compounds inside raw cacao beans." },
    { cat: "🌡️ TEMPERING CURVE", text: "Master tempering requires raising dark chocolate to 45°C, cooling to 27°C, and working precisely at 31°C." },
    { cat: "⚙️ CONCHING ART", text: "Conching was invented in 1879 by Rodolphe Lindt — micro-refining particles under 20 microns for velvet smoothness." },
    { cat: "🍫 PURITY SCIENCE", text: "Pure dark chocolate with 70%+ cocoa is dense with natural flavanols, polyphenols, and essential antioxidants." },
    { cat: "🌎 EQUATORIAL BELT", text: "Theobroma Cacao trees grow exclusively within 20 degrees North and South of the Equator." },
    { cat: "✨ MELT CHEMISTRY", text: "Pure cocoa butter melts precisely at human body temperature (34°C–37°C), giving fine chocolate its legendary melt." },
    { cat: "📜 CACAO HERITAGE", text: "The word 'chocolate' stems from the ancient Aztec word 'xocolātl', revered as the nectar of the gods." }
  ];

  var factIdx = Math.floor(Math.random() * cacaoFacts.length);
  var progress = 0;
  var isDismissed = false;
  var CIRC = 2 * Math.PI * 52; // ~326.7

  function setProgress(pct) {
    var meter = document.getElementById('meter-fill');
    if (meter) meter.style.strokeDashoffset = CIRC - (CIRC * pct / 100);
    var pDisp = document.getElementById('meter-percent-display');
    if (pDisp) pDisp.textContent = Math.round(pct) + '%';

    // Update Phase Timeline Steps
    var step1 = document.getElementById('phase-step-1');
    var step2 = document.getElementById('phase-step-2');
    var step3 = document.getElementById('phase-step-3');
    var step4 = document.getElementById('phase-step-4');

    var line1 = document.getElementById('phase-line-1');
    var line2 = document.getElementById('phase-line-2');
    var line3 = document.getElementById('phase-line-3');

    if (pct >= 25) { if (step2) step2.classList.add('active'); if (line1) line1.classList.add('active'); }
    if (pct >= 55) { if (step3) step3.classList.add('active'); if (line2) line2.classList.add('active'); }
    if (pct >= 85) { if (step4) step4.classList.add('active'); if (line3) line3.classList.add('active'); }

    // Update Headline Status
    var head = document.getElementById('preloader-status-headline');
    if (head) {
      if (pct < 25) head.textContent = "Selecting Single-Origin Cacao Beans...";
      else if (pct < 55) head.textContent = "Roasting & Winnowing Pure Nibs...";
      else if (pct < 85) head.textContent = "Conching Cocoa Liquor to Velvet Smoothness...";
      else if (pct < 100) head.textContent = "Forming Beta Form V Crystal Network...";
      else head.textContent = "Perfectly Tempered & Ready ✓";
    }
  }

  function showFact(item) {
    var tag = document.getElementById('fact-category-tag');
    var body = document.getElementById('fact-body-text');
    var counter = document.getElementById('fact-counter');

    if (body) {
      body.classList.add('animating');
      setTimeout(function() {
        if (tag) tag.textContent = item.cat;
        body.textContent = item.text;
        if (counter) counter.textContent = (factIdx + 1) + ' / ' + cacaoFacts.length;
        body.classList.remove('animating');
      }, 200);
    }
  }

  window.nextCacaoFact = function() {
    factIdx = (factIdx + 1) % cacaoFacts.length;
    showFact(cacaoFacts[factIdx]);
  };

  window.triggerCacaoEmblemPulse = function(e) {
    var icon = document.getElementById('meter-icon-emoji');
    var emojis = ['🍫','🧪','🌱','✨','☕','👩‍🍳','🫘','💎'];
    if (icon) {
      icon.textContent = emojis[Math.floor(Math.random() * emojis.length)];
      icon.style.transform = 'scale(1.35) rotate(15deg)';
      setTimeout(function() { icon.style.transform = ''; }, 350);
    }

    // Spawn Burst Sparkles around click
    if (e && e.clientX) {
      for (var k = 0; k < 6; k++) {
        var sp = document.createElement('div');
        sp.className = 'click-sparkle';
        sp.textContent = '✨';
        sp.style.left = (e.clientX + (Math.random() * 40 - 20)) + 'px';
        sp.style.top = (e.clientY + (Math.random() * 40 - 20)) + 'px';
        document.body.appendChild(sp);
        (function(el) {
          setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 800);
        })(sp);
      }
    }
    window.nextCacaoFact();
  };

  window.dismissCacaoPreloader = function() {
    if (isDismissed) return;
    isDismissed = true;
    if (overlay) {
      overlay.classList.add('fade-out');
      setTimeout(function() {
        if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
      }, 1000);
    }
  };

  document.addEventListener('DOMContentLoaded', function() {
    showFact(cacaoFacts[factIdx]);

    // Auto-cycle trivia every 3.2s
    var factTimer = setInterval(function() {
      if (isDismissed) { clearInterval(factTimer); return; }
      window.nextCacaoFact();
    }, 3200);

    // Guaranteed Luxury Duration: minimum 6.5 seconds display time
    var MIN_DURATION_MS = 6500;
    var startTime = Date.now();
    var pageLoaded = false;

    window.addEventListener('load', function() {
      pageLoaded = true;
    });

    var timer = setInterval(function() {
      if (isDismissed) { clearInterval(timer); return; }
      var elapsed = Date.now() - startTime;
      var targetPct = (elapsed / MIN_DURATION_MS) * 100;

      if (targetPct >= 100) {
        // If minimum time passed and page loaded, finish up
        if (pageLoaded || elapsed > 8000) {
          clearInterval(timer);
          clearInterval(factTimer);
          setProgress(100);
          setTimeout(function() {
            window.dismissCacaoPreloader();
          }, 700);
        } else {
          setProgress(98);
        }
      } else {
        setProgress(targetPct);
      }
    }, 50);

    // Hard safety cap (10 seconds)
    setTimeout(function() { window.dismissCacaoPreloader(); }, 10000);
  });
})();
</script>
