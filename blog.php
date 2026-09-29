<?php
  require_once __DIR__ . '/includes/db.php';
  require_once __DIR__ . '/includes/blog-cache.php';
  require_once __DIR__ . '/includes/blog-data.php';

  $blogs = get_cached_blog_list(1800);
  if ($blogs === null || empty($blogs)) {
      try {
          $pdo = get_db();
          $stmt = $pdo->query("SELECT id, slug, title, category, created_at, excerpt, image_path, thumbnail_path, youtube_url, body_class, read_time FROM blogs WHERE is_published = 1 ORDER BY created_at DESC");
          $dbBlogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
          $blogs = [];
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
          cache_blog_list($blogs);
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
<div id="page-blog" class="page active" style="padding-top:76px;">
  <!-- =========================================================================
       BLOG HERO: Pure Full-Bleed Video Hero (Unmasked, No Filters, Clean Canvas)
       ========================================================================= -->
  <section class="blog-hero-lux">
    <!-- Pure Video Canvas (No Masking, No Filters) -->
    <div class="blog-hero-video-bg">
      <video 
        id="blogHeroFullscreenVideo" 
        class="blog-hero-fullscreen-video" 
        autoplay 
        loop 
        muted 
        playsinline 
        preload="auto" 
        poster="assets/blog_hero_poster.jpg"
      >
        <source src="assets/blog%20section%20herovideo.mp4" type="video/mp4">
        Your browser does not support HTML5 video.
      </video>
    </div>

    <!-- Clean Hero Content -->
    <div class="blog-hero-lux-container">
      <div class="blog-hero-lux-left">
        <h1 class="blog-hero-lux-title">The Cacao Journal</h1>
        <p class="blog-hero-lux-desc">
          From ancient Mayan origins to modern flavour chemistry and bean-to-bar craftsmanship — explore every angle of cacao craft and confectionery science.
        </p>

        <div class="blog-hero-lux-actions">
          <button type="button" class="btn-blog-lux-explore" onclick="document.getElementById('blog-grid').scrollIntoView({behavior: 'smooth'})">
            <span>EXPLORE ARTICLES</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
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

<?php
  include $pathPrefix . 'includes/footer.php';
?>
