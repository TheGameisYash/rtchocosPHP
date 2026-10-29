<?php
// experiment.php — Dedicated Luxury Single Experiment Article for RT Chocos Innovation Lab
// Styled identically to RT Chocos Blog Articles (blog-article.php) with Full Admin Manageability
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

// Full Markdown / HTML Parser (Identical to blog-article.php)
$headings_list = [];

function parse_exp_inline($text) {
    global $pathPrefix;
    if (!$text) return '';
    // Bold: **text**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italic: *text* or _text_
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
    // Inline code: `code`
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    // Inline images with optional position matching: ![alt](url){position}
    $text = preg_replace_callback('/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|wide)\})?/', function($matches) use ($pathPrefix) {
        $caption = $matches[1];
        $url = $matches[2];
        $pos = !empty($matches[3]) ? $matches[3] : 'center';
        $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0 || strpos($url, '../') === 0)
            ? $url 
            : $pathPrefix . $url;
        return "<figure class=\"blog-figure blog-figure-{$pos}\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars($caption) . "\" loading=\"lazy\" decoding=\"async\" onclick=\"openLightbox(this.src)\" style=\"cursor:zoom-in;\">" . ($caption ? "<figcaption>" . htmlspecialchars($caption) . "</figcaption>" : "") . "</figure>";
    }, $text);
    // Links: [text](href)
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    return $text;
}

function parse_exp_markdown($markdown) {
    global $headings_list;
    if (!trim((string)$markdown)) return '';
    $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);
    
    // YouTube embeds: {{youtube:VIDEO_ID}}
    $markdown = preg_replace('/\{\{youtube:([a-zA-Z0-9_\-]+)\}\}/', '<div class="blog-yt-embed" style="margin:36px 0; border-radius:12px; overflow:hidden; position:relative; padding-bottom:56.25%; height:0; box-shadow:0 10px 30px rgba(0,0,0,0.08);"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;"></iframe></div>', $markdown);

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

        // Fenced Code blocks
        if (strpos($block, '```') === 0) {
            $lines = explode("\n", $block);
            $lang = trim(str_replace('```', '', $lines[0]));
            $code = implode("\n", array_slice($lines, 1, count($lines) - 2));
            $classAttr = $lang ? " class=\"language-{$lang}\"" : "";
            $html .= "<pre><code{$classAttr}>" . htmlspecialchars($code) . "</code></pre>\n";
            continue;
        }

        // Heading 1 (demoted to h2 for SEO)
        if (preg_match('/^#\s+(.+)$/', $block, $m)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $m[1]), '-'));
            $headings_list[] = ['level' => 2, 'text' => $m[1], 'slug' => $slug];
            $html .= "<h2 id=\"{$slug}\">" . parse_exp_inline($m[1]) . "</h2>\n";
            continue;
        }

        // Heading 2
        if (preg_match('/^##\s+(.+)$/', $block, $m)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $m[1]), '-'));
            $headings_list[] = ['level' => 2, 'text' => $m[1], 'slug' => $slug];
            $html .= "<h2 id=\"{$slug}\">" . parse_exp_inline($m[1]) . "</h2>\n";
            continue;
        }

        // Heading 3
        if (preg_match('/^###\s+(.+)$/', $block, $m)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $m[1]), '-'));
            $headings_list[] = ['level' => 3, 'text' => $m[1], 'slug' => $slug];
            $html .= "<h3 id=\"{$slug}\">" . parse_exp_inline($m[1]) . "</h3>\n";
            continue;
        }

        // Heading 4
        if (preg_match('/^####\s+(.+)$/', $block, $m)) {
            $html .= "<h4>" . parse_exp_inline($m[1]) . "</h4>\n";
            continue;
        }

        // HTML Heading tag interception for TOC
        if (preg_match('/^<h([2-3])([^>]*)>(.*?)<\/h\1>/i', $block, $hMatches)) {
            $hLevel = (int)$hMatches[1];
            $hAttrs = $hMatches[2];
            $hText = strip_tags($hMatches[3]);
            $hSlug = '';
            if (preg_match('/id=[\'"]([^\'"]+)[\'"]/i', $hAttrs, $idMatch)) {
                $hSlug = $idMatch[1];
            } else {
                $hSlug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $hText), '-'));
                $block = "<h{$hLevel} id=\"{$hSlug}\"{$hAttrs}>{$hMatches[3]}</h{$hLevel}>";
            }
            $headings_list[] = ['level' => $hLevel, 'text' => $hText, 'slug' => $hSlug];
            $html .= $block . "\n";
            continue;
        }

        // Tables (Markdown Pipe Syntax)
        if (strpos($block, '|') === 0) {
            $lines = explode("\n", $block);
            if (count($lines) >= 2) {
                $tableHtml = "<div class=\"table-responsive-wrapper\"><table class=\"blog-custom-table blog-table-artisan\">\n";
                $hasHeader = false;
                foreach ($lines as $line) {
                    $trimmedLine = trim($line, "|\r\n ");
                    if (!$trimmedLine || preg_match('/^[:\-\s|]+$/', $trimmedLine)) continue;
                    $cols = explode('|', $trimmedLine);
                    $rowHtml = "  <tr>\n";
                    foreach ($cols as $col) {
                        $colVal = parse_exp_inline(trim($col));
                        $cellTag = !$hasHeader ? 'th' : 'td';
                        $rowHtml .= "    <{$cellTag}>{$colVal}</{$cellTag}>\n";
                    }
                    $rowHtml .= "  </tr>\n";
                    if (!$hasHeader) {
                        $tableHtml .= "<thead>\n{$rowHtml}</thead>\n<tbody>\n";
                        $hasHeader = true;
                    } else {
                        $tableHtml .= $rowHtml;
                    }
                }
                if ($hasHeader) $tableHtml .= "</tbody>\n";
                $tableHtml .= "</table></div>\n";
                $html .= $tableHtml;
                continue;
            }
        }

        // Blockquotes & Callouts
        if (strpos($block, '> ') === 0) {
            $lines = explode("\n", $block);
            $quoteText = '';
            foreach ($lines as $line) {
                $quoteText .= substr(trim($line), 2) . ' ';
            }
            $quoteText = trim($quoteText);
            $html .= "<blockquote><p>" . parse_exp_inline($quoteText) . "</p></blockquote>\n";
            continue;
        }

        // Bullet Lists
        if (strpos($block, '- ') === 0 || strpos($block, '* ') === 0) {
            $items = explode("\n", $block);
            $listHtml = "<ul>\n";
            foreach ($items as $item) {
                $listHtml .= "  <li>" . parse_exp_inline(substr(trim($item), 2)) . "</li>\n";
            }
            $listHtml .= "</ul>\n";
            $html .= $listHtml;
            continue;
        }

        // Ordered Lists
        if (preg_match('/^\d+\.\s+/', $block)) {
            $items = explode("\n", $block);
            $listHtml = "<ol>\n";
            foreach ($items as $item) {
                $clean = preg_replace('/^\d+\.\s+/', '', trim($item));
                $listHtml .= "  <li>" . parse_exp_inline($clean) . "</li>\n";
            }
            $listHtml .= "</ol>\n";
            $html .= $listHtml;
            continue;
        }

        // Raw HTML blocks (pass through safely)
        if (preg_match('/^<(div|img|hr|p|section|a|span|h[1-6]|table|thead|tbody|tr|td|th|iframe|blockquote|ul|ol|pre|figure)/i', $block)) {
            $html .= $block . "\n";
            continue;
        }

        // Paragraph
        $html .= "<p>" . parse_exp_inline($block) . "</p>\n";
    }

    return $html;
}

// Build compiled article content:
$articleContent = '';
if (!empty($exp['full_content']) && trim($exp['full_content'])) {
    $articleContent = parse_exp_markdown($exp['full_content']);
} else {
    $fallbackMd = '';
    if (!empty($exp['hypothesis'])) {
        $fallbackMd .= "## The Research Objective\n\n" . $exp['hypothesis'] . "\n\n";
    }
    if (!empty($exp['findings'])) {
        $fallbackMd .= "## Sensory Observations & Discoveries\n\n" . $exp['findings'] . "\n\n";
    }
    if (!empty($exp['methodology'])) {
        $fallbackMd .= "## Formulation Methodology & Impact\n\n" . $exp['methodology'] . "\n\n";
    }
    if (!empty($exp['next_steps'])) {
        $fallbackMd .= "## Key Recipe Takeaway & Next Steps\n\n" . $exp['next_steps'] . "\n\n";
    }
    $articleContent = parse_exp_markdown($fallbackMd);
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
   LUXURY BLOG EDITORIAL ARTICLE STYLES (Matching blog-article.php)
   ========================================================================= */

/* Hero status pulse */
.exp-status-dot {
    width: 7.5px;
    height: 7.5px;
    border-radius: 50%;
    display: inline-block;
}
.exp-status-dot.green { 
    background: #16A34A; 
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.25);
    animation: expPulseGreen 2s infinite;
}
.exp-status-dot.amber { 
    background: #D97706; 
    box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.25); 
}
.exp-status-dot.purple { 
    background: #9333EA; 
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.25); 
}
.exp-status-dot.blue { 
    background: #2563EB; 
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25); 
}
@keyframes expPulseGreen {
    0%, 100% { box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.25); }
    50% { box-shadow: 0 0 0 6px rgba(22, 163, 74, 0.1); }
}

/* Luxury Editorial Typography (Matching blog-article-content) */
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
    color: #112217;
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
}

#blog-article-content h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(32px, 5vw, 44px);
    font-weight: 700;
    color: #112217;
    margin-top: 52px;
    margin-bottom: 24px;
    line-height: 1.25;
}

#blog-article-content h2 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(26px, 3.5vw, 33px);
    font-weight: 700;
    color: #112217 !important;
    margin-top: 56px;
    margin-bottom: 22px;
    line-height: 1.32;
    padding-bottom: 12px;
    border-bottom: 2px solid rgba(199, 166, 106, 0.35);
}

#blog-article-content h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(21px, 2.6vw, 25px);
    font-weight: 600;
    color: #16261c !important;
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
    color: #235436;
    margin-top: 32px;
    margin-bottom: 14px;
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
    color: #112217;
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
    margin-bottom: 14px;
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

#blog-article-content strong, #blog-article-content b {
    color: #112217;
    font-weight: 600;
}

#blog-article-content mark, #blog-article-content [style*="background-color"] {
    background: rgba(199, 166, 106, 0.35);
    padding: 2px 6px;
    border-radius: 4px;
}

/* Tables */
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
    font-family: var(--font-sans, sans-serif);
    font-size: 14.5px;
    line-height: 1.6;
    text-align: left;
}
table.blog-table-artisan {
    background: #FAF7F2;
    border: 1px solid #EDE7DB;
}
table.blog-table-artisan th {
    background: #3B2A22;
    color: #C7A66A;
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 18px;
    padding: 14px 18px;
    font-weight: 700;
    letter-spacing: 0.3px;
}
table.blog-table-artisan td {
    padding: 13px 18px;
    border-top: 1px solid rgba(199, 166, 106, 0.2);
    color: #1F2E25;
}
table.blog-table-artisan tr:nth-child(even) td {
    background: rgba(255, 255, 255, 0.6);
}

table.blog-table-minimal th {
    border-bottom: 2px solid var(--gold, #C7A66A);
    color: #3B2A22;
    font-weight: 700;
    padding: 12px 16px;
}
table.blog-table-minimal td {
    border-bottom: 1px solid #EDE7DB;
    padding: 12px 16px;
}

/* Layout Wrappers */
.blog-article-section {
    max-width: 1140px;
    margin: 0 auto;
    padding: 48px 24px 80px;
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
    max-width: 760px;
}

/* Sidebar */
.article-sidebar {
    width: 280px;
    position: sticky;
    top: 100px;
    flex-shrink: 0;
}

.toc-box {
    background: #FEFDFB;
    border: 1px solid #EDE7DB;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(59,42,34,0.03);
    margin-bottom: 24px;
}
.toc-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #3B2A22;
    margin-bottom: 16px;
    border-bottom: 1px solid #EDE7DB;
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
    color: #5C4033;
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

/* Lab Facts Card in Sidebar */
.exp-facts-card {
    background: #FEFDFB;
    border: 1px solid #EDE7DB;
    border-radius: 12px;
    padding: 22px 24px;
    box-shadow: 0 4px 16px rgba(59,42,34,0.03);
    margin-bottom: 24px;
}
.exp-facts-title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 17px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #3B2A22;
    margin-bottom: 14px;
    border-bottom: 1px solid #EDE7DB;
    padding-bottom: 8px;
}
.exp-facts-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.exp-facts-item {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    font-size: 12.5px;
    padding: 8px 0;
    border-bottom: 1px dashed #EDE7DB;
}
.exp-facts-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.exp-facts-label {
    color: #7A8B7E;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 10.5px;
    letter-spacing: 0.08em;
}
.exp-facts-val {
    color: #122217;
    font-weight: 700;
}

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

/* Social Share Buttons */
.share-container {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 36px 0;
    padding: 16px 0;
    border-top: 1px solid #EDE7DB;
    border-bottom: 1px solid #EDE7DB;
    flex-wrap: wrap;
}
.share-title {
    font-size: 12.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #5C4033;
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
.share-btn.copy { background: #EDE7DB; color: #3B2A22; cursor: pointer; border: none; }

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
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    transform: scale(0.95);
    transition: transform 0.3s ease;
}
#lightboxModal.show #lightboxImg {
    transform: scale(1);
}

/* Related Articles Grid */
.related-card {
    background: #FEFDFB;
    border: 1px solid #EDE7DB;
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
                Edit in Admin ↗
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- LUXURY BLOG EDITORIAL HERO (Exact blog-article.php layout) -->
<div id="page-blog-article" class="page active">
  <div class="page-hero blog-page-hero">
    <div class="page-hero-content blog-article-hero-content">
      <a class="btn-outline-dark back-to-blog-btn" href="<?php echo $pathPrefix; ?>innovation-lab.php">&larr; Back to Innovation Lab</a>
      
      <div class="article-hero-text-container" style="max-width: 760px; margin: 0 auto; text-align: left;">
        <!-- Visual Breadcrumbs Trail -->
        <div class="blog-breadcrumbs" style="font-size: 13px; color: var(--gold, #C7A66A); margin: 16px 0 8px; font-family: var(--font-sans, sans-serif);">
          <a href="<?php echo $pathPrefix; ?>index.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Home</a>
          <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
          <a href="<?php echo $pathPrefix; ?>innovation-lab.php" style="color: inherit; text-decoration: none; opacity: 0.85;">Innovation Lab</a>
          <span style="margin: 0 6px; opacity: 0.5;">&rsaquo;</span>
          <span style="opacity: 0.7; font-weight: 500;"><?php echo htmlspecialchars($exp['title']); ?></span>
        </div>

        <div class="section-label" id="blog-article-category" style="display:inline-flex; align-items:center; gap:8px;">
          <span><?php echo htmlspecialchars($exp['category']); ?></span>
          <span style="opacity:0.4;">•</span>
          <span class="exp-status-dot <?php echo $badgeColor; ?>"></span>
          <span><?php echo htmlspecialchars($exp['status_badge'] ?: 'IN PROGRESS'); ?></span>
        </div>
        
        <h1 id="blog-article-title" class="fade-up"><?php echo htmlspecialchars($exp['title']); ?></h1>
        
        <p id="blog-article-meta" class="fade-up-d1">
          <span>Batch #<?php echo $batchCode; ?></span> • 
          <span>Logged <?php echo date('M Y', strtotime($exp['created_at'])); ?></span> • 
          <span>5 min read</span> • 
          <span>RT Chocos Kitchen Research</span>
          <?php if ($isAdmin): ?>
            • <a href="<?php echo $pathPrefix; ?>admin/experiment-editor.php?id=<?php echo $exp['id']; ?>" style="color:var(--gold, #C7A66A); font-weight:700; text-decoration:none;">✏️ Edit in Admin</a>
          <?php endif; ?>
        </p>
      </div>

      <?php if (!empty($rawImg)): ?>
        <img id="blog-article-image" src="<?php echo htmlspecialchars($resolvedImg); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" onclick="openLightbox(this.src)" style="cursor:zoom-in;" onerror="this.style.display='none';" />
      <?php endif; ?>
    </div>
  </div>
  
  <!-- MAIN ARTICLE SECTION -->
  <div class="section blog-article-section">
    <div class="article-layout-wrapper">
        
        <!-- Main body text pane (Flows like a luxury blog article) -->
        <main class="article-main-body">
            
            <!-- Executive Research Brief Card -->
            <?php if (!empty($exp['description'])): ?>
                <div class="exp-brief-card" style="background:#FAF7F2; border:1px solid #EDE7DB; border-left:4px solid var(--gold, #C7A66A); border-radius:0 12px 12px 0; padding:24px 28px; margin-bottom:40px; box-shadow:0 4px 18px rgba(59, 42, 34, 0.04);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                        <span style="font-size:11px; font-weight:800; letter-spacing:0.12em; text-transform:uppercase; color:var(--gold, #C7A66A); font-family:var(--font-sans, sans-serif);">RESEARCH BRIEF &amp; HYPOTHESIS</span>
                        <span style="font-family:monospace; font-size:11.5px; font-weight:700; color:#7D6B62;">BATCH #<?php echo htmlspecialchars($batchCode); ?></span>
                    </div>
                    <p style="font-family:'Playfair Display', Georgia, serif; font-size:19px; line-height:1.68; color:#1F2E25; font-style:italic; margin:0;">
                        "<?php echo htmlspecialchars($exp['description']); ?>"
                    </p>
                </div>
            <?php endif; ?>

            <!-- Article Body (HTML / Markdown formatted with luxury typography) -->
            <div id="blog-article-content">
                <?php echo $articleContent; ?>
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
            <div style="background:#FAF7F2; border-radius:20px; padding:24px; margin-top:40px; margin-bottom:24px; box-shadow:0 4px 16px rgba(59,42,34,0.06); display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                <img src="<?php echo $pathPrefix; ?>assets/myphoto.jpg" alt="Aarti Saluja Sahni — founder and expert chocolate educator at RT Chocos India" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #5C4033; flex-shrink:0;">
                <div style="flex:1; min-width:240px;">
                    <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--gold, #C7A66A); letter-spacing:0.08em; display:block; margin-bottom:4px;">Research &amp; Formulation</span>
                    <h4 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700; color:#3B2A22; margin:0 0 8px;">Aarti Saluja Sahni &amp; The RT Chocos Kitchen</h4>
                    <p style="font-family:var(--font-sans, sans-serif); font-size:13.5px; line-height:1.6; color:#5C4033; font-weight:300; margin:0 0 12px;">
                        Certified chocolate educator and formulation researcher testing bean-to-bar boundaries, tropical fat structures, and indigenous Indian botanical pairings.
                    </p>
                    <a href="<?php echo $pathPrefix; ?>innovation-lab.php" class="btn-outline" style="text-decoration:none; padding:6px 14px; font-size:12px; display:inline-block;">Explore Innovation Lab</a>
                </div>
            </div>

        </main>

        <!-- Sticky Sidebar Panel (On this page TOC & Quick Factsheet) -->
        <aside class="article-sidebar" id="tocSidebar">
            
            <!-- Table of Contents -->
            <?php if (!empty($headings_list)): ?>
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
            <?php endif; ?>

            <!-- Clean Quick Factsheet Card (No rigid boxes) -->
            <div class="exp-facts-card">
                <div class="exp-facts-title">Experiment Overview</div>
                <ul class="exp-facts-list">
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Research Focus</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['category']); ?></span>
                    </li>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Batch Code</span>
                        <span class="exp-facts-val" style="font-family:monospace;"><?php echo htmlspecialchars($batchCode); ?></span>
                    </li>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Trial Status</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['status_badge'] ?: 'IN PROGRESS'); ?></span>
                    </li>
                    <?php if (!empty($exp['origin'])): ?>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Terroir Origin</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['origin']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($exp['polymorph'])): ?>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Crystal Matrix</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['polymorph']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($exp['particle_size'])): ?>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Smoothness</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['particle_size']); ?></span>
                    </li>
                    <?php endif; ?>
                    <?php if (!empty($exp['water_activity'])): ?>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Moisture Level</span>
                        <span class="exp-facts-val"><?php echo htmlspecialchars($exp['water_activity']); ?></span>
                    </li>
                    <?php endif; ?>
                    <li class="exp-facts-item">
                        <span class="exp-facts-label">Kitchen</span>
                        <span class="exp-facts-val">RT Chocos Lab</span>
                    </li>
                </ul>
            </div>

            <!-- Callout Card -->
            <div class="exp-sidebar-callout">
                <h4>Curious About Our Chocolate?</h4>
                <p>Every chocolate bar we craft starts with experiments right here in our Innovation Lab. Taste the outcome in our chocolate shop.</p>
                <a href="<?php echo $pathPrefix; ?>shop.php">
                    <span>Explore Chocolate Shop</span>
                    <span>→</span>
                </a>
            </div>

        </aside>

    </div>

    <!-- Related Experiments Section (Matching Related Insights from blog-article.php) -->
    <?php if (!empty($relatedExperiments)): ?>
        <div style="margin-top: 60px; border-top: 1px solid #EDE7DB; padding-top: 48px;">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom: 24px;">
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; margin:0; color: #3B2A22; font-weight: 700;">More Experiments From the Lab</h3>
                <a href="<?php echo $pathPrefix; ?>innovation-lab.php#currently-in-lab" style="font-size:12px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold, #C7A66A); text-decoration:none;">View All Experiments &rarr;</a>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(265px, 1fr)); gap: 24px;">
                <?php foreach ($relatedExperiments as $rel): 
                    $relRawImg = $rel['image_path'] ?: 'assets/images/placeholder.jpg';
                    $relImgUrl = (strpos($relRawImg, 'http') === 0 || strpos($relRawImg, '/') === 0 || strpos($relRawImg, '../') === 0) ? $relRawImg : ($pathPrefix . $relRawImg);
                    $relLink = $pathPrefix . "experiment/" . $rel['slug'];
                ?>
                    <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                        <div style="height:180px; overflow:hidden; background:linear-gradient(145deg, #FAF7F2 0%, #EDE7DB 100%); display:flex; align-items:center; justify-content:center;">
                            <img src="<?php echo htmlspecialchars($relImgUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" onerror="this.src='<?php echo $pathPrefix; ?>assets/images/placeholder.jpg';">
                        </div>
                        <div style="padding: 18px; display: flex; flex-direction: column; flex: 1;">
                            <span style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--gold, #C7A66A); margin-bottom: 6px;"><?php echo htmlspecialchars($rel['category']); ?></span>
                            <h4 style="font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 700; color: #3B2A22; margin: 0 0 8px; line-height: 1.35;"><?php echo htmlspecialchars($rel['title']); ?></h4>
                            <p style="font-family: var(--font-sans, sans-serif); font-size: 12.5px; color: #5C4033; line-height: 1.5; margin: 0 0 14px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
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

<!-- Image Lightbox Modal -->
<div id="lightboxModal" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="Enlarged visual">
</div>

<script>
// Scroll Progress and Dynamic TOC Scroll Spy Listeners (Matching blog-article.php)
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

// Lightbox helpers
function openLightbox(src) {
    const modal = document.getElementById('lightboxModal');
    const img = document.getElementById('lightboxImg');
    if (modal && img) {
        img.src = src;
        modal.classList.add('show');
    }
}

function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

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