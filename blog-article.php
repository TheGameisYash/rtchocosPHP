<?php
require_once __DIR__ . '/includes/blog-data.php';
require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/blog-cache.php';

$post = null;
$markdown_content = '';
$isFromDb = false;

// Try to fetch from DB first
if (isset($articleKey)) {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND is_published = 1");
        $stmt->execute([$articleKey]);
        $dbPost = $stmt->fetch();
        if ($dbPost) {
            $post = [
                'title' => $dbPost['title'],
                'category' => $dbPost['category'],
                'date' => date('M Y', strtotime($dbPost['created_at'])),
                'read' => $dbPost['read_time'] ?: '5 min',
                'excerpt' => $dbPost['excerpt'],
                'image' => $dbPost['image_path'],
                'thumbnail' => $dbPost['thumbnail_path'] ?: $dbPost['image_path'],
                'bodyClass' => $dbPost['body_class'] ?: '',
                'youtube_url' => $dbPost['youtube_url']
            ];
            $markdown_content = $dbPost['content'];
            $isFromDb = true;

            // Cache article data for offline resilience
            cache_blog_article($articleKey, [
                'post' => $post,
                'markdown_content' => $markdown_content
            ]);

            // Increment article views (Analytics)
            try {
                $upStmt = $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
                $upStmt->execute([$dbPost['id']]);
            } catch (Exception $ex) {
                // Non-blocking
            }
        } else {
            // Check cache fallback
            $cached = get_cached_blog_article($articleKey);
            if ($cached) {
                $post = $cached['post'];
                $markdown_content = $cached['markdown_content'];
                $isFromDb = true;
            }
        }
    } catch (Exception $e) {
        error_log("Database error fetching blog '$articleKey': " . $e->getMessage() . ". Checking cache fallback.");
        $cached = get_cached_blog_article($articleKey);
        if ($cached) {
            $post = $cached['post'];
            $markdown_content = $cached['markdown_content'];
            $isFromDb = true;
        }
    }
}

// Fallback to static blog data array
if (!$post) {
    if (!isset($articleKey) || !isset($BLOGS[$articleKey])) {
        http_response_code(404);
        $pathPrefix = "../";
        include __DIR__ . '/error.php';
        exit;
    }
    $post = $BLOGS[$articleKey];
}

$pageTitle = $post['title'] . " | RT Chocos — India's Chocolate Blog";
$pageDescription = $post['excerpt'];
$pageImage = $post['image'];
$pageType = 'article';
$bodyClass = $post['bodyClass'] ?? '';
$pathPrefix = "../";

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$canonicalUrl = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Blog', 'item' => 'https://www.rtchocos.com/blog.php'],
    ['name' => $post['title'], 'item' => $canonicalUrl]
];

$pageKeywords = htmlspecialchars($post['title']) . ", " . htmlspecialchars($post['category']) . ", India chocolate blog, craft chocolate, cocoa science, bean to bar chocolate";

// Custom markdown parsing function
function parse_markdown($markdown) {
    global $pathPrefix;
    $markdown = str_replace(array("\r\n", "\r"), "\n", $markdown);
    $blocks = explode("\n\n", $markdown);
    $html = '';
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        // Divider
        if ($block === '---') {
            $html .= "<hr class=\"block-divider\">\n";
            continue;
        }

        // Image block: ![caption](url)
        if (preg_match('/^!\[(.*?)\]\((.*?)\)$/', $block, $matches)) {
            $caption = parse_inline($matches[1]);
            $url = $matches[2];
            $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0) 
                ? $url 
                : $pathPrefix . $url;
            $html .= "<div class=\"article-image\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars($caption) . "\">" . (!empty($caption) ? "<span class=\"article-image-caption\">{$caption}</span>" : "") . "</div>\n";
            continue;
        }

        // YouTube embed: [youtube](url)
        if (preg_match('/^\[youtube\]\((.*?)\)$/', $block, $matches)) {
            $url = $matches[1];
            $ytId = '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $ytMatches)) {
                $ytId = $ytMatches[1];
            }
            if ($ytId) {
                $html .= "<div class=\"blog-yt-embed\"><iframe src=\"https://www.youtube.com/embed/{$ytId}\" frameborder=\"0\" allowfullscreen allow=";
                $html .= '"accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"';
                $html .= "></iframe></div>\n";
            }
            continue;
        }
        
        // Fenced Code Block
        if (strpos($block, '```') === 0) {
            $lines = explode("\n", $block);
            $firstLine = array_shift($lines);
            $lastLine = array_pop($lines);
            $lang = trim(str_replace('```', '', $firstLine));
            $code = implode("\n", $lines);
            $classAttr = !empty($lang) ? " class=\"language-" . htmlspecialchars($lang) . "\"" : "";
            $html .= "<pre><code{$classAttr}>" . htmlspecialchars($code) . "</code></pre>\n";
            continue;
        }

        // Tables support
        if (strpos($block, '|') === 0) {
            $lines = explode("\n", $block);
            if (count($lines) >= 2) {
                $tableHtml = "<div class=\"table-responsive\"><table>\n";
                $hasHeader = false;
                foreach ($lines as $line) {
                    $trimmedLine = trim($line, "| ");
                    if (empty($trimmedLine) || preg_match('/^[:\-\s|]+$/', $trimmedLine)) {
                        continue;
                    }
                    $cols = explode('|', $trimmedLine);
                    $rowHtml = "  <tr>\n";
                    foreach ($cols as $col) {
                        $colVal = parse_inline(trim($col));
                        $cellTag = !$hasHeader ? 'th' : 'td';
                        $rowHtml .= "    <{$cellTag}>{$colVal}</{$cellTag}>\n";
                    }
                    $rowHtml .= "  </tr>\n";
                    if (!$hasHeader) {
                        $tableHtml .= "<thead>\n" . $rowHtml . "</thead>\n<tbody>\n";
                        $hasHeader = true;
                    } else {
                        $tableHtml .= $rowHtml;
                    }
                }
                if ($hasHeader) {
                    $tableHtml .= "</tbody>\n";
                }
                $tableHtml .= "</table></div>\n";
                $html .= $tableHtml;
                continue;
            }
        }
        
        // Headers
        if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $matches)) {
            $level = strlen($matches[1]);
            $content = parse_inline($matches[2]);
            $cleanText = strip_tags($content);
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cleanText), '-'));
            
            if ($level === 2 || $level === 3) {
                global $headings_list;
                $headings_list[] = [
                    'level' => $level,
                    'text' => $cleanText,
                    'slug' => $slug
                ];
                $html .= "<h{$level} id=\"{$slug}\">{$content}</h{$level}>\n";
            } else {
                $html .= "<h{$level}>{$content}</h{$level}>\n";
            }
            continue;
        }
        
        // Blockquotes
        if (strpos($block, '> ') === 0) {
            $lines = explode("\n", $block);
            $quoteContent = '';
            foreach ($lines as $line) {
                $quoteContent .= substr($line, 2) . "\n";
            }
            
            $content = trim($quoteContent);
            if (strpos($content, '[!NOTE]') === 0 || strpos($content, '[!TIP]') === 0 || strpos($content, '[!WARNING]') === 0) {
                $clean = preg_replace('/\[!(NOTE|TIP|WARNING)\]/i', '', $content);
                $html .= "<div class='article-callout'>" . parse_inline(trim($clean)) . "</div>\n";
            } else {
                $html .= "<blockquote><p>" . parse_inline($content) . "</p></blockquote>\n";
            }
            continue;
        }
        
        // Bullet Lists
        if (preg_match('/^[\*\-]\s+(.+)$/m', $block)) {
            $lines = explode("\n", $block);
            $listHtml = "<ul>\n";
            foreach ($lines as $line) {
                if (preg_match('/^[\*\-]\s+(.+)$/', trim($line), $matches)) {
                    $content = parse_inline($matches[1]);
                    $listHtml .= "  <li>{$content}</li>\n";
                }
            }
            $listHtml .= "</ul>\n";
            $html .= $listHtml;
            continue;
        }

        // Ordered Lists
        if (preg_match('/^\d+\.\s+(.+)$/m', $block)) {
            $lines = explode("\n", $block);
            $listHtml = "<ol>\n";
            foreach ($lines as $line) {
                if (preg_match('/^\d+\.\s+(.+)$/', trim($line), $matches)) {
                    $content = parse_inline($matches[1]);
                    $listHtml .= "  <li>{$content}</li>\n";
                }
            }
            $listHtml .= "</ol>\n";
            $html .= $listHtml;
            continue;
        }
        
        // HTML blocks (like div, img, hr, iframe, table)
        if (preg_match('/^<(div|img|hr|p|section|a|span|h\d|table|tr|td|th|iframe)/i', $block)) {
            $html .= $block . "\n";
            continue;
        }
        
        // Default to paragraph
        $content = parse_inline($block);
        $html .= "<p>{$content}</p>\n";
    }
    
    return $html;
}

function parse_inline($text) {
    global $pathPrefix;
    // Bold: **text**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italic: *text* or _text_
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
    // Inline code: `code`
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Inline images with optional position matching: ![alt](url){position}
    $text = preg_replace_callback('/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|end)\})?/', function($matches) use ($pathPrefix) {
        $caption = $matches[1];
        $url = $matches[2];
        $pos = !empty($matches[3]) ? $matches[3] : 'center';
        $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0) 
            ? $url 
            : $pathPrefix . $url;
        return "<span class=\"blog-img-container blog-img-{$pos}\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars($caption) . "\" class=\"blog-img-{$pos}\" loading=\"lazy\" decoding=\"async\"></span>";
    }, $text);
    // Links: [text](href)
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    return $text;
}

// Load markdown content from file if not loaded from DB
if (!$isFromDb) {
    $markdown_file = __DIR__ . '/blog/posts/' . $articleKey . '.md';
    $markdown_content = file_exists($markdown_file) ? file_get_contents($markdown_file) : '';
}

// Fetch related articles (limit 3)
$relatedArticles = [];
try {
    $currentId = $isFromDb ? (int)$dbPost['id'] : 0;
    $stmt = $pdo->prepare("SELECT id, title, slug, category, excerpt, thumbnail_path, created_at FROM blogs WHERE category = ? AND id != ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3");
    $stmt->execute([$post['category'], $currentId]);
    $relatedArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fallback: If less than 2 category-matched items, grab any recent ones
    if (count($relatedArticles) < 2) {
        $stmt = $pdo->prepare("SELECT id, title, slug, category, excerpt, thumbnail_path, created_at FROM blogs WHERE id != ? AND is_published = 1 ORDER BY created_at DESC LIMIT 3");
        $stmt->execute([$currentId]);
        $relatedArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Non-blocking
}

include __DIR__ . '/includes/header.php';
?>

<!-- Reading Scroll Progress Bar -->
<div id="readingProgressBar" style="position:fixed; top:0; left:0; width:0; height:4px; background:var(--gold, #C7A66A); z-index:99999; transition:width 0.1s ease;"></div>

<style>
    /* Premium style overrides for public article pages */
    .article-layout-wrapper {
        display: flex;
        gap: 48px;
        align-items: start;
        position: relative;
    }
    .article-main-body {
        flex: 1;
        min-width: 0;
    }
    .article-sidebar {
        width: 260px;
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
    }
    .toc-title {
        font-family: 'Cormorant Garamond', serif;
        font-size: 18px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--brown);
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
    .toc-item.indent-h3 {
        padding-left: 14px;
        font-size: 12.5px;
        opacity: 0.85;
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
        border-left: 2px solid var(--gold);
        padding-left: 6px;
        margin-left: -8px;
    }
    .toc-item.indent-h3.active {
        margin-left: 6px;
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
        color: var(--brown-light);
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
    .share-btn.copy { background: var(--cream-dark); color: var(--brown); }

    /* Lightbox Modal */
    #lightboxModal {
        display: none;
        position: fixed;
        left: 0; top: 0;
        width: 100vw; height: 100vh;
        background: rgba(13, 9, 7, 0.95);
        z-index: 999999;
        align-items: center;
        justify-content: center;
        cursor: zoom-out;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    #lightboxModal.show {
        display: flex;
        opacity: 1;
    }
    #lightboxImg {
        max-width: 90%;
        max-height: 85vh;
        border-radius: 6px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        transform: scale(0.95);
        transition: transform 0.3s ease;
    }
    #lightboxModal.show #lightboxImg {
        transform: scale(1);
    }

    /* Related articles grid */
    .related-card {
        background: #FEFDFB;
        border: 1px solid var(--cream-dark);
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
    
    /* Article callout style */
    .article-callout {
        background: rgba(199, 166, 106, 0.05);
        border-left: 4px solid var(--gold);
        padding: 16px 20px;
        border-radius: 0 8px 8px 0;
        margin: 24px 0;
        font-style: italic;
    }

    /* Print styling rules */
    @media print {
        header, footer, .article-sidebar, .share-container, .related-articles-section, #comments-section, .back-to-blog-btn {
            display: none !important;
        }
        body, #page-blog-article, .blog-article-section {
            background: white !important;
            color: black !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .article-main-body {
            width: 100% !important;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .article-sidebar {
            display: none !important;
        }
    }

    /* Divider block */
    .block-divider {
        border: 0;
        height: 1px;
        background: var(--cream-dark);
        margin: 40px 0;
    }

    /* Block image block */
    .article-image {
        margin: 36px 0;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .article-image img {
        max-width: 100%;
        border-radius: 12px;
        border: 1px solid var(--cream-dark);
        box-shadow: var(--shadow-sm);
        cursor: zoom-in;
        transition: transform 0.3s ease;
    }
    .article-image img:hover {
        transform: scale(1.01);
    }
    .article-image-caption {
        font-size: 13px;
        color: #8E7A70;
        font-style: italic;
    }

    /* YouTube iframe wrapper */
    .article-youtube-embed {
        margin: 36px 0;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-md);
    }
    .yt-iframe-wrap {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
    }
    .yt-iframe-wrap iframe {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        border: 0;
    }
</style>

<!-- --- BLOG ARTICLE SECTION --- -->
<div id="page-blog-article" class="page active">
  <div class="page-hero blog-page-hero">
    <div class="page-hero-content blog-article-hero-content">
      <a class="btn-outline-dark back-to-blog-btn" href="../blog.php">&larr; Back to Blog</a>
      
      <!-- Visual Breadcrumbs Trail -->
      <div class="blog-breadcrumbs" style="font-size: 13px; color: var(--gold); margin: 16px 0 8px; font-family: var(--font-sans);">
        <a href="../index.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Home</a>
        <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
        <a href="../blog.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Blog</a>
        <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
        <span style="opacity: 0.7; font-weight: 500;"><?php echo htmlspecialchars($post['title']); ?></span>
      </div>

      <div class="section-label" id="blog-article-category"><?php echo htmlspecialchars($post['category']); ?></div>
      <h1 id="blog-article-title" class="fade-up"><?php echo htmlspecialchars($post['title']); ?></h1>
      <p id="blog-article-meta" class="fade-up-d1"><?php echo htmlspecialchars($post['date']); ?> • <?php echo htmlspecialchars($post['read']); ?> read</p>
      <?php if (!empty($post['image'])): ?>
      <img id="blog-article-image" src="../<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
      <?php endif; ?>
    </div>
  </div>
  
  <div class="section blog-article-section">
    <div class="article-layout-wrapper">
        <!-- Main body text pane -->
        <div class="article-main-body">
            <!-- YouTube Video Embed -->
            <?php if (!empty($post['youtube_url'])): ?>
                <?php 
                    $ytUrl = $post['youtube_url'];
                    $videoId = null;
                    $regExp = '/^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/';
                    if (preg_match($regExp, $ytUrl, $matches)) {
                        if (isset($matches[2]) && strlen($matches[2]) === 11) {
                            $videoId = $matches[2];
                        }
                    }
                ?>
                <?php if ($videoId): ?>
                    <div class="blog-video-embed" style="margin-bottom: 32px; border-radius:12px; overflow:hidden; position:relative; padding-bottom:56.25%; height:0; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
                        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" 
                                frameborder="0" allowfullscreen
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;">
                        </iframe>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Article Body -->
            <div id="blog-article-content">
              <?php echo parse_markdown($markdown_content); ?>
            </div>
            
            <!-- Social Share Bar -->
            <?php 
                $articleUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'rtchocos.com') . ($_SERVER['REQUEST_URI'] ?? '');
                $shareTitle = $post['title'];
            ?>
            <div class="share-container">
                <span class="share-title">Share Article:</span>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($articleUrl); ?>&text=<?php echo urlencode($shareTitle); ?>" target="_blank" class="share-btn tw" title="Share on Twitter">Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn fb" title="Share on Facebook">Facebook</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn ln" title="Share on LinkedIn">LinkedIn</a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareTitle . ' - ' . $articleUrl); ?>" target="_blank" class="share-btn wa" title="Share via WhatsApp">WhatsApp</a>
                <button type="button" class="share-btn copy" onclick="copyArticleLink()" title="Copy Link">Copy Link</button>
            </div>
            
            <!-- Comments Section -->
            <?php include __DIR__ . '/includes/comments.php'; ?>
        </div>

        <!-- Sticky Sidebar Panel (TOC - Server-side Rendered) -->
        <?php if (!empty($headings_list)): ?>
            <aside class="article-sidebar" id="tocSidebar">
                <div class="toc-box">
                    <div class="toc-title">On this page</div>
                    <ul class="toc-list" id="tocList">
                        <?php foreach ($headings_list as $h): ?>
                            <li class="toc-item <?php echo $h['level'] === 3 ? 'indent-h3' : ''; ?>">
                                <a href="#<?php echo $h['slug']; ?>"><?php echo htmlspecialchars($h['text']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </aside>
        <?php endif; ?>
    </div>

    <!-- Related Articles Section (Bottom Grid) -->
    <?php if (!empty($relatedArticles)): ?>
        <div class="related-articles-section" style="margin-top: 60px; border-top: 1px solid var(--cream-dark); padding-top: 48px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; margin-bottom: 24px; color: var(--brown); font-weight: 700;">Related Insights</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(265px, 1fr)); gap: 24px;">
                <?php foreach ($relatedArticles as $rel): 
                    $relThumb = $rel['thumbnail_path'] ?: 'assets/images/placeholder.jpg';
                    $relThumbUrl = $pathPrefix . $relThumb;
                    $relLink = $pathPrefix . "blog/" . $rel['slug'];
                ?>
                    <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                        <div style="height:150px; overflow:hidden; background:var(--cream);">
                            <img src="<?php echo htmlspecialchars($relThumbUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy">
                        </div>
                        <div style="padding: 20px; display:flex; flex-direction:column; flex-grow:1;">
                            <span style="font-size:10px; font-weight:600; text-transform:uppercase; color:var(--gold); margin-bottom:6px;"><?php echo htmlspecialchars($rel['category']); ?></span>
                            <h4 style="font-size:15px; font-weight:600; color:var(--brown); margin-bottom:8px; line-height:1.4;"><?php echo htmlspecialchars($rel['title']); ?></h4>
                            <p style="font-size:13px; color:var(--brown-light); line-height:1.5; margin-bottom:0; display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($rel['excerpt']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
  </div>
</div>

<!-- Image Lightbox Modal -->
<div id="lightboxModal" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="Lightbox Preview">
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>

<script>
// Initialize comments for this article page.
(function() {
  var articleKey = <?php echo json_encode($articleKey ?? ''); ?>;
  if (articleKey) {
    currentBlogArticleId = articleKey;
    renderBlogComments();
  }
})();

// Scroll Progress, TOC scanner, and Lightbox listeners
document.addEventListener('DOMContentLoaded', function() {
    const content = document.getElementById('blog-article-content');
    const tocList = document.getElementById('tocList');
    const tocSidebar = document.getElementById('tocSidebar');
    const progressBar = document.getElementById('readingProgressBar');
    
    if (!content) return;

    // 1. DYNAMIC TABLE OF CONTENTS SCROLL SPY
    const headings = content.querySelectorAll('h2, h3');
    if (headings.length > 0 && tocList && tocSidebar) {
        // Scroll spy trigger
        window.addEventListener('scroll', () => {
            const scrollPos = window.scrollY + 120;
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

    // 3. IMAGE LIGHTBOX INITIALIZER
    const articleImages = content.querySelectorAll('img');
    const lightbox = document.getElementById('lightboxModal');
    const lightboxImg = document.getElementById('lightboxImg');
    
    articleImages.forEach(img => {
        img.style.cursor = 'zoom-in';
        img.addEventListener('click', (e) => {
            e.stopPropagation();
            lightboxImg.src = img.src;
            lightbox.classList.add('show');
        });
    });
});

function closeLightbox() {
    const lightbox = document.getElementById('lightboxModal');
    if (lightbox) {
        lightbox.classList.remove('show');
    }
}

// Copy URL link helper
function copyArticleLink() {
    navigator.clipboard.writeText(window.location.href)
        .then(() => alert('Article link copied to clipboard!'))
        .catch(() => alert('Failed to copy link.'));
}
</script>
