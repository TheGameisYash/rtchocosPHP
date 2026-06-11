<?php
// admin/blog-editor.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

$blogId = (int)($_GET['id'] ?? 0);
$isEdit = $blogId > 0;

// Default empty values
$post = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'category' => 'Science',
    'excerpt' => '',
    'content' => '',
    'image_path' => '',
    'thumbnail_path' => '',
    'youtube_url' => '',
    'body_class' => '',
    'read_time' => '',
    'is_published' => 1
];

// If Edit, fetch from database
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->execute([$blogId]);
        $fetched = $stmt->fetch();
        if (!$fetched) {
            header('Location: blogs.php?error=' . urlencode('Blog post not found.'));
            exit;
        }
        $post = $fetched;
    } catch (Exception $e) {
        $error = 'Error fetching post: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $readTime = trim($_POST['read_time'] ?? '');
    $bodyClass = trim($_POST['body_class'] ?? '');
    $youtubeUrl = trim($_POST['youtube_url'] ?? '');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    // Build values to retain in form in case of error
    $post['title'] = $title;
    $post['slug'] = $slug;
    $post['category'] = $category;
    $post['excerpt'] = $excerpt;
    $post['content'] = $content;
    $post['read_time'] = $readTime;
    $post['body_class'] = $bodyClass;
    $post['youtube_url'] = $youtubeUrl;
    $post['is_published'] = $isPublished;

    if (!verify_csrf($csrfToken)) {
        $error = 'Invalid security token.';
    } elseif (empty($title) || empty($slug) || empty($excerpt) || empty($content)) {
        $error = 'Title, Slug, Excerpt, and Content are required fields.';
    } else {
        // Clean slug: remove spaces, lowercase, alphanumeric and dashes only
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($slug)));
        $post['slug'] = $slug;

        try {
            // Check if slug is unique
            $slugCheckSql = $isEdit ? "SELECT id FROM blogs WHERE slug = ? AND id != ?" : "SELECT id FROM blogs WHERE slug = ?";
            $slugCheckParams = $isEdit ? [$slug, $blogId] : [$slug];
            
            $stmt = $pdo->prepare($slugCheckSql);
            $stmt->execute($slugCheckParams);
            if ($stmt->fetch()) {
                $error = "The slug '{$slug}' is already in use by another post. Slugs must be unique.";
            } else {
                // Directories
                $blogsDir = __DIR__ . '/../assets/blogs';
                $thumbsDir = __DIR__ . '/../assets/blogs/thumbnails';
                if (!file_exists($blogsDir)) {
                    mkdir($blogsDir, 0755, true);
                }
                if (!file_exists($thumbsDir)) {
                    mkdir($thumbsDir, 0755, true);
                }

                $imagePath = $post['image_path'];
                $thumbnailPath = $post['thumbnail_path'];

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                // Handle Header Image Upload
                if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['header_image']['tmp_name'];
                    $fileName = $_FILES['header_image']['name'];
                    $fileSize = $_FILES['header_image']['size'];
                    $fileType = mime_content_type($fileTmpPath);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($fileType, $allowedMimeTypes)) {
                        $error = 'Header Image must be JPG, PNG or WEBP.';
                    } elseif ($fileSize > $maxFileSize) {
                        $error = 'Header Image file size must be less than 5MB.';
                    } else {
                        // Delete old file if exists
                        if (!empty($imagePath) && strpos($imagePath, 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $imagePath)) {
                            unlink(__DIR__ . '/../' . $imagePath);
                        }
                        // Save new file
                        $newFileName = $slug . '-header-' . time() . '.' . $fileExtension;
                        $destPath = $blogsDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $imagePath = 'assets/blogs/' . $newFileName;
                        } else {
                            $error = 'Failed to save Header Image.';
                        }
                    }
                }

                // Handle Thumbnail Image Upload
                if (empty($error) && isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['thumbnail_image']['tmp_name'];
                    $fileName = $_FILES['thumbnail_image']['name'];
                    $fileSize = $_FILES['thumbnail_image']['size'];
                    $fileType = mime_content_type($fileTmpPath);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    if (!in_array($fileType, $allowedMimeTypes)) {
                        $error = 'Thumbnail Image must be JPG, PNG or WEBP.';
                    } elseif ($fileSize > $maxFileSize) {
                        $error = 'Thumbnail Image file size must be less than 5MB.';
                    } else {
                        // Delete old file if exists
                        if (!empty($thumbnailPath) && strpos($thumbnailPath, 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $thumbnailPath)) {
                            unlink(__DIR__ . '/../' . $thumbnailPath);
                        }
                        // Save new file
                        $newFileName = $slug . '-thumb-' . time() . '.' . $fileExtension;
                        $destPath = $thumbsDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $thumbnailPath = 'assets/blogs/thumbnails/' . $newFileName;
                        } else {
                            $error = 'Failed to save Thumbnail Image.';
                        }
                    }
                }

                // Fallback: if thumbnail is empty but header image is set, use header image
                if (empty($thumbnailPath) && !empty($imagePath)) {
                    $thumbnailPath = $imagePath;
                }

                // If no error, proceed to Save/Update
                if (empty($error)) {
                    if ($isEdit) {
                        $stmt = $pdo->prepare("UPDATE blogs SET slug = ?, title = ?, category = ?, excerpt = ?, content = ?, image_path = ?, thumbnail_path = ?, youtube_url = ?, body_class = ?, read_time = ?, is_published = ? WHERE id = ?");
                        $stmt->execute([
                            $slug,
                            $title,
                            $category,
                            $excerpt,
                            $content,
                            $imagePath,
                            $thumbnailPath,
                            $youtubeUrl ?: null,
                            $bodyClass ?: null,
                            $readTime ?: null,
                            $isPublished,
                            $blogId
                        ]);
                        $success = 'Blog post updated successfully!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO blogs (slug, title, category, excerpt, content, image_path, thumbnail_path, youtube_url, body_class, read_time, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([
                            $slug,
                            $title,
                            $category,
                            $excerpt,
                            $content,
                            $imagePath,
                            $thumbnailPath,
                            $youtubeUrl ?: null,
                            $bodyClass ?: null,
                            $readTime ?: null,
                            $isPublished
                        ]);
                        
                        // Redirect to edit page of newly created post
                        $newId = $pdo->lastInsertId();
                        header('Location: blogs.php?success=' . urlencode('New blog post created successfully!'));
                        exit;
                    }
                    
                    // Reload post data
                    $post['image_path'] = $imagePath;
                    $post['thumbnail_path'] = $thumbnailPath;
                }
            }
        } catch (Exception $e) {
            $error = 'Save failed: ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf();
render_admin_header($isEdit ? "Edit Post" : "New Post", "blogs");
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<div style="margin-bottom: 24px;">
    <a href="blogs.php" class="btn btn-outline">&larr; Back to Blogs List</a>
</div>

<form action="blog-editor.php<?php echo $isEdit ? '?id=' . $blogId : ''; ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    
    <div class="editor-layout">
        <!-- Editor Controls (Left Panel) -->
        <div class="editor-card">
            <div class="editor-title">Post Settings & Content</div>
            
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required placeholder="e.g. Why pH is the Most Underrated Factor in Cocoa Powder">
            </div>

            <div class="form-group">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" class="form-control" value="<?php echo htmlspecialchars($post['slug']); ?>" required placeholder="e.g. cocoa-ph">
                <small style="color: rgba(59,42,34,0.6); display:block; margin-top:4px;">Only lowercase letters, numbers, and dashes. Auto-generated from Title.</small>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="Science" <?php echo $post['category'] === 'Science' ? 'selected' : ''; ?>>Science</option>
                        <option value="Beginner Guide" <?php echo $post['category'] === 'Beginner Guide' ? 'selected' : ''; ?>>Beginner Guide</option>
                        <option value="Recipe" <?php echo $post['category'] === 'Recipe' ? 'selected' : ''; ?>>Recipe</option>
                        <option value="Artisan" <?php echo $post['category'] === 'Artisan' ? 'selected' : ''; ?>>Artisan</option>
                        <option value="Business Tips" <?php echo $post['category'] === 'Business Tips' ? 'selected' : ''; ?>>Business Tips</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="read_time">Read Time</label>
                    <input type="text" id="read_time" name="read_time" class="form-control" value="<?php echo htmlspecialchars($post['read_time']); ?>" placeholder="e.g. 7 min">
                </div>
            </div>

            <div class="form-group">
                <label for="excerpt">Excerpt / Summary (SEO Meta Description)</label>
                <textarea id="excerpt" name="excerpt" rows="3" class="form-control" required placeholder="Provide a 1-2 sentence summary of the article. This appears on blog cards and Google results..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label for="body_class">Body CSS Class (Optional)</label>
                    <input type="text" id="body_class" name="body_class" class="form-control" value="<?php echo htmlspecialchars($post['body_class']); ?>" placeholder="e.g. fat-bloom-article">
                </div>
                <div class="form-group">
                    <label for="youtube_url">YouTube Video URL (Optional)</label>
                    <input type="url" id="youtube_url" name="youtube_url" class="form-control" value="<?php echo htmlspecialchars($post['youtube_url']); ?>" placeholder="e.g. https://www.youtube.com/watch?v=...">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div class="form-group">
                    <label for="header_image">Header Image</label>
                    <input type="file" id="header_image" name="header_image" class="form-control">
                    <?php if (!empty($post['image_path'])): ?>
                        <div class="file-preview">
                            <img src="../<?php echo htmlspecialchars($post['image_path']); ?>" alt="Current Header">
                            <div style="font-size:11px; padding:4px; text-align:center; background:#eee;">Current Header</div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="thumbnail_image">Thumbnail Image (Optional)</label>
                    <input type="file" id="thumbnail_image" name="thumbnail_image" class="form-control">
                    <?php if (!empty($post['thumbnail_path'])): ?>
                        <div class="file-preview">
                            <img src="../<?php echo htmlspecialchars($post['thumbnail_path']); ?>" alt="Current Thumbnail">
                            <div style="font-size:11px; padding:4px; text-align:center; background:#eee;">Current Thumbnail</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="content">Article Body (Markdown)</label>
                <div class="markdown-toolbar">
                    <button type="button" onclick="insertAtTextarea('## ', '')">H2</button>
                    <button type="button" onclick="insertAtTextarea('### ', '')">H3</button>
                    <button type="button" onclick="insertAtTextarea('**', '**')">Bold</button>
                    <button type="button" onclick="insertAtTextarea('*', '*')">Italic</button>
                    <button type="button" onclick="insertAtTextarea('> ', '')">Quote</button>
                    <button type="button" onclick="insertAtTextarea('- ', '')">Bullet List</button>
                    <button type="button" onclick="insertAtTextarea('1. ', '')">Num List</button>
                </div>
                <textarea id="content" name="content" rows="18" class="form-control markdown-textarea" required placeholder="Write your post content using standard markdown here..."><?php echo htmlspecialchars($post['content']); ?></textarea>
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap: 10px;">
                <input type="checkbox" id="is_published" name="is_published" value="1" <?php echo $post['is_published'] ? 'checked' : ''; ?> style="width:18px; height:18px; cursor:pointer;">
                <label for="is_published" style="margin-bottom:0; cursor:pointer;">Publish Immediately (Uncheck to save as draft)</label>
            </div>

            <button type="submit" class="btn btn-gold" style="width:100%; margin-top:20px; justify-content:center;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Save Blog Post
            </button>
        </div>

        <!-- Real-time HTML Preview (Right Panel) -->
        <div>
            <div class="editor-card" style="position: sticky; top: 20px;">
                <div class="editor-title">Real-time Article Preview</div>
                
                <div id="youtube-preview-container" style="display:none; margin-bottom: 20px;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; color:var(--gold);">Video Embed Preview</div>
                    <div class="blog-video-embed" id="yt-embed-preview">
                        <!-- Preview iframe loaded dynamically -->
                    </div>
                </div>

                <!-- Preview Area with Blog Styling Classes -->
                <div id="preview" class="blog-article-section" style="padding: 10px 0; border-top: 1px dashed var(--cream-dark); max-height: 700px; overflow-y: auto;">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>
    </div>
</form>

<style>
/* Embedding style rule overrides for the blog article preview box */
.blog-article-section {
    font-family: 'Jost', sans-serif;
    font-size: 16px;
    line-height: 1.8;
    color: var(--brown);
}
.blog-article-section h2 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 26px;
    font-weight: 600;
    margin: 32px 0 12px;
    color: var(--sidebar-bg);
}
.blog-article-section h3 {
    font-family: 'Cormorant Garamond', serif;
    font-size: 20px;
    font-weight: 600;
    margin: 24px 0 10px;
    color: var(--sidebar-bg);
}
.blog-article-section p {
    margin-bottom: 20px;
}
.blog-article-section blockquote {
    border-left: 3px solid var(--gold);
    padding: 12px 18px;
    margin: 24px 0;
    font-style: italic;
    background: rgba(201, 168, 76, 0.05);
}
.blog-article-section ul, .blog-article-section ol {
    margin: 16px 0;
    padding-left: 24px;
}
.blog-article-section li {
    margin-bottom: 8px;
}
.blog-video-embed {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.blog-video-embed iframe {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%; border: none;
}
</style>

<script>
const titleEl = document.getElementById('title');
const slugEl = document.getElementById('slug');
const contentEl = document.getElementById('content');
const previewEl = document.getElementById('preview');
const youtubeUrlEl = document.getElementById('youtube_url');
const ytPreviewContainer = document.getElementById('youtube-preview-container');
const ytEmbedPreview = document.getElementById('yt-embed-preview');

// Auto-generate slug from Title
if (titleEl && slugEl && !<?php echo $isEdit ? 'true' : 'false'; ?>) {
    titleEl.addEventListener('input', () => {
        const titleText = titleEl.value;
        const slugText = titleText
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '') // Remove symbols
            .trim()
            .replace(/\s+/g, '-'); // Replace spaces with dashes
        slugEl.value = slugText;
    });
}

// Markdown Toolbar Helper
function insertAtTextarea(startTag, endTag) {
    const startPos = contentEl.selectionStart;
    const endPos = contentEl.selectionEnd;
    const text = contentEl.value;
    const selected = text.substring(startPos, endPos);
    const replacement = startTag + (selected || '') + endTag;
    
    contentEl.value = text.substring(0, startPos) + replacement + text.substring(endPos);
    contentEl.focus();
    contentEl.selectionStart = startPos + startTag.length;
    contentEl.selectionEnd = startPos + startTag.length + (selected || '').length;
    
    updatePreview();
}

// Inline Markdown Parser
function parseInline(text) {
    // Bold: **text**
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Italic: *text* or _text_
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/_(.*?)_/g, '<em>$1</em>');
    return text;
}

// Block Markdown Parser
function parseMarkdown(markdown) {
    markdown = markdown.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const blocks = markdown.split(/\n\n+/);
    let html = '';
    
    blocks.forEach(block => {
        block = block.trim();
        if (!block) return;
        
        // Headers
        const headerMatch = block.match(/^(#{1,6})\s+(.+)$/);
        if (headerMatch) {
            const level = headerMatch[1].length;
            const content = parseInline(headerMatch[2]);
            html += `<h${level}>${content}</h${level}>\n`;
            return;
        }
        
        // Blockquotes
        if (block.indexOf('> ') === 0) {
            const lines = block.split('\n');
            let quoteContent = '';
            lines.forEach(line => {
                quoteContent += line.substring(2) + '\n';
            });
            const content = parseInline(quoteContent.trim());
            html += `<blockquote><p>${content}</p></blockquote>\n`;
            return;
        }
        
        // Bullet Lists
        if (/^[\*\-]\s+(.+)$/m.test(block)) {
            const lines = block.split('\n');
            let listHtml = "<ul>\n";
            lines.forEach(line => {
                const match = line.trim().match(/^[\*\-]\s+(.+)$/);
                if (match) {
                    const content = parseInline(match[1]);
                    listHtml += `  <li>${content}</li>\n`;
                }
            });
            listHtml += "</ul>\n";
            html += listHtml;
            return;
        }

        // Ordered Lists
        if (/^\d+\.\s+(.+)$/m.test(block)) {
            const lines = block.split('\n');
            let listHtml = "<ol>\n";
            lines.forEach(line => {
                const match = line.trim().match(/^\d+\.\s+(.+)$/);
                if (match) {
                    const content = parseInline(match[1]);
                    listHtml += `  <li>${content}</li>\n`;
                }
            });
            listHtml += "</ol>\n";
            html += listHtml;
            return;
        }
        
        // HTML blocks (like div, images, iframe)
        if (/^<(div|img|hr|p|section|a|span|h\d|table|tr|td|th|iframe)/i.test(block)) {
            html += block + "\n";
            return;
        }
        
        // Default paragraph
        const content = parseInline(block);
        html += `<p>${content}</p>\n`;
    });
    
    return html;
}

// Update live markdown preview
function updatePreview() {
    previewEl.innerHTML = parseMarkdown(contentEl.value);
}

// Extract YouTube ID helper
function getYoutubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

// YouTube Preview update
function updateYoutubePreview() {
    const url = youtubeUrlEl.value.trim();
    const videoId = getYoutubeId(url);
    
    if (videoId) {
        ytEmbedPreview.innerHTML = `<iframe src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>`;
        ytPreviewContainer.style.display = 'block';
    } else {
        ytEmbedPreview.innerHTML = '';
        ytPreviewContainer.style.display = 'none';
    }
}

contentEl.addEventListener('input', updatePreview);
youtubeUrlEl.addEventListener('input', updateYoutubePreview);

// Run initial renders
updatePreview();
updateYoutubePreview();
</script>

<?php
render_admin_footer();
?>
