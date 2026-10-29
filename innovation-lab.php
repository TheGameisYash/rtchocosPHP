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

    // Helper to extract a short, clean description snippet for preview cards (strips HTML and lists)
    function getLabCardSnippet($text, $maxLength = 110) {
        if (empty($text)) return '';
        $clean = trim(strip_tags(html_entity_decode((string)$text, ENT_QUOTES, 'UTF-8')));
        $clean = preg_replace('/\s+/', ' ', $clean);
        if (preg_match('/^([^.!?]+[.!?])/u', $clean, $m)) {
            $sentence = trim($m[1]);
            if (mb_strlen($sentence) >= 15 && mb_strlen($sentence) <= $maxLength) {
                return htmlspecialchars($sentence, ENT_QUOTES, 'UTF-8');
            }
        }
        if (mb_strlen($clean) > $maxLength) {
            $truncated = mb_substr($clean, 0, $maxLength);
            $lastSpace = mb_strrpos($truncated, ' ');
            if ($lastSpace !== false && $lastSpace > 40) {
                $truncated = mb_substr($truncated, 0, $lastSpace);
            }
            return htmlspecialchars(rtrim($truncated, ' .,;-') . '...', ENT_QUOTES, 'UTF-8');
        }
        return htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
    }

    $heroEyebrow = trim((string)get_site_setting('lab_hero_eyebrow', '')) ?: 'IDEAS · EXPERIMENTS · REAL TASTE';
    $heroTitle = trim((string)get_site_setting('lab_hero_title', '')) ?: 'Innovation Lab';
    $heroSubtitle = trim((string)get_site_setting('lab_hero_subtitle', '')) ?: 'Our creative kitchen where we test fresh ideas, local ingredients, and better ways to make honest chocolate.';
    $heroCtaText = trim((string)get_site_setting('lab_hero_cta_text', '')) ?: 'STEP INTO THE LAB →';
    $heroCtaLink = trim((string)get_site_setting('lab_hero_cta_link', '')) ?: '#currently-in-lab';
    $stickyNoteText = trim((string)get_site_setting('lab_sticky_note', '')) ?: "Better Ingredients\nBigger Questions\nBrighter Chocolate";
    $heroImage = getSafeLabImg(get_site_setting('lab_hero_image', ''), 'assets/images/lab-hero.jpg');

    $whatIfEyebrow = trim((string)get_site_setting('lab_what_if_eyebrow', '')) ?: 'IDEAS WE ARE TESTING NEXT.';
    $whatIfHeading = trim((string)get_site_setting('lab_what_if_heading', '')) ?: 'What if?';
    $whatIfImage = getSafeLabImg(get_site_setting('lab_what_if_image', ''), 'assets/images/lab-what-if.jpg');

    $failedEyebrow = trim((string)get_site_setting('lab_failed_batch_eyebrow', '')) ?: 'WHEN THINGS GO WRONG, WE LEARN.';
    $failedHeading = trim((string)get_site_setting('lab_failed_batch_heading', '')) ?: 'From a Failed Batch to a New Idea';

    $ingredientEyebrow = trim((string)get_site_setting('lab_ingredient_eyebrow', '')) ?: 'LOCAL INGREDIENTS. FRESH FLAVORS. NEW IDEAS.';
    $ingredientHeading = trim((string)get_site_setting('lab_ingredient_heading', '')) ?: 'Ingredient Innovation';

    $futureEyebrow = trim((string)get_site_setting('lab_future_eyebrow', '')) ?: 'BIGGER DREAMS. BETTER CHOCOLATE.';
    $futureHeading = trim((string)get_site_setting('lab_future_heading', '')) ?: 'Future of Chocolate';
    $futureText = trim((string)get_site_setting('lab_future_text', '')) ?: 'Exploring sustainable ideas that support cocoa farmers, care for our soil, and bring clean, honest chocolate to everyone.';
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
            <span>FAILED BATCHES</span>
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
       3. CURRENTLY IN THE LAB SECTION (Blog Card Layout)
       ================================================================= -->
  <section class="lab-section" id="currently-in-lab">
    <div class="lab-section-header-row" style="margin-bottom: 24px;">
      <div class="lab-section-title-wrap">
        <h2 class="lab-section-title">Currently in the Lab</h2>
        <div class="lab-section-rule"></div>
      </div>
      <a href="#currently-in-lab" class="lab-section-link" onclick="filterClientExperiments('all', null, event)">
        <span>VIEW ALL EXPERIMENTS</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </a>
    </div>

    <!-- Blog-Style Header Bar with Filter Pills & Search -->
    <div class="blog-header-bar" style="margin-bottom: 36px;">
      <div class="blog-filters-wrapper" id="expClientFilters">
        <button type="button" class="filter-btn active" data-filter="all" onclick="filterClientExperiments('all', this)">All</button>
        <button type="button" class="filter-btn" data-filter="ingredient-innovation" onclick="filterClientExperiments('ingredient-innovation', this)">Ingredient Innovation</button>
        <button type="button" class="filter-btn" data-filter="process-exploration" onclick="filterClientExperiments('process-exploration', this)">Process Exploration</button>
        <button type="button" class="filter-btn" data-filter="formulation-lab" onclick="filterClientExperiments('formulation-lab', this)">Formulation Lab</button>
        <button type="button" class="filter-btn" data-filter="sensory-insights" onclick="filterClientExperiments('sensory-insights', this)">Sensory Insights</button>
        <button type="button" class="filter-btn" data-filter="stability-shelf-life" onclick="filterClientExperiments('stability-shelf-life', this)">Stability &amp; Shelf Life</button>
      </div>
      <div class="search-container">
        <input class="blog-search" type="text" id="expClientSearch" placeholder="Search experiments by recipe, topic, keyword..." oninput="searchClientExperiments(this.value)" />
        <svg class="search-bar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
    </div>

    <!-- Blog Grid (Matching blog.php) -->
    <div class="grid-blog" id="experimentsGrid">
      <?php if (!empty($experiments)): ?>
        <?php foreach ($experiments as $exp): 
          $badgeColor = htmlspecialchars($exp['status_color'] ?: 'green');
        ?>
          <div class="card blog-card-link client-exp-item" 
               data-category="<?php echo htmlspecialchars(strtolower($exp['category'])); ?>" 
               data-title="<?php echo htmlspecialchars(strtolower($exp['title'])); ?>" 
               data-desc="<?php echo htmlspecialchars(strtolower($exp['description'])); ?>" 
               data-tags="<?php echo htmlspecialchars(strtolower($exp['tags'])); ?>" 
               onclick="openExperimentModal(<?php echo (int)$exp['id']; ?>)">
            <div class="blog-card-img" style="position: relative;">
              <img src="<?php echo htmlspecialchars($exp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" loading="lazy" onerror="this.src='assets/images/placeholder.jpg';" />
              <div class="lab-status-badge" style="position: absolute; top: 12px; left: 12px; margin: 0; background: rgba(14, 10, 8, 0.75); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.15);">
                <span class="status-dot <?php echo $badgeColor; ?>"></span>
                <span style="color: #fff; font-weight: 600;"><?php echo htmlspecialchars($exp['status_badge']); ?></span>
              </div>
            </div>
            <div class="blog-card-body">
              <h3 class="blog-card-title"><?php echo htmlspecialchars($exp['title']); ?></h3>
              <div class="blog-meta">
                <span class="tag"><?php echo htmlspecialchars($exp['category']); ?></span>
                <span class="blog-date">Batch #<?php echo htmlspecialchars($exp['batch_ref'] ?: $exp['id']); ?> • <?php echo htmlspecialchars($exp['status_badge']); ?></span>
              </div>
              <p class="blog-excerpt"><?php echo htmlspecialchars($exp['description']); ?></p>
              <div class="blog-read-more">EXPLORE DOSSIER &rarr;</div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #555; padding: 40px 0;">No active experiments found in the laboratory at this time.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- =================================================================
       4. WHAT IF? SECTION
       ================================================================= -->
  <section class="lab-section" id="what-if-section" style="padding-top: 20px; padding-bottom: 20px;">
    <div class="lab-whatif-card" id="what-if" style="max-width: 1400px; margin: 0 auto;">
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
  </section>

  <!-- =================================================================
       5. FROM A FAILED BATCH TO A NEW INSIGHT (Blog Card Layout)
       ================================================================= -->
  <section class="lab-section" id="failed-experiments" style="padding-top: 36px; padding-bottom: 40px;">
    <div class="lab-section-header-row" style="margin-bottom: 20px;">
      <div class="lab-section-title-wrap">
        <div class="lab-eyebrow" style="margin-bottom: 6px;">
          <span><?php echo htmlspecialchars($failedEyebrow); ?></span>
        </div>
        <h2 class="lab-section-title"><?php echo htmlspecialchars($failedHeading); ?></h2>
        <div class="lab-section-rule"></div>
      </div>
    </div>
    <p style="font-size: 14.5px; color: #555; max-width: 720px; margin: 0 0 32px; font-family: var(--font-sans); line-height: 1.6;">
      How we turn unexpected recipe mistakes into better, more delicious chocolate. Because failure is data.
    </p>

    <!-- Blog Grid for Failed Batches -->
    <div class="grid-blog" id="failedBatchesGrid">
      <?php if (!empty($failedBatches)): ?>
        <?php foreach ($failedBatches as $fb): ?>
          <div class="card blog-card-link client-failed-item" 
               onclick="openFailedBatchModal(<?php echo (int)$fb['id']; ?>)">
            <div class="blog-card-img" style="position: relative;">
              <img src="<?php echo htmlspecialchars($fb['image_path'] ?: 'assets/images/lab-failed-batch.jpg'); ?>" alt="<?php echo htmlspecialchars($fb['title']); ?>" loading="lazy" onerror="this.src='assets/images/lab-failed-batch.jpg';" />
              <?php if ($fb['is_featured']): ?>
                <div style="position: absolute; top: 12px; left: 12px; background: rgba(30, 25, 20, 0.85); backdrop-filter: blur(4px); color: #FCD34D; font-size: 10.5px; font-weight: 700; padding: 4px 10px; border-radius: 999px; letter-spacing: 0.08em; text-transform: uppercase;">
                  ★ FEATURED CASE STUDY
                </div>
              <?php endif; ?>
            </div>
            <div class="blog-card-body">
              <h3 class="blog-card-title"><?php echo htmlspecialchars($fb['title']); ?></h3>
              <div class="blog-meta">
                <span class="tag"><?php echo htmlspecialchars($fb['badge_label'] ?: 'CASE STUDY'); ?></span>
                <span class="blog-date">Failure to Data</span>
              </div>
              <p class="blog-excerpt"><?php echo getLabCardSnippet($fb['hypothesis'] ?: $fb['what_happened']); ?></p>
              <div class="blog-read-more">READ FULL STORY &rarr;</div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align: center; color: #555; padding: 40px 0;">No failed batch case studies documented yet.</p>
      <?php endif; ?>
    </div>
  </section>

  <!-- =================================================================
       6. INGREDIENT INNOVATION SECTION (Blog Card Layout)
       ================================================================= -->
  <section class="lab-ingredient-section" id="ingredient-innovation">
    <div class="lab-ingredient-inner">
      <div class="lab-section-header-row" style="margin-bottom: 20px;">
        <div class="lab-section-title-wrap">
          <div class="lab-eyebrow" style="margin-bottom: 6px;">
            <span><?php echo htmlspecialchars($ingredientEyebrow); ?></span>
          </div>
          <h2 class="lab-ingredient-title" style="margin: 0;"><?php echo htmlspecialchars($ingredientHeading); ?></h2>
          <div class="lab-section-rule" style="background: rgba(255,255,255,0.2);"></div>
        </div>
      </div>
      <p class="lab-ingredient-subtext" style="margin: 0 0 32px; color: rgba(255,255,255,0.8); max-width: 720px; line-height: 1.6;">
        Testing local fruits, nuts, unrefined sugars, and spices to see how they blend naturally with Indian single-estate cocoa.
      </p>

      <!-- Blog-Style Header Bar with Pills & Search -->
      <div class="blog-header-bar" style="margin-bottom: 36px;">
        <div class="blog-filters-wrapper" id="ingClientFilters">
          <button type="button" class="filter-btn active" data-filter="all" onclick="filterClientIngredients('all', this)">All</button>
          <button type="button" class="filter-btn" data-filter="fruits" onclick="filterClientIngredients('fruits', this)">Fruits</button>
          <button type="button" class="filter-btn" data-filter="nuts-seeds" onclick="filterClientIngredients('nuts-seeds', this)">Nuts &amp; Seeds</button>
          <button type="button" class="filter-btn" data-filter="sugars" onclick="filterClientIngredients('sugars', this)">Sugars</button>
          <button type="button" class="filter-btn" data-filter="dairy-vegan" onclick="filterClientIngredients('dairy-vegan', this)">Dairy &amp; Vegan</button>
          <button type="button" class="filter-btn" data-filter="spices" onclick="filterClientIngredients('spices', this)">Spices</button>
        </div>
        <div class="search-container">
          <input class="blog-search" type="text" id="ingClientSearch" placeholder="Search ingredients, sugars, spices..." oninput="searchClientIngredients(this.value)" />
          <svg class="search-bar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
        </div>
      </div>

      <!-- Blog Grid (Matching blog.php) -->
      <div class="grid-blog" id="ingredientsGrid">
        <?php if (!empty($ingredientCategories)): ?>
          <?php foreach ($ingredientCategories as $cat): ?>
            <div class="card blog-card-link client-ing-item" 
                 data-id="<?php echo (int)$cat['id']; ?>"
                 data-slug="<?php echo htmlspecialchars($cat['slug']); ?>"
                 data-title="<?php echo htmlspecialchars(strtolower($cat['title'])); ?>"
                 data-desc="<?php echo htmlspecialchars(strtolower(strip_tags($cat['description'] ?? ''))); ?>"
                 data-varietals="<?php echo htmlspecialchars(strtolower($cat['key_varietals'] ?? '')); ?>"
                 data-science="<?php echo htmlspecialchars(strtolower($cat['science_behavior'] ?? '')); ?>"
                 data-apps="<?php echo htmlspecialchars(strtolower($cat['applications'] ?? '')); ?>"
                 onclick="openIngredientModal(<?php echo (int)$cat['id']; ?>)">
              <div class="blog-card-img" style="position: relative;">
                <img src="<?php echo htmlspecialchars($cat['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($cat['title']); ?>" loading="lazy" onerror="this.src='assets/images/placeholder.jpg';" />
              </div>
              <div class="blog-card-body">
                <h3 class="blog-card-title"><?php echo htmlspecialchars($cat['title']); ?></h3>
                <div class="blog-meta">
                  <span class="tag"><?php echo htmlspecialchars($cat['key_varietals'] ?: 'INGREDIENT RESEARCH'); ?></span>
                  <span class="blog-date">Native Exploration</span>
                </div>
                <p class="blog-excerpt"><?php echo htmlspecialchars(strip_tags($cat['description'] ?? '')); ?></p>
                <div class="blog-read-more">EXPLORE INGREDIENT &rarr;</div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p style="grid-column: 1/-1; text-align: center; color: rgba(255,255,255,0.7); padding: 40px 0;">No ingredient categories found at this time.</p>
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
  const defaults = LAB_EXP_SPECS[exp.id] || {};
  if (exp.origin || exp.polymorph || exp.particle_size || exp.water_activity || exp.conching || exp.batch_ref) {
    return {
      batchRef: exp.batch_ref || defaults.batchRef || ('RT-EXP-0' + exp.id + ' · FORMULATION'),
      origin: exp.origin || defaults.origin || 'Western Ghats Single Estate Cacao',
      polymorph: exp.polymorph || defaults.polymorph || 'Beta-V Triglyceride Matrix (Tm 33.8°C)',
      particleSize: exp.particle_size || defaults.particleSize || '< 18.5 µm (Micro-Refined)',
      conching: exp.conching || defaults.conching || '36 Hours Longitudinal Conching',
      waterActivity: exp.water_activity || defaults.waterActivity || '0.31 Aw (Shelf-Stable Dispersion)',
      sensory: defaults.sensory || [
        { name: 'Crystal Temper Snap', score: '9.1 / 10', fill: '91%', desc: 'Clean acoustic snap' },
        { name: 'Palate Melt Dynamics', score: '33.8°C', fill: '90%', desc: 'Silky body-temp dissolution' },
        { name: 'Flavor Preservation', score: '92%', fill: '92%', desc: 'Balanced single-origin terroir' },
        { name: 'Thermal Stability', score: '33.0°C', fill: '85%', desc: 'Ambient shape resilience' }
      ],
      apparatus: defaults.apparatus || [
        'Continuous Tempering Unit',
        'Stone Granite Melanger',
        'Laser Diffraction Analyzer',
        'Viscosity Flow Gauge'
      ]
    };
  }
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
    defectMode: 'Fruit Flavor Loss & Chocolate Thickening',
    trialScale: '25 kg Test Batch',
    criticalMoisture: 'Moisture reached 0.58 (Target: under 0.35)',
    viscosityImpact: 'Chocolate paste thickened heavily during mixing',
    correctiveChecklist: [
      'Coating the fruit: Lightly coat all dried fruit pieces with warm cocoa butter before grinding so natural fruit flavors stay locked inside.',
      'Gentle grinding heat: Keep machine temperatures under 38°C so delicate fruit aromas do not evaporate away.',
      'Moisture test before mixing: Test every batch of dried fruit to make sure it is completely dry before adding it to melted chocolate.'
    ]
  }
};

const LAB_ING_SPECS = {
  1: { // Indian Fruits
    taxonomy: 'Alphonso Mango, Jamun (Black Plum) & Pink Guava',
    origin: 'Ratnagiri, Konkan Belt & Allahabad Orchards',
    chemistry: 'Natural fruit sweetness, tangy citrus acidity, and gentle pectin',
    temperDelta: 'Needs slow, gentle cooling for high shine',
    pairings: ['70% Kerala Dark', '55% Malabar Milk', 'Spiced Fruit Ganache']
  },
  2: { // Nuts & Seeds
    taxonomy: 'Malabar Cashews, Mamra Almonds & Chironji Seeds',
    origin: 'Malabar Coast, Kashmir Valley & Central India Forests',
    chemistry: 'Silky natural nut oils, rich roasted flavor, and creamy texture',
    temperDelta: 'Melts smoothly and evenly on the tongue',
    pairings: ['Stone-Ground Cashew Praline', 'Roasted Almond Dragees', 'Dark Truffle Fillings']
  },
  3: { // Alternative Sugars
    taxonomy: 'Natural Khandsari Cane Sugar & Palm Jaggery',
    origin: 'Western Uttar Pradesh & Southern Tamil Nadu Palms',
    chemistry: 'Warm caramel notes, rich natural minerals, wild molasses',
    temperDelta: 'Standard Beta-V (Moisture Controlled)',
    pairings: ['Single-Origin Dark Cacao', 'Warm Spice Bars', 'Artisan Caramel Creams']
  },
  4: { // Dairy & Plant-Based
    taxonomy: 'A2 Vedic Cow Milk, Fresh Kerala Coconut & Rolled Oats',
    origin: 'Gujarat Vedic Dairies & Coastal Kerala Coconut Groves',
    chemistry: 'Creamy natural milk fats, gentle on the stomach and velvety',
    temperDelta: '-1.2°C Quick-Melt Palatal Shift',
    pairings: ['Malted Milk Chocolate', 'Vegan White Ganache', 'Creamy Coconut Mousse']
  },
  5: { // Botanicals & Spices
    taxonomy: 'Wayanad Black Pepper, Green Cardamom & Star Anise',
    origin: 'Wayanad High Ranges & Idukki Cardamom Hills (Western Ghats)',
    chemistry: 'Warm aromatic oils with gentle, sweet tingling spice notes',
    temperDelta: 'Leaves chocolate snap perfectly intact',
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
          <span class="lab-modal-category-tag">${escapeHtml(exp.tags || 'LAB EXPERIMENT')}</span>
          <span class="lab-modal-id-badge">EXPERIMENT #${exp.id}</span>
        </div>
        <h2 class="lab-modal-title">${escapeHtml(exp.title)}</h2>
      </div>
      <div class="lab-modal-header-right">
        <span class="lab-modal-esc-hint">Press <kbd>ESC</kbd> to exit</span>
        <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('experimentModal')" aria-label="Close Experiment Dossier">
          <span>CLOSE</span>
          <span class="close-btn-x">✕</span>
        </button>
      </div>
    </div>
  `;

  const specs = getExpSpecs(exp);
  let innerHTML = '<div class="lab-fullscreen-dossier">';

  if (exp.image_path) {
    innerHTML += `
      <!-- LEFT: Laboratory Specimen Visual (True Photographic Frame - NOT a banner!) -->
      <div class="lab-dossier-visual-pane">
        <div class="lab-visual-frame">
          <img src="${escapeHtml(exp.image_path)}" alt="${escapeHtml(exp.title)}">
          <div class="lab-visual-tag">
            <span>🔬 RECIPE SAMPLE #${exp.id}</span>
          </div>
          <div class="lab-visual-caption-bar">
            <span>${escapeHtml(exp.title)}</span>
          </div>
        </div>

        <div class="lab-specimen-passport">
          <div class="lab-passport-top">
            <span class="lab-passport-heading">RECIPE PROFILE</span>
            <span class="lab-passport-batch">${escapeHtml(specs.batchRef || 'RT-EXP-0' + exp.id)}</span>
          </div>
          <div class="lab-passport-grid">
            <div class="lab-passport-cell">
              <span class="cell-label">Terroir Origin</span>
              <span class="cell-value" title="${escapeHtml(specs.origin || '')}">${escapeHtml(specs.origin ? specs.origin.split('(')[0].trim() : 'Western Ghats Cacao')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Melt &amp; Snap</span>
              <span class="cell-value" title="${escapeHtml(specs.polymorph || '')}">${escapeHtml(specs.polymorph ? specs.polymorph.split('(')[0].trim() : 'Beta-V Stabilized')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Smoothness</span>
              <span class="cell-value">${escapeHtml(specs.particleSize ? specs.particleSize.split('(')[0].trim() : '< 18.5 µm')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Moisture Level</span>
              <span class="cell-value">${escapeHtml(specs.waterActivity ? specs.waterActivity.split('(')[0].trim() : '0.31 Aw')}</span>
            </div>
          </div>
          <div class="lab-passport-seal">
            <span class="seal-icon">🧪</span>
            <span class="seal-text">Crafted &amp; Tested in Kerala · Small Batch</span>
          </div>
        </div>
      </div>
    `;
  }

  innerHTML += `
      <!-- RIGHT: 4 Core Research Pillars (2x2 Aligned Quad Grid) -->
      <div class="lab-dossier-pillars-pane">
        <div class="lab-four-pillars-quad">

          <!-- 01. Research Question -->
          <div class="lab-quad-card card-accent-green">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-num num-green">01</span>
                  <span class="lab-quad-dash dash-green"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-green">Research Question</h3>
                  <p class="lab-quad-prompt">What question are we trying to answer?</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sage" title="Research Question">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#166534" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 7h8M22 7v7l-9.5 18a3 3 0 0 0 2.6 4.5h17.8a3 3 0 0 0 2.6-4.5L26 14V7" />
                  <path d="M24 23c2.5-3.5 6-3.5 9-1.5-1 3-3.5 4.5-6.5 4-1.5-.2-2-.8-2.5-2.5z" fill="#166534" fill-opacity="0.12" />
                  <path d="M24 23c3.5 0 6.5 1.5 7.5 4" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-green">🔬 THE QUESTION</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(exp.hypothesis)}</div>
            </div>
          </div>

          <!-- 02. Observations & Findings -->
          <div class="lab-quad-card card-accent-bronze">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-num num-bronze">02</span>
                  <span class="lab-quad-dash dash-bronze"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-bronze">What We Discovered</h3>
                  <p class="lab-quad-prompt">What happened when we made and tasted the chocolate?</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sand" title="Observations & Findings">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#9A6A38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="21" cy="20" r="11" />
                  <line x1="29" y1="28" x2="39" y2="38" stroke-width="2.8" />
                  <line x1="16" y1="23" x2="16" y2="19" stroke-width="2.2" />
                  <line x1="21" y1="23" x2="21" y2="15" stroke-width="2.2" />
                  <line x1="26" y1="23" x2="26" y2="12" stroke-width="2.2" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-bronze">📊 WHAT WE NOTICED</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(exp.findings || exp.methodology)}</div>
            </div>
          </div>

          <!-- 03. Why It Matters -->
          <div class="lab-quad-card card-accent-green">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-num num-green">03</span>
                  <span class="lab-quad-dash dash-green"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-green">Why It Matters</h3>
                  <p class="lab-quad-prompt">How this helps make better, more delicious chocolate for you.</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sage" title="Why It Matters">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#166534" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 22a9 9 0 1 1 16 0c-1.5 2-2.5 3.5-2.5 6h-11c0-2.5-1-4-2.5-6z" />
                  <line x1="18.5" y1="32" x2="29.5" y2="32" />
                  <line x1="20.5" y1="36" x2="27.5" y2="36" />
                  <line x1="24" y1="6" x2="24" y2="9.5" />
                  <line x1="12" y1="11" x2="14.5" y2="13.5" />
                  <line x1="36" y1="11" x2="33.5" y2="13.5" />
                  <line x1="8" y1="22" x2="11.5" y2="22" />
                  <line x1="40" y1="22" x2="36.5" y2="22" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-green">💡 WHY IT MATTERS</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(exp.methodology)}</div>
            </div>
          </div>

          <!-- 04. Key Takeaway -->
          <div class="lab-quad-card card-accent-bronze">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-num num-bronze">04</span>
                  <span class="lab-quad-dash dash-bronze"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-bronze">Key Takeaway</h3>
                  <p class="lab-quad-prompt">The main lesson from this test batch.</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sand" title="Key Takeaway">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#9A6A38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M24 20c-4-2.5-8-2.5-13.5 0v15c5.5-2.5 9.5-2.5 13.5 0 4-2.5 8-2.5 13.5 0V20c-5.5-2.5-9.5-2.5-13.5 0z" />
                  <line x1="24" y1="20" x2="24" y2="35" stroke-width="2" />
                  <line x1="14" y1="25" x2="20" y2="25" stroke-width="1.8" />
                  <line x1="14" y1="29" x2="20" y2="29" stroke-width="1.8" />
                  <line x1="28" y1="25" x2="34" y2="25" stroke-width="1.8" />
                  <line x1="28" y1="29" x2="34" y2="29" stroke-width="1.8" />
                  <line x1="24" y1="11" x2="24" y2="15" />
                  <line x1="18" y1="12" x2="19.5" y2="15" />
                  <line x1="30" y1="12" x2="28.5" y2="15" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body takeaway-highlight">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-bronze">🎯 MAIN LESSON</span>
              </div>
              <div class="lab-quad-text takeaway-text">${renderLabContent(exp.next_steps)}</div>
              <div class="lab-takeaway-meta">
                <span class="meta-dot"></span>
                <span class="meta-label">Status: Tested and ready for our chocolate recipes</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  `;

  if (exp.full_content && exp.full_content.trim()) {
    innerHTML += `
      <div class="lab-full-notes-card">
        <div class="lab-full-notes-header">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <h4>DETAILED RECIPE NOTES &amp; TASTING LOG</h4>
        </div>
        <div class="lab-full-notes-body">
          ${renderLabContent(exp.full_content)}
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
    <div class="lab-failed-header-container">
      <div class="lab-failed-header-top-row">
        <div class="lab-failed-badges">
          <span class="lab-badge-forensic">
            <span class="lab-badge-pulse"></span>
            ${escapeHtml(specs.batchRef || 'FAIL-POSTMORTEM-0' + fail.id)}
          </span>
          <span class="lab-badge-category">RECIPE LESSON &amp; AUDIT</span>
          <span class="lab-badge-motto">${escapeHtml(fail.badge_label || 'EVERY FAILED BATCH TEACHES US SOMETHING.')}</span>
        </div>
        <div class="lab-failed-actions">
          <span class="lab-modal-esc-hint">Press <kbd>ESC</kbd> to exit</span>
          <button type="button" class="lab-action-btn" onclick="copyLabDossier('${escapeHtml(fail.slug || 'failed-' + fail.id)}', '${escapeHtml(fail.title)}')" title="Share Dossier Link">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
            <span>SHARE</span>
          </button>
          <button type="button" class="lab-action-btn" onclick="printLabDossier()" title="Print / Export Dossier">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>PRINT</span>
          </button>
          <button type="button" class="lab-close-btn" onclick="closeLabModal('failedBatchModal')" aria-label="Close Failed Batch Dossier">
            <span>CLOSE</span>
            <span class="close-x">✕</span>
          </button>
        </div>
      </div>
      <div class="lab-failed-header-title-row">
        <h2 class="lab-failed-title">${escapeHtml(fail.title)}</h2>
        <p class="lab-failed-subtitle">What Went Wrong &amp; How We Fixed It · RT Chocos Craft Lab</p>
      </div>
    </div>
  `;

  let innerHTML = '';
  if (fail.image_path) {
    innerHTML += `
      <div class="lab-failed-hero-frame">
        <img src="${escapeHtml(fail.image_path)}" alt="${escapeHtml(fail.title)}">
        <div class="lab-failed-hero-overlay">
          <div class="lab-failed-specimen-pill">
            <span class="specimen-dot"></span>
            <span>TEST BATCH #${fail.id}</span>
            <span class="specimen-divider">·</span>
            <span class="specimen-ref">${escapeHtml(specs.batchRef || 'RUN-01')}</span>
          </div>
          <div class="lab-failed-hero-status">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>PROBLEM SOLVED &amp; FIXED FOR FUTURE BATCHES</span>
          </div>
        </div>
      </div>
    `;
  }

  // 4-Phase Chronological Forensic Investigation Grid (Full-Width Viewport Sequence)
  innerHTML += `
    <div class="lab-failed-postmortem-grid">
      <!-- 01: The Intended Goal -->
      <div class="lab-pm-card card-hypo">
        <div class="lab-pm-head">
          <div class="lab-pm-pill">
            <span class="pm-phase-num">01</span>
            <span class="pm-phase-name">THE GOAL</span>
          </div>
          <div class="lab-pm-icon-blob icon-emerald" title="Intended Goal">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
          </div>
        </div>
        <div class="lab-pm-title-wrap">
          <h3 class="lab-pm-title">The Intended Goal</h3>
          <span class="lab-pm-sub">What we wanted to achieve with flavor and texture</span>
        </div>
        <div class="lab-pm-body">
          ${renderLabContent(fail.hypothesis)}
        </div>
        <div class="lab-pm-foot">
          <span class="lab-pm-tag tag-emerald">🎯 Goal Set</span>
        </div>
      </div>

      <!-- 02: What Happened (The Failure) -->
      <div class="lab-pm-card card-defect">
        <div class="lab-pm-head">
          <div class="lab-pm-pill">
            <span class="pm-phase-num">02</span>
            <span class="pm-phase-name">WHAT WENT WRONG</span>
          </div>
          <div class="lab-pm-icon-blob icon-rose" title="Observed Defect">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
          </div>
        </div>
        <div class="lab-pm-title-wrap">
          <h3 class="lab-pm-title">What Happened? (The Mistake)</h3>
          <span class="lab-pm-sub">How the chocolate changed unexpectedly</span>
        </div>
        <div class="lab-pm-body">
          ${renderLabContent(fail.what_happened)}
        </div>
        <div class="lab-pm-foot">
          <span class="lab-pm-tag tag-rose">⚠️ Issue Found</span>
        </div>
      </div>

      <!-- 03: Understanding The Science -->
      <div class="lab-pm-card card-cause">
        <div class="lab-pm-head">
          <div class="lab-pm-pill">
            <span class="pm-phase-num">03</span>
            <span class="pm-phase-name">WHY IT HAPPENED</span>
          </div>
          <div class="lab-pm-icon-blob icon-amber" title="Root Cause Mechanism">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 2v7.31"></path><path d="M14 9.3V2"></path><path d="M8.5 2h7"></path><path d="M14 9.3a6.5 6.5 0 1 1-4 0"></path><path d="M5.52 16h12.96"></path></svg>
          </div>
        </div>
        <div class="lab-pm-title-wrap">
          <h3 class="lab-pm-title">The Simple Science</h3>
          <span class="lab-pm-sub">The physical reason behind the problem</span>
        </div>
        <div class="lab-pm-body">
          ${renderLabContent(fail.why_it_happened)}
        </div>
        <div class="lab-pm-foot">
          <span class="lab-pm-tag tag-amber">🔬 Cause Found</span>
        </div>
      </div>

      <!-- 04: The Breakthrough -->
      <div class="lab-pm-card card-breakthrough">
        <div class="lab-pm-head">
          <div class="lab-pm-pill">
            <span class="pm-phase-num">04</span>
            <span class="pm-phase-name">THE FIX &amp; WHAT NEXT</span>
          </div>
          <div class="lab-pm-icon-blob icon-gold" title="Validated Breakthrough">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg>
          </div>
        </div>
        <div class="lab-pm-title-wrap">
          <h3 class="lab-pm-title">The Lesson &amp; The Fix</h3>
          <span class="lab-pm-sub">How we fixed the recipe for our next batch</span>
        </div>
        <div class="lab-pm-body">
          ${renderLabContent(fail.what_next)}
        </div>
        <div class="lab-pm-foot">
          <span class="lab-pm-tag tag-gold">💡 Recipe Fixed &amp; Tested ✓</span>
        </div>
      </div>
    </div>
  `;

  // Story & Preventative Protocols Section (Balanced Edge-to-Edge Split)
  const hasStory = !!fail.full_story;
  const hasProtocols = specs.correctiveChecklist && specs.correctiveChecklist.length > 0;

  if (hasStory || hasProtocols) {
    if (hasStory && hasProtocols) {
      innerHTML += '<div class="lab-failed-details-split">';
    }

    if (hasStory) {
      innerHTML += `
        <div class="lab-failed-story-card">
          <div class="lab-story-header">
            <div class="lab-story-header-left">
              <div class="lab-story-icon-medallion">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                  <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                  <line x1="9" y1="7" x2="15" y2="7"></line>
                  <line x1="9" y1="11" x2="15" y2="11"></line>
                </svg>
              </div>
              <div>
                <div class="lab-story-eyebrow">FIELD NOTES &amp; STORY</div>
                <h4 class="lab-story-heading">The Full Story Behind This Test Batch</h4>
              </div>
            </div>
            <div class="lab-story-badge">
              <span>📖 BEHIND THE SCENES</span>
            </div>
          </div>
          <div class="lab-story-body">
            ${renderLabContent(fail.full_story)}
          </div>
          <div class="lab-story-footer">
            <div class="lab-story-stamp">
              <span class="stamp-dot"></span>
              <span>TESTED &amp; SIGNED OFF BY RT CHOCOS CHOCOLATE TEAM · BATCH #${fail.id}</span>
            </div>
          </div>
        </div>
      `;
    }

    if (hasProtocols) {
      innerHTML += `
        <div class="lab-failed-protocols-card">
          <div class="lab-protocols-header">
            <div class="lab-protocols-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <div>
              <div class="lab-protocols-eyebrow">NEW RECIPE RULES</div>
              <h4 class="lab-protocols-heading">Steps We Put in Place for Future Batches</h4>
            </div>
          </div>
          <div class="lab-protocols-list">
            ${specs.correctiveChecklist.map((item, idx) => `
              <div class="lab-protocol-item">
                <span class="lab-protocol-num">${String(idx + 1).padStart(2, '0')}</span>
                <span class="lab-protocol-check">✓</span>
                <span class="lab-protocol-text">${escapeHtml(item)}</span>
              </div>
            `).join('')}
          </div>
        </div>
      `;
    }

    if (hasStory && hasProtocols) {
      innerHTML += '</div>';
    }
  }

  innerHTML += `
    <div class="lab-modal-footer-bar">
      <button type="button" onclick="closeLabModal('failedBatchModal')" class="lab-failed-modal-return-btn">
        <span class="arrow">←</span>
        <span>BACK TO INNOVATION LAB</span>
      </button>
      <button type="button" onclick="closeLabModal('failedBatchModal')" class="lab-failed-modal-close-ghost">
        <span>CLOSE (ESC)</span>
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
          <div class="lab-status-badge" style="position: static; margin-bottom: 0; display: inline-flex;">
            <span class="status-dot green"></span>
            <span>INGREDIENT INNOVATION</span>
          </div>
          <span class="lab-modal-category-tag">SOURCED IN INDIA</span>
          <span class="lab-modal-id-badge">INGREDIENT #${ing.id}</span>
        </div>
        <h2 class="lab-modal-title">${escapeHtml(ing.title)}</h2>
      </div>
      <div class="lab-modal-header-right">
        <span class="lab-modal-esc-hint">Press <kbd>ESC</kbd> to exit</span>
        <button type="button" class="lab-modal-close-btn" onclick="closeLabModal('ingredientModal')" aria-label="Close Ingredient Dossier">
          <span>CLOSE</span>
          <span class="close-btn-x">✕</span>
        </button>
      </div>
    </div>
  `;

  let innerHTML = '<div class="lab-fullscreen-dossier">';

  if (ing.image_path) {
    innerHTML += `
      <!-- LEFT: Botanical Specimen Visual (True Photographic Specimen Frame) -->
      <div class="lab-dossier-visual-pane">
        <div class="lab-visual-frame">
          <img src="${escapeHtml(ing.image_path)}" alt="${escapeHtml(ing.title)}">
          <div class="lab-visual-tag">
            <span>🌿 LOCAL INGREDIENT #${ing.id}</span>
          </div>
          <div class="lab-visual-caption-bar">
            <span>${escapeHtml(ing.title)}</span>
          </div>
        </div>

        <div class="lab-specimen-passport">
          <div class="lab-passport-top">
            <span class="lab-passport-heading">INGREDIENT DETAILS</span>
            <span class="lab-passport-batch">CAT #${ing.id}</span>
          </div>
          <div class="lab-passport-grid">
            <div class="lab-passport-cell">
              <span class="cell-label">Plant / Tree Variety</span>
              <span class="cell-value" title="${escapeHtml(specs.taxonomy || '')}">${escapeHtml(specs.taxonomy ? specs.taxonomy.split('/')[0].trim() : 'Indigenous Cultivar')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Terroir Origin</span>
              <span class="cell-value" title="${escapeHtml(specs.origin || '')}">${escapeHtml(specs.origin ? specs.origin.split(',')[0].trim() : 'Native Indian Terroir')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Natural Flavor &amp; Texture</span>
              <span class="cell-value" title="${escapeHtml(specs.chemistry || '')}">${escapeHtml(specs.chemistry ? specs.chemistry.split(',')[0].trim() : 'Natural Bio-actives')}</span>
            </div>
            <div class="lab-passport-cell">
              <span class="cell-label">Melt Temperature</span>
              <span class="cell-value">${escapeHtml(specs.temperDelta || 'Standard Form V')}</span>
            </div>
          </div>
          <div class="lab-passport-seal">
            <span class="seal-icon">🧪</span>
            <span class="seal-text">Locally Sourced · Kitchen Tested</span>
          </div>
        </div>
      </div>
    `;
  }

  innerHTML += `
      <!-- RIGHT: 4 Core Innovation Pillars (2x2 Aligned Quad Grid) -->
      <div class="lab-dossier-pillars-pane">
        <div class="lab-four-pillars-quad">

          <!-- 01. Ingredient Profile -->
          <div class="lab-quad-card card-accent-green">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-circle-badge badge-circle-green">01</span>
                  <span class="lab-quad-dash dash-green"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-green">Ingredient Profile</h3>
                  <p class="lab-quad-prompt">What makes this ingredient special?</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sage" title="Ingredient Profile">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#166534" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M14 6h14l10 10v24a2 2 0 0 1-2 2H14a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z" />
                  <polyline points="28 6 28 16 38 16" />
                  <line x1="18" y1="23" x2="30" y2="23" />
                  <line x1="18" y1="29" x2="30" y2="29" />
                  <line x1="18" y1="35" x2="26" y2="35" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-green">🌿 INGREDIENT PROFILE</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(ing.description || ing.key_varietals)}</div>
              ${ing.key_varietals ? `
              <div class="lab-takeaway-meta" style="border-top: 1px dashed #D2E3D5; margin-top: 10px; padding-top: 8px;">
                <span class="meta-dot" style="background: #166534;"></span>
                <span class="meta-label" style="color: #2F593A;"><strong>Key Varietals:</strong> ${escapeHtml(ing.key_varietals)}</span>
              </div>
              ` : ''}
            </div>
          </div>

          <!-- 02. Potential in Chocolate -->
          <div class="lab-quad-card card-accent-bronze">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-circle-badge badge-circle-ochre">02</span>
                  <span class="lab-quad-dash dash-bronze"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-bronze">Ways to Use in Chocolate</h3>
                  <p class="lab-quad-prompt">Where and how we use it in chocolate bars, ganaches, and spreads.</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sand" title="Potential in Chocolate">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#9A6A38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M16 22a9 9 0 1 1 16 0c-1.5 2-2.5 3.5-2.5 6h-11c0-2.5-1-4-2.5-6z" />
                  <line x1="18.5" y1="32" x2="29.5" y2="32" />
                  <line x1="20.5" y1="36" x2="27.5" y2="36" />
                  <line x1="24" y1="6" x2="24" y2="9.5" />
                  <line x1="12" y1="11" x2="14.5" y2="13.5" />
                  <line x1="36" y1="11" x2="33.5" y2="13.5" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-bronze">🍫 HOW WE USE IT</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(ing.applications)}</div>
            </div>
          </div>

          <!-- 03. The Technical Challenge -->
          <div class="lab-quad-card card-accent-terracotta">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-circle-badge badge-circle-terracotta">03</span>
                  <span class="lab-quad-dash dash-terracotta"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-terracotta">What to Watch Out For</h3>
                  <p class="lab-quad-prompt">What challenges arise when blending this with melted chocolate?</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-blush" title="The Technical Challenge">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#9C382A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="24" cy="24" r="6" />
                  <path d="M38.8 21.2l-2.4-.7c-.3-1-.7-1.9-1.3-2.8l1.4-2.1c.5-.8.4-1.8-.2-2.4l-2.8-2.8c-.6-.6-1.6-.7-2.4-.2l-2.1 1.4c-.9-.6-1.8-1-2.8-1.3l-.7-2.4C25.6 7.2 24.8 6.6 24 6.6s-1.6.6-1.9 1.4l-.7 2.4c-1 .3-1.9.7-2.8 1.3l-2.1-1.4c-.8-.5-1.8-.4-2.4.2l-2.8 2.8c-.6.6-.7 1.6-.2 2.4l1.4 2.1c-.6.9-1 1.8-1.3 2.8l-2.4.7c-.8.2-1.4 1-1.4 1.9s.6 1.7 1.4 1.9l2.4.7c.3 1 .7 1.9 1.3 2.8l-1.4 2.1c-.5.8-.4 1.8.2 2.4l2.8 2.8c.6.6 1.6.7 2.4.2l2.1-1.4c.9.6 1.8 1 2.8 1.3l.7 2.4c.3.8 1.1 1.4 1.9 1.4s1.6-.6 1.9-1.4l.7-2.4c1-.3 1.9-.7 2.8-1.3l2.1 1.4c.8.5 1.8.4 2.4-.2l2.8-2.8c.6-.6.7-1.6.2-2.4l-1.4-2.1c.6-.9 1-1.8 1.3-2.8l2.4-.7c.8-.2 1.4-1 1.4-1.9s-.6-1.7-1.4-1.9z" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-terracotta">⚙️ WHAT TO WATCH OUT FOR</span>
              </div>
              <div class="lab-quad-text">${renderLabContent(ing.science_behavior)}</div>
            </div>
          </div>

          <!-- 04. Key Takeaway -->
          <div class="lab-quad-card card-accent-green">
            <div class="lab-quad-header">
              <div class="lab-quad-num-title-wrap">
                <div class="lab-quad-num-wrap">
                  <span class="lab-quad-circle-badge badge-circle-green">04</span>
                  <span class="lab-quad-dash dash-green"></span>
                </div>
                <div class="lab-quad-title-group">
                  <h3 class="lab-quad-heading heading-green">Key Takeaway</h3>
                  <p class="lab-quad-prompt">The single most important tip for working with this ingredient.</p>
                </div>
              </div>
              <div class="lab-quad-blob blob-sage" title="Key Takeaway">
                <svg width="34" height="34" viewBox="0 0 48 48" fill="none" stroke="#166534" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="23" cy="25" r="13" />
                  <circle cx="23" cy="25" r="7" />
                  <circle cx="23" cy="25" r="2" fill="#166534" />
                  <line x1="23" y1="25" x2="38" y2="10" stroke-width="2.6" />
                  <polyline points="33 10 38 10 38 15" stroke-width="2.4" />
                  <line x1="31" y1="17" x2="35" y2="13" />
                </svg>
              </div>
            </div>
            <div class="lab-quad-divider"></div>
            <div class="lab-quad-body takeaway-highlight">
              <div class="lab-quad-badge-row">
                <span class="lab-quad-badge badge-green">🎯 KEY TAKEAWAY</span>
              </div>
              <div class="lab-quad-text takeaway-text" style="color: #14251B;">${renderLabContent(ing.lab_notes)}</div>
              <div class="lab-takeaway-meta" style="border-top: 1px dashed #C2D9C5;">
                <span class="meta-dot" style="background: #166534;"></span>
                <span class="meta-label" style="color: #2D5838;">Good to know: Balanced for great taste and natural freshness</span>
              </div>
            </div>
          </div>

        </div>
      </div>

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

function renderLabContent(raw) {
  if (!raw) return '';
  const text = String(raw).trim();
  if (!text) return '';

  // Check if text already contains HTML formatting tags
  const hasHtml = /<(p|br|ul|ol|li|strong|b|em|i|u|s|h[1-6]|blockquote|span|a|div)[^>]*>/i.test(text);
  if (hasHtml) {
    // Strip dangerous scripts or event handlers while keeping full formatting tags
    return text
      .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
      .replace(/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/gi, '')
      .replace(/\bon\w+\s*=\s*(["'])?[^"'>]+(\1)?/gi, '')
      .replace(/javascript:/gi, '');
  }

  // Helper for inline markdown formatting (bold, italic, code)
  const formatInline = (str) => {
    return escapeHtml(str)
      .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
      .replace(/__([^_]+)__/g, '<strong>$1</strong>')
      .replace(/(^|[^*])\*([^*]+)\*([^*]|$)/g, '$1<em>$2</em>$3')
      .replace(/(^|[^_])_([^_]+)_([^_]|$)/g, '$1<em>$2</em>$3')
      .replace(/`([^`]+)`/g, '<code>$1</code>');
  };

  // Plain text with newlines or bullet points (- or * or •) or numbered items (1., 2., 1))
  const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean);
  let html = '';
  let inUl = false;
  let inOl = false;

  lines.forEach(line => {
    if (/^[-*•]\s+/.test(line)) {
      if (inOl) { html += '</ol>'; inOl = false; }
      if (!inUl) { html += '<ul>'; inUl = true; }
      const itemContent = line.replace(/^[-*•]\s+/, '');
      html += '<li>' + formatInline(itemContent) + '</li>';
    } else if (/^\d+[\.\)\-:]\s+/.test(line)) {
      if (inUl) { html += '</ul>'; inUl = false; }
      if (!inOl) { html += '<ol>'; inOl = true; }
      const itemContent = line.replace(/^\d+[\.\)\-:]\s+/, '');
      html += '<li>' + formatInline(itemContent) + '</li>';
    } else if (/^>\s+/.test(line)) {
      if (inUl) { html += '</ul>'; inUl = false; }
      if (inOl) { html += '</ol>'; inOl = false; }
      const quoteContent = line.replace(/^>\s+/, '');
      html += '<blockquote>' + formatInline(quoteContent) + '</blockquote>';
    } else {
      if (inUl) { html += '</ul>'; inUl = false; }
      if (!inOl) { html += '</ol>'; inOl = false; }
      html += '<p>' + formatInline(line) + '</p>';
    }
  });

  if (inUl) html += '</ul>';
  if (inOl) html += '</ol>';
  return html;
}


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

// ===================================================================
// INGREDIENT INNOVATION: AUTO-FLOATING CAROUSEL & SEARCH CONTROLLER
// ===================================================================
let isIngDragging = false;
let ingDragDistance = 0;
let ingLastX = 0;
let isIngPaused = false;
let isIngHovered = false;
let ingActiveCategory = 'all';
let ingResumeTimer = null;

function handleCardClick(id, e) {
  if (ingDragDistance > 7) {
    if (e) {
      e.preventDefault();
      e.stopPropagation();
    }
    return;
  }
  openIngredientModal(id, e);
}

function updateFloatingStatus(text, isLive) {
  // Indicator removed
}

function scrollIngredientCarousel(direction) {
  const viewport = document.getElementById('ingredientCarouselViewport');
  if (!viewport) return;
  isIngPaused = true;
  updateFloatingStatus('MANUAL NAV', false);
  clearTimeout(ingResumeTimer);

  const scrollDistance = (viewport.clientWidth > 600 ? 280 : 200) * direction;
  viewport.scrollBy({ left: scrollDistance, behavior: 'smooth' });

  ingResumeTimer = setTimeout(() => {
    isIngPaused = false;
    updateFloatingStatus('AUTO-FLOATING', true);
  }, 2500);
}

function filterIngredientCategory(catSlug, btnEl) {
  ingActiveCategory = catSlug;
  document.querySelectorAll('.lab-ing-pill').forEach(pill => pill.classList.remove('active'));
  if (btnEl) btnEl.classList.add('active');

  const searchInput = document.getElementById('labIngredientSearch');
  const clearBtn = document.getElementById('labSearchClearBtn');
  if (searchInput) {
    searchInput.value = '';
    if (clearBtn) clearBtn.style.display = 'none';
  }

  applyIngredientFilters();
}

function handleIngredientSearch(query) {
  const clearBtn = document.getElementById('labSearchClearBtn');
  if (clearBtn) {
    clearBtn.style.display = (query && query.trim().length > 0) ? 'block' : 'none';
  }
  applyIngredientFilters();
}

function clearIngredientSearch() {
  const searchInput = document.getElementById('labIngredientSearch');
  const clearBtn = document.getElementById('labSearchClearBtn');
  if (searchInput) searchInput.value = '';
  if (clearBtn) clearBtn.style.display = 'none';

  ingActiveCategory = 'all';
  document.querySelectorAll('.lab-ing-pill').forEach(pill => {
    pill.classList.toggle('active', pill.getAttribute('data-filter') === 'all');
  });

  applyIngredientFilters();
  if (searchInput) searchInput.focus();
}

function applyIngredientFilters() {
  const searchInput = document.getElementById('labIngredientSearch');
  const countEl = document.getElementById('labSearchCount');
  const noResultsEl = document.getElementById('ingredientNoResults');
  const noResultsQuery = document.getElementById('noResultsQuery');
  const track = document.getElementById('ingredientCarouselTrack');
  const viewport = document.getElementById('ingredientCarouselViewport');
  if (!track || !viewport) return;

  const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
  const isFiltered = (ingActiveCategory !== 'all') || (query.length > 0);
  const clones = track.querySelectorAll('.lab-ing-card.is-clone');
  const primaryCards = track.querySelectorAll('.lab-ing-card:not(.is-clone)');

  if (isFiltered) {
    isIngPaused = true;
    updateFloatingStatus('FILTERED', false);
    // Hide clones while searching/filtering to avoid duplicate matches
    clones.forEach(c => c.style.display = 'none');

    let matchCount = 0;
    primaryCards.forEach(card => {
      const cardSlug = card.getAttribute('data-slug') || '';
      const cardTitle = card.getAttribute('data-title') || '';
      const cardDesc = card.getAttribute('data-desc') || '';
      const cardVarietals = card.getAttribute('data-varietals') || '';
      const cardScience = card.getAttribute('data-science') || '';
      const cardApps = card.getAttribute('data-apps') || '';

      let catMatch = true;
      if (ingActiveCategory !== 'all') {
        catMatch = (cardSlug === ingActiveCategory);
      }

      let textMatch = true;
      if (query.length > 0) {
        const fullSearchContent = `${cardTitle} ${cardDesc} ${cardVarietals} ${cardScience} ${cardApps}`.toLowerCase();
        textMatch = fullSearchContent.includes(query);
      }

      if (catMatch && textMatch) {
        card.style.display = 'flex';
        card.classList.add('matched-filter');
        matchCount++;
      } else {
        card.style.display = 'none';
        card.classList.remove('matched-filter');
      }
    });

    if (countEl) {
      countEl.textContent = `${matchCount} ${matchCount === 1 ? 'match' : 'matches'}`;
    }

    if (matchCount === 0) {
      if (noResultsEl) {
        noResultsEl.style.display = 'block';
        if (noResultsQuery) noResultsQuery.textContent = query || ingActiveCategory;
      }
    } else {
      if (noResultsEl) noResultsEl.style.display = 'none';
      viewport.scrollLeft = 0;
    }
  } else {
    // Restore all items and resume auto-float
    isIngPaused = false;
    updateFloatingStatus('AUTO-FLOATING', true);
    clones.forEach(c => c.style.display = 'flex');
    primaryCards.forEach(card => {
      card.style.display = 'flex';
      card.classList.remove('matched-filter');
    });
    if (noResultsEl) noResultsEl.style.display = 'none';
    if (countEl) countEl.textContent = `${primaryCards.length} categories`;
  }
}

// Initialize floating carousel loop on DOM ready
window.addEventListener('DOMContentLoaded', function() {
  const wrapper = document.getElementById('ingredientCarouselWrapper');
  const viewport = document.getElementById('ingredientCarouselViewport');
  const track = document.getElementById('ingredientCarouselTrack');
  if (!wrapper || !viewport || !track) return;

  function getSingleSetWidth() {
    const cards = Array.from(track.querySelectorAll('.lab-ing-card:not(.is-clone)'));
    if (!cards.length) return 0;
    const first = cards[0];
    const last = cards[cards.length - 1];
    return (last.offsetLeft + last.offsetWidth) - first.offsetLeft + 22;
  }

  // Auto-float animation loop
  const speed = 0.65; // gentle continuous gliding speed
  function autoFloatStep() {
    const searchInput = document.getElementById('labIngredientSearch');
    const isSearching = searchInput && searchInput.value.trim().length > 0;

    if (!isIngPaused && !isIngHovered && !isIngDragging && ingActiveCategory === 'all' && !isSearching) {
      const singleSetWidth = getSingleSetWidth();
      if (singleSetWidth > 0) {
        viewport.scrollLeft += speed;
        if (viewport.scrollLeft >= singleSetWidth) {
          viewport.scrollLeft -= singleSetWidth;
        }
      }
    }
    requestAnimationFrame(autoFloatStep);
  }
  requestAnimationFrame(autoFloatStep);

  // Pause on hover over wrapper or buttons
  wrapper.addEventListener('mouseenter', function() {
    isIngHovered = true;
    updateFloatingStatus('PAUSED (HOVER)', false);
  });
  wrapper.addEventListener('mouseleave', function() {
    isIngHovered = false;
    if (!isIngPaused && ingActiveCategory === 'all') {
      updateFloatingStatus('AUTO-FLOATING', true);
    }
  });

  // Drag-to-scroll / swipe support
  let dragStartX = 0;
  let dragScrollStart = 0;

  viewport.addEventListener('mousedown', function(e) {
    isIngDragging = true;
    ingDragDistance = 0;
    dragStartX = e.pageX - viewport.offsetLeft;
    ingLastX = e.pageX;
    dragScrollStart = viewport.scrollLeft;
    viewport.classList.add('is-dragging');
  });

  window.addEventListener('mousemove', function(e) {
    if (!isIngDragging) return;
    ingDragDistance += Math.abs(e.pageX - ingLastX);
    ingLastX = e.pageX;
    const currentX = e.pageX - viewport.offsetLeft;
    const walk = (currentX - dragStartX) * 1.4;
    viewport.scrollLeft = dragScrollStart - walk;
  });

  window.addEventListener('mouseup', function() {
    if (isIngDragging) {
      isIngDragging = false;
      viewport.classList.remove('is-dragging');
      clearTimeout(ingResumeTimer);
      ingResumeTimer = setTimeout(() => {
        ingDragDistance = 0;
      }, 150);
    }
  });

  // Touch handling
  viewport.addEventListener('touchstart', function() {
    isIngPaused = true;
  }, { passive: true });

  viewport.addEventListener('touchend', function() {
    clearTimeout(ingResumeTimer);
    ingResumeTimer = setTimeout(function() {
      isIngPaused = false;
    }, 2000);
  }, { passive: true });
});

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
