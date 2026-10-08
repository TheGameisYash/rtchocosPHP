<?php
  // Fallback router delegation for servers running without router.php
  $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/';
  if ($reqPath !== '/' && $reqPath !== '/index.php' && file_exists(__DIR__ . '/router.php')) {
      $handled = include __DIR__ . '/router.php';
      if ($handled === true) {
          exit;
      }
  }

  $pageTitle = "Chocolate Blog India & Chocolate Academy | RT Chocos Bean-to-Bar Learning";
  $pageDescription = "RT Chocos is the premier chocolate blog in India and professional bean-to-bar chocolate academy. Discover Indian bean-to-bar chocolate making, tempering science, workshops, and courses by expert Aarti Saluja Sahni.";
  $pageKeywords = "chocolate blog india, chocolate academy india, indian bean to bar chocolate, bean to bar chocolate, chocolate course india, chocolate workshops india, learn chocolate making india, cocoa science blog india, craft chocolate india, chocolate education india, chocolate blogging india, tempering chocolate course, chocolate consultant Mumbai, RT Chocos";
  $pathPrefix = "";
  $isHome = true;

  // Load database connection and fetch the latest 5 cached insights for server-side scrolling ticker rendering
  require_once $pathPrefix . 'includes/db.php';
  $tickerCacheFile = __DIR__ . '/data/cache/ticker_insights.json';
  $cachedInsights = null;
  if (file_exists($tickerCacheFile) && (time() - filemtime($tickerCacheFile) < 1800)) {
      $tickerRaw = @file_get_contents($tickerCacheFile);
      if ($tickerRaw !== false) {
          $cachedInsights = json_decode($tickerRaw, true);
      }
  }
  if (!is_array($cachedInsights) || empty($cachedInsights)) {
      try {
          $pdo = get_db();
          $insightStmt = $pdo->query("SELECT insight_text FROM ai_insights ORDER BY id DESC LIMIT 5");
          $cachedInsights = $insightStmt->fetchAll(PDO::FETCH_COLUMN);
          if (!empty($cachedInsights)) {
              $cacheDir = dirname($tickerCacheFile);
              if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
              @file_put_contents($tickerCacheFile, json_encode($cachedInsights, JSON_UNESCAPED_SLASHES));
          }
      } catch (Exception $e) {
          $cachedInsights = [];
      }
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

  // Load dynamic AI ingredient spotlight (refreshed every 6 hours via OpenRouter AI)
  require_once $pathPrefix . 'includes/ingredient_spotlight.php';
  try {
      $activeSpotlight = get_current_ingredient_spotlight();
  } catch (Exception $e) {
      $activeSpotlight = get_curated_spotlight_fallback();
  }

  include $pathPrefix . 'includes/header.php';
?>

<!-- Luxury Landing Page Scoped Stylesheet -->
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>css/landing-luxury.css?v=<?php echo file_exists(__DIR__ . '/css/landing-luxury.css') ? filemtime(__DIR__ . '/css/landing-luxury.css') : time(); ?>">

<!-- --- HOME PAGE --- -->
<div id="page-home" class="page active">
  <!-- Top Viewport Scroll Journey Progress -->
  <div id="landing-scroll-progress" aria-hidden="true"></div>

  <!-- Ambient Luxury Cursor Aurora Glow (Desktop Only) -->
  <div id="landing-cursor-glow" aria-hidden="true"></div>

  <!-- Multi-Layer Botanical Scroll Parallax Leaves -->
  <div class="parallax-leaf leaf-gold leaf-1" data-speed="0.22" data-base-rot="-15" aria-hidden="true"></div>
  <div class="parallax-leaf leaf-emerald leaf-2" data-speed="0.32" data-base-rot="25" aria-hidden="true"></div>
  <div class="parallax-leaf leaf-cacao leaf-3" data-speed="0.18" data-base-rot="45" aria-hidden="true"></div>
  <div class="parallax-leaf leaf-gold leaf-4" data-speed="0.28" data-base-rot="-35" aria-hidden="true"></div>
  <div class="parallax-leaf leaf-emerald leaf-5" data-speed="0.15" data-base-rot="18" aria-hidden="true"></div>

  <!-- Split Hero with Full Video Background -->
  <section id="hero">
    <!-- Full Native Resolution Video Background -->
    <video class="hero-video-bg" autoplay loop muted playsinline preload="auto">
      <source src="assets/cocuapod_circle.mp4" type="video/mp4">
    </video>

    <div class="deco-circle-1"></div>
    <div class="deco-circle-2"></div>
    <div class="deco-radial"></div>
    <div class="split-hero-container">
      <div class="split-hero-content">
        <span class="hero-tag fade-up">✨ Premier Chocolate Academy &amp; Lab</span>
        <h1 class="fade-up-d1">Unlocking Cacao's <em>Science &amp; Art</em></h1>
        <p class="fade-up-d2">An independent Indian chocolate learning academy covering bean-to-bar craftsmanship, cacao formulation science, and professional masterclasses.</p>
        <div class="hero-btns fade-up-d3">
          <a href="workshops.php" class="btn-hero-primary">
            <span>Start Learning</span>
            <span class="btn-arrow">→</span>
          </a>
          <button onclick="toggleAiDrawer()" class="btn-hero-outline">
            <span class="btn-ai-sparkle">✨</span>
            <span>Ask CocoaGenius AI</span>
          </button>
        </div>

        <!-- Ingredient Spotlight Feature Card (Precision Formulation Intelligence) -->
        <div class="hero-ingredient-spotlight-card fade-up-d3" onclick="openTableModal('ingredient-spotlight')" title="Click to view full scientific profile">
          <div class="spotlight-header-row">
            <div class="spotlight-tag-group">
              <span class="spotlight-pod-icon">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2.2C6.8 2.2 4 6 4 10.5c0 3.2 2.2 6.5 6 8 3.8-1.5 6-4.8 6-8C16 6 13.2 2.2 10 2.2z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                  <path d="M10 2.2v16.3M7.2 7.2c0 2.2 1.3 4 2.8 4s2.8-1.8 2.8-4" stroke="currentColor" stroke-width="1.1"/>
                </svg>
              </span>
              <span class="spotlight-tag"><?= htmlspecialchars($activeSpotlight['clean_tag'] ?? 'INGREDIENT SPOTLIGHT') ?></span>
            </div>
            <span class="spotlight-ai-badge" title="AI Rotates Every 6 Hours via OpenRouter">
              <span class="spotlight-pulse"></span>
              <span>AI Live 6h</span>
            </span>
          </div>

          <div class="spotlight-title-wrap">
            <h3 class="spotlight-title"><?= htmlspecialchars($activeSpotlight['ingredient_name'] ?? 'Cocoa Butter') ?></h3>
            <?php if (!empty($activeSpotlight['level_number'])): ?>
              <span class="spotlight-level-tag">L<?= (int)$activeSpotlight['level_number'] ?></span>
            <?php endif; ?>
          </div>

          <p class="spotlight-desc"><?= htmlspecialchars($activeSpotlight['short_desc'] ?? 'The golden fat that gives chocolate its smoothness and soul.') ?></p>

          <?php if (!empty($activeSpotlight['flavor_notes_list']) && is_array($activeSpotlight['flavor_notes_list'])): ?>
            <div class="spotlight-notes-row">
              <?php foreach (array_slice($activeSpotlight['flavor_notes_list'], 0, 3) as $note): ?>
                <span class="spotlight-note-chip">
                  <span class="chip-pip"></span>
                  <?= htmlspecialchars($note) ?>
                </span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="spotlight-footer-row">
            <a href="javascript:void(0)" class="spotlight-link" onclick="openTableModal('ingredient-spotlight'); event.stopPropagation();">
              <span>Explore Scientific Profile</span>
              <span class="link-arrow">→</span>
            </a>
            <span class="spotlight-dossier-pill">Formulation Dossier</span>
          </div>
        </div>
      </div>
    </div>
  </section>
 
  <!-- ====== TRUSTED KNOWLEDGE. PROVEN EXPERIENCE. STRIP ====== -->
  <section class="home-trust-strip-sec">
    <div class="home-trust-inner">
      <div class="home-trust-card">
        
        <!-- Top Cacao Pod Emblem Header -->
        <div class="trust-top-header">
          <div class="trust-pod-badge">
            <svg viewBox="0 0 24 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 2C12 2 4 8 4 17c0 8 8 13 8 13s8-5 8-13c0-9-8-15-8-15z" stroke="#b8860b" stroke-width="1.3"/>
              <path d="M12 2v28" stroke="#b8860b" stroke-width="1.1"/>
              <path d="M8 8c-2 3-2 8 0 12M16 8c2 3 2 8 0 12" stroke="#b8860b" stroke-width="1.1"/>
            </svg>
          </div>

          <h2 class="trust-main-heading">TRUSTED KNOWLEDGE. PROVEN EXPERIENCE.</h2>

          <div class="trust-sub-line">
            <span class="sub-rule"></span>
            <span class="sub-diamond">◆</span>
            <span class="sub-rule"></span>
          </div>
        </div>

        <!-- 5 Pillars Grid -->
        <div class="trust-pillars-grid">

          <!-- 1. 13+ YEARS OF EXPERIENCE -->
          <div class="trust-col">
            <div class="trust-icon-badge">
              <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 6C20 6 12 11 12 21c0 9 8 13 8 13s8-4 8-13c0-10-8-15-8-15z" stroke="#b8860b" stroke-width="1.5" stroke-linejoin="round" fill="rgba(184, 134, 11, 0.06)"/>
                <path d="M20 6v28" stroke="#b8860b" stroke-width="1.2"/>
                <path d="M15.5 13c-2 3-2 8 0 13M24.5 13c2 3 2 8 0 13" stroke="#b8860b" stroke-width="1.2"/>
                <path d="M23 11c3-6 9-6 10-4 1 2 0 8-6 10" fill="#245c36" fill-opacity="0.2" stroke="#245c36" stroke-width="1.3" stroke-linecap="round"/>
                <path d="M24 10.5c3 1.5 6 4 7 5.5" stroke="#245c36" stroke-width="1" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="trust-stat-title">13+ YEARS</div>
            <div class="trust-stat-subtitle">OF EXPERIENCE</div>
          </div>

          <!-- 2. THOUSANDS OF PROFESSIONALS TRAINED -->
          <div class="trust-col">
            <div class="trust-icon-badge">
              <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="11.5" r="4.5" fill="#143322" stroke="#143322" stroke-width="1.2"/>
                <path d="M11 34v-7c0-4.5 4-7 9-7s9 2.5 9 7v7" stroke="#b8860b" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M15 20.5l5 7 5-7" stroke="#b8860b" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M17.5 20.5v13.5M22.5 20.5v13.5" stroke="#b8860b" stroke-width="1" stroke-linecap="round"/>
                <path d="M20 20.5v3.5" stroke="#143322" stroke-width="1.8" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="trust-stat-title">THOUSANDS</div>
            <div class="trust-stat-subtitle">OF PROFESSIONALS TRAINED</div>
          </div>

          <!-- 3. INDUSTRY COLLABORATIONS -->
          <div class="trust-col">
            <div class="trust-icon-badge">
              <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 7h12c1.4 0 2.5 1 2.5 2.2v4.5c0 1.2-1.1 2.2-2.5 2.2h-3.5l-2.5 2.5v-2.5h-6c-1.4 0-2.5-1-2.5-2.2V9.2c0-1.2 1.1-2.2 2.5-2.2z" stroke="#b8860b" stroke-width="1.2" fill="none" stroke-linejoin="round"/>
                <circle cx="20" cy="22" r="3" stroke="#143322" stroke-width="1.3" fill="none"/>
                <path d="M14.5 33c0-3 2.5-5 5.5-5s5.5 2 5.5 5" stroke="#143322" stroke-width="1.3" stroke-linecap="round"/>
                <circle cx="12" cy="24" r="2.3" stroke="#143322" stroke-width="1.1" fill="none"/>
                <path d="M7.5 33c0-2 1.8-3.6 4.5-3.6" stroke="#143322" stroke-width="1.1" stroke-linecap="round"/>
                <circle cx="28" cy="24" r="2.3" stroke="#143322" stroke-width="1.1" fill="none"/>
                <path d="M32.5 33c0-2-1.8-3.6-4.5-3.6" stroke="#143322" stroke-width="1.1" stroke-linecap="round"/>
              </svg>
            </div>
            <div class="trust-stat-title">INDUSTRY</div>
            <div class="trust-stat-subtitle">COLLABORATIONS</div>
          </div>

          <!-- 4. SCIENCE BACKED CONTENT -->
          <div class="trust-col">
            <div class="trust-icon-badge">
              <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20 11l8 4.6v9.2l-8 4.6-8-4.6v-9.2z" stroke="#143322" stroke-width="1.4" stroke-linejoin="round"/>
                <circle cx="20" cy="11" r="1.8" fill="#b8860b"/>
                <circle cx="28" cy="15.6" r="1.8" fill="#b8860b"/>
                <circle cx="28" cy="24.8" r="1.8" fill="#b8860b"/>
                <circle cx="20" cy="29.4" r="1.8" fill="#b8860b"/>
                <circle cx="12" cy="24.8" r="1.8" fill="#b8860b"/>
                <circle cx="12" cy="15.6" r="1.8" fill="#b8860b"/>
                <line x1="20" y1="11" x2="20" y2="7.5" stroke="#b8860b" stroke-width="1.1"/>
                <circle cx="20" cy="7.5" r="1.4" fill="#143322"/>
                <line x1="28" y1="24.8" x2="31.5" y2="26.8" stroke="#b8860b" stroke-width="1.1"/>
                <circle cx="31.5" cy="26.8" r="1.4" fill="#143322"/>
                <line x1="12" y1="24.8" x2="8.5" y2="26.8" stroke="#b8860b" stroke-width="1.1"/>
                <circle cx="8.5" cy="26.8" r="1.4" fill="#143322"/>
              </svg>
            </div>
            <div class="trust-stat-title">SCIENCE</div>
            <div class="trust-stat-subtitle">BACKED CONTENT</div>
          </div>

          <!-- 5. INDEPENDENT & UNBIASED -->
          <div class="trust-col">
            <div class="trust-icon-badge">
              <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="20" cy="20" r="12" stroke="#143322" stroke-width="1.4"/>
                <ellipse cx="20" cy="20" rx="5.5" ry="10" stroke="#b8860b" stroke-width="1.2"/>
                <line x1="20" y1="6" x2="20" y2="34" stroke="#143322" stroke-width="1.2"/>
                <line x1="15" y1="20" x2="25" y2="20" stroke="#b8860b" stroke-width="1.2"/>
              </svg>
            </div>
            <div class="trust-stat-title">INDEPENDENT</div>
            <div class="trust-stat-subtitle">&amp; UNBIASED</div>
          </div>

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
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Bean to Bar</span>
          </div>
        </div>

        <!-- 2. Knowledge Hub → Open leather recipe journal (center of table) -->
        <div class="table-hotspot" style="top: 52%; left: 48%;" onclick="openTableModal('knowledge-hub')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Knowledge Hub</span>
          </div>
        </div>

        <!-- 3. Chocolate Lab → Brass magnifying glass / microscope (top-right) -->
        <div class="table-hotspot" style="top: 14%; left: 80%;" onclick="openTableModal('chocolate-lab')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Chocolate Lab</span>
          </div>
        </div>

        <!-- 4. Recipes & Formulations → Dark chocolate bar broken into pieces (right) -->
        <div class="table-hotspot" style="top: 38%; left: 86%;" onclick="openTableModal('recipes-formulations')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Recipes &amp; Formulations</span>
          </div>
        </div>

        <!-- 5. Techniques → Palette knife, spatula & bench scraper (bottom-left) -->
        <div class="table-hotspot" style="top: 82%; left: 28%;" onclick="openTableModal('techniques')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Techniques</span>
          </div>
        </div>

        <!-- 6. Origins & Atlas → Cacao pod split open + vintage map (far left) -->
        <div class="table-hotspot" style="top: 58%; left: 10%;" onclick="openTableModal('origins-atlas')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Origins &amp; Atlas</span>
          </div>
        </div>

        <!-- 7. Workshops & Academy → Whisk + chocolate mould (bottom-right) -->
        <div class="table-hotspot" style="top: 82%; left: 78%;" onclick="openTableModal('workshops-academy')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Innovation &amp; Wall</span>
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

  <!-- Dedicated Bean-to-Bar 9-Stage Guide Modal -->
  <div id="modal-beantobar-viewer" class="modal-beantobar-backdrop" onclick="if(event.target === this) closeBeanToBarGuideModal()">
    <div class="modal-beantobar-box" role="dialog" aria-modal="true" aria-labelledby="modal-b2b-title">
      <button type="button" class="modal-beantobar-close" onclick="closeBeanToBarGuideModal()" title="Close Guide" aria-label="Close Guide">&times;</button>
      
      <div class="modal-beantobar-header">
        <div class="modal-beantobar-badge">🌿 THE SPINE · NINE STAGES</div>
        <h3 class="modal-beantobar-title" id="modal-b2b-title">Bean to bar, <span class="ital">in full</span></h3>
        <p class="modal-beantobar-lede">
          Every stage below carries the variables that actually move flavour. Open any stage to inspect the technical parameters, temperatures, and durations.
        </p>
        <div class="modal-beantobar-meta-strip">
          <span class="meta-item"><span class="meta-dot"></span> 9 Sequential Stages</span>
          <span class="meta-item"><span class="meta-dot"></span> Single-Origin Standards</span>
          <span class="meta-item"><span class="meta-dot"></span> Real Working Parameters</span>
        </div>
      </div>

      <div class="modal-beantobar-scroll-body" id="modalB2bScrollBody">
        <div class="modal-spine-wrap">
          <div class="modal-spine">
            <div class="modal-spine-line"></div>
            <div class="modal-spine-fill" id="modalSpineFill"></div>
          </div>
          <div class="modal-stages" id="modalStages"></div>
        </div>
      </div>

      <div class="modal-beantobar-footer">
        <button type="button" class="modal-b2b-btn-back" onclick="closeBeanToBarGuideModal(); openTableModal('bean-to-bar');">
          &larr; Back to Chocolate Table
        </button>
        <a href="workshops.php" class="modal-b2b-btn-workshop">
          Book Workshop &rarr;
        </a>
      </div>
    </div>
  </div>

  <script>
    const TABLE_MODAL_DATA = {
      'ingredient-spotlight': {
        badge: <?= json_encode($activeSpotlight['tag'] ?? '🌱 INGREDIENT SPOTLIGHT') ?>,
        title: <?= json_encode($activeSpotlight['ingredient_name'] ?? 'Cocoa Butter') ?>,
        desc: <?= json_encode($activeSpotlight['short_desc'] ?? 'The golden fat that gives chocolate its smoothness and soul.') ?>,
        highlights: <?= json_encode(!empty($activeSpotlight['modal_highlights']) ? $activeSpotlight['modal_highlights'] : [
          'Estate Terroir: ' . ($activeSpotlight['origin_region'] ?? 'Artisan Single Origin'),
          'Sensory Profile: ' . implode(', ', $activeSpotlight['flavor_notes_list'] ?? ['Rich', 'Aromatic']),
          'Refreshed automatically every 6 hours by OpenRouter AI'
        ]) ?>,
        btn1Text: 'Explore Chocopedia →',
        btn1Href: 'chocopedia.php',
        btn2Text: 'Ask CocoaGenius AI',
        btn2Href: 'javascript:toggleAiDrawer()'
      },
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
        btn1Href: 'javascript:void(0)',
        btn1Action: () => {
          closeTableModal();
          if (typeof openBeanToBarGuideModal === 'function') {
            openBeanToBarGuideModal();
          }
        },
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
        btn1Text: 'Launch Chocolab →',
        btn1Href: 'workshops.php',
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
        btn1Href: 'gallery.php',
        btn2Text: 'Formulate Custom Bar',
        btn2Href: 'gallery.php'
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
      if (data.btn2Action) {
        btn2.onclick = (e) => { e.preventDefault(); data.btn2Action(); };
      } else {
        btn2.onclick = null;
      }

      const modal = document.getElementById('table-interactive-modal');
      const scrollBarWidth = window.innerWidth - document.documentElement.clientWidth;
      if (scrollBarWidth > 0) {
        document.body.style.paddingRight = scrollBarWidth + 'px';
      }
      document.body.style.overflow = 'hidden';
      modal.classList.add('active');
    }

    function closeTableModal() {
      const modal = document.getElementById('table-interactive-modal');
      modal.classList.remove('active');
      document.body.style.overflow = '';
      document.body.style.paddingRight = '';
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeTableModal();
    });
  </script>

  <!-- Section 11: Interactive Tempering Lab (Six Crystals. Only one is worth having.) -->
  <section id="bean-to-bar-stepper" class="tempering-lab-sec">
    <div class="tempering-lab-container">
      
      <!-- Compact Section Header -->
      <div class="tempering-lab-header">
        <div class="tempering-lab-badge">
          <span class="lab-badge-pulse"></span>
          <span>THE TEMPERING LAB &bull; MOLECULAR CRYSTALLIZATION</span>
        </div>
        <h2 class="tempering-lab-title">Six crystals. Only <em>one</em> is worth having.</h2>
        <p class="tempering-lab-desc">
          Cocoa butter sets into six polymorphs. Form V is the only lattice that delivers mirror gloss, crisp acoustic snap, and clean palate melt. Select a chocolate to inspect its thermal transition curve.
        </p>
      </div>

      <!-- Main Interactive Laboratory Console -->
      <div class="tempering-lab-card">
        
        <div class="tempering-lab-main-grid">
          
          <!-- Left Column: Controls, Metrics & Science Callout -->
          <div class="tempering-control-col">
            <div class="tempering-type-label">SELECT CHOCOLATE TYPE</div>
            
            <div class="chocolate-type-pills" role="tablist" aria-label="Chocolate Types">
              <button class="choco-pill-btn active" data-type="dark" role="tab" aria-selected="true">
                <span>DARK 70%</span>
              </button>
              <button class="choco-pill-btn" data-type="milk" role="tab" aria-selected="false">
                <span>MILK 38%</span>
              </button>
              <button class="choco-pill-btn" data-type="white" role="tab" aria-selected="false">
                <span>WHITE 32%</span>
              </button>
              <button class="choco-pill-btn" data-type="ruby" role="tab" aria-selected="false">
                <span>RUBY</span>
              </button>
            </div>

            <!-- 3 Stat Metric Displays -->
            <div class="tempering-metrics-row">
              <div class="tempering-metric-box">
                <span class="metric-deg-val" id="metric-melt">50°</span>
                <span class="metric-deg-sub">MELT OUT</span>
              </div>
              <div class="tempering-metric-box">
                <span class="metric-deg-val" id="metric-seed">28°</span>
                <span class="metric-deg-sub">SEED / COOL TO</span>
              </div>
              <div class="tempering-metric-box">
                <span class="metric-deg-val" id="metric-work">31.5°</span>
                <span class="metric-deg-sub">WORKING TEMP</span>
              </div>
            </div>

            <!-- Science Callout Explanation -->
            <div class="tempering-science-callout">
              <p class="science-callout-text" id="tempering-science-text">
                Dark carries no milk fat, so the cocoa butter crystallises cleanly and tolerates the highest working temperature. Hold above 32.5 °C and Form V starts melting out – the bar will set dull and soft.
              </p>
            </div>
          </div>

          <!-- Right Column: Interactive Temperature Curve SVG Chart -->
          <div class="tempering-chart-col">
            <div class="tempering-chart-wrapper">
              <svg class="tempering-svg-chart" viewBox="0 0 520 200" preserveAspectRatio="xMidYMid meet">
                <!-- 8 Gridlines & Y-Axis Labels (20° to 55°) -->
                <g class="chart-gridlines">
                  <text x="32" y="27" class="y-label">55°</text>
                  <line x1="42" y1="24" x2="490" y2="24" class="grid-line" />

                  <text x="32" y="47" class="y-label">50°</text>
                  <line x1="42" y1="44" x2="490" y2="44" class="grid-line" />

                  <text x="32" y="67" class="y-label">45°</text>
                  <line x1="42" y1="64" x2="490" y2="64" class="grid-line" />

                  <text x="32" y="87" class="y-label">40°</text>
                  <line x1="42" y1="84" x2="490" y2="84" class="grid-line" />

                  <text x="32" y="107" class="y-label">35°</text>
                  <line x1="42" y1="104" x2="490" y2="104" class="grid-line" />

                  <text x="32" y="127" class="y-label">30°</text>
                  <line x1="42" y1="124" x2="490" y2="124" class="grid-line" />

                  <text x="32" y="147" class="y-label">25°</text>
                  <line x1="42" y1="144" x2="490" y2="144" class="grid-line" />

                  <text x="32" y="167" class="y-label">20°</text>
                  <line x1="42" y1="164" x2="490" y2="164" class="grid-line baseline-grid" />
                </g>

                <!-- Main Tempering Curve Path -->
                <path id="curveLinePath" class="curve-line" d="M 46 156 L 90 44 L 140 44 L 215 132 L 260 132 L 325 118 L 485 118" />

                <!-- X-Axis Labels (MELT, COOL, WORK) -->
                <g class="chart-x-labels">
                  <text x="115" y="190" class="x-label">MELT</text>
                  <text x="238" y="190" class="x-label">COOL</text>
                  <text x="405" y="190" class="x-label">WORK</text>
                </g>

                <!-- Node 1: Melt Out -->
                <g id="node-melt" class="curve-node" transform="translate(115, 44)">
                  <circle cx="0" cy="0" r="4.5" class="node-dot" />
                  <text x="0" y="-12" class="node-label" id="label-melt">MELT OUT 50°C</text>
                </g>

                <!-- Node 2: Seed / Cool -->
                <g id="node-seed" class="curve-node" transform="translate(238, 132)">
                  <circle cx="0" cy="0" r="4.5" class="node-dot" />
                  <text x="0" y="-12" class="node-label" id="label-seed">SEED 28°C</text>
                </g>

                <!-- Node 3: Working Temp -->
                <g id="node-work" class="curve-node" transform="translate(405, 118)">
                  <circle cx="0" cy="0" r="4.5" class="node-dot" />
                  <text x="0" y="-12" class="node-label" id="label-work">WORK 31.5°C</text>
                </g>
              </svg>
            </div>
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


  <!-- Section 02: Interactive Flavor Wheel Section (Haute Cacao Sensory Lab) -->
  <section id="flavor-wheel-sec" class="luxury-flavor-wheel-sec">
    <div class="wheel-layout">
      <!-- Title Column -->
      <div class="wheel-title-col">
        <div class="wheel-section-badge">
          <span class="badge-pulse-dot"></span>
          <span>02 / SENSORY LAB</span>
        </div>
        <h2 class="wheel-main-heading">
          <span class="wheel-sub-prefix">CACAO MOLECULAR TERROIR</span>
          The Flavor <em>Wheel</em>
        </h2>
        <div class="wheel-gold-bar"></div>
        <p class="wheel-desc">
          Explore the multi-dimensional flavor taxonomy of bean-to-bar craft chocolate. Click any sector on the wheel or select a category card to unpack how terroir, roast profiling, and palate kinetics shape the bar's sensory fingerprint.
        </p>

        <!-- Interactive Helper Pill -->
        <div class="wheel-interactive-hint">
          <span class="hint-pulse">✦</span>
          <span>Click any wheel sector or tasting note to inspect chemistry</span>
        </div>

        <!-- Terroir Quick Stats -->
        <div class="wheel-stat-pills">
          <span class="stat-pill">16 DESCRIPTORS</span>
          <span class="stat-pill">3 CORE PILLARS</span>
          <span class="stat-pill">SINGLE-ORIGIN</span>
        </div>
      </div>

      <!-- Wheel Column -->
      <div class="wheel-svg-col">
        <div class="wheel-ambient-aura"></div>
        <div class="wheel-svg-wrapper">
          <svg class="wheel-svg" viewBox="0 0 540 540" id="interactive-wheel">
            <!-- Dynamic SVG content will be injected here by JS -->
          </svg>
        </div>
      </div>

      <!-- Details Column -->
      <div class="wheel-details-col">
        <!-- Flavor Notes Card -->
        <div class="wheel-detail-card" data-sector="flavor" id="card-flavor">
          <div class="card-header-row">
            <div class="card-icon-container icon-flavor">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d4af37" stroke-width="2">
                <path d="M12 2C6.5 2 2 6.5 2 12c0 3.5 1.8 6.6 4.6 8.4l1.4-1.4C5.7 17.5 4.3 14.9 4.3 12c0-4.2 3.5-7.7 7.7-7.7s7.7 3.5 7.7 7.7c0 2.9-1.4 5.5-3.7 7l1.4 1.4C20.2 18.6 22 15.5 22 12c0-5.5-4.5-10-10-10z"/>
                <path d="M12 6a6 6 0 0 0-6 6c0 2.1.8 4 2.2 5.3l1.4-1.4A4 4 0 0 1 8 12a4 4 0 1 1 8 0c0 1.5-.7 2.8-1.6 3.9l1.4 1.4C19.2 16 20 14.1 20 12a6 6 0 0 0-6-6z"/>
                <circle cx="12" cy="12" r="2" fill="#d4af37"/>
              </svg>
            </div>
            <div class="card-title-group">
              <span class="card-cat-badge badge-flavor">TERROIR &amp; BOTANY</span>
              <span class="card-title-text">1. Flavor Notes</span>
            </div>
          </div>
          <p>Aromas driven by volcanic soil chemistry, heirloom genetics (Criollo, Trinitario, Nacional), and post-harvest anaerobic pulp breakdown.</p>
          <div class="descriptor-pills-row" data-parent="flavor">
            <span class="descriptor-pill" data-desc="Earthy">Earthy</span>
            <span class="descriptor-pill" data-desc="Spicy">Spicy</span>
            <span class="descriptor-pill" data-desc="Sweet">Sweet</span>
            <span class="descriptor-pill" data-desc="Nutty">Nutty</span>
            <span class="descriptor-pill" data-desc="Floral">Floral</span>
            <span class="descriptor-pill" data-desc="Fruity">Fruity</span>
          </div>
        </div>

        <!-- Process Card -->
        <div class="wheel-detail-card" data-sector="process" id="card-process">
          <div class="card-header-row">
            <div class="card-icon-container icon-process">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7acb92" stroke-width="2">
                <circle cx="12" cy="12" r="3" />
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
              </svg>
            </div>
            <div class="card-title-group">
              <span class="card-cat-badge badge-process">CRAFT &amp; ROAST</span>
              <span class="card-title-text">2. Process</span>
            </div>
          </div>
          <p>Artisan transformation stages—box fermentation, solar drying, aerodynamic roasting, micro-shear conching, and crystal tempering.</p>
          <div class="descriptor-pills-row" data-parent="process">
            <span class="descriptor-pill" data-desc="Roasted">Roasted</span>
            <span class="descriptor-pill" data-desc="Fermented">Fermented</span>
            <span class="descriptor-pill" data-desc="Dried">Dried</span>
            <span class="descriptor-pill" data-desc="Conched">Conched</span>
            <span class="descriptor-pill" data-desc="Tempered">Tempered</span>
          </div>
        </div>

        <!-- Taste Profile Card -->
        <div class="wheel-detail-card" data-sector="taste" id="card-taste">
          <div class="card-header-row">
            <div class="card-icon-container icon-taste">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#e5c453" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M2 10h20" />
                <path d="M21.5 10c-.5 5-4.5 9-9.5 9s-9-4-9-9" />
                <path d="M12 10v9" />
                <path d="M12 14c1.5 0 2.5.5 2.5 1" />
              </svg>
            </div>
            <div class="card-title-group">
              <span class="card-cat-badge badge-taste">PALATE SENSORY</span>
              <span class="card-title-text">3. Taste Profile</span>
            </div>
          </div>
          <p>Direct palate sensations—tactile mouthfeel, melt velocity, acidity snap, astringency balance, and lingering velvety finish.</p>
          <div class="descriptor-pills-row" data-parent="taste">
            <span class="descriptor-pill" data-desc="Smooth">Smooth</span>
            <span class="descriptor-pill" data-desc="Creamy">Creamy</span>
            <span class="descriptor-pill" data-desc="Salty">Salty</span>
            <span class="descriptor-pill" data-desc="Sour">Sour / Tart</span>
            <span class="descriptor-pill" data-desc="Bittersweet">Bittersweet</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Molecular Sensory Telemetry Bar -->
    <div class="wheel-telemetry-container">
      <div class="wheel-telemetry-inner">
        <div class="telemetry-pill">
          <span class="telemetry-dot"></span>
          <span class="telemetry-label">MOLECULAR SENSORY DRIVER</span>
        </div>
        <div class="telemetry-content">
          <strong id="telemetry-note-name">SELECT ANY SLICE OR DESCRIPTOR</strong>
          <span class="telemetry-divider">&bull;</span>
          <span id="telemetry-note-desc">Click any wheel sector or tasting chip to inspect its origin chemistry and molecular flavor precursors.</span>
        </div>
      </div>
    </div>

    <!-- Enhanced Interactive Wheel Script -->
    <script>
      document.addEventListener("DOMContentLoaded", function() {
        const cx = 270;
        const cy = 270;
        const R0 = 68;  // Center hub radius
        const R1 = 70;  // Main sector inner radius
        const R2 = 150; // Main sector outer radius
        const R3 = 246; // Subsector outer radius

        const MOLECULAR_DATA = {
          "Earthy": "Volatile pyrazines and geosmin from volcanic terroir and biodynamic equatorial soil microbes.",
          "Spicy": "Warm eugenol and phenylpropanoid fractions that develop in Criollo genetics under moderate fermentation.",
          "Sweet": "Naturally occurring fructose and sucrose caramelized during thermal conching below 75°C, yielding maltol.",
          "Nutty": "2,3,5-trimethylpyrazine produced via gentle convective roasting of raw cocoa nibs at 115°C–125°C.",
          "Floral": "Volatile linalool and beta-damascenone monoterpenes characteristic of fine Nacional and Porcelana heirlooms.",
          "Fruity": "Ethyl 2-methylbutyrate and isoamyl acetate esters synthesized during anaerobic cacao pulp fermentation.",
          "Roasted": "Maillard reaction pyrazines formed when amino acids react with reducing sugars at peak roast crack.",
          "Fermented": "Acetic and lactic organic acids produced during 5-to-7 day hardwood cascade box fermentation.",
          "Dried": "Solar drying on elevated cedar beds reduces bean moisture to 7%, locking in vital flavor precursors.",
          "Conched": "Multi-axis mechanical shear reducing particle size below 20 microns while evaporating volatile harsh acids.",
          "Tempered": "Controlled heating cycle establishing stable Form V (β2) crystal network for clean snap and 34°C palate melt.",
          "Smooth": "Sub-20 micron cocoa solids suspended homogeneously in a continuous cocoa butter triglyceride matrix.",
          "Creamy": "High natural cocoa butter ratio (36%–42%) coating taste buds for slow, luxurious flavor release.",
          "Salty": "Natural oceanic mineral trace elements enhancing sweetness perception and elevating cocoa depth.",
          "Sour": "Natural citric and malic fruit acids providing a bright, wine-like, vibrant acidity on the palate edges.",
          "Bittersweet": "Purine alkaloids (theobromine, caffeine) paired with antioxidant flavan-3-ols for authentic dark structure."
        };

        const WHEEL_DATA = [
          {
            id: "flavor",
            label: "1. FLAVOR NOTES",
            color: "#46141c",
            accentColor: "#d4af37",
            textLight: "#ffffff",
            startAngle: 180,
            endAngle: 300,
            subsectors: [
              { label: "Earthy", startAngle: 180, endAngle: 200, color: "#3f1c22" },
              { label: "Spicy", startAngle: 200, endAngle: 220, color: "#501e27" },
              { label: "Sweet", startAngle: 220, endAngle: 240, color: "#61202c" },
              { label: "Nutty", startAngle: 240, endAngle: 260, color: "#732332" },
              { label: "Floral", startAngle: 260, endAngle: 280, color: "#852639" },
              { label: "Fruity", startAngle: 280, endAngle: 300, color: "#992940" }
            ]
          },
          {
            id: "process",
            label: "2. PROCESS",
            color: "#0c2c1a",
            accentColor: "#7acb92",
            textLight: "#ffffff",
            startAngle: 300,
            endAngle: 420,
            subsectors: [
              { label: "Roasted", startAngle: 300, endAngle: 324, color: "#143622" },
              { label: "Fermented", startAngle: 324, endAngle: 348, color: "#18422a" },
              { label: "Dried", startAngle: 348, endAngle: 372, color: "#1d4e32" },
              { label: "Conched", startAngle: 372, endAngle: 396, color: "#225a3a" },
              { label: "Tempered", startAngle: 396, endAngle: 420, color: "#286843" }
            ]
          },
          {
            id: "taste",
            label: "3. TASTE PROFILE",
            color: "#3a230e",
            accentColor: "#e5c453",
            textLight: "#ffffff",
            startAngle: 60,
            endAngle: 180,
            subsectors: [
              { label: "Smooth", startAngle: 60, endAngle: 84, color: "#472c14" },
              { label: "Creamy", startAngle: 84, endAngle: 108, color: "#543419" },
              { label: "Salty", startAngle: 108, endAngle: 132, color: "#633d1e" },
              { label: "Sour", startAngle: 132, endAngle: 156, color: "#724723" },
              { label: "Bittersweet", startAngle: 156, endAngle: 180, color: "#825029" }
            ]
          }
        ];

        const svg = document.getElementById("interactive-wheel");
        if (!svg) return;

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

        // Generate SVG content with luxury gradients and filter definitions
        let svgContent = `
          <defs>
            <radialGradient id="hubBgGrad" cx="50%" cy="50%" r="50%">
              <stop offset="0%" stop-color="#0e2617" />
              <stop offset="70%" stop-color="#07160d" />
              <stop offset="100%" stop-color="#030b06" />
            </radialGradient>
            <filter id="wheelSliceGlow" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="4" result="blur" />
              <feMerge>
                <feMergeNode in="blur" />
                <feMergeNode in="SourceGraphic" />
              </feMerge>
            </filter>
            <filter id="hubShadow" x="-30%" y="-30%" width="160%" height="160%">
              <feDropShadow dx="0" dy="2" stdDeviation="6" flood-color="#000000" flood-opacity="0.7"/>
            </filter>
          </defs>
          <g id="rotating-group">
        `;

        WHEEL_DATA.forEach((category) => {
          // --- 1. MAIN SECTOR (INNER PILLAR RING) ---
          const mainPath = getSectorPath(cx, cy, R1, R2, category.startAngle, category.endAngle);
          svgContent += `
            <path class="wheel-sector wheel-main-sector" 
                  d="${mainPath}" 
                  fill="${category.color}" 
                  stroke="rgba(212, 175, 55, 0.45)"
                  stroke-width="1.5"
                  data-category="${category.id}"
                  style="color: ${category.accentColor};"
            />
          `;

          const midAngle = (category.startAngle + category.endAngle) / 2;
          const normAngle = ((midAngle % 360) + 360) % 360;
          const isBottom = (normAngle > 0 && normAngle < 180);
          const textR = (R1 + R2) / 2;
          const textSpan = (category.endAngle - category.startAngle) * 0.44;
          
          const textPathD = isBottom
            ? getArcPath(cx, cy, textR, midAngle + textSpan, midAngle - textSpan, true)
            : getArcPath(cx, cy, textR, midAngle - textSpan, midAngle + textSpan, false);

          const textPathId = `textpath-${category.id}`;
          svgContent += `
            <path id="${textPathId}" d="${textPathD}" fill="none" stroke="none" />
            <text class="wheel-label-text" fill="${category.textLight}">
              <textPath href="#${textPathId}" startOffset="50%" text-anchor="middle">
                ${category.label}
              </textPath>
            </text>
          `;

          // --- 2. SUB-SECTORS (OUTER DESCRIPTOR RING) ---
          category.subsectors.forEach((sub, subIdx) => {
            const subPath = getSectorPath(cx, cy, R2, R3, sub.startAngle, sub.endAngle);
            svgContent += `
              <path class="wheel-sector wheel-sub-sector" 
                    d="${subPath}" 
                    fill="${sub.color}" 
                    stroke="rgba(212, 175, 55, 0.35)"
                    stroke-width="1.2"
                    data-category="${category.id}"
                    data-label="${sub.label}"
                    style="color: ${category.accentColor};"
              />
            `;

            const subMidAngle = (sub.startAngle + sub.endAngle) / 2;
            const normSubAngle = ((subMidAngle % 360) + 360) % 360;
            const isSubBottom = (normSubAngle > 0 && normSubAngle < 180);
            const subTextR = (R2 + R3) / 2;
            const subSpan = (sub.endAngle - sub.startAngle) * 0.43;

            const subTextPathD = isSubBottom
              ? getArcPath(cx, cy, subTextR, subMidAngle + subSpan, subMidAngle - subSpan, true)
              : getArcPath(cx, cy, subTextR, subMidAngle - subSpan, subMidAngle + subSpan, false);
            
            const subTextPathId = `textpath-sub-${category.id}-${subIdx}`;

            svgContent += `
              <path id="${subTextPathId}" d="${subTextPathD}" fill="none" stroke="none" />
              <text class="wheel-sublabel-text" fill="#ffffff" data-label="${sub.label}">
                <textPath href="#${subTextPathId}" startOffset="50%" text-anchor="middle">
                  ${sub.label}
                </textPath>
              </text>
            `;
          });
        });

        svgContent += `</g>`; // End rotating-group

        // --- 3. LUXURY MEDALLION CENTER HUB (STATIONARY) ---
        svgContent += `
          <g id="center-hub-group" filter="url(#hubShadow)" style="cursor: pointer;">
            <!-- Outer gold bezel -->
            <circle class="wheel-center-hub" cx="${cx}" cy="${cy}" r="${R0}" fill="url(#hubBgGrad)" stroke="#d4af37" stroke-width="2.5" />
            <!-- Concentric inner dashed gold accent -->
            <circle cx="${cx}" cy="${cy}" r="${R0 - 8}" fill="none" stroke="rgba(212, 175, 55, 0.4)" stroke-width="1" stroke-dasharray="2, 4" />
            <!-- Center Typography -->
            <text x="${cx}" y="${cy - 7}" text-anchor="middle" fill="#d4af37" font-family="'Playfair Display', serif" font-size="14" font-weight="700" letter-spacing="2">CACAO</text>
            <text x="${cx}" y="${cy + 8}" text-anchor="middle" fill="#fbf7ee" font-family="'Inter', sans-serif" font-size="8.5" font-weight="700" letter-spacing="2.5">SENSORY LAB</text>
            <text x="${cx}" y="${cy + 22}" text-anchor="middle" fill="#8eba9f" font-family="'Inter', sans-serif" font-size="7" font-weight="600" letter-spacing="1">✦ TAP ✦</text>
          </g>
        `;

        svg.innerHTML = svgContent;

        const rotatingGroup = document.getElementById("rotating-group");
        const centerHub = document.getElementById("center-hub-group");
        const sectors = document.querySelectorAll(".wheel-sector");
        const cards = document.querySelectorAll(".wheel-detail-card");
        const descriptorPills = document.querySelectorAll(".descriptor-pill");
        const telemetryName = document.getElementById("telemetry-note-name");
        const telemetryDesc = document.getElementById("telemetry-note-desc");

        const rotationMap = {
          "flavor": 30,
          "process": -90,
          "taste": 150
        };

        function updateTelemetry(name, desc) {
          if (!telemetryName || !telemetryDesc) return;
          telemetryName.textContent = name.toUpperCase();
          telemetryDesc.textContent = desc;
        }

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
            updateTelemetry("SELECT ANY SLICE OR DESCRIPTOR", "Click any wheel sector or tasting chip to inspect its origin chemistry and molecular flavor precursors.");
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

        // Wheel Sector Clicks & Hovers
        sectors.forEach(sec => {
          sec.addEventListener("click", function(e) {
            e.stopPropagation();
            const category = this.getAttribute("data-category");
            const label = this.getAttribute("data-label");
            setFocus(category);

            if (label && MOLECULAR_DATA[label]) {
              updateTelemetry(label, MOLECULAR_DATA[label]);
              highlightPill(label);
            }
          });

          sec.addEventListener("mouseenter", function() {
            const label = this.getAttribute("data-label");
            if (label && MOLECULAR_DATA[label]) {
              updateTelemetry(label, MOLECULAR_DATA[label]);
            }
          });
        });

        // Detail Cards Interaction
        cards.forEach(card => {
          card.addEventListener("click", function(e) {
            if (e.target.classList.contains("descriptor-pill")) return;
            const sectorId = this.getAttribute("data-sector");
            if (activeSectorId === sectorId) {
              setFocus(null);
            } else {
              setFocus(sectorId);
            }
          });
        });

        // Descriptor Pills Interaction
        function highlightPill(label) {
          descriptorPills.forEach(p => {
            if (p.getAttribute("data-desc") === label) {
              p.classList.add("active-pill");
            } else {
              p.classList.remove("active-pill");
            }
          });
        }

        descriptorPills.forEach(pill => {
          pill.addEventListener("click", function(e) {
            e.stopPropagation();
            const desc = this.getAttribute("data-desc");
            const parent = this.closest(".wheel-detail-card");
            if (parent) {
              const sec = parent.getAttribute("data-sector");
              setFocus(sec);
            }
            if (desc && MOLECULAR_DATA[desc]) {
              updateTelemetry(desc, MOLECULAR_DATA[desc]);
              highlightPill(desc);
            }
          });

          pill.addEventListener("mouseenter", function() {
            const desc = this.getAttribute("data-desc");
            if (desc && MOLECULAR_DATA[desc]) {
              updateTelemetry(desc, MOLECULAR_DATA[desc]);
            }
          });
        });

        // Center Hub Reset
        centerHub.addEventListener("click", function(e) {
          e.stopPropagation();
          setFocus(null);
          highlightPill(null);
        });

        document.addEventListener("click", function(e) {
          const wheelSection = document.getElementById("flavor-wheel-sec");
          if (wheelSection && !wheelSection.contains(e.target)) {
            setFocus(null);
            highlightPill(null);
          }
        });
      });
    </script>
  </section>

  <!-- Section 03: Journal & Technical Deep Dives (Editorial Luxury) -->
  <section id="home-journal-sec" class="home-journal-sec">
    <div class="section" style="max-width:1160px; margin:0 auto; text-align:center;">
      <div class="section-label">03 / JOURNAL</div>
      <h2 class="section-title">Latest Cocoa Science Insights</h2>
      <div class="divider" style="margin:16px auto 32px;"></div>
      <p class="section-subtitle" style="max-width:560px; margin:0 auto 48px; color:var(--brown-light);">Deep-dives into cocoa powder pH, tempering crystal diagnostics, lecithin emulsion science, and bean-to-bar formulation.</p>
      
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(290px, 1fr)); gap:30px; text-align:left;">
        <?php
          require_once __DIR__ . '/includes/db.php';
          require_once __DIR__ . '/includes/blog-cache.php';
          require_once __DIR__ . '/includes/blog-data.php';

          $latestBlogs = get_cached_blog_list(1800);
          if ($latestBlogs === null || empty($latestBlogs)) {
              try {
                  $pdo = get_db();
                  $stmt = $pdo->query("SELECT id, slug, title, category, created_at, excerpt, image_path, thumbnail_path, youtube_url, body_class, read_time FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
                  $dbBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                  $latestBlogs = [];
                  foreach ($dbBlogs as $blog) {
                      $latestBlogs[] = [
                          'id' => (int)$blog['id'],
                          'slug' => $blog['slug'],
                          'title' => $blog['title'],
                          'category' => $blog['category'],
                          'date' => date('M Y', strtotime($blog['created_at'])),
                          'read_time' => $blog['read_time'] ?: '5 min',
                          'excerpt' => $blog['excerpt'],
                          'image' => $blog['image_path'],
                          'thumbnail' => $blog['thumbnail_path'] ?: $blog['image_path'],
                          'youtube_url' => $blog['youtube_url'],
                          'body_class' => $blog['body_class'] ?: ''
                      ];
                  }
                  cache_blog_list($latestBlogs);
              } catch (Exception $e) {
                  $cached = get_cached_blog_list();
                  if ($cached) {
                      $latestBlogs = $cached;
                  } else {
                      $latestBlogs = [];
                      foreach ($BLOGS as $slug => $meta) {
                          $latestBlogs[] = [
                              'slug' => $slug,
                              'title' => $meta['title'],
                              'category' => $meta['category'],
                              'date' => $meta['date'],
                              'read_time' => $meta['read'] ?? '5 min',
                              'excerpt' => $meta['excerpt'],
                              'image' => $meta['image'],
                              'thumbnail' => $meta['thumbnail'] ?? $meta['image']
                          ];
                      }
                  }
              }
          }
          $recentBlogs = array_slice($latestBlogs, 0, 3);
          foreach ($recentBlogs as $b):
            $bSlug = $b['slug'] ?? '';
            $bThumb = !empty($b['thumbnail']) ? $b['thumbnail'] : (!empty($b['image']) ? $b['image'] : 'assets/logo.png');
            $bFallback = !empty($b['image']) ? $b['image'] : 'assets/logo.png';
            $bRead = !empty($b['read_time']) ? $b['read_time'] : (!empty($b['read']) ? $b['read'] : '5 min');
            $bDate = !empty($b['date']) ? $b['date'] : 'Recent';
        ?>
          <a href="<?php echo $pathPrefix; ?>blog/<?php echo htmlspecialchars($bSlug); ?>" class="home-journal-card">
            <div class="home-journal-img-wrap">
              <img src="<?php echo htmlspecialchars($pathPrefix . $bThumb); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" loading="lazy" onerror="this.onerror=null; this.src='<?php echo htmlspecialchars($pathPrefix . $bFallback); ?>';">
              <span class="home-journal-cat"><?php echo htmlspecialchars($b['category']); ?></span>
            </div>
            <div style="padding:26px 24px 22px; display:flex; flex-direction:column; flex-grow:1;">
              <span style="font-size:12px; color:var(--gold); font-weight:600; margin-bottom:8px; display:block; letter-spacing:0.4px;"><?php echo htmlspecialchars($bDate); ?> • <?php echo htmlspecialchars($bRead); ?> read</span>
              <h4 style="font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:var(--brown); margin-bottom:10px; line-height:1.35;"><?php echo htmlspecialchars($b['title']); ?></h4>
              <p style="font-family:var(--font-sans); font-size:13.5px; line-height:1.6; color:var(--brown-light); font-weight:300; margin-bottom:16px; display:-webkit-box; -webkit-line-clamp:3; -webkit-box-orient:vertical; overflow:hidden;"><?php echo htmlspecialchars($b['excerpt']); ?></p>
              <span style="margin-top:auto; font-size:13px; font-weight:600; color:var(--brown); display:inline-flex; align-items:center; gap:6px;">
                <span>Read Full Article</span>
                <span class="journal-arrow-icon">&rarr;</span>
              </span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
      
      <div style="margin-top:44px;">
        <a href="blog" class="btn-hero-outline" style="text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:14px 28px; border-radius:30px; font-weight:600;">
          <span>Browse All Journal Articles</span>
          <span>&rarr;</span>
        </a>
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



</div><!-- end home -->

<!-- Landing Page Luxury Orchestrator Script -->
<script src="<?php echo $pathPrefix; ?>js/landing-luxury.js?v=<?php echo file_exists(__DIR__ . '/js/landing-luxury.js') ? filemtime(__DIR__ . '/js/landing-luxury.js') : time(); ?>"></script>
<!-- Bean to Bar Master Timeline & Guide Script -->
<script src="<?php echo $pathPrefix; ?>js/bean-to-bar.js?v=<?php echo file_exists(__DIR__ . '/js/bean-to-bar.js') ? filemtime(__DIR__ . '/js/bean-to-bar.js') : time(); ?>"></script>

<!-- --- ABOUT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
