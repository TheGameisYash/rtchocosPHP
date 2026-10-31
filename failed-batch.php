<?php
// failed-batch.php — Dedicated Luxury Single Failed Batch Case Study for RT Chocos Innovation Lab
// Styled with the exact luxury editorial architecture as experiment.php and blog-article.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';

$slug = $_GET['slug'] ?? '';
$slug = trim($slug);

if (empty($slug)) {
    header('Location: ../innovation-lab.php#failed-experiments');
    exit;
}

$pdo = get_db();
$isAdmin = function_exists('is_admin_logged_in') && is_admin_logged_in();

// Fetch failed batch by slug (or fallback by numeric ID)
$stmt = $pdo->prepare("SELECT * FROM lab_failed_batches WHERE slug = ? OR (id = ? AND id > 0) LIMIT 1");
$stmt->execute([$slug, is_numeric($slug) ? (int)$slug : 0]);
$fail = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fail) {
    http_response_code(404);
    $pathPrefix = "../";
    if (file_exists(__DIR__ . '/error.php')) {
        include __DIR__ . '/error.php';
    } else {
        echo "<h1>404 — Case Study Not Found</h1><p><a href='../innovation-lab.php#failed-experiments'>Return to Innovation Lab</a></p>";
    }
    exit;
}

// Check active / draft status
$isAdminPreview = false;
if ((int)$fail['is_active'] === 0) {
    if ($isAdmin) {
        $isAdminPreview = true;
    } else {
        http_response_code(404);
        $pathPrefix = "../";
        if (file_exists(__DIR__ . '/error.php')) {
            include __DIR__ . '/error.php';
        } else {
            echo "<h1>404 — Case Study Not Found</h1><p><a href='../innovation-lab.php#failed-experiments'>Return to Innovation Lab</a></p>";
        }
        exit;
    }
}

// Path prefix for clean URLs (/failed-batch/{slug} is 1 directory deep)
$pathPrefix = "../";

// Curated Laboratory Telemetry Fallbacks for Failed Batches
$curatedFailSpecs = [
    1 => [
        'batchRef' => 'FAIL-POSTMORTEM-01 · GUAVA-TRIAL',
        'defectMode' => 'Fruit Flavor Loss & Lipid Moisture Seizure',
        'trialScale' => '25 kg Pilot Melanger Run',
        'criticalMoisture' => 'Moisture reached 0.58 Aw (Target: under 0.35 Aw)',
        'viscosityImpact' => 'Chocolate mass thickened heavily during final stone-milling',
        'correctiveChecklist' => [
            'Cocoa Butter Barrier Coating: Lightly atomize all freeze-dried fruit inclusions with tempered cocoa butter before stone refining so delicate volatile aroma compounds remain trapped.',
            'Controlled Melanging Temperature: Maintain granite mill friction heat strictly under 38°C to prevent volatile citrus and terpene ester evaporation.',
            'Karl Fischer Pre-Inclusion Titration: Test every batch of dehydrated fruit to verify moisture activity (Aw < 0.32) before introducing into lipid mass.'
        ]
    ]
];

$failSpecs = $curatedFailSpecs[$fail['id']] ?? [
    'batchRef' => 'FAIL-CASE-0' . $fail['id'],
    'defectMode' => 'Physical & Crystallisation Anomaly',
    'trialScale' => 'Pilot Kitchen Formulation',
    'criticalMoisture' => 'Exceeded Baseline Lipid Tolerance',
    'viscosityImpact' => 'Observed Shear & Rheology Deviation',
    'correctiveChecklist' => [
        'Formulation revision: Adjust solid-to-lipid ratio before initial melanging.',
        'Thermal discipline: Re-calibrate tempering curve parameters.',
        'Sensory sign-off: Conduct accelerated stability testing.'
    ]
];

// SEO & Header Variables
$pageTitle = htmlspecialchars($fail['title']) . " | Failed Batch Case Study | RT Chocos Innovation Lab";
$pageDescription = htmlspecialchars(!empty($fail['what_happened']) ? $fail['what_happened'] : 'A transparent behind-the-scenes laboratory case study on what went wrong and how we fixed it.');
$pageImage = $fail['image_path'] ?: 'assets/images/lab-failed-batch.jpg';
$pageType = 'article';

$canonicalUrl = "https://www.rtchocos.com/failed-batch/" . rawurlencode($fail['slug']);

$breadcrumbs = [
    ['name' => 'Home', 'item' => 'https://www.rtchocos.com/'],
    ['name' => 'Innovation Lab', 'item' => 'https://www.rtchocos.com/innovation-lab.php'],
    ['name' => 'Failed Batches', 'item' => 'https://www.rtchocos.com/innovation-lab.php#failed-experiments'],
    ['name' => $fail['title'], 'item' => $canonicalUrl]
];

// Fetch other case studies / lab experiments for bottom grid
$relatedStmt = $pdo->prepare("SELECT id, title, slug, badge_label, image_path, hypothesis, what_happened FROM lab_failed_batches WHERE id != ? AND is_active = 1 ORDER BY sort_order ASC, id DESC LIMIT 3");
$relatedStmt->execute([$fail['id']]);
$relatedFailures = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

if (count($relatedFailures) < 3) {
    // Fill up with active lab experiments if fewer than 3 failed batches
    $needed = 3 - count($relatedFailures);
    $expStmt = $pdo->query("SELECT id, title, slug, category, status_badge, status_color, image_path, description FROM lab_experiments WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT " . (int)$needed);
    $extraExp = $expStmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $extraExp = [];
}

// Full Markdown / HTML Parser
$headings_list = [];

function parse_fail_inline($text) {
    global $pathPrefix;
    if (!$text) return '';
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace_callback('/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|wide)\})?/', function($matches) use ($pathPrefix) {
        $caption = $matches[1];
        $url = $matches[2];
        $pos = !empty($matches[3]) ? $matches[3] : 'center';
        $resolvedSrc = (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0 || strpos($url, '../') === 0)
            ? $url 
            : $pathPrefix . $url;
        return "<figure class=\"blog-figure blog-figure-{$pos}\"><img src=\"" . htmlspecialchars($resolvedSrc) . "\" alt=\"" . htmlspecialchars($caption) . "\" loading=\"lazy\" decoding=\"async\" onclick=\"openLightbox(this.src)\" style=\"cursor:zoom-in;\">" . ($caption ? "<figcaption>" . htmlspecialchars($caption) . "</figcaption>" : "") . "</figure>";
    }, $text);
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2">$1</a>', $text);
    return $text;
}

function parse_fail_markdown($markdown) {
    global $headings_list;
    $headings_list = [];
    if (!trim((string)$markdown)) return '';
    
    $html = str_replace(["\r\n", "\r"], "\n", $markdown);

    // Strip editor helper artifacts
    $html = preg_replace('/<div class="editor-(?:table-topbar|figure-topbar|table-resize-handle|resize-handle|block-drag-handle|drop-line|col-guide|size-badge)"[^>]*>.*?<\/div>/is', '', $html);
    $html = preg_replace('/<div class="topbar-actions"[^>]*>.*?<\/div>/is', '', $html);
    $html = preg_replace('/<button\b[^>]*\b(?:data-action|topbar-btn)[^>]*>.*?<\/button>/is', '', $html);
    $html = preg_replace('/\s*contenteditable=[\'"][^\'"]*[\'"]/i', '', $html);

    // Process Headings and auto-populate TOC
    $html = preg_replace_callback('/<h([1-4])\b([^>]*)>(.*?)<\/h\1>/is', function($matches) use (&$headings_list) {
        $level = (int)$matches[1];
        $attrs = $matches[2];
        $rawText = strip_tags($matches[3]);
        $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $rawText), '-'));
        $id = $id ?: ('sec-' . count($headings_list));
        $headings_list[] = [
            'id' => $id,
            'title' => $rawText,
            'level' => $level
        ];
        return "<h{$level} id=\"{$id}\"{$attrs}>{$matches[3]}</h{$level}>";
    }, $html);

    $html = preg_replace_callback('/^(#{1,4})\s+(.+)$/m', function($matches) use (&$headings_list) {
        $level = strlen($matches[1]);
        $rawText = strip_tags($matches[2]);
        $id = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $rawText), '-'));
        $id = $id ?: ('sec-' . count($headings_list));
        $headings_list[] = [
            'id' => $id,
            'title' => $rawText,
            'level' => $level
        ];
        $title = parse_fail_inline($matches[2]);
        return "<h{$level} id=\"{$id}\">{$title}</h{$level}>";
    }, $html);

    // Convert paragraphs
    $blocks = preg_split('/\n{2,}/', $html);
    $parsedBlocks = [];
    foreach ($blocks as $block) {
        $b = trim($block);
        if (!$b) continue;
        if (preg_match('/^<(?:h[1-6]|div|table|blockquote|figure|ul|ol|p|hr)\b/i', $b)) {
            $parsedBlocks[] = $b;
        } else {
            $parsedBlocks[] = '<p>' . parse_fail_inline($b) . '</p>';
        }
    }
    return implode("\n\n", $parsedBlocks);
}

// Assemble Structured Post-Mortem Article Content
$fullMarkdown = '';

$fullMarkdown .= "## The Research Hypothesis\n\n";
$fullMarkdown .= !empty($fail['hypothesis']) ? $fail['hypothesis'] : "We set out to formulate an ambitious batch testing novel fruit, fat, or botanical interactions in artisanal single-origin chocolate.";
$fullMarkdown .= "\n\n";

$fullMarkdown .= "## What Went Wrong (The Anomaly)\n\n";
$fullMarkdown .= !empty($fail['what_happened']) ? $fail['what_happened'] : "During processing and stone-refining, unexpected rheological changes and sensory breakdown were recorded.";
$fullMarkdown .= "\n\n";

$fullMarkdown .= "## Why It Happened (The Chocolate Chemistry)\n\n";
$fullMarkdown .= !empty($fail['why_it_happened']) ? $fail['why_it_happened'] : "Analysis indicated lipid-phase moisture migration and particle surface energy changes.";
$fullMarkdown .= "\n\n";

$fullMarkdown .= "## The Breakthrough & What We Learned\n\n";
$fullMarkdown .= !empty($fail['what_next']) ? $fail['what_next'] : "Through careful adjustments, we transformed this laboratory failure into an actionable recipe standard.";
$fullMarkdown .= "\n\n";

if (!empty($fail['full_story']) && trim($fail['full_story'])) {
    $fullMarkdown .= "## Behind the Scenes: Field Notes & Full Story\n\n";
    $fullMarkdown .= $fail['full_story'] . "\n\n";
}

if (!empty($failSpecs['correctiveChecklist'])) {
    $fullMarkdown .= "## Preventative Protocols & Future Batch Standards\n\n";
    foreach ($failSpecs['correctiveChecklist'] as $rule) {
        $fullMarkdown .= "- **Standard Operating Rule:** " . $rule . "\n";
    }
    $fullMarkdown .= "\n";
}

$articleContent = parse_fail_markdown($fullMarkdown);

$caseCode = htmlspecialchars($failSpecs['batchRef'] ?? ('FAIL-CASE-0' . $fail['id']));
$rawImg = !empty($fail['image_path']) ? $fail['image_path'] : 'assets/images/lab-failed-batch.jpg';
$resolvedImg = (strpos($rawImg, 'http') === 0 || strpos($rawImg, '/') === 0 || strpos($rawImg, '../') === 0) ? $rawImg : ($pathPrefix . $rawImg);

include 'includes/header.php';
?>

<!-- Reading Scroll Progress Bar -->
<div id="readingProgressBar" style="position:fixed; top:0; left:0; width:0; height:4px; background:var(--gold, #C7A66A); z-index:99999; transition:width 0.1s ease;"></div>

<style>
/* =========================================================================
   LUXURY FAILED BATCH EDITORIAL HERO STYLES (1140px Aligned Grid)
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
    background: rgba(180, 83, 9, 0.1);
    color: #B45309;
    border: 1px solid rgba(180, 83, 9, 0.3);
}

.exp-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    background: #B45309;
    box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.25);
    animation: expPulseAmber 2s infinite;
}

@keyframes expPulseAmber {
    0%, 100% { box-shadow: 0 0 0 3px rgba(180, 83, 9, 0.25); }
    50% { box-shadow: 0 0 0 6px rgba(180, 83, 9, 0.1); }
}

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

/* Luxury Editorial Typography */
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
    margin-right: 6px;
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
    scroll-margin-top: 100px;
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

/* Layout Wrapper */
.blog-article-section {
    max-width: 1140px;
    margin: 0 auto;
    padding: 36px 24px 80px;
    box-sizing: border-box;
}

.article-layout-wrapper {
    display: flex;
    gap: 44px;
    align-items: flex-start;
}

.article-main-body {
    flex: 1;
    min-width: 0;
}

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

/* Related Cards Grid */
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
                <span>This case study is currently <strong>HIDDEN (Draft)</strong> from public visitors.</span>
            </div>
            <a href="<?php echo $pathPrefix; ?>admin/innovation-lab.php#failed-batches" style="background:var(--gold, #C7A66A); color:#111; font-size:12.5px; font-weight:700; padding:6px 14px; border-radius:6px; text-decoration:none;">
                Edit in Admin ↗
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- LAB EXPERIMENT ARTICLE SECTION -->
<div id="page-blog-article" class="page active">

  <!-- 1. EDITORIAL HERO (1140px Aligned Grid) -->
  <section class="exp-hero-section">
    <div class="exp-hero-inner">
      
      <!-- Top Utility Bar: Back Navigation (Left) + Badges (Right) -->
      <div class="exp-nav-bar">
        <a class="exp-back-btn" href="<?php echo $pathPrefix; ?>innovation-lab.php#failed-experiments">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
          <span>Back to Innovation Lab</span>
        </a>
        
        <div class="exp-hero-badges">
          <span class="exp-cat-badge">FAILED BATCH CASE STUDY</span>
          <span class="exp-status-pill">
            <span class="exp-status-dot"></span>
            <span><?php echo htmlspecialchars($fail['badge_label'] ?: 'LESSON LEARNED'); ?></span>
          </span>
        </div>
      </div>

      <!-- Main Headline & Editorial Deck -->
      <div class="exp-hero-text">
        <h1 class="exp-main-title"><?php echo htmlspecialchars($fail['title']); ?></h1>
        
        <?php if (!empty($fail['hypothesis'])): ?>
          <p class="exp-lead-deck"><?php echo htmlspecialchars(trim(strip_tags($fail['hypothesis']))); ?></p>
        <?php endif; ?>

        <!-- Streamlined Metadata Bar -->
        <div class="exp-meta-strip">
          <span class="exp-meta-batch">#<?php echo htmlspecialchars($caseCode); ?></span>
          <span class="exp-meta-sep">•</span>
          <span>Logged <?php echo date('M Y', strtotime($fail['created_at'])); ?></span>
          <span class="exp-meta-sep">•</span>
          <span>4 min read</span>
          <span class="exp-meta-sep">•</span>
          <span>RT Chocos Kitchen Post-Mortem</span>
          <?php if ($isAdmin): ?>
            <span class="exp-meta-sep">•</span>
            <a href="<?php echo $pathPrefix; ?>admin/innovation-lab.php#failed-batches" class="exp-admin-link">✏️ Edit in Admin</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- High-Resolution Cover Image (Full 1140px Grid Alignment) -->
      <?php if (!empty($rawImg)): ?>
        <div class="exp-hero-cover-frame" onclick="openLightbox('<?php echo htmlspecialchars($resolvedImg); ?>')">
          <img id="blog-article-image" src="<?php echo htmlspecialchars($resolvedImg); ?>" alt="<?php echo htmlspecialchars($fail['title']); ?>" onerror="this.parentElement.style.display='none';" />
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
        
        <!-- Main body text pane -->
        <main class="article-main-body">
            
            <div id="blog-article-content">
                <?php echo $articleContent; ?>
            </div>
            
            <!-- Social Share Bar -->
            <?php 
                $articleUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'rtchocos.com') . ($_SERVER['REQUEST_URI'] ?? '');
                $shareTitle = $fail['title'] . ' | RT Chocos Innovation Lab';
            ?>
            <div class="share-container">
                <span class="share-title">Share Case Study:</span>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($articleUrl); ?>&text=<?php echo urlencode($shareTitle); ?>" target="_blank" class="share-btn tw" title="Share on Twitter">Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn fb" title="Share on Facebook">Facebook</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($articleUrl); ?>" target="_blank" class="share-btn ln" title="Share on LinkedIn">LinkedIn</a>
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($shareTitle . ' - ' . $articleUrl); ?>" target="_blank" class="share-btn wa" title="Share via WhatsApp">WhatsApp</a>
                <button type="button" class="share-btn copy" onclick="copyArticleLink()" title="Copy Link">Copy Link</button>
            </div>

            <!-- Author Bio Box -->
            <div style="background:#FAF7F2; border-radius:20px; padding:24px; margin-top:40px; margin-bottom:24px; box-shadow:0 4px 16px rgba(59,42,34,0.06); display:flex; gap:20px; align-items:center; flex-wrap:wrap;">
                <img src="<?php echo $pathPrefix; ?>assets/myphoto.jpg" alt="Aarti Saluja Sahni — founder and expert chocolate educator at RT Chocos India" style="width:90px; height:90px; border-radius:50%; object-fit:cover; border:3px solid #5C4033; flex-shrink:0;">
                <div style="flex:1; min-width:240px;">
                    <span style="font-size:11px; font-weight:600; text-transform:uppercase; color:var(--gold, #C7A66A); letter-spacing:0.08em; display:block; margin-bottom:4px;">Research &amp; Post-Mortem Formulation</span>
                    <h4 style="font-family:'Cormorant Garamond',serif; font-size:22px; font-weight:700; color:#3B2A22; margin:0 0 8px;">Aarti Saluja Sahni &amp; The RT Chocos Kitchen</h4>
                    <p style="font-family:var(--font-sans, sans-serif); font-size:13.5px; line-height:1.6; color:#5C4033; font-weight:300; margin:0 0 12px;">
                        Certified chocolate educator and formulation researcher testing bean-to-bar boundaries, tropical fat structures, and indigenous Indian botanical pairings.
                    </p>
                    <a href="<?php echo $pathPrefix; ?>innovation-lab.php" class="btn-outline" style="text-decoration:none; padding:6px 14px; font-size:12px; display:inline-block;">Explore Innovation Lab</a>
                </div>
            </div>

        </main>

        <!-- Sticky Sidebar Panel (TOC, Shop Promo) -->
        <aside class="article-sidebar" id="tocSidebar">
            
            <!-- Table of Contents -->
            <?php if (!empty($headings_list)): ?>
                <div class="toc-box">
                    <div class="toc-title">On this page</div>
                    <ul class="toc-list" id="tocList">
                        <?php foreach ($headings_list as $h): ?>
                            <li class="toc-item <?php echo $h['level'] === 3 ? 'indent-h3' : ''; ?>">
                                <a href="#<?php echo htmlspecialchars($h['id']); ?>"><?php echo htmlspecialchars($h['title']); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Chocolate Boutique Callout -->
            <div class="exp-sidebar-callout">
                <h4>Taste Tested Recipes</h4>
                <p>Every breakthrough from our failed batches directly powers the artisanal bars in our boutique.</p>
                <a href="<?php echo $pathPrefix; ?>shop.php">
                    <span>Explore Our Boutique</span>
                    <span>&rarr;</span>
                </a>
            </div>

        </aside>

    </div>
  </div>

  <!-- 3. RELATED RESEARCH / EXPERIMENTS SECTION -->
  <div style="background:#FAF8F5; border-top:1px solid #EDE7DB; padding:64px 24px; margin-top:40px;">
    <div style="max-width:1140px; margin:0 auto;">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:32px; flex-wrap:wrap; gap:16px;">
            <div>
                <span style="font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:var(--gold, #C7A66A); display:block; margin-bottom:4px;">Scientific Continuity</span>
                <h3 style="font-family:'Playfair Display', Georgia, serif; font-size:28px; font-weight:700; color:#122217; margin:0;">More Lab Case Studies &amp; Research</h3>
            </div>
            <a href="<?php echo $pathPrefix; ?>innovation-lab.php#failed-experiments" style="font-size:12px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color:var(--gold, #C7A66A); text-decoration:none;">View All In Lab &rarr;</a>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:28px;">
            <?php foreach ($relatedFailures as $rf): 
                $relImg = !empty($rf['image_path']) ? $rf['image_path'] : 'assets/images/lab-failed-batch.jpg';
                $relImgUrl = (strpos($relImg, 'http') === 0 || strpos($relImg, '/') === 0 || strpos($relImg, '../') === 0) ? $relImg : ($pathPrefix . $relImg);
                $relLink = $pathPrefix . "failed-batch/" . $rf['slug'];
            ?>
                <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                    <div style="height:190px; overflow:hidden; background:#ECE4DA;">
                        <img src="<?php echo htmlspecialchars($relImgUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" onerror="this.src='<?php echo $pathPrefix; ?>assets/images/placeholder.jpg';">
                    </div>
                    <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
                        <span style="font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:#B45309; margin-bottom:6px;">CASE STUDY</span>
                        <h4 style="font-family:'Playfair Display', Georgia, serif; font-size:18px; font-weight:700; color:#122217; margin:0 0 8px; line-height:1.3;"><?php echo htmlspecialchars($rf['title']); ?></h4>
                        <p style="font-size:13px; color:#5C4033; line-height:1.55; margin:0; flex:1;"><?php echo htmlspecialchars(mb_strimwidth($rf['hypothesis'] ?: '', 0, 110, '...')); ?></p>
                        <div style="margin-top:14px; font-size:11.5px; font-weight:700; color:var(--gold, #C7A66A); display:flex; align-items:center; gap:4px;">
                            <span>Read Case Study</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php foreach ($extraExp as $ee): 
                $relImg = !empty($ee['image_path']) ? $ee['image_path'] : 'assets/images/placeholder.jpg';
                $relImgUrl = (strpos($relImg, 'http') === 0 || strpos($relImg, '/') === 0 || strpos($relImg, '../') === 0) ? $relImg : ($pathPrefix . $relImg);
                $relLink = $pathPrefix . "experiment/" . $ee['slug'];
            ?>
                <a href="<?php echo htmlspecialchars($relLink); ?>" class="related-card">
                    <div style="height:190px; overflow:hidden; background:#ECE4DA;">
                        <img src="<?php echo htmlspecialchars($relImgUrl); ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy" onerror="this.src='<?php echo $pathPrefix; ?>assets/images/placeholder.jpg';">
                    </div>
                    <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
                        <span style="font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:var(--gold, #C7A66A); margin-bottom:6px;"><?php echo htmlspecialchars($ee['category']); ?></span>
                        <h4 style="font-family:'Playfair Display', Georgia, serif; font-size:18px; font-weight:700; color:#122217; margin:0 0 8px; line-height:1.3;"><?php echo htmlspecialchars($ee['title']); ?></h4>
                        <p style="font-size:13px; color:#5C4033; line-height:1.55; margin:0; flex:1;"><?php echo htmlspecialchars(mb_strimwidth($ee['description'] ?: '', 0, 110, '...')); ?></p>
                        <div style="margin-top:14px; font-size:11.5px; font-weight:700; color:var(--gold, #C7A66A); display:flex; align-items:center; gap:4px;">
                            <span>Explore Experiment</span>
                            <span>&rarr;</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
  </div>

</div>

<!-- Lightbox Modal -->
<div id="lightboxModal" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="Enlarged view">
</div>

<script>
// Reading Scroll Progress Indicator
window.addEventListener('scroll', function() {
    const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const scrolled = (height > 0) ? (winScroll / height) * 100 : 0;
    const bar = document.getElementById('readingProgressBar');
    if (bar) bar.style.width = scrolled + '%';
});

// Lightbox helper
function openLightbox(src) {
    if (!src) return;
    const modal = document.getElementById('lightboxModal');
    const img = document.getElementById('lightboxImg');
    if (modal && img) {
        img.src = src;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}
function closeLightbox() {
    const modal = document.getElementById('lightboxModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Copy Link Toast
function copyArticleLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        if (typeof showToast === 'function') {
            showToast('Case study link copied to clipboard!');
        } else {
            alert('Case study link copied to clipboard!');
        }
    }).catch(function() {
        prompt('Copy this link:', url);
    });
}

// Table of Contents Active Spy
document.addEventListener('DOMContentLoaded', function() {
    const tocLinks = document.querySelectorAll('.toc-list a');
    if (tocLinks.length === 0) return;

    const sections = [];
    tocLinks.forEach(link => {
        const id = link.getAttribute('href').replace('#', '');
        const target = document.getElementById(id);
        if (target) sections.push({ id, target, link: link.parentElement });
    });

    window.addEventListener('scroll', function() {
        const scrollPos = window.scrollY + 140;
        let current = null;
        for (let i = 0; i < sections.length; i++) {
            if (sections[i].target.offsetTop <= scrollPos) {
                current = sections[i];
            }
        }
        sections.forEach(s => s.link.classList.remove('active'));
        if (current) current.link.classList.add('active');
    }, { passive: true });
});
</script>

<?php include 'includes/footer.php'; ?>

