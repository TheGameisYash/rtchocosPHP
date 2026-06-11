<?php
// admin/contacts.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// Handle AJAX mark_read before layout sends HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'ajax_mark_read') {
    $msgId = (int)($_POST['message_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';
    
    if (verify_csrf($token) && $msgId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 WHERE id = ?");
            $stmt->execute([$msgId]);
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

// Handle Form POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $msgId = (int)($_POST['message_id'] ?? 0);
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } elseif ($msgId <= 0) {
        $error = 'Invalid message ID.';
    } else {
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
                $stmt->execute([$msgId]);
                $success = 'Message deleted successfully.';
            } elseif ($action === 'toggle_read') {
                $stmt = $pdo->prepare("UPDATE contacts SET is_read = 1 - is_read WHERE id = ?");
                $stmt->execute([$msgId]);
                $success = 'Message status updated.';
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Paging & Count
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 15;
$offset = ($page - 1) * $limit;

try {
    $totalResults = (int)$pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
    $totalPages = ceil($totalResults / $limit);
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    $stmt = $pdo->prepare("SELECT * FROM contacts ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error fetching messages: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Contact Inbox", "contacts");
?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<!-- Inbox Table -->
<div class="table-container">
    <?php if (empty($messages)): ?>
        <div style="padding: 40px; text-align: center; color: rgba(59, 42, 34, 0.6);">
            No messages received yet. Your inbox is clean!
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">Status</th>
                    <th>Sender Info</th>
                    <th>Subject</th>
                    <th>Date Received</th>
                    <th style="text-align: right; width: 220px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr style="cursor: pointer;" onclick="toggleMessage(<?php echo $msg['id']; ?>, <?php echo !$msg['is_read'] ? 'true' : 'false'; ?>)">
                        <td>
                            <span id="status-dot-<?php echo $msg['id']; ?>" class="status-dot <?php echo $msg['is_read'] ? 'read' : 'unread'; ?>"></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                            <span style="font-size:12px; color:rgba(59,42,34,0.6);"><?php echo htmlspecialchars($msg['email']); ?></span>
                            <?php if (!empty($msg['phone'])): ?>
                                <span style="font-size:11px; color:rgba(59,42,34,0.6);"> • <?php echo htmlspecialchars($msg['phone']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?></strong></td>
                        <td><?php echo date('M d, Y \a\t H:i', strtotime($msg['created_at'])); ?></td>
                        <td style="text-align: right;" onclick="event.stopPropagation();">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <form action="contacts.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="toggle_read">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        Mark <?php echo $msg['is_read'] ? 'Unread' : 'Read'; ?>
                                    </button>
                                </form>
                                <form action="contacts.php" method="POST" style="display: inline;" onsubmit="return confirm('Delete this message permanently?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Message content sub-row (hidden by default) -->
                    <tr id="detail-<?php echo $msg['id']; ?>" class="message-detail-row" style="display: none; background: #faf8f5;" onclick="event.stopPropagation();">
                        <td colspan="5">
                            <div style="padding: 20px 24px; border-left: 4px solid var(--gold);">
                                <div style="font-size: 12px; margin-bottom: 12px; color: rgba(59, 42, 34, 0.6); display: flex; justify-content: space-between;">
                                    <span><strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> (&lt;<?php echo htmlspecialchars($msg['email']); ?>&gt;) <?php echo !empty($msg['phone']) ? ' | ' . htmlspecialchars($msg['phone']) : ''; ?></span>
                                    <span><strong>Received:</strong> <?php echo date('F d, Y H:i:s', strtotime($msg['created_at'])); ?></span>
                                </div>
                                <div style="font-size: 15px; margin-bottom: 16px; font-weight: 700; color: var(--sidebar-bg);">
                                    Subject: <?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?>
                                </div>
                                <div style="font-size: 14.5px; line-height: 1.7; white-space: pre-wrap; color: var(--brown); background: #fff; padding: 20px; border-radius: 6px; border: 1px solid var(--cream-dark);">
                                    <?php echo htmlspecialchars($msg['message']); ?>
                                </div>
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
            <a href="contacts.php?page=<?php echo $page - 1; ?>" class="page-item">&larr;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="contacts.php?page=<?php echo $i; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="contacts.php?page=<?php echo $page + 1; ?>" class="page-item">&rarr;</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<script>
function toggleMessage(id, isUnread) {
    const detailRow = document.getElementById('detail-' + id);
    const isVisible = detailRow.style.display === 'table-row';
    
    // Toggle targeted message detail
    if (isVisible) {
        detailRow.style.display = 'none';
    } else {
        detailRow.style.display = 'table-row';
        
        // Auto mark as read if it is unread
        if (isUnread) {
            markAsRead(id);
        }
    }
}

function markAsRead(id) {
    const fd = new FormData();
    fd.append('action', 'ajax_mark_read');
    fd.append('message_id', id);
    fd.append('csrf_token', '<?php echo $csrfToken; ?>');
    
    fetch('contacts.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: fd
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Update UI status dot class
            const dot = document.getElementById('status-dot-' + id);
            if (dot) {
                dot.className = 'status-dot read';
            }
        }
    })
    .catch(err => console.error('Error auto-marking read status:', err));
}
</script>

<?php
render_admin_footer();
?>
