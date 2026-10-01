<?php
// admin/blogs.php
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/blog-cache.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

// Handle status toggle, duplication, deletion, and bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $blogId = (int)($_POST['blog_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';
    $isAjax = !empty($_POST['is_ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    if (!verify_csrf($token)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
            exit;
        }
        $error = 'Invalid security token.';
    } else {
        try {
            if ($action === 'delete' && $blogId > 0) {
                // Fetch image paths first to delete them from files
                $stmt = $pdo->prepare("SELECT slug, image_path, thumbnail_path FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                $post = $stmt->fetch();
                
                if ($post) {
                    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
                    $parentDir = dirname($docRoot);
                    if (!empty($post['image_path']) && strpos($post['image_path'], 'assets/blogs/') === 0) {
                        $liveImg = $parentDir . '/uploads_blogs/' . basename($post['image_path']);
                        $localImg = __DIR__ . '/../' . $post['image_path'];
                        if (file_exists($liveImg)) @unlink($liveImg);
                        if (file_exists($localImg)) @unlink($localImg);
                    }
                    if (!empty($post['thumbnail_path']) && strpos($post['thumbnail_path'], 'assets/blogs/') === 0) {
                        $liveThumb = $parentDir . '/uploads_blogs/thumbnails/' . basename($post['thumbnail_path']);
                        $localThumb = __DIR__ . '/../' . $post['thumbnail_path'];
                        if (file_exists($liveThumb)) @unlink($liveThumb);
                        if (file_exists($localThumb)) @unlink($localThumb);
                    }
                    clear_blog_cache($post['slug']);
                }

                $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                clear_blog_cache();
                $success = 'Blog post deleted successfully.';
            } elseif ($action === 'toggle_publish' && $blogId > 0) {
                $stmt = $pdo->prepare("UPDATE blogs SET is_published = 1 - is_published WHERE id = ?");
                $stmt->execute([$blogId]);

                $stmt = $pdo->prepare("SELECT id, slug, title, is_published FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                $updatedPost = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($updatedPost) {
                    clear_blog_cache($updatedPost['slug']);
                }
                clear_blog_cache(); // also clear blog list

                $isPub = $updatedPost ? (int)$updatedPost['is_published'] : 0;
                $statusLabel = $isPub ? 'Visible' : 'Hidden';
                $successMsg = 'Article "' . ($updatedPost['title'] ?? 'Post') . '" is now ' . ($isPub ? 'Visible to the public' : 'Hidden from the public (Draft)') . '.';

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'blog_id' => $blogId,
                        'is_published' => $isPub,
                        'status_label' => $statusLabel,
                        'message' => $successMsg
                    ]);
                    exit;
                }
                $success = $successMsg;
            } elseif ($action === 'duplicate' && $blogId > 0) {
                $stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                $blog = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($blog) {
                    $newTitle = $blog['title'] . " (Copy)";
                    $newSlug = $blog['slug'] . "-copy-" . rand(100, 999);
                    
                    $stmt = $pdo->prepare("INSERT INTO blogs (slug, title, category, excerpt, content, image_path, thumbnail_path, body_class, youtube_url, read_time, is_published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())");
                    $stmt->execute([
                        $newSlug,
                        $newTitle,
                        $blog['category'],
                        $blog['excerpt'],
                        $blog['content'],
                        $blog['image_path'],
                        $blog['thumbnail_path'],
                        $blog['body_class'],
                        $blog['youtube_url'],
                        $blog['read_time']
                    ]);
                    clear_blog_cache();
                    $success = 'Blog post duplicated as draft.';
                } else {
                    $error = 'Original post not found.';
                }
            } elseif ($action === 'bulk_delete') {
                $blogIds = $_POST['blog_ids'] ?? [];
                if (!empty($blogIds)) {
                    $placeholders = implode(',', array_fill(0, count($blogIds), '?'));
                    // Fetch images to delete
                    $stmt = $pdo->prepare("SELECT image_path, thumbnail_path FROM blogs WHERE id IN ($placeholders)");
                    $stmt->execute($blogIds);
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
                    $parentDir = dirname($docRoot);
                    foreach ($posts as $post) {
                        if (!empty($post['image_path']) && strpos($post['image_path'], 'assets/blogs/') === 0) {
                            $liveImg = $parentDir . '/uploads_blogs/' . basename($post['image_path']);
                            $localImg = __DIR__ . '/../' . $post['image_path'];
                            if (file_exists($liveImg)) @unlink($liveImg);
                            if (file_exists($localImg)) @unlink($localImg);
                        }
                        if (!empty($post['thumbnail_path']) && strpos($post['thumbnail_path'], 'assets/blogs/') === 0) {
                            $liveThumb = $parentDir . '/uploads_blogs/thumbnails/' . basename($post['thumbnail_path']);
                            $localThumb = __DIR__ . '/../' . $post['thumbnail_path'];
                            if (file_exists($liveThumb)) @unlink($liveThumb);
                            if (file_exists($localThumb)) @unlink($localThumb);
                        }
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id IN ($placeholders)");
                    $stmt->execute($blogIds);
                    clear_blog_cache();
                    $success = 'Selected blog posts deleted successfully.';
                }
            } elseif ($action === 'bulk_publish') {
                $blogIds = $_POST['blog_ids'] ?? [];
                if (!empty($blogIds)) {
                    $placeholders = implode(',', array_fill(0, count($blogIds), '?'));
                    $stmt = $pdo->prepare("UPDATE blogs SET is_published = 1 WHERE id IN ($placeholders)");
                    $stmt->execute($blogIds);
                    clear_blog_cache();
                    $success = 'Selected blog posts published successfully.';
                }
            } elseif ($action === 'bulk_unpublish') {
                $blogIds = $_POST['blog_ids'] ?? [];
                if (!empty($blogIds)) {
                    $placeholders = implode(',', array_fill(0, count($blogIds), '?'));
                    $stmt = $pdo->prepare("UPDATE blogs SET is_published = 0 WHERE id IN ($placeholders)");
                    $stmt->execute($blogIds);
                    clear_blog_cache();
                    $success = 'Selected blog posts hidden (reverted to drafts).';
                }
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Filters & Sorting Setup
$search = trim($_GET['search'] ?? '');
$categoryFilter = trim($_GET['category'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$sortBy = trim($_GET['sort'] ?? 'created_at');
$sortOrder = trim($_GET['order'] ?? 'desc');

$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 12; // Grid view shows nicely with multiples of 3 & 4
$offset = ($page - 1) * $limit;

// SQL construction
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(title LIKE ? OR excerpt LIKE ? OR category LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryFilter !== '') {
    $whereClauses[] = "category = ?";
    $params[] = $categoryFilter;
}

if ($statusFilter !== '') {
    if ($statusFilter === 'published') {
        $whereClauses[] = "is_published = 1";
    } elseif ($statusFilter === 'draft') {
        $whereClauses[] = "is_published = 0";
    }
}

$allowedSorts = ['created_at', 'title', 'category', 'views'];
if (!in_array($sortBy, $allowedSorts)) {
    $sortBy = 'created_at';
}
$orderSql = ($sortOrder === 'asc') ? 'ASC' : 'DESC';

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

try {
    // Count total matches
    $countSql = "SELECT COUNT(*) FROM blogs $whereSql";
    if (empty($params)) {
        $totalResults = (int)$pdo->query($countSql)->fetchColumn();
    } else {
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalResults = (int)$stmt->fetchColumn();
    }
    
    $totalPages = ceil($totalResults / $limit);
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    // Fetch matching blogs
    $selectSql = "SELECT id, slug, title, category, is_published, created_at, image_path, thumbnail_path, read_time, views, excerpt FROM blogs $whereSql ORDER BY $sortBy $orderSql LIMIT $limit OFFSET $offset";
    if (empty($params)) {
        $blogs = $pdo->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare($selectSql);
        $stmt->execute($params);
        $blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch all distinct categories for filtering
    $categories = $pdo->query("SELECT DISTINCT category FROM blogs WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    die("Error fetching blogs: " . $e->getMessage());
}

// Retrieve grid/list view selection from cookie
$viewMode = $_COOKIE['blogs_view_pref'] ?? 'grid';

render_admin_header("Blog Posts", "blogs");
?>

<style>
    /* Styling for additional local items on this page */
    .blog-card {
        overflow: visible !important;
    }
    .blog-card:hover,
    .blog-card:focus-within,
    .blog-card.menu-active {
        z-index: 100 !important;
    }
    .blog-card .more-actions-dropdown {
        display: none;
        position: absolute;
        right: 0;
        bottom: calc(100% + 8px) !important;
        top: auto !important;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.55), 0 4px 10px rgba(0, 0, 0, 0.3);
        z-index: 9999 !important;
        min-width: 155px;
        overflow: hidden;
    }
    
    .more-actions-dropdown button,
    .more-actions-dropdown a {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 9px 16px;
        background: none;
        border: none;
        text-align: left;
        font-size: 13px;
        color: var(--text-muted);
        text-decoration: none;
        cursor: pointer;
        font-family: var(--font-sans);
        transition: background-color var(--transition), color var(--transition);
        white-space: nowrap;
    }

    .more-actions-dropdown button:hover,
    .more-actions-dropdown a:hover {
        background-color: var(--bg-subtle, var(--cream));
        color: var(--text-main);
    }
    
    .more-actions-dropdown button.delete-btn:hover {
        background-color: rgba(211, 47, 47, 0.08);
        color: #d32f2f;
    }
</style>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Bulk Actions Slider Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div class="bulk-info"><span id="bulkSelectedCount">0</span> items selected</div>
    <div class="bulk-actions-btns">
        <form action="blogs.php" method="POST" id="bulkForm" style="display: flex; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" id="bulkActionField" value="">
            <div id="bulkIdsContainer"></div>
            
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulk('bulk_publish')" style="border-color: rgba(255,255,255,0.4); color: white;">Publish</button>
            <button type="button" class="btn btn-outline btn-sm" onclick="submitBulk('bulk_unpublish')" style="border-color: rgba(255,255,255,0.4); color: white;">Draft</button>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBulk('bulk_delete')">Delete</button>
        </form>
    </div>
</div>

<!-- Filters, Search & View Selection Header -->
<div class="action-bar" style="margin-bottom: 28px;">
    <div class="action-bar-left">
        <!-- Search -->
        <form action="blogs.php" method="GET" class="topbar-search" style="display:block; width: 260px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" placeholder="Search title or category..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; border-radius:8px; padding-left:36px; padding-top:10px; padding-bottom:10px;">
            
            <!-- Retain current filters during search -->
            <?php if ($categoryFilter): ?><input type="hidden" name="category" value="<?php echo htmlspecialchars($categoryFilter); ?>"><?php endif; ?>
            <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>"><?php endif; ?>
            <?php if ($sortBy): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortBy); ?>"><?php endif; ?>
            <?php if ($sortOrder): ?><input type="hidden" name="order" value="<?php echo htmlspecialchars($sortOrder); ?>"><?php endif; ?>
        </form>

        <!-- Category Filters -->
        <a href="blogs.php?status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>" class="filter-chip <?php echo $categoryFilter === '' ? 'active' : ''; ?>">All</a>
        <?php foreach ($categories as $cat): ?>
            <a href="blogs.php?category=<?php echo urlencode($cat); ?>&status=<?php echo urlencode($statusFilter); ?>&search=<?php echo urlencode($search); ?>&sort=<?php echo urlencode($sortBy); ?>&order=<?php echo urlencode($sortOrder); ?>" class="filter-chip <?php echo $categoryFilter === $cat ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($cat); ?>
            </a>
        <?php endforeach; ?>
    </div>
    
    <div class="action-bar-right">
        <!-- Sort Select Dropdown -->
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 13px; color: var(--text-light); font-weight: 500;">Sort By:</span>
            <select id="sortSelect" onchange="applySort(this.value)" style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size:13px; font-family: var(--font-sans); outline: none;">
                <option value="created_at-desc" <?php echo ($sortBy === 'created_at' && $sortOrder === 'desc') ? 'selected' : ''; ?>>Newest First</option>
                <option value="created_at-asc" <?php echo ($sortBy === 'created_at' && $sortOrder === 'asc') ? 'selected' : ''; ?>>Oldest First</option>
                <option value="title-asc" <?php echo ($sortBy === 'title' && $sortOrder === 'asc') ? 'selected' : ''; ?>>Title (A-Z)</option>
                <option value="title-desc" <?php echo ($sortBy === 'title' && $sortOrder === 'desc') ? 'selected' : ''; ?>>Title (Z-A)</option>
                <option value="views-desc" <?php echo ($sortBy === 'views' && $sortOrder === 'desc') ? 'selected' : ''; ?>>Most Viewed</option>
            </select>
        </div>

        <!-- Status Filter -->
        <select id="statusSelect" onchange="applyStatus(this.value)" style="padding: 6px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size:13px; font-family: var(--font-sans); outline: none;">
            <option value="" <?php echo $statusFilter === '' ? 'selected' : ''; ?>>All Statuses</option>
            <option value="published" <?php echo $statusFilter === 'published' ? 'selected' : ''; ?>>Visible (Published)</option>
            <option value="draft" <?php echo $statusFilter === 'draft' ? 'selected' : ''; ?>>Hidden (Drafts)</option>
        </select>

        <!-- Layout Selector Toggle -->
        <div class="view-toggle">
            <button type="button" class="view-toggle-btn <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" onclick="setViewMode('grid')" title="Grid View">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            </button>
            <button type="button" class="view-toggle-btn <?php echo $viewMode === 'list' ? 'active' : ''; ?>" onclick="setViewMode('list')" title="List View">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </button>
        </div>

        <a href="blog-editor.php" class="btn btn-primary" style="padding: 8px 16px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
            New Post
        </a>
    </div>
</div>

<!-- Quick Visibility Status Pills -->
<div style="display:flex; gap:10px; align-items:center; margin-bottom:24px; flex-wrap:wrap; background:var(--bg-card); padding:10px 16px; border-radius:10px; border:1px solid var(--border-color);">
    <span style="font-size:12px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.8px;">Visibility Filter:</span>
    <a href="blogs.php?<?php echo http_build_query(array_merge($_GET, ['status' => ''])); ?>" 
       class="filter-chip <?php echo $statusFilter === '' ? 'active' : ''; ?>" style="font-size:12.5px; padding:4px 14px;">
       All Articles
    </a>
    <a href="blogs.php?<?php echo http_build_query(array_merge($_GET, ['status' => 'published'])); ?>" 
       class="filter-chip <?php echo $statusFilter === 'published' ? 'active' : ''; ?>" 
       style="font-size:12.5px; padding:4px 14px; <?php echo $statusFilter === 'published' ? 'background:#15803d; border-color:#15803d; color:#fff;' : 'color:#15803d;'; ?>">
       <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#22c55e; margin-right:5px;"></span>
       Visible (Published)
    </a>
    <a href="blogs.php?<?php echo http_build_query(array_merge($_GET, ['status' => 'draft'])); ?>" 
       class="filter-chip <?php echo $statusFilter === 'draft' ? 'active' : ''; ?>" 
       style="font-size:12.5px; padding:4px 14px; <?php echo $statusFilter === 'draft' ? 'background:#d97706; border-color:#d97706; color:#fff;' : 'color:#d97706;'; ?>">
       <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#f59e0b; margin-right:5px;"></span>
       Hidden (Drafts)
    </a>
</div>

<?php if (empty($blogs)): ?>
    <div class="form-card" style="padding: 60px; text-align: center; color: var(--text-light);">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 48px; height: 48px; opacity: 0.5; margin-bottom: 16px;"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        <p style="font-size: 16px; font-weight: 500;">No articles found matching your query.</p>
        <a href="blogs.php" class="btn btn-outline btn-sm" style="margin-top: 12px;">Reset Filters</a>
    </div>
<?php else: ?>
    <!-- GRID VIEW LAYOUT -->
    <div class="blogs-grid-view" id="gridContainer" style="<?php echo $viewMode === 'grid' ? '' : 'display:none;'; ?>">
        <?php foreach ($blogs as $blog): ?>
            <?php 
                $thumb = $blog['thumbnail_path'] ?: $blog['image_path'];
                $thumbUrl = !empty($thumb) ? '../' . $thumb : '../assets/images/placeholder.jpg';
                $isPub = (int)$blog['is_published'];
            ?>
            <div class="blog-card" id="blog-card-<?php echo $blog['id']; ?>">
                <div class="blog-card-image">
                    <div class="blog-card-badges">
                        <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                              id="badge-blog-grid-<?php echo $blog['id']; ?>"
                              style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                            <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                            <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                        </span>
                    </div>
                    <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" onerror="this.onerror=null; this.src='../assets/images/placeholder.jpg';" loading="lazy">
                    
                    <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.6); color: white; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                        <?php echo htmlspecialchars($blog['read_time'] ?: '5 min'); ?>
                    </div>
                    
                    <!-- Bulk Select checkbox -->
                    <input type="checkbox" class="blog-select-checkbox" value="<?php echo $blog['id']; ?>" onchange="updateBulkSelection()" style="position: absolute; top: 12px; right: 12px; width: 18px; height: 18px; z-index: 20; cursor: pointer; accent-color: var(--green-900);">
                </div>
                
                <div class="blog-card-body">
                    <span class="blog-card-category"><?php echo htmlspecialchars($blog['category']); ?></span>
                    <h3 class="blog-card-title"><?php echo htmlspecialchars($blog['title']); ?></h3>
                    <p class="blog-card-excerpt"><?php echo htmlspecialchars($blog['excerpt'] ?: 'No summary text entered.'); ?></p>
                    
                    <div class="blog-card-footer">
                        <span class="blog-card-date"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></span>
                        <div class="blog-card-actions">
                            <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm" style="padding:6px 12px;">Edit</a>
                            
                            <!-- Instant Visibility Toggle Button -->
                            <button type="button" 
                                    class="btn btn-outline btn-sm btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                    data-blog-id="<?php echo $blog['id']; ?>" 
                                    onclick="toggleBlogVisibility(<?php echo $blog['id']; ?>, this)" 
                                    title="<?php echo $isPub ? 'Click to Hide from website (Draft)' : 'Click to Make Visible on website (Publish)'; ?>"
                                    style="padding:6px 10px; display:inline-flex; align-items:center; gap:5px; font-weight:600; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                            </button>
                            
                            <!-- More menu dropdown triggers -->
                            <div style="position: relative; display: inline-block;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="toggleMoreMenu(event, this)" style="padding: 6px 8px; min-width: 0;">&bull;&bull;&bull;</button>
                                <div class="more-actions-dropdown">
                                    <a href="../blog/<?php echo htmlspecialchars($blog['slug']); ?>" target="_blank">View on Site &nearr;</a>
                                    <form action="blogs.php" method="POST" onsubmit="return confirm('Duplicate this article?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="duplicate">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                        <button type="submit">Duplicate</button>
                                    </form>
                                    <form action="blogs.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?');" class="delete-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- LIST VIEW LAYOUT -->
    <div class="table-container" id="listContainer" style="<?php echo $viewMode === 'list' ? '' : 'display:none;'; ?>">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="width:16px; height:16px; cursor:pointer; accent-color: var(--green-900);"></th>
                    <th style="width: 80px;">Media</th>
                    <th>Title / Slug</th>
                    <th>Category</th>
                    <th>Views</th>
                    <th>Status</th>
                    <th>Created On</th>
                    <th style="text-align: right; width: 240px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $blog): ?>
                    <?php 
                        $thumb = $blog['thumbnail_path'] ?: $blog['image_path'];
                        $thumbUrl = !empty($thumb) ? '../' . $thumb : '../assets/images/placeholder.jpg';
                        $isPub = (int)$blog['is_published'];
                    ?>
                    <tr id="blog-row-<?php echo $blog['id']; ?>">
                        <td style="text-align: center;">
                            <input type="checkbox" class="blog-select-checkbox" value="<?php echo $blog['id']; ?>" onchange="updateBulkSelection()" style="width:16px; height:16px; cursor:pointer; accent-color: var(--green-900);">
                        </td>
                        <td>
                            <div style="width: 60px; height: 40px; border-radius: 6px; overflow: hidden; background: var(--cream); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                                <img src="<?php echo htmlspecialchars($thumbUrl); ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='../assets/images/placeholder.jpg';" loading="lazy">
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main); font-size: 14.5px; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </div>
                            <span style="font-family: monospace; font-size: 11px; color: var(--text-light);">
                                <?php echo htmlspecialchars($blog['slug']); ?>
                            </span>
                        </td>
                        <td><span class="status-badge active" style="font-size: 9.5px;"><?php echo htmlspecialchars($blog['category']); ?></span></td>
                        <td><strong><?php echo $blog['views']; ?></strong> views</td>
                        <td>
                            <!-- Status Badge & Instant Toggle Button in Table -->
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                      id="badge-blog-list-<?php echo $blog['id']; ?>"
                                      style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                    <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                    <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                </span>
                            </div>
                        </td>
                        <td style="white-space: nowrap;"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items:center;">
                                <!-- Instant Visibility Toggle Button -->
                                <button type="button" 
                                        class="btn btn-outline btn-sm btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                        data-blog-id="<?php echo $blog['id']; ?>" 
                                        onclick="toggleBlogVisibility(<?php echo $blog['id']; ?>, this)" 
                                        title="<?php echo $isPub ? 'Click to Hide this article from public' : 'Click to Make Visible to public'; ?>"
                                        style="padding: 5px 8px; font-weight:600; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                    <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                    <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                </button>

                                <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                <a href="../blog/<?php echo htmlspecialchars($blog['slug']); ?>" target="_blank" class="btn btn-outline btn-sm">View</a>
                                
                                <div style="position: relative; display: inline-block;">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleMoreMenu(event, this)" style="padding: 6px 8px; min-width: 0;">&bull;&bull;&bull;</button>
                                    <div class="more-actions-dropdown">
                                        <form action="blogs.php" method="POST" onsubmit="return confirm('Duplicate this article?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="duplicate">
                                            <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                            <button type="submit">Duplicate</button>
                                        </form>
                                        <form action="blogs.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this blog post?');" class="delete-form">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                            <button type="submit" class="delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<!-- Pagination Grid -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php 
            $queryString = '';
            if ($search !== '') $queryString .= '&search=' . urlencode($search);
            if ($categoryFilter !== '') $queryString .= '&category=' . urlencode($categoryFilter);
            if ($statusFilter !== '') $queryString .= '&status=' . urlencode($statusFilter);
            if ($sortBy !== '') $queryString .= '&sort=' . urlencode($sortBy);
            if ($sortOrder !== '') $queryString .= '&order=' . urlencode($sortOrder);
        ?>

        <a href="blogs.php?page=<?php echo $page - 1; ?><?php echo $queryString; ?>" class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">&larr;</a>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="blogs.php?page=<?php echo $i; ?><?php echo $queryString; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <a href="blogs.php?page=<?php echo $page + 1; ?><?php echo $queryString; ?>" class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">&rarr;</a>
    </div>
<?php endif; ?>

<!-- Client-side logic for toggle grids, menus, selection -->
<script>
    // Grid vs List view switcher
    function setViewMode(mode) {
        document.cookie = "blogs_view_pref=" + mode + "; path=/; max-age=" + (86400 * 365);
        
        const gridContainer = document.getElementById('gridContainer');
        const listContainer = document.getElementById('listContainer');
        const viewBtns = document.querySelectorAll('.view-toggle-btn');
        
        if (mode === 'grid') {
            if (gridContainer) gridContainer.style.display = 'grid';
            if (listContainer) listContainer.style.display = 'none';
            viewBtns[0].classList.add('active');
            viewBtns[1].classList.remove('active');
        } else {
            if (gridContainer) gridContainer.style.display = 'none';
            if (listContainer) listContainer.style.display = 'block';
            viewBtns[0].classList.remove('active');
            viewBtns[1].classList.add('active');
        }
        // Uncheck all when shifting modes to avoid confusion
        resetSelections();
    }

    // Toggle more actions menus
    function toggleMoreMenu(event, btn) {
        event.stopPropagation();
        
        const dropdown = btn.nextElementSibling;
        const willOpen = dropdown.style.display !== 'block';

        // Close other dropdowns & remove active card classes
        document.querySelectorAll('.more-actions-dropdown').forEach(d => d.style.display = 'none');
        document.querySelectorAll('.blog-card').forEach(c => c.classList.remove('menu-active'));
        
        if (willOpen) {
            dropdown.style.display = 'block';
            const card = btn.closest('.blog-card');
            if (card) {
                card.classList.add('menu-active');
            }
        }
    }
    
    // Close dropdowns on clicking outside
    window.addEventListener('click', function(e) {
        if (!e.target.closest('.more-actions-dropdown') && !e.target.closest('button[onclick^="toggleMoreMenu"]')) {
            document.querySelectorAll('.more-actions-dropdown').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.blog-card').forEach(c => c.classList.remove('menu-active'));
        }
    });

    // Apply Sorting select change
    function applySort(value) {
        const parts = value.split('-');
        const sort = parts[0];
        const order = parts[1];
        
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('sort', sort);
        urlParams.set('order', order);
        urlParams.set('page', 1);
        window.location.search = urlParams.toString();
    }

    // Apply Status select change
    function applyStatus(value) {
        const urlParams = new URLSearchParams(window.location.search);
        if (value) {
            urlParams.set('status', value);
        } else {
            urlParams.delete('status');
        }
        urlParams.set('page', 1);
        window.location.search = urlParams.toString();
    }

    // Bulk selection logic
    function resetSelections() {
        const checkboxes = document.querySelectorAll('.blog-select-checkbox');
        checkboxes.forEach(c => c.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        updateBulkSelection();
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.blog-select-checkbox');
        checkboxes.forEach(c => c.checked = selectAllCheckbox.checked);
        updateBulkSelection();
    }

    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.blog-select-checkbox:checked');
        const bar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        
        countSpan.innerText = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bar.classList.add('show');
        } else {
            bar.classList.remove('show');
        }
    }

    function submitBulk(action) {
        const confirmMsg = action === 'bulk_delete' 
            ? 'Are you sure you want to delete all selected blog posts? This action is permanent!'
            : `Apply '${action === 'bulk_publish' ? 'Publish' : 'Draft'}' to all selected items?`;
            
        if (!confirm(confirmMsg)) return;

        const checkedBoxes = document.querySelectorAll('.blog-select-checkbox:checked');
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = ''; // Clear prior entries
        
        checkedBoxes.forEach(box => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'blog_ids[]';
            input.value = box.value;
            container.appendChild(input);
        });

        document.getElementById('bulkActionField').value = action;
        document.getElementById('bulkForm').submit();
    }

    // Instant AJAX Visibility Toggle
    function toggleBlogVisibility(blogId, btnEl) {
        if (!blogId) return;

        const csrfToken = <?php echo json_encode($csrfToken); ?>;
        const formData = new FormData();
        formData.append('action', 'toggle_publish');
        formData.append('blog_id', blogId);
        formData.append('csrf_token', csrfToken);
        formData.append('is_ajax', '1');

        if (btnEl) {
            btnEl.disabled = true;
            btnEl.style.opacity = '0.5';
        }

        fetch('blogs.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.style.opacity = '1';
            }
            if (data.success) {
                const isPub = data.is_published === 1;

                // Update all toggle buttons for this blog
                document.querySelectorAll(`.btn-visibility-toggle[data-blog-id="${blogId}"]`).forEach(btn => {
                    const iconSpan = btn.querySelector('.toggle-icon');
                    const textSpan = btn.querySelector('.toggle-text');
                    if (isPub) {
                        btn.classList.remove('btn-status-hidden');
                        btn.classList.add('btn-status-visible');
                        btn.style.color = '#15803d';
                        btn.title = 'Click to Hide from website (Draft)';
                        if (iconSpan) iconSpan.innerText = '👁️';
                        if (textSpan) textSpan.innerText = 'Hide';
                    } else {
                        btn.classList.remove('btn-status-visible');
                        btn.classList.add('btn-status-hidden');
                        btn.style.color = '#d97706';
                        btn.title = 'Click to Make Visible on website (Publish)';
                        if (iconSpan) iconSpan.innerText = '🔒';
                        if (textSpan) textSpan.innerText = 'Show';
                    }
                });

                // Update Grid & List badges
                ['grid', 'list'].forEach(viewType => {
                    const badge = document.getElementById(`badge-blog-${viewType}-${blogId}`);
                    if (badge) {
                        const iconSpan = badge.querySelector('.badge-icon');
                        const labelSpan = badge.querySelector('.badge-label');
                        if (isPub) {
                            badge.className = 'status-badge published';
                            badge.style.background = '';
                            badge.style.color = '';
                            badge.style.borderColor = '';
                            if (iconSpan) iconSpan.innerText = '👁️';
                            if (labelSpan) labelSpan.innerText = 'Visible';
                        } else {
                            badge.className = 'status-badge draft';
                            badge.style.background = 'rgba(217, 119, 6, 0.15)';
                            badge.style.color = '#d97706';
                            badge.style.borderColor = 'rgba(217, 119, 6, 0.3)';
                            if (iconSpan) iconSpan.innerText = '🔒';
                            if (labelSpan) labelSpan.innerText = 'Hidden';
                        }
                    }
                });

                if (window.showToast) {
                    showToast(data.message, isPub ? 'success' : 'warning');
                }
            } else {
                if (window.showToast) {
                    showToast(data.message || 'Failed to update visibility', 'danger');
                } else {
                    alert('Error: ' + (data.message || 'Failed to update visibility'));
                }
            }
        })
        .catch(err => {
            if (btnEl) {
                btnEl.disabled = false;
                btnEl.style.opacity = '1';
            }
            if (window.showToast) {
                showToast('Network error: ' + err.message, 'danger');
            } else {
                alert('Network error: ' + err.message);
            }
        });
    }
</script>

<?php
render_admin_footer();
?>
