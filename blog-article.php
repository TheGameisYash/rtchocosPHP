<?php
require_once __DIR__ . '/includes/blog-data.php';
require_once __DIR__ . '/includes/db.php';

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
        }
    } catch (Exception $e) {
        error_log("Database error fetching blog '$articleKey': " . $e->getMessage());
    }
}

// Fallback to static blog data array
if (!$post) {
    if (!isset($articleKey) || !isset($BLOGS[$articleKey])) {
        header('Location: ../blog.php');
        exit;
    }
    $post = $BLOGS[$articleKey];
}

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
            $html .= "<h{$level}>{$content}</h{$level}>\n";
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

// Load markdown content from file if not loaded from DB
if (!$isFromDb) {
    $markdown_file = __DIR__ . '/blog/posts/' . $articleKey . '.md';
    $markdown_content = file_exists($markdown_file) ? file_get_contents($markdown_file) : '';
}

include __DIR__ . '/includes/header.php';
?>


<!-- --- BLOG ARTICLE SECTION --- -->
<div id="page-blog-article" class="page active">
  <div class="page-hero blog-page-hero">
    <div class="page-hero-content blog-article-hero-content">
      <a class="btn-outline-dark back-to-blog-btn" href="../blog.php">&larr; Back to Blog</a>
      <div class="section-label" id="blog-article-category"><?php echo htmlspecialchars($post['category']); ?></div>
      <?php if (!empty($post['image'])): ?>
      <img id="blog-article-image" src="../<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
      <?php endif; ?>
      <h1 id="blog-article-title" class="fade-up"><?php echo htmlspecialchars($post['title']); ?></h1>
      <p id="blog-article-meta" class="fade-up-d1"><?php echo htmlspecialchars($post['date']); ?> • <?php echo htmlspecialchars($post['read']); ?> read</p>
    </div>
  </div>
  <div class="section blog-article-section">
    <!-- If there is a YouTube video, render embed below header and above content -->
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
            <div class="blog-video-embed">
                <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>" 
                        frameborder="0" allowfullscreen
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                </iframe>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div id="blog-article-content">
      <?php echo parse_markdown($markdown_content); ?>
    </div>
    
    <?php include __DIR__ . '/includes/comments.php'; ?>
  </div>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
<script>
// Initialize comments for this article page.
// currentBlogArticleId is used by submitBlogComment() and renderBlogComments() in script.js.
// On PHP-rendered article pages the SPA never runs openBlogArticle(), so we set it here.
(function() {
  var articleKey = <?php echo json_encode($articleKey ?? ''); ?>;
  if (articleKey) {
    currentBlogArticleId = articleKey;
    renderBlogComments();
  }
})();
</script>
