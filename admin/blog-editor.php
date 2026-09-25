<?php
// admin/blog-editor.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// 1. AJAX INLINE IMAGE UPLOAD INTERCEPTOR (Block Editor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_inline_image') {
    header('Content-Type: application/json');
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }
    
    if (!isset($_FILES['inline_image']) || $_FILES['inline_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No image file uploaded.']);
        exit;
    }
    
    $blogsDir = __DIR__ . '/../assets/blogs';
    if (!file_exists($blogsDir)) {
        mkdir($blogsDir, 0755, true);
    }
    
    $fileTmpPath = $_FILES['inline_image']['tmp_name'];
    $fileName = $_FILES['inline_image']['name'];
    $fileSize = $_FILES['inline_image']['size'];
    $fileType = mime_content_type($fileTmpPath);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'message' => 'Image must be JPG, PNG or WEBP.']);
        exit;
    }
    
    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB.']);
        exit;
    }
    
    $newFileName = 'inline-' . time() . '-' . rand(1000, 9999) . '.' . $fileExtension;
    $destPath = $blogsDir . '/' . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        // Register image in media table
        try {
            $stmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $newFileName,
                'assets/blogs/' . $newFileName,
                $fileType,
                $fileSize
            ]);
        } catch (Exception $e) {
            // Non-blocking fallback
        }

        echo json_encode(['success' => true, 'url' => 'assets/blogs/' . $newFileName]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image.']);
        exit;
    }
}

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
    'is_published' => 1,
    'scheduled_at' => null
];

$selectedTags = [];

// If Edit, fetch from database
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
        $stmt->execute([$blogId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fetched) {
            header('Location: blogs.php?error=' . urlencode('Blog post not found.'));
            exit;
        }
        $post = $fetched;

        // Fetch selected tags mappings
        $stmt = $pdo->prepare("SELECT tag_id FROM blog_tag_map WHERE blog_id = ?");
        $stmt->execute([$blogId]);
        $selectedTags = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        $error = 'Error fetching post: ' . $e->getMessage();
    }
}

// Fetch all available tags for selection
$allTags = [];
try {
    $allTags = $pdo->query("SELECT id, name FROM blog_tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Non-blocking
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
    
    $scheduledAt = trim($_POST['scheduled_at'] ?? '');
    $scheduledVal = !empty($scheduledAt) ? date('Y-m-d H:i:s', strtotime($scheduledAt)) : null;

    // Retain form input on validation error
    $post['title'] = $title;
    $post['slug'] = $slug;
    $post['category'] = $category;
    $post['excerpt'] = $excerpt;
    $post['content'] = $content;
    $post['read_time'] = $readTime;
    $post['body_class'] = $bodyClass;
    $post['youtube_url'] = $youtubeUrl;
    $post['is_published'] = $isPublished;
    $post['scheduled_at'] = $scheduledVal;

    if (!verify_csrf($csrfToken)) {
        $error = 'Invalid security token.';
    } elseif (empty($title) || empty($slug) || empty($excerpt) || empty($content)) {
        $error = 'Title, URL slug, summary, and article content blocks are required.';
    } else {
        // Clean slug formatting
        $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($slug)));
        $post['slug'] = $slug;

        try {
            // Check if slug is unique
            $slugCheckSql = $isEdit ? "SELECT id FROM blogs WHERE slug = ? AND id != ?" : "SELECT id FROM blogs WHERE slug = ?";
            $slugCheckParams = $isEdit ? [$slug, $blogId] : [$slug];
            
            $stmt = $pdo->prepare($slugCheckSql);
            $stmt->execute($slugCheckParams);
            if ($stmt->fetch()) {
                $error = "The URL slug '{$slug}' is already in use by another article. URL slugs must be unique.";
            } else {
                $blogsDir = __DIR__ . '/../assets/blogs';
                $thumbsDir = __DIR__ . '/../assets/blogs/thumbnails';
                if (!file_exists($blogsDir)) mkdir($blogsDir, 0755, true);
                if (!file_exists($thumbsDir)) mkdir($thumbsDir, 0755, true);

                $imagePath = $post['image_path'];
                $thumbnailPath = $post['thumbnail_path'];

                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                $maxFileSize = 5 * 1024 * 1024; // 5MB

                // Header Image Upload
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
                        if (!empty($imagePath) && strpos($imagePath, 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $imagePath)) {
                            unlink(__DIR__ . '/../' . $imagePath);
                        }
                        $newFileName = $slug . '-header-' . time() . '.' . $fileExtension;
                        $destPath = $blogsDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $imagePath = 'assets/blogs/' . $newFileName;
                            
                            // Insert to media library
                            $stmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$newFileName, $imagePath, $fileType, $fileSize]);
                        } else {
                            $error = 'Failed to save Header Image.';
                        }
                    }
                }

                // Thumbnail Image Upload
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
                        if (!empty($thumbnailPath) && strpos($thumbnailPath, 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $thumbnailPath)) {
                            unlink(__DIR__ . '/../' . $thumbnailPath);
                        }
                        $newFileName = $slug . '-thumb-' . time() . '.' . $fileExtension;
                        $destPath = $thumbsDir . '/' . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            $thumbnailPath = 'assets/blogs/thumbnails/' . $newFileName;
                            
                            // Insert to media library
                            $stmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$newFileName, $thumbnailPath, $fileType, $fileSize]);
                        } else {
                            $error = 'Failed to save Thumbnail Image.';
                        }
                    }
                }

                if (empty($thumbnailPath) && !empty($imagePath)) {
                    $thumbnailPath = $imagePath;
                }

                // Save or Update details
                if (empty($error)) {
                    if ($isEdit) {
                        $stmt = $pdo->prepare("UPDATE blogs SET slug = ?, title = ?, category = ?, excerpt = ?, content = ?, image_path = ?, thumbnail_path = ?, youtube_url = ?, body_class = ?, read_time = ?, is_published = ?, scheduled_at = ? WHERE id = ?");
                        $stmt->execute([
                            $slug, $title, $category, $excerpt, $content, $imagePath, $thumbnailPath,
                            $youtubeUrl ?: null, $bodyClass ?: null, $readTime ?: null, $isPublished, $scheduledVal, $blogId
                        ]);
                        $success = 'Blog article updated successfully!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO blogs (slug, title, category, excerpt, content, image_path, thumbnail_path, youtube_url, body_class, read_time, is_published, scheduled_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                        $stmt->execute([
                            $slug, $title, $category, $excerpt, $content, $imagePath, $thumbnailPath,
                            $youtubeUrl ?: null, $bodyClass ?: null, $readTime ?: null, $isPublished, $scheduledVal
                        ]);
                        $blogId = $pdo->lastInsertId();
                        $isEdit = true;
                        $success = 'New blog post created successfully!';
                    }

                    // Save tags mappings
                    // Delete prior mappings first
                    $stmt = $pdo->prepare("DELETE FROM blog_tag_map WHERE blog_id = ?");
                    $stmt->execute([$blogId]);

                    $tags = $_POST['tags'] ?? [];
                    
                    // Add new comma-separated tags
                    $newTagsInput = trim($_POST['new_tags'] ?? '');
                    if (!empty($newTagsInput)) {
                        $newTagsList = explode(',', $newTagsInput);
                        foreach ($newTagsList as $newTag) {
                            $newTag = trim($newTag);
                            if (empty($newTag)) continue;
                            
                            $tagSlug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($newTag)));
                            
                            // Insert tag if unique
                            $stmt = $pdo->prepare("INSERT IGNORE INTO blog_tags (name, slug) VALUES (?, ?)");
                            $stmt->execute([$newTag, $tagSlug]);
                            
                            // Query tag ID
                            $stmt = $pdo->prepare("SELECT id FROM blog_tags WHERE slug = ?");
                            $stmt->execute([$tagSlug]);
                            $tId = $stmt->fetchColumn();
                            if ($tId) {
                                $tags[] = $tId;
                            }
                        }
                    }

                    // Map associations
                    $tags = array_unique(array_map('intval', $tags));
                    if (!empty($tags)) {
                        $stmt = $pdo->prepare("INSERT INTO blog_tag_map (blog_id, tag_id) VALUES (?, ?)");
                        foreach ($tags as $tagIdVal) {
                            $stmt->execute([$blogId, $tagIdVal]);
                        }
                    }

                    // Reload configurations
                    $post['image_path'] = $imagePath;
                    $post['thumbnail_path'] = $thumbnailPath;
                    $selectedTags = $tags;
                    
                    // Re-load tags
                    $allTags = $pdo->query("SELECT id, name FROM blog_tags ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

                    // Redirect to escape resubmission
                    header('Location: blogs.php?success=' . urlencode($success));
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = 'Save failed: ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf();
render_admin_header($isEdit ? "Edit Article" : "New Article", "blogs");
?>

<link rel="stylesheet" href="editor-style.css">

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>

<!-- Sub-Header Actions -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
    <a href="blogs.php" class="btn btn-outline">
        <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:14px;height:14px;"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to List
    </a>
    
    <div style="display:flex; gap:12px; align-items:center;">
        <span id="autosaveIndicator" style="font-size:12.5px; color:var(--text-light); opacity:0.5; transition:opacity var(--transition);">Autosave is active</span>
        <button type="button" class="btn btn-outline" onclick="triggerUndo()" title="Undo (Ctrl+Z)">Undo</button>
        <button type="button" class="btn btn-outline" onclick="triggerRedo()" title="Redo (Ctrl+Y)">Redo</button>
        <button type="button" class="btn btn-outline" onclick="openFullPreview()">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Immersive Preview
        </button>
        <button type="button" class="btn btn-outline" id="drawerToggle" title="Open Article Settings, Cover & Thumbnail Images" style="display:inline-flex; align-items:center; gap:8px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span>Cover Images &amp; Settings</span>
        </button>
    </div>
</div>

<!-- Main Forms -->
<form action="blog-editor.php<?php echo $isEdit ? '?id=' . $blogId : ''; ?>" method="POST" enctype="multipart/form-data" id="editorForm">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="content" id="content" value="<?php echo htmlspecialchars($post['content']); ?>">
    <input type="hidden" id="blogId" value="<?php echo $post['id']; ?>">

    <div class="editor-canvas-wrapper">
        <!-- Main Canvas Panel (Normal Word-Style Document Editor) -->
        <div class="editor-main-canvas word-editor-canvas">
            <!-- Word Formatting Toolbar -->
            <div class="word-editor-toolbar" id="wordToolbar">
                <select class="word-format-select" id="formatBlockSelect" title="Paragraph Style">
                    <option value="p">Normal Text</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                    <option value="blockquote">Quote / Callout</option>
                    <option value="pre">Code Block</option>
                </select>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                <button type="button" class="word-btn" data-action="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                <button type="button" class="word-btn" data-action="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                <button type="button" class="word-btn" data-action="strikeThrough" title="Strikethrough"><s>S</s></button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="justifyLeft" title="Align Left">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="justifyCenter" title="Align Center">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M5 18h14"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="justifyRight" title="Align Right">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M10 12h10M6 18h14"/></svg>
                </button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="insertUnorderedList" title="Bullet List">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="insertOrderedList" title="Numbered List">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M3 18h3-3v-2a2 2 0 012-2h1"/></svg>
                </button>
                <button type="button" class="word-btn" id="insertQuoteBtn" data-action="toggleQuote" onclick="window.toggleWordQuote && window.toggleWordQuote()" title="Quote / Callout Box (Gold Bar)">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>
                </button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" id="insertLinkBtn" title="Insert Link (Ctrl+K)">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                </button>
                <button type="button" class="word-btn word-btn-featured" id="insertImageBtn" onclick="window.openWordImageModal && window.openWordImageModal()" title="Insert Article Image (Upload or URL)" style="display:inline-flex; align-items:center; gap:5px; padding:0 10px; font-weight:600;">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    <span>Image</span>
                </button>
                <button type="button" class="word-btn" id="insertYoutubeBtn" title="Insert YouTube Embed">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="insertHorizontalRule" title="Horizontal Divider Line">—</button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="removeFormat" title="Clear Formatting">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12M12 4v16M4 20l4-4"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="undo" title="Undo (Ctrl+Z)">↶</button>
                <button type="button" class="word-btn" data-action="redo" title="Redo (Ctrl+Y)">↷</button>
            </div>

            <!-- Single Continuous Editable Document Area -->
            <div class="word-editor-body" id="wordEditorDoc" contenteditable="true" spellcheck="true" placeholder="Start typing your article here..."></div>

            <!-- Stats Bar -->
            <div class="word-editor-footer">
                <span id="char-count">0 characters</span>
                <span class="footer-dot">•</span>
                <span id="word-count">0 words</span>
                <span class="footer-dot">•</span>
                <span id="read-time-calc">1 min read</span>
            </div>
        </div>

        <!-- Collapsible Settings Side-Drawer -->
        <div class="editor-settings-drawer" id="settingsDrawer">
            <div class="drawer-header">
                <h3>Article Metadata</h3>
                <button type="button" class="topbar-btn" id="closeDrawerBtn" title="Close Details Drawer">&times;</button>
            </div>
            
            <div class="drawer-body">
                <div class="form-group">
                    <label class="static-label" for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required placeholder="e.g. Science of fat bloom">
                </div>

                <div class="form-group">
                    <label class="static-label" for="slug">URL Slug</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" required placeholder="e.g. cocoa-fat-bloom">
                </div>

                <div class="form-group">
                    <label class="static-label" for="category">Category</label>
                    <select id="category" name="category">
                        <option value="Science" <?php echo $post['category'] === 'Science' ? 'selected' : ''; ?>>Science</option>
                        <option value="Beginner Guide" <?php echo $post['category'] === 'Beginner Guide' ? 'selected' : ''; ?>>Beginner Guide</option>
                        <option value="Recipe" <?php echo $post['category'] === 'Recipe' ? 'selected' : ''; ?>>Recipe</option>
                        <option value="Artisan" <?php echo $post['category'] === 'Artisan' ? 'selected' : ''; ?>>Artisan</option>
                        <option value="Business Tips" <?php echo $post['category'] === 'Business Tips' ? 'selected' : ''; ?>>Business Tips</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="static-label" for="read_time">Estimated Read Time</label>
                    <input type="text" id="read_time" name="read_time" value="<?php echo htmlspecialchars($post['read_time'] ?? ''); ?>" placeholder="e.g. 5 min">
                </div>

                <div class="form-group">
                    <label class="static-label" for="scheduled_at">Schedule Publish Date</label>
                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="<?php echo !empty($post['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($post['scheduled_at'])) : ''; ?>">
                    <span style="font-size: 11px; color: var(--text-light); margin-top:4px; display:block;">Leave blank to publish immediately on saving.</span>
                </div>

                <!-- Custom Tags Selector Checkboxes -->
                <div class="form-group">
                    <label class="static-label">Tags</label>
                    <div style="max-height: 120px; overflow-y: auto; border: 1px solid var(--border-color); padding: 8px; border-radius: 8px; background: var(--bg-app); margin-bottom: 8px;">
                        <?php if (empty($allTags)): ?>
                            <span style="font-size: 12px; color: var(--text-light);">No tags defined. Add one below.</span>
                        <?php else: ?>
                            <?php foreach ($allTags as $tag): ?>
                                <label style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:normal; margin-bottom:4px; cursor:pointer; text-transform: none;">
                                    <input type="checkbox" name="tags[]" value="<?php echo $tag['id']; ?>" <?php echo in_array($tag['id'], $selectedTags) ? 'checked' : ''; ?> style="width:16px; height:16px; accent-color:var(--green-900);">
                                    <span><?php echo htmlspecialchars($tag['name'] ?? ''); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <input type="text" name="new_tags" placeholder="Add new tags (comma-separated)" style="font-size:13px; padding:8px 12px; border-radius:6px;">
                </div>

                <div class="form-group">
                    <label class="static-label" for="excerpt">Excerpt / Summary</label>
                    <textarea id="excerpt" name="excerpt" rows="3" required placeholder="Write a short blog card teaser summary..."><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                </div>

                <!-- Google SEO Result Live Mockup Card -->
                <div class="form-group">
                    <label class="static-label">Google Search Listing Preview</label>
                    <div class="seo-preview-card">
                        <div class="seo-title" id="seoPreviewTitle">Why pH matters in cocoa</div>
                        <div class="seo-url" id="seoPreviewUrl">https://rtchocos.com/blog/cocoa-ph</div>
                        <div class="seo-snippet" id="seoPreviewExcerpt">Learn how pH affects cocoa extraction...</div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="static-label" for="header_image">Header Image</label>
                    <div class="file-dropzone" id="headerDropzone">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"></path></svg>
                        <p>Upload header image</p>
                        <input type="file" id="header_image" name="header_image" onchange="previewFile(this, 'header-preview')">
                        <div class="file-preview" id="header-preview" style="<?php echo !empty($post['image_path']) ? '' : 'display:none;'; ?>">
                            <img src="<?php echo !empty($post['image_path']) ? '../' . htmlspecialchars($post['image_path']) : ''; ?>" alt="">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="static-label" for="thumbnail_image">Thumbnail Image</label>
                    <div class="file-dropzone" id="thumbDropzone">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"></path></svg>
                        <p>Upload thumbnail image</p>
                        <input type="file" id="thumbnail_image" name="thumbnail_image" onchange="previewFile(this, 'thumb-preview')">
                        <div class="file-preview" id="thumb-preview" style="<?php echo !empty($post['thumbnail_path']) ? '' : 'display:none;'; ?>">
                            <img src="<?php echo !empty($post['thumbnail_path']) ? '../' . htmlspecialchars($post['thumbnail_path']) : ''; ?>" alt="">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="static-label" for="youtube_url">YouTube URL</label>
                    <input type="url" id="youtube_url" name="youtube_url" value="<?php echo htmlspecialchars($post['youtube_url'] ?? ''); ?>" placeholder="e.g. https://youtube.com/watch?v=...">
                </div>

                <div class="form-group">
                    <label class="static-label" for="body_class">Body CSS Class</label>
                    <input type="text" id="body_class" name="body_class" value="<?php echo htmlspecialchars($post['body_class'] ?? ''); ?>" placeholder="e.g. cocoa-article">
                </div>

                <div class="form-group" style="display:flex; justify-content:space-between; align-items:center;">
                    <span class="custom-toggle-label" style="font-weight:600; font-size:12.5px; color:var(--text-muted); text-transform:uppercase;">Publish Post</span>
                    <label class="custom-toggle">
                        <input type="checkbox" name="is_published" value="1" <?php echo $post['is_published'] ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px;"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Save Article
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Word Editor Image Insert Modal -->
<div class="word-modal-overlay" id="wordEditorImageModal">
    <div class="word-modal-card">
        <div class="word-modal-header">
            <h3>Insert Image</h3>
            <button type="button" class="word-modal-close" id="closeWordImageModal">&times;</button>
        </div>
        <div class="word-image-dropzone" id="wordImageDropzone">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
            <p style="margin:0; font-size:14px; font-weight:600; color:var(--text-main);">Click or Drag &amp; Drop Image File Here</p>
            <span style="font-size:11.5px; color:var(--text-light); margin-top:4px; display:block;">Supports JPG, PNG, WEBP (Max 5MB)</span>
            <input type="file" id="wordImageFileInput" accept="image/*" style="display:none;">
        </div>
        <div style="text-align:center; font-size:12px; color:var(--text-light); margin:12px 0;">— OR USE AN IMAGE URL —</div>
        <div class="form-group" style="margin-bottom:12px;">
            <label class="static-label" for="wordImageUrlInput">Image URL</label>
            <input type="url" id="wordImageUrlInput" placeholder="https://example.com/image.jpg" style="font-size:13px;">
        </div>
        <div class="form-group" style="margin-bottom:20px;">
            <label class="static-label" for="wordImageAltInput">Alt Text (Caption / Description)</label>
            <input type="text" id="wordImageAltInput" placeholder="e.g. Cocoa beans drying under sun" style="font-size:13px;">
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('wordEditorImageModal').style.display='none'">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" id="submitWordImageUrlBtn">Insert Image</button>
        </div>
    </div>
</div>

<!-- Immersive Full Preview Overlay -->
<div class="full-preview-overlay" id="fullPreviewOverlay">
    <button type="button" class="btn btn-outline full-preview-close" onclick="closeFullPreview()">Close Preview</button>
    <div class="full-preview-container">
        <h1 id="previewFullTitle" style="font-family: var(--font-heading); font-size:42px; font-weight:700; color:var(--text-main); text-align:center; margin-bottom:12px;"></h1>
        <div id="previewFullCategory" style="text-align:center; font-size:12px; font-weight:600; text-transform:uppercase; color:var(--gold); letter-spacing:2px; margin-bottom:32px;"></div>
        <div id="previewFullHeaderImg" style="border-radius:12px; overflow:hidden; border:1px solid var(--border-color); height:380px; margin-bottom:40px; background:var(--cream); display:none;">
            <img src="" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <div class="blog-article-section" id="previewFullContent"></div>
    </div>
</div>

<style>
/* CSS overrides for preview layouts */
.blog-article-section {
    font-size: 16px;
    line-height: 1.85;
    color: var(--text-muted);
}
.blog-article-section h2 {
    font-family: var(--font-heading);
    font-size: 26px;
    font-weight: 700;
    margin: 32px 0 16px;
    color: var(--text-main);
}
.blog-article-section h3 {
    font-family: var(--font-heading);
    font-size: 21px;
    font-weight: 600;
    margin: 24px 0 12px;
    color: var(--text-main);
}
.blog-article-section p {
    margin-bottom: 24px;
}
.blog-article-section blockquote {
    border-left: 3px solid var(--gold);
    padding: 14px 22px;
    margin: 30px 0;
    font-style: italic;
    background: rgba(199, 166, 106, 0.05);
}
.blog-article-section ul, .blog-article-section ol {
    margin: 20px 0;
    padding-left: 24px;
}
.blog-article-section li {
    margin-bottom: 10px;
}
.blog-article-section pre {
    font-family: monospace;
    background: var(--bg-app);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 16px;
    margin: 24px 0;
    overflow-x: auto;
}
.blog-article-section img {
    max-width: 100%;
    border-radius: 8px;
    margin: 24px 0;
    box-shadow: var(--shadow-sm);
}
</style>

<!-- Parse Inline Markdown helper for preview compile -->
<script>
function escapeHtml(text) {
    if (!text) return '';
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function parseInline(text) {
    if (!text) return '';
    // Bold: **text**
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    // Italic: *text* or _text_
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    text = text.replace(/_(.*?)_/g, '<em>$1</em>');
    // Inline code: `code`
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>');
    // Inline images with optional position matching: ![alt](url){position}
    text = text.replace(/!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|end)\})?/g, function(match, alt, url, pos) {
        pos = pos || 'center';
        return `<span class="blog-img-container blog-img-${pos}"><img src="../${url}" alt="${alt}" class="blog-img-${pos}" loading="lazy" decoding="async"></span>`;
    });
    // Links: [text](href)
    text = text.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2">$1</a>');
    return text;
}

function parseMarkdown(markdown) {
    if (!markdown) return '';
    markdown = markdown.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    
    // Global replacements: YouTube embeds {{youtube:VIDEO_ID}}
    markdown = markdown.replace(/\{\{youtube:([a-zA-Z0-9_\-]+)\}\}/g, '<div class="blog-yt-embed"><iframe src="https://www.youtube.com/embed/$1" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe></div>');
    
    const blocks = markdown.split(/\n\n+/);
    let html = '';
    
    blocks.forEach(block => {
        block = block.trim();
        if (!block) return;
        
        // Fenced Code blocks
        if (block.startsWith('```')) {
            const lines = block.split('\n');
            const firstLine = lines[0];
            const lang = firstLine.replace('```', '').trim();
            const code = lines.slice(1, lines.length - 1).join('\n');
            const classAttr = lang ? ` class="language-${lang}"` : '';
            html += `<pre><code${classAttr}>${escapeHtml(code)}</code></pre>\n`;
            return;
        }

        // Table support
        if (block.startsWith('|')) {
            const lines = block.split('\n');
            if (lines.length >= 2) {
                let tableHtml = '<div class="table-responsive"><table>\n';
                let hasHeader = false;
                lines.forEach(line => {
                    const trimmedLine = line.trim().replace(/^\||\|$/g, '');
                    if (!trimmedLine || /^[:\-\s|]+$/.test(trimmedLine)) {
                        return;
                    }
                    const cols = trimmedLine.split('|');
                    let rowHtml = '  <tr>\n';
                    cols.forEach(col => {
                        const colVal = parseInline(col.trim());
                        const cellTag = !hasHeader ? 'th' : 'td';
                        rowHtml += `    <${cellTag}>${colVal}</${cellTag}>\n`;
                    });
                    rowHtml += '  </tr>\n';
                    if (!hasHeader) {
                        tableHtml += '<thead>\n' + rowHtml + '</thead>\n<tbody>\n';
                        hasHeader = true;
                    } else {
                        tableHtml += rowHtml;
                    }
                });
                if (hasHeader) {
                    tableHtml += '</tbody>\n';
                }
                tableHtml += '</table></div>\n';
                html += tableHtml;
                return;
            }
        }

        // Heading 2
        if (block.startsWith('## ')) {
            html += `<h2>${parseInline(block.substring(3))}</h2>\n`;
            return;
        }
        // Heading 3
        if (block.startsWith('### ')) {
            html += `<h3>${parseInline(block.substring(4))}</h3>\n`;
            return;
        }
        // Heading 4
        if (block.startsWith('#### ')) {
            html += `<h4>${parseInline(block.substring(5))}</h4>\n`;
            return;
        }
        // Divider
        if (block === '---') {
            html += `<hr class="block-divider">\n`;
            return;
        }
        // Blockquotes & Callouts
        if (block.startsWith('> ')) {
            const lines = block.split('\n');
            let content = '';
            lines.forEach(line => {
                content += line.trim().substring(2) + ' ';
            });
            content = content.trim();
            if (content.startsWith('[!NOTE]') || content.startsWith('[!TIP]') || content.startsWith('[!WARNING]')) {
                const clean = content.replace(/\[!(NOTE|TIP|WARNING)\]/i, '').trim();
                html += `<div class="block-callout">${parseInline(clean)}</div>\n`;
            } else {
                html += `<blockquote><p>${parseInline(content)}</p></blockquote>\n`;
            }
            return;
        }
        // Bullet list item
        if (block.startsWith('- ') || block.startsWith('* ')) {
            const items = block.split('\n');
            let listHtml = '<ul>\n';
            items.forEach(item => {
                const text = item.trim().substring(2);
                listHtml += `<li>${parseInline(text)}</li>\n`;
            });
            listHtml += '</ul>\n';
            html += listHtml;
            return;
        }
        // Ordered list item
        if (/^\d+\.\s+/.test(block)) {
            const items = block.split('\n');
            let listHtml = '<ol>\n';
            items.forEach(item => {
                const clean = item.trim().replace(/^\d+\.\s+/, '');
                listHtml += `<li>${parseInline(clean)}</li>\n`;
            });
            listHtml += '</ol>\n';
            html += listHtml;
            return;
        }
        // Standalone Images block-level
        const imgBlockMatch = block.match(/^!\[(.*?)\]\((.*?)\)(?:\{(left|right|center|end)\})?$/);
        if (imgBlockMatch) {
            const alt = imgBlockMatch[1];
            const url = imgBlockMatch[2];
            const pos = imgBlockMatch[3] || 'center';
            html += `<div class="blog-img-${pos}"><img src="../${url}" alt="${alt}" loading="lazy" decoding="async">${alt ? `<span class="article-image-caption">${parseInline(alt)}</span>` : ''}</div>\n`;
            return;
        }
        // YouTube (legacy block format)
        if (block.startsWith('[youtube](') && block.endsWith(')')) {
            const url = block.match(/\[youtube\]\((.*?)\)/)[1];
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
            const match = url.match(regExp);
            const ytId = (match && match[2].length === 11) ? match[2] : null;
            if (ytId) {
                html += `<div class="blog-yt-embed" style="margin:24px 0;"><iframe src="https://www.youtube.com/embed/${ytId}" frameborder="0" allowfullscreen></iframe></div>\n`;
            }
            return;
        }

        // Paragraph
        html += `<p>${parseInline(block)}</p>\n`;
    });
    return html;
}
</script>

<script src="word-editor.js"></script>

<!-- UI logic for drawer toggle, preview toggles, image previews, and SEO synchronization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Word Document Editor Engine
    const initialContent = <?php echo json_encode($post['content']); ?>;
    initWordEditor(initialContent);

    // 2. Settings Drawer Toggle
    const drawer = document.getElementById('settingsDrawer');
    const toggleBtn = document.getElementById('drawerToggle');
    const closeBtn = document.getElementById('closeDrawerBtn');
    
    if (toggleBtn && drawer) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            drawer.classList.toggle('collapsed');
        });
    }
    
    if (closeBtn && drawer) {
        closeBtn.addEventListener('click', function() {
            drawer.classList.add('collapsed');
        });
    }

    // 3. Auto-generate Slug from Title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    if (titleInput && slugInput && !<?php echo $isEdit ? 'true' : 'false'; ?>) {
        titleInput.addEventListener('input', function() {
            const clean = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
            slugInput.value = clean;
            updateSeoPreview();
        });
    }

    // 4. SEO live card preview syncer
    const excerptInput = document.getElementById('excerpt');
    const seoTitle = document.getElementById('seoPreviewTitle');
    const seoUrl = document.getElementById('seoPreviewUrl');
    const seoExcerpt = document.getElementById('seoPreviewExcerpt');

    function updateSeoPreview() {
        if (seoTitle) {
            seoTitle.innerText = titleInput.value.trim() || 'Article Title Placeholder';
        }
        if (seoUrl) {
            const slug = slugInput.value.trim() || 'url-slug';
            seoUrl.innerText = 'https://rtchocos.com/blog/' + slug;
        }
        if (seoExcerpt) {
            seoExcerpt.innerText = excerptInput.value.trim() || 'Provide an excerpt summary to see how search engines will represent your post here...';
        }
    }

    if (titleInput) titleInput.addEventListener('input', updateSeoPreview);
    if (slugInput) slugInput.addEventListener('input', updateSeoPreview);
    if (excerptInput) excerptInput.addEventListener('input', updateSeoPreview);
    updateSeoPreview(); // Run initial SEO preview load
});

// Dropzone preview image loaders
function previewFile(input, previewId) {
    const previewDiv = document.getElementById(previewId);
    const img = previewDiv.querySelector('img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            previewDiv.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Immersive preview modal helpers
function openFullPreview() {
    const overlay = document.getElementById('fullPreviewOverlay');
    const title = document.getElementById('title').value.trim();
    const category = document.getElementById('category').value;
    const contentMarkdown = document.getElementById('content').value;
    
    document.getElementById('previewFullTitle').innerText = title || 'Untitled Blog Post';
    document.getElementById('previewFullCategory').innerText = category;
    
    // Check if header preview is visible and has an image
    const headerImgPreview = document.querySelector('#header-preview img');
    const headerWrapper = document.getElementById('previewFullHeaderImg');
    if (headerImgPreview && headerImgPreview.src) {
        headerWrapper.style.display = 'block';
        headerWrapper.querySelector('img').src = headerImgPreview.src;
    } else {
        headerWrapper.style.display = 'none';
    }

    document.getElementById('previewFullContent').innerHTML = parseMarkdown(contentMarkdown);
    overlay.style.display = 'block';
}

function closeFullPreview() {
    document.getElementById('fullPreviewOverlay').style.display = 'none';
}
</script>

<?php
render_admin_footer();
?>
