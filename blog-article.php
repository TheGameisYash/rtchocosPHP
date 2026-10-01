<?php
require_once __DIR__ . '/includes/blog-data.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/blog-cache.php';
require_once __DIR__ . '/admin/auth.php';

$articleKey = $articleKey ?? ($_GET['slug'] ?? ($_GET['article'] ?? null));

$post = null;
$markdown_content = '';
$isFromDb = false;
$isAdminPreview = false;

// Try to fetch from cache first for fast response, unless admin is logged in previewing drafts
if (isset($articleKey)) {
    $isAdmin = function_exists('is_admin_logged_in') && is_admin_logged_in();
    $cached = !$isAdmin ? get_cached_blog_article($articleKey, 1800) : null;

    if ($cached && !empty($cached['post'])) {
        if (isset($cached['post']['is_published']) && (int)$cached['post']['is_published'] === 0) {
            if ($isAdmin) {
                $isAdminPreview = true;
                $post = $cached['post'];
                $markdown_content = $cached['markdown_content'] ?? '';
                $isFromDb = true;
            } else {
                http_response_code(404);
                $pathPrefix = "../";
                include __DIR__ . '/error.php';
                exit;
            }
        } else {
            $post = $cached['post'];
            $markdown_content = $cached['markdown_content'] ?? '';
            $isFromDb = true;
        }
    } else {
        try {
            $pdo = get_db();
            $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ?");
            $stmt->execute([$articleKey]);
            $dbPost = $stmt->fetch();
            if ($dbPost) {
                // Check visibility / publish state
                if ((int)$dbPost['is_published'] === 0) {
                    if ($isAdmin) {
                        $isAdminPreview = true;
                    } else {
                        // Hidden post requested by public user -> 404 immediately
                        http_response_code(404);
                        $pathPrefix = "../";
                        include __DIR__ . '/error.php';
                        exit;
                    }
                }

                $post = [
                    'id' => (int)$dbPost['id'],
                    'title' => $dbPost['title'],
                    'category' => $dbPost['category'],
                    'date' => date('M Y', strtotime($dbPost['created_at'])),
                    'read' => $dbPost['read_time'] ?: '5 min',
                    'excerpt' => $dbPost['excerpt'],
                    'published' => $dbPost['created_at'],
                    'modified' => $dbPost['updated_at'],
                    'image' => $dbPost['image_path'],
                    'thumbnail' => $dbPost['thumbnail_path'] ?: $dbPost['image_path'],
                    'bodyClass' => $dbPost['body_class'] ?: '',
                    'youtube_url' => $dbPost['youtube_url'],
                    'is_published' => (int)$dbPost['is_published']
                ];
                $markdown_content = $dbPost['content'];
                $isFromDb = true;

                // Cache article if published
                if ((int)$dbPost['is_published'] === 1) {
                    cache_blog_article($articleKey, [
                        'post' => $post,
                        'markdown_content' => $markdown_content
                    ]);
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

    // Increment article views (Analytics) - only once per visitor session to eliminate DB row locking
    if (!empty($post['id']) && empty($_SESSION['viewed_article_' . $post['id']]) && empty($isAdminPreview)) {
        $_SESSION['viewed_article_' . $post['id']] = true;
        try {
            $pdo = get_db();
            $upStmt = $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
            $upStmt->execute([$post['id']]);
        } catch (Exception $ex) {
            // Non-blocking
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

$pageTitle = $post['title'] . " | RT Chocos";
$pageDescription = $post['excerpt'];
$pageImage = $post['image'];
$pageType = 'article';
$bodyClass = $post['bodyClass'] ?? '';
$pathPrefix = "../";

$canonicalUrl = "https://www.rtchocos.com/blog/" . rawurlencode($articleKey);

$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Blog', 'item' => 'https://www.rtchocos.com/blog.php'],
    ['name' => $post['title'], 'item' => $canonicalUrl]
];

$pageKeywords = htmlspecialchars($post['title']) . ", " . htmlspecialchars($post['category']) . ", India chocolate blog, craft chocolate, cocoa science, bean to bar chocolate";

// Custom markdown parsing function
if (!function_exists('parse_markdown')) {
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
            $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0 || strpos($url, '../') === 0) 
                ? $url 
                : $pathPrefix . $url;
            $html .= "<figure class=\"blog-figure blog-figure-center\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars(strip_tags($caption)) . "\" loading=\"lazy\">" . (!empty($caption) ? "<figcaption>{$caption}</figcaption>" : "") . "</figure>\n";
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
                $tableHtml = "<div class=\"table-responsive-wrapper\"><table class=\"blog-custom-table blog-table-artisan\">\n";
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
            if ($level === 1) $level = 2; // Demote H1 to H2 so article has only 1 H1 (post title)
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

        // Paragraph blocks containing markdown heading like <p># Title</p> or <p>## Heading</p> or <p>### Heading</p>
        if (preg_match('/^<p>\s*(#{1,6})\s+(.*?)\s*<\/p>$/is', $block, $pHeadingMatch)) {
            $level = strlen($pHeadingMatch[1]);
            if ($level === 1) $level = 2; // Demote H1 to H2
            $level = min(4, max(2, $level));
            $content = parse_inline($pHeadingMatch[2]);
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

        // Paragraph blocks containing markdown bullet list like <p>* item<br>* item</p>
        if (preg_match('/^<p>\s*([*•\-]\s+.*?)\s*<\/p>$/is', $block, $pListMatch)) {
            $rawList = preg_replace('/<br\s*\/?>/i', "\n", $pListMatch[1]);
            $lines = explode("\n", $rawList);
            if (count($lines) > 0 && preg_match('/^[*•\-]\s+/', trim($lines[0]))) {
                $listHtml = "<ul>\n";
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (preg_match('/^[*•\-]\s+(.+)$/', $trimmedLine, $lMatches)) {
                        $listHtml .= "  <li>" . parse_inline($lMatches[1]) . "</li>\n";
                    } elseif (!empty($trimmedLine)) {
                        $listHtml .= "  <li>" . parse_inline($trimmedLine) . "</li>\n";
                    }
                }
                $listHtml .= "</ul>\n";
                $html .= $listHtml;
                continue;
            }
        }
        
        // HTML blocks (like div, img, hr, p, section, a, span, h\d, table, tr, td, th, iframe, blockquote, ul, ol, pre, figure)
        if (preg_match('/^<h([1-4])(?:\s+id="([^"]+)")?[^>]*>(.*?)<\/h\1>/i', $block, $hMatches)) {
            $hLevel = (int)$hMatches[1];
            if ($hLevel === 1) $hLevel = 2; // Demote H1 to H2 so article has only 1 H1 (post title)
            $hClean = strip_tags($hMatches[3]);
            $hSlug = !empty($hMatches[2]) ? $hMatches[2] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $hClean), '-'));
            global $headings_list;
            $headings_list[] = [
                'level' => $hLevel,
                'text' => $hClean,
                'slug' => $hSlug
            ];
            $html .= "<h{$hLevel} id=\"{$hSlug}\">{$hMatches[3]}</h{$hLevel}>\n";
            continue;
        }

        if (preg_match('/^<(div|img|hr|p|section|a|span|h\d|table|thead|tbody|tr|td|th|iframe|blockquote|ul|ol|pre|figure)/i', $block)) {
            $html .= $block . "\n";
            continue;
        }
        
        // Default to paragraph
        $content = parse_inline($block);
        $html .= "<p>{$content}</p>\n";    } // end foreach
    
    return $html;
}
} // end function_exists check

if (!function_exists('parse_inline')) {
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
        $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0 || strpos($url, '../') === 0)
            ? $url 
            : $pathPrefix . $url;
        return "<span class=\"blog-img-container blog-img-{$pos}\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars($caption) . "\" class=\"blog-img-{$pos}\" loading=\"lazy\" decoding=\"async\"></span>";
    }, $text);
    // Links: [text](href)
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    return $text;
}
} // end function_exists check

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
    /* ==========================================================================
       LUXURY EDITORIAL TYPOGRAPHY & CONSISTENT ARTICLE STYLES
       ========================================================================== */
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

    #blog-article-content h4 {
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--accent, #235436);
        margin-top: 32px;
        margin-bottom: 14px;
    }

    /* Freedom to set everything: Author-defined custom text & background colors */
    #blog-article-content h1 *:not([style*="color"]):not([color]),
    #blog-article-content h2 *:not([style*="color"]):not([color]),
    #blog-article-content h3 *:not([style*="color"]):not([color]),
    #blog-article-content h4 *:not([style*="color"]):not([color]) {
        font-family: inherit;
        font-size: inherit;
        line-height: inherit;
    }

    #blog-article-content [style*="color"] {
        /* Let author inline style apply directly */
    }

    #blog-article-content font[color] {
        /* Let font color attribute apply */
    }

    #blog-article-content mark,
    #blog-article-content [style*="background-color"] {
        padding: 2px 6px;
        border-radius: 4px;
    }

    #blog-article-content strong,
    #blog-article-content b {
        color: var(--text-heading, #112217);
        font-weight: 600;
    }

    #blog-article-content a {
        color: var(--gold, #C7A66A);
        text-decoration: underline;
        text-underline-offset: 3px;
        transition: color 0.2s ease;
    }

    #blog-article-content a:hover {
        color: var(--accent, #235436);
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

    #blog-article-content hr.block-divider,
    #blog-article-content hr {
        border: none;
        border-top: 1px solid rgba(199, 166, 106, 0.25);
        margin: 40px 0;
    }

    .article-layout-wrapper {
        display: flex;
        gap: 48px;
        align-items: start;
        position: relative;
    }
    .article-main-body {
        flex: 1;
        min-width: 0;
        max-width: 760px; /* Limit text container size for professional typography reading layout */
    }

    /* Clean, responsive inline article figures and images */
    #blog-article-content img,
    .article-main-body img {
        max-width: 100%;
        height: auto;
        display: block;
    }
    #blog-article-content figure.blog-figure {
        margin: 32px auto;
        text-align: center;
        display: block;
        max-width: 100%;
        clear: both;
    }
    #blog-article-content figure.blog-figure img {
        width: 100%;
        max-width: 100%;
        height: auto;
        max-height: 520px;
        object-fit: contain;
        border-radius: 12px;
        border: 1px solid var(--cream-dark, #EDE7DB);
        box-shadow: 0 4px 18px rgba(59, 42, 34, 0.08);
        margin: 0 auto;
        display: block;
        transition: transform 0.3s ease;
    }
    #blog-article-content figure.blog-figure img:hover {
        transform: scale(1.01);
    }
    #blog-article-content figure.blog-figure figcaption {
        font-size: 13.5px;
        color: var(--brown-light, #7D6B62);
        font-style: italic;
        margin-top: 10px;
        line-height: 1.5;
        text-align: center;
    }
    #blog-article-content figure.blog-figure-center {
        margin: 32px auto;
    }
    #blog-article-content figure.blog-figure-left {
        float: left;
        margin: 12px 28px 20px 0;
        max-width: 48%;
    }
    #blog-article-content figure.blog-figure-right {
        float: right;
        margin: 12px 0 20px 28px;
        max-width: 48%;
    }
    #blog-article-content figure.blog-figure-wide {
        width: 100% !important;
        max-width: 100%;
        margin: 40px 0;
    }
    @media (max-width: 768px) {
        #blog-article-content figure.blog-figure,
        #blog-article-content figure.blog-figure-left,
        #blog-article-content figure.blog-figure-right {
            float: none !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 20px auto !important;
        }
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

    /* Admin Preview Mode Banner */
    .admin-preview-banner {
        position: sticky;
        top: 0;
        z-index: 9999;
        background: #1c1511;
        border-bottom: 2px solid var(--gold);
        padding: 10px 24px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    }
    .admin-preview-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .admin-preview-badge {
        background: #eab308;
        color: #000;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 4px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    .admin-preview-text {
        font-size: 13.5px;
        color: #f7ede2;
        flex: 1;
    }
    .admin-preview-edit-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--gold);
        color: #111;
        font-size: 12.5px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .admin-preview-edit-btn:hover {
        background: #d4b57b;
        transform: translateY(-1px);
    }

    /* Production Table Responsive Wrappers & Tables */
    .table-responsive-wrapper {
        width: 100%;
        overflow-x: auto;
        margin: 36px 0;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(59, 42, 34, 0.08);
        -webkit-overflow-scrolling: touch;
    }
    table.blog-custom-table {
        width: 100%;
        border-collapse: collapse;
        font-family: var(--font-sans);
        font-size: 15px;
        line-height: 1.6;
        text-align: left;
    }
    table.blog-custom-table th {
        padding: 14px 18px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
    table.blog-custom-table td {
        padding: 12px 18px;
        border-top: 1px solid rgba(199, 166, 106, 0.15);
    }

    /* Table Styles */
    table.blog-table-artisan {
        background: var(--cream);
        border: 1px solid var(--cream-dark);
    }
    table.blog-table-artisan th {
        background: var(--brown);
        color: var(--gold);
        font-family: 'Cormorant Garamond', serif;
        font-size: 18px;
    }
    table.blog-table-artisan tr:nth-child(even) td {
        background: rgba(255, 255, 255, 0.6);
    }

    table.blog-table-minimal th {
        border-bottom: 2px solid var(--gold);
        color: var(--brown);
        font-weight: 700;
    }
    table.blog-table-minimal td {
        border-bottom: 1px solid var(--cream-dark);
    }

    table.blog-table-striped th {
        background: var(--green-900, #1b4d3e);
        color: #ffffff;
    }
    table.blog-table-striped tr:nth-child(even) td {
        background: rgba(27, 77, 62, 0.04);
    }

    /* Production Figures & Captions */
    figure.blog-figure {
        margin: 36px auto;
        text-align: center;
        display: block;
        max-width: 100%;
    }
    figure.blog-figure img {
        max-width: 100%;
        height: auto;
        border-radius: 12px;
        border: 1px solid var(--cream-dark);
        box-shadow: var(--shadow-sm);
        transition: transform 0.3s ease;
    }
    figure.blog-figure img:hover {
        transform: scale(1.01);
    }
    figure.blog-figure figcaption {
        font-size: 13.5px;
        color: #8E7A70;
        font-style: italic;
        margin-top: 10px;
        line-height: 1.5;
    }

    figure.blog-figure-center {
        margin: 36px auto;
    }
    figure.blog-figure-left {
        float: left;
        margin: 12px 28px 24px 0;
        width: 48%;
        max-width: 100%;
    }
    figure.blog-figure-right {
        float: right;
        margin: 12px 0 24px 28px;
        width: 48%;
        max-width: 100%;
    }
    figure.blog-figure-wide {
        width: 100% !important;
        max-width: 100%;
        margin: 44px 0;
    }
    .article-main-body::after,
    .blog-article-section::after {
        content: "";
        display: table;
        clear: both;
    }

    @media (max-width: 768px) {
        figure.blog-figure-left,
        figure.blog-figure-right {
            float: none !important;
            margin: 28px 0 !important;
            max-width: 100% !important;
        }
    }

    /* Public article content typography & color consistency safeguard */
    #blog-article-content p *,
    #blog-article-content li *,
    #blog-article-content blockquote *,
    #blog-article-content td *,
    #blog-article-content th * {
        font-family: inherit !important;
        font-size: inherit !important;
        line-height: inherit !important;
    }
    #blog-article-content [style*="color: #fff"],
    #blog-article-content [style*="color: #FFF"],
    #blog-article-content [style*="color: #ffffff"],
    #blog-article-content [style*="color: #FFFFFF"],
    #blog-article-content [style*="color: rgb(255, 255, 255)"],
    #blog-article-content [style*="color: rgb(255,255,255)"],
    #blog-article-content [style*="color: white"] {
        color: inherit !important;
    }
    #blog-article-content [style*="background-color: rgb(255, 255, 255)"],
    #blog-article-content [style*="background-color: #fff"],
    #blog-article-content [style*="background-color: #ffffff"],
    #blog-article-content [style*="background-color: white"],
    #blog-article-content [style*="background-color: rgb(0, 0, 0)"],
    #blog-article-content [style*="background-color: #000"] {
        background-color: transparent !important;
    }
    #blog-article-content font {
        color: inherit !important;
        font-family: inherit !important;
        font-size: inherit !important;
    }
</style>

<?php if (!empty($isAdminPreview)): ?>
<div class="admin-preview-banner">
    <div class="admin-preview-content">
        <div style="display:flex; align-items:center; gap:10px; flex:1;">
            <span class="admin-preview-badge">Admin Preview Mode</span>
            <span class="admin-preview-text">This article is currently <strong>HIDDEN (Draft)</strong> from the public. Only logged-in administrators can view this page.</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <button type="button" onclick="publishThisArticleNow(<?php echo (int)($post['id'] ?? 0); ?>)" class="admin-preview-publish-btn" style="background:#15803d; color:#fff; border:none; border-radius:6px; font-weight:700; font-size:12.5px; padding:6px 14px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; transition:background 0.2s;">
                <span>👁️</span> Make Visible (Publish)
            </button>
            <a href="../admin/blog-editor.php?id=<?php echo (int)($post['id'] ?? 0); ?>" class="admin-preview-edit-btn">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Article
            </a>
        </div>
    </div>
</div>
<script>
function publishThisArticleNow(blogId) {
    if (!confirm('Make this article visible to the public now?')) return;
    const form = new FormData();
    form.append('action', 'toggle_publish');
    form.append('blog_id', blogId);
    form.append('is_published', '1');
    form.append('csrf_token', <?php echo json_encode(generate_csrf()); ?>);
    fetch('../admin/blog-editor.php', { method: 'POST', body: form })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Article is now published and visible to the public!');
                location.reload();
            } else {
                alert('Failed to publish: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => alert('Network error: ' + err.message));
}
</script>
<?php endif; ?>

<!-- --- BLOG ARTICLE SECTION --- -->
<div id="page-blog-article" class="page active">
  <div class="page-hero blog-page-hero">
    <div class="page-hero-content blog-article-hero-content">
      <a class="btn-outline-dark back-to-blog-btn" href="../blog.php">&larr; Back to Blog</a>
      
      <div class="article-hero-text-container" style="max-width: 760px; margin: 0 auto;">
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
      </div>

      <?php if (!empty($post['image'])): 
        $heroImg = $post['image'];
        if (strpos($heroImg, 'http://') !== 0 && strpos($heroImg, 'https://') !== 0 && strpos($heroImg, '/') !== 0 && strpos($heroImg, '../') !== 0) {
            $heroImg = $pathPrefix . $heroImg;
        }
      ?>
      <img id="blog-article-image" src="<?php echo htmlspecialchars($heroImg); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
      <div class="blog-article-image-fallback" style="display:none; justify-content:center; align-items:center; width:100%; height:320px; background:linear-gradient(145deg, var(--cream) 0%, var(--cream-dark) 100%); border-radius:12px; margin:24px 0; font-family:'Cormorant Garamond', serif; font-size:28px; font-style:italic; color:var(--brown-light); box-shadow: 0 12px 40px rgba(59, 42, 34, 0.12); border: 1px solid var(--cream-dark);">
          RT Chocos Chocolate Journal
      </div>
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

            <!-- Author Bio Box (E-E-A-T) -->
            <div style="background:var(--cream); border-radius:20px; padding:24px; margin-top:40px; margin-bottom:24px; box-shadow:0 4px 16px rgba(59,42,34,0.06); display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                <img src="<?php echo $pathPrefix; ?>assets/myphoto.jpg" alt="Aarti Saluja Sahni — founder and expert chocolate educator at RT Chocos India" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid var(--brown-light); flex-shrink:0;">
                <div style="flex:1; min-width:240px;">
                    <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--gold); letter-spacing:0.08em; display:block; margin-bottom:4px;">Written By The Founder</span>
                    <h4 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700; color:var(--brown); margin:0 0 8px;">Aarti Saluja Sahni</h4>
                    <p style="font-family:var(--font-sans); font-size:13.5px; line-height:1.6; color:var(--brown-light); font-weight:300; margin:0 0 12px;">
                        Aarti is India's first chocolate blogger and a certified chocolate educator with 10+ years of bean-to-bar and cocoa science expertise. She has trained 2,000+ students and consults for craft chocolate brands across India.
                    </p>
                    <a href="<?php echo $pathPrefix; ?>about" class="btn-outline" style="text-decoration:none; padding:6px 14px; font-size:12px; display:inline-block;">About Aarti</a>
                </div>
            </div>

            <!-- Contextual FAQ Block -->
            <?php
            $faqCategory = 'general';
            if (stripos($post['category'], 'Beginner') !== false || stripos($post['category'], 'Business') !== false) {
                $faqCategory = 'courses';
            }
            $faqLimit = 3;
            include __DIR__ . '/includes/faq-block.php';
            ?>
            
            <!-- Comments Section -->
            <?php include __DIR__ . '/includes/comments.php'; ?>
        </div>

        <!-- Sticky Sidebar Panel (TOC - Server-side Rendered) -->
        <?php 
        global $headings_list;
        if (!empty($headings_list)): ?>
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
                        <div style="height:180px; overflow:hidden; background:linear-gradient(145deg, var(--cream) 0%, var(--cream-dark) 100%); display:flex; align-items:center; justify-content:center;">
                            <img src="<?php echo htmlspecialchars($relThumbUrl); ?>" style="width:100%; height:100%; object-fit:fill;" loading="lazy" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                            <span style="display:none; font-family:'Cormorant Garamond', serif; font-size:18px; color:var(--brown-light); font-style:italic; text-align:center; padding:15px;">Chocolate Journal</span>
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
