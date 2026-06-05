<?php
  $pageTitle = "Blog | RT Chocos — India's Chocolate Blog";
  $pageDescription = "Articles on cocoa science, chocolate formulation, quality control, and recipes.";
  $pathPrefix = "";
  include $pathPrefix . 'includes/header.php';
?>

<!-- --- HOME PAGE --- -->
<div id="page-blog" class="page active" style="padding-top:72px;">
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
    <div style="display:flex;flex-direction:column;align-items:center;gap:20px;margin-bottom:48px;">
      <div class="search-container">
        <input class="blog-search" type="text" placeholder="Search articles by topic, keyword..." oninput="searchBlog(this.value)" />
        <svg class="search-bar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;" id="blog-filters">
        <button class="filter-btn active" data-filter="All" onclick="filterBlog('All')">All</button>
        <button class="filter-btn" data-filter="Science" onclick="filterBlog('Science')">Science</button>
        <button class="filter-btn" data-filter="Beginner Guide" onclick="filterBlog('Beginner Guide')">Beginner Guide</button>
        <button class="filter-btn" data-filter="Business Tips" onclick="filterBlog('Business Tips')">Business Tips</button>
      </div>
    </div>
    <div class="grid-blog" id="blog-grid"></div>
  </div>
</div>
<div id="page-blog-article" class="page" style="padding-top:72px;">
  <div class="page-hero blog-page-hero" style="min-height:320px;">
    <div class="page-hero-content" style="max-width:860px;text-align:left;">
      <button class="btn-outline-dark" type="button" onclick="navigate('blog')" style="margin-bottom:20px;">&larr; Back to Blog</button>
      <div class="section-label" id="blog-article-category">Science</div>
      <img id="blog-article-image" src="" alt="" style="display:none;width:100%;max-width:720px;border-radius:20px;box-shadow:0 18px 50px rgba(59,42,34,0.18);margin:8px 0 20px;" />
      <h2 id="blog-article-title" class="fade-up" style="max-width:14ch;font-size:clamp(32px,4vw,44px);">Why pH is the Most Underrated Factor in Cocoa Powder</h2>
      <p id="blog-article-meta" class="fade-up-d1" style="max-width:none;">Apr 2026 ? 7 min read</p>
    </div>
  </div>
  <div class="section" style="max-width:860px;">
    <?php include $pathPrefix . 'includes/comments.php'; ?>
  </div>
</div>

<!-- --- GALLERY PAGE --- -->

<?php
  include $pathPrefix . 'includes/footer.php';
?>
