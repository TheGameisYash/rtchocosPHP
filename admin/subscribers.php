<?php
// admin/subscribers.php
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

// Handle CSV Export before any HTML is sent
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=subscribers_export_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Email Address', 'Date Subscribed']);
    
    try {
        $stmt = $pdo->query("SELECT email, created_at FROM subscribers ORDER BY created_at DESC");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [$row['email'], $row['created_at']]);
        }
    } catch (Exception $e) {
        fputcsv($output, ['Error exporting data: ' . $e->getMessage()]);
    }
    
    fclose($output);
    exit;
}

// Handle Form submissions (Delete, Import CSV, Bulk actions)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } else {
        try {
            if ($action === 'delete') {
                $subId = (int)($_POST['subscriber_id'] ?? 0);
                if ($subId > 0) {
                    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id = ?");
                    $stmt->execute([$subId]);
                    $success = 'Subscriber removed successfully.';
                }
            } elseif ($action === 'bulk_delete') {
                $ids = $_POST['subscriber_ids'] ?? [];
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $stmt = $pdo->prepare("DELETE FROM subscribers WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $success = 'Selected subscribers deleted successfully.';
                }
            } elseif ($action === 'import_csv') {
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['csv_file']['tmp_name'];
                    $file = fopen($fileTmpPath, 'r');
                    if ($file) {
                        $header = fgetcsv($file); // skip header row
                        $imported = 0;
                        $skipped = 0;
                        while (($row = fgetcsv($file)) !== false) {
                            $emailCandidate = '';
                            foreach ($row as $col) {
                                $col = trim($col);
                                if (filter_var($col, FILTER_VALIDATE_EMAIL)) {
                                    $emailCandidate = $col;
                                    break;
                                }
                            }
                            if ($emailCandidate) {
                                $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscribers WHERE email = ?");
                                $stmt->execute([$emailCandidate]);
                                if ($stmt->fetchColumn() == 0) {
                                    $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
                                    $stmt->execute([$emailCandidate]);
                                    $imported++;
                                } else {
                                    $skipped++;
                                }
                            }
                        }
                        fclose($file);
                        $success = "CSV imported successfully. Imported: $imported, Skipped: $skipped.";
                    } else {
                        $error = 'Failed to open CSV file.';
                    }
                } else {
                    $error = 'Please select a valid CSV file to upload.';
                }
            }
        } catch (Exception $e) {
            $error = 'Operation failed: ' . $e->getMessage();
        }
    }
}

// Search & Pagination Setup
$search = trim($_GET['search'] ?? '');
$page = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "email LIKE ?";
    $params[] = "%$search%";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

try {
    // Count matches
    $countSql = "SELECT COUNT(*) FROM subscribers $whereSql";
    if (empty($params)) {
        $totalResults = (int)$pdo->query($countSql)->fetchColumn();
    } else {
        $stmt = $pdo->prepare($countSql);
        $stmt->execute($params);
        $totalResults = (int)$stmt->fetchColumn();
    }

    $totalPages = ceil($totalResults / $limit);
    if ($totalPages < 1) $totalPages = 1;
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $limit;
    }

    // Fetch entries
    $selectSql = "SELECT id, email, created_at FROM subscribers $whereSql ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($selectSql);
    $paramIndex = 1;
    foreach ($params as $paramValue) {
        $stmt->bindValue($paramIndex++, $paramValue);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $subscribers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch monthly stats for canvas growth chart
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count FROM subscribers GROUP BY month ORDER BY MIN(created_at) ASC LIMIT 6");
    $chartData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch BCC email list for "Email All"
    $allEmails = $pdo->query("SELECT email FROM subscribers")->fetchAll(PDO::FETCH_COLUMN);
    $bccList = implode(',', $allEmails);

} catch (Exception $e) {
    die("Error loading subscribers: " . $e->getMessage());
}

render_admin_header("Newsletter Subscribers", "subscribers");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Bulk Actions Slider Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar" style="background: var(--green-900);">
    <div class="bulk-info"><span id="bulkSelectedCount">0</span> subscribers selected</div>
    <div class="bulk-actions-btns">
        <form action="subscribers.php" method="POST" id="bulkForm" style="display: flex; gap: 12px;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" id="bulkActionField" value="">
            <div id="bulkIdsContainer"></div>
            <button type="button" class="btn btn-danger btn-sm" onclick="submitBulk('bulk_delete')">Delete Selected</button>
        </form>
    </div>
</div>

<div class="editor-layout" style="align-items: stretch; margin-bottom: 28px;">
    <!-- Subscriber Listing Table panel -->
    <div class="form-card" style="margin-bottom:0; flex-grow:2; display:flex; flex-direction:column; gap:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <form action="subscribers.php" method="GET" class="topbar-search" style="display:block; width: 240px;">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" name="search" placeholder="Search email..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; border-radius:8px; padding-left:36px; padding-top:8px; padding-bottom:8px;">
            </form>
            
            <div style="display:flex; gap:12px; align-items:center;">
                <a href="subscribers.php?export=csv" class="btn btn-outline btn-sm">Export CSV</a>
                <button type="button" class="btn btn-outline btn-sm" onclick="openImportModal()">Import CSV</button>
                <a href="mailto:?bcc=<?php echo urlencode($bccList); ?>&subject=RT%20Chocos%20Newsletter" class="btn btn-primary btn-sm">Email All</a>
            </div>
        </div>

        <div class="table-container" style="border:1px solid var(--border-color); box-shadow:none; margin-bottom: 0;">
            <?php if (empty($subscribers)): ?>
                <div style="padding: 40px; text-align: center; color: var(--text-light);">
                    No subscribers found.
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)" style="width:16px; height:16px; cursor:pointer; accent-color:var(--green-900);"></th>
                            <th>Email Address</th>
                            <th>Subscribed On</th>
                            <th style="text-align: right; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subscribers as $sub): ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" class="subscriber-select-checkbox" value="<?php echo $sub['id']; ?>" onchange="updateBulkSelection()" style="width:16px; height:16px; cursor:pointer; accent-color:var(--green-900);">
                                </td>
                                <td><strong><?php echo htmlspecialchars($sub['email']); ?></strong></td>
                                <td><?php echo date('M d, Y \a\t H:i', strtotime($sub['created_at'])); ?></td>
                                <td style="text-align: right;">
                                    <form action="subscribers.php" method="POST" onsubmit="return confirm('Remove subscriber: <?php echo htmlspecialchars($sub['email']); ?>?');" style="display:inline-block;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="subscriber_id" value="<?php echo $sub['id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 8px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Paging grid -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination" style="margin-bottom:0; margin-top: auto; padding-top: 12px;">
                <a href="subscribers.php?page=<?php echo $page - 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">&larr;</a>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="subscribers.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                <a href="subscribers.php?page=<?php echo $page + 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">&rarr;</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Subscriber growth chart widget -->
    <div class="form-card" style="margin-bottom:0; flex-grow:1; display:flex; flex-direction:column; gap:16px;">
        <div class="editor-title" style="margin-bottom:0;">Subscriber Growth</div>
        <div style="flex-grow:1; position:relative; min-height:240px; display:flex; align-items:center; justify-content:center;">
            <canvas id="growthChart" width="340" height="240" style="max-width:100%;"></canvas>
        </div>
        <div style="font-size:12px; color:var(--text-light); text-align:center; padding-top:8px; border-top:1px solid var(--border-color);">
            Shows total registrations grouped by month.
        </div>
    </div>
</div>

<!-- Import CSV Modal overlay -->
<div class="modal-overlay" id="importModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Import Subscribers</h3>
            <button type="button" class="modal-close" onclick="closeImportModal()">&times;</button>
        </div>
        <form action="subscribers.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="import_csv">
            <div class="modal-body">
                <div class="form-group">
                    <label class="static-label">Select CSV File</label>
                    <div class="file-dropzone" id="csvDropzone">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"></path></svg>
                        <p>Select or drag CSV here</p>
                        <input type="file" name="csv_file" accept=".csv" required onchange="updateCsvDropzone(this)">
                    </div>
                </div>
                <div style="font-size:12.5px; color:var(--text-muted); line-height:1.5;">
                    Ensure the CSV file contains a header row. The script will scan the columns automatically to locate valid email addresses and import them.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Start Import</button>
            </div>
        </form>
    </div>
</div>

<!-- Render Growth Canvas JS Chart -->
<script>
    const chartData = <?php echo json_encode($chartData); ?>;

    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('growthChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const labels = chartData.map(item => item.month);
        const data = chartData.map(item => parseInt(item.count));
        
        // Setup dimensions
        const width = canvas.width;
        const height = canvas.height;
        const paddingLeft = 36;
        const paddingRight = 16;
        const paddingTop = 20;
        const paddingBottom = 30;
        
        const chartWidth = width - paddingLeft - paddingRight;
        const chartHeight = height - paddingTop - paddingBottom;
        
        function drawChart() {
            ctx.clearRect(0, 0, width, height);
            
            if (data.length === 0) {
                ctx.fillStyle = '#8E7A70';
                ctx.font = '14px Jost';
                ctx.fillText('No subscription data available', 60, 120);
                return;
            }

            // Retrieve CSS variables dynamically to match light/dark mode
            const styles = getComputedStyle(document.documentElement);
            const textLight = styles.getPropertyValue('--text-light').trim() || '#8E7A70';
            const textMain = styles.getPropertyValue('--text-main').trim() || '#3B2A22';
            const borderColor = styles.getPropertyValue('--border-color').trim() || '#EDE7DB';

            const maxVal = Math.max(...data, 5); // Fallback limit min 5
            const scaleY = chartHeight / (maxVal * 1.1);
            const barWidth = Math.max(16, (chartWidth / labels.length) * 0.6);
            const spacingX = chartWidth / labels.length;

            // Draw grids & Axes
            ctx.strokeStyle = borderColor;
            ctx.lineWidth = 1;
            
            // Horizontal lines
            for (let i = 0; i <= 4; i++) {
                const val = Math.round((maxVal / 4) * i);
                const y = height - paddingBottom - (val * scaleY);
                
                ctx.beginPath();
                ctx.moveTo(paddingLeft, y);
                ctx.lineTo(width - paddingRight, y);
                ctx.stroke();
                
                // Draw axis labels
                ctx.fillStyle = textLight;
                ctx.font = '10px Jost';
                ctx.textAlign = 'right';
                ctx.fillText(val, paddingLeft - 8, y + 3);
            }

            // Draw bars
            labels.forEach((label, idx) => {
                const val = data[idx];
                const x = paddingLeft + (idx * spacingX) + (spacingX - barWidth) / 2;
                const barHeight = val * scaleY;
                const y = height - paddingBottom - barHeight;

                // Draw bar with subtle gradient
                const grad = ctx.createLinearGradient(x, y, x, height - paddingBottom);
                grad.addColorStop(0, '#C7A66A'); // gold
                grad.addColorStop(1, '#0D3B12'); // green
                
                ctx.fillStyle = grad;
                ctx.fillRect(x, y, barWidth, barHeight);
                
                // Draw rounded top overlay (vanilla fallback)
                ctx.fillStyle = '#C7A66A';
                ctx.fillRect(x, y, barWidth, 3);

                // Draw text values above bar
                ctx.fillStyle = textMain;
                ctx.font = '11px Jost';
                ctx.textAlign = 'center';
                ctx.fillText(val, x + barWidth / 2, y - 6);

                // Draw labels
                ctx.fillStyle = textLight;
                ctx.font = '10px Jost';
                ctx.textAlign = 'center';
                ctx.fillText(label, x + barWidth / 2, height - paddingBottom + 16);
            });
        }

        // Draw initial chart
        drawChart();

        // Redraw on theme change
        window.addEventListener('themechanged', drawChart);
    });

    // Import CSV details Modal helpers
    function openImportModal() {
        document.getElementById('importModal').classList.add('show');
    }

    function closeImportModal() {
        document.getElementById('importModal').classList.remove('show');
    }

    function updateCsvDropzone(input) {
        const zone = document.getElementById('csvDropzone');
        if (input.files && input.files[0]) {
            zone.querySelector('p').innerText = input.files[0].name;
            zone.style.borderColor = 'var(--green-700)';
        }
    }

    // Bulk selection triggers
    function resetSelections() {
        const checkboxes = document.querySelectorAll('.subscriber-select-checkbox');
        checkboxes.forEach(c => c.checked = false);
        const selectAll = document.getElementById('selectAllCheckbox');
        if (selectAll) selectAll.checked = false;
        updateBulkSelection();
    }

    function toggleSelectAll(selectAllCheckbox) {
        const checkboxes = document.querySelectorAll('.subscriber-select-checkbox');
        checkboxes.forEach(c => c.checked = selectAllCheckbox.checked);
        updateBulkSelection();
    }

    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.subscriber-select-checkbox:checked');
        const bar = document.getElementById('bulkActionsBar');
        const countSpan = document.getElementById('bulkSelectedCount');
        
        countSpan.innerText = checkboxes.length;
        
        if (checkboxes.length > 0) {
            bar.classList.add('show');
        } else {
            bar.classList.remove('show');
        }
    }

    function submitBulk(action) {
        if (!confirm('Are you sure you want to delete all selected subscribers? This is permanent!')) return;

        const checkedBoxes = document.querySelectorAll('.subscriber-select-checkbox:checked');
        const container = document.getElementById('bulkIdsContainer');
        container.innerHTML = ''; // Clear prior entries
        
        checkedBoxes.forEach(box => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'subscriber_ids[]';
            input.value = box.value;
            container.appendChild(input);
        });

        document.getElementById('bulkActionField').value = action;
        document.getElementById('bulkForm').submit();
    }
</script>
