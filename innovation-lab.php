<?php
// innovation-lab.php - RT Chocos Innovation Lab Experience
$pathPrefix = "";
$isHome = false;
$pageTitle = "Innovation Lab — Ideas, Experiments & Evidence | RT Chocos";
$pageDescription = "A working space where curious minds explore new ingredients, techniques and formulations to shape a thoughtful chocolate future. Live experiments, failed batches, and sustainable cacao science.";
$schemaType = "CollectionPage";
$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Innovation Lab', 'item' => 'https://www.rtchocos.com/innovation-lab.php']
];

require_once __DIR__ . '/includes/db.php';

// Fetch dynamic data from database
try {
    $pdo = get_db();

    // 1. Dynamic Site & Section Settings
    $heroEyebrow = get_site_setting('lab_hero_eyebrow', 'IDEAS × EXPERIMENTS × EVIDENCE');
    $heroTitle = get_site_setting('lab_hero_title', 'Innovation Lab');
    $heroSubtitle = get_site_setting('lab_hero_subtitle', 'A working space where curious minds come together to explore new ingredients, techniques and ideas to shape a more thoughtful chocolate future.');
    $heroCtaText = get_site_setting('lab_hero_cta_text', 'STEP INTO THE LAB →');
    $heroCtaLink = get_site_setting('lab_hero_cta_link', '#currently-in-lab');
    $stickyNoteText = get_site_setting('lab_sticky_note', "Better Ingredients\nBigger Questions\nBrighter Chocolate");
    $heroImage = get_site_setting('lab_hero_image', 'assets/images/lab-hero.jpg');

    $whatIfEyebrow = get_site_setting('lab_what_if_eyebrow', 'IDEAS WE ARE EXPLORING NEXT.');
    $whatIfHeading = get_site_setting('lab_what_if_heading', 'What if?');
    $whatIfImage = get_site_setting('lab_what_if_image', 'assets/images/lab-what-if.jpg');

    $failedEyebrow = get_site_setting('lab_failed_batch_eyebrow', 'BECAUSE FAILURE IS DATA.');
    $failedHeading = get_site_setting('lab_failed_batch_heading', 'From a Failed Batch to a New Insight');

    $ingredientEyebrow = get_site_setting('lab_ingredient_eyebrow', 'NEW INGREDIENTS. NEW BEHAVIOURS. NEW POSSIBILITIES.');
    $ingredientHeading = get_site_setting('lab_ingredient_heading', 'Ingredient Innovation');

    $futureEyebrow = get_site_setting('lab_future_eyebrow', 'BOLDER QUESTIONS. BIGGER IMPACT.');
    $futureHeading = get_site_setting('lab_future_heading', 'Future of Chocolate');
    $futureText = get_site_setting('lab_future_text', 'Exploring ideas and technologies that can make chocolate more inclusive, resilient and regenerative — for people, communities and the planet.');
    $futureCtaText = get_site_setting('lab_future_cta_text', 'EXPLORE FUTURE IDEAS →');
    $futureCtaLink = get_site_setting('lab_future_cta_link', '#future-ideas');
    $futureImage = get_site_setting('lab_future_image', 'assets/images/lab-future.jpg');

    // 2. Query Experiments
    $experimentsStmt = $pdo->query("SELECT * FROM lab_experiments WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $experiments = $experimentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Query Failed Batches (Featured first)
    $failedStmt = $pdo->query("SELECT * FROM lab_failed_batches WHERE is_active = 1 ORDER BY is_featured DESC, sort_order ASC, id DESC");
    $failedBatches = $failedStmt->fetchAll(PDO::FETCH_ASSOC);
    $primaryFailed = !empty($failedBatches) ? $failedBatches[0] : null;

    // 4. Query What-If Questions
    $whatIfStmt = $pdo->query("SELECT * FROM lab_what_if_questions WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $whatIfQuestions = $whatIfStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Query Ingredient Categories
    $ingredientsStmt = $pdo->query("SELECT * FROM lab_ingredient_categories WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $ingredientCategories = $ingredientsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Query Future Pillars
    $pillarsStmt = $pdo->query("SELECT * FROM lab_future_pillars WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $futurePillars = $pillarsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // Graceful fallback if database connection has temporary issue
    $experiments = [];
    $failedBatches = [];
    $primaryFailed = null;
    $whatIfQuestions = [];
    $ingredientCategories = [];
    $futurePillars = [];
}

include 'includes/header.php';
?>

<!-- Master Innovation Lab Page Container -->
<div class="lab-page-wrapper">

  <!-- =================================================================
       1. HERO SECTION
       ================================================================= -->
  <section class="lab-hero" id="lab-hero">
    <div class="lab-hero-left">
      <div class="lab-eyebrow">
        <span><?php echo htmlspecialchars($heroEyebrow); ?></span>
      </div>
      <h1 class="lab-hero-title"><?php echo htmlspecialchars($heroTitle); ?></h1>
      <p class="lab-hero-subtitle"><?php echo nl2br(htmlspecialchars($heroSubtitle)); ?></p>
      <a href="<?php echo htmlspecialchars($heroCtaLink); ?>" class="lab-hero-cta">
        <span><?php echo htmlspecialchars($heroCtaText); ?></span>
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </a>
    </div>

    <div class="lab-hero-right">
      <div class="lab-hero-media-card">
        <img src="<?php echo htmlspecialchars($heroImage ?: 'assets/images/lab-hero.jpg'); ?>" alt="RT Chocos Innovation Lab Workbench" class="lab-hero-img" loading="eager" />
        
        <?php if (!empty(trim($stickyNoteText))): ?>
        <div class="lab-sticky-note" title="Laboratory Working Mantra">
          <div class="lab-sticky-note-pin"></div>
          <div><?php echo nl2br(htmlspecialchars($stickyNoteText)); ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- =================================================================
       2. EXPLORE THE LAB (SUB-NAV STRIP)
       ================================================================= -->
  <nav class="lab-subnav-strip" aria-label="Explore the Lab Categories">
    <div class="lab-subnav-inner">
      <div class="lab-subnav-header">EXPLORE<br>THE LAB</div>
      <ul class="lab-subnav-list">
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link active" data-filter="all" onclick="filterLabExperiments('all', this, event)">
            <!-- Cocoa Pod Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2C7.5 7 4 11 4 16a8 8 0 0 0 16 0c0-5-3.5-9-8-14z"/>
              <path d="M12 2v20"/>
            </svg>
            <span>ALL LAB WORK</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#ingredient-innovation" class="lab-subnav-link" data-filter="ingredient-innovation" onclick="filterLabExperiments('ingredient-innovation', this, event)">
            <!-- Sprout / Seed Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M7 20h10"/>
              <path d="M10 20c0-4 1.5-7 6-7 0-4-3-7-7-7-2.5 0-4.5 1.5-5 4 0 5 3 7 6 10z"/>
            </svg>
            <span>INGREDIENT INNOVATION</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link" data-filter="process-exploration" onclick="filterLabExperiments('process-exploration', this, event)">
            <!-- Gear / Wheel Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>PROCESS EXPLORATION</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link" data-filter="formulation-lab" onclick="filterLabExperiments('formulation-lab', this, event)">
            <!-- Flask / Chemical Beaker Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
              <line x1="8.5" y1="2" x2="15.5" y2="2"/>
            </svg>
            <span>FORMULATION LAB</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link" data-filter="sensory-insights" onclick="filterLabExperiments('sensory-insights', this, event)">
            <!-- Sensory Magnifier Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
              <path d="M11 8v6M8 11h6"/>
            </svg>
            <span>SENSORY INSIGHTS</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link" data-filter="stability-shelf-life" onclick="filterLabExperiments('stability-shelf-life', this, event)">
            <!-- Layers / Crystal Polymorph Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 2 7 12 12 22 7 12 2"/>
              <polyline points="2 17 12 22 22 17"/>
              <polyline points="2 12 12 17 22 12"/>
            </svg>
            <span>STABILITY &amp; SHELF LIFE</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#currently-in-lab" class="lab-subnav-link" data-filter="scale-applications" onclick="filterLabExperiments('scale-applications', this, event)">
            <!-- Scale Trending Up Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
              <polyline points="16 7 22 7 22 13"/>
            </svg>
            <span>SCALE &amp; APPLICATIONS</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#future-ideas" class="lab-subnav-link" data-filter="future-of-chocolate" onclick="filterLabExperiments('future-of-chocolate', this, event)">
            <!-- Leaf / Planet Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
              <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
            </svg>
            <span>FUTURE OF CHOCOLATE</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
      </ul>
    </div>
  </nav>

  <!-- =================================================================
       3. CURRENTLY IN THE LAB SECTION
       ================================================================= -->
  <section class="lab-section" id="currently-in-lab">
    <div class="lab-section-header-row">
      <div class="lab-section-title-wrap">
        <h2 class="lab-section-title">Currently in the Lab</h2>
        <div class="lab-section-rule"></div>
      </div>
      <a href="#currently-in-lab" class="lab-section-link" onclick="filterLabExperiments('all', null, event)">
        <span>VIEW ALL EXPERIMENTS</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </a>
    </div>

    <!-- Dynamic 4-Column Grid of Experiments -->
    <div class="lab-experiments-grid" id="experimentsGrid">
      <?php if (!empty($experiments)): ?>
        <?php foreach ($experiments as $exp): 
          $badgeColor = htmlspecialchars($exp['status_color'] ?: 'green');
        ?>
          <div class="lab-exp-card" data-category="<?php echo htmlspecialchars($exp['category']); ?>" onclick="openExperimentModal(<?php echo (int)$exp['id']; ?>)">
            <div class="lab-exp-media">
              <img src="<?php echo htmlspecialchars($exp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" loading="lazy" />
              <div class="lab-status-badge">
                <span class="status-dot <?php echo $badgeColor; ?>"></span>
                <span><?php echo htmlspecialchars($exp['status_badge']); ?></span>
              </div>
            </div>
            <div class="lab-exp-content">
              <h3 class="lab-exp-title"><?php echo htmlspecialchars($exp['title']); ?></h3>
              <p class="lab-exp-desc"><?php echo htmlspecialchars($exp['description']); ?></p>
              <div class="lab-exp-footer">
                <span class="lab-exp-tags"><?php echo htmlspecialchars($exp['tags'] ?: 'R&D | FORMULATION'); ?></span>
                <span class="lab-circle-btn" aria-label="Open Experiment Dossier">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </span>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #555; padding: 40px 0;">No active experiments found in the laboratory at this time.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- =================================================================
       4. TWO-COLUMN SPLIT: "WHAT IF?" & "FROM A FAILED BATCH TO A NEW INSIGHT"
       ================================================================= -->
  <section class="lab-split-section" id="ideas-and-failures">
    
    <!-- LEFT: WHAT IF? CARD -->
    <div class="lab-whatif-card">
      <div class="lab-whatif-header">
        <h2 class="lab-whatif-title"><?php echo htmlspecialchars($whatIfHeading); ?></h2>
        <div class="lab-eyebrow" style="margin-top: 6px; margin-bottom: 0;">
          <span><?php echo htmlspecialchars($whatIfEyebrow); ?></span>
        </div>
      </div>

      <div class="lab-whatif-body-split">
        <ul class="lab-whatif-list">
          <?php if (!empty($whatIfQuestions)): ?>
            <?php foreach ($whatIfQuestions as $q): ?>
              <li class="lab-whatif-item">
                <a href="<?php echo htmlspecialchars($q['link_url'] ?: '#currently-in-lab'); ?>" title="Explore question: <?php echo htmlspecialchars($q['question']); ?>">
                  <span class="lab-whatif-num"><?php echo htmlspecialchars($q['num_label']); ?></span>
                  <span class="lab-whatif-question"><?php echo htmlspecialchars($q['question']); ?></span>
                  <span class="lab-whatif-arrow">→</span>
                </a>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="lab-whatif-item" style="padding: 12px 0; color: #666;">No exploratory questions listed currently.</li>
          <?php endif; ?>
        </ul>

        <div class="lab-whatif-visual">
          <img src="<?php echo htmlspecialchars($whatIfImage ?: 'assets/images/lab-what-if.jpg'); ?>" alt="Artisanal chocolate sculpture with dried citrus and botanical accents" loading="lazy" />
        </div>
      </div>
    </div>

    <!-- RIGHT: FROM A FAILED BATCH TO A NEW INSIGHT CARD -->
    <div class="lab-failed-card">
      <div class="lab-failed-header">
        <h2 class="lab-failed-title"><?php echo htmlspecialchars($failedHeading); ?></h2>
        <div class="lab-eyebrow" style="margin-top: 6px; margin-bottom: 0;">
          <span><?php echo htmlspecialchars($failedEyebrow); ?></span>
        </div>
      </div>

      <?php if ($primaryFailed): ?>
      <div class="lab-failed-inner-box">
        <div class="lab-failed-img-wrap">
          <img src="<?php echo htmlspecialchars($primaryFailed['image_path'] ?: 'assets/images/lab-failed-batch.jpg'); ?>" alt="<?php echo htmlspecialchars($primaryFailed['title']); ?>" loading="lazy" />
        </div>
        <div class="lab-failed-content">
          <div class="lab-failed-case-tag"><?php echo htmlspecialchars($primaryFailed['title']); ?></div>
          
          <div class="lab-failed-data-row">
            <span class="label">Hypothesis</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['hypothesis']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">What happened?</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['what_happened']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">Why?</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['why_it_happened']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">What next?</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['what_next']); ?></span>
          </div>

          <button type="button" class="lab-failed-btn" onclick="openFailedBatchModal(<?php echo (int)$primaryFailed['id']; ?>)">
            <span>READ FULL STORY</span>
            <span>→</span>
          </button>
        </div>
      </div>
      <?php else: ?>
        <div class="lab-failed-inner-box" style="padding: 24px; text-align: center; color: #666;">
          No failed batch case studies documented yet. All experiments are running smoothly!
        </div>
      <?php endif; ?>
    </div>

  </section>

  <!-- =================================================================
       5. INGREDIENT INNOVATION SECTION
       ================================================================= -->
  <section class="lab-ingredient-section" id="ingredient-innovation">
    <div class="lab-ingredient-inner">
      <h2 class="lab-ingredient-title"><?php echo htmlspecialchars($ingredientHeading); ?></h2>
      <div class="lab-eyebrow" style="margin-top: -24px; margin-bottom: 32px;">
        <span><?php echo htmlspecialchars($ingredientEyebrow); ?></span>
      </div>

      <div class="lab-ingredients-grid">
        <?php if (!empty($ingredientCategories)): ?>
          <?php foreach ($ingredientCategories as $cat): ?>
            <a href="<?php echo htmlspecialchars($cat['link_url'] ?: '#currently-in-lab'); ?>" class="lab-ing-card" onclick="filterByIngredient('<?php echo htmlspecialchars($cat['title']); ?>', event)">
              <div class="lab-ing-photo-wrap">
                <img src="<?php echo htmlspecialchars($cat['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($cat['title']); ?>" loading="lazy" />
              </div>
              <span class="lab-ing-label">
                <span><?php echo htmlspecialchars($cat['title']); ?></span>
                <span>→</span>
              </span>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- =================================================================
       6. FUTURE OF CHOCOLATE SECTION
       ================================================================= -->
  <section class="lab-future-section" id="future-ideas">
    <div class="lab-future-grid">
      
      <!-- Left Column -->
      <div class="lab-future-left">
        <h2 class="lab-future-title"><?php echo htmlspecialchars($futureHeading); ?></h2>
        <div class="lab-eyebrow" style="margin-top: -8px; margin-bottom: 18px;">
          <span><?php echo htmlspecialchars($futureEyebrow); ?></span>
        </div>
        <p class="lab-future-text"><?php echo nl2br(htmlspecialchars($futureText)); ?></p>
        <a href="<?php echo htmlspecialchars($futureCtaLink); ?>" class="lab-future-cta">
          <span><?php echo htmlspecialchars($futureCtaText); ?></span>
          <span>→</span>
        </a>
      </div>

      <!-- Center Panoramic Image -->
      <div class="lab-future-mid">
        <img src="<?php echo htmlspecialchars($futureImage ?: 'assets/images/lab-future.jpg'); ?>" alt="Sustainable Cacao Agroforestry in Western Ghats" loading="lazy" />
      </div>

      <!-- Right Column: Future Pillars -->
      <div class="lab-future-right">
        <ul class="lab-pillars-list">
          <?php if (!empty($futurePillars)): ?>
            <?php foreach ($futurePillars as $pillar): ?>
              <li class="lab-pillar-item">
                <a href="<?php echo htmlspecialchars($pillar['link_url'] ?: '#future-ideas'); ?>" title="<?php echo htmlspecialchars($pillar['description'] ?: $pillar['title']); ?>">
                  <span><?php echo htmlspecialchars($pillar['title']); ?></span>
                  <span class="arrow">→</span>
                </a>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>

    </div>
  </section>

</div> <!-- End .lab-page-wrapper -->

<!-- =================================================================
     MODAL: EXPERIMENT DOSSIER MODAL
     ================================================================= -->
<div id="experimentModal" class="lab-modal-overlay" onclick="closeModalOnOverlay(event, this)">
  <div class="lab-modal-container" role="dialog" aria-modal="true">
    <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('experimentModal')" aria-label="Close Experiment Dossier">✕</button>
    <div class="lab-modal-header" id="modalExpHeader">
      <!-- Injected dynamically -->
    </div>
    <div class="lab-modal-body" id="modalExpBody">
      <!-- Injected dynamically -->
    </div>
  </div>
</div>

<!-- =================================================================
     MODAL: FAILED BATCH DEEP-DIVE MODAL
     ================================================================= -->
<div id="failedBatchModal" class="lab-modal-overlay" onclick="closeModalOnOverlay(event, this)">
  <div class="lab-modal-container" role="dialog" aria-modal="true">
    <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('failedBatchModal')" aria-label="Close Failed Batch Dossier">✕</button>
    <div class="lab-modal-header" id="modalFailHeader">
      <!-- Injected dynamically -->
    </div>
    <div class="lab-modal-body" id="modalFailBody">
      <!-- Injected dynamically -->
    </div>
  </div>
</div>

<!-- Raw Client-Side Data Store for Instant Modal Rendering -->
<script>
window.LAB_EXPERIMENTS = <?php echo json_encode($experiments, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
window.LAB_FAILED_BATCHES = <?php echo json_encode($failedBatches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

function filterLabExperiments(category, el, e) {
  if (e) e.preventDefault();
  
  // Highlight active tab
  document.querySelectorAll('.lab-subnav-link').forEach(link => link.classList.remove('active'));
  if (el) el.classList.add('active');

  const cards = document.querySelectorAll('.lab-exp-card');
  cards.forEach(card => {
    const cardCat = card.getAttribute('data-category');
    if (category === 'all' || cardCat === category) {
      card.style.display = 'flex';
      card.style.animation = 'fadeIn 0.3s ease';
    } else {
      card.style.display = 'none';
    }
  });

  // Smooth scroll to experiments section if triggered from navigation
  const expSection = document.getElementById('currently-in-lab');
  if (expSection && el) {
    const offset = 140;
    const bodyRect = document.body.getBoundingClientRect().top;
    const elementRect = expSection.getBoundingClientRect().top;
    const elementPosition = elementRect - bodyRect;
    const offsetPosition = elementPosition - offset;
    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth'
    });
  }
}

function filterByIngredient(ingredientName, e) {
  if (e) e.preventDefault();
  // Filter experiments that mention this ingredient or tag
  const query = ingredientName.toLowerCase();
  const cards = document.querySelectorAll('.lab-exp-card');
  let matched = 0;
  cards.forEach(card => {
    const text = card.innerText.toLowerCase();
    if (text.includes(query) || text.includes(query.split(' ')[0])) {
      card.style.display = 'flex';
      matched++;
    } else {
      card.style.display = 'none';
    }
  });

  if (matched === 0) {
    // Show all if no direct match
    cards.forEach(c => c.style.display = 'flex');
  }

  const expSection = document.getElementById('currently-in-lab');
  if (expSection) {
    expSection.scrollIntoView({ behavior: 'smooth' });
  }
}

function openExperimentModal(id) {
  const exp = window.LAB_EXPERIMENTS.find(item => Number(item.id) === Number(id));
  if (!exp) return;

  const header = document.getElementById('modalExpHeader');
  const body = document.getElementById('modalExpBody');

  header.innerHTML = `
    <div class="lab-status-badge" style="position: static; margin-bottom: 12px; display: inline-flex;">
      <span class="status-dot ${exp.status_color || 'green'}"></span>
      <span>${exp.status_badge || 'IN PROGRESS'}</span>
    </div>
    <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 28px; color: #122217; margin: 0 0 8px;">${escapeHtml(exp.title)}</h2>
    <div style="font-size: 11px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #6C7E72;">
      ${escapeHtml(exp.tags || 'LABORATORY DOSSIER')}
    </div>
  `;

  let innerHTML = '';
  if (exp.image_path) {
    innerHTML += `<div style="border-radius: 6px; overflow: hidden; max-height: 280px; margin-bottom: 24px;">
      <img src="${escapeHtml(exp.image_path)}" style="width: 100%; height: 100%; object-fit: cover;" alt="${escapeHtml(exp.title)}">
    </div>`;
  }

  innerHTML += `
    <div style="font-size: 15px; line-height: 1.6; color: #2A3C30; margin-bottom: 24px; font-style: italic; background: #FAF5EB; padding: 14px 18px; border-left: 3px solid #14251B; border-radius: 2px;">
      "${escapeHtml(exp.description)}"
    </div>

    <div class="lab-dossier-grid">
      ${exp.hypothesis ? `
      <div class="lab-dossier-card">
        <h4>Hypothesis</h4>
        <p>${escapeHtml(exp.hypothesis)}</p>
      </div>` : ''}

      ${exp.methodology ? `
      <div class="lab-dossier-card">
        <h4>Methodology & Setup</h4>
        <p>${escapeHtml(exp.methodology)}</p>
      </div>` : ''}

      ${exp.findings ? `
      <div class="lab-dossier-card">
        <h4>Observations & Findings</h4>
        <p>${escapeHtml(exp.findings)}</p>
      </div>` : ''}

      ${exp.next_steps ? `
      <div class="lab-dossier-card">
        <h4>Next Iteration Steps</h4>
        <p>${escapeHtml(exp.next_steps)}</p>
      </div>` : ''}
    </div>
  `;

  if (exp.full_content) {
    innerHTML += `
      <div style="border-top: 1px solid #E5DCCE; padding-top: 24px; margin-top: 20px;">
        <h4 style="font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: #14251B; margin-bottom: 14px;">Detailed Lab Notes</h4>
        <div style="font-size: 14px; line-height: 1.7; color: #384A3F;">
          ${exp.full_content}
        </div>
      </div>
    `;
  }

  body.innerHTML = innerHTML;
  document.getElementById('experimentModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function openFailedBatchModal(id) {
  const fail = window.LAB_FAILED_BATCHES.find(item => Number(item.id) === Number(id));
  if (!fail) return;

  const header = document.getElementById('modalFailHeader');
  const body = document.getElementById('modalFailBody');

  header.innerHTML = `
    <div style="font-size: 10px; font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; color: #B45309; margin-bottom: 8px;">
      ${escapeHtml(fail.badge_label || 'BECAUSE FAILURE IS DATA.')}
    </div>
    <h2 style="font-family: 'Playfair Display', Georgia, serif; font-size: 28px; color: #122217; margin: 0;">${escapeHtml(fail.title)}</h2>
  `;

  let innerHTML = '';
  if (fail.image_path) {
    innerHTML += `<div style="border-radius: 6px; overflow: hidden; max-height: 260px; margin-bottom: 24px;">
      <img src="${escapeHtml(fail.image_path)}" style="width: 100%; height: 100%; object-fit: cover;" alt="${escapeHtml(fail.title)}">
    </div>`;
  }

  innerHTML += `
    <div class="lab-dossier-grid">
      <div class="lab-dossier-card">
        <h4>1. Initial Hypothesis</h4>
        <p>${escapeHtml(fail.hypothesis)}</p>
      </div>
      <div class="lab-dossier-card" style="border-left: 3px solid #DC2626;">
        <h4>2. What Happened? (The Failure)</h4>
        <p>${escapeHtml(fail.what_happened)}</p>
      </div>
      <div class="lab-dossier-card" style="border-left: 3px solid #D97706;">
        <h4>3. Root Cause Analysis (Why?)</h4>
        <p>${escapeHtml(fail.why_it_happened)}</p>
      </div>
      <div class="lab-dossier-card" style="border-left: 3px solid #16A34A;">
        <h4>4. The Breakthrough (What Next?)</h4>
        <p>${escapeHtml(fail.what_next)}</p>
      </div>
    </div>
  `;

  if (fail.full_story) {
    innerHTML += `
      <div style="background: #FFFFFF; border: 1px solid #E6DED2; border-radius: 6px; padding: 24px; margin-top: 18px;">
        <h4 style="font-family: 'Inter', sans-serif; font-size: 11.5px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: #14251B; margin-bottom: 12px;">Full Post-Mortem Story &amp; Science</h4>
        <div style="font-size: 14px; line-height: 1.7; color: #384A3F;">
          ${fail.full_story}
        </div>
      </div>
    `;
  }

  body.innerHTML = innerHTML;
  document.getElementById('failedBatchModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function closeLabModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  }
}

function closeModalOnOverlay(e, modalEl) {
  if (e.target === modalEl) {
    modalEl.classList.remove('active');
    document.body.style.overflow = '';
  }
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeLabModal('experimentModal');
    closeLabModal('failedBatchModal');
  }
});

function escapeHtml(text) {
  if (!text) return '';
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<?php include 'includes/footer.php'; ?>
