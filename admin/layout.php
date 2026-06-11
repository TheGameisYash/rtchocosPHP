<?php
// admin/layout.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Check auth immediately
require_auth();

function render_admin_header($title, $activePage = '') {
    // Get unread messages count
    $unreadCount = 0;
    try {
        $pdo = get_db();
        $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
        $unreadCount = (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        // Fallback
    }

    $adminUser = $_SESSION['admin_user'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | RT Chocos Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Jost:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h1>RT Chocos</h1>
            <p>Management Portal</p>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                    <a href="dashboard.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 12px;"><path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zm10-3a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"></path></svg>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo $activePage === 'blogs' ? 'active' : ''; ?>">
                    <a href="blogs.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 12px;"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span>Blogs</span>
                    </a>
                </li>
                <li class="<?php echo $activePage === 'subscribers' ? 'active' : ''; ?>">
                    <a href="subscribers.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 12px;"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Subscribers</span>
                    </a>
                </li>
                <li class="<?php echo $activePage === 'contacts' ? 'active' : ''; ?>">
                    <a href="contacts.php">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 12px;"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <span>Messages</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge badge-unread" style="margin-left: auto; border-radius: 4px; padding: 2px 6px; font-size: 10px;"><?php echo $unreadCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <div class="admin-user-info" style="margin-bottom: 12px; font-size: 13px;">Logged in: <strong><?php echo htmlspecialchars($adminUser); ?></strong></div>
            <a href="logout.php" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center; display: flex; font-size: 12px; padding: 8px 12px; font-family: 'Jost', sans-serif;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px;"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="page-header">
            <h2><?php echo htmlspecialchars($title); ?></h2>
            <div style="font-size: 14px; font-weight: 600;">
                <a href="../index.php" target="_blank" style="color: var(--green-900); border-bottom: 1px solid var(--gold); padding-bottom: 2px; text-decoration: none;">
                    View Website &nearr;
                </a>
            </div>
        </div>
<?php
}

function render_admin_footer() {
?>
    </main>
</body>
</html>
<?php
}
?>
