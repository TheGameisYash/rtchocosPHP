<?php
// admin/blogs.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

// Handle status toggle or deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $blogId = (int)($_POST['blog_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } elseif ($blogId <= 0) {
        $error = 'Invalid blog post ID.';
    } else {
        try {
            if ($action === 'delete') {
                // Fetch image paths first to delete them from files
                $stmt = $pdo->prepare("SELECT image_path, thumbnail_path FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                $post = $stmt->fetch();
                
                if ($post) {
                    // Delete file assets if they are custom uploads (not the default assets/ph.png etc.)
                    // Check if path contains upload directory
                    if (!empty($post['image_path']) && strpos($post['image_path'], 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $post['image_path'])) {
                        unlink(__DIR__ . '/../' . $post['image_path']);
                    }
                    if (!empty($post['thumbnail_path']) && strpos($post['thumbnail_path'], 'assets/blogs/') === 0 && file_exists(__DIR__ . '/../' . $post['thumbnail_path'])) {
                        unlink(__DIR__ . '/../' . $post['thumbnail_path']);
                    }
                }

                $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
                $stmt->execute([$blogId]);
                $success = 'Blog post deleted successfully.';
            } elseif ($action === 'toggle_publish') {
                $stmt = $pdo->prepare("UPDATE blogs SET is_published = 1 - is_published WHERE id = ?");
                $stmt->execute([$blogId]);
                $success = 'Blog publication status updated.';
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Search & Pagination Setup
$search = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 10;
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
    $selectSql = "SELECT id, slug, title, category, is_published, created_at, image_path, thumbnail_path, read_time FROM blogs $whereSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    if (empty($params)) {
        $blogs = $pdo->query($selectSql)->fetchAll();
    } else {
        $stmt = $pdo->prepare($selectSql);
        $stmt->execute($params);
        $blogs = $stmt->fetchAll();
    }
} catch (Exception $e) {
    die("Error fetching blogs: " . $e->getMessage());
}

render_admin_header("Blog Posts", "blogs");
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Action Bar -->
<div class="action-bar">
    <form action="blogs.php" method="GET" class="search-box">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: rgba(59,42,34,0.4);"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <input type="text" name="search" placeholder="Search by title, category, summary..." value="<?php echo htmlspecialchars($search); ?>">
        <?php if ($search !== ''): ?>
            <a href="blogs.php" style="color: rgba(59,42,34,0.4); font-size: 13px;">Clear</a>
        <?php endif; ?>
    </form>
    <a href="blog-editor.php" class="btn btn-gold">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle;"><path d="M12 4v16m8-8H4"></path></svg>
        New Post
    </a>
</div>

<!-- Blogs Table -->
<div class="table-container">
    <?php if (empty($blogs)): ?>
        <div style="padding: 40px; text-align: center; color: rgba(59, 42, 34, 0.6);">
            No blog posts found matching your search.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px;">Media</th>
                    <th>Title / Slug</th>
                    <th>Category</th>
                    <th>Read Time</th>
                    <th>Status</th>
                    <th>Created On</th>
                    <th style="text-align: right; width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $blog): ?>
                    <?php 
                        $thumb = $blog['thumbnail_path'] ?: $blog['image_path'];
                        // If path starts with assets/, it's relative to root
                        $thumbUrl = !empty($thumb) ? '../' . $thumb : null;
                    ?>
                    <tr>
                        <td>
                            <div style="width: 60px; height: 40px; border-radius: 4px; overflow: hidden; background: #e0e0e0; border: 1px solid var(--cream-dark); display: flex; align-items: center; justify-content: center;">
                                <?php if ($thumbUrl && file_exists(__DIR__ . '/../' . $thumb)): ?>
                                    <img src="<?php echo htmlspecialchars($thumbUrl); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <span style="font-size: 10px; color: #757575;">No Image</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--sidebar-bg); font-size: 15px; margin-bottom: 4px;">
                                <?php echo htmlspecialchars($blog['title']); ?>
                            </div>
                            <span style="font-family: monospace; font-size: 11px; color: rgba(59,42,34,0.6);">
                                <?php echo htmlspecialchars($blog['slug']); ?>
                            </span>
                        </td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($blog['category']); ?></span></td>
                        <td><?php echo htmlspecialchars($blog['read_time'] ?: '-'); ?></td>
                        <td>
                            <?php if ($blog['is_published']): ?>
                                <span class="badge badge-success">Published</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="background: #e0e0e0; color: #616161;">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td style="white-space: nowrap;"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="blog-editor.php?id=<?php echo $blog['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                
                                <form action="blogs.php" method="POST" style="display: inline;" onsubmit="return confirm('Change publication status of this post?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="toggle_publish">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?php echo $blog['is_published'] ? 'Unpublish' : 'Publish'; ?>
                                    </button>
                                </form>

                                <form action="blogs.php" method="POST" style="display: inline;" onsubmit="return confirm('ARE YOU SURE? This will permanently delete this blog post, its file attachments, and all associated content. This action is irreversible.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Pagination Grid -->
<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="blogs.php?page=<?php echo $page - 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item">&larr;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="blogs.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="blogs.php?page=<?php echo $page + 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item">&rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
render_admin_footer();
?>
