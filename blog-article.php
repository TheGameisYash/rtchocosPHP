<?php
require_once 'includes/blog-data.php';

if (!isset($articleKey) || !isset($BLOGS[$articleKey])) {
    header('Location: ../blog.php');
    exit;
}

$post = $BLOGS[$articleKey];
$pageTitle = $post['title'] . " | RT Chocos";
$pageDescription = $post['excerpt'];
$bodyClass = $post['bodyClass'] ?? '';
$pathPrefix = "../";

// Custom markdown parsing function
function parse_markdown($markdown) {
    // Standardize line breaks
    $markdown = str_replace(array("\r\n", "\r"), "\n", $markdown);
    
    // Split by double newlines to find paragraphs and blocks
    $blocks = explode("\n\n", $markdown);
    $html = '';
    
    foreach ($blocks as $block) {
        $block = trim($block);
        if (empty($block)) continue;
        
        // Headers
        if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $matches)) {
            $level = strlen($matches[1]);
            $content = parse_inline($matches[2]);
            $html .= "<h{$level} style=\"font-size:" . ($level == 2 ? "32px" : "24px") . ";line-height:1.25;margin-top:18px;\">{$content}</h{$level}>\n";
            continue;
        }
        
        // Blockquotes
        if (strpos($block, '> ') === 0) {
            $lines = explode("\n", $block);
            $quoteContent = '';
            foreach ($lines as $line) {
                $quoteContent .= substr($line, 2) . "\n";
            }
            $content = parse_inline(trim($quoteContent));
            $html .= "<blockquote><p>{$content}</p></blockquote>\n";
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
        
        // If it starts with an HTML block tag (like div, img, hr, iframe, p)
        if (preg_match('/^<(div|img|hr|p|section|a|span|h\d|table|tr|td|th)/i', $block)) {
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
    // Bold: **text**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    // Italic: *text* or _text_
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
    $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);
    return $text;
}

// Load markdown content
$markdown_file = __DIR__ . '/blog/posts/' . $articleKey . '.md';
$markdown_content = file_exists($markdown_file) ? file_get_contents($markdown_file) : '';

include $pathPrefix . 'includes/header.php';
?>

<!-- --- BLOG ARTICLE SECTION --- -->
<div id="page-blog-article" class="page active" style="padding-top:72px;">
  <div class="page-hero blog-page-hero" style="min-height:320px;">
    <div class="page-hero-content" style="max-width:860px;text-align:left;">
      <a class="btn-outline-dark" href="../blog.php" style="margin-bottom:20px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">&larr; Back to Blog</a>
      <div class="section-label" id="blog-article-category"><?php echo htmlspecialchars($post['category']); ?></div>
      <?php if (!empty($post['image'])): ?>
      <img id="blog-article-image" src="../<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" style="display:block;width:100%;max-width:720px;border-radius:20px;box-shadow:0 18px 50px rgba(59,42,34,0.18);margin:8px 0 20px;" />
      <?php endif; ?>
      <h1 id="blog-article-title" class="fade-up" style="max-width:14ch;"><?php echo htmlspecialchars($post['title']); ?></h1>
      <p id="blog-article-meta" class="fade-up-d1" style="max-width:none;"><?php echo htmlspecialchars($post['date']); ?> • <?php echo htmlspecialchars($post['read']); ?> read</p>
    </div>
  </div>
  <div class="section" style="max-width:860px;">
    <div id="blog-article-content" style="display:grid;gap:18px;font-size:18px;line-height:1.9;color:var(--brown);">
      <?php echo parse_markdown($markdown_content); ?>
    </div>
    
    <?php include $pathPrefix . 'includes/comments.php'; ?>
  </div>
</div>

<?php
include $pathPrefix . 'includes/footer.php';
?>
