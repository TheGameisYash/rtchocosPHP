<?php
// dev/database.php - Master Database Console, Table Editor & Schema Inspector
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$isRoot = is_root_dev();
$successMsg = '';
$errorMsg = '';
$queryResults = null;
$queryHeaders = [];
$customQuery = '';

// Handle Database Operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $errorMsg = 'Security validation failed.';
    } else {
        try {
            if ($action === 'optimize_all') {
                $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                foreach ($tables as $t) {
                    $pdo->exec("OPTIMIZE TABLE `{$t}`");
                }
                $successMsg = 'Successfully optimized all ' . count($tables) . ' database tables!';
            } elseif ($action === 'run_query') {
                $customQuery = trim($_POST['sql_query'] ?? '');
                if (empty($customQuery)) {
                    $errorMsg = 'Please enter a SQL query.';
                } else {
                    $upper = strtoupper(trim($customQuery));
                    $isReadOnly = (bool)preg_match('/^(SELECT|SHOW|DESCRIBE|DESC|EXPLAIN)\s+/i', $upper);

                    if ($isReadOnly) {
                        $stmt = $pdo->query($customQuery);
                        $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!empty($queryResults)) {
                            $queryHeaders = array_keys($queryResults[0]);
                        }
                        $successMsg = 'Query executed successfully (' . count($queryResults) . ' rows returned).';
                    } else {
                        if (!$isRoot) {
                            $errorMsg = 'Security Policy: Write and DDL queries (INSERT, UPDATE, DELETE, ALTER, DROP) require Root Developer authorization.';
                        } else {
                            $affected = $pdo->exec($customQuery);
                            $msg = 'Root SQL command executed successfully!';
                            if ($affected !== false) {
                                $msg .= ' (' . $affected . ' rows affected)';
                            }
                            $successMsg = $msg;
                        }
                    }
                }
            } elseif ($action === 'insert_row') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can insert records into tables.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    $colsInput = $_POST['cols'] ?? [];

                    // Verify table exists
                    $checkTable = $pdo->prepare("SHOW TABLES LIKE ?");
                    $checkTable->execute([$tableName]);
                    if (!$checkTable->fetchColumn()) {
                        throw new Exception("Invalid table name.");
                    }

                    $schema = $pdo->query("DESCRIBE `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
                    $insertCols = [];
                    $placeholders = [];
                    $params = [];

                    foreach ($schema as $col) {
                        $f = $col['Field'];
                        $isAi = (strpos($col['Extra'], 'auto_increment') !== false);
                        
                        if (isset($colsInput[$f])) {
                            $val = $colsInput[$f];
                            // If auto-increment and left empty, skip to let MySQL assign
                            if ($isAi && $val === '') {
                                continue;
                            }
                            $insertCols[] = "`{$f}`";
                            $placeholders[] = "?";
                            // Handle null
                            if ($val === '' && $col['Null'] === 'YES') {
                                $params[] = null;
                            } else {
                                $params[] = $val;
                            }
                        }
                    }

                    if (empty($insertCols)) {
                        throw new Exception("No column values provided.");
                    }

                    $sql = "INSERT INTO `{$tableName}` (" . implode(', ', $insertCols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $successMsg = "Record successfully inserted into '{$tableName}'!";
                }
            } elseif ($action === 'update_row') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can modify table rows.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    $pkCol = trim($_POST['pk_col'] ?? '');
                    $pkVal = $_POST['pk_val'] ?? '';
                    $colsInput = $_POST['cols'] ?? [];

                    $checkTable = $pdo->prepare("SHOW TABLES LIKE ?");
                    $checkTable->execute([$tableName]);
                    if (!$checkTable->fetchColumn()) {
                        throw new Exception("Invalid table name.");
                    }

                    $schema = $pdo->query("DESCRIBE `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
                    $setClauses = [];
                    $params = [];

                    foreach ($schema as $col) {
                        $f = $col['Field'];
                        if (isset($colsInput[$f])) {
                            $val = $colsInput[$f];
                            $setClauses[] = "`{$f}` = ?";
                            if ($val === '' && $col['Null'] === 'YES') {
                                $params[] = null;
                            } else {
                                $params[] = $val;
                            }
                        }
                    }

                    if (empty($setClauses)) {
                        throw new Exception("No columns to update.");
                    }

                    $params[] = $pkVal;
                    $sql = "UPDATE `{$tableName}` SET " . implode(', ', $setClauses) . " WHERE `{$pkCol}` = ? LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $successMsg = "Record with {$pkCol} = '{$pkVal}' updated in '{$tableName}'!";
                }
            } elseif ($action === 'delete_row') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can delete records.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    $pkCol = trim($_POST['pk_col'] ?? '');
                    $pkVal = $_POST['pk_val'] ?? '';

                    $checkTable = $pdo->prepare("SHOW TABLES LIKE ?");
                    $checkTable->execute([$tableName]);
                    if (!$checkTable->fetchColumn()) {
                        throw new Exception("Invalid table name.");
                    }

                    $sql = "DELETE FROM `{$tableName}` WHERE `{$pkCol}` = ? LIMIT 1";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$pkVal]);
                    $successMsg = "Record with {$pkCol} = '{$pkVal}' deleted from '{$tableName}'!";
                }
            } elseif ($action === 'truncate_table') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can truncate tables.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    if (in_array($tableName, ['site_settings', 'developers', 'admins'])) {
                        throw new Exception("Truncating core security table '{$tableName}' is prohibited for system safety.");
                    }
                    $pdo->exec("TRUNCATE TABLE `{$tableName}`");
                    $successMsg = "Table '{$tableName}' has been truncated successfully!";
                }
            } elseif ($action === 'drop_table') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can drop tables.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    if (in_array($tableName, ['site_settings', 'developers', 'admins', 'products', 'orders'])) {
                        throw new Exception("Dropping critical system table '{$tableName}' is prohibited.");
                    }
                    $pdo->exec("DROP TABLE `{$tableName}`");
                    header('Location: database.php?dropped=' . urlencode($tableName));
                    exit;
                }
            } elseif ($action === 'add_column') {
                if (!$isRoot) {
                    $errorMsg = 'Only Root Developers can alter table schemas.';
                } else {
                    $tableName = trim($_POST['table_name'] ?? '');
                    $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['col_name'] ?? '');
                    $colType = trim($_POST['col_type'] ?? 'VARCHAR(255)');
                    $isNullable = !empty($_POST['is_nullable']) ? 'NULL' : 'NOT NULL';
                    $defaultVal = $_POST['default_val'] ?? '';

                    if (empty($colName)) {
                        throw new Exception("Column name cannot be empty.");
                    }

                    $defaultSql = '';
                    if ($defaultVal !== '') {
                        $defaultSql = "DEFAULT " . $pdo->quote($defaultVal);
                    }

                    $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$colName}` {$colType} {$isNullable} {$defaultSql}";
                    $pdo->exec($sql);
                    $successMsg = "Column '{$colName}' added to table '{$tableName}' successfully!";
                }
            }
        } catch (Exception $e) {
            $errorMsg = 'Database execution error: ' . $e->getMessage();
        }
    }
}

if (!empty($_GET['dropped'])) {
    $successMsg = "Table '" . htmlspecialchars($_GET['dropped']) . "' was dropped successfully!";
}

// Fetch all tables with metadata
$tableStatus = [];
$totalSize = 0;
$totalRows = 0;
try {
    $tableStatus = $pdo->query("SHOW TABLE STATUS")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tableStatus as $ts) {
        $totalSize += ($ts['Data_length'] + $ts['Index_length']);
        $totalRows += (int)($ts['Rows'] ?? 0);
    }
} catch (Exception $e) {
    $errorMsg = 'Could not load tables: ' . $e->getMessage();
}

// Check if a specific table inspection is requested via GET
$inspectTable = $_GET['inspect'] ?? '';
$inspectRows = [];
$inspectColumns = [];
$pkCol = null;

if (!empty($inspectTable)) {
    $validTable = false;
    foreach ($tableStatus as $ts) {
        if ($ts['Name'] === $inspectTable) {
            $validTable = true;
            break;
        }
    }

    if ($validTable) {
        try {
            $inspectColumns = $pdo->query("DESCRIBE `{$inspectTable}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($inspectColumns as $c) {
                if ($c['Key'] === 'PRI') {
                    $pkCol = $c['Field'];
                    break;
                }
            }
            if (!$pkCol && !empty($inspectColumns)) {
                $pkCol = $inspectColumns[0]['Field'];
            }
            $inspectRows = $pdo->query("SELECT * FROM `{$inspectTable}` ORDER BY 1 DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $errorMsg = 'Failed inspecting table: ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf();
render_dev_header("Database Master Console", "database");
?>

<?php if (!empty($successMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($successMsg); ?>, 'success'));</script>
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showDevToast(<?php echo json_encode($errorMsg); ?>, 'danger'));</script>
<?php endif; ?>

<div class="dev-page-header">
    <div class="dev-page-title">
        <h2>Database Master Console & Table Editor</h2>
        <p>Live exploration, full table row editing, unrestricted SQL execution, and schema management</p>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if (!empty($inspectTable) && $isRoot): ?>
            <button type="button" class="dev-btn dev-btn-primary" onclick="openModal('insertRowModal')">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                <span>+ Insert New Row</span>
            </button>
            <button type="button" class="dev-btn dev-btn-outline" onclick="openModal('addColumnModal')">
                <span>+ Add Column</span>
            </button>
        <?php endif; ?>

        <form action="database.php" method="POST" style="margin:0;">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="action" value="optimize_all">
            <button type="submit" class="dev-btn dev-btn-outline">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Optimize All Tables</span>
            </button>
        </form>
    </div>
</div>

<!-- Database Metrics -->
<div class="dev-grid-4">
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Total Tables</div>
            <div class="dev-stat-value"><?php echo count($tableStatus); ?> Tables</div>
            <div class="dev-stat-sub">InnoDB / UTF8MB4</div>
        </div>
        <div class="dev-stat-icon"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Total Records</div>
            <div class="dev-stat-value" style="color: var(--dev-cyan);"><?php echo number_format($totalRows); ?></div>
            <div class="dev-stat-sub">Across all catalog & orders</div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-cyan);"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Storage Allocated</div>
            <div class="dev-stat-value" style="color: var(--dev-emerald);"><?php echo round($totalSize / (1024 * 1024), 2); ?> MB</div>
            <div class="dev-stat-sub">Data + Index Footprint</div>
        </div>
        <div class="dev-stat-icon" style="color: var(--dev-emerald);"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
    </div>
    <div class="dev-card dev-stat-card">
        <div class="dev-stat-info">
            <div class="dev-stat-label">Root Authority</div>
            <div class="dev-stat-value" style="color: <?php echo $isRoot ? '#fde047' : 'var(--dev-cyan)'; ?>; font-size: 16px;">
                <?php echo $isRoot ? '👑 ROOT UNRESTRICTED' : 'READ-ONLY CONSOLE'; ?>
            </div>
            <div class="dev-stat-sub"><?php echo $isRoot ? 'Edit Tables, Rows & Schemas' : 'Safe Diagnostics'; ?></div>
        </div>
        <div class="dev-stat-icon" style="color: <?php echo $isRoot ? '#fde047' : 'var(--dev-cyan)'; ?>;"><svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>
    </div>
</div>

<!-- SQL Console -->
<div class="dev-card" style="margin-bottom: 24px;">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>SQL Master Query Console</span>
        </h3>
        <?php if ($isRoot): ?>
            <span class="dev-pill" style="background: rgba(234, 179, 8, 0.15); border: 1px solid var(--dev-gold); color: #fde047; font-size: 10.5px; font-weight: 700;">
                👑 ROOT DEV: SELECT &bull; INSERT &bull; UPDATE &bull; DELETE &bull; ALTER &bull; DROP
            </span>
        <?php else: ?>
            <span style="font-size: 11px; font-family: 'Fira Code', monospace; color: var(--dev-cyan);">READ-ONLY &bull; SELECT / SHOW / DESCRIBE</span>
        <?php endif; ?>
    </div>

    <form action="database.php<?php echo !empty($inspectTable) ? '?inspect=' . urlencode($inspectTable) : ''; ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
        <input type="hidden" name="action" value="run_query">

        <div class="dev-form-group" style="margin-bottom: 14px;">
            <textarea name="sql_query" class="dev-textarea" style="font-family: 'Fira Code', monospace; font-size: 13px;" rows="3" placeholder="<?php echo $isRoot ? 'UPDATE products SET price = 450 WHERE id = 1;' : 'SELECT id, username, created_at FROM admins LIMIT 10;'; ?>"><?php echo htmlspecialchars($customQuery); ?></textarea>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" onclick="setQuery('SELECT id, name, price, stock, is_active FROM products ORDER BY id DESC LIMIT 10;')">Products</button>
                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" onclick="setQuery('SELECT id, order_number, total_amount, order_status, created_at FROM orders ORDER BY id DESC LIMIT 10;')">Orders</button>
                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" onclick="setQuery('SELECT setting_key, setting_value FROM site_settings;')">Settings</button>
                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" onclick="setQuery('SELECT id, username, email, role, is_active FROM developers;')">Developers</button>
                <?php if ($isRoot): ?>
                    <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="border-color: rgba(234,179,8,0.4); color: #fde047;" onclick="setQuery('UPDATE site_settings SET setting_value = \'0\' WHERE setting_key = \'maintenance_mode\';')">SQL Update</button>
                <?php endif; ?>
            </div>

            <button type="submit" class="dev-btn dev-btn-primary dev-btn-sm">Execute SQL</button>
        </div>
    </form>

    <?php if ($queryResults !== null): ?>
        <div style="margin-top: 20px; border-top: 1px solid var(--dev-border-subtle); padding-top: 16px;">
            <div style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 10px;">Query Result (<?php echo count($queryResults); ?> records)</div>
            <?php if (empty($queryResults)): ?>
                <div style="color: var(--dev-text-sub); font-size: 13px;">No records matched query.</div>
            <?php else: ?>
                <div class="dev-table-wrap">
                    <table class="dev-table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <?php foreach ($queryHeaders as $qh): ?>
                                    <th><?php echo htmlspecialchars($qh); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queryResults as $row): ?>
                                <tr>
                                    <?php foreach ($row as $val): ?>
                                        <td style="font-family: 'Fira Code', monospace; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars((string)$val); ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- If a table inspection was requested, show inspector & row editor card -->
<?php if (!empty($inspectTable) && !empty($inspectColumns)): ?>
    <div class="dev-card" style="margin-bottom: 24px; border-color: var(--dev-cyan);">
        <div class="dev-card-header">
            <h3>
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Inspecting Table: <code style="color: var(--dev-cyan); font-size: 15px;"><?php echo htmlspecialchars($inspectTable); ?></code></span>
                <span class="dev-pill dev-pill-cyan" style="font-size: 10px; margin-left: 8px;">PK: <?php echo htmlspecialchars($pkCol ?: 'None'); ?></span>
            </h3>

            <div style="display: flex; gap: 8px; align-items: center;">
                <?php if ($isRoot && !in_array($inspectTable, ['site_settings', 'developers', 'admins'])): ?>
                    <form action="database.php?inspect=<?php echo urlencode($inspectTable); ?>" method="POST" style="margin:0; display:inline;" onsubmit="return confirm('WARNING: Are you sure you want to TRUNCATE all records in <?php echo htmlspecialchars($inspectTable); ?>?');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="action" value="truncate_table">
                        <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($inspectTable); ?>">
                        <button type="submit" class="dev-btn dev-btn-warning dev-btn-sm" style="font-size: 11px;">Truncate</button>
                    </form>
                <?php endif; ?>

                <a href="database.php" class="dev-btn dev-btn-outline dev-btn-sm">Close Inspector</a>
            </div>
        </div>

        <!-- Schema Columns View -->
        <div style="margin-bottom: 20px;">
            <div style="font-size: 12px; font-weight: 700; color: var(--dev-text-sub); text-transform: uppercase; margin-bottom: 8px;">
                Column Schema (<?php echo count($inspectColumns); ?> Columns)
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <?php foreach ($inspectColumns as $col): ?>
                    <div style="background: rgba(7, 10, 16, 0.7); border: 1px solid var(--dev-border-subtle); border-radius: 6px; padding: 6px 10px; font-size: 11px;">
                        <strong style="color: #fff; font-family: 'Fira Code', monospace;"><?php echo htmlspecialchars($col['Field']); ?></strong>
                        <span style="color: var(--dev-cyan); margin-left: 4px;"><?php echo htmlspecialchars($col['Type']); ?></span>
                        <?php if ($col['Key'] === 'PRI'): ?>
                            <span class="dev-pill dev-pill-cyan" style="font-size: 9px; padding: 1px 4px; margin-left: 4px;">PK</span>
                        <?php endif; ?>
                        <?php if (strpos($col['Extra'], 'auto_increment') !== false): ?>
                            <span class="dev-pill dev-pill-success" style="font-size: 9px; padding: 1px 4px; margin-left: 4px;">AI</span>
                        <?php endif; ?>
                        <?php if ($col['Null'] === 'NO'): ?>
                            <span style="color: var(--dev-crimson); margin-left: 2px;">*</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Rows Explorer with Live Edit and Delete -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <div style="font-size: 12px; font-weight: 700; color: var(--dev-text-sub); text-transform: uppercase;">
                    Table Records (Showing up to 100)
                </div>
                <?php if ($isRoot): ?>
                    <button type="button" class="dev-btn dev-btn-primary dev-btn-sm" onclick="openModal('insertRowModal')">
                        + Insert New Row
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($inspectRows)): ?>
                <div style="color: var(--dev-text-sub); font-size: 13px; padding: 20px; text-align: center; border: 1px dashed var(--dev-border-subtle); border-radius: 8px;">
                    Table is currently empty. <?php if ($isRoot): ?><a href="javascript:void(0)" onclick="openModal('insertRowModal')" style="color: var(--dev-cyan);">Insert the first record</a>.<?php endif; ?>
                </div>
            <?php else: ?>
                <div class="dev-table-wrap">
                    <table class="dev-table" style="font-size: 12px;">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($inspectRows[0]) as $ck): ?>
                                    <th><?php echo htmlspecialchars($ck); ?></th>
                                <?php endforeach; ?>
                                <?php if ($isRoot && $pkCol): ?>
                                    <th style="text-align: right; min-width: 140px;">Row Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inspectRows as $r): 
                                $rowJson = json_encode($r, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                                $rowPkVal = $r[$pkCol] ?? '';
                            ?>
                                <tr>
                                    <?php foreach ($r as $val): ?>
                                        <td style="font-family: 'Fira Code', monospace; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?php echo htmlspecialchars((string)$val); ?>
                                        </td>
                                    <?php endforeach; ?>

                                    <?php if ($isRoot && $pkCol): ?>
                                        <td style="text-align: right;">
                                            <div style="display: inline-flex; gap: 6px;">
                                                <button type="button" class="dev-btn dev-btn-outline dev-btn-sm" style="font-size: 11px; padding: 3px 8px;" onclick='openEditRow(<?php echo $rowJson; ?>)'>
                                                    Edit
                                                </button>

                                                <form action="database.php?inspect=<?php echo urlencode($inspectTable); ?>" method="POST" style="margin:0; display:inline;" onsubmit="return confirm('Delete row where <?php echo htmlspecialchars($pkCol); ?> = <?php echo htmlspecialchars($rowPkVal); ?>?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                                    <input type="hidden" name="action" value="delete_row">
                                                    <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($inspectTable); ?>">
                                                    <input type="hidden" name="pk_col" value="<?php echo htmlspecialchars($pkCol); ?>">
                                                    <input type="hidden" name="pk_val" value="<?php echo htmlspecialchars($rowPkVal); ?>">
                                                    <button type="submit" class="dev-btn dev-btn-danger dev-btn-sm" style="font-size: 11px; padding: 3px 8px;">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- All Tables Directory -->
<div class="dev-card">
    <div class="dev-card-header">
        <h3>
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>All Database Tables (<?php echo count($tableStatus); ?>)</span>
        </h3>
    </div>

    <div class="dev-table-wrap">
        <table class="dev-table">
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Engine</th>
                    <th>Row Count</th>
                    <th>Data Size</th>
                    <th>Index Size</th>
                    <th>Collation</th>
                    <th style="text-align: right;">Master Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tableStatus as $ts): 
                    $dataKb = round($ts['Data_length'] / 1024, 1);
                    $idxKb = round($ts['Index_length'] / 1024, 1);
                ?>
                    <tr>
                        <td style="font-weight: 700; color: #fff; font-family: 'Fira Code', monospace;">
                            <a href="database.php?inspect=<?php echo urlencode($ts['Name']); ?>" style="color: var(--dev-cyan); text-decoration: none;">
                                <?php echo htmlspecialchars($ts['Name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($ts['Engine'] ?? 'InnoDB'); ?></td>
                        <td style="font-family: 'Fira Code', monospace; font-weight: 600; color: #fff;">
                            <?php echo number_format((int)($ts['Rows'] ?? 0)); ?>
                        </td>
                        <td style="font-family: 'Fira Code', monospace;"><?php echo $dataKb; ?> KB</td>
                        <td style="font-family: 'Fira Code', monospace; color: var(--dev-text-sub);"><?php echo $idxKb; ?> KB</td>
                        <td style="font-size: 11px; color: var(--dev-text-sub); font-family: 'Fira Code', monospace;"><?php echo htmlspecialchars($ts['Collation'] ?? 'utf8mb4'); ?></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 6px;">
                                <a href="database.php?inspect=<?php echo urlencode($ts['Name']); ?>" class="dev-btn dev-btn-primary dev-btn-sm" style="padding: 3px 8px; font-size: 11px;">
                                    Inspect & Edit &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Insert New Row -->
<?php if (!empty($inspectTable) && !empty($inspectColumns)): ?>
    <div class="dev-modal-overlay" id="insertRowModal">
        <div class="dev-modal dev-modal-lg">
            <div class="dev-modal-header">
                <h3>Insert New Record: <code style="color: var(--dev-cyan);"><?php echo htmlspecialchars($inspectTable); ?></code></h3>
                <button type="button" class="dev-modal-close" onclick="closeModal('insertRowModal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="database.php?inspect=<?php echo urlencode($inspectTable); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="insert_row">
                <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($inspectTable); ?>">

                <div class="dev-modal-body">
                    <div style="background: rgba(0, 240, 255, 0.08); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--dev-cyan); margin-bottom: 16px;">
                        ⚡ <strong>Root Table Insert:</strong> Fill the column values below. Primary key fields with auto-increment can be left blank.
                    </div>

                    <?php foreach ($inspectColumns as $col): 
                        $f = $col['Field'];
                        $isAi = (strpos($col['Extra'], 'auto_increment') !== false);
                    ?>
                        <div class="dev-form-group">
                            <label for="ins_<?php echo $f; ?>">
                                <strong style="color: #fff; font-family: 'Fira Code', monospace;"><?php echo htmlspecialchars($f); ?></strong>
                                <span style="color: var(--dev-text-sub); font-size: 11px;">(<?php echo htmlspecialchars($col['Type']); ?><?php echo $isAi ? ', Auto-Increment' : ''; ?>)</span>
                            </label>
                            <?php if (strpos(strtolower($col['Type']), 'text') !== false): ?>
                                <textarea id="ins_<?php echo $f; ?>" name="cols[<?php echo $f; ?>]" class="dev-textarea" rows="2"></textarea>
                            <?php else: ?>
                                <input type="text" id="ins_<?php echo $f; ?>" name="cols[<?php echo $f; ?>]" class="dev-input" placeholder="<?php echo $isAi ? 'Auto generated' : htmlspecialchars($col['Default'] ?? ''); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="dev-modal-footer">
                    <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('insertRowModal')">Cancel</button>
                    <button type="submit" class="dev-btn dev-btn-primary">Insert Record</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Row -->
    <div class="dev-modal-overlay" id="editRowModal">
        <div class="dev-modal dev-modal-lg">
            <div class="dev-modal-header">
                <h3>Edit Record in <code style="color: var(--dev-cyan);"><?php echo htmlspecialchars($inspectTable); ?></code></h3>
                <button type="button" class="dev-modal-close" onclick="closeModal('editRowModal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="database.php?inspect=<?php echo urlencode($inspectTable); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="update_row">
                <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($inspectTable); ?>">
                <input type="hidden" name="pk_col" value="<?php echo htmlspecialchars($pkCol); ?>">
                <input type="hidden" name="pk_val" id="edit_pk_val" value="">

                <div class="dev-modal-body">
                    <div style="background: rgba(0, 240, 255, 0.08); border: 1px solid rgba(0, 240, 255, 0.2); border-radius: 8px; padding: 10px 14px; font-size: 12px; color: var(--dev-cyan); margin-bottom: 16px;">
                        ⚡ <strong>Root Table Editor:</strong> Modifying record where <code><?php echo htmlspecialchars($pkCol); ?> = <span id="edit_pk_display"></span></code>.
                    </div>

                    <?php foreach ($inspectColumns as $col): 
                        $f = $col['Field'];
                        $isPk = ($f === $pkCol);
                    ?>
                        <div class="dev-form-group">
                            <label for="edit_<?php echo $f; ?>">
                                <strong style="color: #fff; font-family: 'Fira Code', monospace;"><?php echo htmlspecialchars($f); ?></strong>
                                <span style="color: var(--dev-text-sub); font-size: 11px;">(<?php echo htmlspecialchars($col['Type']); ?><?php echo $isPk ? ', PRIMARY KEY' : ''; ?>)</span>
                            </label>
                            <?php if (strpos(strtolower($col['Type']), 'text') !== false): ?>
                                <textarea id="edit_<?php echo $f; ?>" name="cols[<?php echo $f; ?>]" class="dev-textarea" rows="3"></textarea>
                            <?php else: ?>
                                <input type="text" id="edit_<?php echo $f; ?>" name="cols[<?php echo $f; ?>]" class="dev-input">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="dev-modal-footer">
                    <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('editRowModal')">Cancel</button>
                    <button type="submit" class="dev-btn dev-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Add Column -->
    <div class="dev-modal-overlay" id="addColumnModal">
        <div class="dev-modal">
            <div class="dev-modal-header">
                <h3>Add Column to <code style="color: var(--dev-cyan);"><?php echo htmlspecialchars($inspectTable); ?></code></h3>
                <button type="button" class="dev-modal-close" onclick="closeModal('addColumnModal')">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="database.php?inspect=<?php echo urlencode($inspectTable); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="action" value="add_column">
                <input type="hidden" name="table_name" value="<?php echo htmlspecialchars($inspectTable); ?>">

                <div class="dev-modal-body">
                    <div class="dev-form-group">
                        <label for="new_col_name">Column Name</label>
                        <input type="text" id="new_col_name" name="col_name" class="dev-input" required placeholder="e.g. tracking_code">
                    </div>

                    <div class="dev-form-group">
                        <label for="new_col_type">Column Data Type</label>
                        <select id="new_col_type" name="col_type" class="dev-select">
                            <option value="VARCHAR(255)">VARCHAR(255) (Short Text)</option>
                            <option value="INT(11)">INT(11) (Integer Number)</option>
                            <option value="TEXT">TEXT (Long Text)</option>
                            <option value="TINYINT(1)">TINYINT(1) (Boolean / Flag)</option>
                            <option value="DECIMAL(10,2)">DECIMAL(10,2) (Currency / Price)</option>
                            <option value="TIMESTAMP">TIMESTAMP (Date & Time)</option>
                        </select>
                    </div>

                    <div class="dev-form-group">
                        <label for="new_default_val">Default Value (Optional)</label>
                        <input type="text" id="new_default_val" name="default_val" class="dev-input" placeholder="e.g. 0 or active">
                    </div>

                    <div class="dev-form-group">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; color: #fff;">
                            <input type="checkbox" name="is_nullable" value="1" checked style="width: 16px; height: 16px; accent-color: var(--dev-cyan);">
                            <span>Allow NULL values</span>
                        </label>
                    </div>
                </div>

                <div class="dev-modal-footer">
                    <button type="button" class="dev-btn dev-btn-outline" onclick="closeModal('addColumnModal')">Cancel</button>
                    <button type="submit" class="dev-btn dev-btn-primary">Alter Table & Add Column</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
    function setQuery(sql) {
        document.querySelector('textarea[name="sql_query"]').value = sql;
    }

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

    function openEditRow(rowData) {
        const pkCol = <?php echo json_encode($pkCol); ?>;
        const pkVal = rowData[pkCol];
        document.getElementById('edit_pk_val').value = pkVal;
        document.getElementById('edit_pk_display').textContent = pkVal;

        for (const [colName, colVal] of Object.entries(rowData)) {
            const input = document.getElementById('edit_' + colName);
            if (input) {
                input.value = (colVal !== null && colVal !== undefined) ? colVal : '';
            }
        }
        openModal('editRowModal');
    }

    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('dev-modal-overlay')) {
            e.target.classList.remove('open');
        }
    });
</script>

<?php
render_dev_footer();
?>
