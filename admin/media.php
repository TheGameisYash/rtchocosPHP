<?php
// admin/media.php - Media Library Manager
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// Handle File Upload or File Delete POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token.';
    } else {
        try {
            if ($action === 'upload_media') {
                if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['media_file']['tmp_name'];
                    $fileName = $_FILES['media_file']['name'];
                    $fileSize = $_FILES['media_file']['size'];
                    $fileType = mime_content_type($fileTmpPath);
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
                    $maxFileSize = 5 * 1024 * 1024; // 5MB

                    if (!in_array($fileType, $allowedMimeTypes)) {
                        $error = 'Only JPG, PNG, and WEBP images are allowed.';
                    } elseif ($fileSize > $maxFileSize) {
                        $error = 'File size must be less than 5MB.';
                    } else {
                        $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__);
                        $parentDir = dirname($docRoot);
                        $liveUploadsDir = $parentDir . '/uploads_blogs';
                        if (is_dir($liveUploadsDir)) {
                            $blogsDir = $liveUploadsDir;
                        } else {
                            $blogsDir = $docRoot . '/assets/blogs';
                        }
                        if (!file_exists($blogsDir)) {
                            mkdir($blogsDir, 0755, true);
                        }

                        $newFileName = 'media-' . time() . '-' . rand(100, 999) . '.' . $fileExtension;
                        $destPath = $blogsDir . '/' . $newFileName;
                        $relativePath = 'assets/blogs/' . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            // Register in media table
                            $stmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
                            $stmt->execute([
                                $fileName,
                                $relativePath,
                                $fileType,
                                $fileSize
                            ]);
                            $success = 'Image uploaded successfully!';
                        } else {
                            $error = 'Failed to save uploaded file.';
                        }
                    }
                } else {
                    $error = 'Please select a valid image file.';
                }
            } elseif ($action === 'delete_media') {
                $mediaId = (int)($_POST['media_id'] ?? 0);
                if ($mediaId > 0) {
                    $stmt = $pdo->prepare("SELECT path FROM media WHERE id = ?");
                    $stmt->execute([$mediaId]);
                    $path = $stmt->fetchColumn();

                    if ($path) {
                        // Delete file
                        if (file_exists(__DIR__ . '/../' . $path)) {
                            unlink(__DIR__ . '/../' . $path);
                        }
                        
                        // Delete DB entry
                        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
                        $stmt->execute([$mediaId]);
                        $success = 'Image deleted successfully.';
                    } else {
                        $error = 'Image path not found in database.';
                    }
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
$limit = 18;
$offset = ($page - 1) * $limit;

$whereClauses = [];
$params = [];

if ($search !== '') {
    $whereClauses[] = "filename LIKE ?";
    $params[] = "%$search%";
}

$whereSql = '';
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

try {
    // Count matches
    $countSql = "SELECT COUNT(*) FROM media $whereSql";
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
    $selectSql = "SELECT * FROM media $whereSql ORDER BY uploaded_at DESC LIMIT ? OFFSET ?";
    $stmt = $pdo->prepare($selectSql);
    $paramIndex = 1;
    foreach ($params as $paramValue) {
        $stmt->bindValue($paramIndex++, $paramValue);
    }
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $mediaItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error loading media items: " . $e->getMessage());
}

$csrfToken = generate_csrf();
render_admin_header("Media Library", "media");
?>

<?php if (!empty($error)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($error); ?>, 'danger'));</script>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <script>window.addEventListener('DOMContentLoaded', () => showToast(<?php echo json_encode($success); ?>, 'success'));</script>
<?php endif; ?>

<!-- Actions Bar -->
<div class="action-bar" style="margin-bottom: 24px;">
    <div class="action-bar-left">
        <form action="media.php" method="GET" class="topbar-search" style="display:block; width: 280px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" name="search" placeholder="Search filenames..." value="<?php echo htmlspecialchars($search); ?>" style="width:100%; border-radius:8px; padding-left:36px; padding-top:8px; padding-bottom:8px;">
        </form>
    </div>
    
    <div class="action-bar-right">
        <button type="button" class="btn btn-primary" onclick="openUploadModal()">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Upload Image
        </button>
    </div>
</div>

<!-- Media Library Grid -->
<div class="form-card" style="padding:32px;">
    <?php if (empty($mediaItems)): ?>
        <div style="padding: 60px; text-align: center; color: var(--text-light);">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="width: 48px; height: 48px; opacity: 0.5; margin-bottom: 16px;"><path d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 002.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"></path></svg>
            <p style="font-size: 15px; font-weight: 500;">No images in media library.</p>
        </div>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($mediaItems as $item): 
                $kb = round($item['size'] / 1024, 1);
            ?>
                <div class="media-item" onclick="openDetailsModal(<?php echo htmlspecialchars(json_encode($item)); ?>, <?php echo $kb; ?>)">
                    <img src="../<?php echo htmlspecialchars($item['path']); ?>" alt="<?php echo htmlspecialchars($item['filename']); ?>">
                    <div class="media-item-info">
                        <?php echo htmlspecialchars($item['filename']); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" style="margin-bottom:0; margin-top:24px;">
            <a href="media.php?page=<?php echo $page - 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">&larr;</a>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="media.php?page=<?php echo $i; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <a href="media.php?page=<?php echo $page + 1; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">&rarr;</a>
        </div>
    <?php endif; ?>
</div>

<!-- Upload Media Modal Overlay -->
<div class="modal-overlay" id="uploadModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>Upload Media Image</h3>
            <button type="button" class="modal-close" onclick="closeUploadModal()">&times;</button>
        </div>
        <form action="media.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="upload_media">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="static-label">Select File</label>
                    <div class="file-dropzone" id="mediaDropzone">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"></path></svg>
                        <p>Click or drag image file here</p>
                        <input type="file" name="media_file" accept="image/*" required onchange="updateMediaDropzone(this)">
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeUploadModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Start Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Media Details Modal Overlay -->
<div class="modal-overlay" id="detailsModal">
    <div class="modal-container" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="detFilename">Image Details</h3>
            <button type="button" class="modal-close" onclick="closeDetailsModal()">&times;</button>
        </div>
        
        <div class="modal-body" style="display:flex; gap:20px; flex-wrap:wrap;">
            <!-- Left preview -->
            <div style="flex:1; min-width:200px; display:flex; align-items:center; justify-content:center; background:var(--bg-app); border:1px solid var(--border-color); border-radius:8px; overflow:hidden; padding:12px;">
                <img id="detImg" src="" style="max-width:100%; max-height:220px; object-fit:contain;">
            </div>
            
            <!-- Right metadata -->
            <div style="flex:1.2; min-width:200px; display:flex; flex-direction:column; gap:12px; font-size:13.5px;">
                <div>
                    <span style="font-weight:600; color:var(--text-light); display:block; font-size:11px; text-transform:uppercase;">URL Path</span>
                    <div style="display:flex; gap:6px; margin-top:4px;">
                        <input type="text" id="detPathUrl" readonly style="flex-grow:1; padding:6px 10px; border-radius:6px; border:1px solid var(--border-color); font-size:12.5px; background:var(--bg-app); outline:none;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="copyPathToClipboard()" style="padding:6px 10px;">Copy</button>
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:8px;">
                    <div>
                        <span style="font-weight:600; color:var(--text-light); font-size:11px; text-transform:uppercase;">Size</span>
                        <p id="detSize" style="font-weight:600; color:var(--text-main); margin-top:2px;">12 KB</p>
                    </div>
                    <div>
                        <span style="font-weight:600; color:var(--text-light); font-size:11px; text-transform:uppercase;">Mime-type</span>
                        <p id="detMime" style="font-weight:600; color:var(--text-main); margin-top:2px;">image/png</p>
                    </div>
                </div>
                
                <div style="margin-top:4px;">
                    <span style="font-weight:600; color:var(--text-light); font-size:11px; text-transform:uppercase;">Uploaded On</span>
                    <p id="detDate" style="font-weight:600; color:var(--text-main); margin-top:2px;">June 15, 2026</p>
                </div>
            </div>
        </div>
        
        <div class="modal-footer">
            <form action="media.php" method="POST" onsubmit="return confirm('Delete this image permanently from disk? This cannot be undone!');">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <input type="hidden" name="action" value="delete_media">
                <input type="hidden" name="media_id" id="detDeleteId" value="">
                <button type="submit" class="btn btn-danger" style="padding: 8px 16px;">Delete Permanently</button>
            </form>
            <button type="button" class="btn btn-outline" onclick="closeDetailsModal()" style="margin-left:8px;">Close</button>
        </div>
    </div>
</div>

<script>
    // Upload Modal helpers
    function openUploadModal() {
        document.getElementById('uploadModal').classList.add('show');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.remove('show');
    }

    function updateMediaDropzone(input) {
        const zone = document.getElementById('mediaDropzone');
        if (input.files && input.files[0]) {
            zone.querySelector('p').innerText = input.files[0].name;
            zone.style.borderColor = 'var(--green-700)';
        }
    }

    // Details Modal helpers
    function openDetailsModal(item, sizeKb) {
        document.getElementById('detFilename').innerText = item.filename;
        document.getElementById('detImg').src = '../' + item.path;
        document.getElementById('detPathUrl').value = item.path; // e.g. assets/blogs/filename.jpg
        document.getElementById('detSize').innerText = sizeKb + ' KB';
        document.getElementById('detMime').innerText = item.mime_type;
        document.getElementById('detDate').innerText = new Date(item.uploaded_at).toLocaleString();
        document.getElementById('detDeleteId').value = item.id;
        
        document.getElementById('detailsModal').classList.add('show');
    }

    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.remove('show');
    }

    function copyPathToClipboard() {
        const input = document.getElementById('detPathUrl');
        input.select();
        input.setSelectionRange(0, 99999); // For mobile
        
        navigator.clipboard.writeText(input.value)
            .then(() => showToast('Relative path copied to clipboard', 'success'))
            .catch(() => showToast('Failed to copy path', 'danger'));
    }
</script>
