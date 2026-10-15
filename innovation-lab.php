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
    // Helper to safely load existing image or fallback
    function getSafeLabImg($path, $fallback) {
        $clean = trim((string)$path);
        if (!empty($clean) && file_exists(__DIR__ . '/' . $clean)) {
            return $clean;
        }
        return $fallback;
    }

    $heroEyebrow = trim((string)get_site_setting('lab_hero_eyebrow', '')) ?: 'IDEAS × EXPERIMENTS × EVIDENCE';
    $heroTitle = trim((string)get_site_setting('lab_hero_title', '')) ?: 'Innovation Lab';
    $heroSubtitle = trim((string)get_site_setting('lab_hero_subtitle', '')) ?: 'A working space where curious minds come together to explore new ingredients, techniques and ideas to shape a more thoughtful chocolate future.';
    $heroCtaText = trim((string)get_site_setting('lab_hero_cta_text', '')) ?: 'STEP INTO THE LAB →';
    $heroCtaLink = trim((string)get_site_setting('lab_hero_cta_link', '')) ?: '#currently-in-lab';
    $stickyNoteText = trim((string)get_site_setting('lab_sticky_note', '')) ?: "Better Ingredients\nBigger Questions\nBrighter Chocolate";
    $heroImage = getSafeLabImg(get_site_setting('lab_hero_image', ''), 'assets/images/lab-hero.jpg');

    $whatIfEyebrow = trim((string)get_site_setting('lab_what_if_eyebrow', '')) ?: 'IDEAS WE ARE EXPLORING NEXT.';
    $whatIfHeading = trim((string)get_site_setting('lab_what_if_heading', '')) ?: 'What if?';
    $whatIfImage = getSafeLabImg(get_site_setting('lab_what_if_image', ''), 'assets/images/lab-what-if.jpg');

    $failedEyebrow = trim((string)get_site_setting('lab_failed_batch_eyebrow', '')) ?: 'BECAUSE FAILURE IS DATA.';
    $failedHeading = trim((string)get_site_setting('lab_failed_batch_heading', '')) ?: 'From a Failed Batch to a New Insight';

    $ingredientEyebrow = trim((string)get_site_setting('lab_ingredient_eyebrow', '')) ?: 'NEW INGREDIENTS. NEW BEHAVIOURS. NEW POSSIBILITIES.';
    $ingredientHeading = trim((string)get_site_setting('lab_ingredient_heading', '')) ?: 'Ingredient Innovation';

    $futureEyebrow = trim((string)get_site_setting('lab_future_eyebrow', '')) ?: 'BOLDER QUESTIONS. BIGGER IMPACT.';
    $futureHeading = trim((string)get_site_setting('lab_future_heading', '')) ?: 'Future of Chocolate';
    $futureText = trim((string)get_site_setting('lab_future_text', '')) ?: 'Exploring ideas and technologies that can make chocolate more inclusive, resilient and regenerative — for people, communities and the planet.';
    $futureCtaText = trim((string)get_site_setting('lab_future_cta_text', '')) ?: 'EXPLORE FUTURE IDEAS →';
    $futureCtaLink = trim((string)get_site_setting('lab_future_cta_link', '')) ?: '#future-ideas';
    $futureImage = getSafeLabImg(get_site_setting('lab_future_image', ''), 'assets/images/lab-future.jpg');

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
<link rel="stylesheet" href="css/innovation-lab.css?v=<?php echo filemtime(__DIR__ . '/css/innovation-lab.css'); ?>">

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
        <img src="<?php echo htmlspecialchars($heroImage); ?>" alt="RT Chocos Innovation Lab Workbench" class="lab-hero-img" loading="eager" />
        
        <?php if (!empty(trim($stickyNoteText))): ?>
        <div class="lab-sticky-note" title="Laboratory Working Mantra">
          <div class="lab-mantra-tag">LABORATORY MANTRA</div>
          <div class="lab-mantra-text"><?php echo nl2br(htmlspecialchars($stickyNoteText)); ?></div>
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
          <a href="#currently-in-lab" class="lab-subnav-link active" data-section="currently-in-lab" onclick="navigateLabSection('currently-in-lab', this, event)">
            <!-- Stacked Layers Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <polygon points="12 2 2 7 12 12 22 7 12 2"/>
              <polyline points="2 12 12 17 22 12"/>
              <polyline points="2 17 12 22 22 17"/>
            </svg>
            <span>ALL LAB WORK</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#ingredient-innovation" class="lab-subnav-link" data-section="ingredient-innovation" onclick="navigateLabSection('ingredient-innovation', this, event)">
            <!-- Leaf with Vein Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/>
              <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>
            </svg>
            <span>INGREDIENT INNOVATION</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#what-if" class="lab-subnav-link" data-section="what-if" onclick="navigateLabSection('what-if', this, event)">
            <!-- Lightbulb with Rays Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="1.5" x2="12" y2="3.5"/>
              <line x1="5.5" y1="4.5" x2="7.2" y2="6.2"/>
              <line x1="18.5" y1="4.5" x2="16.8" y2="6.2"/>
              <path d="M14.8 15c.2-.9.6-1.6 1.4-2.4 1-.9 1.4-2 1.4-3.2a5.6 5.6 0 0 0-11.2 0c0 1.2.4 2.3 1.4 3.2.8.8 1.2 1.5 1.4 2.4"/>
              <line x1="9.5" y1="18" x2="14.5" y2="18"/>
              <line x1="10.5" y1="21" x2="13.5" y2="21"/>
            </svg>
            <span>WHAT IF?</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#failed-experiments" class="lab-subnav-link" data-section="failed-experiments" onclick="navigateLabSection('failed-experiments', this, event)">
            <!-- Flask / Chemical Beaker Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M10 2v5.5L4.4 17.6A2 2 0 0 0 6.1 20h11.8a2 2 0 0 0 1.7-2.4L14 7.5V2"/>
              <line x1="8.5" y1="2" x2="15.5" y2="2"/>
            </svg>
            <span>FAILED EXPERIMENTS</span>
            <span class="arrow-icon">→</span>
          </a>
        </li>
        <li class="lab-subnav-item">
          <a href="#future-ideas" class="lab-subnav-link" data-section="future-ideas" onclick="navigateLabSection('future-ideas', this, event)">
            <!-- Sprouting Cocoa Tree / Plant Icon -->
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 21h12"/>
              <path d="M10 21a2 2 0 0 1 4 0"/>
              <path d="M12 19V11"/>
              <path d="M12 14c-2.5 0-4.5-1.5-4.5-4"/>
              <path d="M12 14c2.5 0 4.5-1.5 4.5-4"/>
              <circle cx="12" cy="6" r="2.2"/>
              <circle cx="7" cy="9.5" r="1.8"/>
              <circle cx="17" cy="9.5" r="1.8"/>
              <path d="M12 8.2V11"/>
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
    <div class="lab-whatif-card" id="what-if">
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
    <div class="lab-failed-card" id="failed-experiments">
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
            <span class="label">1. The Hypothesis</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['hypothesis']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">2. What Happened?</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['what_happened']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">3. Understanding The Science</span>
            <span class="text"><?php echo htmlspecialchars($primaryFailed['why_it_happened']); ?></span>
          </div>

          <div class="lab-failed-data-row">
            <span class="label">4. The Learning</span>
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
      <div class="lab-eyebrow" style="margin-bottom: 10px;">
        <span><?php echo htmlspecialchars($ingredientEyebrow); ?></span>
      </div>
      <div class="lab-ingredient-heading-row">
        <h2 class="lab-ingredient-title"><?php echo htmlspecialchars($ingredientHeading); ?></h2>
        <p class="lab-ingredient-subtext">Exploring native botanical behaviours, regional harvest dynamics, and sensory possibilities with Indian terroir cacao.</p>
      </div>

      <div class="lab-ingredients-grid">
        <?php if (!empty($ingredientCategories)): ?>
          <?php foreach ($ingredientCategories as $cat): ?>
            <a href="javascript:void(0)" class="lab-ing-card" onclick="openIngredientModal(<?php echo (int)$cat['id']; ?>, event)" role="button" aria-haspopup="dialog" aria-label="Explore <?php echo htmlspecialchars($cat['title']); ?> dossier">
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
        <div class="lab-eyebrow" style="margin-top: -6px; margin-bottom: 14px;">
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
            <?php foreach ($futurePillars as $idx => $pillar): ?>
              <li class="lab-pillar-item">
                <a href="<?php echo htmlspecialchars($pillar['link_url'] ?: '#future-ideas'); ?>" title="<?php echo htmlspecialchars($pillar['description'] ?: $pillar['title']); ?>">
                  <div class="lab-pillar-content">
                    <span class="lab-pillar-num"><?php echo sprintf('%02d', $idx + 1); ?></span>
                    <div class="lab-pillar-info">
                      <div class="lab-pillar-name"><?php echo htmlspecialchars($pillar['title']); ?></div>
                      <?php if (!empty($pillar['description'])): ?>
                        <div class="lab-pillar-desc"><?php echo htmlspecialchars($pillar['description']); ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
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
    <div class="lab-modal-header" id="modalFailHeader">
      <!-- Injected dynamically -->
    </div>
    <div class="lab-modal-body" id="modalFailBody">
      <!-- Injected dynamically -->
    </div>
  </div>
</div>

<!-- =================================================================
     MODAL: INGREDIENT INNOVATION DOSSIER MODAL
     ================================================================= -->
<div id="ingredientModal" class="lab-modal-overlay" onclick="closeModalOnOverlay(event, this)">
  <div class="lab-modal-container" role="dialog" aria-modal="true">
    <div class="lab-modal-header" id="modalIngHeader">
      <!-- Injected dynamically -->
    </div>
    <div class="lab-modal-body" id="modalIngBody">
      <!-- Injected dynamically -->
    </div>
  </div>
</div>

<!-- Floating Toast Notification -->
<div id="labToast" class="lab-toast" role="status" aria-live="polite">
  <span style="color: #22C55E; font-size: 14px;">✓</span>
  <span id="labToastMsg">Dossier link copied to clipboard!</span>
</div>

<!-- Raw Client-Side Data Store for Instant Modal Rendering -->
<script>
window.LAB_EXPERIMENTS = <?php echo json_encode($experiments, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
window.LAB_FAILED_BATCHES = <?php echo json_encode($failedBatches, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
window.LAB_INGREDIENTS = <?php echo json_encode($ingredientCategories, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Comprehensive Laboratory Telemetry & Science Specs
const LAB_EXP_SPECS = {
  1: {
    batchRef: 'RT-EXP-01 · CBE-POLY',
    origin: 'Idukki Single Estate, Kerala (70% Cacao)',
    polymorph: 'Beta-V (Form V) Triglyceride Matrix (Tm 34.2°C)',
    particleSize: '< 18.2 µm (D90 Laser Diffraction)',
    conching: '36 Hours Longitudinal Dry Conche @ 48°C',
    waterActivity: '0.31 Aw (Optimal Moisture Inactivity)',
    sensory: [
      { name: 'Acoustic Crystal Snap', score: '9.2 / 10', fill: '92%', desc: 'Crisp acoustic fracture' },
      { name: 'Palate Melt Velocity', score: '34.2°C', fill: '95%', desc: 'Silky, zero-waxiness glide' },
      { name: 'Aroma Volatiles', score: '94%', fill: '94%', desc: 'Deep warm cacao & fruit esters' },
      { name: 'Thermal Stability', score: '33.5°C', fill: '88%', desc: 'Slump-resistant ambient curve' }
    ],
    apparatus: [
      'Selmi Continuous Tempering',
      'DSC Thermal Calorimeter',
      'Santha Granite Melanger',
      'Laser Diffraction Sizer'
    ]
  },
  2: {
    batchRef: 'RT-EXP-02 · HERITAGE-FRUIT',
    origin: 'Ratnagiri Alphonso Mango & Konkan Jamun',
    polymorph: 'Pre-Tempered Beta-V Lipid Film (Aw < 0.35)',
    particleSize: '< 17.8 µm Base Chocolate Matrix',
    conching: 'Cold-Shear Melanger Folding @ 38°C',
    waterActivity: '0.33 Aw (Bloom Inactive)',
    sensory: [
      { name: 'Fruit Volatile Retention', score: '9.6 / 10', fill: '96%', desc: 'Punchy tropical citric acidity' },
      { name: 'Inclusion Crunch Half-Life', score: '+340%', fill: '92%', desc: 'Stable after 6 weeks ambient' },
      { name: 'Crystal Temper Index', score: '5.2 CTU', fill: '89%', desc: 'Well-formed crystal network' },
      { name: 'Sugar-Bloom Resistance', score: '100%', fill: '100%', desc: 'Zero surface recrystallization' }
    ],
    apparatus: [
      'Lyophilization Freeze-Dryer',
      'Ultra-Sonic Lipid Barrier Atomizer',
      'Rotational Water Activity Meter',
      'Texture Acoustic Fracture Meter'
    ]
  },
  3: {
    batchRef: 'RT-EXP-03 · UNREFINED-CANE',
    origin: 'Western UP Artisan Khandsari & Malabar Cacao',
    polymorph: 'Beta-V Stabilized Matrix with Trace Minerals',
    particleSize: '< 19.5 µm Fine Sub-Palatal Grind',
    conching: 'Stepwise Heating (42°C → 58°C Forced Ventilation)',
    waterActivity: '0.34 Aw (Stable Dispersion)',
    sensory: [
      { name: 'Caramel & Malt Complexity', score: '9.5 / 10', fill: '95%', desc: 'Rich molasses & wild honey' },
      { name: 'Palatal Smoothness', score: '9.1 / 10', fill: '91%', desc: 'Granite stone micro-milled' },
      { name: 'Tannin Softening Index', score: '+45%', fill: '85%', desc: 'Minerals balance cacao astringency' },
      { name: 'Viscosity Fluidity @ 45°C', score: '2.1 Pa·s', fill: '88%', desc: 'Easy continuous mold flow' }
    ],
    apparatus: [
      'Santha Granite Stone Refiner',
      'Dry-Air Venting Extraction Conch',
      'Brookfield Rotational Viscometer',
      'Karl Fischer Moisture Titrator'
    ]
  },
  4: {
    batchRef: 'RT-EXP-04 · EMULSION-LAB',
    origin: 'Malabar Roasted Cashews & Konkan Cold-Pressed Coconut',
    polymorph: 'Lauric-Oleic Interlocking Triacylglycerols',
    particleSize: '< 16.5 µm Ultrafine Silky Dispersion',
    conching: '24 Hours Micro-Shear Milling @ 50°C',
    waterActivity: '0.28 Aw (Preservative-Free Shelf Life)',
    sensory: [
      { name: 'Oil Separation Index', score: '< 1.8%', fill: '98%', desc: '90-day phase stability' },
      { name: 'Spreadability Spectrum', score: '18°C–32°C', fill: '94%', desc: 'Immediate bread/pastry glide' },
      { name: 'Palatal Creaminess', score: '9.6 / 10', fill: '96%', desc: 'Velvety, non-greasy mouthfeel' },
      { name: 'Clean Label Purity', score: '100%', fill: '100%', desc: 'Zero hydrogenated fats or lecithin' }
    ],
    apparatus: [
      'High-Shear Colloid Mill',
      'Inert Nitrogen Gas Purging Line',
      'Centrifugal Accelerated Separation Rig',
      'Digital Cone-and-Plate Rheometer'
    ]
  }
};

function getExpSpecs(exp) {
  if (LAB_EXP_SPECS[exp.id]) return LAB_EXP_SPECS[exp.id];
  return {
    batchRef: `RT-EXP-0${exp.id} · FORMULATION`,
    origin: 'Single Estate Western Ghats Cacao',
    polymorph: 'Beta-V Triglyceride Matrix (Tm 33.8°C)',
    particleSize: '< 18.5 µm (Micro-Refined)',
    conching: '36 Hours Longitudinal Conching',
    waterActivity: '0.32 Aw (Shelf-Stable Dispersion)',
    sensory: [
      { name: 'Crystal Temper Snap', score: '9.1 / 10', fill: '91%', desc: 'Clean acoustic snap' },
      { name: 'Palate Melt Dynamics', score: '33.8°C', fill: '90%', desc: 'Silky body-temp dissolution' },
      { name: 'Flavor Preservation', score: '92%', fill: '92%', desc: 'Balanced single-origin terroir' },
      { name: 'Thermal Stability', score: '33.0°C', fill: '85%', desc: 'Ambient shape resilience' }
    ],
    apparatus: [
      'Continuous Tempering Unit',
      'Stone Granite Melanger',
      'Laser Diffraction Analyzer',
      'Viscosity Flow Gauge'
    ]
  };
}

const LAB_FAIL_SPECS = {
  1: {
    batchRef: 'FAIL-POSTMORTEM-01 · GUAVA-TRIAL',
    defectMode: 'Terpene Volatilization & Hydrophilic Sugar Seizure',
    trialScale: '25 kg Pilot Melanger Batch',
    criticalMoisture: 'Aw spiked to 0.58 (Safety Threshold: 0.35)',
    viscosityImpact: '+280% Paste Thickening @ 44°C',
    correctiveChecklist: [
      'Mandatory Lipid Envelope: Pre-coat all freeze-dried fruits with a 5µm atomized cocoa butter barrier before refiner introduction.',
      'Conching Thermal Ceiling: Capped exhaust conch temperature at 38°C for volatile fruit inclusion formulas to prevent terpene boil-off.',
      'In-Line Aw Screening: Installed real-time water activity inspection for all incoming dried fruit cultivars.'
    ]
  }
};

const LAB_ING_SPECS = {
  1: { // Indian Fruits
    taxonomy: 'Mangifera indica / Syzygium cumini / Psidium guajava',
    origin: 'Ratnagiri, Konkan Belt & Allahabad Orchards',
    chemistry: 'High Natural Pectin, Citric/Malic Acidity, Invert Fructose',
    temperDelta: '+0.4°C Crystallization Offset',
    pairings: ['70% Kerala Dark', '55% Malabar Milk', 'Spiced Fruit Ganache']
  },
  2: { // Nuts & Seeds
    taxonomy: 'Anacardium occidentale / Prunus dulcis (Mamra) / Buchanania lanzan',
    origin: 'Malabar Coast, Kashmir Valley & Central India Forests',
    chemistry: 'High Oleic Fatty Acids (62%), Plant Protein Emulsifiers',
    temperDelta: '-0.8°C Polymorph Softening Delta',
    pairings: ['Stone-Ground Cashew Praline', 'Roasted Almond Dragees', 'Dark Truffle Fillings']
  },
  3: { // Alternative Sugars
    taxonomy: 'Saccharum officinarum (Artisan Clarified) / Borassus flabellifer',
    origin: 'Western Uttar Pradesh & Southern Tamil Nadu Palms',
    chemistry: 'Unbleached Sucrose with Iron, Potassium & Mineral Salts',
    temperDelta: 'Standard Beta-V (Moisture Controlled)',
    pairings: ['Single-Origin Dark Cacao', 'Warm Spice Bars', 'Artisan Caramel Creams']
  },
  4: { // Dairy & Plant-Based
    taxonomy: 'Bos indicus (A2 Gir/Sahiwal Vedic) / Cocos nucifera / Avena sativa',
    origin: 'Gujarat Vedic Dairies & Coastal Kerala Coconut Groves',
    chemistry: 'Short-Chain Lauric Fats & A2 Beta-Casein Protein Lattice',
    temperDelta: '-1.2°C Quick-Melt Palatal Shift',
    pairings: ['Malted Milk Chocolate', 'Vegan White Ganache', 'Creamy Coconut Mousse']
  },
  5: { // Botanicals & Spices
    taxonomy: 'Piper nigrum / Elettaria cardamomum / Illicium verum',
    origin: 'Wayanad High Ranges & Idukki Cardamom Hills (Western Ghats)',
    chemistry: 'Lipophilic Essential Oils (Piperine, Cineole, Terpenyl Acetate)',
    temperDelta: 'Zero Polymorphic Lattice Interference',
    pairings: ['72% High-Tannin Dark', 'Cardamom Sea-Salt Bars', 'Winter Solstice Edition']
  }
};

function showLabToast(msg) {
  const toast = document.getElementById('labToast');
  const msgEl = document.getElementById('labToastMsg');
  if (toast && msgEl) {
    msgEl.textContent = msg;
    toast.classList.add('active');
    setTimeout(() => {
      toast.classList.remove('active');
    }, 2400);
  }
}

function copyLabDossier(slug, title) {
  const url = window.location.origin + window.location.pathname + '#' + slug;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(() => {
      showLabToast(`✓ Copied link for "${title}"!`);
    }).catch(() => {
      prompt('Copy dossier link:', url);
    });
  } else {
    prompt('Copy dossier link:', url);
  }
}

function printLabDossier() {
  window.print();
}

function navigateExperiment(currentId, step) {
  const list = window.LAB_EXPERIMENTS || [];
  const currentIndex = list.findIndex(e => Number(e.id) === Number(currentId));
  if (currentIndex === -1) return;
  let newIndex = currentIndex + step;
  if (newIndex < 0) newIndex = list.length - 1;
  if (newIndex >= list.length) newIndex = 0;
  openExperimentModal(list[newIndex].id);
}

function navigateLabSection(targetId, el, e) {
  if (e) e.preventDefault();
  
  // Highlight active tab
  document.querySelectorAll('.lab-subnav-link').forEach(link => link.classList.remove('active'));
  if (el) el.classList.add('active');

  // If ALL LAB WORK clicked, show all experiments
  if (targetId === 'currently-in-lab') {
    const cards = document.querySelectorAll('.lab-exp-card');
    cards.forEach(card => {
      card.style.display = 'flex';
      card.style.animation = 'fadeIn 0.3s ease';
    });
  }

  const targetEl = document.getElementById(targetId);
  if (targetEl) {
    const offset = 140;
    const bodyRect = document.body.getBoundingClientRect().top;
    const elementRect = targetEl.getBoundingClientRect().top;
    const elementPosition = elementRect - bodyRect;
    const offsetPosition = elementPosition - offset;
    window.scrollTo({
      top: Math.max(0, offsetPosition),
      behavior: 'smooth'
    });

    if (history.pushState) {
      history.pushState(null, null, '#' + targetId);
    }
  }
}

function filterLabExperiments(category, el, e) {
  if (e) e.preventDefault();
  
  // Highlight active tab
  if (el) {
    document.querySelectorAll('.lab-subnav-link').forEach(link => link.classList.remove('active'));
    el.classList.add('active');
  } else {
    const allTab = document.querySelector('.lab-subnav-link[data-section="currently-in-lab"]');
    if (allTab) {
      document.querySelectorAll('.lab-subnav-link').forEach(link => link.classList.remove('active'));
      allTab.classList.add('active');
    }
  }

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

  const specs = getExpSpecs(exp);
  const header = document.getElementById('modalExpHeader');
  const body = document.getElementById('modalExpBody');

  header.innerHTML = `
    <div class="lab-modal-header-inner">
      <div class="lab-modal-header-left">
        <div class="lab-modal-meta-row">
          <div class="lab-status-badge" style="position: static; margin-bottom: 0; display: inline-flex;">
            <span class="status-dot ${exp.status_color || 'green'}"></span>
            <span>${exp.status_badge || 'IN PROGRESS'}</span>
          </div>
          <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: #166534; background: #EBF7EE; padding: 4px 10px; border-radius: 4px;">
            ${escapeHtml(specs.batchRef)}
          </span>
          <span class="lab-modal-category-tag">${escapeHtml(exp.tags || 'LABORATORY DOSSIER')}</span>
        </div>
        <h2 class="lab-modal-title">${escapeHtml(exp.title)}</h2>
      </div>
      <div class="lab-modal-header-right">
        <div class="lab-modal-header-actions">
          <button type="button" class="lab-modal-tool-btn" onclick="copyLabDossier('${escapeHtml(exp.slug || 'exp-' + exp.id)}', '${escapeHtml(exp.title)}')" title="Copy Dossier Link">
            <span>📋</span>
            <span>SHARE</span>
          </button>
          <button type="button" class="lab-modal-tool-btn" onclick="printLabDossier()" title="Print / Export Dossier">
            <span>🖨️</span>
            <span>PRINT</span>
          </button>
          <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('experimentModal')" aria-label="Close Experiment Dossier">
            <span>CLOSE</span>
            <span class="close-btn-x">✕</span>
          </button>
        </div>
      </div>
    </div>
  `;

  let innerHTML = '';
  if (exp.image_path) {
    innerHTML += `
      <div class="lab-modal-hero-frame">
        <img src="${escapeHtml(exp.image_path)}" alt="${escapeHtml(exp.title)}">
        <div class="lab-modal-hero-badge">
          <span>🔬 RT CHOCOS R&amp;D KITCHEN · WESTERN GHATS BATCH LAB #${exp.id}</span>
        </div>
      </div>
    `;
  }

  if (exp.description) {
    innerHTML += `
      <div class="lab-inquiry-callout">
        <div class="lab-inquiry-tag">
          <span>💡</span>
          <span>THE HYPOTHESIS &amp; RESEARCH INQUIRY</span>
        </div>
        <blockquote class="lab-inquiry-quote">
          “${escapeHtml(exp.description)}”
        </blockquote>
      </div>
    `;
  }

  // 1. Scientific Telemetry & Specs Dashboard (6 Cells)
  innerHTML += `
    <div class="lab-specs-grid">
      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🌱</span>
          <span>CACAO ORIGIN &amp; TERROIR</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.origin)}</div>
        <div class="lab-spec-sub">Single Estate Cultivar</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🧬</span>
          <span>CRYSTAL POLYMORPHISM</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.polymorph)}</div>
        <div class="lab-spec-sub">High-Melting Stability Matrix</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🔬</span>
          <span>PARTICLE FINENESS (D90)</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.particleSize)}</div>
        <div class="lab-spec-sub">Sub-palatal tongue detection</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>⚙️</span>
          <span>CONCHING REGIMEN</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.conching)}</div>
        <div class="lab-spec-sub">Longitudinal dry-shear aeration</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>💧</span>
          <span>WATER ACTIVITY (Aw)</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.waterActivity)}</div>
        <div class="lab-spec-sub">Hygroscopic fat bloom shield</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🏷️</span>
          <span>BATCH VERIFICATION</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.batchRef)}</div>
        <div class="lab-spec-sub">Active pilot batch log</div>
      </div>
    </div>
  `;

  // 2. Sensory & Empirical Performance Panel
  innerHTML += `
    <div class="lab-sensory-panel">
      <div class="lab-sensory-header">
        <h3 class="lab-sensory-title">
          <span>📊</span>
          <span>SENSORY AUDIT &amp; PHYSICAL EMPIRICAL DATA</span>
        </h3>
        <span class="lab-sensory-subtitle">LAB BENCHMARK TRIAL</span>
      </div>
      <div class="lab-sensory-grid">
        ${specs.sensory.map(s => `
          <div class="lab-sensory-item">
            <div class="lab-sensory-label-row">
              <span class="lab-sensory-name">${escapeHtml(s.name)}</span>
              <span class="lab-sensory-score">${escapeHtml(s.score)}</span>
            </div>
            <div class="lab-sensory-bar">
              <div class="lab-sensory-fill" style="width: ${s.fill};"></div>
            </div>
            <div class="lab-sensory-desc">${escapeHtml(s.desc)}</div>
          </div>
        `).join('')}
      </div>
    </div>
  `;

  // 3. 4-Pillar Scientific Dossier Grid
  innerHTML += `
    <div class="lab-dossier-grid">
      ${exp.hypothesis ? `
      <div class="lab-dossier-card card-accent-green">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">01</span>
          <span class="lab-dossier-step-tag">HYPOTHESIS</span>
        </div>
        <h4>Initial Target &amp; Premise</h4>
        <p>${escapeHtml(exp.hypothesis)}</p>
      </div>` : ''}

      ${exp.methodology ? `
      <div class="lab-dossier-card card-accent-blue">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">02</span>
          <span class="lab-dossier-step-tag">METHODOLOGY &amp; SETUP</span>
        </div>
        <h4>Process &amp; Equipment</h4>
        <p>${escapeHtml(exp.methodology)}</p>
      </div>` : ''}

      ${exp.findings ? `
      <div class="lab-dossier-card card-accent-amber">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">03</span>
          <span class="lab-dossier-step-tag">OBSERVATIONS &amp; FINDINGS</span>
        </div>
        <h4>Lab Data &amp; Texture</h4>
        <p>${escapeHtml(exp.findings)}</p>
      </div>` : ''}

      ${exp.next_steps ? `
      <div class="lab-dossier-card card-accent-purple">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">04</span>
          <span class="lab-dossier-step-tag">NEXT ITERATION STEPS</span>
        </div>
        <h4>Scaling &amp; Refinement</h4>
        <p>${escapeHtml(exp.next_steps)}</p>
      </div>` : ''}
    </div>
  `;

  // 4. Laboratory Apparatus & Instrumentation Used
  if (specs.apparatus && specs.apparatus.length > 0) {
    innerHTML += `
      <div class="lab-apparatus-strip">
        <span class="lab-apparatus-label">
          <span>⚙️</span>
          <span>APPARATUS &amp; INSTRUMENTATION:</span>
        </span>
        ${specs.apparatus.map(app => `
          <span class="lab-apparatus-tag">${escapeHtml(app)}</span>
        `).join('')}
      </div>
    `;
  }

  // 5. Detailed Lab Logs & Scientific Notes
  if (exp.full_content) {
    innerHTML += `
      <div class="lab-full-notes-card">
        <div class="lab-full-notes-header">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
          </svg>
          <h4>DETAILED LAB LOGS &amp; SCIENTIFIC NOTES</h4>
        </div>
        <div class="lab-full-notes-body">
          ${exp.full_content}
        </div>
      </div>
    `;
  }

  // 6. Collaborative R&D & Tasting Request Card
  innerHTML += `
    <div class="lab-collab-card">
      <div class="lab-collab-text">
        <h4>EVALUATE TRIAL SAMPLES OR COLLABORATE</h4>
        <p>Are you a pastry chef, food scientist, or artisan chocolatier interested in sampling this batch or co-developing chocolate formulations with the RT Chocos Innovation Lab?</p>
      </div>
      <a href="contact.php?subject=${encodeURIComponent('Inquiry regarding Innovation Lab Experiment: ' + exp.title)}" class="lab-collab-btn">
        <span>REQUEST SAMPLE BATCH</span>
        <span>→</span>
      </a>
    </div>
  `;

  // 7. Footer Bar with Next/Prev Experiment Navigation
  innerHTML += `
    <div class="lab-modal-footer-bar">
      <div class="lab-modal-footer-nav">
        <button type="button" onclick="navigateExperiment(${exp.id}, -1)" class="lab-modal-nav-btn" title="Previous Experiment">
          <span>← PREV EXP</span>
        </button>
        <button type="button" onclick="navigateExperiment(${exp.id}, 1)" class="lab-modal-nav-btn" title="Next Experiment">
          <span>NEXT EXP →</span>
        </button>
      </div>
      <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="closeLabModal('experimentModal')" class="lab-modal-return-btn">
          <span class="arrow">←</span>
          <span>RETURN TO LAB WORKBENCH</span>
        </button>
        <button type="button" onclick="closeLabModal('experimentModal')" class="lab-modal-return-ghost">
          <span>CLOSE DOSSIER (ESC)</span>
        </button>
      </div>
    </div>
  `;

  body.innerHTML = innerHTML;
  document.getElementById('experimentModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function openFailedBatchModal(id) {
  const fail = window.LAB_FAILED_BATCHES.find(item => Number(item.id) === Number(id));
  if (!fail) return;

  const specs = LAB_FAIL_SPECS[fail.id] || {
    batchRef: `FAIL-POSTMORTEM-0${fail.id}`,
    defectMode: 'Volatilization & Rheology Imbalance',
    trialScale: 'Pilot Batch Trial',
    criticalMoisture: 'Aw Spike Detected',
    viscosityImpact: 'Viscosity Shift',
    correctiveChecklist: [
      'Pre-condition sensitive inclusions with a tempered lipid barrier.',
      'Calibrate conching thermal limits for volatile aromatics.',
      'Screen ambient humidity prior to batch unsealing.'
    ]
  };

  const header = document.getElementById('modalFailHeader');
  const body = document.getElementById('modalFailBody');

  header.innerHTML = `
    <div class="lab-modal-header-inner">
      <div class="lab-modal-header-left">
        <div class="lab-modal-meta-row">
          <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #B45309; background: #FEF3C7; padding: 4px 10px; border-radius: 4px;">
            ${escapeHtml(fail.badge_label || 'BECAUSE FAILURE IS DATA.')}
          </span>
          <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: #DC2626; background: #FEE2E2; padding: 4px 10px; border-radius: 4px;">
            ${escapeHtml(specs.batchRef)}
          </span>
          <span class="lab-modal-category-tag">POST-MORTEM SCIENTIFIC AUDIT</span>
        </div>
        <h2 class="lab-modal-title">${escapeHtml(fail.title)}</h2>
      </div>
      <div class="lab-modal-header-right">
        <div class="lab-modal-header-actions">
          <button type="button" class="lab-modal-tool-btn" onclick="copyLabDossier('${escapeHtml(fail.slug || 'failed-' + fail.id)}', '${escapeHtml(fail.title)}')" title="Copy Dossier Link">
            <span>📋</span>
            <span>SHARE</span>
          </button>
          <button type="button" class="lab-modal-tool-btn" onclick="printLabDossier()" title="Print / Export Dossier">
            <span>🖨️</span>
            <span>PRINT</span>
          </button>
          <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('failedBatchModal')" aria-label="Close Failed Batch Dossier">
            <span>CLOSE</span>
            <span class="close-btn-x">✕</span>
          </button>
        </div>
      </div>
    </div>
  `;

  let innerHTML = '';
  if (fail.image_path) {
    innerHTML += `
      <div class="lab-modal-hero-frame">
        <img src="${escapeHtml(fail.image_path)}" alt="${escapeHtml(fail.title)}">
        <div class="lab-modal-hero-badge" style="background: rgba(185, 28, 28, 0.9);">
          <span>⚠️ ANOMALY DETECTED IN TRIAL RUN #${fail.id} · FORENSIC AUDIT</span>
        </div>
      </div>
    `;
  }

  // Failure Diagnostic Telemetry Dashboard
  innerHTML += `
    <div class="lab-specs-grid">
      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>⚠️</span>
          <span>PRIMARY FAILURE MODE</span>
        </div>
        <div class="lab-spec-value" style="color: #DC2626;">${escapeHtml(specs.defectMode)}</div>
        <div class="lab-spec-sub">Organoleptic &amp; Rheology Defect</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>⚖️</span>
          <span>PILOT SCALE / VOLUME</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.trialScale)}</div>
        <div class="lab-spec-sub">Dedicated experimental pilot run</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>💧</span>
          <span>CRITICAL MOISTURE ANOMALY</span>
        </div>
        <div class="lab-spec-value" style="color: #D97706;">${escapeHtml(specs.criticalMoisture)}</div>
        <div class="lab-spec-sub">Free moisture pulled into fat matrix</div>
      </div>
    </div>
  `;

  // 4-Step Post-Mortem Cards
  innerHTML += `
    <div class="lab-dossier-grid">
      <div class="lab-dossier-card card-accent-green">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">01</span>
          <span class="lab-dossier-step-tag">INITIAL HYPOTHESIS</span>
        </div>
        <h4>The Intended Goal</h4>
        <p>${escapeHtml(fail.hypothesis)}</p>
      </div>

      <div class="lab-dossier-card card-accent-red">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">02</span>
          <span class="lab-dossier-step-tag">THE ANOMALY</span>
        </div>
        <h4>What Happened? (The Failure)</h4>
        <p>${escapeHtml(fail.what_happened)}</p>
      </div>

      <div class="lab-dossier-card card-accent-amber">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">03</span>
          <span class="lab-dossier-step-tag">ROOT CAUSE SCIENCE</span>
        </div>
        <h4>Understanding The Science</h4>
        <p>${escapeHtml(fail.why_it_happened)}</p>
      </div>

      <div class="lab-dossier-card card-accent-green">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">04</span>
          <span class="lab-dossier-step-tag">THE BREAKTHROUGH</span>
        </div>
        <h4>The Learning (What Next?)</h4>
        <p>${escapeHtml(fail.what_next)}</p>
      </div>
    </div>
  `;

  // Full Story
  if (fail.full_story) {
    innerHTML += `
      <div class="lab-full-notes-card">
        <div class="lab-full-notes-header">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
          <h4>THE FULL EXPERIMENT STORY &amp; POST-MORTEM SCIENCE</h4>
        </div>
        <div class="lab-full-notes-body">
          ${fail.full_story}
        </div>
      </div>
    `;
  }

  // Validated Corrective Protocols Checklist Card
  if (specs.correctiveChecklist && specs.correctiveChecklist.length > 0) {
    innerHTML += `
      <div class="lab-corrective-card">
        <div class="lab-corrective-title">
          <span>🛡️</span>
          <span>VALIDATED CORRECTIVE LAB PROTOCOLS ADOPTED</span>
        </div>
        <ul class="lab-corrective-list">
          ${specs.correctiveChecklist.map(item => `
            <li class="lab-corrective-item">
              <span class="lab-corrective-check">✓</span>
              <span>${escapeHtml(item)}</span>
            </li>
          `).join('')}
        </ul>
      </div>
    `;
  }

  innerHTML += `
    <div class="lab-modal-footer-bar">
      <button type="button" onclick="closeLabModal('failedBatchModal')" class="lab-modal-return-btn">
        <span class="arrow">←</span>
        <span>RETURN TO LAB WORKBENCH</span>
      </button>
      <button type="button" onclick="closeLabModal('failedBatchModal')" class="lab-modal-return-ghost">
        <span>CLOSE DOSSIER (ESC)</span>
      </button>
    </div>
  `;

  body.innerHTML = innerHTML;
  document.getElementById('failedBatchModal').classList.add('active');
  document.body.style.overflow = 'hidden';
}

function openIngredientModal(id, e) {
  if (e) e.preventDefault();
  const ing = (window.LAB_INGREDIENTS || []).find(item => Number(item.id) === Number(id));
  if (!ing) return;

  const specs = LAB_ING_SPECS[ing.id] || {
    taxonomy: 'Botanical Indigenous Cultivar',
    origin: 'Native Indian Terroir & Agroforestry',
    chemistry: 'Natural Triglycerides & Organic Saccharides',
    temperDelta: 'Standard Beta-V Matrix Alignment',
    pairings: ['68% Kerala Dark', 'Vedic Milk Chocolate', 'Pastry Ganache']
  };

  const header = document.getElementById('modalIngHeader');
  const body = document.getElementById('modalIngBody');

  header.innerHTML = `
    <div class="lab-modal-header-inner">
      <div class="lab-modal-header-left">
        <div class="lab-modal-meta-row">
          <span style="font-size: 10px; font-weight: 800; letter-spacing: 0.18em; text-transform: uppercase; color: #166534; background: #EBF7EE; padding: 4px 10px; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;">
            <span>🌿</span>
            <span>INGREDIENT INNOVATION DOSSIER</span>
          </span>
          <span class="lab-modal-category-tag">NATIVE INDIAN TERROIR</span>
        </div>
        <h2 class="lab-modal-title">${escapeHtml(ing.title)}</h2>
      </div>
      <div class="lab-modal-header-right">
        <div class="lab-modal-header-actions">
          <button type="button" class="lab-modal-tool-btn" onclick="copyLabDossier('${escapeHtml(ing.slug || 'ing-' + ing.id)}', '${escapeHtml(ing.title)}')" title="Copy Dossier Link">
            <span>📋</span>
            <span>SHARE</span>
          </button>
          <button type="button" class="lab-modal-tool-btn" onclick="printLabDossier()" title="Print / Export Dossier">
            <span>🖨️</span>
            <span>PRINT</span>
          </button>
          <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('ingredientModal')" aria-label="Close Ingredient Dossier">
            <span>CLOSE</span>
            <span class="close-btn-x">✕</span>
          </button>
        </div>
      </div>
    </div>
  `;

  let innerHTML = '';
  if (ing.image_path) {
    innerHTML += `
      <div class="lab-modal-hero-frame">
        <img src="${escapeHtml(ing.image_path)}" alt="${escapeHtml(ing.title)}">
        <div class="lab-modal-hero-badge">
          <span>BOTANICAL INGREDIENT DOSSIER · ${escapeHtml(ing.title).toUpperCase()}</span>
        </div>
      </div>
    `;
  }

  if (ing.description) {
    innerHTML += `
      <div class="lab-inquiry-callout">
        <div class="lab-inquiry-tag">
          <span>🌿</span>
          <span>BOTANICAL ORIGIN &amp; CHARACTERISTICS</span>
        </div>
        <blockquote class="lab-inquiry-quote">
          “${escapeHtml(ing.description)}”
        </blockquote>
      </div>
    `;
  }

  // Botanical Taxonomy & Food Chemistry Matrix (4 Cells)
  innerHTML += `
    <div class="lab-specs-grid" style="grid-template-columns: repeat(2, 1fr);">
      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🌿</span>
          <span>BOTANICAL TAXONOMY / SPECIES</span>
        </div>
        <div class="lab-spec-value" style="font-style: italic;">${escapeHtml(specs.taxonomy)}</div>
        <div class="lab-spec-sub">Native heritage cultivars</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>📍</span>
          <span>TERROIR &amp; HARVEST ORIGIN</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.origin)}</div>
        <div class="lab-spec-sub">Direct ethical farmer sourcing</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🧪</span>
          <span>LIPID &amp; SACCHARIDE CHEMISTRY</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.chemistry)}</div>
        <div class="lab-spec-sub">Food matrix functional behavior</div>
      </div>

      <div class="lab-spec-card">
        <div class="lab-spec-label">
          <span>🌡️</span>
          <span>TEMPERING OFFSET DELTA</span>
        </div>
        <div class="lab-spec-value">${escapeHtml(specs.temperDelta)}</div>
        <div class="lab-spec-sub">Polymorphic crystal calibration</div>
      </div>
    </div>
  `;

  // 4-Step Innovation Cards
  innerHTML += `
    <div class="lab-dossier-grid">
      ${ing.key_varietals ? `
      <div class="lab-dossier-card card-accent-green">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">01</span>
          <span class="lab-dossier-step-tag">SOURCING &amp; TERROIR</span>
        </div>
        <h4>Key Varietals &amp; Harvesting</h4>
        <p>${escapeHtml(ing.key_varietals)}</p>
      </div>` : ''}

      ${ing.science_behavior ? `
      <div class="lab-dossier-card card-accent-blue">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">02</span>
          <span class="lab-dossier-step-tag">FOOD SCIENCE</span>
        </div>
        <h4>Polymorphism &amp; Melting Dynamics</h4>
        <p>${escapeHtml(ing.science_behavior)}</p>
      </div>` : ''}

      ${ing.applications ? `
      <div class="lab-dossier-card card-accent-amber">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">03</span>
          <span class="lab-dossier-step-tag">FORMULATION</span>
        </div>
        <h4>Conching &amp; Viscosity</h4>
        <p>${escapeHtml(ing.applications)}</p>
      </div>` : ''}

      ${ing.lab_notes ? `
      <div class="lab-dossier-card card-accent-purple">
        <div class="lab-dossier-step-row">
          <span class="lab-dossier-step-num">04</span>
          <span class="lab-dossier-step-tag">LAB NOTES</span>
        </div>
        <h4>Live Benchmarks &amp; Shelf Life</h4>
        <p>${escapeHtml(ing.lab_notes)}</p>
      </div>` : ''}
    </div>
  `;

  // Formulation Pairing Suite
  if (specs.pairings && specs.pairings.length > 0) {
    innerHTML += `
      <div class="lab-apparatus-strip">
        <span class="lab-apparatus-label">
          <span>🍫</span>
          <span>RECOMMENDED PAIRING FORMULATIONS:</span>
        </span>
        ${specs.pairings.map(pair => `
          <span class="lab-apparatus-tag" style="background: #EBF7EE; border-color: #A3D9B1; color: #166534;">${escapeHtml(pair)}</span>
        `).join('')}
      </div>
    `;
  }

  innerHTML += `
    <div class="lab-modal-footer-bar">
      <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <button type="button" onclick="closeLabModal('ingredientModal'); filterByIngredient('${escapeHtml(ing.title)}')" class="lab-modal-return-btn">
          <span>Filter Live Experiments (${escapeHtml(ing.title)}) →</span>
        </button>
        <button type="button" onclick="closeLabModal('ingredientModal')" class="lab-modal-return-ghost">
          <span>← RETURN TO LAB</span>
        </button>
      </div>
      <button type="button" onclick="closeLabModal('ingredientModal')" class="lab-modal-return-ghost">
        <span>CLOSE DOSSIER (ESC)</span>
      </button>
    </div>
  `;

  body.innerHTML = innerHTML;
  document.getElementById('ingredientModal').classList.add('active');
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
    closeLabModal('ingredientModal');
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

// Active section scroll spy for the 5 Explore the Lab tabs
window.addEventListener('DOMContentLoaded', function() {
  const sections = [
    { id: 'currently-in-lab', el: document.getElementById('currently-in-lab') },
    { id: 'ingredient-innovation', el: document.getElementById('ingredient-innovation') },
    { id: 'what-if', el: document.getElementById('what-if') },
    { id: 'failed-experiments', el: document.getElementById('failed-experiments') },
    { id: 'future-ideas', el: document.getElementById('future-ideas') }
  ];

  let scrollTimeout = null;
  window.addEventListener('scroll', function() {
    if (scrollTimeout) return;
    scrollTimeout = setTimeout(function() {
      scrollTimeout = null;
      const scrollPos = window.scrollY + 200;
      let currentId = null;

      sections.forEach(function(sec) {
        if (!sec.el) return;
        const top = sec.el.offsetTop;
        const height = sec.el.offsetHeight;
        if (scrollPos >= top && scrollPos < top + height + 60) {
          currentId = sec.id;
        }
      });

      if (currentId) {
        const activeLink = document.querySelector(`.lab-subnav-link[data-section="${currentId}"]`);
        if (activeLink && !activeLink.classList.contains('active')) {
          document.querySelectorAll('.lab-subnav-link').forEach(l => l.classList.remove('active'));
          activeLink.classList.add('active');
        }
      }
    }, 60);
  }, { passive: true });
});
</script>

<?php include 'includes/footer.php'; ?>
