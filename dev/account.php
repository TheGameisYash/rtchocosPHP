<?php
// dev/account.php - Root Developer Security Hub & Multi-Dev Governance
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$successMsg = '';
$errorMsg = '';

$currentDev = $_SESSION['dev_user'] ?? 'dev';
$isRoot = is_root_dev();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed.';
    } else {
        try {
            if ($action === 'change_dev_password') {
                $currentPass = $_POST['current_password'] ?? '';
                $newPass = $_POST['new_password'] ?? '';
                $confirmPass = $_POST['confirm_password'] ?? '';

                $stmt = $pdo->prepare("SELECT password FROM developers WHERE username = ?");
                $stmt->execute([$currentDev]);
                $hash = $stmt->fetchColumn();

                if (empty($currentPass) || empty($newPass)) {
                    $errorMsg = 'Please complete all password fields.';
                } elseif (!password_verify($currentPass, $hash)) {
                    $errorMsg = 'Current developer password does not match.';
                } elseif ($newPass !== $confirmPass) {
                    $errorMsg = 'New passwords do not match.';
                } elseif (strlen($newPass) < 8) {
                    $errorMsg = 'New password must be at least 8 characters long.';
                } else {
                    $newHash = password_hash($newPass, PASSWORD_BCRYPT);
                    $upd = $pdo->prepare("UPDATE developers SET password = ?, password_vault = ? WHERE username = ?");
                    $upd->execute([$newHash, $newPass, $currentDev]);

                    // Sync to admins if exists
                    try {
                        $sync = $pdo->prepare("UPDATE admins SET password = ?, password_vault = ? WHERE username = ?");
                        $sync->execute([$newHash, $newPass, $currentDev]);
                    } catch (Exception $e) {}

                    $successMsg = 'Developer password updated successfully!';
                }
            } elseif ($action === 'add_dev_account') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can provision new developer accounts.';
                } else {
                    $newUsername = trim($_POST['new_username'] ?? '');
                    $newPassword = $_POST['new_password'] ?? '';
                    $email = trim($_POST['email'] ?? '');
                    $role = trim($_POST['role'] ?? 'developer');
                    if (!in_array($role, ['root_dev', 'developer'])) {
                        $role = 'developer';
                    }

                    if (empty($newUsername) || empty($newPassword)) {
                        $errorMsg = 'Username and password required.';
                    } elseif (strlen($newPassword) < 8) {
                        $errorMsg = 'Password must be at least 8 characters.';
                    } else {
                        $check = $pdo->prepare("SELECT COUNT(*) FROM developers WHERE username = ?");
                        $check->execute([$newUsername]);
                        if ($check->fetchColumn() > 0) {
                            $errorMsg = "Developer '{$newUsername}' already exists.";
                        } else {
                            $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                            $ins = $pdo->prepare("INSERT INTO developers (username, password, password_vault, email, role, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                            $ins->execute([$newUsername, $hash, $newPassword, $email, $role]);
                            $roleLabel = ($role === 'root_dev') ? 'ROOT DEVELOPER' : 'Standard Developer';
                            $successMsg = "New {$roleLabel} '{$newUsername}' created successfully!";
                        }
                    }
                }
            } elseif ($action === 'toggle_dev_role') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can promote or demote developer roles.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT id, username, role FROM developers WHERE id = ?");
                    $stmt->execute([$targetDevId]);
                    $target = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$target) {
                        $errorMsg = 'Developer not found.';
                    } elseif ($target['username'] === $currentDev) {
                        $errorMsg = 'You cannot demote your own active developer session.';
                    } else {
                        $currentRole = $target['role'];
                        $newRole = ($currentRole === 'root_dev' || $currentRole === 'super_dev') ? 'developer' : 'root_dev';

                        // Protection: Ensure at least one active root dev remains
                        if ($newRole === 'developer') {
                            $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchColumn();
                            if ($rootCount <= 1) {
                                throw new Exception("Cannot demote the last remaining active Root Developer.");
                            }
                        }

                        $upd = $pdo->prepare("UPDATE developers SET role = ? WHERE id = ?");
                        $upd->execute([$newRole, $targetDevId]);

                        $roleTitle = ($newRole === 'root_dev') ? 'ROOT DEVELOPER 👑' : 'Standard Developer';
                        $successMsg = "Developer '{$target['username']}' role changed to {$roleTitle}!";
                    }
                }
            } elseif ($action === 'revoke_dev_access') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can revoke developer access.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT id, username, role FROM developers WHERE id = ?");
                    $stmt->execute([$targetDevId]);
                    $target = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$target) {
                        $errorMsg = 'Developer account not found.';
                    } elseif ($target['username'] === $currentDev) {
                        $errorMsg = 'You cannot revoke your own active developer account.';
                    } else {
                        if (in_array($target['role'], ['root_dev', 'super_dev'])) {
                            $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchColumn();
                            if ($rootCount <= 1) {
                                throw new Exception("Cannot revoke the last remaining active Root Developer.");
                            }
                        }

                        $del = $pdo->prepare("DELETE FROM developers WHERE id = ?");
                        $del->execute([$targetDevId]);
                        $successMsg = "Developer privileges completely revoked for '{$target['username']}' (demoted/removed from developers)!";
                    }
                }
            } elseif ($action === 'toggle_dev_status') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can toggle developer access.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username, is_active FROM developers WHERE id = ?");
                    $stmt->execute([$targetDevId]);
                    $target = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$target) {
                        $errorMsg = 'Developer not found.';
                    } elseif ($target['username'] === $currentDev) {
                        $errorMsg = 'You cannot suspend your own active developer session.';
                    } else {
                        $newStatus = ((int)$target['is_active'] === 1) ? 0 : 1;
                        $upd = $pdo->prepare("UPDATE developers SET is_active = ? WHERE id = ?");
                        $upd->execute([$newStatus, $targetDevId]);
                        $statusName = ($newStatus === 1) ? 'restored' : 'suspended';
                        $successMsg = "Developer '{$target['username']}' account has been {$statusName}.";
                    }
                }
            } elseif ($action === 'reset_dev_password') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can reset other developers\' passwords.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $newPass = $_POST['new_password'] ?? '';
                    $confirmPass = $_POST['confirm_password'] ?? '';
                    $revealAfter = !empty($_POST['reveal_pass']);

                    if (empty($newPass) || strlen($newPass) < 8) {
                        $errorMsg = 'New password must be at least 8 characters long.';
                    } elseif ($newPass !== $confirmPass) {
                        $errorMsg = 'Passwords do not match.';
                    } else {
                        $stmt = $pdo->prepare("SELECT username FROM developers WHERE id = ?");
                        $stmt->execute([$targetDevId]);
                        $targetUser = $stmt->fetchColumn();

                        $hash = password_hash($newPass, PASSWORD_BCRYPT);
                        $upd = $pdo->prepare("UPDATE developers SET password = ?, password_vault = ? WHERE id = ?");
                        $upd->execute([$hash, $newPass, $targetDevId]);

                        if ($targetUser) {
                            $sync = $pdo->prepare("UPDATE admins SET password = ?, password_vault = ? WHERE username = ?");
                            $sync->execute([$hash, $newPass, $targetUser]);
                        }

                        if ($revealAfter && $targetUser) {
                            $_SESSION['revealed_pass'] = [
                                'user' => $targetUser,
                                'pass' => $newPass,
                                'type' => 'Developer',
                                'is_current' => true
                            ];
                        }

                        $successMsg = 'Developer password reset successfully via Root Master Override!';
                    }
                }
            } elseif ($action === 'reveal_current_password' || $action === 'generate_and_reveal_password') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can view passwords.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username, password_vault FROM developers WHERE id = ?");
                    $stmt->execute([$targetDevId]);
                    $targetDev = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$targetDev) {
                        $errorMsg = 'Developer account not found.';
                    } else {
                        $targetUser = $targetDev['username'];
                        $currentPass = $targetDev['password_vault'];
                        if (empty($currentPass)) {
                            if ($targetUser === 'dev') {
                                $currentPass = 'Dev@rtchocosMaster1';
                                $pdo->prepare("UPDATE developers SET password_vault = ? WHERE id = ?")->execute([$currentPass, $targetDevId]);
                            } else {
                                $currentPass = 'Dev@rtchocosMaster1';
                            }
                        }

                        $_SESSION['revealed_pass'] = [
                            'user' => $targetUser,
                            'pass' => $currentPass,
                            'type' => 'Developer',
                            'is_current' => true
                        ];
                        $successMsg = "Active password for developer '{$targetUser}' revealed on screen.";
                    }
                }
            } elseif ($action === 'delete_dev') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can delete developer accounts.';
                } else {
                    $targetDevId = (int)($_POST['dev_id'] ?? 0);
                    $stmt = $pdo->prepare("SELECT username, role FROM developers WHERE id = ?");
                    $stmt->execute([$targetDevId]);
                    $target = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$target) {
                        $errorMsg = 'Developer not found.';
                    } elseif ($target['username'] === $currentDev) {
                        $errorMsg = 'You cannot delete your own active developer account.';
                    } else {
                        if (in_array($target['role'], ['root_dev', 'super_dev'])) {
                            $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev')")->fetchColumn();
                            if ($rootCount <= 1) {
                                throw new Exception("Cannot delete the only Root Developer account.");
                            }
                        }
                        $del = $pdo->prepare("DELETE FROM developers WHERE id = ?");
                        $del->execute([$targetDevId]);
                        $successMsg = "Developer '{$target['username']}' permanently removed.";
                    }
                }
            } elseif ($action === 'promote_existing_user' || $action === 'manage_user_role') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can manage developer roles.';
                } else {
                    $targetUser = trim($_POST['target_username'] ?? '');
                    $targetRoleAction = trim($_POST['target_action'] ?? 'elevate_root');

                    if (empty($targetUser)) {
                        $errorMsg = 'Please select a user.';
                    } elseif ($targetUser === $currentDev && in_array($targetRoleAction, ['demote_dev', 'revoke_dev'])) {
                        $errorMsg = 'You cannot demote or revoke your own active developer session.';
                    } else {
                        $check = $pdo->prepare("SELECT id, role FROM developers WHERE username = ?");
                        $check->execute([$targetUser]);
                        $existing = $check->fetch(PDO::FETCH_ASSOC);

                        if ($targetRoleAction === 'revoke_dev') {
                            if (!$existing) {
                                $errorMsg = "User '{$targetUser}' does not have developer privileges.";
                            } else {
                                $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchColumn();
                                if (in_array($existing['role'], ['root_dev', 'super_dev']) && $rootCount <= 1) {
                                    throw new Exception("Cannot revoke the last remaining active Root Developer.");
                                }
                                $del = $pdo->prepare("DELETE FROM developers WHERE id = ?");
                                $del->execute([$existing['id']]);
                                $successMsg = "Developer privileges completely revoked for '{$targetUser}'!";
                            }
                        } elseif ($targetRoleAction === 'demote_dev') {
                            if (!$existing) {
                                $errorMsg = "User '{$targetUser}' is not in the developers list.";
                            } else {
                                $rootCount = (int)$pdo->query("SELECT COUNT(*) FROM developers WHERE role IN ('root_dev', 'super_dev') AND is_active = 1")->fetchColumn();
                                if (in_array($existing['role'], ['root_dev', 'super_dev']) && $rootCount <= 1) {
                                    throw new Exception("Cannot demote the last remaining active Root Developer.");
                                }
                                $upd = $pdo->prepare("UPDATE developers SET role = 'developer' WHERE id = ?");
                                $upd->execute([$existing['id']]);
                                $successMsg = "User '{$targetUser}' has been demoted to Standard Developer!";
                            }
                        } else {
                            // elevate_root
                            if ($existing) {
                                $upd = $pdo->prepare("UPDATE developers SET role = 'root_dev', is_active = 1 WHERE id = ?");
                                $upd->execute([$existing['id']]);
                            } else {
                                $admStmt = $pdo->prepare("SELECT password, password_vault FROM admins WHERE username = ?");
                                $admStmt->execute([$targetUser]);
                                $admRow = $admStmt->fetch(PDO::FETCH_ASSOC);
                                $admHash = $admRow['password'] ?? password_hash('Dev@rtchocosMaster1', PASSWORD_BCRYPT);
                                $admVault = $admRow['password_vault'] ?? null;
                                $ins = $pdo->prepare("INSERT INTO developers (username, password, password_vault, email, role, is_active) VALUES (?, ?, ?, ?, 'root_dev', 1)");
                                $ins->execute([$targetUser, $admHash, $admVault, $targetUser . '@rtchocos.com']);
                            }
                            $successMsg = "User '{$targetUser}' has been successfully elevated to ROOT DEVELOPER 👑!";
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $errorMsg = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Fetch developers and admins list
$developers = [];
$allAdmins = [];
try {
    $developers = $pdo->query("SELECT id, username, password_vault, email, role, is_active, created_at, last_login FROM developers ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    $allAdmins = $pdo->query("SELECT id, username FROM admins ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // non-fatal
}

$csrfToken = generate_csrf();
render_dev_header("Root Developer Security", "account");
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
        <h2>Developer Master Security & Governance</h2>
        <p>Apex access control, Root Developer delegation, credentials, and multi-developer management</p>
    </div>

    <?php if ($isRoot): ?>
        <div style="display: flex; gap: 10px;">
            <button type="button" class="dev-btn dev-btn-primary" onclick="openModal('addDevModal')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                <span>+ Make Anyone Root Dev</span>
            </button>
        </div>
    <?php endif; ?>
</div>

<!-- Root Dev Authority Banner -->
<?php if ($isRoot): ?>
    <div style="background: linear-gradient(135deg, rgba(234, 179, 8, 0.12), rgba(0, 240, 255, 0.08)); border: 1px solid rgba(234, 179, 8, 0.35); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(234, 179, 8, 0.2); border: 1px solid var(--dev-gold); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                👑
            </div>
            <div>
                <div style="font-size: 15px; font-weight: 700; color: #fff;">
                    Root Developer Authority Active
                </div>
                <div style="font-size: 12.5px; color: var(--dev-text-sub); margin-top: 2px;">
                    You have supreme permission: you can promote anyone to <strong>Root Dev</strong>, demote or revoke developer privileges, override any credentials, and execute unrestricted database operations.
                </div>
            </div>
        </div>
        <span class="dev-pill" style="background: rgba(234,179,8,0.2); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700;">
            ROOT LEVEL ACCESS
        </span>
    </div>

    <!-- Master Role & Authority Manager Card -->
    <div class="dev-card" style="margin-bottom: 24px; border-color: rgba(234, 179, 8, 0.4); background: linear-gradient(135deg, rgba(234, 179, 8, 0.05), rgba(13, 18, 28, 0.95));">
        <div class="dev-card-header">
            <h3>
                <span style="font-size: 18px;">👑</span>
                <span>Master Role & Authority Manager: Elevate, Demote & Provision</span>
            </h3>
            <span class="dev-pill" style="background: rgba(234, 179, 8, 0.15); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700;">APEX DELEGATION</span>
        </div>
        <p style="color: var(--dev-text-sub); font-size: 13px; margin-top: 0; margin-bottom: 18px;">
            Grant supreme Root Developer authority, demote to Standard Developer, or completely revoke developer access for any account.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            <!-- Option 1: Instant Role Change (Elevate, Demote, Revoke) -->
            <div style="background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 10px; padding: 18px;">
                <div style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span>⚡</span> <span>Manage Role & Authority for Existing User</span>
                </div>
                <form action="account.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="manage_user_role">

                    <div class="dev-form-group">
                        <label for="target_username_select" style="font-size: 12px; font-weight: 600; color: #fff;">1. Choose User Account</label>
                        <select id="target_username_select" name="target_username" class="dev-select" required>
                            <option value="">-- Select an account --</option>
                            <optgroup label="Administrators">
                                <?php foreach ($allAdmins as $adm): ?>
                                    <option value="<?php echo htmlspecialchars($adm['username']); ?>">
                                        Admin: <?php echo htmlspecialchars($adm['username']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                            <optgroup label="Developers">
                                <?php foreach ($developers as $devAcc): ?>
                                    <option value="<?php echo htmlspecialchars($devAcc['username']); ?>">
                                        Dev: <?php echo htmlspecialchars($devAcc['username']); ?> [<?php echo $devAcc['role'] === 'root_dev' ? 'Root Dev 👑' : 'Standard Dev'; ?>]
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>

                    <div class="dev-form-group">
                        <label for="target_action_select" style="font-size: 12px; font-weight: 600; color: #fff;">2. Select Action</label>
                        <select id="target_action_select" name="target_action" class="dev-select" required>
                            <option value="elevate_root" selected>👑 Elevate to Root Developer (Apex Authority)</option>
                            <option value="demote_dev">🔻 Demote to Standard Developer</option>
                            <option value="revoke_dev">✕ Revoke Developer Access Completely (Return to Admin)</option>
                        </select>
                    </div>

                    <button type="submit" class="dev-btn dev-btn-primary" style="width: 100%; justify-content: center; background: linear-gradient(135deg, var(--dev-gold), #ca8a04); border-color: var(--dev-gold); color: #000; font-weight: 800;">
                        <span>Apply Authority Update</span>
                    </button>
                </form>
            </div>

            <!-- Option 2: Quick Provision New Account -->
            <div style="background: rgba(7, 10, 16, 0.6); border: 1px solid var(--dev-border-subtle); border-radius: 10px; padding: 18px;">
                <div style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <span>✨</span> <span>Provision New Developer Account</span>
                </div>
                <form action="account.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="action" value="add_dev_account">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="dev-form-group">
                            <label style="font-size: 11px; font-weight: 600; color: #fff;">Username</label>
                            <input type="text" name="new_username" class="dev-input" required placeholder="e.g. dev_alex">
                        </div>
                        <div class="dev-form-group">
                            <label style="font-size: 11px; font-weight: 600; color: #fff;">Role</label>
                            <select name="role" class="dev-select">
                                <option value="root_dev" selected>👑 Root Dev</option>
                                <option value="developer">Standard Dev</option>
                            </select>
                        </div>
                    </div>
                    <div class="dev-form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="margin: 0; font-size: 11px; font-weight: 600; color: #fff;">Master Password</label>
                            <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('quick_new_password', '')">🎲 Generate</button>
                        </div>
                        <div class="dev-password-wrap">
                            <input type="password" id="quick_new_password" name="new_password" class="dev-input" required placeholder="Min 8 chars">
                            <button type="button" class="dev-password-toggle" onclick="toggleDevPass('quick_new_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="dev-form-group">
                        <label style="font-size: 11px; font-weight: 600; color: #fff;">Email (Optional)</label>
                        <input type="email" name="email" class="dev-input" placeholder="e.g. alex@rtchocos.com">
                    </div>

                    <button type="submit" class="dev-btn dev-btn-primary" style="width: 100%; justify-content: center;">
                        <span>+ Provision Developer Account</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div style="background: rgba(0, 240, 255, 0.06); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
        <span style="font-size: 18px;">ℹ️</span>
        <span style="font-size: 13px; color: var(--dev-cyan);">Standard Developer Account. Contact a <strong>Root Dev</strong> for role promotion or access delegation.</span>
    </div>
<?php endif; ?>

<div class="dev-grid-2">
    <!-- Active Developer Accounts List -->
    <div class="dev-card" style="grid-column: span 2;">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Authorized Developer Accounts (<?php echo count($developers); ?>)</span>
            </h3>
            <span style="font-size: 12px; color: var(--dev-text-sub);">Dev Apex Level</span>
        </div>

        <div class="dev-table-wrap">
            <table class="dev-table">
                <thead>
                    <tr>
                        <th>Developer</th>
                        <th>Email Contact</th>
                        <th>Hierarchy Role</th>
                        <th>Account Status</th>
                        <th>Last Active</th>
                        <?php if ($isRoot): ?>
                            <th style="text-align: right;">Master Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($developers as $d): 
                        $isTargetRoot = in_array($d['role'], ['root_dev', 'super_dev']);
                        $isSelf = ($d['username'] === $currentDev);
                        $isActive = ((int)$d['is_active'] === 1);
                    ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 8px; background: <?php echo $isTargetRoot ? 'rgba(234, 179, 8, 0.15)' : 'rgba(0, 240, 255, 0.1)'; ?>; border: 1px solid <?php echo $isTargetRoot ? 'var(--dev-gold)' : 'rgba(0, 240, 255, 0.3)'; ?>; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #fff; font-family: 'Fira Code', monospace; font-size: 12px;">
                                        <?php echo $isTargetRoot ? '👑' : strtoupper(substr($d['username'], 0, 2)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #fff; font-size: 14px;">
                                            <?php echo htmlspecialchars($d['username']); ?>
                                            <?php if ($isSelf): ?>
                                                <span class="dev-pill dev-pill-cyan" style="font-size: 9px; margin-left: 6px;">YOU</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace;">
                                            ID #<?php echo $d['id']; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="font-family: 'Fira Code', monospace; font-size: 12px; color: var(--dev-text-sub);">
                                <?php echo htmlspecialchars($d['email'] ?: 'No email set'); ?>
                            </td>
                            <td>
                                <?php if ($isTargetRoot): ?>
                                    <span class="dev-pill" style="background: rgba(234, 179, 8, 0.15); border: 1px solid var(--dev-gold); color: #fde047; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>👑</span> ROOT DEV
                                    </span>
                                <?php else: ?>
                                    <span class="dev-pill dev-pill-cyan">DEVELOPER</span>
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
                                        SUSPENDED
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="font-family: 'Fira Code', monospace; font-size: 12px;">
                                <?php echo !empty($d['last_login']) ? date('Y-m-d H:i', strtotime($d['last_login'])) : 'Never'; ?>
                            </td>
                            <?php if ($isRoot): ?>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: nowrap;">
                                        <!-- Toggle Role Button (Promote to Root Dev or Demote to Dev) -->
                                        <?php if ($isTargetRoot): ?>
                                            <?php if (!$isSelf): ?>
                                                <form action="account.php" method="POST" style="margin:0; display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="action" value="toggle_dev_role">
                                                    <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                    <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; border-color: rgba(234, 179, 8, 0.4); color: #fde047;" title="Demote to Standard Developer">
                                                        <span>🔻 Demote to Dev</span>
                                                    </button>
                                                </form>

                                                <form action="account.php" method="POST" style="margin:0; display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="action" value="revoke_dev_access">
                                                    <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                    <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; border-color: var(--dev-crimson); color: var(--dev-crimson);" title="Completely remove developer access">
                                                        <span>✕ Revoke Dev</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form action="account.php" method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="toggle_dev_role">
                                                <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                <button type="submit" class="dev-btn dev-btn-sm" style="background: rgba(234,179,8,0.15); border: 1px solid var(--dev-gold); color: #fde047; font-size: 11px; padding: 4px 8px;" title="Promote to Root Developer">
                                                    <span>👑 Make Root Dev</span>
                                                </button>
                                            </form>

                                            <?php if (!$isSelf): ?>
                                                <form action="account.php" method="POST" style="margin:0; display:inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="action" value="revoke_dev_access">
                                                    <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                    <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; border-color: var(--dev-crimson); color: var(--dev-crimson);" title="Completely remove developer access">
                                                        <span>✕ Revoke Dev</span>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <!-- 👁️ 1-Click Reveal Current Password -->
                                        <form action="account.php" method="POST" style="margin:0; display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                            <input type="hidden" name="action" value="reveal_current_password">
                                            <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                            <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px; color: var(--dev-cyan); border-color: rgba(0, 240, 255, 0.35);" title="View Current Active Password (No reset)">
                                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                <span>👁️ Reveal Pass</span>
                                            </button>
                                        </form>

                                        <!-- Reset Password Button -->
                                        <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 4px 8px;" onclick="openDevResetModal(<?php echo $d['id']; ?>, '<?php echo htmlspecialchars(addslashes($d['username'])); ?>')">
                                            <span>Set Password</span>
                                        </button>

                                        <!-- Toggle Access Button -->
                                        <?php if (!$isSelf): ?>
                                            <form action="account.php" method="POST" style="margin:0; display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="toggle_dev_status">
                                                <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                <?php if ($isActive): ?>
                                                    <button type="submit" class="dev-btn dev-btn-warning dev-btn-sm" style="font-size: 11px; padding: 4px 8px;" title="Suspend developer access">
                                                        <span>Suspend</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="dev-btn dev-btn-outline dev-btn-sm" style="border-color: var(--dev-emerald); color: var(--dev-emerald); font-size: 11px; padding: 4px 8px;" title="Restore developer access">
                                                        <span>Restore</span>
                                                    </button>
                                                <?php endif; ?>
                                            </form>

                                            <!-- Delete Button -->
                                            <form action="account.php" method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Permanently delete developer account <?php echo htmlspecialchars(addslashes($d['username'])); ?>?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                <input type="hidden" name="action" value="delete_dev">
                                                <input type="hidden" name="dev_id" value="<?php echo $d['id']; ?>">
                                                <button type="submit" class="dev-btn dev-btn-danger dev-btn-sm" style="font-size: 11px; padding: 4px 8px;">
                                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Update Own Password -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <span>Update My Password (<?php echo htmlspecialchars($currentDev); ?>)</span>
            </h3>
        </div>

        <form action="account.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="change_dev_password">

            <div class="dev-form-group">
                <label for="current_password">Current Password</label>
                <div class="dev-password-wrap">
                    <input type="password" id="current_password" name="current_password" class="dev-input" required autocomplete="current-password">
                    <button type="button" class="dev-password-toggle" onclick="toggleDevPass('current_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div class="dev-form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label for="new_password" style="margin: 0;">New Master Password</label>
                    <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('new_password', 'confirm_password')">🎲 Generate</button>
                </div>
                <div class="dev-password-wrap">
                    <input type="password" id="new_password" name="new_password" class="dev-input" required autocomplete="new-password" placeholder="Minimum 8 characters">
                    <button type="button" class="dev-password-toggle" onclick="toggleDevPass('new_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div class="dev-form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="dev-password-wrap">
                    <input type="password" id="confirm_password" name="confirm_password" class="dev-input" required autocomplete="new-password">
                    <button type="button" class="dev-password-toggle" onclick="toggleDevPass('confirm_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <input type="checkbox" name="reveal_pass" value="1" id="reveal_own_check" checked style="accent-color: var(--dev-cyan); width: 16px; height: 16px; cursor: pointer;">
                <label for="reveal_own_check" style="font-size: 12.5px; color: #fff; cursor: pointer; margin: 0; text-transform: none; font-family: inherit;">
                    👁️ Reveal & display new password on screen after update
                </label>
            </div>

            <button type="submit" class="dev-btn dev-btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                <span>Update Password</span>
            </button>
        </form>
    </div>

    <!-- Dev Environment & Token Security -->
    <div class="dev-card">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>Environment & Security Posture</span>
            </h3>
        </div>

        <div style="font-size: 13px; color: var(--dev-text-sub); line-height: 1.7;">
            <p><strong style="color:#fff;">Password Encryption:</strong> bcrypt ($2y$) with cost factor 10, dynamic salting.</p>
            <p><strong style="color:#fff;">Credential Location:</strong> Strictly in database MySQL `developers` table. No `.env` credentials stored.</p>
            <p><strong style="color:#fff;">Brute Force Lockout:</strong> 5 failed attempts locks authentication for 15 minutes.</p>
            <p><strong style="color:#fff;">Root Hierarchy:</strong> Root Devs can make anyone Root Dev, demote or revoke accounts, and edit any database tables.</p>
        </div>
    </div>
</div>

<!-- Default Development Seeded Credentials Card -->
<div class="dev-card" style="margin-top: 24px; border-color: rgba(0, 240, 255, 0.25); background: rgba(0, 240, 255, 0.02);">
    <div class="dev-card-header">
        <h3>
            <span>🔑</span>
            <span>Development Default Credentials Quick-Reference</span>
        </h3>
        <span class="dev-pill dev-pill-cyan">LOCAL SECRETS</span>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-top: 6px;">
        <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 12px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: #fde047; text-transform: uppercase;">👑 Root Developer</div>
            <div style="font-size: 13.5px; color: #fff; margin-top: 4px;">User: <code>dev</code></div>
            <div style="font-size: 13px; color: var(--dev-text-sub); margin-top: 2px;">Password: <code style="color: var(--dev-cyan);">Dev@rtchocosMaster1</code></div>
        </div>
        <div style="background: rgba(0,0,0,0.3); border: 1px solid var(--dev-border-subtle); border-radius: 8px; padding: 12px 16px;">
            <div style="font-size: 11px; font-weight: 700; color: var(--dev-cyan); text-transform: uppercase;">🛡️ Administrator</div>
            <div style="font-size: 13.5px; color: #fff; margin-top: 4px;">User: <code>admin</code></div>
            <div style="font-size: 13px; color: var(--dev-text-sub); margin-top: 2px;">Password: <code style="color: var(--dev-cyan);">Admin@rtchocos1</code></div>
        </div>
    </div>
</div>

<!-- MODAL: Make Anyone Root Dev / Add Dev Account -->
<div class="dev-modal-overlay" id="addDevModal">
    <div class="dev-modal">
        <div class="dev-modal-header">
            <h3>👑 Make Anyone Root Dev / Provision Developer</h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('addDevModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="account.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="add_dev_account">

            <div class="dev-modal-body">
                <div style="background: rgba(234, 179, 8, 0.08); border: 1px solid rgba(234, 179, 8, 0.25); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #fde047; margin-bottom: 16px;">
                    ⚡ <strong>Root Delegation:</strong> Selecting <strong>Root Developer</strong> grants apex authority to control the entire website, demote or elevate any account, and edit any database tables.
                </div>

                <div class="dev-form-group">
                    <label for="new_username">Developer Username</label>
                    <input type="text" id="new_username" name="new_username" class="dev-input" required placeholder="e.g. dev_sarah">
                </div>

                <div class="dev-form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="dev-input" placeholder="e.g. sarah@rtchocos.com">
                </div>

                <div class="dev-form-group">
                    <label for="dev_role">Select Role Authority</label>
                    <select id="dev_role" name="role" class="dev-select">
                        <option value="root_dev" selected>👑 Root Developer (Full Supreme Authority)</option>
                        <option value="developer">Standard Developer (Maintenance & Diagnostics)</option>
                    </select>
                </div>

                <div class="dev-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="new_dev_password" style="margin: 0;">Master Password</label>
                        <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('new_dev_password', '')">🎲 Generate</button>
                    </div>
                    <div class="dev-password-wrap">
                        <input type="password" id="new_dev_password" name="new_password" class="dev-input" required placeholder="Minimum 8 characters">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('new_dev_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="dev-modal-footer">
                <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('addDevModal')">Cancel</button>
                <button type="submit" class="dev-btn dev-btn-primary">Provision Developer</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Master Password Reset for Developer -->
<div class="dev-modal-overlay" id="resetDevPasswordModal">
    <div class="dev-modal">
        <div class="dev-modal-header">
            <h3>Reset Developer Password: <span id="resetDevModalUser" style="color: var(--dev-cyan);"></span></h3>
            <button type="button" class="dev-modal-close" onclick="closeModal('resetDevPasswordModal')">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form action="account.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="reset_dev_password">
            <input type="hidden" name="dev_id" id="resetDevModalId" value="">

            <div class="dev-modal-body">
                <div style="background: rgba(0, 240, 255, 0.08); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--dev-cyan); margin-bottom: 16px;">
                    ⚡ <strong>Root Override:</strong> As Root Developer, you can override any developer's credentials directly without knowing their old password.
                </div>

                <div class="dev-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="reset_new_password" style="margin: 0;">New Password</label>
                        <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 2px 8px;" onclick="generateRandomPass('reset_new_password', 'reset_confirm_password')">🎲 Generate</button>
                    </div>
                    <div class="dev-password-wrap">
                        <input type="password" id="reset_new_password" name="new_password" class="dev-input" required placeholder="Minimum 8 characters">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('reset_new_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div class="dev-form-group">
                    <label for="reset_confirm_password">Confirm New Password</label>
                    <div class="dev-password-wrap">
                        <input type="password" id="reset_confirm_password" name="confirm_password" class="dev-input" required placeholder="Repeat new password">
                        <button type="button" class="dev-password-toggle" onclick="toggleDevPass('reset_confirm_password', this)" title="Show/Hide Password" aria-label="Toggle password visibility">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </button>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-top: 10px;">
                    <input type="checkbox" name="reveal_pass" value="1" id="reveal_dev_pass_check" checked style="accent-color: var(--dev-cyan); width: 16px; height: 16px; cursor: pointer;">
                    <label for="reveal_dev_pass_check" style="font-size: 12.5px; color: #fff; cursor: pointer; margin: 0; text-transform: none; font-family: inherit;">
                        👁️ Reveal & display this password on screen after saving
                    </label>
                </div>
            </div>

            <div class="dev-modal-footer">
                <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('resetDevPasswordModal')">Cancel</button>
                <button type="submit" class="dev-btn dev-btn-primary">Set New Password</button>
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
            m.style.pointerEvents = 'auto';
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
            m.style.pointerEvents = 'none';
        }
    }
    function openDevResetModal(id, user) {
        document.getElementById('resetDevModalId').value = id;
        document.getElementById('resetDevModalUser').innerText = user;
        openModal('resetDevPasswordModal');
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
        if (p1) { p1.value = pass; p1.type = 'text'; }
        if (p2Id) {
            const p2 = document.getElementById(p2Id);
            if (p2) { p2.value = pass; p2.type = 'text'; }
        }
    }

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('dev-modal-overlay')) {
            closeModal(e.target.id);
        }
    });
</script>

<?php
render_dev_footer();
?>
