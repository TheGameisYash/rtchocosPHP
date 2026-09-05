<?php
// admin/layout.php - Upgraded Modern Shell for RT Chocos Admin Portal
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Check auth immediately
require_auth();

function render_admin_header($title, $activePage = '') {
    // Consolidated Header Counts Query (Single Network Round-Trip)
    $unreadCount = 0;
    $pendingOrdersCount = 0;
    $recentUnread = [];
    try {
        $pdo = get_db();
        $stmt = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM contacts WHERE is_read = 0) as unread_contacts,
            (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders
        ");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $unreadCount = (int)($row['unread_contacts'] ?? 0);
        $pendingOrdersCount = (int)($row['pending_orders'] ?? 0);

        if ($unreadCount > 0) {
            $stmt = $pdo->query("SELECT id, name, subject, created_at FROM contacts WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
            $recentUnread = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
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
        $pageLabel = ucfirst($activePage);
        if ($activePage === 'product-editor') {
            $pageLabel = 'Product Editor';
        } elseif ($activePage === 'blog-editor') {
            $pageLabel = 'Blog Editor';
        }
        $breadcrumbs[] = ['label' => $pageLabel, 'url' => $activePage . '.php'];
    }
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | RT Chocos Admin</title>
    <!-- Google Fonts: Plus Jakarta Sans for UI + Cormorant Garamond for Luxury Branding -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
    <script>
        // Check theme & collapsed sidebar state immediately to prevent layout shifts
        (function() {
            const savedTheme = localStorage.getItem('admin-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            if (localStorage.getItem('admin-sidebar-collapsed') === 'true' && window.innerWidth >= 992) {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
</head>
<body>
    <script>
        // Sync body class immediately
        if (document.documentElement.classList.contains('sidebar-collapsed')) {
            document.body.classList.add('sidebar-collapsed');
        }
    </script>
    <div class="app-container">
        <!-- Sidebar Navigation -->
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
                    <!-- E-Commerce Group -->
                    <div class="nav-group-label">Store Operations</div>
                    <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="dashboard.php" title="Dashboard (Shift+D)">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zm10-3a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"></path></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo ($activePage === 'products' || $activePage === 'product-editor') ? 'active' : ''; ?>">
                        <a href="products.php" title="Products (Shift+P)">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>Products</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'categories' ? 'active' : ''; ?>">
                        <a href="categories.php" title="Product Categories">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                            <span>Categories</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'orders' ? 'active' : ''; ?>">
                        <a href="orders.php" title="Orders (Shift+O)" id="sidebarOrdersLink">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            <span>Orders</span>
                            <span class="status-badge pending" id="sidebarOrdersBadge" style="<?php echo $pendingOrdersCount > 0 ? '' : 'display: none;'; ?> margin-left: auto; font-size: 10px; padding: 2px 7px;">
                                <?php echo $pendingOrdersCount; ?>
                            </span>
                        </a>
                    </li>

                    <!-- Content & Media Group -->
                    <div class="nav-group-label">Content & Media</div>
                    <li class="<?php echo ($activePage === 'blogs' || $activePage === 'blog-editor') ? 'active' : ''; ?>">
                        <a href="blogs.php" title="Blogs (Shift+B)">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span>Blogs</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'media' ? 'active' : ''; ?>">
                        <a href="media.php" title="Media Library (Shift+M)">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>Media Library</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'comments' ? 'active' : ''; ?>">
                        <a href="comments.php" title="Comments Moderation">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                            <span>Comments</span>
                        </a>
                    </li>

                    <!-- Audience & CRM Group -->
                    <div class="nav-group-label">Audience & CRM</div>
                    <li class="<?php echo $activePage === 'subscribers' ? 'active' : ''; ?>">
                        <a href="subscribers.php" title="Subscribers">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <span>Subscribers</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'contacts' ? 'active' : ''; ?>">
                        <a href="contacts.php" title="Inquiries & Messages" id="sidebarContactsLink">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span>Messages</span>
                            <span class="status-badge unread" id="sidebarMessagesBadge" style="<?php echo $unreadCount > 0 ? '' : 'display: none;'; ?> margin-left: auto;">
                                <?php echo $unreadCount; ?>
                            </span>
                        </a>
                    </li>

                    <!-- Administration Group -->
                    <div class="nav-group-label">System</div>
                    <li class="<?php echo $activePage === 'changelog' ? 'active' : ''; ?>">
                        <a href="changelog.php" title="Audit Changelog">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            <span>Changelog</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'settings' ? 'active' : ''; ?>">
                        <a href="settings.php" title="Settings">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <!-- Desktop Sidebar Collapse Toggle -->
                <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="Toggle Compact Menu (Ctrl+B)">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                    <span>Collapse Menu</span>
                </button>
                <a href="logout.php" class="btn btn-outline btn-sm" style="width: 100%; justify-content: center;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    <span>Sign Out</span>
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
                    
                    <!-- Store Online Live Status Badge -->
                    <a href="../shop.php" target="_blank" class="store-status-pill" title="Storefront is live & online">
                        <span class="pulse-dot"></span>
                        <span>Store Live</span>
                    </a>

                    <!-- Store Business Focus Mode Switcher Pill & Dropdown -->
                    <?php
                    $activeStoreMode = get_site_setting('store_mode', 'retail');
                    $modeIcons = ['retail' => '🛒', 'bulk' => '📦', 'hybrid' => '🔄'];
                    $modeLabels = ['retail' => 'Retail Mode', 'bulk' => 'Bulk & B2B', 'hybrid' => 'Hybrid Mode'];
                    ?>
                    <div class="store-mode-switcher-wrap" style="position: relative; margin-left: 6px;">
                        <button type="button" class="btn btn-outline btn-sm" id="storeModeToggleBtn" style="padding: 5px 12px; font-size: 12px; gap: 6px; font-weight: 600; border-radius: 20px; background: rgba(229,179,88,0.1); border-color: rgba(229,179,88,0.35); color: var(--text-main); display: flex; align-items: center; cursor: pointer;" title="Current Website Focus. Click to switch between Retail and Bulk">
                            <span id="storeModeIcon"><?php echo $modeIcons[$activeStoreMode] ?? '🛒'; ?></span>
                            <span id="storeModeText"><?php echo $modeLabels[$activeStoreMode] ?? 'Retail Mode'; ?></span>
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 9l6 6 6-6"/></svg>
                        </button>
                        <div id="storeModeDropdown" style="display: none; position: absolute; left: 0; top: 120%; width: 230px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow-md); z-index: 1050; padding: 6px; overflow: hidden;">
                            <div style="font-size: 10px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; padding: 6px 8px; letter-spacing: 0.5px;">Switch Website Focus</div>
                            <button type="button" onclick="switchStoreModeFast('retail')" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: none; background: transparent; border-radius: 8px; cursor: pointer; color: var(--text-main); font-size: 12px; text-align: left; font-weight: <?php echo $activeStoreMode === 'retail' ? '700' : '500'; ?>;">
                                <span style="font-size: 16px;">🛒</span>
                                <div>
                                    <div>Retail Mode (B2C)</div>
                                    <div style="font-size: 10px; color: var(--text-muted);">Consumer shopping &amp; cart</div>
                                </div>
                                <?php if ($activeStoreMode === 'retail'): ?><span style="margin-left: auto; color: #74E291; font-weight: 700;">✓</span><?php endif; ?>
                            </button>
                            <button type="button" onclick="switchStoreModeFast('bulk')" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: none; background: transparent; border-radius: 8px; cursor: pointer; color: var(--text-main); font-size: 12px; text-align: left; font-weight: <?php echo $activeStoreMode === 'bulk' ? '700' : '500'; ?>;">
                                <span style="font-size: 16px;">📦</span>
                                <div>
                                    <div>Bulk &amp; B2B Mode</div>
                                    <div style="font-size: 10px; color: var(--text-muted);">Wholesale &amp; corporate gifting</div>
                                </div>
                                <?php if ($activeStoreMode === 'bulk'): ?><span style="margin-left: auto; color: #74E291; font-weight: 700;">✓</span><?php endif; ?>
                            </button>
                            <button type="button" onclick="switchStoreModeFast('hybrid')" style="width: 100%; display: flex; align-items: center; gap: 10px; padding: 8px 10px; border: none; background: transparent; border-radius: 8px; cursor: pointer; color: var(--text-main); font-size: 12px; text-align: left; font-weight: <?php echo $activeStoreMode === 'hybrid' ? '700' : '500'; ?>;">
                                <span style="font-size: 16px;">🔄</span>
                                <div>
                                    <div>Hybrid Mode (Dual)</div>
                                    <div style="font-size: 10px; color: var(--text-muted);">Retail + Bulk customer toggle</div>
                                </div>
                                <?php if ($activeStoreMode === 'hybrid'): ?><span style="margin-left: auto; color: var(--gold-light); font-weight: 700;">✓</span><?php endif; ?>
                            </button>
                        </div>
                    </div>

                    <!-- Command Palette Trigger Search Box -->
                    <div class="topbar-search-wrapper">
                        <div class="topbar-search-trigger" id="cmdPaletteTrigger" title="Quick Search & Actions (Ctrl + K)">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <span>Search or jump to...</span>
                            <span class="kbd-chip">Ctrl K</span>
                        </div>
                    </div>
                </div>
                
                <div class="topbar-right">
                    <!-- Quick Action "+ New" Button Dropdown -->
                    <div style="position: relative;">
                        <button class="btn btn-secondary btn-sm" id="quickActionBtn" style="padding: 6px 12px; gap: 6px; font-weight: 600;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                            <span>New</span>
                        </button>
                        <div id="quickActionDropdown" style="display: none; position: absolute; right: 0; top: 115%; width: 175px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; box-shadow: var(--shadow-md); z-index: 1000; overflow: hidden; padding: 6px 0;">
                            <a href="product-editor.php" style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; font-size: 13px; color: var(--text-main); text-decoration: none; transition: background var(--transition);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                New Product
                            </a>
                            <a href="categories.php" style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; font-size: 13px; color: var(--text-main); text-decoration: none; transition: background var(--transition);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                New Category
                            </a>
                            <a href="blog-editor.php" style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; font-size: 13px; color: var(--text-main); text-decoration: none; transition: background var(--transition);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                New Blog Post
                            </a>
                            <a href="media.php" style="display: flex; align-items: center; gap: 10px; padding: 8px 14px; font-size: 13px; color: var(--text-main); text-decoration: none; transition: background var(--transition);">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Upload Media
                            </a>
                        </div>
                    </div>

                    <!-- Keyboard Shortcuts Helper Button (?) -->
                    <button class="topbar-btn" id="shortcutsModalBtn" title="Keyboard Shortcuts (?)">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </button>

                    <!-- Order Alert Audio Chime Toggle Button -->
                    <button class="topbar-btn" id="audioChimeToggleBtn" title="Order Alert Sound: On">
                        <svg class="audio-on-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>
                        <svg class="audio-off-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line></svg>
                    </button>

                    <!-- Theme Toggle Switch -->
                    <button class="topbar-btn" id="themeToggleBtn" title="Toggle Light/Dark Theme (Shift+T)">
                        <svg class="sun-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m11.314 11.314l.707.707M12 7a5 5 0 100 10 5 5 0 000-10z"></path></svg>
                        <svg class="moon-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>

                    <!-- Notifications Dropdown -->
                    <button class="topbar-btn" id="notifBell" title="Notification Center">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="badge-dot" id="notifBellDot" style="<?php echo ($unreadCount > 0 || $pendingOrdersCount > 0) ? '' : 'display: none;'; ?>"></span>
                        
                        <!-- Dropdown Content -->
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <h4 id="notifHeaderTitle">Notifications (<?php echo ($unreadCount + $pendingOrdersCount); ?>)</h4>
                                <a href="orders.php">Manage Orders</a>
                            </div>
                            <div class="notif-list" id="notifDropdownList">
                                <?php if ($pendingOrdersCount > 0): ?>
                                    <a href="orders.php?status=pending" class="notif-item" style="border-left: 3px solid var(--gold);">
                                        <div class="notif-icon" style="color: var(--gold);">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                        </div>
                                        <div class="notif-content">
                                            <span class="notif-title"><?php echo $pendingOrdersCount; ?> Order<?php echo $pendingOrdersCount > 1 ? 's' : ''; ?> Pending</span>
                                            <span class="notif-desc">Action required: package and ship.</span>
                                            <span class="notif-time">Store operations</span>
                                        </div>
                                    </a>
                                <?php endif; ?>

                                <?php if (empty($recentUnread) && $pendingOrdersCount === 0): ?>
                                    <div style="padding: 24px; text-align: center; color: var(--text-light); font-size: 13px;">All caught up! No pending alerts.</div>
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
                                <a href="contacts.php">View Inquiries</a>
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
                        <a href="../shop.php" target="_blank" class="btn btn-outline btn-sm">
                            View Storefront &nearr;
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


    <!-- Command Palette Modal (Ctrl + K) -->
    <div class="cmd-palette-backdrop" id="cmdPaletteBackdrop">
        <div class="cmd-palette-modal">
            <div class="cmd-palette-header">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" class="cmd-palette-input" id="cmdPaletteInput" placeholder="Search pages, products, orders, or run a command..." autocomplete="off">
                <span class="kbd-chip" style="cursor: pointer;" id="cmdCloseChip">ESC</span>
            </div>
            <div class="cmd-palette-results" id="cmdPaletteResults">
                <!-- Injected via JavaScript -->
            </div>
            <div class="cmd-palette-footer">
                <span>Navigate <kbd>&uarr;</kbd> <kbd>&darr;</kbd> &bull; Select <kbd>&crarr;</kbd> &bull; Close <kbd>Esc</kbd></span>
                <span style="color: var(--gold); font-weight: 600;">RT Chocos Spotlight</span>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts Cheatsheet Modal (?) -->
    <div class="shortcuts-modal-backdrop" id="shortcutsModalBackdrop">
        <div class="shortcuts-modal">
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border-color);">
                <h3 style="font-size: 16px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    Keyboard Shortcuts
                </h3>
                <button type="button" id="closeShortcutsBtn" style="background: none; border: none; font-size: 20px; color: var(--text-light); cursor: pointer;">&times;</button>
            </div>
            <div style="padding: 10px 16px; max-height: 400px; overflow-y: auto;">
                <table class="shortcuts-table">
                    <tr><td><strong>Ctrl + K</strong> or <strong>Cmd + K</strong></td><td style="color: var(--text-muted);">Open Command Palette / Search</td></tr>
                    <tr><td><strong>Shift + P</strong></td><td style="color: var(--text-muted);">Jump to Products Catalog</td></tr>
                    <tr><td><strong>Shift + O</strong></td><td style="color: var(--text-muted);">Jump to Customer Orders</td></tr>
                    <tr><td><strong>Shift + D</strong></td><td style="color: var(--text-muted);">Jump to Dashboard</td></tr>
                    <tr><td><strong>Shift + B</strong></td><td style="color: var(--text-muted);">Jump to Blog Posts</td></tr>
                    <tr><td><strong>Shift + M</strong></td><td style="color: var(--text-muted);">Jump to Media Library</td></tr>
                    <tr><td><strong>Shift + T</strong></td><td style="color: var(--text-muted);">Toggle Dark / Light Mode</td></tr>
                    <tr><td><strong>Ctrl + B</strong></td><td style="color: var(--text-muted);">Collapse / Expand Sidebar</td></tr>
                    <tr><td><strong>Ctrl + S</strong></td><td style="color: var(--text-muted);">Quick Save Form (in Editors)</td></tr>
                    <tr><td><strong>?</strong></td><td style="color: var(--text-muted);">Open this Shortcuts Guide</td></tr>
                    <tr><td><strong>Esc</strong></td><td style="color: var(--text-muted);">Close active modal or palette</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Toast container -->
    <div id="toast-container"></div>

    <!-- Global Client Automation, Pulse Polling, and UI Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Sidebar Desktop Collapse & Mobile Drawer
            const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const adminSidebar = document.getElementById('adminSidebar');

            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-collapsed');
                    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                    localStorage.setItem('admin-sidebar-collapsed', isCollapsed ? 'true' : 'false');
                });
            }

            if (sidebarToggle && adminSidebar) {
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    adminSidebar.classList.toggle('show');
                });
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && !adminSidebar.contains(e.target) && e.target !== sidebarToggle) {
                        adminSidebar.classList.remove('show');
                    }
                });
            }

            // 2. Quick Action Menu Toggle
            const quickActionBtn = document.getElementById('quickActionBtn');
            const quickActionDropdown = document.getElementById('quickActionDropdown');
            if (quickActionBtn && quickActionDropdown) {
                quickActionBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    quickActionDropdown.style.display = quickActionDropdown.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', function() {
                    quickActionDropdown.style.display = 'none';
                });
            }

            // 2.5 Store Business Focus Switcher Dropdown & AJAX Trigger
            const storeModeBtn = document.getElementById('storeModeToggleBtn');
            const storeModeDropdown = document.getElementById('storeModeDropdown');
            if (storeModeBtn && storeModeDropdown) {
                storeModeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    storeModeDropdown.style.display = storeModeDropdown.style.display === 'block' ? 'none' : 'block';
                });
                document.addEventListener('click', function() {
                    storeModeDropdown.style.display = 'none';
                });
            }

            window.switchStoreModeFast = function(mode) {
                fetch('api_switch_mode.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ mode: mode })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        showToast(data.message || 'Failed to switch mode', 'danger');
                    }
                })
                .catch(() => showToast('Network error while switching store mode', 'danger'));
            };

            // 3. Notification Dropdown Toggle
            const notifBell = document.getElementById('notifBell');
            const notifDropdown = document.getElementById('notifDropdown');
            if (notifBell && notifDropdown) {
                notifBell.addEventListener('click', function(e) {
                    e.stopPropagation();
                    notifDropdown.classList.toggle('show');
                });
                document.addEventListener('click', function() {
                    notifDropdown.classList.remove('show');
                });
            }

            // 4. Light/Dark Theme Switcher
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

                const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
                updateThemeIcons(currentTheme);

                window.toggleAdminTheme = function() {
                    const current = document.documentElement.getAttribute('data-theme') || 'light';
                    const nextTheme = current === 'light' ? 'dark' : 'light';
                    
                    document.documentElement.setAttribute('data-theme', nextTheme);
                    localStorage.setItem('admin-theme', nextTheme);
                    updateThemeIcons(nextTheme);
                    showToast('Switched to ' + nextTheme + ' theme', 'info');
                    window.dispatchEvent(new CustomEvent('themechanged', { detail: { theme: nextTheme } }));
                };

                themeToggleBtn.addEventListener('click', window.toggleAdminTheme);
            }

            // 5. Audio Alert Chime (Web Audio API Synthesizer)
            let audioEnabled = localStorage.getItem('admin-audio-chime') !== 'false';
            const audioBtn = document.getElementById('audioChimeToggleBtn');
            function updateAudioIcon() {
                if (!audioBtn) return;
                const onIcon = audioBtn.querySelector('.audio-on-icon');
                const offIcon = audioBtn.querySelector('.audio-off-icon');
                if (audioEnabled) {
                    onIcon.style.display = 'block';
                    offIcon.style.display = 'none';
                    audioBtn.title = 'Order Alert Sound: Enabled (Click to Mute)';
                } else {
                    onIcon.style.display = 'none';
                    offIcon.style.display = 'block';
                    audioBtn.title = 'Order Alert Sound: Muted (Click to Enable)';
                }
            }
            updateAudioIcon();

            if (audioBtn) {
                audioBtn.addEventListener('click', function() {
                    audioEnabled = !audioEnabled;
                    localStorage.setItem('admin-audio-chime', audioEnabled ? 'true' : 'false');
                    updateAudioIcon();
                    showToast(audioEnabled ? 'Order sound alerts enabled' : 'Order sound alerts muted', 'info');
                    if (audioEnabled) playOrderChime();
                });
            }

            function playOrderChime() {
                if (!audioEnabled) return;
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const now = ctx.currentTime;

                    // Note 1 (C5 - 523Hz)
                    const osc1 = ctx.createOscillator();
                    const gain1 = ctx.createGain();
                    osc1.type = 'sine';
                    osc1.frequency.setValueAtTime(523.25, now);
                    gain1.gain.setValueAtTime(0.18, now);
                    gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                    osc1.connect(gain1);
                    gain1.connect(ctx.destination);
                    osc1.start(now);
                    osc1.stop(now + 0.3);

                    // Note 2 (G5 - 783.99Hz)
                    const osc2 = ctx.createOscillator();
                    const gain2 = ctx.createGain();
                    osc2.type = 'sine';
                    osc2.frequency.setValueAtTime(783.99, now + 0.12);
                    gain2.gain.setValueAtTime(0.22, now + 0.12);
                    gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
                    osc2.connect(gain2);
                    gain2.connect(ctx.destination);
                    osc2.start(now + 0.12);
                    osc2.stop(now + 0.55);
                } catch(e) {}
            }

            // 6. Real-Time Automation: Pulse Polling Engine (Every 25s)
            let lastKnownOrderId = parseInt(sessionStorage.getItem('last_order_id') || '0', 10);
            
            function runPulseCheck() {
                fetch('pulse.php')
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) return;

                        const counts = data.counts;

                        // Update Orders Badge
                        const ordersBadge = document.getElementById('sidebarOrdersBadge');
                        if (ordersBadge) {
                            if (counts.pending_orders > 0) {
                                ordersBadge.textContent = counts.pending_orders;
                                ordersBadge.style.display = 'inline-block';
                            } else {
                                ordersBadge.style.display = 'none';
                            }
                        }

                        // Update Messages Badge
                        const msgBadge = document.getElementById('sidebarMessagesBadge');
                        if (msgBadge) {
                            if (counts.unread_messages > 0) {
                                msgBadge.textContent = counts.unread_messages;
                                msgBadge.style.display = 'inline-block';
                            } else {
                                msgBadge.style.display = 'none';
                            }
                        }

                        // Update Bell Dot
                        const bellDot = document.getElementById('notifBellDot');
                        if (bellDot) {
                            bellDot.style.display = (counts.pending_orders > 0 || counts.unread_messages > 0) ? 'inline-block' : 'none';
                        }

                        // Check for new incoming order
                        if (data.latest_order) {
                            const latestId = data.latest_order.id;
                            if (lastKnownOrderId > 0 && latestId > lastKnownOrderId) {
                                playOrderChime();
                                if (ordersBadge) {
                                    ordersBadge.classList.add('badge-pulse');
                                    setTimeout(() => ordersBadge.classList.remove('badge-pulse'), 1000);
                                }
                                showToast(`🛍️ New Order #${data.latest_order.order_number} received for ₹${data.latest_order.total.toFixed(2)} from ${data.latest_order.customer_name}`, 'success');
                            }
                            lastKnownOrderId = latestId;
                            sessionStorage.setItem('last_order_id', latestId);
                        }
                    })
                    .catch(() => {});
            }

            // Optimized Polling Timer (Runs every 60s only when browser tab is active/visible)
            let lastPollTime = Date.now();
            setInterval(function() {
                if (document.visibilityState === 'visible') {
                    runPulseCheck();
                    lastPollTime = Date.now();
                }
            }, 60000);

            // Re-sync when user returns to this tab if more than 60s elapsed
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible' && (Date.now() - lastPollTime > 60000)) {
                    runPulseCheck();
                    lastPollTime = Date.now();
                }
            });

            // 7. Command Palette Logic (Ctrl + K)
            const cmdBackdrop = document.getElementById('cmdPaletteBackdrop');
            const cmdTrigger = document.getElementById('cmdPaletteTrigger');
            const cmdInput = document.getElementById('cmdPaletteInput');
            const cmdResults = document.getElementById('cmdPaletteResults');
            const cmdCloseChip = document.getElementById('cmdCloseChip');

            const commandItems = [
                // Quick Actions
                { group: 'Quick Actions', title: 'Create New Product', desc: 'Add chocolate item with pricing & inventory', url: 'product-editor.php', icon: 'M12 4v16m8-8H4', shortcut: 'Shift+P' },
                { group: 'Quick Actions', title: 'Fulfill Store Orders', desc: 'Process customer orders and print invoices', url: 'orders.php', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2', shortcut: 'Shift+O' },
                { group: 'Quick Actions', title: 'Write Blog Article', desc: 'Draft articles for Cacao Chronicles', url: 'blog-editor.php', icon: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z', shortcut: 'Shift+B' },
                { group: 'Quick Actions', title: 'Toggle Light / Dark Theme', desc: 'Switch visual interface scheme', action: 'toggleTheme', icon: 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z', shortcut: 'Shift+T' },
                { group: 'Quick Actions', title: 'View Customer Storefront', desc: 'Open public website shop page in new tab', url: '../shop.php', external: true, icon: 'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14', shortcut: '' },
                
                // Navigation
                { group: 'Navigation', title: 'Store Dashboard', desc: 'Sales metrics, revenue trends & activity', url: 'dashboard.php', icon: 'M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5z', shortcut: 'Shift+D' },
                { group: 'Navigation', title: 'Products Catalog', desc: 'Manage inventory, categories, active status', url: 'products.php', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', shortcut: 'Shift+P' },
                { group: 'Navigation', title: 'Customer Orders', desc: 'Pending, shipped and delivered packages', url: 'orders.php', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2', shortcut: 'Shift+O' },
                { group: 'Navigation', title: 'Blog Posts', desc: 'Publish bean-to-bar stories and guides', url: 'blogs.php', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13', shortcut: 'Shift+B' },
                { group: 'Navigation', title: 'Media Library', desc: 'Browse assets, product photos, banners', url: 'media.php', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16', shortcut: 'Shift+M' },
                { group: 'Navigation', title: 'Subscribers & CRM', desc: 'Newsletter subscribers and customer emails', url: 'subscribers.php', icon: 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8', shortcut: '' },
                { group: 'Navigation', title: 'Customer Messages', desc: 'Inquiries, contact forms, bulk inquiries', url: 'contacts.php', icon: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8', shortcut: '' },
                { group: 'Navigation', title: 'Audit Changelog', desc: 'Review system changes and updates', url: 'changelog.php', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10', shortcut: '' },
                { group: 'Navigation', title: 'Site Settings', desc: 'Configure company information and password', url: 'settings.php', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0', shortcut: '' }
            ];

            let activeIndex = 0;
            let filteredItems = [...commandItems];

            function openCommandPalette() {
                if (!cmdBackdrop) return;
                cmdBackdrop.classList.add('active');
                cmdInput.value = '';
                renderCommandResults('');
                setTimeout(() => cmdInput.focus(), 50);
            }

            function closeCommandPalette() {
                if (!cmdBackdrop) return;
                cmdBackdrop.classList.remove('active');
            }

            function renderCommandResults(query) {
                const q = query.toLowerCase().trim();
                filteredItems = commandItems.filter(item => 
                    item.title.toLowerCase().includes(q) || 
                    item.desc.toLowerCase().includes(q) ||
                    item.group.toLowerCase().includes(q)
                );

                if (filteredItems.length === 0) {
                    cmdResults.innerHTML = `
                        <div style="padding: 28px; text-align: center; color: var(--text-light); font-size: 13.5px;">
                            No matching actions or pages found for "<strong>${escapeHtml(query)}</strong>"
                        </div>
                    `;
                    return;
                }

                activeIndex = Math.min(activeIndex, filteredItems.length - 1);

                let html = '';
                let currentGroup = '';

                filteredItems.forEach((item, index) => {
                    if (item.group !== currentGroup) {
                        currentGroup = item.group;
                        html += `<div class="cmd-group-label">${currentGroup}</div>`;
                    }

                    const isSelected = index === activeIndex ? 'active' : '';
                    html += `
                        <div class="cmd-item ${isSelected}" data-index="${index}">
                            <div class="cmd-item-icon">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="${item.icon}"></path></svg>
                            </div>
                            <div class="cmd-item-text">
                                <div class="cmd-item-title">${escapeHtml(item.title)}</div>
                                <div class="cmd-item-desc">${escapeHtml(item.desc)}</div>
                            </div>
                            ${item.shortcut ? `<span class="kbd-chip">${item.shortcut}</span>` : ''}
                        </div>
                    `;
                });

                cmdResults.innerHTML = html;

                // Bind click listeners
                cmdResults.querySelectorAll('.cmd-item').forEach(el => {
                    el.addEventListener('click', function() {
                        const idx = parseInt(this.getAttribute('data-index'), 10);
                        executeCommandItem(filteredItems[idx]);
                    });
                });
            }

            function executeCommandItem(item) {
                if (!item) return;
                closeCommandPalette();

                if (item.action === 'toggleTheme') {
                    if (window.toggleAdminTheme) window.toggleAdminTheme();
                } else if (item.external) {
                    window.open(item.url, '_blank');
                } else if (item.url) {
                    window.location.href = item.url;
                }
            }

            function escapeHtml(str) {
                return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            }

            if (cmdTrigger) cmdTrigger.addEventListener('click', openCommandPalette);
            if (cmdCloseChip) cmdCloseChip.addEventListener('click', closeCommandPalette);
            if (cmdBackdrop) {
                cmdBackdrop.addEventListener('click', function(e) {
                    if (e.target === cmdBackdrop) closeCommandPalette();
                });
            }

            if (cmdInput) {
                cmdInput.addEventListener('input', function() {
                    activeIndex = 0;
                    renderCommandResults(this.value);
                });

                cmdInput.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (filteredItems.length > 0) {
                            activeIndex = (activeIndex + 1) % filteredItems.length;
                            updateActiveCommandItem();
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (filteredItems.length > 0) {
                            activeIndex = (activeIndex - 1 + filteredItems.length) % filteredItems.length;
                            updateActiveCommandItem();
                        }
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        if (filteredItems[activeIndex]) {
                            executeCommandItem(filteredItems[activeIndex]);
                        }
                    } else if (e.key === 'Escape') {
                        closeCommandPalette();
                    }
                });
            }

            function updateActiveCommandItem() {
                const items = cmdResults.querySelectorAll('.cmd-item');
                items.forEach((el, idx) => {
                    if (idx === activeIndex) {
                        el.classList.add('active');
                        el.scrollIntoView({ block: 'nearest' });
                    } else {
                        el.classList.remove('active');
                    }
                });
            }

            // 8. Keyboard Shortcuts Modal (?)
            const shortcutsBackdrop = document.getElementById('shortcutsModalBackdrop');
            const shortcutsBtn = document.getElementById('shortcutsModalBtn');
            const closeShortcutsBtn = document.getElementById('closeShortcutsBtn');

            function openShortcutsModal() {
                if (shortcutsBackdrop) shortcutsBackdrop.classList.add('active');
            }
            function closeShortcutsModal() {
                if (shortcutsBackdrop) shortcutsBackdrop.classList.remove('active');
            }

            if (shortcutsBtn) shortcutsBtn.addEventListener('click', openShortcutsModal);
            if (closeShortcutsBtn) closeShortcutsBtn.addEventListener('click', closeShortcutsModal);
            if (shortcutsBackdrop) {
                shortcutsBackdrop.addEventListener('click', function(e) {
                    if (e.target === shortcutsBackdrop) closeShortcutsModal();
                });
            }

            // 9. Global Keyboard Shortcuts Listener
            document.addEventListener('keydown', function(e) {
                const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
                const isTyping = activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select' || (document.activeElement && document.activeElement.isContentEditable);

                // Ctrl + K / Cmd + K : Open Spotlight Command Palette
                if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    if (cmdBackdrop && cmdBackdrop.classList.contains('active')) {
                        closeCommandPalette();
                    } else {
                        openCommandPalette();
                    }
                    return;
                }

                // Ctrl + B : Toggle Sidebar Collapse
                if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
                    e.preventDefault();
                    document.body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem('admin-sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
                    return;
                }

                // Escape: Close active modals
                if (e.key === 'Escape') {
                    if (cmdBackdrop && cmdBackdrop.classList.contains('active')) closeCommandPalette();
                    if (shortcutsBackdrop && shortcutsBackdrop.classList.contains('active')) closeShortcutsModal();
                    const orderModal = document.getElementById('orderDetailsModal');
                    if (orderModal && orderModal.classList.contains('active')) orderModal.classList.remove('active');
                    return;
                }

                // Navigation Shortcuts (when not typing in form inputs)
                if (!isTyping) {
                    if (e.key === '?') {
                        e.preventDefault();
                        openShortcutsModal();
                    } else if (e.shiftKey && (e.key === 'P' || e.key === 'p')) {
                        e.preventDefault();
                        window.location.href = 'products.php';
                    } else if (e.shiftKey && (e.key === 'O' || e.key === 'o')) {
                        e.preventDefault();
                        window.location.href = 'orders.php';
                    } else if (e.shiftKey && (e.key === 'D' || e.key === 'd')) {
                        e.preventDefault();
                        window.location.href = 'dashboard.php';
                    } else if (e.shiftKey && (e.key === 'B' || e.key === 'b')) {
                        e.preventDefault();
                        window.location.href = 'blogs.php';
                    } else if (e.shiftKey && (e.key === 'M' || e.key === 'm')) {
                        e.preventDefault();
                        window.location.href = 'media.php';
                    } else if (e.shiftKey && (e.key === 'T' || e.key === 't')) {
                        e.preventDefault();
                        if (window.toggleAdminTheme) window.toggleAdminTheme();
                    }
                }
            });

            // 10. Global Toast Notification System
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

                // Auto-dismiss after 6 seconds
                setTimeout(() => {
                    dismissToast(toast);
                }, 6000);
            };

            function dismissToast(toast) {
                if (!toast) return;
                toast.classList.add('toast-fadeout');
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
