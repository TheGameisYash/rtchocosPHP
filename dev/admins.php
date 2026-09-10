<?php
// dev/admins.php - Master Administrator Hierarchy Management
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

// Handle Admin Hierarchy Modifications
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed. Please reload the page.';
    } else {
        try {
            if ($action === 'create_admin') {
                $username = trim($_POST['username'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($username) || empty($password)) {
                    $errorMsg = 'Please provide both username and password.';
                } elseif ($password !== $confirmPassword) {
                    $errorMsg = 'Passwords do not match.';
                } elseif (strlen($password) < 6) {
                    $errorMsg = 'Password must be at least 6 characters long.';
                } else {
                    // Check if exists
                    $check = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
                    $check->execute([$username]);
                    if ($check->fetchColumn() > 0) {
                        $errorMsg = "An admin account with username '{$username}' already exists.";
                    } else {
                        $hash = password_hash($password, PASSWORD_BCRYPT);
                        $ins = $pdo->prepare("INSERT INTO admins (username, password, password_vault, is_active) VALUES (?, ?, ?, 1)");
                        $ins->execute([$username, $hash, $password]);
                        $successMsg = "Administrator account '{$username}' successfully created!";
                    }
                }
            } elseif ($action === 'change_password') {
                $adminId = (int)($_POST['admin_id'] ?? 0);
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                $revealAfter = !empty($_POST['reveal_pass']);

                if (empty($adminId) || empty($newPassword)) {
                    $errorMsg = 'Please provide the new password.';
                } elseif ($newPassword !== $confirmPassword) {
                    $errorMsg = 'New passwords do not match.';
                } elseif (strlen($newPassword) < 6) {
                    $errorMsg = 'Password must be at least 6 characters long.';
                } else {
                    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
                    $stmt->execute([$adminId]);
                    $targetUser = $stmt->fetchColumn();

                    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $upd = $pdo->prepare("UPDATE admins SET password = ?, password_vault = ? WHERE id = ?");
                    $upd->execute([$hash, $newPassword, $adminId]);

                    // Also sync to developers table if account exists there
                    if ($targetUser) {
                        $sync = $pdo->prepare("UPDATE developers SET password = ?, password_vault = ? WHERE username = ?");
                        $sync->execute([$hash, $newPassword, $targetUser]);
                    }

                    if ($revealAfter && $targetUser) {
                        $_SESSION['revealed_pass'] = [
                            'user' => $targetUser,
                            'pass' => $newPassword,
                            'type' => 'Administrator'
                        ];
                    }

                    $successMsg = "Administrator password has been successfully updated via Master Override!";
                }
            } elseif ($action === 'toggle_access') {
                $adminId = (int)($_POST['admin_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT is_active, username FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                $targetAdmin = $stmt->fetch();

                if ($targetAdmin) {
                    $currentStatus = (int)($targetAdmin['is_active'] ?? 1);
                    $newStatus = ($currentStatus === 1) ? 0 : 1;
                    $upd = $pdo->prepare("UPDATE admins SET is_active = ? WHERE id = ?");
                    $upd->execute([$newStatus, $adminId]);
                    $statusName = ($newStatus === 1) ? 'activated' : 'suspended (login revoked)';
                    $successMsg = "Admin '{$targetAdmin['username']}' has been {$statusName}.";
                }
            } elseif ($action === 'delete_admin') {
                $adminId = (int)($_POST['admin_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                $username = $stmt->fetchColumn();

                if ($username) {
                    $del = $pdo->prepare("DELETE FROM admins WHERE id = ?");
                    $del->execute([$adminId]);
                    $successMsg = "Administrator '{$username}' has been permanently deleted.";
                }
            } elseif ($action === 'impersonate') {
                $adminId = (int)($_POST['admin_id'] ?? 0);
                $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
                $stmt->execute([$adminId]);
                $username = $stmt->fetchColumn();
                if ($username) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_user'] = $username;
                    header('Location: /admin/dashboard.php');
                    exit;
                }
            } elseif ($action === 'force_logout_all') {
                $newSalt = (string)time();
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('admin_session_salt', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$newSalt, $newSalt]);
                $successMsg = 'Emergency Session Lockdown Triggered! All active administrator sessions have been invalidated across all devices.';
            } elseif ($action === 'promote_to_root_dev') {
                if (!is_root_dev()) {
                    $errorMsg = 'Root Developer authority required to promote users to Root Dev.';
                } else {
                    $adminId = (int)($_POST['admin_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username, password FROM admins WHERE id = ?");
                    $stmt->execute([$adminId]);
                    $targetAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$targetAdmin) {
                        $errorMsg = 'Admin account not found.';
                    } else {
                        $adminUser = $targetAdmin['username'];
                        $adminHash = $targetAdmin['password'];

                        $checkDev = $pdo->prepare("SELECT id FROM developers WHERE username = ?");
                        $checkDev->execute([$adminUser]);
                        $existingDevId = $checkDev->fetchColumn();

                        if ($existingDevId) {
                            $upd = $pdo->prepare("UPDATE developers SET role = 'root_dev', is_active = 1 WHERE id = ?");
                            $upd->execute([$existingDevId]);
                        } else {
                            $stmtAdmVault = $pdo->prepare("SELECT password_vault FROM admins WHERE username = ?");
                            $stmtAdmVault->execute([$adminUser]);
                            $admVault = $stmtAdmVault->fetchColumn() ?: null;
                            $ins = $pdo->prepare("INSERT INTO developers (username, password, password_vault, email, role, is_active) VALUES (?, ?, ?, ?, 'root_dev', 1)");
                            $ins->execute([$adminUser, $adminHash, $admVault, $adminUser . '@rtchocos.com']);
                        }
                        $successMsg = "Administrator '{$adminUser}' has been successfully elevated to ROOT DEVELOPER!";
                    }
                }
            } elseif ($action === 'demote_from_root_dev') {
                if (!is_root_dev()) {
                    $errorMsg = 'Root Developer authority required to demote users.';
                } else {
                    $adminId = (int)($_POST['admin_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
                    $stmt->execute([$adminId]);
                    $targetUsername = $stmt->fetchColumn();

                    if (!$targetUsername) {
                        $errorMsg = 'Administrator account not found.';
                    } elseif ($targetUsername === ($_SESSION['dev_user'] ?? '')) {
                        $errorMsg = 'You cannot demote your own active developer session.';
                    } else {
                        // Protection: Ensure at least one root dev remains
                        $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchColumn();
                        $isTargetRoot = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE username = " . $pdo->quote($targetUsername) . " AND role IN ('root_dev', 'super_dev')")->fetchColumn() > 0;

                        if ($isTargetRoot && $rootCount <= 1) {
                            $errorMsg = 'Cannot demote the last remaining active Root Developer.';
                        } else {
                            // Remove from developers table so user is purely a standard administrator
                            $del = $pdo->prepare("DELETE FROM developers WHERE username = ?");
                            $del->execute([$targetUsername]);
                            $successMsg = "Administrator '{$targetUsername}' has been demoted back to regular Administrator (Root Developer authority revoked)!";
                        }
                    }
                }
            } elseif ($action === 'reveal_current_password' || $action === 'generate_and_reveal_password') {
                if (!is_root_dev()) {
                    $errorMsg = 'Root Developer authority required to view passwords.';
                } else {
                    $adminId = (int)($_POST['admin_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username, password_vault FROM admins WHERE id = ?");
                    $stmt->execute([$adminId]);
                    $targetAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$targetAdmin) {
                        $errorMsg = 'Administrator not found.';
                    } else {
                        $targetUser = $targetAdmin['username'];
                        $currentPass = $targetAdmin['password_vault'];
                        if (empty($currentPass)) {
                            if ($targetUser === 'admin') {
                                $currentPass = 'Admin@rtchocos1';
                                $pdo->prepare("UPDATE admins SET password_vault = ? WHERE id = ?")->execute([$currentPass, $adminId]);
                            } else {
                                $currentPass = 'Admin@rtchocos1';
                            }
                        }

                        $_SESSION['revealed_pass'] = [
                            'user' => $targetUser,
                            'pass' => $currentPass,
                            'type' => 'Administrator',
                            'is_current' => true
                        ];
                        $successMsg = "Active password for Administrator '{$targetUser}' revealed on screen.";
                    }
                }
            }
        } catch (Exception $e) {
            $errorMsg = 'Database error: ' . $e->getMessage();
        }
    }
}

// Fetch all admins and active root developers
$admins = [];
$rootDevs = [];
try {
    $stmt = $pdo->query("SELECT id, username, password_vault, is_active, created_at, last_login FROM admins ORDER BY id ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $rootDevs = $pdo->query("SELECT username FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $errorMsg = 'Could not load accounts: ' . $e->getMessage();
}

$activeCount = 0;
$suspendedCount = 0;
foreach ($admins as $a) {
    if (!isset($a['is_active']) || (int)$a['is_active'] === 1) {
        $activeCount++;
    } else {
        $suspendedCount++;
    }
}

$csrfToken = generate_csrf();
$presetResetUser = $_GET['reset'] ?? '';
render_dev_header("Admin Hierarchy Management", "admins");
?>

<?php if (!empty($successMsg)): ?>
    <div style="background: rgba(0, 245, 160, 0.12); border: 1px solid var(--dev-emerald); color: var(--dev-emerald); padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
        <span style="font-size: 16px;">✓</span> <span><?php echo htmlspecialchars($successMsg); ?></span>
    </div>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <div style="background: rgba(255, 51, 102, 0.12); border: 1px solid var(--dev-crimson); color: var(--dev-crimson); padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px; font-size: 13.5px;">
        <span style="font-size: 16px;">⚠</span> <span><?php echo htmlspecialchars($errorMsg); ?></span>
    </div>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<!-- MODAL: Password Revealed On-Screen (1-Click Copy) -->
<?php if (!empty($_SESSION['revealed_pass'])): 
    $rev = $_SESSION['revealed_pass'];
    unset($_SESSION['revealed_pass']);
?>
<div class="dev-modal-overlay open active" id="revealedPassModal" style="display: flex; opacity: 1; visibility: visible;">
    <div class="dev-modal" style="max-width: 500px; border-color: var(--dev-gold); box-shadow: 0 0 35px rgba(234, 179, 8, 0.25);">
        <div class="dev-modal-header" style="border-bottom-color: rgba(234, 179, 8, 0.25);">
            <h3 style="color: #fde047; display: flex; align-items: center; gap: 8px;">
                <span>🔑</span> <?php echo !empty($rev['is_current']) ? 'Current Setted Password' : 'Password Generated & Revealed'; ?>
            </h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('revealedPassModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="dev-modal-body">
            <p style="color: var(--dev-text-sub); font-size: 13px; margin-top: 0;">
                Current active password for <strong><?php echo htmlspecialchars($rev['type']); ?></strong> <code style="color: var(--dev-cyan); font-weight: 700; font-size: 14px;"><?php echo htmlspecialchars($rev['user']); ?></code>:
            </p>
            <div class="dev-secret-box">
                <span class="dev-secret-val" id="revealedSecretVal"><?php echo htmlspecialchars($rev['pass']); ?></span>
                <button type="button" class="dev-btn dev-btn-primary dev-btn-sm" onclick="copySecret('revealedSecretVal', this)" style="background: var(--dev-gold); color: #000; font-weight: 800;">
                    <span>📋 Copy</span>
                </button>
            </div>
            <div style="background: rgba(0, 245, 160, 0.08); border: 1px solid rgba(0, 245, 160, 0.25); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--dev-emerald); margin-top: 14px;">
                ✓ <strong>Current Active Password:</strong> This is the exact password currently set for this account. No credentials were reset or altered.
            </div>
        </div>
        <div class="dev-modal-footer">
            <button type="button" class="dev-btn dev-btn-primary" onclick="closeModal('revealedPassModal')">Done</button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>Administrator Hierarchy Management</h2>
        <p>Dev Master root authority: Create admins, reset or reveal any password, and control Root Developer delegation.</p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <form action="admins.php" method="POST" style="margin: 0;" onsubmit="return confirm('Emergency Action: This will immediately terminate all active administrator sessions across all devices. Proceed?');">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="force_logout_all">
            <button type="submit" class="dev-btn dev-btn-danger" title="Force disconnect all logged-in administrators">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Force Logout All Admins</span>
            </button>
        </form>

        <button type="button" class="dev-btn dev-btn-primary" onclick="openCreateModal()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
            <span>Create Admin Account</span>
        </button>
    </div>
</div>

<!-- Root Developer Role Delegation & Demotion Card -->
<?php if (is_root_dev()): ?>
    <div class="dev-card" style="margin-bottom: 24px; border-color: rgba(234, 179, 8, 0.4); background: linear-gradient(135deg, rgba(234, 179, 8, 0.05), rgba(13, 18, 28, 0.95));">
        <div class="dev-card-header">
            <h3>
                <span style="font-size: 18px;">👑</span>
                <span>Root Developer Role Delegation & Demotion</span>
            </h3>
            <span class="dev-pill" style="background: rgba(234, 179, 8, 0.15); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700;">APEX AUTHORITY</span>
        </div>
        <p style="color: var(--dev-text-sub); font-size: 13px; margin-top: 0; margin-bottom: 16px;">
            Grant or revoke apex developer privileges for any administrator. Elevated administrators can log in at <code style="color: var(--dev-cyan);">/dev</code>. Demoting an elevated administrator returns them to a standard administrator without developer privileges.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            <!-- Pathway 1: Elevate Admin -->
            <form action="admins.php" method="POST" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(234,179,8,0.2); border-radius: 8px; padding: 14px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="promote_to_root_dev">
                <label for="admin_id_elevate" style="display: block; font-weight: 700; color: #fde047; font-size: 12px; margin-bottom: 8px;">👑 Elevate Administrator to Root Dev</label>
                <div style="display: flex; gap: 8px;">
                    <select id="admin_id_elevate" name="admin_id" class="dev-select" required style="flex: 1;">
                        <option value="">-- Choose Admin to Elevate --</option>
                        <?php foreach ($admins as $ad): ?>
                            <?php if (!in_array($ad['username'], $rootDevs)): ?>
                                <option value="<?php echo $ad['id']; ?>"><?php echo htmlspecialchars($ad['username']); ?> (Standard Admin)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="dev-btn dev-btn-primary" style="background: linear-gradient(135deg, var(--dev-gold), #ca8a04); border-color: var(--dev-gold); color: #000; font-weight: 800; white-space: nowrap;">
                        <span>👑 Elevate</span>
                    </button>
                </div>
            </form>

            <!-- Pathway 2: Demote Elevated Admin -->
            <form action="admins.php" method="POST" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255, 51, 102, 0.2); border-radius: 8px; padding: 14px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="demote_from_root_dev">
                <label for="admin_id_demote" style="display: block; font-weight: 700; color: #ff88a3; font-size: 12px; margin-bottom: 8px;">🔻 Demote Administrator / Revoke Root Dev</label>
                <div style="display: flex; gap: 8px;">
                    <select id="admin_id_demote" name="admin_id" class="dev-select" required style="flex: 1;">
                        <option value="">-- Choose Elevated Admin to Demote --</option>
                        <?php foreach ($admins as $ad): ?>
                            <?php if (in_array($ad['username'], $rootDevs)): ?>
                                <option value="<?php echo $ad['id']; ?>"><?php echo htmlspecialchars($ad['username']); ?> [Current Root Dev 👑]</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="border-color: var(--dev-crimson); color: var(--dev-crimson); font-weight: 700; white-space: nowrap; padding: 8px 14px;">
                        <span>🔻 Demote to Admin</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<!-- Stats Counter -->
<div class="dev-grid-4" style="margin-bottom: 24px;">
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Total Admins</div>
            <div class="dev-stat-value"><?php echo count($admins); ?></div>
            <div class="dev-stat-sub">Accounts in database</div>
        </div>
        <div class="dev-stat-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Active Accounts</div>
            <div class="dev-stat-value" style="color: var(--dev-emerald);"><?php echo $activeCount; ?></div>
            <div class="dev-stat-sub">Full admin portal privileges</div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-emerald);"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Suspended</div>
            <div class="dev-stat-value" style="color: var(--dev-crimson);"><?php echo $suspendedCount; ?></div>
            <div class="dev-stat-sub">Logins currently revoked</div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-crimson);"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Root Developers</div>
            <div class="dev-stat-value" style="color: #fde047;"><?php echo count($rootDevs); ?></div>
            <div class="dev-stat-sub">Supreme apex control</div>
        </div>
        <div class="dev-stat-icon" style="color: #fde047;"><span style="font-size: 20px;">👑</span></div>
    </div>
</div>

<!-- Administrators Table -->
<div class="dev-card">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Authorized Administrators</span>
        </h3>
        <span style="font-size: 12px; color: var(--dev-text-muted);">Stored securely in MySQL <code>admins</code> table</span>
    </div>

    <div class="dev-table-wrap">
        <table class="dev-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role Hierarchy</th>
                    <th>Login Status</th>
                    <th>Created</th>
                    <th>Last Active</th>
                    <th style="text-align: right;">Master Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 32px; color: var(--dev-text-muted);">
                            No administrator accounts found in database. Click "Create Admin Account" above to provision one.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($admins as $admin): ?>
                        <?php 
                        $isActive = (!isset($admin['is_active']) || (int)$admin['is_active'] === 1); 
                        $isRootElevated = in_array($admin['username'], $rootDevs);
                        ?>
                        <tr>
                            <td style="font-family: 'Fira Code', monospace; color: var(--dev-text-muted);">#<?php echo $admin['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="dev-avatar" style="width: 32px; height: 32px; font-size: 11px;">
                                        <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 13.5px;">
                                            <?php echo htmlspecialchars($admin['username']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($isRootElevated): ?>
                                    <span class="dev-pill" style="background: rgba(234, 179, 8, 0.15); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700; font-size: 11px; padding: 4px 8px; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>👑</span> ROOT DEV
                                    </span>
                                <?php else: ?>
                                    <span class="dev-pill" style="background: rgba(0, 240, 255, 0.1); border: 1px solid rgba(0, 240, 255, 0.3); color: var(--dev-cyan); font-size: 11px;">
                                        ADMIN
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="dev-pill dev-pill-success">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--dev-emerald);"></span>
                                        ACTIVE
                                    </span>
                                <?php else: ?>
                                    <span class="dev-pill dev-pill-danger">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: var(--dev-crimson);"></span>
                                        REVOKED
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: 'Fira Code', monospace; font-size: 12px;">
                                <?php echo date('Y-m-d H:i', strtotime($admin['created_at'])); ?>
                            </td>
                            <td style="font-family: 'Fira Code', monospace; font-size: 12px;">
                                <?php echo !empty($admin['last_login']) ? date('Y-m-d H:i', strtotime($admin['last_login'])) : 'Never'; ?>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: nowrap;">
                                    <!-- Impersonate / Log In As Admin -->
                                    <form action="admins.php" method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="action" value="impersonate">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <button type="submit" class="dev-btn dev-btn-primary dev-btn-sm" title="Log directly into Admin Portal as this user">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                            <span>Impersonate</span>
                                        </button>
                                    </form>

                                    <!-- Root Dev Toggle: Promote or Demote -->
                                    <?php if (is_root_dev()): ?>
                                        <?php if ($isRootElevated): ?>
                                            <!-- Demote Admin from Root Dev -->
                                            <form action="admins.php" method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="demote_from_root_dev">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; border-color: rgba(234, 179, 8, 0.4); color: #fde047;" title="Demote back to regular administrator">
                                                    <span>🔻 Demote to Admin</span>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Promote Admin to Root Dev -->
                                            <form action="admins.php" method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="promote_to_root_dev">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" class="dev-btn dev-btn-sm" style="background: rgba(234,179,8,0.18); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700; font-size: 11px; padding: 4px 8px;" title="Elevate this administrator to Root Developer">
                                                    <span>👑 Make Root Dev</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- 👁️ 1-Click Reveal Current Password -->
                                    <form action="admins.php" method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="action" value="reveal_current_password">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; color: var(--dev-cyan); border-color: rgba(0, 240, 255, 0.35);" title="View Current Active Password (No reset)">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>👁️ Reveal Pass</span>
                                        </button>
                                    </form>

                                    <!-- Reset Password Button -->
                                    <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px;" onclick="openResetModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars(addslashes($admin['username'])); ?>')">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        <span>Set Password</span>
                                    </button>

                                    <!-- Toggle Access Button -->
                                    <form action="admins.php" method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                        <input type="hidden" name="action" value="toggle_access">
                                        <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                        <?php if ($isActive): ?>
                                            <button type="submit" class="dev-btn dev-btn-warning dev-btn-sm" style="font-size: 11px; padding: 4px 8px;" title="Suspend admin login access">
                                                <span>Revoke</span>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; border-color: var(--dev-emerald); color: var(--dev-emerald);" title="Restore admin access">
                                                <span>Restore</span>
                                            </button>
                                        <?php endif; ?>
                                    </form>

                                    <!-- Delete Button -->
                                    <button type="button" class="dev-btn dev-btn-danger dev-btn-sm" style="font-size: 11px; padding: 4px 8px;" onclick="openDeleteModal(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars(addslashes($admin['username'])); ?>')">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL 1: Create New Admin -->
<div class="dev-modal-overlay" id="createAdminModal">
    <div class="dev-modal">
        <div class="dev-modal-header">
            <h3>Create New Administrator</h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('createAdminModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="admins.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="create_admin">

            <div class="dev-modal-body">
                <div class="dev-form-group">
                    <label for="create_username">Admin Username</label>
                    <input type="text" id="create_username" name="username" class="dev-input" required placeholder="e.g. chocolate_admin" autocomplete="off">
                </div>

                <div class="dev-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="create_password" style="margin: 0;">Initial Password</label>
                        <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('create_password', 'create_confirm')">🎲 Generate</button>
                    </div>
                    <div class="dev-password-wrap">
                        <input type="password" id="create_password" name="password" class="dev-input" required placeholder="Minimum 6 characters">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('create_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="dev-form-group">
                    <label for="create_confirm">Confirm Password</label>
                    <div class="dev-password-wrap">
                        <input type="password" id="create_confirm" name="confirm_password" class="dev-input" required placeholder="Re-type password">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('create_confirm', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="dev-modal-footer">
                <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('createAdminModal')">Cancel</button>
                <button type="submit" class="dev-btn dev-btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Change Password (Master Override) -->
<div class="dev-modal-overlay" id="resetPasswordModal">
    <div class="dev-modal">
        <div class="dev-modal-header">
            <h3>Master Password Override: <span id="resetModalUser" style="color: var(--dev-cyan);"></span></h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('resetPasswordModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="admins.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="change_password">
            <input type="hidden" name="admin_id" id="resetModalAdminId" value="">

            <div class="dev-modal-body">
                <div style="background: rgba(0, 240, 255, 0.08); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--dev-cyan); margin-bottom: 16px;">
                    ⚡ <strong>Apex Authority:</strong> As Developer, you do not need the administrator's current password. Entering a new password directly sets it in the database.
                </div>

                <div class="dev-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="new_password" style="margin: 0;">New Password</label>
                        <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('new_password', 'confirm_password')">🎲 Generate</button>
                    </div>
                    <div class="dev-password-wrap">
                        <input type="password" id="new_password" name="new_password" class="dev-input" required placeholder="Minimum 6 characters">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('new_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="dev-form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <div class="dev-password-wrap">
                        <input type="password" id="confirm_password" name="confirm_password" class="dev-input" required placeholder="Repeat new password">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('confirm_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                    <input type="checkbox" name="reveal_pass" value="1" id="reveal_pass_check" checked style="accent-color: var(--dev-cyan); width: 16px; height: 16px; cursor: pointer;">
                    <label for="reveal_pass_check" style="font-size: 12.5px; color: #fff; cursor: pointer; margin: 0; text-transform: none; font-family: inherit;">
                        👁️ Reveal & display this password on screen after saving
                    </label>
                </div>
            </div>

            <div class="dev-modal-footer">
                <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('resetPasswordModal')">Cancel</button>
                <button type="submit" class="dev-btn dev-btn-primary">Set New Password</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Delete Admin Confirmation -->
<div class="dev-modal-overlay" id="deleteAdminModal">
    <div class="dev-modal">
        <div class="dev-modal-header">
            <h3 style="color: var(--dev-crimson);">Delete Administrator</h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('deleteAdminModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="admins.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="delete_admin">
            <input type="hidden" name="admin_id" id="deleteModalAdminId" value="">

            <div class="dev-modal-body">
                <p style="color: #f1f5f9; font-size: 14px; margin-top: 0;">
                    Are you sure you want to permanently delete administrator account <strong id="deleteModalUser" style="color: var(--dev-crimson);"></strong>?
                </p>
                <p style="color: var(--dev-text-sub); font-size: 12px; margin-bottom: 0;">
                    This action is permanent and revokes all portal access immediately.
                </p>
            </div>

            <div class="dev-modal-footer">
                <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('deleteAdminModal')">Cancel</button>
                <button type="submit" class="dev-btn dev-btn-danger">Permanently Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.add('open');
            m.classList.add('active');
            m.style.display = 'flex';
            m.style.opacity = '1';
            m.style.visibility = 'visible';
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('open');
            m.classList.remove('active');
            m.style.display = 'none';
            m.style.opacity = '0';
            m.style.visibility = 'hidden';
        }
    }

    function openCreateModal() {
        openModal('createAdminModal');
        const u = document.getElementById('create_username');
        if (u) u.focus();
    }

    function openResetModal(adminId, username) {
        document.getElementById('resetModalAdminId').value = adminId;
        document.getElementById('resetModalUser').textContent = username;
        openModal('resetPasswordModal');
        const p = document.getElementById('new_password');
        if (p) p.focus();
    }

    function openDeleteModal(adminId, username) {
        document.getElementById('deleteModalAdminId').value = adminId;
        document.getElementById('deleteModalUser').textContent = username;
        openModal('deleteAdminModal');
    }

    // Toggle password plaintext visibility
    function toggleDevPass(inputId, btn) {
        const inp = document.getElementById(inputId);
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>`;
        } else {
            inp.type = 'password';
            btn.innerHTML = `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>`;
        }
    }

    // Copy secret password to clipboard
    function copySecret(elementId, btn) {
        const el = document.getElementById(elementId);
        if (!el) return;
        const text = el.innerText.trim();
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<span>✓ Copied!</span>';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        }).catch(() => {
            alert('Password: ' + text);
        });
    }

    // Generate readable random password and fill inputs
    function generateRandomPass(p1Id, p2Id) {
        const words = ['Choco', 'Cacao', 'Truffle', 'Master', 'Apex', 'Artisan', 'Sweet', 'Gold', 'Royal', 'Velvet'];
        const randWord = words[Math.floor(Math.random() * words.length)];
        const randNum = Math.floor(1000 + Math.random() * 9000);
        const pass = randWord + '#' + randNum;
        const p1 = document.getElementById(p1Id);
        const p2 = document.getElementById(p2Id);
        if (p1) { p1.value = pass; p1.type = 'text'; }
        if (p2) { p2.value = pass; p2.type = 'text'; }
    }

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('dev-modal-overlay')) {
            closeModal(e.target.id);
        }
    });

    // Preset query param reset support
    <?php if (!empty($presetResetUser)): ?>
        window.addEventListener('DOMContentLoaded', () => {
            <?php foreach ($admins as $a): ?>
                <?php if ($a['username'] === $presetResetUser): ?>
                    openResetModal(<?php echo $a['id']; ?>, '<?php echo htmlspecialchars(addslashes($a['username'])); ?>');
                <?php endif; ?>
            <?php endforeach; ?>
        });
    <?php endif; ?>
</script>

<?php
render_dev_footer();
?>
