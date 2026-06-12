<?php
// admin/dashboard.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();

// Fetch metrics
try {
    $totalBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
    $publishedBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 1")->fetchColumn();
    $draftBlogs = (int)$pdo->query("SELECT COUNT(*) FROM blogs WHERE is_published = 0")->fetchColumn();
    
    $totalSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers")->fetchColumn();
    $newSubscribers = (int)$pdo->query("SELECT COUNT(*) FROM subscribers WHERE created_at >= NOW() - INTERVAL 7 DAY")->fetchColumn();
    
    $unreadMessages = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    
    // Fetch 5 most recent unread contact messages
    $stmt = $pdo->query("SELECT id, name, email, subject, created_at FROM contacts ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll();

    // Fetch 5 most recent subscribers
    $stmt = $pdo->query("SELECT id, email, created_at FROM subscribers ORDER BY created_at DESC LIMIT 5");
    $recentSubscribers = $stmt->fetchAll();
} catch (Exception $e) {
    die("Error loading dashboard metrics: " . $e->getMessage());
}

render_admin_header("Dashboard", "dashboard");
?>

<!-- Metrics Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <div class="metric-title">Total Blogs</div>
        <div class="metric-value"><?php echo $totalBlogs; ?></div>
        <div class="metric-sub" style="color: var(--brown-light);"><?php echo $publishedBlogs; ?> published / <?php echo $draftBlogs; ?> drafts</div>
    </div>
    <div class="metric-card">
        <div class="metric-title">Newsletter Subscribers</div>
        <div class="metric-value"><?php echo $totalSubscribers; ?></div>
        <div class="metric-sub"><?php echo $newSubscribers; ?> new (7 days)</div>
    </div>
    <div class="metric-card">
        <div class="metric-title">Unread Messages</div>
        <div class="metric-value" style="color: <?php echo $unreadMessages > 0 ? 'var(--danger)' : 'var(--success)'; ?>;"><?php echo $unreadMessages; ?></div>
        <div class="metric-sub" style="color: rgba(59, 42, 34, 0.6);">Action required</div>
    </div>
</div>

<div class="editor-layout" style="margin-top: 32px;">
    <!-- Recent Messages Box -->
    <div class="editor-card">
        <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span>Recent Contact Messages</span>
            <a href="contacts.php" class="btn btn-outline btn-sm">View Inbox</a>
        </div>
        
        <?php if (empty($recentMessages)): ?>
            <p style="color: rgba(59, 42, 34, 0.6); font-size: 14px;">No contact messages received yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentMessages as $msg): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($msg['name']); ?></strong><br>
                                    <span style="font-size: 11px; color: rgba(59,42,34,0.6);"><?php echo htmlspecialchars($msg['email']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($msg['subject'] ?: '(No Subject)'); ?></td>
                                <td style="white-space: nowrap;"><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Recent Subscribers Box -->
    <div class="editor-card">
        <div class="editor-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <span>Recent Subscribers</span>
            <a href="subscribers.php" class="btn btn-outline btn-sm">Manage List</a>
        </div>
        
        <?php if (empty($recentSubscribers)): ?>
            <p style="color: rgba(59, 42, 34, 0.6); font-size: 14px;">No subscribers registered yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>Email Address</th>
                            <th>Subscribed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSubscribers as $sub): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sub['email']); ?></strong></td>
                                <td style="white-space: nowrap;"><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
render_admin_footer();
?>
