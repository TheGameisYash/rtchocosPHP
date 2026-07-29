<?php
  $pageTitle = "Chocolate Blog India & Chocolate Academy | RT Chocos Bean-to-Bar Learning";
  $pageDescription = "RT Chocos is the premier chocolate blog in India and professional bean-to-bar chocolate academy. Discover Indian bean-to-bar chocolate making, tempering science, workshops, and courses by expert Aarti Saluja Sahni.";
  $pageKeywords = "chocolate blog india, chocolate academy india, indian bean to bar chocolate, bean to bar chocolate, chocolate course india, chocolate workshops india, learn chocolate making india, cocoa science blog india, craft chocolate india, chocolate education india, chocolate blogging india, tempering chocolate course, chocolate consultant Mumbai, RT Chocos";
  $pathPrefix = "";
  $isHome = true;

  // Load database connection and fetch the latest 5 cached insights for server-side scrolling ticker rendering
  require_once $pathPrefix . 'includes/db.php';
  try {
      $pdo = get_db();
      $insightStmt = $pdo->query("SELECT insight_text FROM ai_insights ORDER BY id DESC LIMIT 5");
      $cachedInsights = $insightStmt->fetchAll(PDO::FETCH_COLUMN);
  } catch (Exception $e) {
      $cachedInsights = [];
  }
  if (empty($cachedInsights)) {
      $cachedInsights = [
          "Cacao beans contain over 600 flavor compounds, making them chemically more complex than red wine.",
          "The ideal temperature for dark chocolate tempering is between 88°F and 90°F (31°C - 32°C).",
          "Criollo cacao is highly prized for its delicate, aromatic flavor profile and low bitterness.",
          "Water is chocolate's biggest enemy; even a single drop can cause a batch to seize.",
          "Roasting cacao beans sterilizes them, reduces moisture, and develops crucial chocolate aroma precursors."
      ];
  }

  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-home" class="page active">
  <div class="deco-leaf-left"></div>
  <div class="deco-leaf-right"></div>

  <!-- Split Hero (No Video) -->
  <section id="hero" style="min-height: 85vh; padding: 140px 24px 80px;">
    <div class="deco-circle-1"></div>
    <div class="deco-circle-2"></div>
    <div class="deco-radial"></div>
    <div class="split-hero-container">
      <div class="split-hero-content">
        <span class="hero-tag fade-up">✨ Premier Chocolate Academy &amp; Lab</span>
        <h1 class="fade-up-d1">Unlocking Cacao's <em>Science &amp; Art</em></h1>
        <p class="fade-up-d2">An independent Indian chocolate learning academy covering bean-to-bar craftsmanship, cacao formulation science, and professional masterclasses.</p>
        <div class="hero-btns fade-up-d3">
          <a href="workshops.php" class="btn-hero-primary">Start Learning</a>
          <button onclick="toggleAiDrawer()" class="btn-hero-outline" style="display:inline-flex; align-items:center; gap:8px;">✨ Ask CocoaGenius AI</button>
        </div>
      </div>
      <div class="split-hero-visual fade-in">
        <div class="split-hero-img-wrapper">
          <div class="hero-slideshow">
            <div class="slide active"><img src="assets/premium_chocolate.png" alt="Luxury artisanal chocolate bar craft photography" loading="eager"></div>
            <div class="slide"><img src="assets/premium_bonbons.png" alt="Glossy hand-painted artisan chocolate bonbons"></div>
            <div class="slide"><img src="assets/premium_pods.png" alt="Organic raw cacao pods split open displaying pulp"></div>
          </div>
          
          <!-- Circular Rotating Brand Stamp -->
          <div class="circular-stamp-container">
            <svg class="circular-stamp" viewBox="0 0 100 100">
              <path id="circlePath" d="M 50, 50 m -37, 0 a 37,37 0 1,1 74,0 a 37,37 0 1,1 -74,0" fill="none" />
              <text>
                <textPath href="#circlePath">🌱 100% CRAFT BEAN-TO-BAR • RT CHOCOS ACADEMY •</textPath>
              </text>
            </svg>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Moving AI Insights Ticker Bar (Looping horizontal ticker) -->
  <div class="ticker-wrap">
    <div class="ticker-title">✨ AI Live Insights</div>
    <div class="ticker-track">
      <div class="ticker-content">
        <?php foreach ($cachedInsights as $insight): ?>
          <span class="ticker-item">🌱 <?php echo htmlspecialchars($insight, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
      </div>
      <!-- Duplicate for infinite seamless scroll -->
      <div class="ticker-content" aria-hidden="true">
        <?php foreach ($cachedInsights as $insight): ?>
          <span class="ticker-item">🌱 <?php echo htmlspecialchars($insight, ENT_QUOTES, 'UTF-8'); ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

   <!-- Credibility & Trust Strip -->
  <div id="cred-strip" class="trust-proof-bar">
    <div class="cred-item"><div class="cred-num">10+</div><div class="cred-label">Years of Experience</div></div>
    <div class="cred-item"><div class="cred-num">2,000+</div><div class="cred-label">Students Trained</div></div>
    <div class="cred-item"><div class="cred-num">50+</div><div class="cred-label">Workshops Conducted</div></div>
    <div class="cred-item"><div class="cred-num">100%</div><div class="cred-label">Bean-to-Bar &amp; Science First</div></div>
  </div>

  <!-- Section 01: Why RT Chocos / Philosophy -->
  <section id="why-us">
    <div class="section" style="text-align:center;">
      <div class="section-label">01 / PHILOSOPHY</div>
      <h2 class="section-title">Craftsmanship Meets Science</h2>
      <div class="divider" style="margin:20px auto;"></div>
      <p class="section-subtitle" style="margin:0 auto 48px;text-align:center;">We don't just make chocolate — we teach you the science, art and business behind every bar.</p>
      <div class="why-grid">
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/cocoabeans.png.jpg" alt="Bean-to-Bar Cacao Procurement" loading="lazy">
          </div>
          <div class="why-card-text">
            <h4>Bean-to-Bar Expertise</h4>
            <p>Direct cacao procurement from Kerala &amp; Karnataka. We control every step — from roast to wrap.</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/temepring.jpg" alt="Science-First Chocolate Approach" loading="lazy">
          </div>
          <div class="why-card-text">
            <h4>Science-First Approach</h4>
            <p>Tempering curves, water activity, crystal polymorphism — we teach the why, not just the how.</p>
          </div>
        </div>
        <div class="why-card">
          <div class="why-card-img-wrapper">
            <img src="assets/bonbons.png" alt="10+ Years Chocolate Teaching" loading="lazy">
          </div>
          <div class="why-card-text">
            <h4>10+ Years Teaching</h4>
            <p>From curious home bakers to aspiring entrepreneurs, our workshops transform skill and confidence.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 02: AI Diagnostics Tool -->
  <section class="ai-widget-sec section">
    <div class="ai-widget-container">
      <div class="section-label">02 / DIAGNOSTICS</div>
      <h2 class="section-title">Troubleshoot Chocolate Defects Instantly</h2>
      <p class="section-subtitle" style="margin: 0 auto 36px;">Experiencing issues with your batch? Our CocoaGenius AI can diagnose common tempering, crystallization, and texture defects in seconds. Select a symptom or chat with the AI helper.</p>
      
      <div class="ai-widget-grid">
        <div class="ai-widget-card" onclick="sendTroubleshootQuery('Why does my tempered chocolate have dull white streaks or haze on the surface?')">
          <div class="ai-widget-icon">🔬</div>
          <h3>Dull Streaks or Haze</h3>
          <p>Chocolate has greyish streaks, swirls, or a dull finish instead of a glossy shine.</p>
          <span>Diagnose Defect &rarr;</span>
        </div>
        <div class="ai-widget-card" onclick="sendTroubleshootQuery('Why is my chocolate soft at room temperature and refuses to snap when broken?')">
          <div class="ai-widget-icon">🍫</div>
          <h3>No Snap or Soft Texture</h3>
          <p>Chocolate melts immediately in fingers, bends instead of snaps, or won\'t release from the mould.</p>
          <span>Diagnose Temper &rarr;</span>
        </div>
        <div class="ai-widget-card" onclick="sendTroubleshootQuery('Why does my chocolate feel gritty, sandy, or coarse on the tongue instead of silky smooth?')">
          <div class="ai-widget-icon">👅</div>
          <h3>Gritty or Coarse Mouthfeel</h3>
          <p>Particles feel sandy or rough on the palate, lacking the signature smooth melt.</p>
          <span>Diagnose Grind &rarr;</span>
        </div>
        <div class="ai-widget-card" onclick="sendTroubleshootQuery('How can I safely add water-based liquid flavors or colors to chocolate without seizing it?')">
          <div class="ai-widget-icon">⚠️</div>
          <h3>Chocolate Seizing Risk</h3>
          <p>Learn how to safely introduce colors or liquid flavors without thickening the batch.</p>
          <span>Explain Process &rarr;</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 03: AI Chocolab Section -->
  <section class="ai-chocolab-sec section" style="position: relative; overflow: hidden; padding: 80px 24px;">
    <div class="deco-circle-3"></div>
    
    <div class="chocolab-inner">
      <div class="section-label" style="text-align: center;">03 / INNOVATION</div>
      <h2 class="section-title" style="text-align: center; margin-bottom: 12px;">✨ AI Chocolab Formulation Playground</h2>
      <p class="section-subtitle" style="max-width: 680px; margin: 0 auto 48px; text-align: center;">Design your dream chocolate bar. Select a base, cacao percentage, and gourmet inclusions. Our CocoaGenius AI will instantly formulate a custom recipe, tasting profile, and tempering guide for your creation.</p>
      
      <div class="chocolab-layout">
        <!-- Control Panel -->
        <div class="chocolab-controls">
          <h3>Customize Ingredients</h3>
          
          <div class="form-group">
            <label>1. Select Cacao Base</label>
            <select id="chocolab-base">
              <option value="Dark Chocolate" selected>Dark Chocolate (Rich &amp; Complex)</option>
              <option value="Milk Chocolate">Milk Chocolate (Creamy &amp; Sweet)</option>
              <option value="White Chocolate">White Chocolate (Buttery &amp; Smooth)</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>2. Cacao Percentage: <span id="chocolab-percent-val">72%</span></label>
            <input type="range" id="chocolab-percent" min="30" max="100" value="72" oninput="document.getElementById('chocolab-percent-val').textContent = this.value + '%'">
          </div>
          
          <div class="form-group">
            <label>3. Choose Inclusions (Up to 3)</label>
            <div class="inclusions-grid">
              <label class="inclusion-checkbox"><input type="checkbox" value="Sea Salt" name="inclusions"> Sea Salt</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Cardamom" name="inclusions"> Cardamom</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Lavender" name="inclusions"> Lavender</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Bird's Eye Chili" name="inclusions"> Bird's Eye Chili</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Orange Zest" name="inclusions"> Orange Zest</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Rose Petals" name="inclusions"> Rose Petals</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Peppermint" name="inclusions"> Peppermint</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Roasted Almonds" name="inclusions"> Roasted Almonds</label>
            </div>
          </div>
          
          <button class="btn-primary chocolab-submit-btn" onclick="generateCustomBarFormula()">⚡ Formulate Recipe</button>
        </div>
        
        <!-- Formulation Output -->
        <div class="chocolab-output">
          <div id="chocolab-placeholder">
            <div class="chocolab-placeholder-icon">🧪</div>
            <h4>Ready for Formulation</h4>
            <p>Select your custom ingredients on the left and click "Formulate Recipe" to generate your custom chocolate bar profile.</p>
          </div>
          
          <div id="chocolab-loader" style="display: none;">
            <div class="ai-typing-indicator">
              <span class="ai-typing-dot"></span>
              <span class="ai-typing-dot"></span>
              <span class="ai-typing-dot"></span>
            </div>
            <h4>AI Alchemist at Work...</h4>
            <p>Calculating tempering ranges, flavor chemistry, and custom descriptions...</p>
          </div>
          
          <div id="chocolab-results" style="display: none; width: 100%;">
            <div class="chocolab-results-header">
              <span class="chocolab-results-label">AI Dynamic Recipe Profile</span>
              <span id="chocolab-result-base" class="chocolab-results-base">72% Dark Chocolate</span>
            </div>
            <h3 id="chocolab-result-name">Signature formulation</h3>
            <p id="chocolab-result-desc" class="chocolab-result-desc"></p>
            
            <div class="chocolab-details-grid">
              <div>
                <h5>👅 Tasting Notes</h5>
                <p id="chocolab-result-tasting"></p>
              </div>
              <div>
                <h5>🌡️ Tempering Guide</h5>
                <p id="chocolab-result-tempering"></p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 04: Featured Workshops -->
  <section style="background:var(--ivory);">
    <div class="section">
      <div class="workshops-section-header">
        <p class="section-label" style="margin-bottom:12px;">04 / ACADEMY</p>
        <h2 class="section-title">Workshops &amp; Masterclasses</h2>
        <div class="divider"></div>
        <p style="max-width: 540px; font-size: 16px; line-height: 1.7; color: var(--brown-light); margin: 18px auto 0; font-weight: 400; text-align: center;">
          A collection of premium, science-first chocolate workshops and masterclasses. Learn bean-to-bar making, tempering science, and recipe formulation from expert Aarti Saluja Sahni.
        </p>
      </div>
      
      <div class="grid-3" id="home-workshops" style="margin-top: 48px;">
        <?php
          require_once 'includes/workshops_data.php';
          foreach ($workshops as $w) {
              echo renderWorkshopCard($w);
          }
        ?>
      </div>
    </div>
  </section>


  <!-- Section 05: Interactive Flavor Wheel Section -->
  <section id="flavor-wheel-sec">
    <div class="wheel-layout">
      <!-- Title Column -->
      <div class="wheel-title-col">
        <span class="section-label" style="color:var(--gold); display:block; margin-bottom:8px;">05 / SCIENCE</span>
        <h2><span>Chocolate</span>Flavor Wheel</h2>
        <div class="gold-divider"></div>
        <p>Explore the intricate dimensions of bean-to-bar chocolate. Click on the main sectors of the wheel or the cards on the right to discover how cacao origin, farm processing, and taste profiles shape the final bar's character.</p>
      </div>

      <!-- Wheel Column -->
      <div class="wheel-svg-col">
        <div class="wheel-svg-wrapper">
          <svg class="wheel-svg" viewBox="0 0 500 500" id="interactive-wheel">
            <!-- Dynamic SVG content will be injected here by JS -->
          </svg>
        </div>
      </div>

      <!-- Details Column -->
      <div class="wheel-details-col">
        <!-- Flavor Notes Card -->
        <div class="wheel-detail-card" data-sector="flavor" id="card-flavor">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG cup icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ff5252" stroke-width="2">
                <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                <line x1="6" y1="1" x2="6" y2="4" />
                <line x1="10" y1="1" x2="10" y2="4" />
                <line x1="14" y1="1" x2="14" y2="4" />
              </svg>
            </div>
            <span class="card-title-text">1. Flavor Notes</span>
          </div>
          <p>Aromas and flavors derived from the ingredients, soil, climate (terroir), and natural chemical compounds in the cacao beans.</p>
        </div>

        <!-- Process Card -->
        <div class="wheel-detail-card" data-sector="process" id="card-process">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG gear icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f39c12" stroke-width="2">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
              </svg>
            </div>
            <span class="card-title-text">2. Process</span>
          </div>
          <p>The crucial post-harvest and production steps—fermenting, drying, roasting, conching, and tempering—that shape chocolate's character.</p>
        </div>

        <!-- Taste Profile Card -->
        <div class="wheel-detail-card" data-sector="taste" id="card-taste">
          <div class="card-header-row">
            <div class="card-icon-container">
              <!-- Inline SVG tongue icon -->
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#82c91e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 10h20" />
                <path d="M21.5 10c-.5 5-4.5 9-9.5 9s-9-4-9-9" />
                <path d="M12 10v9" />
                <path d="M12 14c1.5 0 2.5.5 2.5 1" />
              </svg>
            </div>
            <span class="card-title-text">3. Taste Profile</span>
          </div>
          <p>The tactile and basic taste sensations perceived on the palate—sweetness, acidity, bitterness, saltiness, melt rate, and texture.</p>
        </div>
      </div>

      <!-- CTA Button beneath -->
      <div class="wheel-cta-container">
        <a href="blog" class="btn-outline" style="text-decoration:none; background: rgba(38,15,6,0.35); backdrop-filter: blur(2px);">Read Latest Articles</a>
      </div>
    </div>

    <!-- Inline Script for interactive wheel logic -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const cx = 250;
        const cy = 250;
        const R0 = 62; // Center hub radius
        const R1 = 62;
        const R2 = 138; // Main sector outer radius
        const R3 = 220; // Subsector outer radius
        
        const WHEEL_DATA = [
          {
            id: "flavor",
            label: "1. FLAVOR NOTES",
            color: "#6e1d28", // Rich deep red-brown
            accentColor: "#b82e46",
            textLight: "#ffd1d6",
            startAngle: 180,
            endAngle: 300,
            icon: `<path d="M-6 -2c0-3.3 2.7-6 6-6s6 2.7 6 6v2c0 2.2-1.8 4-4 4h-4c-2.2 0-4-1.8-4-4v-2zm12 6h2v-2h-2v2zM0 -14v2M-3 -13v1.5M3 -13v1.5" stroke="#ff8797" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Earthy", startAngle: 180, endAngle: 200, icon: `<path d="M0 -6 C4 -2 4 4 0 6 C-4 4 -4 -2 0 -6 Z M0 -6 L0 6" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Spicy", startAngle: 200, endAngle: 220, icon: `<path d="M0 -6 L1.5 -2 L6 -2 L2.5 1 L4 5 L0 2.5 L-4 5 L-2.5 1 L-6 -2 L-1.5 -2 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Sweet", startAngle: 220, endAngle: 240, icon: `<path d="M-5 -5 L5 5 M-5 5 L5 -5 M-2.5 0 A 2.5 2.5 0 1 0 2.5 0 A 2.5 2.5 0 1 0 -2.5 0 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Nutty", startAngle: 240, endAngle: 260, icon: `<path d="M-3 -2 C-3 2 3 2 3 -2 C3 -4 -3 -4 -3 -2 Z M-3 -2 Q 0 -6 3 -2" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Floral", startAngle: 260, endAngle: 280, icon: `<circle cx="0" cy="0" r="2" fill="#f6f2ea"/><circle cx="0" cy="-4" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="4" cy="0" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="0" cy="4" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/><circle cx="-4" cy="0" r="1.8" fill="none" stroke="#f6f2ea" stroke-width="1.2"/>` },
              { label: "Fruity", startAngle: 280, endAngle: 300, icon: `<path d="M-1.5 -4 A 2.5 2.5 0 1 0 -1.5 1 A 2.5 2.5 0 1 0 -1.5 -4 Z M1.5 -1 A 2.5 2.5 0 1 0 1.5 4 A 2.5 2.5 0 1 0 1.5 -1 Z M-1.5 -1 Q 0 -6 3 -5" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` }
            ]
          },
          {
            id: "process",
            label: "2. PROCESS",
            color: "#843d0e", // Rich deep orange-brown
            accentColor: "#c85a17",
            textLight: "#ffe0cc",
            startAngle: 300,
            endAngle: 420,
            icon: `<path d="M-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0 M7 0 a1.6 1.6 0 0 0 .3 1.8 l.1.1 a2 2 0 1 1 -2.8 2.8 l-.1-.1 a1.6 1.6 0 0 0 -1.8-.3 a1.6 1.6 0 0 0 -1 1.5 v.1 a2 2 0 0 1 -4 0 v-.1 a1.6 1.6 0 0 0 -1-1.5 a1.6 1.6 0 0 0 -1.8.3 l-.1.1 a2 2 0 1 1 -2.8 -2.8 l.1-.1 a1.6 1.6 0 0 0 .3 -1.8 a1.6 1.6 0 0 0 -1.5 -1 h-.1 a2 2 0 0 1 0 -4 h.1 a1.6 1.6 0 0 0 1.5 -1 a1.6 1.6 0 0 0 -.3 -1.8 l-.1-.1 a2 2 0 1 1 2.8 -2.8 l.1.1 a1.6 1.6 0 0 0 1.8 .3 a1.6 1.6 0 0 0 1 -1.5 v-.1 a2 2 0 0 1 4 0 v.1 a1.6 1.6 0 0 0 1 1.5 a1.6 1.6 0 0 0 1.8 -.3 l.1-.1 a2 2 0 1 1 2.8 2.8 l-.1.1 a1.6 1.6 0 0 0 -.3 1.8 a1.6 1.6 0 0 0 1.5 1 h.1 a2 2 0 0 1 0 4 h-.1 a1.6 1.6 0 0 0 -1.5 1 z" stroke="#ffb88c" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Roasted", startAngle: 300, endAngle: 324, icon: `<path d="M0 5 C-3.5 5 -4 2.5 -2.5 0 C-3 -1.5 -1.5 -5 0 -7 C1.5 -5 3 -1.5 2.5 0 C4 2.5 3.5 5 0 5 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Fermented", startAngle: 324, endAngle: 348, icon: `<path d="M-2.5 -5 H2.5 V-3 H-2.5 Z M-3 -3 H3 V4 C3 5 2.5 5.5 -3 5.5 H-3 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Dried", startAngle: 348, endAngle: 372, icon: `<circle cx="0" cy="0" r="3" stroke="#f6f2ea" fill="none" stroke-width="1.2"/><path d="M0 -5 V-7 M0 5 V7 M-5 0 H-7 M5 0 H7" stroke="#f6f2ea" stroke-width="1.2"/>` },
              { label: "Conched", startAngle: 372, endAngle: 396, icon: `<path d="M-4 4 V-4 L-1.5 -1.5 V-4 L1 -1.5 V-4 L4 -0.5 V4 Z M-1.5 4 V1.5 H1.5 V4" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Tempered", startAngle: 396, endAngle: 420, icon: `<path d="M-1.5 -6 H1.5 V2.5 A 2.5 2.5 0 1 1 -1.5 2.5 Z M0 -3.5 V1" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` }
            ]
          },
          {
            id: "taste",
            label: "3. TASTE PROFILE",
            color: "#2a4224", // Rich deep green-brown
            accentColor: "#476b3c",
            textLight: "#d0ffd0",
            startAngle: 60,
            endAngle: 180,
            icon: `<path d="M-6 0c0 0 3 3 6 3s6-3 6-3M-5 0c0 4 2.5 7 5 7s5-3 5-7" stroke="#a9e39a" fill="none" stroke-width="1.2"/>`,
            subsectors: [
              { label: "Smooth", startAngle: 60, endAngle: 84, icon: `<path d="M-5 -1.5 Q-2.5 -4 0 -1.5 T5 -1.5 M-5 1.5 Q-2.5 -1 0 1.5 T5 1.5" stroke="#f6f2ea" fill="none" stroke-width="1.2" stroke-linecap="round"/>` },
              { label: "Creamy", startAngle: 84, endAngle: 108, icon: `<path d="M0 -6 C3 -2.5 4 1 2.5 3.5 C0 6 -2.5 6 -2.5 3.5 C-4 1 -3 -2.5 0 -6 Z" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Salty", startAngle: 108, endAngle: 132, icon: `<path d="M-2.5 -3.5 H2.5 V5 H-2.5 Z M-1.5 -5 H1.5 V-3.5 H-1.5 Z M-1 -1 H1 M-1 1.5 H1" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Sour", startAngle: 132, endAngle: 156, icon: `<path d="M0 -5 A 5 5 0 1 0 0 5 A 5 5 0 1 0 0 -5 Z M0 0 L4 0 M0 0 L-2 -3.5 M0 0 L-2 3.5" stroke="#f6f2ea" fill="none" stroke-width="1.2"/>` },
              { label: "Other", startAngle: 156, endAngle: 180, icon: `<circle cx="-4" cy="0" r="1.2" fill="#f6f2ea"/><circle cx="0" cy="0" r="1.2" fill="#f6f2ea"/><circle cx="4" cy="0" r="1.2" fill="#f6f2ea"/>` }
            ]
          }
        ];

        const svg = document.getElementById("interactive-wheel");
        let activeSectorId = null;
        let gRotation = 0;

        function polarToCartesian(centerX, centerY, radius, angleInDegrees) {
          const radians = (angleInDegrees * Math.PI) / 180.0;
          return {
            x: centerX + radius * Math.cos(radians),
            y: centerY + radius * Math.sin(radians)
          };
        }

        function getSectorPath(x, y, r1, r2, startAngle, endAngle) {
          const start = polarToCartesian(x, y, r2, startAngle);
          const end = polarToCartesian(x, y, r2, endAngle);
          const startInner = polarToCartesian(x, y, r1, endAngle);
          const endInner = polarToCartesian(x, y, r1, startAngle);
          const largeArc = (endAngle - startAngle) > 180 ? 1 : 0;
          return `M ${start.x} ${start.y} A ${r2} ${r2} 0 ${largeArc} 1 ${end.x} ${end.y} L ${startInner.x} ${startInner.y} A ${r1} ${r1} 0 ${largeArc} 0 ${endInner.x} ${endInner.y} Z`;
        }

        function getArcPath(x, y, r, startAngle, endAngle, isCounterClockwise) {
          const start = polarToCartesian(x, y, r, startAngle);
          const end = polarToCartesian(x, y, r, endAngle);
          const sweep = isCounterClockwise ? 0 : 1;
          return `M ${start.x} ${start.y} A ${r} ${r} 0 0 ${sweep} ${end.x} ${end.y}`;
        }

        // Generate the SVG contents dynamically
        let svgContent = "";
        
        svgContent += `
          <defs>
            <filter id="glow-flavor" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="glow-process" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="glow-taste" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="8" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
          </defs>
        `;

        svgContent += `<g id="rotating-group">`;

        WHEEL_DATA.forEach((category) => {
          // --- 1. MAIN SECTOR ---
          const mainPath = getSectorPath(cx, cy, R1, R2, category.startAngle, category.endAngle);
          svgContent += `
            <path class="wheel-sector wheel-main-sector" 
                  d="${mainPath}" 
                  fill="${category.color}" 
                  data-category="${category.id}"
                  style="color: ${category.accentColor};"
            />
          `;

          const midAngle = (category.startAngle + category.endAngle) / 2;
          const textR = (R1 + R2) / 2 - 3;
          const textSpan = 45;
          
          // Always draw clockwise so text is right-side up when rotated to the top (12 o'clock)
          const textPathD = getArcPath(cx, cy, textR, midAngle - textSpan, midAngle + textSpan, false);

          const textPathId = `textpath-${category.id}`;
          svgContent += `
            <path id="${textPathId}" d="${textPathD}" fill="none" stroke="none" />
            <text class="wheel-label-text" fill="${category.textLight}">
              <textPath href="#${textPathId}" startOffset="50%" text-anchor="middle">
                ${category.label}
              </textPath>
            </text>
          `;

          const iconR = (R1 + R2) / 2 - 22;
          const iconPos = polarToCartesian(cx, cy, iconR, midAngle);
          const iconRot = midAngle + 90;
          svgContent += `
            <g transform="translate(${iconPos.x}, ${iconPos.y}) rotate(${iconRot})">
              ${category.icon}
            </g>
          `;

          // --- 2. SUB-SECTORS (OUTER RING) ---
          category.subsectors.forEach((sub, subIdx) => {
            const subPath = getSectorPath(cx, cy, R2, R3, sub.startAngle, sub.endAngle);
            svgContent += `
              <path class="wheel-sector wheel-sub-sector" 
                    d="${subPath}" 
                    fill="${category.color}" 
                    data-category="${category.id}"
                    style="opacity: ${0.75 + (subIdx * 0.05)}; color: ${category.accentColor};"
              />
            `;

            const subMidAngle = (sub.startAngle + sub.endAngle) / 2;
            const subTextR = (R2 + R3) / 2 + 2;
            const subTextSpan = 18;

            const subTextPathD = getArcPath(cx, cy, subTextR, subMidAngle - subTextSpan, subMidAngle + subTextSpan, false);
            const subTextPathId = `textpath-sub-${category.id}-${subIdx}`;

            svgContent += `
              <path id="${subTextPathId}" d="${subTextPathD}" fill="none" stroke="none" />
              <text class="wheel-sublabel-text" fill="#f6f2ea">
                <textPath href="#${subTextPathId}" startOffset="50%" text-anchor="middle">
                  ${sub.label}
                </textPath>
              </text>
            `;

            const subIconR = R2 + 18;
            const subIconPos = polarToCartesian(cx, cy, subIconR, subMidAngle);
            const subIconRot = subMidAngle + 90;
            svgContent += `
              <g transform="translate(${subIconPos.x}, ${subIconPos.y}) rotate(${subIconRot})">
                ${sub.icon}
              </g>
            `;
          });
        });

        svgContent += `</g>`; // End rotating-group

        // --- 3. CENTER HUB (STATIONARY) ---
        svgContent += `
          <g id="center-hub-group">
            <circle class="wheel-center-hub" cx="${cx}" cy="${cy}" r="${R0}" />
            <text x="${cx}" y="${cy - 4}" text-anchor="middle" fill="#d4af37" font-family="'Playfair Display', serif" font-size="14" font-weight="700" letter-spacing="1">CACAO</text>
            <text x="${cx}" y="${cy + 12}" text-anchor="middle" fill="#e8d8c8" font-family="'Inter', sans-serif" font-size="9" font-weight="600" letter-spacing="2">WHEEL</text>
          </g>
        `;

        svg.innerHTML = svgContent;

        const rotatingGroup = document.getElementById("rotating-group");
        const centerHub = document.getElementById("center-hub-group");
        const sectors = document.querySelectorAll(".wheel-sector");
        const cards = document.querySelectorAll(".wheel-detail-card");

        const rotationMap = {
          "flavor": 30,
          "process": -90,
          "taste": 150
        };

        function setFocus(sectorId) {
          if (activeSectorId === sectorId) return;
          
          activeSectorId = sectorId;

          if (sectorId) {
            const targetRotation = rotationMap[sectorId];
            const currentNorm = ((gRotation % 360) + 360) % 360;
            const targetNorm = ((targetRotation % 360) + 360) % 360;
            
            let diff = targetNorm - currentNorm;
            if (diff > 180) diff -= 360;
            if (diff < -180) diff += 360;
            
            gRotation += diff;
            rotatingGroup.style.transform = `rotate(${gRotation}deg)`;
            svg.classList.add("has-focus");
          } else {
            gRotation = 0;
            rotatingGroup.style.transform = `rotate(0deg)`;
            svg.classList.remove("has-focus");
          }

          sectors.forEach(sec => {
            if (!sectorId) {
              sec.classList.remove("focused");
            } else if (sec.getAttribute("data-category") === sectorId) {
              sec.classList.add("focused");
            } else {
              sec.classList.remove("focused");
            }
          });

          cards.forEach(card => {
            const cardSector = card.getAttribute("data-sector");
            card.classList.remove("active-flavor", "active-process", "active-taste");
            
            if (cardSector === sectorId) {
              card.classList.add(`active-${cardSector}`);
            }
          });
        }

        sectors.forEach(sec => {
          sec.addEventListener("click", function(e) {
            e.stopPropagation();
            const category = this.getAttribute("data-category");
            setFocus(category);
          });
        });

        cards.forEach(card => {
          card.addEventListener("click", function() {
            const sectorId = this.getAttribute("data-sector");
            if (activeSectorId === sectorId) {
              setFocus(null);
            } else {
              setFocus(sectorId);
            }
          });
        });

        centerHub.addEventListener("click", function(e) {
          e.stopPropagation();
          setFocus(null);
        });

        document.addEventListener("click", function(e) {
          const wheelSection = document.getElementById("flavor-wheel-sec");
          if (wheelSection && !wheelSection.contains(e.target)) {
            setFocus(null);
          }
        });
      });
    </script>
  </section>

  <!-- Section 06: Journal & Technical Deep Dives -->
  <section style="background:var(--ivory); padding: 80px 24px;">
    <div class="section" style="max-width:1140px; margin:0 auto; text-align:center;">
      <div class="section-label">06 / JOURNAL</div>
      <h2 class="section-title">Latest Cocoa Science Insights</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <p class="section-subtitle" style="max-width:540px; margin:0 auto 48px; color:var(--brown-light);">Deep-dives into cocoa powder pH, tempering crystal diagnostics, lecithin emulsion science, and bean-to-bar formulation.</p>
      
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:28px; text-align:left;">
        <?php
          require_once 'includes/blog-data.php';
          $recentBlogs = array_slice($BLOGS, 0, 3, true);
          foreach ($recentBlogs as $slug => $b):
            $bImg = $b['thumbnail'] ?? $b['image'] ?? 'assets/logo.png';
        ?>
          <a href="blog/<?php echo htmlspecialchars($slug); ?>" style="text-decoration:none; color:inherit; background:white; border-radius:20px; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.05); border:1px solid rgba(0,0,0,0.04); display:flex; flex-direction:column; transition:transform 0.3s ease, box-shadow 0.3s ease;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 16px 40px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 32px rgba(0,0,0,0.05)';">
            <div style="height:190px; overflow:hidden; background:var(--cream-dark); position:relative;">
              <img src="<?php echo htmlspecialchars($bImg); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy">
              <span style="position:absolute; top:12px; left:12px; background:var(--brown); color:var(--ivory); font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; padding:4px 10px; border-radius:30px;"><?php echo htmlspecialchars($b['category']); ?></span>
            </div>
            <div style="padding:24px; display:flex; flex-direction:column; flex-grow:1;">
              <span style="font-size:12px; color:var(--gold); font-weight:600; margin-bottom:8px; display:block;"><?php echo htmlspecialchars($b['date']); ?> • <?php echo htmlspecialchars($b['read']); ?> read</span>
              <h4 style="font-family:'Cormorant Garamond',serif; font-size:20px; font-weight:700; color:var(--brown); margin-bottom:10px; line-height:1.35;"><?php echo htmlspecialchars($b['title']); ?></h4>
              <p style="font-family:var(--font-sans); font-size:13.5px; line-height:1.6; color:var(--brown-light); font-weight:300; margin-bottom:16px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;"><?php echo htmlspecialchars($b['excerpt']); ?></p>
              <span style="margin-top:auto; font-size:13px; font-weight:600; color:var(--brown); display:inline-flex; align-items:center; gap:6px;">Read Full Article &rarr;</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      
      <div style="margin-top:40px;">
        <a href="blog" class="btn-outline-dark" style="text-decoration:none;">Browse All Journal Articles &rarr;</a>
      </div>
    </div>
  </section>

  <!-- Section 07: SEO Content Block -->
  <section style="background:var(--cream-dark); padding: 80px 24px;">
    <div class="section" style="max-width:900px; margin:0 auto; text-align:center;">
      <div class="section-label">07 / EDUCATION</div>
      <h2 class="section-title">The Science of Indian Bean-to-Bar Chocolate</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <div style="font-family:var(--font-sans); font-size:15.5px; line-height:1.8; color:var(--brown-light); font-weight:300; text-align:left; display:flex; flex-direction:column; gap:20px;">
        <p>
          Welcome to <strong>RT Chocos</strong>, India's first chocolate blogging website and premier bean-to-bar learning academy. Founded by certified chocolate educator and recipe developer <strong>Aarti Saluja Sahni</strong>, RT Chocos is dedicated to demystifying the complex chemistry, tempering science, and formulation metrics behind craft chocolate making.
        </p>
        <p>
          The chocolate landscape in India is undergoing a massive transformation. Craft chocolate makers are shifting away from mass-produced compound coatings to source single-origin organic cacao beans directly from estates in Kerala, Karnataka, and Tamil Nadu. At our academy, we believe that understanding the science of cacao fermentation, roasting thermodynamics, stone grinding (conching), and tempering curves is the key to creating award-winning artisan bars.
        </p>
        <p>
          Whether you are a home baker wanting to learn how to temper chocolate, an entrepreneur looking to launch your own brand of Indian craft chocolate, or a hobbyist searching for authentic cocoa science resources, our blog and workshops provide the technical blueprints you need. We cover everything from pH levels in cocoa powder, FAT bloom versus SUGAR bloom diagnostics, and organic sugar alternatives, to hands-on tempering masterclasses in Mumbai and online.
        </p>
        <p>
          Explore our professional <a href="workshops" style="color:var(--brown); font-weight:600; text-decoration:none; border-bottom:1px solid var(--brown);">Chocolate Academy India Workshops</a>, browse our curated <a href="shop" style="color:var(--brown); font-weight:600; text-decoration:none; border-bottom:1px solid var(--brown);">Chocolate Shop</a> for starter kits and single-origin ingredients, or read our latest <a href="blog" style="color:var(--brown); font-weight:600; text-decoration:none; border-bottom:1px solid var(--brown);">Chocolate Blog India articles</a> to start your craft chocolate learning journey.
        </p>
      </div>
    </div>
  </section>

  <!-- Section 08: Craft Gallery Preview -->
  <section style="text-align:center;">
    <div class="section">
    <div class="section-label">08 / CRAFT</div>
    <h2 class="section-title">Gallery</h2>
    <div class="divider" style="margin:20px auto 40px;"></div>
    <div class="gallery-grid">
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/cocoabeans.png.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Roasting</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/temepring.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Tempering</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/bonbons.png');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Moulding</span>
      </div>
      <div class="gallery-item">
        <div class="gallery-item-bg" style="background-image: url('assets/workshop.jpg');"></div>
        <div class="gallery-item-overlay"></div>
        <span class="gallery-item-text">Workshop</span>
      </div>
    </div>
    <a href="gallery" class="btn-outline-dark" style="margin-top:32px; text-decoration:none;">View Full Gallery &rarr;</a>
    </div>
  </section>

  <!-- Section 09: Newsletter CTA -->
  <section id="newsletter-section">
    <div class="inner">
      <div class="sub-label">09 / COMMUNITY</div>
      <h2>The Chocolate Letter</h2>
      <p>Weekly recipes, science deep-dives, workshop announcements and exclusive offers.</p>
      <form class="newsletter-row" id="newsletter-home-form" novalidate>
        <input class="newsletter-input" type="email" placeholder="Enter your email" required />
        <button class="btn-gold" type="submit">Subscribe</button>
      </form>
      <div id="newsletter-home-feedback" style="margin-top: 18px; display: none; font-size: 14.5px; font-weight: 400; line-height: 1.6; animation: fadeIn 0.3s ease;"></div>
    </div>
  </section>

</div><!-- end home -->

<!-- --- ABOUT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
