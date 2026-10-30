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

// Full Markdown / HTML Parser for Innovation Lab Experiments
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
    $headings_list = [];
    if (!trim((string)$markdown)) return '';
    
    $html = str_replace(["\r\n", "\r"], "\n", $markdown);

    // 1. YouTube embeds: {{youtube:ID}} or [youtube](url)
    $html = preg_replace('/\{\{youtube:([a-zA-Z0-9_\-]+)\}\}/i', '<div class="blog-yt-embed" style="margin:36px 0; border-radius:12px; overflow:hidden; position:relative; padding-bottom:56.25%; height:0; box-shadow:0 10px 30px rgba(0,0,0,0.08);"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;"></iframe></div>', $html);
    $html = preg_replace_callback('/\[youtube\]\((.*?)\)/i', function($m) {
        $url = $m[1];
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $url, $yt)) {
            return '<div class="blog-yt-embed" style="margin:36px 0; border-radius:12px; overflow:hidden; position:relative; padding-bottom:56.25%; height:0; box-shadow:0 10px 30px rgba(0,0,0,0.08);"><iframe src="https://www.youtube.com/embed/' . $yt[1] . '" frameborder="0" allowfullscreen style="position:absolute; top:0; left:0; width:100%; height:100%; border:none;"></iframe></div>';
        }
        return '';
    }, $html);

    // 2. Strip any lingering admin editor UI artifacts and unwanted helper attributes
    $html = preg_replace('/<div class="editor-(?:table-topbar|figure-topbar|table-resize-handle|resize-handle|block-drag-handle|drop-line|col-guide|size-badge)"[^>]*>.*?<\/div>/is', '', $html);
    $html = preg_replace('/<div class="topbar-actions"[^>]*>.*?<\/div>/is', '', $html);
    $html = preg_replace('/<button\b[^>]*\b(?:data-action|topbar-btn)[^>]*>.*?<\/button>/is', '', $html);
    $html = preg_replace('/<span class="topbar-sep"[^>]*>.*?<\/span>/is', '', $html);
    $html = preg_replace('/\s*contenteditable=[\'"][^\'"]*[\'"]/i', '', $html);
    $html = preg_replace('/\s*data-controls-attached=[\'"][^\'"]*[\'"]/i', '', $html);
    $html = preg_replace('/\s*class=[\'"]([^\'"]*)\b(is-selected|is-dragging|is-resizing|just-moved|drag-over)\b([^\'"]*)[\'"]/i', 'class="$1 $3"', $html);

    // 3. Process Markdown Pipe Tables (converts markdown table syntax to responsive HTML table)
    $html = preg_replace_callback('/(?:^|\n)(\|[^\n]+\|\n\|[\s:\-\|]+\|\n(?:\|[^\n]+\|\n?)+)/m', function($m) {
        $lines = explode("\n", trim($m[1]));
        $tHtml = "\n<div class=\"table-responsive-wrapper\"><table class=\"blog-custom-table blog-table-artisan\">\n";
        $hasHeader = false;
        foreach ($lines as $line) {
            $trimmedLine = trim($line, "|\r\n ");
            if (!$trimmedLine || preg_match('/^[:\-\s|]+$/', $trimmedLine)) continue;
            $cols = explode('|', $trimmedLine);
            $rowHtml = "  <tr>\n";
            foreach ($cols as $col) {
                $cellTag = !$hasHeader ? 'th' : 'td';
                $rowHtml .= "    <{$cellTag}>" . parse_exp_inline(trim($col)) . "</{$cellTag}>\n";
            }
            $rowHtml .= "  </tr>\n";
            if (!$hasHeader) {
                $tHtml .= "<thead>\n{$rowHtml}</thead>\n<tbody>\n";
                $hasHeader = true;
            } else {
                $tHtml .= $rowHtml;
            }
        }
        if ($hasHeader) $tHtml .= "</tbody>\n";
        $tHtml .= "</table></div>\n";
        return $tHtml;
    }, $html);

    // 4. Ensure all HTML tables (WYSIWYG or pasted from Word/Excel) have luxury artisan styling & responsive wrapper
    $html = preg_replace_callback('/<table\b([^>]*)>(.*?)<\/table>/is', function($m) {
        $attrs = $m[1];
        $inner = $m[2];
        if (strpos($attrs, 'blog-custom-table') === false) {
            if (preg_match('/class=[\'"]([^\'"]*)[\'"]/i', $attrs, $cm)) {
                $attrs = preg_replace('/class=[\'"][^\'"]*[\'"]/i', 'class="' . trim($cm[1]) . ' blog-custom-table blog-table-artisan"', $attrs);
            } else {
                $attrs .= ' class="blog-custom-table blog-table-artisan"';
            }
        }
        // Remove hardcoded width / height attributes from pasted Word tables to keep them fully responsive
        $attrs = preg_replace('/\s*(?:width|height)=[\'"][^\'"]*[\'"]/i', '', $attrs);
        return '<table' . $attrs . '>' . $inner . '</table>';
    }, $html);

    // Clean up any empty wrappers or nested wrappers before wrapping
    $html = preg_replace('/<div class="table-responsive-wrapper">\s*<\/div>/is', '', $html);
    // Canonicalize: strip existing wrapper so we wrap exactly once without duplicates or orphaned </div>
    $html = preg_replace('/<div class="table-responsive-wrapper">\s*(<table\b.*?<\/table>)\s*<\/div>/is', '$1', $html);
    // Wrap bare tables in table-responsive-wrapper
    $html = preg_replace('/(<table\b.*?<\/table>)/is', "\n<div class=\"table-responsive-wrapper\">$1</div>\n", $html);
    // Remove any trailing orphan </div> immediately following table-responsive-wrapper
    $html = preg_replace('/(<div class="table-responsive-wrapper">.*?<\/div>)\s*<\/div>/is', '$1', $html);

    // 5. Convert Markdown Headings (# H1 -> H2, ## H2, ### H3, #### H4)
    $html = preg_replace_callback('/^(#{1,6})\s+(.+)$/m', function($m) {
        $level = min(4, max(2, strlen($m[1])));
        $text = trim($m[2]);
        $cleanHeading = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanHeading), '-'));
        return "<h{$level} id=\"{$slug}\">" . parse_exp_inline($text) . "</h{$level}>";
    }, $html);

    // 6. Extract Table of Contents from ALL H2 and H3 tags & ensure each has a clean anchor ID (in-place replacement)
    $html = preg_replace_callback('/<h([2-3])([^>]*)>(.*?)<\/h\1>/is', function($m) use (&$headings_list) {
        $level = (int)$m[1];
        $attrs = $m[2];
        $content = $m[3];
        $cleanText = html_entity_decode(trim(strip_tags($content)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $slug = '';
        if (preg_match('/id=[\'"]([^\'"]+)[\'"]/i', $attrs, $idMatch)) {
            $slug = $idMatch[1];
        } else {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cleanText), '-'));
            $attrs = ' id="' . htmlspecialchars($slug) . '"' . $attrs;
        }

        if ($cleanText) {
            $headings_list[] = [
                'level' => $level,
                'text' => $cleanText,
                'slug' => $slug
            ];
        }

        return "<h{$level}{$attrs}>{$content}</h{$level}>";
    }, $html);

    // 7. Process Markdown blockquotes (> quote)
    $html = preg_replace_callback('/(?:^|\n)((?:>\s?[^\n]+\n?)+)/', function($m) {
        $lines = explode("\n", trim($m[1]));
        $q = '';
        foreach ($lines as $l) {
            $q .= preg_replace('/^>\s?/', '', $l) . ' ';
        }
        $q = trim($q);
        if (preg_match('/^\[!(NOTE|TIP|WARNING)\]\s*(.*)$/is', $q, $cm)) {
            return "\n<blockquote class=\"callout\"><p>" . parse_exp_inline(trim($cm[2])) . "</p></blockquote>\n";
        }
        return "\n<blockquote><p>" . parse_exp_inline($q) . "</p></blockquote>\n";
    }, $html);

    // 8. Process Markdown lists if not already HTML
    $html = preg_replace_callback('/(?:^|\n)((?:[*•\-]\s+[^\n]+\n?)+)/', function($m) {
        $lines = explode("\n", trim($m[1]));
        $lHtml = "\n<ul>\n";
        foreach ($lines as $l) {
            $t = trim(preg_replace('/^[*•\-]\s+/', '', trim($l)));
            if ($t) $lHtml .= "  <li>" . parse_exp_inline($t) . "</li>\n";
        }
        $lHtml .= "</ul>\n";
        return $lHtml;
    }, $html);

    $html = preg_replace_callback('/(?:^|\n)((?:\d+[\.\)]\s+[^\n]+\n?)+)/', function($m) {
        $lines = explode("\n", trim($m[1]));
        $lHtml = "\n<ol>\n";
        foreach ($lines as $l) {
            $t = trim(preg_replace('/^\d+[\.\)]\s+/', '', trim($l)));
            if ($t) $lHtml .= "  <li>" . parse_exp_inline($t) . "</li>\n";
        }
        $lHtml .= "</ol>\n";
        return $lHtml;
    }, $html);

    // 9. Process Markdown images: ![alt](url)
    $html = preg_replace_callback('/!\[(.*?)\]\((.*?)\)/', function($m) {
        $caption = htmlspecialchars($m[1]);
        $url = htmlspecialchars($m[2]);
        return "<figure class=\"blog-figure blog-figure-center\"><img src=\"{$url}\" alt=\"{$caption}\" loading=\"lazy\" onclick=\"openLightbox(this.src)\" style=\"cursor:zoom-in;\">" . ($caption ? "<figcaption>{$caption}</figcaption>" : "") . "</figure>";
    }, $html);

    // 10. Process Markdown links: [text](url)
    $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $html);

    // 11. Process Markdown inline bold and italic
    $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/', '<em>$1</em>', $html);

    // 12. Handle loose blocks: if content is mixed plain text, wrap loose non-HTML blocks in <p>
    $hasBlockTags = preg_match('/<(?:p|div|blockquote|ul|ol|table|figure)\b/i', $html);
    if (!$hasBlockTags) {
        $paragraphs = explode("\n\n", $html);
        $res = '';
        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (!$para) continue;
            if (preg_match('/^<(?:h[1-6]|div|table|blockquote|ul|ol|figure|hr)/i', $para)) {
                $res .= $para . "\n\n";
            } else {
                $res .= "<p>" . nl2br($para) . "</p>\n\n";
            }
        }
        $html = $res;
    }

    // Defensive guarantee: ensure <div> tags are strictly balanced so that
    // stray or missing </div> tags in content can NEVER break the outer 2-column grid layout.
    $openDivs = substr_count(strtolower($html), '<div');
    $closeDivs = substr_count(strtolower($html), '</div>');
    while ($closeDivs > $openDivs) {
        $pos = strrpos($html, '</div>');
        if ($pos !== false) {
            $html = substr_replace($html, '', $pos, 6);
            $closeDivs--;
        } else {
            break;
        }
    }
    while ($openDivs > $closeDivs) {
        $html .= "\n</div>\n";
        $closeDivs++;
    }

    return trim($html);
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
   LUXURY EXPERIMENT EDITORIAL HERO STYLES
   ========================================================================= */
/* =========================================================================
   LUXURY EXPERIMENT EDITORIAL HERO STYLES (1140px Aligned Grid)
   ========================================================================= */
.exp-hero-section {
    background: linear-gradient(180deg, #FBF8F3 0%, #F5EFEB 100%);
    border-bottom: 1px solid rgba(199, 166, 106, 0.25);
    padding: 32px 24px 44px;
    position: relative;
    overflow: hidden;
}

.exp-hero-inner {
    max-width: 1140px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

/* Top Navigation Bar: Back button on left, Badges on right */
.exp-nav-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
    gap: 16px;
    width: 100%;
}

.exp-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FFFFFF;
    border: 1px solid rgba(199, 166, 106, 0.38);
    color: #3B2A22;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(59, 42, 34, 0.04);
    transition: all 0.25s ease;
}
.exp-back-btn:hover {
    background: #3B2A22;
    color: #FFFFFF;
    border-color: #3B2A22;
    transform: translateX(-3px);
    box-shadow: 0 4px 14px rgba(59, 42, 34, 0.12);
}
.exp-back-btn svg {
    transition: transform 0.2s ease;
}
.exp-back-btn:hover svg {
    transform: translateX(-2px);
}

.exp-hero-badges {
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.exp-cat-badge {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--gold, #C7A66A);
    font-family: var(--font-sans, 'Inter', sans-serif);
}

.exp-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: var(--font-sans, 'Inter', sans-serif);
}
.exp-status-pill.green {
    background: rgba(22, 163, 74, 0.1);
    color: #16A34A;
    border: 1px solid rgba(22, 163, 74, 0.28);
}
.exp-status-pill.amber {
    background: rgba(217, 119, 6, 0.1);
    color: #D97706;
    border: 1px solid rgba(217, 119, 6, 0.28);
}
.exp-status-pill.blue {
    background: rgba(37, 99, 235, 0.1);
    color: #2563EB;
    border: 1px solid rgba(37, 99, 235, 0.28);
}
.exp-status-pill.purple {
    background: rgba(147, 51, 234, 0.1);
    color: #9333EA;
    border: 1px solid rgba(147, 51, 234, 0.28);
}

/* Pulsing Status Dot */
.exp-status-dot {
    width: 7px;
    height: 7px;
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

/* Hero Typography (Center-balanced within 1140px grid) */
.exp-hero-text {
    text-align: center;
    max-width: 900px;
    margin: 0 auto 30px;
}

.exp-main-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(34px, 4.8vw, 50px);
    font-weight: 700;
    line-height: 1.15;
    color: #122217;
    margin: 0 0 16px;
    letter-spacing: -0.015em;
    text-align: center;
}

.exp-lead-deck {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(17px, 2vw, 20px);
    line-height: 1.55;
    color: #5C4033;
    font-style: italic;
    margin: 0 auto 20px;
    max-width: 760px;
    text-align: center;
}

.exp-meta-strip {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 13px;
    color: #7D6B62;
    font-family: var(--font-sans, 'Inter', sans-serif);
    flex-wrap: wrap;
    margin: 0 auto;
}

.exp-meta-sep {
    opacity: 0.4;
    color: #8C786E;
    margin: 0 2px;
}

.exp-meta-batch {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 700;
    color: #8A6420;
    background: #FAF5EB;
    border: 1px solid rgba(199, 166, 106, 0.35);
    padding: 2px 7px;
    border-radius: 4px;
    font-size: 11px;
    letter-spacing: 0.04em;
}

.exp-admin-link {
    color: var(--gold, #C7A66A);
    font-weight: 700;
    text-decoration: none;
    transition: opacity 0.2s;
}
.exp-admin-link:hover {
    text-decoration: underline;
    opacity: 0.85;
}

/* Cover Image Frame: Completely fills the 1140px width to align with content grid below */
.exp-hero-cover-frame {
    width: 100%;
    max-width: 1140px;
    margin: 0 auto;
    height: 480px;
    border-radius: 20px;
    overflow: hidden;
    position: relative;
    background: #E8DFD3;
    border: 1px solid rgba(199, 166, 106, 0.35);
    box-shadow: 0 20px 50px rgba(18, 34, 23, 0.1), 0 4px 12px rgba(18, 34, 23, 0.03);
    cursor: zoom-in;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
}
.exp-hero-cover-frame:hover {
    transform: translateY(-2px);
    box-shadow: 0 28px 64px rgba(18, 34, 23, 0.14), 0 6px 18px rgba(18, 34, 23, 0.05);
}
.exp-hero-cover-frame img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center !important;
    display: block !important;
    transition: transform 0.7s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.exp-hero-cover-frame:hover img {
    transform: scale(1.025) !important;
}

.exp-cover-zoom-hint {
    position: absolute;
    bottom: 16px;
    right: 16px;
    background: rgba(18, 34, 23, 0.72);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    color: #FFFFFF;
    font-size: 11.5px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.3s ease, transform 0.3s ease;
    pointer-events: none;
}
.exp-hero-cover-frame:hover .exp-cover-zoom-hint {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .exp-hero-section {
        padding: 24px 16px 36px;
    }
    .exp-nav-bar {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 20px;
    }
    .exp-hero-cover-frame {
        height: 280px;
        border-radius: 14px;
    }
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
    line-height: 0.8;
    margin-right: 6px; /* Optimized kerning for slender initial glyphs like 'I' */
    margin-top: 4px;
    font-weight: 700;
    color: var(--gold, #C7A66A);
    text-shadow: 0 2px 8px rgba(199, 166, 106, 0.2);
}

#blog-article-content p {
    margin-bottom: 26px;
    color: var(--text-primary, #1F2E25);
}

#blog-article-content h1,
#blog-article-content h2,
#blog-article-content h3,
#blog-article-content h4 {
    scroll-margin-top: 100px; /* Clear fixed sticky header on TOC anchor navigation */
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
    border: 1px solid #EDE7DB;
    background: #FAF7F2;
    box-shadow: 0 4px 20px rgba(59, 42, 34, 0.08);
    -webkit-overflow-scrolling: touch;
}
table.blog-custom-table, #blog-article-content table {
    width: 100%;
    min-width: 580px; /* Ensures clean layout without syllable fragmentation on mobile */
    table-layout: auto !important;
    border-collapse: collapse;
    font-family: var(--font-sans, sans-serif);
    font-size: 14.5px;
    line-height: 1.6;
    text-align: left;
}
table.blog-table-artisan, #blog-article-content table {
    background: #FAF7F2;
    border-radius: 10px;
    overflow: hidden;
}
#blog-article-content table.blog-table-artisan th,
#blog-article-content table.blog-custom-table th,
table.blog-table-artisan th, 
#blog-article-content table th {
    background: #1B2E23 !important; /* Deep forest green artisan header */
    color: #F7E7CE !important;      /* Soft warm champagne gold high-contrast text */
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 17.5px;
    padding: 14px 18px;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-bottom: 2px solid #C7A66A !important;
}
#blog-article-content table.blog-table-artisan td,
#blog-article-content table.blog-custom-table td,
table.blog-table-artisan td, 
#blog-article-content table td {
    padding: 13px 18px;
    border-top: 1px solid rgba(199, 166, 106, 0.2);
    color: #1F2E25;
}
#blog-article-content table.blog-table-artisan tr:nth-child(even) td,
table.blog-table-artisan tr:nth-child(even) td, 
#blog-article-content table tr:nth-child(even) td {
    background: rgba(255, 255, 255, 0.65);
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
    padding: 36px 24px 80px;
}
.article-layout-wrapper {
    display: flex;
    gap: 44px;
    align-items: start;
    position: relative;
}
.article-main-body {
    flex: 1;
    min-width: 0;
    max-width: 740px;
}

/* Sidebar */
.article-sidebar {
    width: 315px;
    position: sticky;
    top: 96px;
    flex-shrink: 0;
    z-index: 10;
}



/* Table of Contents */
.toc-box {
    background: #FEFDFB;
    border: 1px solid rgba(199, 166, 106, 0.35);
    border-radius: 16px;
    padding: 22px 20px;
    box-shadow: 0 6px 20px rgba(59,42,34,0.03);
    margin-bottom: 24px;
}
.toc-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: #122217;
    margin-bottom: 14px;
    border-bottom: 1px solid #F0EAE1;
    padding-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.toc-title::before {
    content: '✦';
    color: var(--gold, #C7A66A);
    font-size: 11px;
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

/* Sidebar Callout Card */
.exp-sidebar-callout {
    background: linear-gradient(135deg, #14251B 0%, #1A3324 100%);
    color: #FAF6EF;
    border-radius: 16px;
    padding: 24px 22px;
    box-shadow: 0 12px 30px rgba(18, 34, 23, 0.18);
    border: 1px solid rgba(199, 166, 106, 0.25);
    position: relative;
    overflow: hidden;
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
    .article-layout-wrapper {
        flex-direction: column !important;
        gap: 40px !important;
    }
    .article-main-body {
        max-width: 100% !important;
    }
    .article-sidebar {
        width: 100% !important;
        max-width: 640px !important;
        margin: 12px auto 0 !important;
        position: static !important;
        top: auto !important;
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

<!-- --- LAB EXPERIMENT ARTICLE SECTION (Identical Architecture to blog-article.php) --- -->
<div id="page-blog-article" class="page active">

  <!-- 1. EDITORIAL HERO (1140px Aligned Grid, Clutter-Free) -->
  <section class="exp-hero-section">
    <div class="exp-hero-inner">
      
      <!-- Top Utility Bar: Back Navigation (Left) + Badges (Right) -->
      <div class="exp-nav-bar">
        <a class="exp-back-btn" href="<?php echo $pathPrefix; ?>innovation-lab.php">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
          <span>Back to Innovation Lab</span>
        </a>
        
        <div class="exp-hero-badges">
          <span class="exp-cat-badge"><?php echo htmlspecialchars($exp['category']); ?></span>
          <span class="exp-status-pill <?php echo $badgeColor; ?>">
            <span class="exp-status-dot <?php echo $badgeColor; ?>"></span>
            <?php echo htmlspecialchars($exp['status_badge'] ?: 'IN PROGRESS'); ?>
          </span>
        </div>
      </div>

      <!-- Main Headline & Editorial Deck -->
      <div class="exp-hero-text">
        <h1 class="exp-main-title"><?php echo htmlspecialchars($exp['title']); ?></h1>
        
        <?php if (!empty($exp['description'])): ?>
          <p class="exp-lead-deck"><?php echo htmlspecialchars($exp['description']); ?></p>
        <?php endif; ?>

        <!-- Streamlined Metadata Bar -->
        <div class="exp-meta-strip">
          <span class="exp-meta-batch">Batch #<?php echo htmlspecialchars($batchCode); ?></span>
          <span class="exp-meta-sep">•</span>
          <span>Logged <?php echo date('M Y', strtotime($exp['created_at'])); ?></span>
          <span class="exp-meta-sep">•</span>
          <span>5 min read</span>
          <span class="exp-meta-sep">•</span>
          <span>RT Chocos Kitchen Research</span>
          <?php if ($isAdmin): ?>
            <span class="exp-meta-sep">•</span>
            <a href="<?php echo $pathPrefix; ?>admin/experiment-editor.php?id=<?php echo $exp['id']; ?>" class="exp-admin-link">✏️ Edit in Admin</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- High-Resolution Cover Image (Full 1140px Grid Alignment) -->
      <?php if (!empty($rawImg)): ?>
        <div class="exp-hero-cover-frame" onclick="openLightbox('<?php echo htmlspecialchars($resolvedImg); ?>')">
          <img id="blog-article-image" src="<?php echo htmlspecialchars($resolvedImg); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" onerror="this.parentElement.style.display='none';" />
          <div class="exp-cover-zoom-hint">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            <span>Click to expand visual</span>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- 2. ARTICLE SECTION (Main Body + Sidebar) -->
  <div class="section blog-article-section">
    <div class="article-layout-wrapper">
        
        <!-- Main body text pane (Flows like a luxury blog article) -->
        <main class="article-main-body">
            
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

        <!-- Sticky Sidebar Panel (Specs, TOC, Shop Promo) -->
        <aside class="article-sidebar" id="tocSidebar">
            
            <!-- 1. Table of Contents -->
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

            <!-- 3. Chocolate Shop Callout Card -->
            <div class="exp-sidebar-callout">
                <h4>Curious About Our Chocolate?</h4>
                <p>Every chocolate bar we craft starts with experiments right here in our Innovation Lab. Taste the outcome in our chocolate shop.</p>
                <a href="<?php echo $pathPrefix; ?>shop.php">
                    <span>Explore Chocolate Shop</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </aside>

    </div>

    <!-- 3. Related Experiments Section (Matching Related Insights from blog-article.php) -->
    <?php if (!empty($relatedExperiments)): ?>
        <div style="margin-top:64px; border-top:1px solid #EDE7DB; padding-top:48px;">
            <div style="display:flex; justify-content:space-between; align-items:baseline; margin-bottom:24px;">
                <h3 style="font-family:'Cormorant Garamond', serif; font-size:28px; margin:0; color:#3B2A22; font-weight:700;">More Experiments From the Lab</h3>
                <a href="<?php echo $pathPrefix; ?>innovation-lab.php#currently-in-lab" style="font-size:12px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold, #C7A66A); text-decoration:none;">View All Experiments &rarr;</a>
            </div>
            
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(265px, 1fr)); gap:24px;">
                <?php foreach ($relatedExperiments as $rel): 
                    $relRawImg = $rel['image_path'] ?: 'assets/images/placeholder.jpg';
                    $relImgUrl = (strpos($relRawImg, 'http') === 0 || strpos($relRawImg, '/') === 0 || strpos($relRawImg, '../') === 0) ? $relRawImg : ($pathPrefix . $relRawImg);
                    $relLink = $pathPrefix . "experiment/" . $rel['slug'];
                ?>
                    <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                        <div style="height:180px; overflow:hidden; background:linear-gradient(145deg, #FAF7F2 0%, #EDE7DB 100%); display:flex; align-items:center; justify-content:center;">
                            <img src="<?php echo htmlspecialchars($relImgUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" onerror="this.src='<?php echo $pathPrefix; ?>assets/images/placeholder.jpg';">
                        </div>
                        <div style="padding:18px; display:flex; flex-direction:column; flex:1;">
                            <span style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--gold, #C7A66A); margin-bottom:6px;"><?php echo htmlspecialchars($rel['category']); ?></span>
                            <h4 style="font-family:'Playfair Display', serif; font-size:17px; font-weight:700; color:#3B2A22; margin:0 0 8px; line-height:1.35;"><?php echo htmlspecialchars($rel['title']); ?></h4>
                            <p style="font-family:var(--font-sans, sans-serif); font-size:12.5px; color:#5C4033; line-height:1.5; margin:0 0 14px; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                                <?php echo htmlspecialchars($rel['description']); ?>
                            </p>
                            <span style="font-size:11px; font-weight:700; color:var(--gold, #C7A66A); text-transform:uppercase; letter-spacing:0.5px;">Read Dossier &rarr;</span>
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