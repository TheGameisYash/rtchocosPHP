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

  $pageTitle = "Chocolate Blog — Articles on Cocoa Science, Recipes & Making | RT Chocos India";
  $pageDescription = "Read India's best chocolate blog. Deep-dive articles on cocoa science, chocolate tempering, bean-to-bar making, flavour development, and industry insights by RT Chocos.";
  $pageKeywords = "chocolate blog, cocoa science, craft chocolate making, tempering chocolate science, bean to bar articles India";
  $pathPrefix = "";
  
  $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
  $canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  
  $breadcrumbs = [
      ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
      ['name' => 'Blog', 'item' => $canonicalUrl]
  ];
  
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-blog" class="page active" style="padding-top:80px;">
  <div class="page-hero blog-page-hero">
    <div class="blog-page-hero-image fade-in">
      <img src="assets/blog.jpg" alt="RT Chocos blog" />
    </div>
  </div>
  <div class="section">
    <div style="text-align:center;margin-bottom:48px;">
      <div class="section-label" style="margin-bottom:12px;">The Cacao Journal</div>
      <h1 style="font-family:'Cormorant Garamond',serif;font-size:clamp(36px,5vw,52px);font-weight:400;color:var(--brown);margin-bottom:12px;letter-spacing:-0.01em;">Articles &amp; Insights</h1>
      <p style="font-family:'Cormorant Garamond',serif;font-size:19px;font-style:italic;color:var(--brown-light);font-weight:300;max-width:560px;margin:0 auto;">Science, craft, and stories from the world of bean-to-bar chocolate</p>
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
