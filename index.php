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
<link rel="stylesheet" href="<?php echo $pathPrefix; ?>css/landing-sections-upgrade.css?v=<?php echo file_exists(__DIR__ . '/css/landing-sections-upgrade.css') ? filemtime(__DIR__ . '/css/landing-sections-upgrade.css') : time(); ?>">

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
        <div class="hero-ingredient-spotlight-card fade-up-d3" id="heroIngredientSpotlightCard" onclick="openTableModal('ingredient-spotlight')" title="Click to view full scientific profile">
          <div class="spotlight-header-row">
            <div class="spotlight-tag-group">
              <span class="spotlight-pod-icon">
                <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10 2.2C6.8 2.2 4 6 4 10.5c0 3.2 2.2 6.5 6 8 3.8-1.5 6-4.8 6-8C16 6 13.2 2.2 10 2.2z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/>
                  <path d="M10 2.2v16.3M7.2 7.2c0 2.2 1.3 4 2.8 4s2.8-1.8 2.8-4" stroke="currentColor" stroke-width="1.1"/>
                </svg>
              </span>
              <span class="spotlight-tag" id="homeSpotlightTag"><?= htmlspecialchars($activeSpotlight['clean_tag'] ?? 'INGREDIENT SPOTLIGHT') ?></span>
            </div>
          </div>

          <div class="spotlight-title-wrap">
            <h3 class="spotlight-title" id="homeSpotlightTitle"><?= htmlspecialchars($activeSpotlight['ingredient_name'] ?? 'Cocoa Butter') ?></h3>
            <span class="spotlight-level-tag" id="homeSpotlightLevel"><?= !empty($activeSpotlight['level_number']) ? 'L' . (int)$activeSpotlight['level_number'] : 'L1' ?></span>
          </div>

          <p class="spotlight-desc" id="homeSpotlightDesc"><?= htmlspecialchars($activeSpotlight['short_desc'] ?? 'The golden fat that gives chocolate its smoothness and soul.') ?></p>

          <div class="spotlight-notes-row" id="homeSpotlightNotes">
            <?php if (!empty($activeSpotlight['flavor_notes_list']) && is_array($activeSpotlight['flavor_notes_list'])): ?>
              <?php foreach (array_slice($activeSpotlight['flavor_notes_list'], 0, 3) as $note): ?>
                <span class="spotlight-note-chip">
                  <span class="chip-pip"></span>
                  <?= htmlspecialchars($note) ?>
                </span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

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

        <!-- 2. Cacao Journal → Open leather recipe journal (center of table) -->
        <div class="table-hotspot" style="top: 52%; left: 48%;" onclick="openTableModal('knowledge-hub')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Cacao Journal</span>
          </div>
        </div>

        <!-- 3. Chocolate Lab → Brass magnifying glass / microscope (top-right) -->
        <div class="table-hotspot" style="top: 14%; left: 80%;" onclick="openTableModal('chocolate-lab')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Cacao Lab</span>
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

        <!-- 6. Work With Us → Collaboration & Consulting (far left) -->
        <div class="table-hotspot" style="top: 58%; left: 10%;" onclick="openTableModal('origins-atlas')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Work With Us</span>
          </div>
        </div>

        <!-- 7. Workshops & Academy → Whisk + chocolate mould (bottom-right) -->
        <div class="table-hotspot" style="top: 82%; left: 78%;" onclick="openTableModal('workshops-academy')">
          <div class="hotspot-pin">
            <span class="hotspot-dot" style="pointer-events: none;"></span>
            <span style="pointer-events: none;">Innovation Lab</span>
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
        <h5 id="modal-table-highlights-title">✨ KEY INSIGHTS &amp; TAKEAWAYS</h5>
        <ul id="modal-table-list">
          <li>Single-Origin Bean Sourcing from Kerala &amp; Karnataka</li>
          <li>Fermentation &amp; Drying Thermodynamics</li>
          <li>72-Hour Stone Grinding (Conching) for Silky Smooth Melt</li>
        </ul>
      </div>

      <p id="modal-table-prompt" class="table-modal-prompt" style="display: none;"></p>

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
        desc: 'Explore the complete bean-to-bar journey and understand how each stage transforms simple beans into extraordinary chocolate.',
        highlights: [
          'Trace cacao’s transformation from bean to bar.',
          'Explore how processing shapes flavour, aroma and texture.',
          'Uncover the science behind chocolate quality.',
          'Understand what defines the character of a finished bar.'
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
        badge: '📖 RT CHOCOS · THE CACAO JOURNAL',
        title: 'The Cacao Journal',
        desc: 'A deeper understanding of chocolate, from its science and ingredients to the processes that shape its character.',
        highlightsTitle: '📖 EXPLORE THE JOURNAL',
        highlights: [
          '⚗ Chocolate science, explained clearly',
          '🌿 Ingredients, functionality & flavour',
          '⚙ Processing, techniques & troubleshooting',
          '♧ Ideas and insights shaping the chocolate industry'
        ],
        btn1Text: 'Explore Articles →',
        btn1Href: 'blog.php',
        btn2Text: 'Search Chocopedia',
        btn2Href: 'chocopedia.php'
      },
      'chocolate-lab': {
        badge: '🧪 AI FORMULATION & R&D',
        title: 'The Cacao Lab',
        desc: 'Explore chocolate through hands-on masterclasses, recipe development and applied chocolate science.',
        highlights: [
          'Build practical skills, from chocolate-making fundamentals to specialised techniques.',
          'Understand ingredients, processing and the science behind chocolate’s behaviour.',
          'Develop, test and refine recipes into distinctive chocolate products.'
        ],
        btn1Text: 'Launch Chocolab →',
        btn1Href: 'workshops.php',
        btn2Text: 'Ask CocoaGenius AI',
        btn2Href: 'javascript:toggleAiDrawer()'
      },
      'recipes-formulations': {
        badge: '🍫 PRO FORMULATIONS',
        title: 'Recipes & Formulations',
        desc: 'Explore chocolate recipes, understand ingredient behaviour and discover the techniques behind better results.',
        highlights: [
          'Recipes worth exploring',
          'Ingredients that make a difference',
          'Techniques that shape the result',
          'The science behind every formulation'
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
        badge: '🤝 RT CHOCOS · COLLABORATION & CONSULTING',
        title: 'Work With Us',
        desc: 'From product innovation to technical problem-solving, we collaborate with businesses and chocolate professionals to turn ideas into better chocolate.',
        highlightsTitle: 'WHAT WE CAN WORK ON',
        highlights: [
          '◇ Product development & formulation',
          '🍃 Ingredient innovation & application',
          '⚙ Process optimisation & troubleshooting',
          '♧ Research, training & technical consulting'
        ],
        promptText: 'Have a chocolate challenge or an idea to explore?',
        btn1Text: 'Start a Conversation →',
        btn1Href: 'work-with-us.php#inquiry-form',
        btn2Text: 'Consulting Services',
        btn2Href: 'work-with-us.php'
      },
      'workshops-academy': {
        badge: '💡 RT CHOCOS · THE INNOVATION WALL',
        title: 'Innovation Wall',
        desc: 'Explore the ideas, experiments and discoveries shaping the future of chocolate.',
        highlightsTitle: '🔬 INSIDE THE INNOVATION LAB',
        highlights: [
          'Ingredient innovations worth exploring',
          'What if? New possibilities in chocolate',
          'Experiments, results & unexpected insights',
          'Ideas shaping the future of chocolate'
        ],
        btn1Text: 'Explore the Innovation Lab →',
        btn1Href: 'innovation-lab.php',
        btn2Text: 'View Experiments',
        btn2Href: 'innovation-lab.php#currently-in-lab'
      }
    };

    function openTableModal(key) {
      const data = TABLE_MODAL_DATA[key];
      if (!data) return;

      document.getElementById('modal-table-badge').innerText = data.badge;
      document.getElementById('modal-table-title').innerText = data.title;
      document.getElementById('modal-table-desc').innerText = data.desc;

      const highlightsHeading = document.getElementById('modal-table-highlights-title');
      if (highlightsHeading) {
        highlightsHeading.innerText = data.highlightsTitle || '✨ KEY INSIGHTS & TAKEAWAYS';
      }

      const listContainer = document.getElementById('modal-table-list');
      listContainer.innerHTML = data.highlights.map(item => `<li>${item}</li>`).join('');

      const promptEl = document.getElementById('modal-table-prompt');
      if (promptEl) {
        if (data.promptText) {
          promptEl.innerText = data.promptText;
          promptEl.style.display = 'block';
        } else {
          promptEl.style.display = 'none';
        }
      }

      const btn1 = document.getElementById('modal-table-btn-primary');
      btn1.innerText = data.btn1Text;
      btn1.href = data.btn1Href;
      if (data.btn1Action) {
        btn1.onclick = (e) => { e.preventDefault(); data.btn1Action(); };
      } else {
        btn1.onclick = null;
      }

      const btn2 = document.getElementById('modal-table-btn-secondary');
      if (data.btn2Text) {
        btn2.innerText = data.btn2Text;
        btn2.href = data.btn2Href || '#';
        btn2.style.display = '';
        if (data.btn2Action) {
          btn2.onclick = (e) => { e.preventDefault(); data.btn2Action(); };
        } else {
          btn2.onclick = null;
        }
      } else {
        btn2.style.display = 'none';
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

    function rotateSpotlightCard(e) {
      if (e) {
        e.stopPropagation();
        e.preventDefault();
      }
      const btnSvg = document.querySelector('.spotlight-rotate-btn svg');
      if (btnSvg) {
        btnSvg.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)';
        btnSvg.style.transform = 'rotate(360deg)';
      }

      const card = document.getElementById('heroIngredientSpotlightCard');
      if (card) {
        card.style.transition = 'opacity 0.2s ease';
        card.style.opacity = '0.45';
      }

      fetch('api_generate_spotlight.php?action=rotate')
        .then(res => res.json())
        .then(data => {
          if (data && data.success && data.spotlight) {
            const s = data.spotlight;
            const titleEl = document.getElementById('homeSpotlightTitle');
            const descEl = document.getElementById('homeSpotlightDesc');
            const levelEl = document.getElementById('homeSpotlightLevel');
            const notesEl = document.getElementById('homeSpotlightNotes');
            const badgeText = document.getElementById('spotlightBadgeText');

            setTimeout(() => {
              if (titleEl) titleEl.textContent = s.ingredient_name;
              if (descEl) descEl.textContent = s.short_desc;
              if (levelEl) levelEl.textContent = s.level_number ? 'L' + s.level_number : 'L1';
              if (notesEl && Array.isArray(s.flavor_notes_list)) {
                notesEl.innerHTML = s.flavor_notes_list.slice(0, 3).map(n => `
                  <span class="spotlight-note-chip">
                    <span class="chip-pip"></span>
                    ${n}
                  </span>
                `).join('');
              }
              if (badgeText) {
                badgeText.textContent = 'Rotated!';
                setTimeout(() => { badgeText.textContent = 'AI Live 6h'; }, 1600);
              }

              // Keep modal synced with rotated ingredient
              if (typeof TABLE_MODAL_DATA !== 'undefined' && TABLE_MODAL_DATA['ingredient-spotlight']) {
                TABLE_MODAL_DATA['ingredient-spotlight'].title = s.ingredient_name;
                TABLE_MODAL_DATA['ingredient-spotlight'].desc = s.short_desc;
                TABLE_MODAL_DATA['ingredient-spotlight'].highlights = s.modal_highlights || [
                  'Level: ' + (s.origin_region || s.level || 'Chocolate Science'),
                  'Sensory Triad: ' + (s.triplet || s.flavor_notes || ''),
                  'Refreshed automatically every 6 hours'
                ];
              }

              if (card) card.style.opacity = '1';
              if (btnSvg) {
                setTimeout(() => {
                  btnSvg.style.transition = 'none';
                  btnSvg.style.transform = 'none';
                }, 500);
              }
            }, 120);
          } else {
            if (card) card.style.opacity = '1';
          }
        })
        .catch(err => {
          console.error('Error rotating spotlight:', err);
          if (card) card.style.opacity = '1';
        });
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
  <section id="flavor-wheel-sec" class="luxury-flavor-wheel-sec" aria-label="Interactive Flavor Wheel">
    <div class="wheel-shell">
      <div class="section-head">
        <div>
          <div class="eyebrow">02 / SENSORY LAB</div>
          <h2>The Flavor <em>Wheel</em></h2>
        </div>
        <p>Start with what you notice, then select any descriptor to explore its meaning, practical tasting cue, and the science behind it.</p>
      </div>

      <div class="workspace">
        <!-- Interactive Flavor Wheel Panel -->
        <section class="panel wheel-panel" aria-label="Interactive flavor wheel">
          <div class="wheel-toolbar">
            <span class="micro">Explore a sensory note</span>
            <button class="subtle-btn" id="resetWheel" type="button">Show all</button>
          </div>
          <div class="wheel-wrap">
            <svg id="wheel" viewBox="0 0 500 500" role="group" aria-label="Chocolate sensory wheel"></svg>
          </div>
          <div class="wheel-hint">Choose the word that comes closest to what you notice. You can return to the guide at any time.</div>
          <div class="legend">
            <span><i class="swatch" style="background:#B96870"></i>Flavor notes</span>
            <span><i class="swatch" style="background:#C58B55"></i>Taste &amp; mouthfeel</span>
            <span><i class="swatch" style="background:#7D9C72"></i>Process context</span>
          </div>
        </section>

        <!-- Dynamic Sensory Detail Notebook Panel -->
        <section class="panel detail-panel" id="detailPanel" aria-live="polite" aria-atomic="true">
          <div class="detail-top">
            <span class="tag" id="detailGroup">START HERE</span>
            <span class="micro">Your tasting notebook</span>
          </div>
          <div id="detailContent" class="fade-in">
            <h3>Begin with what you notice.</h3>
            <p class="detail-lead">Choose any descriptor on the wheel. You can explore the science, try a small exercise, or simply continue tasting.</p>
            <div class="callout"><b>Try this</b>Smell your chocolate gently. Pause, then smell again. What comes to mind first?</div>
            <p>Your first impression is worth recording, even if it is “nothing distinctive yet.”</p>
          </div>
          <div class="reference">Your senses are the starting point. The descriptor is a tool, not a verdict.</div>
        </section>
      </div>
    </div>

    <!-- Flavor Wheel Interactive Script -->
    <script>
      (function() {
        const data = {
          "Floral":{group:"AROMA",color:"#28664e",meaning:"A floral note is an aroma that brings flowers or blossoms to mind. It may feel delicate, perfumed or fresh.",cue:"Smell gently. Pause. Try again. Does anything remind you of jasmine, rose or orange blossom?",try:"Smell the chocolate before tasting. Record the first association that comes to mind without searching for a particular answer.",refs:"Jasmine · Rose · Orange blossom",dist:"Floral is an aroma association, not a basic taste. A fragrant chocolate need not taste sweet.",science:"Floral impressions can arise from mixtures of volatile compounds, including some terpenoids and other aroma-active compounds. Linalool is associated with floral and citrus-like aromas in many foods, but its presence alone cannot establish that a chocolate will smell floral. Genetics, fermentation, roasting and formulation all influence aroma.",insight:"A recognised aroma is the brain's interpretation of a mixture—not proof of a single ingredient or molecule."},
          "Fruity":{group:"AROMA",color:"#28664e",meaning:"Fruity describes an aroma that reminds you of fruit: perhaps berries, citrus, ripe tropical fruit or dried fruit.",cue:"Look for the closest memory: fresh and juicy, citrus-like, berry-like, tropical or raisin-like.",try:"Smell before tasting, then notice whether the same fruit-like association returns as the chocolate melts.",refs:"Berries · Citrus peel · Ripe fruit · Raisins",dist:"Fruity is an aroma association. Sourness is a taste-related sensation. Chocolate can smell fruity without tasting sour.",science:"Fruit-like aromas can involve esters, aldehydes, alcohols and other volatile compounds. Fermentation, drying, roasting, genetics and storage can change the mixture. A fruity impression cannot be attributed to fermentation alone.",insight:"Fruit-like aroma, acidity and fermentation-related aroma can coexist, but each describes a different aspect of tasting."},
          "Nutty":{group:"AROMA",color:"#28664e",meaning:"A nutty note is an aroma reminiscent of nuts, from fresh and mild to toasted and rich.",cue:"Ask whether the impression is closer to a raw almond or a roasted hazelnut.",try:"Smell the chocolate, then let it melt. Check whether the nut-like association becomes stronger or fades.",refs:"Almond · Hazelnut · Peanut · Toasted nuts",dist:"Nutty is an aroma family. It does not prove that nuts are ingredients or that a product is allergen-free.",science:"Nut-like and toasted aromas can involve several volatile compounds formed or transformed during processing. Roasting can promote Maillard chemistry, but nutty aroma cannot be assigned to roasting alone.",insight:"Always check the ingredient and allergen declaration; sensory resemblance is not evidence of ingredient presence."},
          "Spicy":{group:"AROMA & SENSATION",color:"#28664e",meaning:"A spicy note may evoke cinnamon or clove. A prickling or warming sensation is a different experience.",cue:"Ask whether you are smelling a spice-like aroma or physically feeling heat, tingling or irritation.",try:"Smell first, then taste. Identify a spice-like association before deciding whether you feel a physical sensation.",refs:"Cinnamon · Clove · Black pepper",dist:"A cinnamon-like aroma does not mean the chocolate contains cinnamon. Trigeminal heat is not simply an aroma.",science:"Different spice-associated aromas arise from different mixtures. Eugenol contributes clove-like aroma in many foods. Capsaicin can activate TRPV1 receptors and create burning; not every spicy impression in chocolate shares that mechanism.",insight:"One word can describe two different experiences: what a spice smells like and what a pungent ingredient physically feels like."},
          "Smoky":{group:"AROMA",color:"#28664e",meaning:"Smoky describes an aroma reminiscent of smoke, charred wood or a fire. It differs from the warmer smell of toasted foods.",cue:"Ask: does this remind me of a campfire, or more of toasted coffee and nuts?",try:"Compare the aroma before and after breaking the chocolate. Notice whether the smoke-like impression is distinct from roasted notes.",refs:"Wood smoke · Campfire · Charred wood",dist:"Roasted notes suggest heating or toasting; smoky notes suggest smoke or char. They can overlap.",science:"Volatile phenols such as guaiacol can contribute smoky impressions in foods. In cacao, drying conditions and smoke exposure can be relevant, but a smoky impression alone cannot identify its source or establish a defect.",insight:"A smoky note is an observation, not proof of poor processing. Its significance depends on intensity, balance and intended profile."},
          "Earthy":{group:"AROMA",color:"#28664e",meaning:"Earthy describes an aroma reminiscent of soil, forest floor or damp roots. It may be subtle or pronounced.",cue:"Look for a soil-like association and notice whether it feels clean and gentle or musty and stale.",try:"Take one gentle smell, pause and smell again. Distinguish earthy from woody or roasted.",refs:"Forest floor · Damp soil · Roots",dist:"Earthy is not automatically mouldy. Signs of spoilage should not be dismissed as a desirable flavour note.",science:"Geosmin and other volatile compounds can create earthy impressions in foods, but their presence in a particular chocolate must not be assumed without evidence. Handling and storage may affect unwanted aromas.",insight:"The same broad descriptor can feel pleasant or undesirable; context and intensity matter."},
          "Roasted":{group:"AROMA",color:"#28664e",meaning:"Roasted notes resemble foods that have been heated or toasted, such as coffee, toasted nuts or baked crust.",cue:"Look for warm, toasted, coffee-like or browned aromas rather than a fresh fruit or floral impression.",try:"Smell first, then taste. Notice whether the roasted impression becomes clearer as the chocolate melts.",refs:"Coffee · Toasted nuts · Baked crust",dist:"Roasted describes a sensory impression, not a direct measurement of roast temperature. Smoky suggests smoke or char.",science:"Roasting changes the volatile profile through reactions including Maillard chemistry and transformation of aroma precursors. Pyrazines and other compound families can contribute, depending on the specific compounds and concentrations.",insight:"Roasting can develop desirable aromas while transforming others. More roasting does not automatically mean better flavour."},
          "Sweet":{group:"BASIC TASTE",color:"#476d72",meaning:"Sweetness is a basic taste often associated with sugars. A honey-like or caramel-like aroma is related, but not the same thing.",cue:"Notice the taste itself. Then ask whether a honey-like or caramel-like aroma adds to the overall impression.",try:"Let the chocolate melt and focus on sweetness separately from bitterness and aroma.",refs:"Sugar · Honey-like aroma · Ripe fruit",dist:"A sweet aroma is not proof of sugar content, and perceived sweetness does not reveal the precise amount of sugar.",science:"Sweet taste is mediated primarily by the T1R2/T1R3 receptor. Aroma, texture, bitterness and expectation can influence how sweet the whole experience seems.",insight:"Two chocolates with similar sugar levels may not seem equally sweet because their bitterness, aroma and formulation differ."},
          "Bittersweet":{group:"BOTH TASTE",color:"#476d72",meaning:"Bittersweet describes a combined impression of sweetness and bitterness. Their balance may shift as the chocolate melts.",cue:"Notice whether sweetness appears first and bitterness follows, or whether both are present together.",try:"Taste slowly and name sweetness and bitterness separately before describing their balance.",refs:"Dark cocoa · Sweetened espresso · Dark caramel",dist:"Bittersweet is a combined description, not a separate basic taste.",science:"Sweet and bitter sensations arise through different taste mechanisms. Cocoa solids, sugar, formulation and sensory context affect their perceived balance.",insight:"Separating the two sensations first makes a more useful note than simply calling chocolate 'strong'."},
          "Sour / Tart":{group:"TASTE",color:"#476d72",meaning:"Sourness is a taste associated with acids. Tartness is a broader everyday description of a bright, sharp or mouth-watering impression.",cue:"Notice whether the sensation resembles citrus-like sharpness, and whether it is a taste or a fruit-like aroma.",try:"Taste, then pause. Describe sourness separately from fruity aroma or drying sensation.",refs:"Lemon-like sharpness · Tart berries · Plain yoghurt",dist:"Fruity does not mean sour. A fruit-like aroma can occur without strong acidity.",science:"Acids influence sour taste through specialised taste-cell mechanisms. pH and titratable acidity are different chemical measurements and neither captures the entire sensory experience.",insight:"Perceived sourness cannot tell you the chocolate's pH."},
          "Salty":{group:"BASIC TASTE",color:"#476d72",meaning:"Salty is a basic taste associated with salts. In chocolate it may be subtle or more obvious in some formulations.",cue:"Focus on a salt-like taste rather than sharpness, bitterness or a mineral-like aroma.",try:"Taste a small piece and identify saltiness independently of sweetness and bitterness.",refs:"A tiny pinch of salt · Salted nuts",dist:"Salty is a taste. Mineral-like is an aroma or flavour association and does not prove a specific mineral is present.",science:"Salty taste involves ion-sensitive pathways, especially for sodium salts. Other ingredients and formulation influence how noticeable it is.",insight:"A chocolate may contain salt without tasting strongly salty; perception depends on concentration and context."},
          "Creamy":{group:"MOUTHFEEL",color:"#476d72",meaning:"Creamy describes a rich, coating or soft sensation. It may occur in milk or dark chocolate and does not necessarily mean dairy is present.",cue:"Notice how the chocolate coats your mouth, how it melts, and whether it feels rich, smooth or waxy.",try:"Let a small piece melt naturally. Focus on the physical sensation rather than the flavour you smell.",refs:"Cream · Soft butter · Rich custard",dist:"Creamy is mouthfeel; milky is a flavour association; smooth describes the absence of noticeable graininess.",science:"Fat composition, particle size and distribution, moisture, emulsifiers and structure can influence mouthfeel. Cocoa butter matters, but creaminess is shaped by several interacting factors.",insight:"Dark chocolate can feel creamy without tasting milky."},
          "Astringent":{group:"MOUTHFEEL",color:"#476d72",meaning:"Astringency is a drying, rough or puckering sensation that may develop during tasting or linger afterwards.",cue:"Notice whether your tongue, gums or inner cheeks feel less lubricated, almost as if moisture has been drawn away.",try:"After the chocolate melts, pause. Distinguish drying or roughness from the bitter taste that may accompany it.",refs:"Strong black tea · Unripe banana · Dry red wine",dist:"Bitterness is a taste; astringency is mainly a tactile mouthfeel. You may experience either without the other.",science:"Cacao polyphenols, including flavan-3-ols and procyanidins, can interact with salivary proteins and contribute to reduced lubrication. Astringency is complex and cannot be predicted from one compound or bitterness alone.",insight:"A chocolate may seem less bitter without losing every drying sensation."},
          "Fermented":{group:"PROCESS CONTEXT",color:"#927747",meaning:"Fermentation is a cacao-processing stage, not a single flavour. Its effects may be perceived as fruity, acidic, floral or wine-like, depending on the process.",cue:"Look for a specific sensory observation first. Treat the process as a hypothesis only when supporting information exists.",try:"Describe the actual aroma before exploring how fermentation might have contributed.",refs:"Fruit-like aroma · Wine-like impression · Tangy notes",dist:"A fermented impression is not proof of a particular fermentation method, duration or quality.",science:"Microbial activity changes sugars, acids and precursors that later influence aroma development. Drying and roasting further transform this chemistry; the final flavour reflects the whole process chain.",insight:"Ask what you perceive first, and what process information supports your interpretation."},
          "Caramelized":{group:"AROMA",color:"#28664e",meaning:"Caramelized describes an aroma or flavour reminiscent of heated sugar, toffee or browned sweet foods.",cue:"Look for a warm, browned-sugar impression rather than straightforward sweetness alone.",try:"Notice whether the association is mainly aromatic, mainly sweet, or a combination.",refs:"Toffee · Caramel · Browned sugar",dist:"Caramel-like aroma does not prove that caramel is an ingredient or that caramelization alone produced the note.",science:"Caramelization and Maillard reactions are distinct pathways, though both can contribute to complex browned aromas. The profile depends on ingredients and processing.",insight:"A caramel-like impression can occur without added caramel."},
          "Woody":{group:"AROMA",color:"#28664e",meaning:"Woody describes an aroma reminiscent of dry wood, cedar or pencil shavings.",cue:"Look for a dry, wood-like association distinct from earthy soil or smoky char.",try:"Smell again and choose the closest association: woody, earthy, smoky or roasted.",refs:"Cedar · Dry wood · Pencil shavings",dist:"Woody does not establish the use of wood in processing or prove a particular cacao origin.",science:"Woody aroma is a perceptual category that can reflect different volatile mixtures. Assigning it to a single compound or processing stage requires specific evidence.",insight:"Sensory categories describe what an aroma resembles; they do not automatically reveal its chemical source."},
          "Malty":{group:"AROMA",color:"#28664e",meaning:"Malty describes an aroma reminiscent of cereal, malted grain or warm grain-based foods.",cue:"Look for a grain-like, warm, sometimes bread-like association rather than a straightforward roasted-nut note.",try:"Smell, then taste, and note whether the malty impression grows as the chocolate melts.",refs:"Malted cereal · Toasted grain · Bread crust",dist:"Malty is not identical to roasted, caramelized or sweet. These can overlap but describe different impressions.",science:"Malt-like aromas can involve multiple volatile compounds and processing reactions. Their specific origin in a chocolate cannot be established from the descriptor alone.",insight:"Several aroma families can coexist in one chocolate; the goal is to describe the dominant impression accurately."},
          "Cocoa / Chocolate":{group:"AROMA",color:"#28664e",meaning:"This descriptor captures recognisable cocoa-like or chocolate-like aromas, from mild and rounded to intense and deep.",cue:"Notice whether the impression resembles cocoa powder, cocoa nibs or a deeper roasted cocoa aroma.",try:"Smell before tasting, then check whether the cocoa impression changes during melting or remains in the finish.",refs:"Cocoa powder · Cocoa nibs · Brownie-like cocoa",dist:"Cocoa intensity is not the same as bitterness, cocoa percentage or overall quality.",science:"Chocolate aroma comes from a complex mixture of volatile compounds shaped by cacao material, processing and formulation. No single compound explains the complete cocoa character.",insight:"A higher cocoa percentage does not guarantee a more intense or more pleasant cocoa aroma."}
        };

        const categories = [
          {name:"Floral",group:"Flavor notes"},{name:"Fruity",group:"Flavor notes"},{name:"Nutty",group:"Flavor notes"},{name:"Spicy",group:"Flavor notes"},{name:"Smoky",group:"Flavor notes"},{name:"Earthy",group:"Flavor notes"},{name:"Roasted",group:"Flavor notes"},{name:"Caramelized",group:"Flavor notes"},{name:"Woody",group:"Flavor notes"},{name:"Malty",group:"Flavor notes"},{name:"Cocoa / Chocolate",group:"Flavor notes"},
          {name:"Sweet",group:"Taste profile"},{name:"Bittersweet",group:"Taste profile"},{name:"Sour / Tart",group:"Taste profile"},{name:"Salty",group:"Taste profile"},{name:"Creamy",group:"Taste profile"},{name:"Astringent",group:"Taste profile"},{name:"Fermented",group:"Process"}
        ];

        const svgNS = "http://www.w3.org/2000/svg";
        const svg = document.getElementById("wheel");
        if (!svg) return;

        function polar(cx, cy, r, a) {
          const rad = (a - 90) * Math.PI / 180;
          return [cx + r * Math.cos(rad), cy + r * Math.sin(rad)];
        }

        function sectorPath(cx, cy, r1, r2, start, end) {
          const p1 = polar(cx, cy, r2, start);
          const p2 = polar(cx, cy, r2, end);
          const p3 = polar(cx, cy, r1, end);
          const p4 = polar(cx, cy, r1, start);
          const large = (end - start) > 180 ? 1 : 0;
          return `M ${p1[0]} ${p1[1]} A ${r2} ${r2} 0 ${large} 1 ${p2[0]} ${p2[1]} L ${p3[0]} ${p3[1]} A ${r1} ${r1} 0 ${large} 0 ${p4[0]} ${p4[1]} Z`;
        }

        function el(name, attrs = {}) {
          const n = document.createElementNS(svgNS, name);
          Object.entries(attrs).forEach(([k, v]) => n.setAttribute(k, v));
          return n;
        }

        function buildWheel() {
          svg.innerHTML = "";
          const palettes = {
            "Flavor notes": ["#9B5D75","#B96870","#C67E70","#C38D58","#B8A05D","#81976C","#5F9A8B","#4F8C91","#6F83A8","#8B79A8","#A4779B"],
            "Taste profile": ["#D0A35E","#C58B55","#B87955","#A96C5B","#8C8B65","#729A8B"],
            "Process": ["#7D9C72"]
          };

          const bg = el("circle", { cx: 250, cy: 250, r: 244, fill: "#06251F", stroke: "#D6B56F", "stroke-opacity": ".8", "stroke-width": "1.3" });
          svg.appendChild(bg);

          const start = -90, step = 360 / categories.length;
          const counters = { "Flavor notes": 0, "Taste profile": 0, "Process": 0 };

          categories.forEach((item, i) => {
            const a = start + i * step;
            const b = a + step - 0.8;
            const palette = palettes[item.group];
            const color = palette[counters[item.group]++ % palette.length];
            const g = el("g", { "data-name": item.name, tabindex: "0", role: "button", "aria-label": item.name + " descriptor" });
            const path = el("path", { d: sectorPath(250, 250, 112, 235, a, b), fill: color, "fill-opacity": ".94", stroke: "#F3E7D0", "stroke-opacity": ".65", "stroke-width": "1.1", class: "wedge" });
            g.appendChild(path);

            const mid = (a + b) / 2, radius = 177, [x, y] = polar(250, 250, radius, mid);
            let rot = mid + 90;
            if (mid > 90 && mid < 270) rot += 180;
            const text = el("text", { x, y, class: "wheel-label", transform: `rotate(${rot} ${x} ${y})`, "font-size": item.name.length > 13 ? "10.2" : "11.5" });
            
            let label = item.name.replace(" / ", " / ");
            if (label === "Cocoa / Chocolate") label = "Cocoa / Chocolate";
            if (label.length > 14) {
              let words = label.split(" ");
              let cut = Math.ceil(words.length / 2);
              const t1 = el("tspan", { x, y: y - 5 });
              t1.textContent = words.slice(0, cut).join(" ");
              const t2 = el("tspan", { x, y: y + 7 });
              t2.textContent = words.slice(cut).join(" ");
              text.append(t1, t2);
            } else {
              text.textContent = label;
            }
            g.appendChild(text);

            g.addEventListener("click", () => selectDescriptor(item.name));
            g.addEventListener("keydown", e => {
              if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                selectDescriptor(item.name);
              }
            });
            svg.appendChild(g);
          });

          // Center Hub
          const hubGroup = el("g", { id: "wheelCenterHub", style: "cursor: pointer;", role: "button", tabindex: "0", "aria-label": "Reset wheel view" });
          const outer = el("circle", { cx: 250, cy: 250, r: 108, fill: "#07382D", stroke: "#D8B96F", "stroke-width": "2" });
          const inner = el("circle", { cx: 250, cy: 250, r: 99, fill: "none", stroke: "#D8B96F", "stroke-opacity": ".35", "stroke-dasharray": "2 5", "stroke-width": "1" });
          const c1 = el("text", { x: 250, y: 230, class: "wheel-center", "font-size": "17", "letter-spacing": "2", fill: "#f5f0e7" });
          c1.textContent = "RT CHOCOS";
          const c2 = el("text", { x: 250, y: 254, class: "wheel-center", "font-size": "12", "letter-spacing": "1.2", fill: "#f5f0e7" });
          c2.textContent = "FLAVOR WHEEL";
          const c3 = el("text", { x: 250, y: 275, class: "wheel-center", "font-size": "9", fill: "#E2C88B", "letter-spacing": "1" });
          c3.textContent = "SMELL · TASTE · NOTICE";
          
          hubGroup.append(outer, inner, c1, c2, c3);
          hubGroup.addEventListener("click", resetWheel);
          hubGroup.addEventListener("keydown", e => {
            if (e.key === "Enter" || e.key === " ") {
              e.preventDefault();
              resetWheel();
            }
          });
          svg.appendChild(hubGroup);
        }

        function escapeHtml(s) {
          return String(s).replace(/[&<>"']/g, c => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#39;"
          }[c]));
        }

        function selectDescriptor(name) {
          const d = data[name];
          if (!d) return;

          document.querySelectorAll(".wedge").forEach(p => {
            p.classList.remove("selected");
            p.style.opacity = ".42";
          });

          const group = [...svg.querySelectorAll("g")].find(g => g.dataset.name === name);
          if (group) {
            const wedge = group.querySelector(".wedge");
            if (wedge) {
              wedge.classList.add("selected");
              wedge.style.opacity = "1";
            }
            group.focus({ preventScroll: true });
          }

          const groupEl = document.getElementById("detailGroup");
          if (groupEl) groupEl.textContent = d.group;

          const content = document.getElementById("detailContent");
          if (!content) return;

          content.innerHTML = `
            <h3>${escapeHtml(name)}</h3>
            <p class="detail-lead">${escapeHtml(d.meaning)}</p>
            <div class="callout"><b>What to notice</b>${escapeHtml(d.cue)}</div>
            <p><span class="mini-label">Try this now</span>${escapeHtml(d.try)}</p>
            <p><span class="mini-label">Familiar reference points</span>${escapeHtml(d.refs)}</p>
            <p><span class="mini-label">Don't confuse it with…</span>${escapeHtml(d.dist)}</p>
            <details>
              <summary>Explore the science</summary>
              <div class="science">
                <p>${escapeHtml(d.science)}</p>
                <div class="callout"><b>A useful insight</b>${escapeHtml(d.insight)}</div>
                <p class="motion-reduced">These are general mechanisms and plausible contributors, not a chemical diagnosis of a specific chocolate.</p>
              </div>
            </details>
            <div class="mini-label" style="margin-top:18px">What did you notice?</div>
            <div class="observations" role="group" aria-label="Record sensory observation">
              <button type="button" data-observation="I can smell it">◌ &nbsp; I can smell it</button>
              <button type="button" data-observation="I can taste it">◉ &nbsp; I can taste it</button>
              <button type="button" data-observation="I notice the texture">⌁ &nbsp; I notice the texture</button>
              <button type="button" data-observation="I'm not sure yet">? &nbsp; I'm not sure yet</button>
            </div>
            <div class="note-status" id="noteStatus" aria-live="polite"></div>
          `;

          content.classList.remove("fade-in");
          void content.offsetWidth;
          content.classList.add("fade-in");

          content.querySelectorAll("[data-observation]").forEach(btn => {
            btn.addEventListener("click", () => {
              content.querySelectorAll("[data-observation]").forEach(b => b.classList.remove("chosen"));
              btn.classList.add("chosen");
              const status = document.getElementById("noteStatus");
              if (status) {
                status.textContent = "Noted: " + btn.dataset.observation + ". Keep tasting at your own pace.";
              }
            });
          });
        }

        function resetWheel() {
          document.querySelectorAll(".wedge").forEach(p => {
            p.classList.remove("selected");
            p.style.opacity = "1";
          });

          const groupEl = document.getElementById("detailGroup");
          if (groupEl) groupEl.textContent = "START HERE";

          const content = document.getElementById("detailContent");
          if (!content) return;

          content.innerHTML = `
            <h3>Begin with what you notice.</h3>
            <p class="detail-lead">Choose any descriptor on the wheel. You can explore the science, try a small exercise, or simply continue tasting.</p>
            <div class="callout"><b>Try this</b>Smell your chocolate gently. Pause, then smell again. What comes to mind first?</div>
            <p>Your first impression is worth recording, even if it is “nothing distinctive yet.”</p>
          `;
          content.classList.remove("fade-in");
          void content.offsetWidth;
          content.classList.add("fade-in");
        }

        const resetBtn = document.getElementById("resetWheel");
        if (resetBtn) {
          resetBtn.addEventListener("click", resetWheel);
        }

        buildWheel();
      })();
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
<!-- Landing Sections Upgrade: Trust Strip, Table, Workshops Enhancement -->
<script src="<?php echo $pathPrefix; ?>js/landing-sections-upgrade.js?v=<?php echo file_exists(__DIR__ . '/js/landing-sections-upgrade.js') ? filemtime(__DIR__ . '/js/landing-sections-upgrade.js') : time(); ?>"></script>

<!-- --- ABOUT PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
