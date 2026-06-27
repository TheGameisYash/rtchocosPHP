<?php
// admin/comments.php - Manage blog comments in admin panel
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

// Handle Comment Actions (Approve, Unapprove, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentId = (int)($_POST['comment_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } elseif ($commentId <= 0) {
        $error = 'Invalid comment ID.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
                $stmt->execute([$commentId]);
                $success = 'Comment deleted successfully.';
            } elseif ($action === 'toggle_approve') {
                $stmt = $pdo->prepare("UPDATE comments SET is_approved = 1 - is_approved WHERE id = ?");
                $stmt->execute([$commentId]);
                $success = 'Comment status updated successfully.';
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
$limit = 15;
$offset = ($page - 1) * $limit;

// SQL construction
$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "(name LIKE ? OR comment LIKE ? OR blog_slug LIKE ?)";
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
    $countSql = "SELECT COUNT(*) FROM comments $whereSql";
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

    // Fetch matching comments
    $selectSql = "SELECT * FROM comments $whereSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    if (empty($params)) {
        $comments = $pdo->query($selectSql)->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare($selectSql);
        $stmt->execute($params);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    die("Error fetching comments: " . $e->getMessage());
}

render_admin_header("Blog Comments", "comments");
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Action Bar -->
<div class="action-bar" style="margin-bottom: 24px;">
    <form action="comments.php" method="GET" class="search-box">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: rgba(59,42,34,0.4);"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <input type="text" name="search" placeholder="Search comments by user name, slug, content..." value="<?php echo htmlspecialchars($search); ?>">
        <?php if ($search !== ''): ?>
            <a href="comments.php" style="color: rgba(59,42,34,0.4); font-size: 13px;">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Comments Table -->
<div class="table-container">
    <?php if (empty($comments)): ?>
        <div style="padding: 40px; text-align: center; color: rgba(59, 42, 34, 0.6);">
            No comments found matching your search.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Article Link</th>
                    <th>Comment Text</th>
                    <th>Posted On</th>
                    <th>Status</th>
                    <th style="text-align: right; width: 180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td style="font-weight: 600; color: var(--sidebar-bg);"><?php echo htmlspecialchars($comment['name']); ?></td>
                        <td>
                            <a href="../blog/<?php echo htmlspecialchars($comment['blog_slug']); ?>" target="_blank" style="font-family: monospace; font-size: 12px; color: var(--gold); text-decoration: none;">
                                <?php echo htmlspecialchars($comment['blog_slug']); ?> &nearr;
                            </a>
                        </td>
                        <td style="max-width: 400px; word-break: break-word; font-size: 14px;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></td>
                        <td style="white-space: nowrap;"><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></td>
                        <td>
                            <?php if ($comment['is_approved']): ?>
                                <span class="badge badge-success">Approved</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="background: #e0e0e0; color: #616161;">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <form action="comments.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="toggle_approve">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?php echo $comment['is_approved'] ? 'Unapprove' : 'Approve'; ?>
                                    </button>
                                </form>

                                <form action="comments.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete this comment?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
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
            <a href="comments.php?page=<?php echo $page - 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item">&larr;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="comments.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="comments.php?page=<?php echo $page + 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item">&rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
render_admin_footer();
?>
