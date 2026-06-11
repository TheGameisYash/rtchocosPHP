<?php
// admin/subscribers.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// Handle CSV Export before any HTML is sent
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=subscribers_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email Address', 'Date Subscribed']);
    
    try {
        $stmt = $pdo->query("SELECT email, created_at FROM subscribers ORDER BY created_at DESC");
        while ($row = $stmt->fetch()) {
            fputcsv($output, [$row['email'], $row['created_at']]);
        }
    } catch (Exception $e) {
        // Stream error text if something fails
        fputcsv($output, ['Error exporting data: ' . $e->getMessage()]);
    }
    
    fclose($output);
    exit;
}

// Handle Delete Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $subId = (int)($_POST['subscriber_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } elseif ($subId <= 0) {
        $error = 'Invalid subscriber ID.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id = ?");
                $stmt->execute([$subId]);
                $success = 'Subscriber removed successfully.';
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Paging
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    $totalResults = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    $totalPages = ceil($totalResults / $limit);
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    $stmt = $pdo->prepare("SELECT id, email, created_at FROM subscribers ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $subscribers = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error fetching subscribers: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Newsletter Subscribers", "subscribers");
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Top Action Bar -->
<div class="action-bar">
    <div style="font-size: 15px; font-weight: 600; color: var(--brown);">
        Total: <?php echo $totalResults; ?> subscriber<?php echo $totalResults !== 1 ? 's' : ''; ?>
    </div>
    <a href="subscribers.php?export=csv" class="btn btn-gold">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: inline-block; vertical-align: middle;"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        Export CSV
    </a>
</div>

<!-- Subscribers Table -->
<div class="table-container">
    <?php if (empty($subscribers)): ?>
        <div style="padding: 40px; text-align: center; color: rgba(59, 42, 34, 0.6);">
            No subscribers found in the database.
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Email Address</th>
                    <th>Subscribed On</th>
                    <th style="text-align: right; width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $sub): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($sub['email']); ?></strong></td>
                        <td><?php echo date('M d, Y \a\t H:i', strtotime($sub['created_at'])); ?></td>
                        <td style="text-align: right;">
                            <form action="subscribers.php" method="POST" style="display: inline;" onsubmit="return confirm('Remove <?php echo htmlspecialchars($sub['email']); ?> from subscribers list?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="subscriber_id" value="<?php echo $sub['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
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
            <a href="subscribers.php?page=<?php echo $page - 1; ?>" class="page-item">&larr;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="subscribers.php?page=<?php echo $i; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="subscribers.php?page=<?php echo $page + 1; ?>" class="page-item">&rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php
render_admin_footer();
?>
