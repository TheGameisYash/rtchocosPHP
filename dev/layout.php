<?php
// dev/layout.php - Shared Developer Shell for RT Chocos Master Control
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Guarantee dev authentication on every page that includes layout
require_dev_auth();

function render_dev_header($title, $activePage = '') {
    $pdo = get_db();
    
    // Fetch live maintenance status & site URL
    $maintenanceMode = '0';
    $siteUrl = 'https://www.rtchocos.com';
    $adminCount = 0;
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings WHERE setting_key IN ('maintenance_mode', 'site_url')");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['setting_key'] === 'maintenance_mode') $maintenanceMode = $row['setting_value'];
            if ($row['setting_key'] === 'site_url') $siteUrl = $row['setting_value'];
        }
        $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    } catch (Exception $e) {
        // non-fatal
    }

    $isMaintenanceActive = ($maintenanceMode === '1');
    $devUser = $_SESSION['dev_user'] ?? 'Developer';
    $isRootUser = is_root_dev();
    $devRole = $isRootUser ? 'root_dev' : ($_SESSION['dev_role'] ?? 'developer');
    $initials = strtoupper(substr($devUser, 0, 2));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> | Master Dev Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="dev-style.css?v=<?php echo filemtime(__DIR__ . '/dev-style.css'); ?>">
    <style>
        .dev-modal-overlay.open, .dev-modal-overlay.active {
            opacity: 1 !important;
            visibility: visible !important;
            display: flex !important;
            pointer-events: auto !important;
        }
        .dev-modal-overlay.open .dev-modal, .dev-modal-overlay.active .dev-modal {
            transform: scale(1) translateY(0) !important;
        }
    </style>
</head>
<body class="dev-body">
    <div class="dev-grid-layer"></div>

    <div class="dev-app-wrap">
        <!-- Sidebar Navigation -->
        <aside class="dev-sidebar" id="devSidebar">
            <div class="dev-brand">
                <div class="dev-brand-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                </div>
                <div class="dev-brand-info">
                    <h1>RT Chocos</h1>
                    <p>Apex Developer &bull; Root</p>
                </div>
            </div>

            <!-- Dev Profile Card -->
            <div class="dev-user-card">
                <div class="dev-avatar"><?php echo htmlspecialchars($initials); ?></div>
                <div class="dev-user-meta">
                    <div class="dev-user-name"><?php echo htmlspecialchars($devUser); ?></div>
                    <div class="dev-user-role" style="<?php echo $isRootUser ? 'color: #fde047; font-weight: 700;' : ''; ?>"><?php echo $isRootUser ? '👑 Root Developer' : htmlspecialchars(ucwords(str_replace('_', ' ', $devRole))); ?></div>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="dev-nav">
                <div class="dev-nav-section-title">Master Website Controls</div>
                <ul>
                    <li class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
                        <a href="index.php" title="Master Developer Dashboard">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zm10-3a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1h-4a1 1 0 01-1-1v-6z"/></svg>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'admins' ? 'active' : ''; ?>">
                        <a href="admins.php" title="Manage Admins & Credentials">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <span>Admin Hierarchy</span>
                            <span class="dev-nav-badge"><?php echo $adminCount; ?></span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'store-ops' ? 'active' : ''; ?>">
                        <a href="store-ops.php" title="Manage Retail/B2B Mode, Announcement & Contacts">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>Store & Operations</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'maintenance' ? 'active' : ''; ?>">
                        <a href="maintenance.php" title="Maintenance Studio & Video/Photo Controls">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <span>Maintenance Studio</span>
                            <?php if ($isMaintenanceActive): ?>
                                <span class="dev-nav-badge active-warn">ACTIVE</span>
                            <?php else: ?>
                                <span class="dev-nav-badge" style="color:#00f5a0;">OFF</span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'design' ? 'active' : ''; ?>">
                        <a href="design.php" title="Theme Palettes & Custom Script Injections">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            <span>Design & Scripts</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'database' ? 'active' : ''; ?>">
                        <a href="database.php" title="Database Tables & SQL Inspector">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                            <span>Database Console</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'urls' ? 'active' : ''; ?>">
                        <a href="urls.php" title="URL & Route Configurations">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span>URL & Routes</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'ai-cache' ? 'active' : ''; ?>">
                        <a href="ai-cache.php" title="Manage AI Cacao Insights & Facts Cache">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <span>AI Insights Cache</span>
                        </a>
                    </li>
                </ul>

                <div class="dev-nav-section-title">System & Security</div>
                <ul>
                    <li class="<?php echo $activePage === 'system' ? 'active' : ''; ?>">
                        <a href="system.php" title="Live SMTP Mailer Test & System Diagnostics">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                            <span>System & SMTP</span>
                        </a>
                    </li>
                    <li class="<?php echo $activePage === 'account' ? 'active' : ''; ?>">
                        <a href="account.php" title="Developer Credentials & Master Security">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <span>Dev Security</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Bottom Actions -->
            <div class="dev-sidebar-footer">
                <a href="/admin/dashboard.php" class="dev-btn dev-btn-outline dev-btn-sm" style="width: 100%; justify-content: center; gap: 8px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Jump to Admin CMS</span>
                </a>
                <a href="logout.php" class="dev-btn dev-btn-danger dev-btn-sm" style="width: 100%; justify-content: center; gap: 8px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main class="dev-main">
            <!-- Topbar Header -->
            <div class="dev-topbar">
                <div class="dev-topbar-left">
                    <button class="dev-btn dev-btn-outline dev-btn-sm" id="devSidebarMobileToggle" style="display: none;" aria-label="Toggle Navigation">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="dev-breadcrumbs">
                        <a href="index.php">Developer Console</a>
                        <span class="sep">&rsaquo;</span>
                        <span class="current"><?php echo htmlspecialchars($title); ?></span>
                    </div>
                </div>

                <div class="dev-topbar-right">
                    <!-- Maintenance Indicator -->
                    <a href="maintenance.php" class="dev-maint-pill <?php echo $isMaintenanceActive ? 'in-maintenance' : 'live'; ?>" title="Click to open Maintenance Studio">
                        <span class="dev-status-dot"></span>
                        <span><?php echo $isMaintenanceActive ? 'MAINTENANCE MODE ON' : 'SITE LIVE'; ?></span>
                    </a>

                    <!-- Jump into Admin CMS (Single Sign-On Apex override) -->
                    <a href="/admin/dashboard.php" class="dev-btn dev-btn-outline dev-btn-sm" title="Seamless Master Admin Portal Access">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Admin Portal</span>
                    </a>

                    <!-- Live Public Site link -->
                    <a href="/" target="_blank" class="dev-btn dev-btn-primary dev-btn-sm" title="Preview Main Site">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Visit Website</span>
                    </a>
                </div>
            </div>

            <!-- Page Content Injection -->
            <div class="dev-content">
<?php
}

function render_dev_footer() {
?>
            </div><!-- /.dev-content -->
        </main>
    </div><!-- /.dev-app-wrap -->

    <!-- Global Toast Messenger -->
    <div id="devToastContainer"></div>

    <script>
        // Toast notification utility
        function showDevToast(message, type = 'success') {
            const container = document.getElementById('devToastContainer');
            const toast = document.createElement('div');
            toast.className = `dev-toast ${type}`;
            toast.innerHTML = `
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    ${type === 'success' 
                        ? '<path d="M5 13l4 4L19 7"/>' 
                        : '<path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'}
                </svg>
                <span>${message}</span>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Mobile sidebar toggle
        const mobBtn = document.getElementById('devSidebarMobileToggle');
        const sidebar = document.getElementById('devSidebar');
        if (window.innerWidth <= 900 && mobBtn) {
            mobBtn.style.display = 'inline-flex';
            mobBtn.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
    </script>
</body>
</html>
<?php
}
?>
