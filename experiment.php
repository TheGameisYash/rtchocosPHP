<?php
// experiment.php — Dedicated Single Experiment Article for RT Chocos Innovation Lab
// Styled identically to RT Chocos Blog Articles (blog-article.php)
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';

$slug = $_GET['slug'] ?? '';
$slug = trim($slug);

if (empty($slug)) {
    header('Location: ../innovation-lab.php');
    exit;
}

$pdo = get_db();
$isAdmin = function_exists('is_admin_logged_in') && is_admin_logged_in();

// Fetch experiment by slug (or fallback by numeric ID)
$stmt = $pdo->prepare("SELECT * FROM lab_experiments WHERE slug = ? OR (id = ? AND id > 0) LIMIT 1");
$stmt->execute([$slug, is_numeric($slug) ? (int)$slug : 0]);
$exp = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$exp) {
    http_response_code(404);
    $pathPrefix = "../";
    if (file_exists(__DIR__ . '/error.php')) {
        include __DIR__ . '/error.php';
    } else {
        echo "<h1>404 — Experiment Not Found</h1><p><a href='../innovation-lab.php'>Return to Innovation Lab</a></p>";
    }
    exit;
}

// Check active / draft status
$isAdminPreview = false;
if ((int)$exp['is_active'] === 0) {
    if ($isAdmin) {
        $isAdminPreview = true;
    } else {
        http_response_code(404);
        $pathPrefix = "../";
        if (file_exists(__DIR__ . '/error.php')) {
            include __DIR__ . '/error.php';
        } else {
            echo "<h1>404 — Experiment Not Found</h1><p><a href='../innovation-lab.php'>Return to Innovation Lab</a></p>";
        }
        exit;
    }
}

// Path prefix for clean URLs (/experiment/{slug} is 1 directory deep)
$pathPrefix = "../";

// SEO & Header Variables
$pageTitle = htmlspecialchars($exp['title']) . " | RT Chocos Innovation Lab";
$pageDescription = htmlspecialchars($exp['description'] ?: 'Active confectionery research and chocolate formulation experiment from the RT Chocos kitchen.');
$pageImage = $exp['image_path'] ?: 'assets/images/placeholder.jpg';
$pageType = 'article';

$canonicalUrl = "https://www.rtchocos.com/experiment/" . rawurlencode($exp['slug']);

$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Innovation Lab', 'item' => 'https://www.rtchocos.com/innovation-lab.php'],
    ['name' => $exp['title'], 'item' => $canonicalUrl]
];

// Fetch 3 other active experiments for the "Related Experiments" footer grid
$relatedStmt = $pdo->prepare("SELECT id, title, slug, category, status_badge, status_color, image_path, description, tags, batch_ref FROM lab_experiments WHERE id != ? AND is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 3");
$relatedStmt->execute([$exp['id']]);
$relatedExperiments = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

// Helper for rendering rich text / markdown blocks safely
function render_exp_content($raw) {
    if (!$raw) return '';
    $text = trim((string)$raw);
    if (!$text) return '';

    // If already contains HTML tags, sanitize scripts and return
    if (preg_match('/<(p|br|ul|ol|li|strong|b|em|i|h[1-6]|blockquote|table|div|span)[^>]*>/i', $text)) {
        return preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/i', '', $text);
    }

    // Markdown-like parser
    $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $text));
    $html = '';
    $inUl = false;
    $inOl = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            if ($inUl) { $html .= "</ul>\n"; $inUl = false; }
            if ($inOl) { $html .= "</ol>\n"; $inOl = false; }
            continue;
        }

        // Unordered list
        if (preg_match('/^[-*•]\s+(.+)$/', $trimmed, $m)) {
            if ($inOl) { $html .= "</ol>\n"; $inOl = false; }
            if (!$inUl) { $html .= "<ul>\n"; $inUl = true; }
            $html .= "  <li>" . format_exp_inline($m[1]) . "</li>\n";
            continue;
        }

        // Ordered list
        if (preg_match('/^\d+[\.\)]\s+(.+)$/', $trimmed, $m)) {
            if ($inUl) { $html .= "</ul>\n"; $inUl = false; }
            if (!$inOl) { $html .= "<ol>\n"; $inOl = true; }
            $html .= "  <li>" . format_exp_inline($m[1]) . "</li>\n";
            continue;
        }

        if ($inUl) { $html .= "</ul>\n"; $inUl = false; }
        if ($inOl) { $html .= "</ol>\n"; $inOl = false; }

        // Headings
        if (preg_match('/^(#{1,4})\s+(.+)$/', $trimmed, $m)) {
            $lvl = strlen($m[1]) + 1;
            $hSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $m[2]), '-'));
            $html .= "<h{$lvl} id=\"{$hSlug}\">" . format_exp_inline($m[2]) . "</h{$lvl}>\n";
            continue;
        }

        // Blockquotes
        if (strpos($trimmed, '> ') === 0) {
            $html .= "<blockquote><p>" . format_exp_inline(substr($trimmed, 2)) . "</p></blockquote>\n";
            continue;
        }

        // Paragraph
        $html .= "<p>" . format_exp_inline($trimmed) . "</p>\n";
    }

    if ($inUl) $html .= "</ul>\n";
    if ($inOl) $html .= "</ol>\n";
    return $html;
}

function format_exp_inline($str) {
    $str = htmlspecialchars($str);
    $str = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $str);
    $str = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $str);
    $str = preg_replace('/`([^`]+)`/', '<code>$1</code>', $str);
    return $str;
}

$badgeColor = htmlspecialchars($exp['status_color'] ?: 'green');
$batchCode = htmlspecialchars($exp['batch_ref'] ?: ('RT-EXP-0' . $exp['id']));
$rawImg = !empty($exp['image_path']) ? $exp['image_path'] : 'assets/images/placeholder.jpg';
$resolvedImg = (strpos($rawImg, 'http') === 0 || strpos($rawImg, '/') === 0 || strpos($rawImg, '../') === 0) ? $rawImg : ($pathPrefix . $rawImg);

include 'includes/header.php';
?>

<!-- Reading Scroll Progress Bar -->
<div id="readingProgressBar" style="position:fixed; top:0; left:0; width:0; height:4px; background:var(--gold, #C7A66A); z-index:99999; transition:width 0.1s ease;"></div>

<style>
/* =========================================================================
   LUXURY BLOG EDITORIAL ARTICLE STYLES (Identical to blog-article.php)
   ========================================================================= */
#blog-article-content {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 17.5px;
    line-height: 1.88;
    color: var(--text-primary, #1F2E25);
    letter-spacing: -0.003em;
}

#blog-article-content > p:first-of-type {
    font-size: 19.5px;
    line-height: 1.85;
    color: var(--text-heading, #112217);
    font-weight: 400;
    margin-bottom: 34px;
}

#blog-article-content > p:first-of-type::first-letter {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 3.8rem;
    float: left;
    line-height: 0.78;
    margin-right: 12px;
    margin-top: 4px;
    font-weight: 700;
    color: var(--gold, #C7A66A);
    text-shadow: 0 2px 8px rgba(199, 166, 106, 0.2);
}

#blog-article-content p {
    margin-bottom: 26px;
    color: var(--text-primary, #1F2E25);
    font-weight: 400;
}

#blog-article-content h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(32px, 5vw, 44px);
    font-weight: 700;
    color: var(--text-heading, #112217);
    margin-top: 52px;
    margin-bottom: 24px;
    line-height: 1.25;
    letter-spacing: -0.015em;
}

#blog-article-content h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(26px, 3.5vw, 33px);
    font-weight: 700;
    color: var(--text-heading, #112217) !important;
    margin-top: 56px;
    margin-bottom: 22px;
    line-height: 1.32;
    letter-spacing: -0.01em;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(199, 166, 106, 0.35);
}

#blog-article-content h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(21px, 2.6vw, 25px);
    font-weight: 600;
    color: var(--text-heading, #16261c) !important;
    margin-top: 42px;
    margin-bottom: 18px;
    line-height: 1.38;
}

#blog-article-content blockquote {
    border-left: 4px solid var(--gold, #C7A66A);
    padding: 24px 32px;
    margin: 40px 0;
    font-family: 'Cormorant Garamond', 'Playfair Display', serif;
    font-size: 22px;
    line-height: 1.65;
    font-style: italic;
    background: rgba(199, 166, 106, 0.08);
    border-radius: 0 12px 12px 0;
    position: relative;
    color: var(--text-heading, #112217);
    box-shadow: 0 4px 16px rgba(17, 34, 23, 0.03);
}

#blog-article-content blockquote p {
    margin: 0;
    font-family: inherit;
    font-style: inherit;
    font-size: inherit;
    line-height: inherit;
    color: inherit;
}

#blog-article-content ul {
    list-style: none;
    margin: 24px 0;
    padding-left: 12px;
}

#blog-article-content ul li {
    position: relative;
    padding-left: 26px;
    margin-bottom: 12px;
    color: var(--text-primary, #1F2E25);
}

#blog-article-content ul li::before {
    content: '✦';
    position: absolute;
    left: 0;
    color: var(--gold, #C7A66A);
    font-size: 13px;
    top: 0;
}

#blog-article-content ol {
    margin: 24px 0;
    padding-left: 24px;
}

#blog-article-content ol li {
    margin-bottom: 12px;
    padding-left: 6px;
    color: var(--text-primary, #1F2E25);
}

#blog-article-content ol li::marker {
    color: var(--gold, #C7A66A);
    font-weight: 700;
}

/* Layout Wrappers */
.article-layout-wrapper {
    display: flex;
    gap: 48px;
    align-items: start;
    position: relative;
}
.article-main-body {
    flex: 1;
    min-width: 0;
    max-width: 760px;
}

.article-sidebar {
    width: 280px;
    position: sticky;
    top: 100px;
    flex-shrink: 0;
}

.toc-box {
    background: #FEFDFB;
    border: 1px solid var(--cream-dark, #EDE7DB);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(59,42,34,0.03);
    margin-bottom: 24px;
}
.toc-title {
    font-family: 'Cormorant Garamond', serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--brown, #3B2A22);
    margin-bottom: 16px;
    border-bottom: 1px solid var(--cream-dark, #EDE7DB);
    padding-bottom: 8px;
}
.toc-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.toc-item {
    margin-bottom: 10px;
    font-size: 13.5px;
    line-height: 1.4;
}
.toc-item a {
    color: var(--brown-light, #5C4033);
    text-decoration: none;
    transition: color 0.2s ease;
}
.toc-item a:hover, .toc-item.active a {
    color: var(--gold, #C7A66A);
}
.toc-item.active {
    font-weight: 600;
    border-left: 2px solid var(--gold, #C7A66A);
    padding-left: 6px;
    margin-left: -8px;
}

/* Social Share Buttons */
.share-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 32px 0;
    padding: 16px 0;
    border-top: 1px solid var(--cream-dark, #EDE7DB);
    border-bottom: 1px solid var(--cream-dark, #EDE7DB);
    flex-wrap: wrap;
}
.share-title {
    font-size: 12.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--brown-light, #5C4033);
}
.share-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    color: white;
    text-decoration: none;
    transition: transform 0.2s, opacity 0.2s;
}
.share-btn:hover {
    transform: translateY(-1px);
    opacity: 0.9;
}
.share-btn.tw { background: #1DA1F2; }
.share-btn.fb { background: #1877F2; }
.share-btn.ln { background: #0A66C2; }
.share-btn.wa { background: #25D366; }
.share-btn.copy { background: var(--cream-dark, #EDE7DB); color: var(--brown, #3B2A22); cursor: pointer; border: none; }

/* Sidebar Callout Card */
.exp-sidebar-callout {
    background: #14251B;
    color: #FAF6EF;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 10px 24px rgba(18, 34, 23, 0.15);
}
.exp-sidebar-callout h4 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 19px;
    color: #FFFFFF;
    margin: 0 0 10px;
}
.exp-sidebar-callout p {
    font-size: 13px;
    color: #A3B8AA;
    line-height: 1.6;
    margin: 0 0 18px;
}
.exp-sidebar-callout a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #C7A66A;
    color: #122217;
    padding: 9px 16px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-decoration: none;
    transition: background 0.2s;
}
.exp-sidebar-callout a:hover {
    background: #D8BA82;
}

/* Related articles grid */
.related-card {
    background: #FEFDFB;
    border: 1px solid var(--cream-dark, #EDE7DB);
    border-radius: 8px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 12px rgba(59,42,34,0.02);
}
.related-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(59,42,34,0.06);
}

.status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.status-dot.green { background: #16A34A; }
.status-dot.amber { background: #D97706; }
.status-dot.purple { background: #9333EA; }
.status-dot.blue { background: #2563EB; }

@media (max-width: 1024px) {
    .article-sidebar {
        display: none !important;
    }
}
</style>

<?php if ($isAdminPreview): ?>
    <div style="background:#1c1511; border-bottom:2px solid var(--gold, #C7A66A); padding:10px 24px; position:sticky; top:0; z-index:9999;">
        <div style="max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:10px; color:#f7ede2; font-size:13.5px;">
                <span style="background:#eab308; color:#000; font-size:11px; font-weight:800; text-transform:uppercase; padding:3px 8px; border-radius:20px;">Admin Preview</span>
                <span>This experiment is currently <strong>HIDDEN (Draft)</strong> from public visitors.</span>
            </div>
            <a href="<?php echo $pathPrefix; ?>admin/experiment-editor.php?id=<?php echo $exp['id']; ?>" style="background:var(--gold, #C7A66A); color:#111; font-size:12.5px; font-weight:700; padding:6px 14px; border-radius:6px; text-decoration:none;">
                Edit Experiment ↗
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- --- EXPERIMENT ARTICLE SECTION (Modeled directly after blog-article.php) --- -->
<div id="page-blog-article" class="page active">
  <div class="page-hero blog-page-hero">
    <div class="page-hero-content blog-article-hero-content">
      <a class="btn-outline-dark back-to-blog-btn" href="<?php echo $pathPrefix; ?>innovation-lab.php">&larr; Back to Innovation Lab</a>
      
      <div class="article-hero-text-container" style="max-width: 760px; margin: 0 auto;">
        <!-- Visual Breadcrumbs Trail -->
        <div class="blog-breadcrumbs" style="font-size: 13px; color: var(--gold, #C7A66A); margin: 16px 0 8px; font-family: var(--font-sans);">
          <a href="<?php echo $pathPrefix; ?>index.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Home</a>
          <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
          <a href="<?php echo $pathPrefix; ?>innovation-lab.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Innovation Lab</a>
          <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
          <span style="opacity: 0.7; font-weight: 500;"><?php echo htmlspecialchars($exp['title']); ?></span>
        </div>

        <div class="section-label" id="blog-article-category">
          <span><?php echo htmlspecialchars($exp['category']); ?></span>
          <span style="display:inline-flex; align-items:center; gap:5px; margin-left:8px; font-size:10.5px; padding:2px 8px; border-radius:12px; background:rgba(18,34,23,0.08); vertical-align:middle;">
            <span class="status-dot <?php echo $badgeColor; ?>"></span>
            <span><?php echo htmlspecialchars($exp['status_badge'] ?: 'IN PROGRESS'); ?></span>
          </span>
        </div>
        <h1 id="blog-article-title" class="fade-up"><?php echo htmlspecialchars($exp['title']); ?></h1>
        <p id="blog-article-meta" class="fade-up-d1">
          Batch #<?php echo $batchCode; ?> • Logged <?php echo date('M Y', strtotime($exp['created_at'])); ?> • RT Chocos Innovation Lab
        </p>
      </div>

      <?php if (!empty($rawImg)): ?>
      <img id="blog-article-image" src="<?php echo htmlspecialchars($resolvedImg); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" onerror="this.style.display='none';" />
      <?php endif; ?>
    </div>
  </div>
  
  <div class="section blog-article-section">
    <div class="article-layout-wrapper">
        
        <!-- Main body text pane (Flows like a luxury blog article) -->
        <div class="article-main-body">
            
            <div id="blog-article-content">
              <!-- Lead / Summary Excerpt (Styled with blog drop cap) -->
              <p><?php echo htmlspecialchars($exp['description']); ?></p>

              <!-- 01. Research Objective -->
              <?php if (!empty($exp['hypothesis'])): ?>
                <h2 id="research-objective">The Research Objective</h2>
                <?php echo render_exp_content($exp['hypothesis']); ?>
              <?php endif; ?>

              <!-- 02. Observations & Discoveries -->
              <?php if (!empty($exp['findings'])): ?>
                <h2 id="discoveries-observations">What We Discovered &amp; Sensory Notes</h2>
                <?php echo render_exp_content($exp['findings']); ?>
              <?php endif; ?>

              <!-- 03. Formulation Methodology & Impact -->
              <?php if (!empty($exp['methodology'])): ?>
                <h2 id="culinary-significance">Why This Matters &amp; Formulation Methodology</h2>
                <?php echo render_exp_content($exp['methodology']); ?>
              <?php endif; ?>

              <!-- 04. Key Takeaways -->
              <?php if (!empty($exp['next_steps'])): ?>
                <h2 id="key-takeaways">Key Recipe Takeaways &amp; Next Steps</h2>
                <?php echo render_exp_content($exp['next_steps']); ?>
              <?php endif; ?>

              <!-- Optional In-Depth Kitchen Notes & Tasting Log -->
              <?php if (!empty($exp['full_content']) && trim($exp['full_content'])): ?>
                <h2 id="detailed-recipe-notes">Detailed Kitchen Notes &amp; Formulation Log</h2>
                <?php echo render_exp_content($exp['full_content']); ?>
              <?php endif; ?>
            </div>
            
            <!-- Social Share Bar (Exact blog share container) -->
            <?php 
                $articleUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'rtchocos.com') . ($_SERVER['REQUEST_URI'] ?? '');
                $shareTitle = $exp['title'] . ' | RT Chocos Innovation Lab';
            ?>
            <div class="share-container">
                <span class="share-title">Share Dossier:</span>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($articleUrl); ?>&text=<?php echo urlencode($shareTitle); ?>" target="_blank" class="share-btn tw" title="Share on Twitter">Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn fb" title="Share on Facebook">Facebook</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn ln" title="Share on LinkedIn">LinkedIn</a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareTitle . ' - ' . $articleUrl); ?>" target="_blank" class="share-btn wa" title="Share via WhatsApp">WhatsApp</a>
                <button type="button" class="share-btn copy" onclick="copyArticleLink()" title="Copy Link">Copy Link</button>
            </div>

            <!-- Author / Lab Kitchen Bio Box (E-E-A-T) -->
            <div style="background:var(--cream, #FAF7F2); border-radius:20px; padding:24px; margin-top:40px; margin-bottom:24px; box-shadow:0 4px 16px rgba(59,42,34,0.06); display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                <img src="<?php echo $pathPrefix; ?>assets/myphoto.jpg" alt="Aarti Saluja Sahni — founder and expert chocolate educator at RT Chocos India" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid var(--brown-light, #5C4033); flex-shrink:0;">
                <div style="flex:1; min-width:240px;">
                    <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--gold, #C7A66A); letter-spacing:0.08em; display:block; margin-bottom:4px;">Research &amp; Formulation</span>
                    <h4 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700; color:var(--brown, #3B2A22); margin:0 0 8px;">Aarti Saluja Sahni &amp; The RT Chocos Kitchen</h4>
                    <p style="font-family:var(--font-sans, sans-serif); font-size:13.5px; line-height:1.6; color:var(--brown-light, #5C4033); font-weight:300; margin:0 0 12px;">
                        Certified chocolate educator and formulation researcher testing bean-to-bar boundaries, tropical fat structures, and indigenous Indian botanical pairings.
                    </p>
                    <a href="<?php echo $pathPrefix; ?>innovation-lab.php" class="btn-outline" style="text-decoration:none; padding:6px 14px; font-size:12px; display:inline-block;">Explore Innovation Lab</a>
                </div>
            </div>

        </div>

        <!-- Sticky Sidebar Panel (On this page TOC & Callout — Specimen passport 4 boxes removed per user request) -->
        <aside class="article-sidebar" id="tocSidebar">
            <div class="toc-box">
                <div class="toc-title">On this page</div>
                <ul class="toc-list" id="tocList">
                    <?php if (!empty($exp['hypothesis'])): ?>
                        <li class="toc-item"><a href="#research-objective">The Research Objective</a></li>
                    <?php endif; ?>
                    <?php if (!empty($exp['findings'])): ?>
                        <li class="toc-item"><a href="#discoveries-observations">Discoveries &amp; Notes</a></li>
                    <?php endif; ?>
                    <?php if (!empty($exp['methodology'])): ?>
                        <li class="toc-item"><a href="#culinary-significance">Why This Matters</a></li>
                    <?php endif; ?>
                    <?php if (!empty($exp['next_steps'])): ?>
                        <li class="toc-item"><a href="#key-takeaways">Key Takeaways</a></li>
                    <?php endif; ?>
                    <?php if (!empty($exp['full_content']) && trim($exp['full_content'])): ?>
                        <li class="toc-item"><a href="#detailed-recipe-notes">Detailed Kitchen Notes</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Callout Card -->
            <div class="exp-sidebar-callout">
                <h4>Curious About Our Chocolate?</h4>
                <p>Every chocolate bar we craft starts with experiments right here in our Innovation Lab. Taste the outcome of our culinary research.</p>
                <a href="<?php echo $pathPrefix; ?>shop.php">
                    <span>Explore Chocolate Shop</span>
                    <span>→</span>
                </a>
            </div>
        </aside>

    </div>

    <!-- Related Experiments Section (Matching Related Insights from blog-article.php) -->
    <?php if (!empty($relatedExperiments)): ?>
        <div class="related-articles-section" style="margin-top: 60px; border-top: 1px solid var(--cream-dark, #EDE7DB); padding-top: 48px;">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom: 24px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; margin:0; color: var(--brown, #3B2A22); font-weight: 700;">More Experiments From the Lab</h3>
                <a href="<?php echo $pathPrefix; ?>innovation-lab.php#currently-in-lab" style="font-size:12px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold, #C7A66A); text-decoration:none;">View All Experiments &rarr;</a>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(265px, 1fr)); gap: 24px;">
                <?php foreach ($relatedExperiments as $rel): 
                    $relRawImg = $rel['image_path'] ?: 'assets/images/placeholder.jpg';
                    $relImgUrl = (strpos($relRawImg, 'http') === 0 || strpos($relRawImg, '/') === 0 || strpos($relRawImg, '../') === 0) ? $relRawImg : ($pathPrefix . $relRawImg);
                    $relLink = $pathPrefix . "experiment/" . $rel['slug'];
                ?>
                    <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                        <div style="height:180px; overflow:hidden; background:linear-gradient(145deg, var(--cream, #FAF7F2) 0%, var(--cream-dark, #EDE7DB) 100%); display:flex; align-items:center; justify-content:center;">
                            <img src="<?php echo htmlspecialchars($relImgUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" onerror="this.src='<?php echo $pathPrefix; ?>assets/images/placeholder.jpg';">
                        </div>
                        <div style="padding: 18px; display: flex; flex-direction: column; flex: 1;">
                            <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--gold, #C7A66A); margin-bottom: 6px;"><?php echo htmlspecialchars($rel['category']); ?></span>
                            <h4 style="font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 700; color: var(--brown, #3B2A22); margin: 0 0 8px; line-height: 1.35;"><?php echo htmlspecialchars($rel['title']); ?></h4>
                            <p style="font-family: var(--font-sans, sans-serif); font-size: 12.5px; color: var(--brown-light, #5C4033); line-height: 1.5; margin: 0 0 14px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($rel['description']); ?>
                            </p>
                            <span style="font-size: 11px; font-weight: 700; color: var(--gold, #C7A66A); text-transform: uppercase; letter-spacing: 0.5px;">Read Dossier &rarr;</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

  </div>
</div>

<script>
// Scroll Progress and TOC Scroll Spy Listeners (Matching blog-article.php)
document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('blog-article-content');
    const tocList = document.getElementById('tocList');
    const tocSidebar = document.getElementById('tocSidebar');
    const progressBar = document.getElementById('readingProgressBar');
    
    if (!content) return;

    // 1. DYNAMIC TABLE OF CONTENTS SCROLL SPY
    const headings = content.querySelectorAll('h2, h3');
    if (headings.length > 0 && tocList && tocSidebar) {
        window.addEventListener('scroll', () => {
            const scrollPos = window.scrollY + 140;
            let activeId = '';
            
            headings.forEach(heading => {
                if (heading.offsetTop <= scrollPos) {
                    activeId = heading.id;
                }
            });
            
            const tocItems = tocList.querySelectorAll('.toc-item');
            tocItems.forEach(item => {
                const link = item.querySelector('a');
                if (link && link.getAttribute('href') === '#' + activeId) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });
    }

    // 2. SCROLL PROGRESS INDICATOR
    window.addEventListener('scroll', () => {
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        if (docHeight > 0 && progressBar) {
            const progress = (window.scrollY / docHeight) * 100;
            progressBar.style.width = progress + '%';
        }
    });
});

// Copy URL link helper
function copyArticleLink() {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(window.location.href)
            .then(() => alert('✓ Experiment link copied to clipboard!'))
            .catch(() => prompt('Copy link:', window.location.href));
    } else {
        prompt('Copy link:', window.location.href);
    }
}
</script>

<?php
include 'includes/footer.php';
?>
