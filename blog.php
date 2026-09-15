<?php
  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/blog-cache.php';
  require_once __DIR__ . '/includes/blog-data.php';

  $blogs = [];
  try {
      $pdo = get_db();
      $stmt = $pdo->query("SELECT id, slug, title, category, created_at, excerpt, image_path, thumbnail_path, youtube_url, body_class, read_time FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
      $dbBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($dbBlogs as $blog) {
          $blogs[] = [
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
  } catch (Exception $e) {
      $cached = get_cached_blog_list();
      if ($cached) {
          $blogs = $cached;
      } else {
          $idx = 1;
          $reversedBlogs = array_reverse($BLOGS, true);
          foreach ($reversedBlogs as $slug => $meta) {
              $blogs[] = [
                  'id' => $idx++,
                  'slug' => $slug,
                  'title' => $meta['title'],
                  'category' => $meta['category'],
                  'date' => $meta['date'],
                  'read_time' => $meta['read'] ?? '5 min',
                  'excerpt' => $meta['excerpt'],
                  'image' => $meta['image'],
                  'thumbnail' => $meta['image'],
                  'youtube_url' => $meta['youtube_url'] ?? null,
                  'body_class' => $meta['bodyClass'] ?? ''
              ];
          }
      }
  }

  $pageTitle = "Chocolate Blog India: Cocoa Science & Making | RT Chocos";
  $pageDescription = "An Indian chocolate blog with practical guides to cocoa science, tempering, ingredients, flavour, defects and bean-to-bar chocolate making.";
  $pathPrefix = "";
  $canonicalUrl = "https://www.rtchocos.com/blog";
  $schemaType = "CollectionPage";
  $itemList = ['name' => 'RT Chocos chocolate articles', 'items' => array_map(function($blog) {
      return ['name' => $blog['title'], 'url' => 'https://www.rtchocos.com/blog/' . $blog['slug']];
  }, $blogs)];
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Blog', 'item' => $canonicalUrl]
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-blog" class="page active" style="padding-top:80px;">
  <div class="page-hero blog-page-hero" style="padding: 24px 20px 0;">
    <div class="blog-wheel-hero-container">
      
      <!-- Left Column: The Cacao Journal Lead (Matching Video Graphic) -->
      <div class="blog-wheel-left">
        <div class="wheel-hero-eyebrow">THE</div>
        <h1 class="wheel-hero-title">
          <span>CACAO</span>
          <span>JOURNAL</span>
        </h1>
        <div class="wheel-hero-divider"></div>
        <p class="wheel-hero-desc">
          From origin to flavour, science to innovation — every angle of cacao, in one place.
        </p>

        <div class="wheel-hero-actions">
          <button type="button" class="btn-cacao-explore-blog" onclick="document.getElementById('blog-grid').scrollIntoView({behavior: 'smooth'})">
            <span>EXPLORE THE BLOG</span> &rarr;
          </button>
        </div>

        <!-- Sleek Active Topic Lens Indicator -->
        <div class="wheel-active-indicator-bar" id="wheelActiveBar">
          <span class="indicator-dot"></span>
          <span class="indicator-label">Active Lens:</span>
          <strong class="indicator-topic" id="wheelActiveTopicName">All Cacao Fields</strong>
          <button type="button" class="indicator-reset-btn" id="wheelResetFilter" onclick="window.cacaoResetFilter()" style="display:none;">Reset</button>
        </div>

        <!-- Mobile Quick Topic Chips -->
        <div class="wheel-mobile-topics-bar" id="wheelMobileChips">
          <button type="button" class="wheel-topic-chip" data-sector="origins" onclick="window.cacaoSelectTopic('origins')">Origins</button>
          <button type="button" class="wheel-topic-chip" data-sector="ingredients" onclick="window.cacaoSelectTopic('ingredients')">Ingredients</button>
          <button type="button" class="wheel-topic-chip" data-sector="processing" onclick="window.cacaoSelectTopic('processing')">Processing</button>
          <button type="button" class="wheel-topic-chip" data-sector="formulation" onclick="window.cacaoSelectTopic('formulation')">Formulation</button>
          <button type="button" class="wheel-topic-chip" data-sector="science" onclick="window.cacaoSelectTopic('science')">Science</button>
          <button type="button" class="wheel-topic-chip" data-sector="people" onclick="window.cacaoSelectTopic('people')">People</button>
          <button type="button" class="wheel-topic-chip" data-sector="trends" onclick="window.cacaoSelectTopic('trends')">Trends</button>
          <button type="button" class="wheel-topic-chip" data-sector="flavour" onclick="window.cacaoSelectTopic('flavour')">Flavour</button>
        </div>
      </div>

      <!-- Right Column: Interactive Cacao Journal Wheel (Fixed Compass Pointers + Rotating Slices) -->
      <div class="cacao-wheel-stage" id="cacaoWheelStage" title="Cacao Journal Interactive Wheel">
        <svg viewBox="0 0 800 660" class="cacao-wheel-svg" id="cacaoWheelSvg" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <filter id="goldGlow" x="-20%" y="-20%" width="140%" height="140%">
              <feGaussianBlur stdDeviation="5" result="blur" />
              <feComposite in="SourceGraphic" in2="blur" operator="over" />
            </filter>
            <filter id="subtleShadow" x="-10%" y="-10%" width="120%" height="120%">
              <feDropShadow dx="0" dy="6" stdDeviation="10" flood-color="#180E06" flood-opacity="0.14" />
            </filter>
            <clipPath id="clip-wheel-origins"><path d="M 319.64 135.99 A 210 210 0 0 1 480.36 135.99 L 430.61 256.09 A 80 80 0 0 0 369.39 256.09 Z" /></clipPath>
            <clipPath id="clip-wheel-ingredients"><path d="M 480.36 135.99 A 210 210 0 0 1 594.01 249.64 L 473.91 299.39 A 80 80 0 0 0 430.61 256.09 Z" /></clipPath>
            <clipPath id="clip-wheel-processing"><path d="M 594.01 249.64 A 210 210 0 0 1 594.01 410.36 L 473.91 360.61 A 80 80 0 0 0 473.91 299.39 Z" /></clipPath>
            <clipPath id="clip-wheel-formulation"><path d="M 594.01 410.36 A 210 210 0 0 1 480.36 524.01 L 430.61 403.91 A 80 80 0 0 0 473.91 360.61 Z" /></clipPath>
            <clipPath id="clip-wheel-science"><path d="M 480.36 524.01 A 210 210 0 0 1 319.64 524.01 L 369.39 403.91 A 80 80 0 0 0 430.61 403.91 Z" /></clipPath>
            <clipPath id="clip-wheel-people"><path d="M 319.64 524.01 A 210 210 0 0 1 205.99 410.36 L 326.09 360.61 A 80 80 0 0 0 369.39 403.91 Z" /></clipPath>
            <clipPath id="clip-wheel-trends"><path d="M 205.99 410.36 A 210 210 0 0 1 205.99 249.64 L 326.09 299.39 A 80 80 0 0 0 326.09 360.61 Z" /></clipPath>
            <clipPath id="clip-wheel-flavour"><path d="M 205.99 249.64 A 210 210 0 0 1 319.64 135.99 L 369.39 256.09 A 80 80 0 0 0 326.09 299.39 Z" /></clipPath>
            <clipPath id="clip-wheel-center"><circle cx="400" cy="330" r="79" /></clipPath>
          </defs>

          <!-- Outer Orbit Accents -->
          <circle cx="400" cy="330" r="218" class="wheel-orbit-ring-dashed" />
          <circle cx="400" cy="330" r="210" class="wheel-outer-rim" />

          <!-- ROTATING WHEEL BODY (Continuous Smooth Rotation) -->
          <g id="cacaoWheelRotator" class="wheel-rotator-slices" style="transform-origin: 400px 330px;">
            <!-- Sector: ORIGINS -->
            <g class="wheel-sector-group" data-sector="origins" onclick="window.cacaoSelectTopic('origins')">
              <g clip-path="url(#clip-wheel-origins)">
                <image href="assets/blog_wheel/segment_origins.jpg" x="311.6" y="128.0" width="176.7" height="136.1" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 319.64 135.99 A 210 210 0 0 1 480.36 135.99 L 430.61 256.09 A 80 80 0 0 0 369.39 256.09 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: INGREDIENTS -->
            <g class="wheel-sector-group" data-sector="ingredients" onclick="window.cacaoSelectTopic('ingredients')">
              <g clip-path="url(#clip-wheel-ingredients)">
                <image href="assets/blog_wheel/segment_ingredients.jpg" x="422.6" y="128.0" width="179.4" height="179.4" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 480.36 135.99 A 210 210 0 0 1 594.01 249.64 L 473.91 299.39 A 80 80 0 0 0 430.61 256.09 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: PROCESSING -->
            <g class="wheel-sector-group" data-sector="processing" onclick="window.cacaoSelectTopic('processing')">
              <g clip-path="url(#clip-wheel-processing)">
                <image href="assets/blog_wheel/segment_processing.jpg" x="465.9" y="241.6" width="136.1" height="176.7" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 594.01 249.64 A 210 210 0 0 1 594.01 410.36 L 473.91 360.61 A 80 80 0 0 0 473.91 299.39 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: FORMULATION -->
            <g class="wheel-sector-group" data-sector="formulation" onclick="window.cacaoSelectTopic('formulation')">
              <g clip-path="url(#clip-wheel-formulation)">
                <image href="assets/blog_wheel/segment_formulation.jpg" x="422.6" y="352.6" width="179.4" height="179.4" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 594.01 410.36 A 210 210 0 0 1 480.36 524.01 L 430.61 403.91 A 80 80 0 0 0 473.91 360.61 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: SCIENCE -->
            <g class="wheel-sector-group" data-sector="science" onclick="window.cacaoSelectTopic('science')">
              <g clip-path="url(#clip-wheel-science)">
                <image href="assets/blog_wheel/segment_science.jpg" x="311.6" y="395.9" width="176.7" height="136.1" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 480.36 524.01 A 210 210 0 0 1 319.64 524.01 L 369.39 403.91 A 80 80 0 0 0 430.61 403.91 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: PEOPLE -->
            <g class="wheel-sector-group" data-sector="people" onclick="window.cacaoSelectTopic('people')">
              <g clip-path="url(#clip-wheel-people)">
                <image href="assets/blog_wheel/segment_people.jpg" x="198.0" y="352.6" width="179.4" height="179.4" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 319.64 524.01 A 210 210 0 0 1 205.99 410.36 L 326.09 360.61 A 80 80 0 0 0 369.39 403.91 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: TRENDS -->
            <g class="wheel-sector-group" data-sector="trends" onclick="window.cacaoSelectTopic('trends')">
              <g clip-path="url(#clip-wheel-trends)">
                <image href="assets/blog_wheel/segment_trends.jpg" x="198.0" y="241.6" width="136.1" height="176.7" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 205.99 410.36 A 210 210 0 0 1 205.99 249.64 L 326.09 299.39 A 80 80 0 0 0 326.09 360.61 Z" class="wheel-sector-stroke" />
            </g>
            <!-- Sector: FLAVOUR -->
            <g class="wheel-sector-group" data-sector="flavour" onclick="window.cacaoSelectTopic('flavour')">
              <g clip-path="url(#clip-wheel-flavour)">
                <image href="assets/blog_wheel/segment_flavour.jpg" x="198.0" y="128.0" width="179.4" height="179.4" preserveAspectRatio="xMidYMid slice" class="wheel-sector-img" />
              </g>
              <path d="M 205.99 249.64 A 210 210 0 0 1 319.64 135.99 L 369.39 256.09 A 80 80 0 0 0 326.09 299.39 Z" class="wheel-sector-stroke" />
            </g>

            <!-- 8 Sector Gold Separator Lines -->
            <line x1="369.39" y1="256.09" x2="319.64" y2="135.99" class="wheel-divider-line" />
            <line x1="430.61" y1="256.09" x2="480.36" y2="135.99" class="wheel-divider-line" />
            <line x1="473.91" y1="299.39" x2="594.01" y2="249.64" class="wheel-divider-line" />
            <line x1="473.91" y1="360.61" x2="594.01" y2="410.36" class="wheel-divider-line" />
            <line x1="430.61" y1="403.91" x2="480.36" y2="524.01" class="wheel-divider-line" />
            <line x1="369.39" y1="403.91" x2="319.64" y2="524.01" class="wheel-divider-line" />
            <line x1="326.09" y1="360.61" x2="205.99" y2="410.36" class="wheel-divider-line" />
            <line x1="326.09" y1="299.39" x2="205.99" y2="249.64" class="wheel-divider-line" />

            <circle cx="400" cy="330" r="210" class="wheel-outer-rim-inner" />
            <circle cx="400" cy="330" r="80" class="wheel-inner-rim" />
          </g>

          <!-- CENTER HUB: MOLTEN CHOCOLATE BOWL (NO PLAY BUTTON) -->
          <g class="wheel-center-hub">
            <g clip-path="url(#clip-wheel-center)">
              <image href="assets/blog_wheel/wheel_center_cacao.jpg" x="320" y="250" width="160" height="160" preserveAspectRatio="xMidYMid slice" class="wheel-center-img" />
            </g>
            <circle cx="400" cy="330" r="80" class="wheel-center-ring-gold" />
            <circle cx="400" cy="330" r="76.5" class="wheel-center-ring-inner" />
          </g>

          <!-- 8 FIXED COMPASS POINTER LINES & LABELS -->
          <g class="wheel-labels-layer">
            <!-- Label: ORIGINS -->
            <g class="wheel-label-item" data-sector="origins" onclick="window.cacaoSelectTopic('origins')">
              <line x1="400.00" y1="120.00" x2="400.00" y2="72.00" class="wheel-ptr-line" />
              <circle cx="400.00" cy="120.00" r="3.5" class="wheel-ptr-node" />
              <text x="400" y="52" text-anchor="middle" class="wheel-lbl-title">ORIGINS</text>
              <text x="400" y="68" text-anchor="middle" class="wheel-lbl-sub">Stories &amp; Terroir</text>
            </g>
            <!-- Label: INGREDIENTS -->
            <g class="wheel-label-item" data-sector="ingredients" onclick="window.cacaoSelectTopic('ingredients')">
              <line x1="548.49" y1="181.51" x2="582.43" y2="147.57" class="wheel-ptr-line" />
              <circle cx="548.49" cy="181.51" r="3.5" class="wheel-ptr-node" />
              <text x="592" y="142" text-anchor="start" class="wheel-lbl-title">INGREDIENTS</text>
              <text x="592" y="158" text-anchor="start" class="wheel-lbl-sub">Composition &amp; Function</text>
            </g>
            <!-- Label: PROCESSING -->
            <g class="wheel-label-item" data-sector="processing" onclick="window.cacaoSelectTopic('processing')">
              <line x1="610.00" y1="330.00" x2="658.00" y2="330.00" class="wheel-ptr-line" />
              <circle cx="610.00" cy="330.00" r="3.5" class="wheel-ptr-node" />
              <text x="670" y="325" text-anchor="start" class="wheel-lbl-title">PROCESSING</text>
              <text x="670" y="341" text-anchor="start" class="wheel-lbl-sub">From Bean to Bar</text>
            </g>
            <!-- Label: FORMULATION -->
            <g class="wheel-label-item" data-sector="formulation" onclick="window.cacaoSelectTopic('formulation')">
              <line x1="548.49" y1="478.49" x2="582.43" y2="512.43" class="wheel-ptr-line" />
              <circle cx="548.49" cy="478.49" r="3.5" class="wheel-ptr-node" />
              <text x="592" y="508" text-anchor="start" class="wheel-lbl-title">FORMULATION</text>
              <text x="592" y="524" text-anchor="start" class="wheel-lbl-sub">Applications</text>
            </g>
            <!-- Label: SCIENCE -->
            <g class="wheel-label-item" data-sector="science" onclick="window.cacaoSelectTopic('science')">
              <line x1="400.00" y1="540.00" x2="400.00" y2="588.00" class="wheel-ptr-line" />
              <circle cx="400.00" cy="540.00" r="3.5" class="wheel-ptr-node" />
              <text x="400" y="608" text-anchor="middle" class="wheel-lbl-title">SCIENCE</text>
              <text x="400" y="624" text-anchor="middle" class="wheel-lbl-sub">Research &amp; Insights</text>
            </g>
            <!-- Label: PEOPLE -->
            <g class="wheel-label-item" data-sector="people" onclick="window.cacaoSelectTopic('people')">
              <line x1="251.51" y1="478.49" x2="217.57" y2="512.43" class="wheel-ptr-line" />
              <circle cx="251.51" cy="478.49" r="3.5" class="wheel-ptr-node" />
              <text x="208" y="508" text-anchor="end" class="wheel-lbl-title">PEOPLE</text>
              <text x="208" y="524" text-anchor="end" class="wheel-lbl-sub">Makers, Thinkers</text>
            </g>
            <!-- Label: TRENDS -->
            <g class="wheel-label-item" data-sector="trends" onclick="window.cacaoSelectTopic('trends')">
              <line x1="190.00" y1="330.00" x2="142.00" y2="330.00" class="wheel-ptr-line" />
              <circle cx="190.00" cy="330.00" r="3.5" class="wheel-ptr-node" />
              <text x="130" y="325" text-anchor="end" class="wheel-lbl-title">TRENDS</text>
              <text x="130" y="341" text-anchor="end" class="wheel-lbl-sub">What's Next</text>
            </g>
            <!-- Label: FLAVOUR -->
            <g class="wheel-label-item" data-sector="flavour" onclick="window.cacaoSelectTopic('flavour')">
              <line x1="251.51" y1="181.51" x2="217.57" y2="147.57" class="wheel-ptr-line" />
              <circle cx="251.51" cy="181.51" r="3.5" class="wheel-ptr-node" />
              <text x="208" y="142" text-anchor="end" class="wheel-lbl-title">FLAVOUR</text>
              <text x="208" y="158" text-anchor="end" class="wheel-lbl-sub">Tasting &amp; Aroma</text>
            </g>
          </g>
        </svg>
      </div>

    </div>
  </div>
  <div class="section">
    <div style="text-align:center;margin-bottom:48px; display: flex; flex-direction: column; align-items: center;">
      <div class="section-label">The Cacao Journal</div>
      <h1 class="section-title">Articles &amp; Insights</h1>
      <p class="section-subtitle">Science, craft, and stories from the world of bean-to-bar chocolate</p>
    </div>
    <div class="blog-seo-intro">
      <p><strong>RT Chocos is an independent chocolate blog from India</strong>, written for makers who want to understand what happens inside the bowl—not simply follow a method. Explore evidence-led articles on cocoa ingredients, tempering, bloom, flavour development, formulation and bean-to-bar production.</p>
      <nav aria-label="Chocolate learning topics">
        <a href="#blog-grid">Cocoa science</a>
        <a href="#blog-grid">Beginner guides</a>
        <a href="gallery.php">Chocolate recipes</a>
        <a href="workshops.php">Workshops</a>
      </nav>
    </div>
    <div class="blog-header-bar">
      <div class="blog-filters-wrapper" id="blog-filters">
        <button class="filter-btn active" data-filter="All" onclick="filterBlog('All')">All</button>
        <button class="filter-btn" data-filter="Science" onclick="filterBlog('Science')">Science</button>
        <button class="filter-btn" data-filter="Beginner Guide" onclick="filterBlog('Beginner Guide')">Beginner Guide</button>
        <button class="filter-btn" data-filter="Business Tips" onclick="filterBlog('Business Tips')">Business Tips</button>
      </div>
      <div class="search-container">
        <input class="blog-search" type="text" placeholder="Search articles by topic, keyword..." oninput="searchBlog(this.value)" />
        <svg class="search-bar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
    </div>
    <div class="grid-blog" id="blog-grid">
      <?php foreach ($blogs as $b): ?>
        <?php 
          $href = "blog/" . $b['slug'];
          $ytBadge = !empty($b['youtube_url']) ? ' • <span class="yt-badge">🎥 Video</span>' : '';
        ?>
        <a class="card blog-card-link" href="<?php echo htmlspecialchars($href); ?>">
          <div class="blog-card-img">
            <?php if (!empty($b['image'])): ?>
              <img src="<?php echo htmlspecialchars($b['image']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
              <span style="display:none;">Chocolate Journal</span>
            <?php else: ?>
              <span>Chocolate Journal</span>
            <?php endif; ?>
          </div>
          <div class="blog-card-body">
            <h3 class="blog-card-title"><?php echo htmlspecialchars($b['title']); ?></h3>
            <div class="blog-meta">
              <span class="tag"><?php echo htmlspecialchars($b['category']); ?></span>
              <span class="blog-date"><?php echo htmlspecialchars($b['date']); ?> • <?php echo htmlspecialchars($b['read_time']); ?> read<?php echo $ytBadge; ?></span>
            </div>
            <p class="blog-excerpt"><?php echo htmlspecialchars($b['excerpt']); ?></p>
            <div class="blog-read-more">Read Article</div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
  // Cacao Journal Interactive Wheel Controller
  const CACAO_TOPICS = {
    origins: {
      title: 'Origins',
      fullTitle: 'Origins: Stories & Terroir',
      targetCategory: 'All',
      searchQuery: 'origin'
    },
    ingredients: {
      title: 'Ingredients',
      fullTitle: 'Ingredients: Composition & Function',
      targetCategory: 'Science',
      searchQuery: 'cocoa'
    },
    processing: {
      title: 'Processing',
      fullTitle: 'Processing: From Bean to Bar',
      targetCategory: 'Beginner Guide',
      searchQuery: 'processing'
    },
    formulation: {
      title: 'Formulation',
      fullTitle: 'Formulation: Applications & Ratios',
      targetCategory: 'Science',
      searchQuery: 'butter'
    },
    science: {
      title: 'Science',
      fullTitle: 'Science: Research & Insights',
      targetCategory: 'Science',
      searchQuery: 'science'
    },
    people: {
      title: 'People',
      fullTitle: 'People: Makers & Thinkers',
      targetCategory: 'All',
      searchQuery: 'craft'
    },
    trends: {
      title: 'Trends',
      fullTitle: "Trends: What's Next",
      targetCategory: 'All',
      searchQuery: 'industry'
    },
    flavour: {
      title: 'Flavour',
      fullTitle: 'Flavour: Tasting & Aroma',
      targetCategory: 'Beginner Guide',
      searchQuery: 'flavour'
    }
  };

  let activeTopicKey = null;
  let currentWheelAngle = 0;
  let isDragging = false;
  let startDragAngle = 0;
  let startWheelAngle = 0;
  const SPIN_SPEED = 0.16; // Smooth, continuous, mesmerizing rotation (~37s per full turn)

  window.cacaoSelectTopic = function(key) {
    const data = CACAO_TOPICS[key];
    if (!data) return;

    activeTopicKey = key;

    // Update active state on labels
    document.querySelectorAll('.wheel-label-item').forEach(el => {
      el.classList.toggle('active', el.getAttribute('data-sector') === key);
    });

    // Update active state on mobile chips
    document.querySelectorAll('.wheel-topic-chip').forEach(el => {
      el.classList.toggle('active', el.getAttribute('data-sector') === key);
    });

    // Update active state on wheel sectors
    document.querySelectorAll('.wheel-sector-group').forEach(el => {
      el.classList.toggle('active', el.getAttribute('data-sector') === key);
    });

    // Update active lens status pill
    const topicNameEl = document.getElementById('wheelActiveTopicName');
    const resetBtn = document.getElementById('wheelResetFilter');
    if (topicNameEl) {
      topicNameEl.textContent = data.fullTitle;
    }
    if (resetBtn) {
      resetBtn.style.display = 'inline-block';
    }

    // Filter blog grid
    if (typeof filterBlog === 'function' && data.targetCategory) {
      filterBlog(data.targetCategory);
    }

    // Set search query for focused precision
    const searchInput = document.querySelector('.blog-search');
    if (searchInput && data.searchQuery) {
      searchInput.value = data.searchQuery;
      if (typeof searchBlog === 'function') {
        searchBlog(data.searchQuery);
      }
    }

    // Smoothly scroll down to articles
    const target = document.getElementById('blog-grid');
    if (target) {
      const topOffset = target.getBoundingClientRect().top + window.pageYOffset - 110;
      window.scrollTo({ top: Math.max(0, topOffset), behavior: 'smooth' });
    }
  };

  window.cacaoResetFilter = function() {
    activeTopicKey = null;
    document.querySelectorAll('.wheel-label-item').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.wheel-topic-chip').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.wheel-sector-group').forEach(el => el.classList.remove('active'));

    const topicNameEl = document.getElementById('wheelActiveTopicName');
    const resetBtn = document.getElementById('wheelResetFilter');
    if (topicNameEl) topicNameEl.textContent = 'All Cacao Fields';
    if (resetBtn) resetBtn.style.display = 'none';

    const searchInput = document.querySelector('.blog-search');
    if (searchInput) {
      searchInput.value = '';
      if (typeof searchBlog === 'function') searchBlog('');
    }

    if (typeof filterBlog === 'function') {
      filterBlog('All');
    }
  };

  // Continuous Rotation & Drag Engine
  document.addEventListener('DOMContentLoaded', function() {
    const stage = document.getElementById('cacaoWheelStage');
    const rotator = document.getElementById('cacaoWheelRotator');
    if (!stage || !rotator) return;

    // Center coordinates calculation helper: center is (400, 330) in 800x660
    function getPointerAngle(e) {
      const rect = stage.getBoundingClientRect();
      const centerX = rect.left + rect.width * (400 / 800);
      const centerY = rect.top + rect.height * (330 / 660);
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const clientY = e.touches ? e.touches[0].clientY : e.clientY;
      const rad = Math.atan2(clientY - centerY, clientX - centerX);
      return rad * (180 / Math.PI);
    }

    function onPointerDown(e) {
      if (e.target.closest('.wheel-label-item') || e.target.closest('.wheel-center-hub')) {
        return;
      }
      isDragging = true;
      rotator.classList.remove('smooth-transition');
      startDragAngle = getPointerAngle(e);
      startWheelAngle = currentWheelAngle;
    }

    function onPointerMove(e) {
      if (!isDragging) return;
      e.preventDefault();
      const angle = getPointerAngle(e);
      const delta = angle - startDragAngle;
      currentWheelAngle = (startWheelAngle + delta) % 360;
      rotator.style.transform = `rotate(${currentWheelAngle}deg)`;
    }

    function onPointerUp() {
      if (isDragging) {
        isDragging = false;
      }
    }

    stage.addEventListener('mousedown', onPointerDown);
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('mouseup', onPointerUp);

    stage.addEventListener('touchstart', onPointerDown, { passive: true });
    window.addEventListener('touchmove', onPointerMove, { passive: false });
    window.addEventListener('touchend', onPointerUp);

    // Continuous Silky 60fps Rotation (Keeps spinning continuously without stalling)
    function loop() {
      if (!isDragging) {
        currentWheelAngle = (currentWheelAngle + SPIN_SPEED) % 360;
        rotator.style.transform = `rotate(${currentWheelAngle}deg)`;
      }
      requestAnimationFrame(loop);
    }
    requestAnimationFrame(loop);
  });
</script>

<?php
  include $pathPrefix . 'includes/footer.php';
?>
