<?php
// experiment.php — Dedicated Single Experiment Page for RT Chocos Innovation Lab
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/admin/auth.php';

$slug = $_GET['slug'] ?? '';
$slug = trim($slug);

if (empty($slug)) {
    header('Location: innovation-lab.php');
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
    $pathPrefix = "./";
    if (file_exists(__DIR__ . '/error.php')) {
        include __DIR__ . '/error.php';
    } else {
        echo "<h1>404 — Experiment Not Found</h1><p><a href='innovation-lab.php'>Return to Innovation Lab</a></p>";
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
        $pathPrefix = "./";
        if (file_exists(__DIR__ . '/error.php')) {
            include __DIR__ . '/error.php';
        } else {
            echo "<h1>404 — Experiment Not Found</h1><p><a href='innovation-lab.php'>Return to Innovation Lab</a></p>";
        }
        exit;
    }
}

// SEO & Header Variables
$pageTitle = htmlspecialchars($exp['title']) . " | RT Chocos Innovation Lab";
$pageDescription = htmlspecialchars($exp['description'] ?: 'Active confectionery research and chocolate formulation experiment from the RT Chocos kitchen.');
$pageImage = $exp['image_path'] ?: 'assets/images/placeholder.jpg';
$pageType = 'article';
$pathPrefix = "./";

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

    // If already contains HTML tags, sanitize scripts/styles and return
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
            $html .= "<h{$lvl}>" . format_exp_inline($m[2]) . "</h{$lvl}>\n";
            continue;
        }

        // Blockquotes
        if (strpos($trimmed, '> ') === 0) {
            $html .= "<blockquote>" . format_exp_inline(substr($trimmed, 2)) . "</blockquote>\n";
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
$imageUrl = !empty($exp['image_path']) ? htmlspecialchars($exp['image_path']) : 'assets/images/placeholder.jpg';

include 'includes/header.php';
?>

<style>
/* =========================================================================
   DEDICATED EXPERIMENT ARTICLE STYLING (Inspired by blog-article.php)
   ========================================================================= */
.exp-page-wrapper {
    background-color: var(--bg-main, #FAF7F2);
    min-height: 100vh;
    padding-top: 96px;
    padding-bottom: 80px;
    color: var(--text-main, #1F2E25);
    font-family: var(--font-sans, 'Inter', -apple-system, sans-serif);
}

.exp-container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

/* Admin Draft Preview Alert Bar */
.exp-admin-bar {
    background: #FEF3C7;
    border: 1px solid #F59E0B;
    color: #92400E;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 13.5px;
    font-weight: 600;
}

/* Breadcrumbs */
.exp-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #6C7E72;
    margin-bottom: 24px;
    flex-wrap: wrap;
}
.exp-breadcrumbs a {
    color: #6C7E72;
    text-decoration: none;
    transition: color 0.2s;
}
.exp-breadcrumbs a:hover {
    color: var(--gold, #C7A66A);
}
.exp-breadcrumbs .sep {
    color: #B8AFA2;
    font-size: 11px;
}
.exp-breadcrumbs .current {
    color: #122217;
    font-weight: 600;
}

/* Hero Header */
.exp-hero-header {
    margin-bottom: 40px;
}
.exp-meta-badges {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.exp-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(18, 34, 23, 0.06);
    border: 1px solid rgba(18, 34, 23, 0.12);
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #122217;
}
.exp-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}
.exp-status-dot.green { background: #16A34A; box-shadow: 0 0 6px rgba(22, 163, 74, 0.6); }
.exp-status-dot.amber { background: #D97706; box-shadow: 0 0 6px rgba(217, 119, 6, 0.6); }
.exp-status-dot.purple { background: #9333EA; box-shadow: 0 0 6px rgba(147, 51, 234, 0.6); }
.exp-status-dot.blue { background: #2563EB; box-shadow: 0 0 6px rgba(37, 99, 235, 0.6); }

.exp-category-tag {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #8C6D37;
    background: rgba(199, 166, 106, 0.14);
    padding: 4px 12px;
    border-radius: 999px;
}
.exp-batch-badge {
    font-size: 11.5px;
    font-family: monospace;
    font-weight: 700;
    color: #555;
    background: #EFE9DF;
    padding: 4px 10px;
    border-radius: 6px;
}

.exp-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(30px, 4.5vw, 48px);
    font-weight: 700;
    line-height: 1.2;
    color: #122217;
    margin: 0 0 16px;
    letter-spacing: -0.015em;
}

.exp-excerpt {
    font-size: clamp(16px, 1.3vw, 19px);
    line-height: 1.65;
    color: #495B4E;
    font-weight: 300;
    max-width: 900px;
    margin: 0 0 24px;
}

.exp-meta-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 0;
    border-top: 1px solid #E6DED2;
    border-bottom: 1px solid #E6DED2;
    font-size: 13px;
    color: #6C7E72;
    flex-wrap: wrap;
    gap: 16px;
}
.exp-meta-left {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.exp-meta-right {
    display: flex;
    align-items: center;
    gap: 12px;
}
.exp-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1px solid #D8CFC1;
    background: #FFFFFF;
    color: #122217;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.exp-action-btn:hover {
    border-color: #C7A66A;
    color: #8C6D37;
    background: #FAF7F2;
}

/* Two-Column Grid: Left Content (68%) & Right Specimen Sidebar (32%) */
.exp-layout-grid {
    display: grid;
    grid-template-columns: 1.9fr 1.1fr;
    gap: 40px;
    align-items: start;
    margin-bottom: 64px;
}

@media (max-width: 960px) {
    .exp-layout-grid {
        grid-template-columns: 1fr;
        gap: 32px;
    }
}

/* Left Column: Cover Image & 4 Discovery Sections */
.exp-cover-frame {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 36px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    background: #ECE4D7;
    aspect-ratio: 16 / 10;
}
.exp-cover-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.exp-cover-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(14, 10, 8, 0.85) 0%, rgba(14, 10, 8, 0) 100%);
    color: #FFFFFF;
    padding: 24px 20px 14px;
    font-size: 12.5px;
    font-weight: 500;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

/* 4 Core Discovery Blocks (Styled after the lab dossier) */
.exp-discovery-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 40px;
}

@media (max-width: 640px) {
    .exp-discovery-grid {
        grid-template-columns: 1fr;
    }
}

.exp-quad-card {
    background: #FFFFFF;
    border: 1px solid #E6DED2;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.exp-quad-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
}
.exp-quad-card.card-1::before { background: #16A34A; }
.exp-quad-card.card-2::before { background: #D97706; }
.exp-quad-card.card-3::before { background: #15803D; }
.exp-quad-card.card-4::before { background: #9A6A38; }

.exp-quad-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}
.exp-quad-num {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}
.exp-quad-card.card-1 .exp-quad-num { color: #16A34A; }
.exp-quad-card.card-2 .exp-quad-num { color: #D97706; }
.exp-quad-card.card-3 .exp-quad-num { color: #15803D; }
.exp-quad-card.card-4 .exp-quad-num { color: #9A6A38; }

.exp-quad-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
.exp-quad-card.card-1 .exp-quad-icon { background: rgba(22, 163, 74, 0.12); color: #16A34A; }
.exp-quad-card.card-2 .exp-quad-icon { background: rgba(217, 119, 6, 0.12); color: #D97706; }
.exp-quad-card.card-3 .exp-quad-icon { background: rgba(21, 128, 61, 0.12); color: #15803D; }
.exp-quad-card.card-4 .exp-quad-icon { background: rgba(154, 106, 56, 0.12); color: #9A6A38; }

.exp-quad-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    color: #122217;
    margin: 0 0 4px;
}
.exp-quad-prompt {
    font-size: 12px;
    color: #6C7E72;
    margin: 0 0 14px;
    line-height: 1.4;
}
.exp-quad-body {
    font-size: 14px;
    line-height: 1.65;
    color: #2D3A30;
    flex: 1;
}
.exp-quad-body p:last-child {
    margin-bottom: 0;
}

/* Full Detailed Recipe Notes Section */
.exp-notes-card {
    background: #FFFFFF;
    border: 1px solid #E6DED2;
    border-radius: 12px;
    padding: 36px 32px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
    margin-bottom: 40px;
}
.exp-notes-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1px solid #F0EAE1;
}
.exp-notes-header h3 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 22px;
    font-weight: 700;
    color: #122217;
    margin: 0;
}
.exp-notes-content {
    font-size: 15.5px;
    line-height: 1.8;
    color: #2D3A30;
}
.exp-notes-content p {
    margin-bottom: 18px;
}
.exp-notes-content h2, .exp-notes-content h3 {
    font-family: 'Playfair Display', Georgia, serif;
    color: #122217;
    margin-top: 28px;
    margin-bottom: 12px;
}
.exp-notes-content ul, .exp-notes-content ol {
    margin: 0 0 20px 24px;
    padding: 0;
}
.exp-notes-content li {
    margin-bottom: 8px;
}
.exp-notes-content blockquote {
    border-left: 3px solid #C7A66A;
    margin: 20px 0;
    padding: 12px 20px;
    background: rgba(199, 166, 106, 0.08);
    font-style: italic;
    color: #4A3E37;
}

/* Right Column: Specimen Passport & Quick Facts */
.exp-sidebar {
    position: sticky;
    top: 110px;
}
.exp-passport-card {
    background: #FFFFFF;
    border: 1px solid #E6DED2;
    border-radius: 12px;
    padding: 28px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    margin-bottom: 24px;
}
.exp-passport-top {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    padding-bottom: 14px;
    border-bottom: 1.5px solid #122217;
    margin-bottom: 20px;
}
.exp-passport-title {
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #122217;
}
.exp-passport-ref {
    font-family: monospace;
    font-size: 12px;
    font-weight: 700;
    color: #C7A66A;
}

.exp-passport-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 24px;
}
.exp-spec-item {
    background: #FAF7F2;
    border: 1px solid #EBE4D8;
    padding: 12px 14px;
    border-radius: 8px;
}
.exp-spec-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #7A8B7E;
    margin-bottom: 4px;
}
.exp-spec-value {
    font-size: 13.5px;
    font-weight: 700;
    color: #122217;
}

.exp-passport-footer {
    padding-top: 16px;
    border-top: 1px dashed #E2D9CC;
    font-size: 11.5px;
    color: #6C7E72;
    display: flex;
    align-items: center;
    gap: 8px;
}
.exp-passport-footer span.check {
    color: #16A34A;
    font-weight: 800;
}

/* Sidebar Callout Card */
.exp-sidebar-cta {
    background: #14251B;
    color: #FAF6EF;
    border-radius: 12px;
    padding: 28px;
    box-shadow: 0 10px 24px rgba(18, 34, 23, 0.15);
}
.exp-sidebar-cta h4 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 20px;
    color: #FFFFFF;
    margin: 0 0 10px;
}
.exp-sidebar-cta p {
    font-size: 13.5px;
    color: #A3B8AA;
    line-height: 1.6;
    margin: 0 0 20px;
}
.exp-sidebar-cta a {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #C7A66A;
    color: #122217;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: none;
    transition: background 0.2s;
}
.exp-sidebar-cta a:hover {
    background: #D8BA82;
}

/* Related Experiments Grid */
.exp-related-section {
    padding-top: 48px;
    border-top: 1px solid #E6DED2;
    margin-top: 48px;
}
.exp-related-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 28px;
}
.exp-related-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 26px;
    font-weight: 700;
    color: #122217;
    margin: 0;
}
.exp-related-link {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: #8C6D37;
    text-decoration: none;
}
.exp-related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
}
.exp-related-card {
    background: #FFFFFF;
    border: 1px solid #E6DED2;
    border-radius: 10px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.exp-related-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(18, 34, 23, 0.1);
    border-color: #CFBEA5;
}
.exp-related-img {
    aspect-ratio: 16 / 10;
    background: #ECE4D7;
    position: relative;
    overflow: hidden;
}
.exp-related-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.exp-related-card:hover .exp-related-img img {
    transform: scale(1.05);
}
.exp-related-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.exp-related-cat {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #8C6D37;
    margin-bottom: 6px;
}
.exp-related-card-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 17px;
    font-weight: 700;
    color: #122217;
    margin: 0 0 8px;
    line-height: 1.35;
}
.exp-related-desc {
    font-size: 12.5px;
    line-height: 1.55;
    color: #555;
    margin-bottom: 14px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}
.exp-related-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid #F0EAE1;
    font-size: 11px;
    font-weight: 700;
    color: #8C6D37;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
</style>

<div class="exp-page-wrapper">
    <div class="exp-container">

        <?php if ($isAdminPreview): ?>
            <div class="exp-admin-bar">
                <span>🔒 <strong>Admin Draft Preview</strong> — This experiment is currently <em>Hidden</em> from public visitors.</span>
                <a href="admin/experiment-editor.php?id=<?php echo $exp['id']; ?>" class="exp-action-btn" style="background:#FFF;">Edit in Admin ↗</a>
            </div>
        <?php endif; ?>

        <!-- Breadcrumbs -->
        <nav class="exp-breadcrumbs" aria-label="Breadcrumbs">
            <a href="index.php">Home</a>
            <span class="sep">/</span>
            <a href="innovation-lab.php">Innovation Lab</a>
            <span class="sep">/</span>
            <span class="current"><?php echo htmlspecialchars($exp['title']); ?></span>
        </nav>

        <!-- Experiment Hero Header -->
        <header class="exp-hero-header">
            <div class="exp-meta-badges">
                <span class="exp-status-pill">
                    <span class="exp-status-dot <?php echo $badgeColor; ?>"></span>
                    <span><?php echo htmlspecialchars($exp['status_badge'] ?: 'IN PROGRESS'); ?></span>
                </span>
                <span class="exp-category-tag"><?php echo htmlspecialchars($exp['category']); ?></span>
                <span class="exp-batch-badge">BATCH #<?php echo $batchCode; ?></span>
            </div>

            <h1 class="exp-title"><?php echo htmlspecialchars($exp['title']); ?></h1>
            <p class="exp-excerpt"><?php echo htmlspecialchars($exp['description']); ?></p>

            <div class="exp-meta-bar">
                <div class="exp-meta-left">
                    <span><strong>Kitchen:</strong> RT Chocos Innovation Lab</span>
                    <span><strong>Target:</strong> <?php echo htmlspecialchars($exp['tags'] ?: 'Formulation Trials'); ?></span>
                    <span><strong>Logged:</strong> <?php echo date('M d, Y', strtotime($exp['created_at'])); ?></span>
                </div>
                <div class="exp-meta-right">
                    <button type="button" class="exp-action-btn" onclick="copyExpLink()">
                        <span>🔗</span>
                        <span>Copy Link</span>
                    </button>
                    <button type="button" class="exp-action-btn" onclick="window.print()">
                        <span>🖨️</span>
                        <span>Print Dossier</span>
                    </button>
                    <?php if ($isAdmin): ?>
                        <a href="admin/experiment-editor.php?id=<?php echo $exp['id']; ?>" class="exp-action-btn" style="color:#C7A66A;">
                            <span>✏️</span>
                            <span>Edit</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- Main Layout Grid: Content + Specimen Sidebar -->
        <div class="exp-layout-grid">
            
            <!-- Left Column: Photographic Frame & 4 Core Editorial Discovery Sections -->
            <main>
                <!-- Cover Image -->
                <div class="exp-cover-frame">
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" onerror="this.src='assets/images/placeholder.jpg';">
                    <div class="exp-cover-caption">
                        <span>🔬 SPECIMEN SAMPLE · <?php echo $batchCode; ?></span>
                        <span><?php echo htmlspecialchars($exp['category']); ?></span>
                    </div>
                </div>

                <!-- 4 Core Discovery Blocks -->
                <div class="exp-discovery-grid">
                    
                    <!-- 01. Research Question -->
                    <div class="exp-quad-card card-1">
                        <div class="exp-quad-top">
                            <span class="exp-quad-num">01</span>
                            <span class="exp-quad-icon">🧪</span>
                        </div>
                        <h3 class="exp-quad-title">Research Question</h3>
                        <p class="exp-quad-prompt">What question are we trying to answer in this trial?</p>
                        <div class="exp-quad-body">
                            <?php echo render_exp_content($exp['hypothesis'] ?: $exp['description']); ?>
                        </div>
                    </div>

                    <!-- 02. What We Discovered -->
                    <div class="exp-quad-card card-2">
                        <div class="exp-quad-top">
                            <span class="exp-quad-num">02</span>
                            <span class="exp-quad-icon">🔍</span>
                        </div>
                        <h3 class="exp-quad-title">What We Discovered</h3>
                        <p class="exp-quad-prompt">What happened when we made and tasted the chocolate?</p>
                        <div class="exp-quad-body">
                            <?php echo render_exp_content($exp['findings'] ?: 'Sensory observations and crystallization curves recorded in our kitchen logbook.'); ?>
                        </div>
                    </div>

                    <!-- 03. Why It Matters -->
                    <div class="exp-quad-card card-3">
                        <div class="exp-quad-top">
                            <span class="exp-quad-num">03</span>
                            <span class="exp-quad-icon">💡</span>
                        </div>
                        <h3 class="exp-quad-title">Why It Matters</h3>
                        <p class="exp-quad-prompt">How this helps make better, more delicious chocolate.</p>
                        <div class="exp-quad-body">
                            <?php echo render_exp_content($exp['methodology'] ?: 'Translating trial data directly into production recipes and heat stability.'); ?>
                        </div>
                    </div>

                    <!-- 04. Key Takeaways -->
                    <div class="exp-quad-card card-4">
                        <div class="exp-quad-top">
                            <span class="exp-quad-num">04</span>
                            <span class="exp-quad-icon">🎯</span>
                        </div>
                        <h3 class="exp-quad-title">Key Takeaways</h3>
                        <p class="exp-quad-prompt">The main lesson and formula takeaway from this test.</p>
                        <div class="exp-quad-body">
                            <?php echo render_exp_content($exp['next_steps'] ?: 'Validated recipe parameters ready for small-batch confectionery batches.'); ?>
                        </div>
                    </div>

                </div>

                <!-- Optional Full In-Depth Laboratory Notes & Tasting Log -->
                <?php if (!empty($exp['full_content']) && trim($exp['full_content'])): ?>
                    <div class="exp-notes-card">
                        <div class="exp-notes-header">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C7A66A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            <h3>Detailed Recipe Notes &amp; Tasting Log</h3>
                        </div>
                        <div class="exp-notes-content">
                            <?php echo render_exp_content($exp['full_content']); ?>
                        </div>
                    </div>
                <?php endif; ?>

            </main>

            <!-- Right Column: Specimen Passport & Sticky Sidebar -->
            <aside class="exp-sidebar">
                
                <!-- Specimen Passport -->
                <div class="exp-passport-card">
                    <div class="exp-passport-top">
                        <span class="exp-passport-title">Specimen Passport</span>
                        <span class="exp-passport-ref"><?php echo $batchCode; ?></span>
                    </div>

                    <div class="exp-passport-grid">
                        <div class="exp-spec-item">
                            <span class="exp-spec-label">Terroir Origin</span>
                            <span class="exp-spec-value"><?php echo htmlspecialchars($exp['origin'] ?: 'Idukki Estate, Kerala'); ?></span>
                        </div>
                        <div class="exp-spec-item">
                            <span class="exp-spec-label">Melt &amp; Snap</span>
                            <span class="exp-spec-value"><?php echo htmlspecialchars($exp['polymorph'] ?: 'Beta-V Matrix'); ?></span>
                        </div>
                        <div class="exp-spec-item">
                            <span class="exp-spec-label">Smoothness</span>
                            <span class="exp-spec-value"><?php echo htmlspecialchars($exp['particle_size'] ?: '< 18.2 µm'); ?></span>
                        </div>
                        <div class="exp-spec-item">
                            <span class="exp-spec-label">Moisture Level</span>
                            <span class="exp-spec-value"><?php echo htmlspecialchars($exp['water_activity'] ?: '0.31 Aw'); ?></span>
                        </div>
                    </div>

                    <div class="exp-passport-footer">
                        <span class="check">✓</span>
                        <span>Crafted &amp; Tested in India · Small Batch Kitchen Trials</span>
                    </div>
                </div>

                <!-- Callout Card -->
                <div class="exp-sidebar-cta">
                    <h4>Curious About Our Chocolate?</h4>
                    <p>Every chocolate bar we craft starts with experiments right here in our Innovation Lab. Taste the outcome of our culinary research.</p>
                    <a href="shop.php">
                        <span>Explore Chocolate Shop</span>
                        <span>→</span>
                    </a>
                </div>

            </aside>

        </div>

        <!-- Related Experiments Section -->
        <?php if (!empty($relatedExperiments)): ?>
            <section class="exp-related-section">
                <div class="exp-related-header">
                    <h2 class="exp-related-title">More Experiments From the Lab</h2>
                    <a href="innovation-lab.php#currently-in-lab" class="exp-related-link">View All Experiments &rarr;</a>
                </div>

                <div class="exp-related-grid">
                    <?php foreach ($relatedExperiments as $rel): 
                        $relBadgeColor = htmlspecialchars($rel['status_color'] ?: 'green');
                        $relImg = !empty($rel['image_path']) ? htmlspecialchars($rel['image_path']) : 'assets/images/placeholder.jpg';
                    ?>
                        <a href="experiment/<?php echo htmlspecialchars($rel['slug']); ?>" class="exp-related-card">
                            <div class="exp-related-img">
                                <img src="<?php echo $relImg; ?>" alt="<?php echo htmlspecialchars($rel['title']); ?>" loading="lazy" onerror="this.src='assets/images/placeholder.jpg';">
                            </div>
                            <div class="exp-related-body">
                                <span class="exp-related-cat"><?php echo htmlspecialchars($rel['category']); ?></span>
                                <h3 class="exp-related-card-title"><?php echo htmlspecialchars($rel['title']); ?></h3>
                                <p class="exp-related-desc"><?php echo htmlspecialchars($rel['description']); ?></p>
                                <div class="exp-related-footer">
                                    <span>Batch #<?php echo htmlspecialchars($rel['batch_ref'] ?: $rel['id']); ?></span>
                                    <span>Read Dossier &rarr;</span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

    </div>
</div>

<script>
function copyExpLink() {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('✓ Experiment link copied to clipboard!');
        }).catch(() => {
            prompt('Copy experiment link:', window.location.href);
        });
    } else {
        prompt('Copy experiment link:', window.location.href);
    }
}
</script>

<?php
include 'includes/footer.php';
?>
