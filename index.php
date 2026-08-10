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

  <!-- BENTO DISCOVERY ROW -->
  <section class="bento-discovery-sec">
    <div class="bento-grid">
      <!-- Bento 1: Daily Discovery -->
      <div class="bento-card">
        <div>
          <div class="bento-label">DAILY DISCOVERY 🌿</div>
          <h3 class="bento-title">What makes chocolate bloom?</h3>
          <p class="bento-desc">Understand the science, causes and solutions in simple words.</p>
        </div>
        <img src="assets/bloom.png" alt="Chocolate Bloom Science" class="bento-img">
        <a href="blog" class="bento-link">EXPLORE NOW &rarr;</a>
      </div>

      <!-- Bento 2: Live from the Lab -->
      <div class="lab-test-card">
        <div>
          <div class="bento-label">LIVE FROM THE LAB 🌿</div>
          <h3 class="bento-title" style="font-size: 20px;">Freeze-Dried Strawberry in Chocolate</h3>
          <span class="lab-tag">Test #23</span>
          <ul class="lab-specs-list">
            <li><strong>Objective:</strong> Improve crunch &amp; flavor balance</li>
            <li><strong>Observation:</strong> Intense natural strawberry aroma</li>
            <li><strong>Result:</strong> Excellent texture &amp; crystal stability</li>
            <li><strong>Conclusion:</strong> Works beautifully in 70% dark chocolate</li>
          </ul>
          <a href="blog" class="bento-link">SEE FULL EXPERIMENT &rarr;</a>
        </div>
        <div>
          <img src="assets/freeze-dried-fruits.png" alt="Freeze dried strawberries in chocolate" style="width: 100%; height: 180px; object-fit: cover; border-radius: 14px;">
        </div>
      </div>

      <!-- Bento 3: Ingredient Spotlight -->
      <div class="bento-card">
        <div>
          <div class="bento-label">INGREDIENT SPOTLIGHT</div>
          <h3 class="bento-title">Cocoa Butter</h3>
          <p class="bento-desc">The soul of chocolate. Explore its types, properties, functions &amp; impact.</p>
        </div>
        <img src="assets/cocoa_butter_spotlight.png" alt="Cocoa Butter Chunks" class="bento-img">
        <a href="blog" class="bento-link">EXPLORE MORE &rarr;</a>
      </div>
    </div>
  </section>

  <!-- INTERACTIVE CHOCOLATE TABLE SECTION -->
  <section class="chocolate-table-sec">
    <div class="chocolate-table-header">
      <h2 class="chocolate-table-title">EXPLORE THE CHOCOLATE TABLE 🌿</h2>
      <p class="chocolate-table-subtitle">Click any object to explore a world of knowledge.</p>
    </div>

    <div class="chocolate-table-wrapper">
      <img src="assets/chocolate_table_interactive.png" alt="Artisan Chocolate Table" class="chocolate-table-bg-img">

      <!-- Interactive Hotspot Pins -->
      <!-- 1. Bean to Bar (bowl of cacao beans) -->
      <div class="table-hotspot" style="top: 32%; left: 32%;" onclick="openTableModal('bean-to-bar')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Bean to Bar</span>
        </div>
      </div>

      <!-- 2. Knowledge Hub (open leather journal) -->
      <div class="table-hotspot" style="top: 72%; left: 52%;" onclick="openTableModal('knowledge-hub')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Knowledge Hub</span>
        </div>
      </div>

      <!-- 3. Chocolate Lab (microscope) -->
      <div class="table-hotspot" style="top: 26%; left: 74%;" onclick="openTableModal('chocolate-lab')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Chocolate Lab</span>
        </div>
      </div>

      <!-- 4. Recipes & Formulations (chocolate bar) -->
      <div class="table-hotspot" style="top: 60%; left: 85%;" onclick="openTableModal('recipes-formulations')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Recipes &amp; Formulations</span>
        </div>
      </div>

      <!-- 5. Techniques (knife / scraper) -->
      <div class="table-hotspot" style="top: 66%; left: 36%;" onclick="openTableModal('techniques')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Techniques</span>
        </div>
      </div>

      <!-- 6. Origins & Atlas (cacao pod / map) -->
      <div class="table-hotspot" style="top: 66%; left: 14%;" onclick="openTableModal('origins-atlas')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Origins &amp; Atlas</span>
        </div>
      </div>

      <!-- 7. Workshops & Academy (whisk) -->
      <div class="table-hotspot" style="top: 66%; left: 70%;" onclick="openTableModal('workshops-academy')">
        <div class="hotspot-pin">
          <span class="hotspot-dot"></span>
          <span>Workshops &amp; Academy</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Interactive Table Modal Backdrop -->
  <div id="table-interactive-modal" class="table-modal-backdrop" onclick="if(event.target === this) closeTableModal()">
    <div class="table-modal-box">
      <button type="button" class="table-modal-close" onclick="closeTableModal()" title="Close Modal">&times;</button>
      
      <div class="table-modal-badge" id="modal-table-badge">🌿 CRAFT PROCESS</div>
      <h3 class="table-modal-title" id="modal-table-title">Bean to Bar</h3>
      <p class="table-modal-desc" id="modal-table-desc">
        Explore the entire journey from raw cacao bean harvesting to precision roasting, winnowing, stone grinding, and tempering.
      </p>

      <div class="table-modal-highlights">
        <h5>✨ KEY INSIGHTS &amp; TAKEAWAYS</h5>
        <ul id="modal-table-list">
          <li>Single-Origin Bean Sourcing from Kerala &amp; Karnataka</li>
          <li>Fermentation &amp; Drying Thermodynamics</li>
          <li>72-Hour Stone Grinding (Conching) for Silky Smooth Melt</li>
        </ul>
      </div>

      <div class="table-modal-actions" id="modal-table-actions">
        <a href="chocopedia.php" id="modal-table-btn-primary" class="table-modal-btn-primary">Explore Bean-to-Bar Guide &rarr;</a>
        <a href="workshops.php" id="modal-table-btn-secondary" class="table-modal-btn-secondary">Book Workshop</a>
      </div>
    </div>
  </div>

  <script>
    const TABLE_MODAL_DATA = {
      'bean-to-bar': {
        badge: '🌿 CRAFT PROCESS',
        title: 'Bean to Bar Cacao Craft',
        desc: 'From estate cacao bean selection and post-harvest fermentation chemistry to roasting thermodynamics, winnowing, and stone grinding (conching). Learn how raw beans transform into fine chocolate.',
        highlights: [
          'Direct Single-Origin Procurement from Estates in Kerala & Karnataka',
          'Fermentation & Drying Thermodynamics for Flavor Precursor Development',
          '72-Hour Stone Grinding & Micro-Particle Size Reduction (<20 Microns)'
        ],
        btn1Text: 'Explore Bean-to-Bar Guide →',
        btn1Href: 'chocopedia.php',
        btn2Text: 'Book Workshop',
        btn2Href: 'workshops.php'
      },
      'knowledge-hub': {
        badge: '📚 ENCYCLOPEDIA & JOURNAL',
        title: 'RT Chocos Knowledge Hub',
        desc: 'Our comprehensive digital repository of cocoa science articles, troubleshooting manuals, research papers, and technical crystallization guides.',
        highlights: [
          'Fat Bloom vs Sugar Bloom Defect Diagnostics',
          'Cocoa Butter Polymorphism & Form V Crystal Nucleation Curves',
          'pH Balance & Alkalization in Natural vs Dutch-Processed Cocoa Powder'
        ],
        btn1Text: 'Browse Science Journal →',
        btn1Href: 'blog.php',
        btn2Text: 'Search Chocopedia',
        btn2Href: 'chocopedia.php'
      },
      'chocolate-lab': {
        badge: '🧪 AI FORMULATION & R&D',
        title: 'Chocolate Science Lab',
        desc: 'Our advanced R&D laboratory playground for precision ingredient analysis, crystal structure testing, and AI-powered chocolate bar formulation.',
        highlights: [
          'CocoaGenius AI Alchemist Recipe Engine',
          'Water Activity & Emulsion Stability Matrix',
          'Custom Inclusions & Fat-to-Sugar Crystallization Ratios'
        ],
        btn1Text: 'Launch AI Chocolab →',
        btn1Href: '#ai-chocolab-sec',
        btn1Action: () => { closeTableModal(); document.querySelector('.ai-chocolab-sec')?.scrollIntoView({behavior:'smooth'}); },
        btn2Text: 'Ask CocoaGenius AI',
        btn2Href: 'javascript:toggleAiDrawer()'
      },
      'recipes-formulations': {
        badge: '🍫 PRO FORMULATIONS',
        title: 'Recipes & Formulations',
        desc: 'Craft precision blueprints for single-origin dark slabs, glossy hand-painted bonbons, ganache emulsions, and artisan truffles.',
        highlights: [
          '72% Single Origin Aztec Dark Formula',
          'Passionfruit & Cardamom Ganache Bonbon Emulsion',
          'Roasted Almond & Sea Salt Caramel Slab'
        ],
        btn1Text: 'Explore All Recipes →',
        btn1Href: 'chocopedia.php',
        btn2Text: 'Formulate Custom Bar',
        btn2Href: '#ai-chocolab-sec'
      },
      'techniques': {
        badge: '👩‍🍳 MASTER SKILLS',
        title: 'Chocolatier Techniques',
        desc: 'Master essential craft techniques: seed method tempering, tabling on granite/marble, airbrushing cocoa butter colors, and poly-carbonate shell moulding.',
        highlights: [
          'Marble Slab Tabling & Crystal Seeding Thermodynamics',
          'Form V Nucleation (88°F - 90°F Working Range)',
          'Glossy Moulding & Snap Physics'
        ],
        btn1Text: 'View Masterclass Courses →',
        btn1Href: 'workshops.php',
        btn2Text: 'Troubleshoot Defect',
        btn2Href: 'blog.php'
      },
      'origins-atlas': {
        badge: '🗺️ TERROIR & SOURCING',
        title: 'Origins & Cacao Atlas',
        desc: 'Trace the roots of global cacao genetics—Criollo, Trinitario, and Forastero—and explore Indian estate micro-climates and terroir profiling.',
        highlights: [
          'Idukki, Kerala Single Estate Terroir & Flavor Profiles',
          'Puttur, Karnataka Cacao Fermentation Protocols',
          'Soil Chemistry, Altitude & Solar Drying Flavor Impact'
        ],
        btn1Text: 'Discover Cacao Sourcing →',
        btn1Href: 'about.php',
        btn2Text: 'Read Origin Articles',
        btn2Href: 'blog.php'
      },
      'workshops-academy': {
        badge: '🎓 PROFESSIONAL ACADEMY',
        title: 'Workshops & Academy',
        desc: 'Hands-on and online certification masterclasses led by expert Aarti Saluja Sahni for home bakers, pastry chefs, and craft chocolate entrepreneurs.',
        highlights: [
          'Professional Bean-to-Bar Certification (Mumbai & Online)',
          'Bonbon Spraying, Moulding & Ganache Emulsion Science',
          'Commercial Chocolatier Setup & Scaling Blueprint'
        ],
        btn1Text: 'View Workshop Schedule →',
        btn1Href: 'workshops.php',
        btn2Text: 'Contact Aarti',
        btn2Href: 'contact.php'
      }
    };

    function openTableModal(key) {
      const data = TABLE_MODAL_DATA[key];
      if (!data) return;

      document.getElementById('modal-table-badge').innerText = data.badge;
      document.getElementById('modal-table-title').innerText = data.title;
      document.getElementById('modal-table-desc').innerText = data.desc;

      const listContainer = document.getElementById('modal-table-list');
      listContainer.innerHTML = data.highlights.map(item => `<li>${item}</li>`).join('');

      const btn1 = document.getElementById('modal-table-btn-primary');
      btn1.innerText = data.btn1Text;
      btn1.href = data.btn1Href;
      if (data.btn1Action) {
        btn1.onclick = (e) => { e.preventDefault(); data.btn1Action(); };
      } else {
        btn1.onclick = null;
      }

      const btn2 = document.getElementById('modal-table-btn-secondary');
      btn2.innerText = data.btn2Text;
      btn2.href = data.btn2Href;

      const modal = document.getElementById('table-interactive-modal');
      modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeTableModal() {
      const modal = document.getElementById('table-interactive-modal');
      modal.classList.remove('active');
      document.body.style.overflow = '';
    }

    function triggerHeroSearch(query) {
      const input = document.getElementById('hero-search-input');
      if (input) {
        input.value = query;
      }
      handleHeroSearchSubmit(new Event('submit'));
    }

    function handleHeroSearchSubmit(e) {
      if (e && e.preventDefault) e.preventDefault();
      const query = document.getElementById('hero-search-input')?.value?.trim();
      if (!query) return;

      if (typeof sendTroubleshootQuery === 'function') {
        sendTroubleshootQuery(query);
      } else {
        window.location.href = `chocopedia.php?q=${encodeURIComponent(query)}`;
      }
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeTableModal();
    });
  </script>

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
      <p class="section-subtitle" style="max-width: 720px; margin: 0 auto 40px; text-align: center;">
        Design your custom artisanal chocolate bar. Choose your cacao base, target cocoa percentage, batch size, and up to 3 gourmet inclusions. 
        Our <strong>CocoaGenius AI Alchemist</strong> formulates exact ingredient ratios, a 5-step process timeline, and professional tempering profiles.
      </p>

      <!-- Preset Quick Inspiration Chips -->
      <div class="chocolab-presets-wrapper">
        <span class="chocolab-presets-label">⚡ Chef's Instant Presets:</span>
        <div class="chocolab-presets-grid">
          <button type="button" class="chocolab-preset-btn" onclick="applyChocolabPreset('Dark Chocolate', 72, ['Bird\'s Eye Chili', 'Sea Salt'])">
            🌶️ Aztec Fire (72% Dark + Chili &amp; Salt)
          </button>
          <button type="button" class="chocolab-preset-btn" onclick="applyChocolabPreset('Dark Chocolate', 85, ['Orange Zest', 'Roasted Almonds'])">
            🍊 Citrus Almond (85% Dark + Orange &amp; Almond)
          </button>
          <button type="button" class="chocolab-preset-btn" onclick="applyChocolabPreset('Milk Chocolate', 48, ['Cardamom', 'Rose Petals'])">
            🌸 Persian Rose Milk (48% Milk + Cardamom &amp; Rose)
          </button>
          <button type="button" class="chocolab-preset-btn" onclick="applyChocolabPreset('White Chocolate', 34, ['Peppermint', 'Sea Salt'])">
            ❄️ Mint Frost (34% White + Peppermint &amp; Salt)
          </button>
        </div>
      </div>
      
      <div class="chocolab-layout" style="margin-top: 30px;">
        <!-- Control Panel -->
        <div class="chocolab-controls">
          <div class="chocolab-controls-header">
            <h3>🧪 Formulation Specs</h3>
            <span class="chocolab-badge">Craft Mode</span>
          </div>
          
          <div class="form-group">
            <label for="chocolab-base">1. Select Cacao Base</label>
            <select id="chocolab-base" onchange="updatePercentRangeHint()">
              <option value="Dark Chocolate" selected>Dark Chocolate (Rich &amp; Complex)</option>
              <option value="Milk Chocolate">Milk Chocolate (Creamy &amp; Sweet)</option>
              <option value="White Chocolate">White Chocolate (Buttery &amp; Smooth)</option>
              <option value="Ruby Chocolate">Ruby Chocolate (Fruity &amp; Tangy)</option>
              <option value="Oat Milk Vegan">Oat Milk Vegan (Plant-Based Creamy)</option>
            </select>
          </div>
          
          <div class="form-group">
            <div class="chocolab-label-row">
              <label for="chocolab-percent">2. Target Cacao Percentage</label>
              <span id="chocolab-percent-val" class="chocolab-val-tag">72% (Bittersweet)</span>
            </div>
            <input type="range" id="chocolab-percent" min="30" max="100" value="72" oninput="updatePercentRangeHint()">
            <div class="chocolab-range-labels">
              <span>30% (Sweet)</span>
              <span>65% (Medium)</span>
              <span>100% (Pure Cacao)</span>
            </div>
          </div>

          <div class="form-group">
            <label for="chocolab-batch">3. Batch Weight Target</label>
            <select id="chocolab-batch">
              <option value="500" selected>500g (Standard Craft Batch)</option>
              <option value="1000">1000g (1 kg Professional Batch)</option>
              <option value="250">250g (Micro Test Batch)</option>
            </select>
          </div>
          
          <div class="form-group">
            <div class="chocolab-label-row">
              <label>4. Gourmet Inclusions</label>
              <span id="chocolab-inc-counter" class="chocolab-count-tag">0 / 3 Selected</span>
            </div>
            <div class="inclusions-grid">
              <label class="inclusion-checkbox"><input type="checkbox" value="Sea Salt" name="inclusions" onchange="handleInclusionCheck(this)"> 🧂 Sea Salt</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Cardamom" name="inclusions" onchange="handleInclusionCheck(this)"> 🌿 Cardamom</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Lavender" name="inclusions" onchange="handleInclusionCheck(this)"> 🪻 Lavender</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Bird's Eye Chili" name="inclusions" onchange="handleInclusionCheck(this)"> 🌶️ Bird's Eye Chili</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Orange Zest" name="inclusions" onchange="handleInclusionCheck(this)"> 🍊 Orange Zest</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Rose Petals" name="inclusions" onchange="handleInclusionCheck(this)"> 🌹 Rose Petals</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Peppermint" name="inclusions" onchange="handleInclusionCheck(this)"> 🍃 Peppermint</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Roasted Almonds" name="inclusions" onchange="handleInclusionCheck(this)"> 🌰 Roasted Almonds</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Cacao Nibs" name="inclusions" onchange="handleInclusionCheck(this)"> 🟤 Cacao Nibs</label>
              <label class="inclusion-checkbox"><input type="checkbox" value="Espresso Powder" name="inclusions" onchange="handleInclusionCheck(this)"> ☕ Espresso Powder</label>
            </div>
          </div>
          
          <button class="btn-primary chocolab-submit-btn" onclick="generateCustomBarFormula()">
            <span>⚡ Formulate Master Recipe</span>
          </button>
        </div>
        
        <!-- Formulation Output -->
        <div class="chocolab-output">
          <!-- Placeholder State -->
          <div id="chocolab-placeholder">
            <div class="chocolab-placeholder-icon">🧪</div>
            <h4>Alchemist Playground Ready</h4>
            <p>Select your specs or click an instant preset on the left, then hit <strong>"Formulate Master Recipe"</strong> to generate your precision chocolate formula.</p>
            
            <div class="chocolab-preview-pills">
              <span class="preview-pill">📊 Exact Gram Formula</span>
              <span class="preview-pill">👅 Tasting Profile</span>
              <span class="preview-pill">⏱️ 5-Step Process Timeline</span>
            </div>
          </div>
          
          <!-- Loader State -->
          <div id="chocolab-loader" style="display: none;">
            <div class="chocolab-loader-animation">
              <div class="chocolab-spinner"></div>
              <div class="ai-typing-indicator" style="margin-top: 15px;">
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
                <span class="ai-typing-dot"></span>
              </div>
            </div>
            <h4>CocoaGenius AI Alchemist at Work...</h4>
            <p id="chocolab-loader-status">Calculating fat-to-sugar crystallization ratios &amp; Form V tempering points...</p>
          </div>
          
          <!-- Results State -->
          <div id="chocolab-results" style="display: none; width: 100%;">
            <!-- Header Bar -->
            <div class="chocolab-results-header">
              <div class="chocolab-header-left">
                <span class="chocolab-badge-gold">✨ Master Formulation Sheet</span>
                <span id="chocolab-result-base" class="chocolab-results-base">72% Dark Chocolate (500g Batch)</span>
              </div>
              <div class="chocolab-header-actions">
                <button type="button" class="chocolab-action-btn" onclick="copyChocolabRecipe()" title="Copy Recipe text">
                  📋 Copy
                </button>
                <button type="button" class="chocolab-action-btn" onclick="printChocolabRecipe()" title="Print Recipe Sheet">
                  🖨️ Print
                </button>
              </div>
            </div>

            <!-- Recipe Title & Story -->
            <div class="chocolab-title-block">
              <h3 id="chocolab-result-name">Aztec Velvet &amp; Bird's Eye Flame</h3>
              <p id="chocolab-result-desc" class="chocolab-result-desc"></p>
            </div>
            
            <!-- Section 1: Exact Batch Ratios Grid -->
            <div class="chocolab-section-block">
              <h5 class="chocolab-sub-heading">⚖️ Precision Ingredient Ratios (<span id="chocolab-batch-display">500g</span> Total)</h5>
              
              <!-- Multi-Segment Visual Composition Bar -->
              <div class="chocolab-bar-composition">
                <div class="comp-legend-row">
                  <span class="legend-item leg-mass"><span class="legend-dot"></span> Cacao Mass (<span id="comp-val-mass">54%</span>)</span>
                  <span class="legend-item leg-butter"><span class="legend-dot"></span> Cocoa Butter (<span id="comp-val-butter">18%</span>)</span>
                  <span class="legend-item leg-sugar"><span class="legend-dot"></span> Organic Sugar (<span id="comp-val-sugar">25%</span>)</span>
                  <span class="legend-item leg-inc"><span class="legend-dot"></span> Inclusions (<span id="comp-val-inc">3%</span>)</span>
                </div>
                <div class="composition-bar-track">
                  <div id="comp-bar-mass" class="comp-segment seg-mass" style="width: 54%;" title="Cacao Mass"></div>
                  <div id="comp-bar-butter" class="comp-segment seg-butter" style="width: 18%;" title="Cocoa Butter"></div>
                  <div id="comp-bar-sugar" class="comp-segment seg-sugar" style="width: 25%;" title="Organic Sugar"></div>
                  <div id="comp-bar-inc" class="comp-segment seg-inc" style="width: 3%;" title="Inclusions"></div>
                </div>
              </div>

              <div id="chocolab-result-ratios" class="chocolab-ratios-grid">
                <!-- Injected by JS -->
              </div>
            </div>

            <!-- Section 2: Sensory & Tasting Profile -->
            <div class="chocolab-section-block">
              <h5 class="chocolab-sub-heading">👅 Sensory Profile</h5>
              <div id="chocolab-result-sensory" class="chocolab-sensory-grid">
                <!-- Injected by JS -->
              </div>
            </div>

            <!-- Section 3: Structured 5-Step Process Timeline -->
            <div class="chocolab-section-block">
              <h5 class="chocolab-sub-heading">⏱️ 5-Step Master Crafting Process</h5>
              <div id="chocolab-result-steps" class="chocolab-timeline">
                <!-- Injected by JS -->
              </div>
            </div>

            <!-- Section 4: Master Chocolatier Pro Tip -->
            <div class="chocolab-protip-box">
              <div class="protip-header">
                <span class="protip-icon">🎓</span>
                <h6>Master Chocolatier Pro Tip (Aarti Saluja Sahni)</h6>
              </div>
              <p id="chocolab-result-protip"></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 04: Featured Workshops -->
  <section id="featured-workshops">
    <div class="section">
      <div class="workshops-section-header">
        <div class="section-label">04 / ACADEMY</div>
        <h2 class="section-title">Workshops &amp; Masterclasses</h2>
        <div class="divider"></div>
        <p class="section-subtitle">
          A collection of premium, science-first chocolate workshops and masterclasses. Learn bean-to-bar making, tempering science, and recipe formulation from expert Aarti Saluja Sahni.
        </p>
      </div>
      
      <div class="grid-3" id="home-workshops" style="margin-top: 40px;">
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
