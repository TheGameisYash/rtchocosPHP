<?php
// admin/layout.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Check auth immediately
require_auth();

function render_admin_header($title, $activePage = '') {
    // Get unread messages count and recent unread list
    $unreadCount = 0;
    $recentUnread = [];
    try {
        $pdo = get_db();
        $stmt = $pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0");
        $unreadCount = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("SELECT id, name, subject, created_at FROM contacts WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
        $recentUnread = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Fallback
    }

    $adminUser = $_SESSION['admin_user'] ?? 'Admin';
    $initials = strtoupper(substr($adminUser, 0, 2));

    // Dynamic breadcrumbs
    $breadcrumbs = [
        ['label' => 'Admin', 'url' => 'dashboard.php']
    ];
    if ($activePage && $activePage !== 'dashboard') {
        $breadcrumbs[] = ['label' => ucfirst($activePage), 'url' => $activePage . '.php'];
    }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | RT Chocos Admin</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Jost:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
    <script>
        // Check theme immediately to avoid flash of light mode
        (function() {
            const savedTheme = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <h1>RT Chocos</h1>
                <p>Management Portal</p>
            </div>
            
            <div class="sidebar-user">
                <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
                <div class="user-info">
                    <span class="username"><?php echo htmlspecialchars($adminUser); ?></span>
                    <span class="role">Administrator</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="dashboard.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zm10-3a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"></path></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'blogs' ? 'active' : ''; ?>">
                        <a href="blogs.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>Blogs</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'media' ? 'active' : ''; ?>">
                        <a href="media.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Media Library</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'subscribers' ? 'active' : ''; ?>">
                        <a href="subscribers.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span>Subscribers</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'contacts' ? 'active' : ''; ?>">
                        <a href="contacts.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span>Messages</span>
                            <?php if ($unreadCount > 0): ?>
                                <span class="status-badge unread" style="margin-left: auto;"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'comments' ? 'active' : ''; ?>">
                        <a href="comments.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            <span>Comments</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'changelog' ? 'active' : ''; ?>">
                        <a href="changelog.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span>Changelog</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'settings' ? 'active' : ''; ?>">
                        <a href="settings.php">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="logout.php" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar Header -->
            <div class="admin-topbar">
                <div class="topbar-left">
                    <button class="menu-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <?php
                    $searchAction = 'blogs.php';
                    $searchPlaceholder = 'Search blog posts...';
                    if ($activePage === 'contacts') {
                        $searchAction = 'contacts.php';
                        $searchPlaceholder = 'Search messages...';
                    } elseif ($activePage === 'subscribers') {
                        $searchAction = 'subscribers.php';
                        $searchPlaceholder = 'Search subscribers...';
                    } elseif ($activePage === 'media') {
                        $searchAction = 'media.php';
                        $searchPlaceholder = 'Search media...';
                    }
                    ?>
                    <div class="topbar-search">
                        <form action="<?php echo $searchAction; ?>" method="GET">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            <input type="text" name="search" placeholder="<?php echo $searchPlaceholder; ?>" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </form>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <!-- Theme Toggle Switch -->
                    <button class="topbar-btn" id="themeToggleBtn" title="Toggle Light/Dark Theme">
                        <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 7a5 5 0 100 10 5 5 0 000-10z"></path></svg>
                        <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>

                    <!-- Notifications (Unread Messages) -->
                    <button class="topbar-btn" id="notifBell" title="Unread Messages">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge-dot"></span>
                        <?php endif; ?>
                        
                        <!-- Dropdown -->
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <h4>Unread Messages (<?php echo $unreadCount; ?>)</h4>
                                <a href="contacts.php?filter=unread">View All</a>
                            </div>
                            <div class="notif-list">
                                <?php if (empty($recentUnread)): ?>
                                    <div style="padding: 24px; text-align: center; color: var(--text-light); font-size: 13px;">No unread messages.</div>
                                <?php else: ?>
                                    <?php foreach ($recentUnread as $msg): ?>
                                        <a href="contacts.php?id=<?php echo $msg['id']; ?>" class="notif-item">
                                            <div class="notif-icon">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                            </div>
                                            <div class="notif-content">
                                                <span class="notif-title"><?php echo htmlspecialchars($msg['name']); ?></span>
                                                <span class="notif-desc"><?php echo htmlspecialchars($msg['subject'] ?: 'No Subject'); ?></span>
                                                <span class="notif-time"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="notif-footer">
                                <a href="contacts.php">Open Message Center</a>
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Breadcrumbs & Dynamic Info -->
            <div class="page-header">
                <ul class="breadcrumb">
                    <?php foreach ($breadcrumbs as $index => $crumb): ?>
                        <li>
                            <?php if ($index === count($breadcrumbs) - 1): ?>
                                <span><?php echo htmlspecialchars($crumb['label']); ?></span>
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
                                <span class="breadcrumb-separator">&rsaquo;</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="page-title-row">
                    <div>
                        <h2><?php echo htmlspecialchars($title); ?></h2>
                    </div>
                    <div>
                        <a href="../index.php" target="_blank" class="btn btn-outline btn-sm">
                            View Website &nearr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Page-specific contents start -->
            <div class="animate-fade-in">
<?php
}

function render_admin_footer() {
?>
            </div> <!-- End animate-fade-in -->
        </main>
    </div> <!-- End app-container -->

    <!-- Floating Action Button (FAB) for quick blog creation -->
    <div class="fab-container">
        <a href="blog-editor.php" class="fab-btn" title="Create New Blog Post">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
        </a>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <!-- Global shared javascript for UI logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Mobile Toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.getElementById('adminSidebar');
            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    adminSidebar.classList.toggle('show');
                });
                // Close sidebar on tapping outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && !adminSidebar.contains(e.target) && e.target !== sidebarToggle) {
                        adminSidebar.classList.remove('show');
                    }
                });
            }

            // Notification dropdown toggle
            const notifBell = document.getElementById('notifBell');
            const notifDropdown = document.getElementById('notifDropdown');
            if (notifBell && notifDropdown) {
                notifBell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Close other dropdowns if any
                    notifDropdown.classList.toggle('show');
                });
                document.addEventListener('click', function() {
                    notifDropdown.classList.remove('show');
                });
            }

            // Theme toggle (light/dark) logic
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            if (themeToggleBtn) {
                const sunIcon = themeToggleBtn.querySelector('.sun-icon');
                const moonIcon = themeToggleBtn.querySelector('.moon-icon');

                function updateThemeIcons(theme) {
                    if (sunIcon && moonIcon) {
                        if (theme === 'dark') {
                            sunIcon.style.display = 'block';
                            moonIcon.style.display = 'none';
                        } else {
                            sunIcon.style.display = 'none';
                            moonIcon.style.display = 'block';
                        }
                    }
                }

                // Initialize icons
                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                updateThemeIcons(currentTheme);

                themeToggleBtn.addEventListener('click', function() {
                    const current = document.documentElement.getAttribute('data-theme') || 'light';
                    const nextTheme = current === 'light' ? 'dark' : 'light';
                    
                    document.documentElement.setAttribute('data-theme', nextTheme);
                    localStorage.setItem('admin-theme', nextTheme);
                    updateThemeIcons(nextTheme);
                    showToast('Theme switched to ' + nextTheme + ' mode', 'info');
                    
                    // Dispatch event so custom elements/charts can redraw
                    window.dispatchEvent(new CustomEvent('themechanged', { detail: { theme: nextTheme } }));
                });
            }

            // Global Toast Notification System
            window.showToast = function(message, type = 'info') {
                const container = document.getElementById('toast-container');
                if (!container) return;

                const toast = document.createElement('div');
                toast.className = `toast toast-${type}`;
                
                let iconSvg = '';
                if (type === 'success') {
                    iconSvg = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                } else if (type === 'danger') {
                    iconSvg = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                } else {
                    iconSvg = `<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;
                }

                toast.innerHTML = `
                    <div class="toast-content">
                        ${iconSvg}
                        <span>${message}</span>
                    </div>
                    <button class="toast-close" style="cursor:pointer; background:none; border:none; padding:4px; font-size:18px;">&times;</button>
                `;

                container.appendChild(toast);

                const closeBtn = toast.querySelector('.toast-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        dismissToast(toast);
                    });
                }

                // Auto-dismiss after 5 seconds
                setTimeout(() => {
                    dismissToast(toast);
                }, 5000);
            };

            function dismissToast(toast) {
                if (!toast) return;
                toast.classList.add('toast-fadeout');
                // Use a reliable timer instead of transitionend
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }
        });
    </script>
</body>
</html>
<?php
}
?>
