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

  <!-- Split Hero with Full Video Background -->
  <section id="hero">
    <!-- Full Background Video (orb rotates on right side) -->
    <video class="hero-video-bg" autoplay loop muted playsinline preload="auto">
      <source src="assets/cocuapod_circle.mp4" type="video/mp4">
    </video>
    <div class="hero-video-overlay"></div>

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

        <!-- Ingredient Spotlight Feature Card integrated into left hero column -->
        <div class="hero-ingredient-spotlight-card fade-up-d3" onclick="openTableModal('bean-to-bar')">
          <div class="spotlight-header-row">
            <span class="spotlight-tag">🌱 INGREDIENT SPOTLIGHT</span>
          </div>
          <h3 class="spotlight-title">Cocoa Butter</h3>
          <p class="spotlight-desc">The golden fat that gives chocolate its smoothness and soul.</p>
          <a href="javascript:void(0)" class="spotlight-link" onclick="openTableModal('bean-to-bar'); event.stopPropagation();">
            <span>Explore Ingredient</span>
            <span class="link-arrow">→</span>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- INTERACTIVE CHOCOLATE TABLE SECTION -->
  <section class="chocolate-table-sec">
    <div class="chocolate-table-inner">
      <div class="chocolate-table-header">
        <div class="chocolate-table-label">🌿 INTERACTIVE EXPERIENCE</div>
        <h2 class="chocolate-table-title">Explore the Chocolate Table</h2>
        <p class="chocolate-table-subtitle">Click any object on the artisan workbench to explore a world of chocolate knowledge, techniques, and science.</p>
        <div class="chocolate-table-divider"></div>
      </div>

      <div class="chocolate-table-wrapper">
        <img src="assets/chocolate_table_interactive.png" alt="Artisan Chocolate Workbench with cocoa beans, notebook, chocolate bar, and tools" class="chocolate-table-bg-img">

        <!-- Interactive Hotspot Pins — positions match the generated image exactly -->
        <!-- 1. Bean to Bar → Wooden bowl of cacao beans (top-center-left) -->
        <div class="table-hotspot" style="top: 18%; left: 32%;" onclick="openTableModal('bean-to-bar')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Bean to Bar</span>
          </div>
        </div>

        <!-- 2. Knowledge Hub → Open leather recipe journal (center of table) -->
        <div class="table-hotspot" style="top: 52%; left: 48%;" onclick="openTableModal('knowledge-hub')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Knowledge Hub</span>
          </div>
        </div>

        <!-- 3. Chocolate Lab → Brass magnifying glass / microscope (top-right) -->
        <div class="table-hotspot" style="top: 14%; left: 80%;" onclick="openTableModal('chocolate-lab')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Chocolate Lab</span>
          </div>
        </div>

        <!-- 4. Recipes & Formulations → Dark chocolate bar broken into pieces (right) -->
        <div class="table-hotspot" style="top: 38%; left: 86%;" onclick="openTableModal('recipes-formulations')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Recipes &amp; Formulations</span>
          </div>
        </div>

        <!-- 5. Techniques → Palette knife, spatula & bench scraper (bottom-left) -->
        <div class="table-hotspot" style="top: 82%; left: 28%;" onclick="openTableModal('techniques')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Techniques</span>
          </div>
        </div>

        <!-- 6. Origins & Atlas → Cacao pod split open + vintage map (far left) -->
        <div class="table-hotspot" style="top: 58%; left: 10%;" onclick="openTableModal('origins-atlas')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Origins &amp; Atlas</span>
          </div>
        </div>

        <!-- 7. Workshops & Academy → Whisk + chocolate mould (bottom-right) -->
        <div class="table-hotspot" style="top: 82%; left: 78%;" onclick="openTableModal('workshops-academy')">
          <div class="hotspot-pin">
            <span class="hotspot-dot"></span>
            <span>Workshops &amp; Academy</span>
          </div>
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

  <!-- Section 01: Featured Workshops -->
  <section id="featured-workshops">
    <div class="section">
      <div class="workshops-section-header">
        <div class="section-label">01 / ACADEMY</div>
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


  <!-- Section 02: Interactive Flavor Wheel Section -->
  <section id="flavor-wheel-sec">
    <div class="wheel-layout">
      <!-- Title Column -->
      <div class="wheel-title-col">
        <span class="section-label" style="color:var(--gold); display:block; margin-bottom:8px;">02 / SCIENCE</span>
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
      <div class="wheel-cta-container" style="grid-column: 1 / -1; margin-top: 45px; text-align: center;">
        <a href="blog.php" class="btn-hero-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;">
          <span>Read Latest Articles</span>
          <span>→</span>
        </a>
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
            color: "#361b20", // Deep Cacao Mahogany
            accentColor: "#d4af37",
            textLight: "#ffffff",
            startAngle: 180,
            endAngle: 300,
            icon: `<path d="M-6 -2c0-3.3 2.7-6 6-6s6 2.7 6 6v2c0 2.2-1.8 4-4 4h-4c-2.2 0-4-1.8-4-4v-2zm12 6h2v-2h-2v2zM0 -14v2M-3 -13v1.5M3 -13v1.5" stroke="#d4af37" fill="none" stroke-width="1.4"/>`,
            subsectors: [
              { label: "Earthy", startAngle: 180, endAngle: 200, icon: `<path d="M0 -6 C4 -2 4 4 0 6 C-4 4 -4 -2 0 -6 Z M0 -6 L0 6" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Spicy", startAngle: 200, endAngle: 220, icon: `<path d="M0 -6 L1.5 -2 L6 -2 L2.5 1 L4 5 L0 2.5 L-4 5 L-2.5 1 L-6 -2 L-1.5 -2 Z" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Sweet", startAngle: 220, endAngle: 240, icon: `<path d="M-5 -5 L5 5 M-5 5 L5 -5 M-2.5 0 A 2.5 2.5 0 1 0 2.5 0 A 2.5 2.5 0 1 0 -2.5 0 Z" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Nutty", startAngle: 240, endAngle: 260, icon: `<path d="M-3 -2 C-3 2 3 2 3 -2 C3 -4 -3 -4 -3 -2 Z M-3 -2 Q 0 -6 3 -2" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Floral", startAngle: 260, endAngle: 280, icon: `<circle cx="0" cy="0" r="2" fill="#ffffff"/><circle cx="0" cy="-4" r="1.8" fill="none" stroke="#ffffff" stroke-width="1.3"/><circle cx="4" cy="0" r="1.8" fill="none" stroke="#ffffff" stroke-width="1.3"/><circle cx="0" cy="4" r="1.8" fill="none" stroke="#ffffff" stroke-width="1.3"/><circle cx="-4" cy="0" r="1.8" fill="none" stroke="#ffffff" stroke-width="1.3"/>` },
              { label: "Fruity", startAngle: 280, endAngle: 300, icon: `<path d="M-1.5 -4 A 2.5 2.5 0 1 0 -1.5 1 A 2.5 2.5 0 1 0 -1.5 -4 Z M1.5 -1 A 2.5 2.5 0 1 0 1.5 4 A 2.5 2.5 0 1 0 1.5 -1 Z M-1.5 -1 Q 0 -6 3 -5" stroke="#ffffff" fill="none" stroke-width="1.3"/>` }
            ]
          },
          {
            id: "process",
            label: "2. PROCESS",
            color: "#142e20", // Dark Forest Emerald
            accentColor: "#569269",
            textLight: "#ffffff",
            startAngle: 300,
            endAngle: 420,
            icon: `<path d="M-3 0 a3 3 0 1 0 6 0 a3 3 0 1 0 -6 0 M7 0 a1.6 1.6 0 0 0 .3 1.8 l.1.1 a2 2 0 1 1 -2.8 2.8 l-.1-.1 a1.6 1.6 0 0 0 -1.8-.3 a1.6 1.6 0 0 0 -1 1.5 v.1 a2 2 0 0 1 -4 0 v-.1 a1.6 1.6 0 0 0 -1-1.5 a1.6 1.6 0 0 0 -1.8.3 l-.1.1 a2 2 0 1 1 -2.8 -2.8 l.1-.1 a1.6 1.6 0 0 0 .3 -1.8 a1.6 1.6 0 0 0 -1.5 -1 h-.1 a2 2 0 0 1 0 4 h.1 a1.6 1.6 0 0 0 1.5 -1 a1.6 1.6 0 0 0 -.3 -1.8 l-.1-.1 a2 2 0 1 1 2.8 -2.8 l.1.1 a1.6 1.6 0 0 0 1.8 .3 a1.6 1.6 0 0 0 1 -1.5 v-.1 a2 2 0 0 1 4 0 v.1 a1.6 1.6 0 0 0 1 1.5 a1.6 1.6 0 0 0 1.8 -.3 l.1-.1 a2 2 0 1 1 2.8 2.8 l-.1.1 a1.6 1.6 0 0 0 -.3 1.8 a1.6 1.6 0 0 0 1.5 1 h.1 a2 2 0 0 1 0 4 h-.1 a1.6 1.6 0 0 0 -1.5 1 z" stroke="#7acb92" fill="none" stroke-width="1.4"/>`,
            subsectors: [
              { label: "Roasted", startAngle: 300, endAngle: 324, icon: `<path d="M0 5 C-3.5 5 -4 2.5 -2.5 0 C-3 -1.5 -1.5 -5 0 -7 C1.5 -5 3 -1.5 2.5 0 C4 2.5 3.5 5 0 5 Z" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Fermented", startAngle: 324, endAngle: 348, icon: `<path d="M-2.5 -5 H2.5 V-3 H-2.5 Z M-3 -3 H3 V4 C3 5 2.5 5.5 -3 5.5 H-3 Z" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Dried", startAngle: 348, endAngle: 372, icon: `<circle cx="0" cy="0" r="3" stroke="#ffffff" fill="none" stroke-width="1.3"/><path d="M0 -5 V-7 M0 5 V7 M-5 0 H-7 M5 0 H7" stroke="#ffffff" stroke-width="1.3"/>` },
              { label: "Conched", startAngle: 372, endAngle: 396, icon: `<path d="M-4 4 V-4 L-1.5 -1.5 V-4 L1 -1.5 V-4 L4 -0.5 V4 Z M-1.5 4 V1.5 H1.5 V4" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Tempered", startAngle: 396, endAngle: 420, icon: `<path d="M-1.5 -6 H1.5 V2.5 A 2.5 2.5 0 1 1 -1.5 2.5 Z M0 -3.5 V1" stroke="#ffffff" fill="none" stroke-width="1.3"/>` }
            ]
          },
          {
            id: "taste",
            label: "3. TASTE PROFILE",
            color: "#382a18", // Roasted Cocoa Amber
            accentColor: "#e5c453",
            textLight: "#ffffff",
            startAngle: 60,
            endAngle: 180,
            icon: `<path d="M-6 0c0 0 3 3 6 3s6-3 6-3M-5 0c0 4 2.5 7 5 7s5-3 5-7" stroke="#e5c453" fill="none" stroke-width="1.4"/>`,
            subsectors: [
              { label: "Smooth", startAngle: 60, endAngle: 84, icon: `<path d="M-5 -1.5 Q-2.5 -4 0 -1.5 T5 -1.5 M-5 1.5 Q-2.5 -1 0 1.5 T5 1.5" stroke="#ffffff" fill="none" stroke-width="1.3" stroke-linecap="round"/>` },
              { label: "Creamy", startAngle: 84, endAngle: 108, icon: `<path d="M0 -6 C3 -2.5 4 1 2.5 3.5 C0 6 -2.5 6 -2.5 3.5 C-4 1 -3 -2.5 0 -6 Z" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Salty", startAngle: 108, endAngle: 132, icon: `<path d="M-2.5 -3.5 H2.5 V5 H-2.5 Z M-1.5 -5 H1.5 V-3.5 H-1.5 Z M-1 -1 H1 M-1 1.5 H1" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Sour", startAngle: 132, endAngle: 156, icon: `<path d="M0 -5 A 5 5 0 1 0 0 5 A 5 5 0 1 0 0 -5 Z M0 0 L4 0 M0 0 L-2 -3.5 M0 0 L-2 3.5" stroke="#ffffff" fill="none" stroke-width="1.3"/>` },
              { label: "Other", startAngle: 156, endAngle: 180, icon: `<circle cx="-4" cy="0" r="1.2" fill="#ffffff"/><circle cx="0" cy="0" r="1.2" fill="#ffffff"/><circle cx="4" cy="0" r="1.2" fill="#ffffff"/>` }
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

  <!-- Section 03: Journal & Technical Deep Dives -->
  <section style="background:var(--ivory); padding: 80px 24px;">
    <div class="section" style="max-width:1140px; margin:0 auto; text-align:center;">
      <div class="section-label">03 / JOURNAL</div>
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

  <!-- Visually Hidden SEO Content Block for Search Engines -->
  <section class="visually-hidden-seo-block" style="position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;">
    <div class="section">
      <h2>The Science of Indian Bean-to-Bar Chocolate</h2>
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
        Explore our professional <a href="workshops">Chocolate Academy India Workshops</a>, browse our curated <a href="shop">Chocolate Shop</a> for starter kits and single-origin ingredients, or read our latest <a href="blog">Chocolate Blog India articles</a> to start your craft chocolate learning journey.
      </p>
    </div>
  </section>

  <!-- Section 04: Newsletter CTA -->
  <section id="newsletter-section">
    <div class="inner">
      <div class="sub-label">04 / COMMUNITY</div>
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
