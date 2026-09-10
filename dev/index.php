<?php
// dev/index.php - Apex Developer Master Dashboard
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

// Handle quick dashboard actions (Toggle Maintenance Mode, Flush Cache, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security token expired. Please reload.';
    } else {
        try {
            if ($action === 'toggle_maintenance') {
                $curr = get_site_setting('maintenance_mode', '0');
                $newMode = ($curr === '1') ? '0' : '1';
                
                $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'");
                $stmt->execute([$newMode]);
                $successMsg = ($newMode === '1') ? 'Maintenance Mode is now ACTIVE. Public visitors will see the maintenance page.' : 'Maintenance Mode is now DEACTIVATED. The website is LIVE.';
            } elseif ($action === 'toggle_store_mode') {
                $currStore = get_site_setting('store_mode', 'retail');
                $newStore = ($currStore === 'bulk') ? 'retail' : 'bulk';
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('store_mode', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$newStore, $newStore]);
                $successMsg = "Store Mode switched to: " . strtoupper($newStore) . " Mode.";
            } elseif ($action === 'force_logout_all') {
                $newSalt = (string)time();
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('admin_session_salt', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$newSalt, $newSalt]);
                $successMsg = 'Emergency Session Lockdown Triggered! All active administrator sessions have been invalidated across all devices.';
            } elseif ($action === 'flush_cache') {
                // Clear site settings static cache in current process and any cache files in data/cache
                $cacheDir = __DIR__ . '/../data/cache';
                $clearedFiles = 0;
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*.json');
                    foreach ($files as $f) {
                        if (is_file($f)) {
                            unlink($f);
                            $clearedFiles++;
                        }
                    }
                }
                $successMsg = "System and blog caches successfully flushed ({$clearedFiles} cache files purged).";
            }
        } catch (Exception $e) {
            $errorMsg = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Fetch overview metrics
$maintMode = get_site_setting('maintenance_mode', '0');
$maintTitle = get_site_setting('maintenance_title', "We're Perfecting Something Delicious");
$maintMediaType = get_site_setting('maintenance_media_type', 'both');
$maintImage = get_site_setting('maintenance_image', 'assets/ph.png');
$maintVideo = get_site_setting('maintenance_video', 'https://www.youtube.com/watch?v=kY3Pvdq0YpY');
$siteUrl = get_site_setting('site_url', 'https://www.rtchocos.com');
$betaUrl = get_site_setting('beta_url', 'https://www.rtchocos.com/beta');
$bypassKey = get_site_setting('maintenance_bypass_key', 'rtdev2026');

// Admins summary
$admins = [];
try {
    $stmt = $pdo->query("SELECT id, username, is_active, created_at, last_login FROM admins ORDER BY id ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // non-fatal
}
$storeMode = get_site_setting('store_mode', 'retail');
$showTester = get_site_setting('show_theme_tester', '0');

// Additional commerce & system metrics
$pendingOrders = 0;
$unreadContacts = 0;
$activeProducts = 0;
try {
    $pendingOrders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE order_status = 'pending'")->fetchColumn();
    $unreadContacts = (int)$pdo->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetchColumn();
    $activeProducts = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
} catch (Exception $e) {
    // non-fatal
}

$csrfToken = generate_csrf();
render_dev_header("Master Dashboard", "dashboard");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>Apex Master Command Center</h2>
        <p>Complete root authority over database, store operations, administration, styling, and server health</p>
    </div>
    
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <!-- Emergency Session Lockdown Button -->
        <form action="index.php" method="POST" style="margin: 0;" onsubmit="return confirm('EMERGENCY ACTION: Terminate all active administrator sessions immediately across all devices?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="force_logout_all">
            <button type="submit" class="dev-btn dev-btn-danger" title="Instantly force-disconnect all logged-in administrators">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Force Logout Admins</span>
            </button>
        </form>

        <!-- Toggle Store Mode -->
        <form action="index.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="toggle_store_mode">
            <button type="submit" class="dev-btn dev-btn-outline" title="Switch between Retail Mode and Bulk B2B Mode">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Mode: <strong style="color: <?php echo ($storeMode === 'bulk') ? 'var(--dev-cyan)' : 'var(--dev-emerald)'; ?>;"><?php echo strtoupper($storeMode); ?></strong></span>
            </button>
        </form>

        <!-- Instant Maintenance Mode Toggle Button -->
        <form action="index.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="toggle_maintenance">
            <?php if ($maintMode === '1'): ?>
                <button type="submit" class="dev-btn dev-btn-danger" style="box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    <span>Turn Maintenance OFF</span>
                </button>
            <?php else: ?>
                <button type="submit" class="dev-btn dev-btn-warning">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Turn Maintenance ON</span>
                </button>
            <?php endif; ?>
        </form>

        <form action="index.php" method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="flush_cache">
            <button type="submit" class="dev-btn dev-btn-outline" title="Clear site settings and blog json cache">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Flush Cache</span>
            </button>
        </form>
    </div>
</div>

<!-- 4 Key Stat Cards -->
<div class="dev-grid-4">
    <!-- Stat 1: Maintenance Mode -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">System State</div>
            <div class="dev-stat-value" style="color: <?php echo ($maintMode === '1') ? 'var(--dev-crimson)' : 'var(--dev-emerald)'; ?>;">
                <?php echo ($maintMode === '1') ? 'MAINTENANCE' : 'ONLINE'; ?>
            </div>
            <div class="dev-stat-sub">
                <?php echo ($maintMode === '1') ? 'Public visitors see 503 screen' : 'Full public access active'; ?>
            </div>
        </div>
        <div class="dev-stat-icon" style="color: <?php echo ($maintMode === '1') ? 'var(--dev-crimson)' : 'var(--dev-emerald)'; ?>;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
    </div>

    <!-- Stat 2: Store Mode & Operations -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Catalog Mode</div>
            <div class="dev-stat-value" style="color: <?php echo ($storeMode === 'bulk') ? 'var(--dev-cyan)' : 'var(--dev-emerald)'; ?>;">
                <?php echo strtoupper($storeMode); ?>
            </div>
            <div class="dev-stat-sub">
                <?php echo $activeProducts; ?> Active Products &bull; <a href="store-ops.php" style="color: var(--dev-cyan); text-decoration: none;">Configure &rarr;</a>
            </div>
        </div>
        <div class="dev-stat-icon" style="color: <?php echo ($storeMode === 'bulk') ? 'var(--dev-cyan)' : 'var(--dev-emerald)'; ?>;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
    </div>

    <!-- Stat 3: Commerce Activity -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Pending Orders</div>
            <div class="dev-stat-value" style="color: <?php echo ($pendingOrders > 0) ? 'var(--dev-gold)' : '#fff'; ?>;">
                <?php echo $pendingOrders; ?> Orders
            </div>
            <div class="dev-stat-sub">
                <?php echo $unreadContacts; ?> unread inquiries &bull; <a href="/admin/orders.php" style="color: var(--dev-cyan); text-decoration: none;">View orders &rarr;</a>
            </div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-gold);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
    </div>

    <!-- Stat 4: Admin Hierarchy -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Admin Hierarchy</div>
            <div class="dev-stat-value"><?php echo $totalAdmins; ?> Admins</div>
            <div class="dev-stat-sub"><a href="admins.php" style="color: var(--dev-cyan); text-decoration: none;">Manage credentials &rarr;</a></div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-cyan);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>
</div>

<!-- Quick Operations Grid -->
<div class="dev-card" style="margin-bottom: 28px;">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Master Navigation Hub</span>
        </h3>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
        <a href="store-ops.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--dev-cyan);"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            <span>Store Operations</span>
        </a>
        <a href="database.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--dev-emerald);"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            <span>Database Console</span>
        </a>
        <a href="design.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--dev-gold);"><path d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            <span>Theme & Scripts</span>
        </a>
        <a href="system.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--dev-purple);"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
            <span>System Diagnostics</span>
        </a>
        <a href="ai-cache.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #38bdf8;"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            <span>AI Knowledge Cache</span>
        </a>
        <a href="maintenance.php" class="dev-btn dev-btn-outline" style="justify-content: flex-start; padding: 12px 14px;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #fca5a5;"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Maintenance Studio</span>
        </a>
    </div>
</div>

    <!-- Stat 2: Admin Accounts -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Admin Hierarchy</div>
            <div class="dev-stat-value"><?php echo $totalAdmins; ?> Admins</div>
            <div class="dev-stat-sub"><a href="admins.php" style="color: var(--dev-cyan); text-decoration: none;">Manage credentials &rarr;</a></div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-cyan);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
    </div>

    <!-- Stat 3: Site URL -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Primary Site URL</div>
            <div class="dev-stat-value" style="font-size: 16px; word-break: break-all;">
                <?php echo htmlspecialchars($siteUrl); ?>
            </div>
            <div class="dev-stat-sub"><a href="urls.php" style="color: var(--dev-cyan); text-decoration: none;">Configure routes &rarr;</a></div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-gold);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
        </div>
    </div>

    <!-- Stat 4: Server Diagnostics -->
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Environment</div>
            <div class="dev-stat-value" style="font-size: 18px;">PHP <?php echo phpversion(); ?></div>
            <div class="dev-stat-sub">DB: <?php echo htmlspecialchars($_ENV['DB_NAME'] ?? 'MySQL'); ?></div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-purple);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
        </div>
    </div>
</div>

<!-- 2-Column Split: Admin Management & Maintenance Studio Overview -->
<div class="dev-grid-2">
    <!-- Admin Hierarchy Quick Control -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Administrators Control</span>
            </h3>
            <a href="admins.php" class="dev-btn dev-btn-primary dev-btn-sm">+ New Admin</a>
        </div>

        <div class="dev-table-wrap">
            <table class="dev-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="text-align: right;">Master Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($admins)): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--dev-text-sub); padding: 24px;">No administrator accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($admins as $admin): ?>
                            <tr>
                                <td style="font-weight: 600; color: #fff; display: flex; align-items: center; gap: 8px;">
                                    <span style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo (!isset($admin['is_active']) || (int)$admin['is_active'] === 1) ? 'var(--dev-emerald)' : 'var(--dev-crimson)'; ?>;"></span>
                                    <?php echo htmlspecialchars($admin['username']); ?>
                                </td>
                                <td>
                                    <?php if (!isset($admin['is_active']) || (int)$admin['is_active'] === 1): ?>
                                        <span class="dev-pill dev-pill-success">ACTIVE</span>
                                    <?php else: ?>
                                        <span class="dev-pill dev-pill-danger">SUSPENDED</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-family: 'Fira Code', monospace; font-size: 11px;">
                                    <?php echo date('M d, Y', strtotime($admin['created_at'])); ?>
                                </td>
                                <td style="text-align: right;">
                                    <a href="admins.php?reset=<?php echo urlencode($admin['username']); ?>" class="dev-btn dev-btn-outline dev-btn-sm" style="padding: 4px 8px; font-size: 11px;">
                                        Reset Password
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 16px; text-align: right;">
            <a href="admins.php" class="dev-btn dev-btn-outline dev-btn-sm" style="width: 100%; justify-content: center;">
                Manage All Admins & Access Rights &rarr;
            </a>
        </div>
    </div>

    <!-- Maintenance Studio Preview -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <span>Maintenance Studio</span>
            </h3>
            <a href="/maintenance.php?preview=1" target="_blank" class="dev-btn dev-btn-outline dev-btn-sm">
                Live Preview
            </a>
        </div>

        <div style="margin-bottom: 16px;">
            <div style="font-size: 12px; color: var(--dev-text-sub); text-transform: uppercase; font-family: 'Fira Code', monospace; margin-bottom: 4px;">Active Headline</div>
            <div style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 12px;">
                "<?php echo htmlspecialchars($maintTitle); ?>"
            </div>

            <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px;">
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 12px;">
                    <span style="color: var(--dev-text-sub);">Media Display:</span> 
                    <strong style="color: var(--dev-cyan); text-transform: uppercase;"><?php echo htmlspecialchars($maintMediaType); ?></strong>
                </div>
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 8px 12px; font-size: 12px;">
                    <span style="color: var(--dev-text-sub);">Secret Bypass Key:</span> 
                    <code style="color: var(--dev-emerald);"><?php echo htmlspecialchars($bypassKey); ?></code>
                </div>
            </div>

            <!-- Media Preview Thumb -->
            <div style="display: flex; gap: 14px; align-items: center; background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 10px; padding: 12px;">
                <?php if (!empty($maintImage)): ?>
                    <img src="/<?php echo ltrim($maintImage, '/'); ?>" alt="Maintenance Picture" style="width: 70px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid var(--dev-border-subtle);">
                <?php endif; ?>
                <div style="overflow: hidden; font-size: 12.5px;">
                    <div style="color: #fff; font-weight: 600; margin-bottom: 2px;">Configured Media Content</div>
                    <div style="color: var(--dev-text-muted); font-size: 11px; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">
                        Video: <?php echo htmlspecialchars($maintVideo ?: 'None'); ?>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <a href="maintenance.php" class="dev-btn dev-btn-primary dev-btn-sm" style="width: 100%; justify-content: center;">
                Configure Maintenance Content & Media &rarr;
            </a>
        </div>
    </div>
</div>

<?php
render_dev_footer();
?>
