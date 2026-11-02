<?php
// admin/failed-batch-editor.php — Dedicated Word-Style Rich Editor for RT Chocos Failed Batch Case Studies
// Provides full WYSIWYG formatting, typography controls, colors, tints, tables, media management, and live previews.
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/blog-uploads.php';

$pdo = get_db();
$error = '';
$success = '';

// Helper: Ensure columns exist in lab_failed_batches
if (!function_exists('ensure_failed_batch_columns')) {
function ensure_failed_batch_columns($pdo) {
    static $done = false;
    if ($done) return;
    try {
        $pdo->exec("ALTER TABLE lab_failed_batches ADD COLUMN full_content LONGTEXT NULL");
    } catch (Exception $e) {}
    $done = true;
}
}
ensure_failed_batch_columns($pdo);

// 1. AJAX INLINE IMAGE UPLOAD INTERCEPTOR (Block / Word Editor)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload_inline_image') {
    header('Content-Type: application/json');
    $token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }
    
    if (!isset($_FILES['inline_image']) || $_FILES['inline_image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No image file uploaded.']);
        exit;
    }
    
    $uploadDir = dirname(__DIR__) . '/assets/images';
    if (!file_exists($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    
    $fileTmpPath = $_FILES['inline_image']['tmp_name'];
    $fileName = $_FILES['inline_image']['name'];
    $fileSize = $_FILES['inline_image']['size'];
    $fileType = get_file_mime_type($fileTmpPath, $fileName);
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($fileType, $allowedMimeTypes)) {
        echo json_encode(['success' => false, 'message' => 'Image must be JPG, PNG or WEBP.']);
        exit;
    }
    
    if ($fileSize > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'Image size must be less than 5MB.']);
        exit;
    }
    
    $newFileName = 'fail-inline-' . time() . '-' . rand(1000, 9999) . '.' . $fileExtension;
    $targetPath = $uploadDir . '/' . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $targetPath)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO media (filename, path, mime_type, size) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $newFileName,
                'assets/images/' . $newFileName,
                $fileType,
                $fileSize
            ]);
        } catch (Exception $e) {}

        echo json_encode([
            'success' => true,
            'url' => 'assets/images/' . $newFileName,
            'full_url' => '/assets/images/' . $newFileName
        ]);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded image.']);
        exit;
    }
}

// 2. AJAX MEDIA LIBRARY FETCHER FOR IMAGE MODAL
if (($_GET['action'] ?? ($_POST['action'] ?? '')) === 'get_media_library') {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->query("SELECT id, filename, path, path as url, size, created_at FROM media ORDER BY id DESC LIMIT 60");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'media' => $items]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 3. AJAX INSTANT VISIBILITY TOGGLE (Instant Active / Draft)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_active') {
    header('Content-Type: application/json');
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }
    $failId = (int)($_POST['failed_id'] ?? 0);
    $targetState = isset($_POST['is_active']) ? ((int)$_POST['is_active'] === 1 ? 1 : 0) : null;
    if ($failId > 0) {
        if ($targetState !== null) {
            $stmt = $pdo->prepare("UPDATE lab_failed_batches SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$targetState, $failId]);
        } else {
            $stmt = $pdo->prepare("UPDATE lab_failed_batches SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$failId]);
        }
        $stmt = $pdo->prepare("SELECT id, slug, title, is_active FROM lab_failed_batches WHERE id = ?");
        $stmt->execute([$failId]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        $isActive = $updated ? (int)$updated['is_active'] : 0;
        echo json_encode([
            'success' => true,
            'failed_id' => $failId,
            'is_active' => $isActive,
            'status_label' => $isActive ? 'Visible (Active)' : 'Hidden (Draft)',
            'message' => 'Case study "' . ($updated['title'] ?? 'Batch') . '" is now ' . ($isActive ? 'Visible to public visitors' : 'Hidden from public visitors (Draft)') . '.'
        ]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Failed batch not saved in database yet. Save first to toggle visibility.']);
    exit;
}

$failId = (int)($_GET['id'] ?? 0);
$isEdit = $failId > 0;

// Default empty values for new failed batch
$fail = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'badge_label' => 'WHEN THINGS GO WRONG, WE LEARN.',
    'image_path' => 'assets/images/lab-failed-batch.jpg',
    'hypothesis' => '',
    'what_happened' => '',
    'why_it_happened' => '',
    'what_next' => '',
    'full_story' => '',
    'full_content' => '',
    'is_featured' => 0,
    'sort_order' => 0,
    'is_active' => 1
];

// If Edit, fetch from database
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lab_failed_batches WHERE id = ?");
        $stmt->execute([$failId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fetched) {
            header('Location: innovation-lab.php?tab=failed-batches&error=' . urlencode('Failed batch case study not found.'));
            exit;
        }
        $fail = array_merge($fail, $fetched);
    } catch (Exception $e) {
        $error = 'Error fetching failed batch: ' . $e->getMessage();
    }
}

// Build initial editor content if full_content is empty
if (empty(trim((string)$fail['full_content']))) {
    $prefill = '';
    if (!empty($fail['hypothesis'])) {
        $prefill .= "<h2>The Research Hypothesis</h2>\n<p>" . nl2br(htmlspecialchars($fail['hypothesis'])) . "</p>\n\n";
    } else {
        $prefill .= "<h2>The Research Hypothesis</h2>\n<p>We set out to formulate an ambitious trial testing novel ingredients, fat systems, or botanical interactions in artisanal single-origin chocolate.</p>\n\n";
    }

    if (!empty($fail['what_happened'])) {
        $prefill .= "<h2>What Went Wrong (The Anomaly)</h2>\n<p>" . nl2br(htmlspecialchars($fail['what_happened'])) . "</p>\n\n";
    }

    if (!empty($fail['why_it_happened'])) {
        $prefill .= "<h2>Why It Happened (The Chocolate Chemistry)</h2>\n<p>" . nl2br(htmlspecialchars($fail['why_it_happened'])) . "</p>\n\n";
    }

    if (!empty($fail['what_next'])) {
        $prefill .= "<h2>The Breakthrough &amp; What We Learned</h2>\n<p>" . nl2br(htmlspecialchars($fail['what_next'])) . "</p>\n\n";
    }

    if (!empty($fail['full_story']) && trim($fail['full_story'])) {
        $prefill .= "<h2>Behind the Scenes: Field Notes &amp; Full Story</h2>\n<p>" . nl2br(htmlspecialchars($fail['full_story'])) . "</p>\n\n";
    }

    $prefill .= "<h2>Preventative Protocols &amp; Future Batch Standards</h2>\n<ul>\n  <li><strong>Standard Operating Rule:</strong> Maintain careful lipid friction and thermal control under 38°C during conching.</li>\n  <li><strong>Water Activity Check:</strong> Measure moisture titration (Aw &lt; 0.32) before introducing freeze-dried fruit or botanical powders.</li>\n</ul>\n";

    $fail['full_content'] = trim($prefill);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_failed_batch') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $badgeLabel = trim($_POST['badge_label'] ?? 'WHEN THINGS GO WRONG, WE LEARN.');
    $hypothesis = trim($_POST['hypothesis'] ?? '');
    $whatHappened = trim($_POST['what_happened'] ?? '');
    $whyItHappened = trim($_POST['why_it_happened'] ?? '');
    $whatNext = trim($_POST['what_next'] ?? '');
    $fullContent = trim($_POST['full_content'] ?? ($_POST['content'] ?? ''));

    // Server-side cleanup of any editor UI artifacts before saving
    $fullContent = preg_replace('/<div class="editor-(?:table-topbar|figure-topbar|table-resize-handle|resize-handle|block-drag-handle|drop-line|col-guide|size-badge)"[^>]*>.*?<\/div>/is', '', $fullContent);
    $fullContent = preg_replace('/<div class="topbar-actions"[^>]*>.*?<\/div>/is', '', $fullContent);
    $fullContent = preg_replace('/<button\b[^>]*\b(?:data-action|topbar-btn)[^>]*>.*?<\/button>/is', '', $fullContent);
    $fullContent = preg_replace('/<span class="topbar-sep"[^>]*>.*?<\/span>/is', '', $fullContent);
    $fullContent = preg_replace('/\s*contenteditable=[\'"][^\'"]*[\'"]/i', '', $fullContent);
    $fullContent = preg_replace('/\s*data-controls-attached=[\'"][^\'"]*[\'"]/i', '', $fullContent);
    $fullContent = preg_replace('/\s*class=[\'"]([^\'"]*)\b(is-selected|is-dragging|is-resizing|just-moved|drag-over)\b([^\'"]*)[\'"]/i', 'class="$1 $3"', $fullContent);

    $isFeatured = !empty($_POST['is_featured']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = (!empty($_POST['is_active']) && $_POST['is_active'] !== '0') ? 1 : 0;
    $existingImg = trim($_POST['existing_image_path'] ?? $fail['image_path']);

    // Retain form input on validation error
    $fail['title'] = $title;
    $fail['slug'] = $slug;
    $fail['badge_label'] = $badgeLabel;
    $fail['hypothesis'] = $hypothesis;
    $fail['what_happened'] = $whatHappened;
    $fail['why_it_happened'] = $whyItHappened;
    $fail['what_next'] = $whatNext;
    $fail['full_content'] = $fullContent;
    $fail['full_story'] = $fullContent;
    $fail['is_featured'] = $isFeatured;
    $fail['sort_order'] = $sortOrder;
    $fail['is_active'] = $isActive;

    if (!verify_csrf($csrfToken)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } elseif (empty($title)) {
        $error = 'Case study title is required.';
    } else {
        // Auto-generate or sanitize slug
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        } else {
            $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($slug)));
        }
        $fail['slug'] = $slug;

        try {
            // Check for slug uniqueness
            $slugCheckSql = $isEdit ? "SELECT id FROM lab_failed_batches WHERE slug = ? AND id != ?" : "SELECT id FROM lab_failed_batches WHERE slug = ?";
            $slugCheckParams = $isEdit ? [$slug, $failId] : [$slug];
            $stmt = $pdo->prepare($slugCheckSql);
            $stmt->execute($slugCheckParams);
            if ($stmt->fetch()) {
                $error = "The URL slug '{$slug}' is already in use by another failed batch case study.";
            } else {
                $imagePath = $existingImg;

                // Handle Header / Cover Image Upload
                if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['header_image']['tmp_name'];
                    $fileName = $_FILES['header_image']['name'];
                    $fileSize = $_FILES['header_image']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    $maxSize = 5 * 1024 * 1024; // 5MB

                    if (!in_array($fileExt, $allowedExts)) {
                        $error = 'Cover image must be a JPG, PNG, or WEBP file.';
                    } elseif ($fileSize > $maxSize) {
                        $error = 'Cover image must be less than 5MB.';
                    } else {
                        $destName = 'failed-batch-' . $slug . '-' . time() . '.' . $fileExt;
                        $uploadDir = dirname(__DIR__) . '/assets/images';
                        if (!file_exists($uploadDir)) {
                            @mkdir($uploadDir, 0755, true);
                        }
                        $targetPath = $uploadDir . '/' . $destName;
                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $imagePath = 'assets/images/' . $destName;
                        } else {
                            $error = 'Failed to save uploaded image file.';
                        }
                    }
                }

                if (empty($error)) {
                    if ($isFeatured === 1) {
                        $pdo->exec("UPDATE lab_failed_batches SET is_featured = 0");
                    }

                    if ($isEdit) {
                        $stmt = $pdo->prepare("UPDATE lab_failed_batches SET 
                            title = ?, slug = ?, badge_label = ?, image_path = ?, hypothesis = ?, 
                            what_happened = ?, why_it_happened = ?, what_next = ?, full_story = ?, full_content = ?, 
                            is_featured = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $slug, $badgeLabel, $imagePath, $hypothesis,
                            $whatHappened, $whyItHappened, $whatNext, $fullContent, $fullContent,
                            $isFeatured, $sortOrder, $isActive, $failId
                        ]);
                        $success = 'Failed batch case study updated successfully!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_failed_batches 
                            (title, slug, badge_label, image_path, hypothesis, what_happened, why_it_happened, what_next, full_story, full_content, is_featured, sort_order, is_active, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([
                            $title, $slug, $badgeLabel, $imagePath, $hypothesis,
                            $whatHappened, $whyItHappened, $whatNext, $fullContent, $fullContent,
                            $isFeatured, $sortOrder, $isActive
                        ]);
                        $failId = (int)$pdo->lastInsertId();
                        $isEdit = true;
                        $success = 'New failed batch case study logged successfully!';
                    }

                    // Refresh $fail array
                    $stmt = $pdo->prepare("SELECT * FROM lab_failed_batches WHERE id = ?");
                    $stmt->execute([$failId]);
                    $fail = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Redirect back to list if requested
                    if (isset($_POST['save_and_exit'])) {
                        header('Location: innovation-lab.php?tab=failed-batches&success=' . urlencode($success));
                        exit;
                    }
                }
            }
        } catch (Exception $e) {
            $error = 'Save failed: ' . $e->getMessage();
        }
    }
}

$csrfToken = generate_csrf();
render_admin_header($isEdit ? "Edit Failed Batch — " . htmlspecialchars($fail['title']) : "Log Failed Batch Case Study", "innovation-lab");
?>

<link rel="stylesheet" href="editor-style.css?v=<?php echo file_exists(__DIR__ . '/editor-style.css') ? filemtime(__DIR__ . '/editor-style.css') : '1'; ?>">

<?php if (!empty($error)): ?>
    <div style="background:#FEE2E2; border:1px solid #EF4444; color:#991B1B; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600;">
        ⚠️ <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div style="background:#DCFCE7; border:1px solid #22C55E; color:#166534; padding:12px 18px; border-radius:8px; margin-bottom:20px; font-size:13.5px; font-weight:600;">
        ✓ <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- Sub-Header Actions -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <a href="innovation-lab.php?tab=failed-batches" class="btn btn-outline" style="display:inline-flex; align-items:center; gap:6px;">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:14px;height:14px;"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Failed Batches
        </a>

        <!-- Live Topbar Visibility Toggle Switch -->
        <div class="topbar-visibility-box" style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:8px; background:var(--bg-card); border:1px solid var(--border-color);">
            <span style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--text-light); letter-spacing:0.5px;">Visibility:</span>
            <label class="custom-toggle" style="transform:scale(0.85); margin:0; cursor:pointer;">
                <input type="checkbox" id="topbarVisibilityToggle" <?php echo $fail['is_active'] ? 'checked' : ''; ?> onchange="toggleFailedBatchActive(<?php echo $fail['id']; ?>, this.checked)">
                <span class="toggle-slider"></span>
            </label>
            <span id="topbarVisibilityBadge" class="status-badge <?php echo $fail['is_active'] ? 'published' : 'draft'; ?>" style="font-size:11px; padding:3px 8px; <?php echo $fail['is_active'] ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                <span id="topbarVisibilityIcon"><?php echo $fail['is_active'] ? '👁️' : '🔒'; ?></span>
                <span id="topbarVisibilityLabel"><?php echo $fail['is_active'] ? 'Visible (Public)' : 'Hidden (Draft)'; ?></span>
            </span>
        </div>
    </div>
    
    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <span id="autosaveIndicator" style="font-size:12.5px; color:var(--text-light); opacity:0.75;"><span style="color:#2e7d32;">●</span> Rich Styling Active</span>
        <button type="button" class="btn btn-outline" onclick="window.triggerUndo && window.triggerUndo()" title="Undo (Ctrl+Z)">Undo</button>
        <button type="button" class="btn btn-outline" onclick="window.triggerRedo && window.triggerRedo()" title="Redo (Ctrl+Y)">Redo</button>
        <button type="button" class="btn btn-outline" onclick="openFullPreview()">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Live Preview
        </button>
        <?php if ($isEdit && !empty($fail['slug'])): ?>
            <a href="../failed-batch/<?php echo htmlspecialchars($fail['slug']); ?>" target="_blank" class="btn btn-outline" style="display:inline-flex; align-items:center; gap:6px;">
                <span>View on Site</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            </a>
        <?php endif; ?>
        <button type="button" class="btn btn-outline" id="drawerToggle" title="Open Case Study Settings &amp; Cover Photo" style="display:inline-flex; align-items:center; gap:8px;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span>Cover &amp; Settings</span>
        </button>
        <button type="button" class="btn btn-primary" onclick="submitFailEditorForm()" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; font-weight:700;">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
            <span>Save Case Study</span>
        </button>
    </div>
</div>

<!-- Main Form -->
<form action="failed-batch-editor.php<?php echo $isEdit ? '?id=' . $failId : ''; ?>" method="POST" enctype="multipart/form-data" id="failEditorForm">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="action" value="save_failed_batch">
    <input type="hidden" name="failed_id" value="<?php echo (int)$fail['id']; ?>">
    <input type="hidden" name="is_active" id="isActiveHiddenInput" value="<?php echo $fail['is_active'] ? '1' : '0'; ?>">
    <input type="hidden" name="full_content" id="content" value="<?php echo htmlspecialchars($fail['full_content']); ?>">
    <input type="hidden" name="existing_image_path" id="existing_image_path" value="<?php echo htmlspecialchars($fail['image_path']); ?>">

    <div class="editor-canvas-wrapper">
        <!-- Main Canvas Panel -->
        <div class="editor-main-canvas word-editor-canvas">
            
            <!-- Quick Title & Permastrip at Top of Canvas -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <input type="text" id="canvasTitleInput" value="<?php echo htmlspecialchars($fail['title']); ?>" placeholder="Failed Batch Case Title (e.g. Trial #04: Guava Seizure)..." style="font-family:'Playfair Display', Georgia, serif; font-size:32px; font-weight:700; width:100%; border:none; background:transparent; color:var(--text-main); outline:none; padding:0; margin-bottom:8px;" oninput="syncCanvasTitle(this.value)">
                <div style="font-size:12.5px; color:var(--text-light); display:flex; align-items:center; gap:6px;">
                    <span style="font-weight:600; color:var(--text-muted);">Permalink:</span>
                    <span>https://www.rtchocos.com/failed-batch/<strong id="permalinkSlugPreview" style="color:var(--gold, #C7A66A);"><?php echo htmlspecialchars($fail['slug'] ?: 'batch-slug'); ?></strong></span>
                </div>
            </div>

            <!-- Word Formatting Toolbar -->
            <div class="word-editor-toolbar" id="wordToolbar">
                <select class="word-format-select" id="formatBlockSelect" title="Paragraph Style">
                    <option value="p">Normal Text</option>
                    <option value="h2">Heading 2</option>
                    <option value="h3">Heading 3</option>
                    <option value="h4">Heading 4</option>
                    <option value="blockquote">Quote / Callout</option>
                    <option value="pre">Code Block</option>
                </select>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                <button type="button" class="word-btn" data-action="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                <button type="button" class="word-btn" data-action="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                <button type="button" class="word-btn" data-action="strikeThrough" title="Strikethrough"><s>S</s></button>

                <!-- Text Color Dropdown -->
                <div class="word-color-dropdown-wrapper">
                    <button type="button" class="word-btn word-color-btn" id="textColorBtn" title="Text Color" onclick="toggleColorDropdown('textColorDropdown')">
                        <span style="font-weight:700; font-size:13px; line-height:1;">A</span>
                        <span class="color-indicator-bar" id="textColorIndicator" style="background:var(--gold, #c7a66a);"></span>
                    </button>
                    <div class="word-color-dropdown" id="textColorDropdown" style="display:none; min-width:200px; padding:10px 8px;">
                        <div class="color-dropdown-header" style="font-size:11px; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:8px; color:var(--text-light); font-weight:700;">Text Color</div>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <button type="button" class="color-swatch-row" data-color="" title="Default Theme">
                                <span style="width:14px; height:14px; border-radius:50%; background:var(--text-main); border:1px solid var(--border-color); display:inline-block; flex-shrink:0;"></span>
                                <span style="font-weight:600;">Theme Default</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-color="#c7a66a" title="Gold Accent">
                                <span style="width:14px; height:14px; border-radius:50%; background:#c7a66a; display:inline-block; flex-shrink:0;"></span>
                                <span>Gold Accent</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-color="#5a3825" title="Deep Cocoa Bronze">
                                <span style="width:14px; height:14px; border-radius:50%; background:#5a3825; display:inline-block; flex-shrink:0;"></span>
                                <span>Deep Cocoa</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-color="#1b4d3e" title="Artisan Green">
                                <span style="width:14px; height:14px; border-radius:50%; background:#1b4d3e; display:inline-block; flex-shrink:0;"></span>
                                <span>Artisan Green</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-color="#dc2626" title="Defect Red">
                                <span style="width:14px; height:14px; border-radius:50%; background:#dc2626; display:inline-block; flex-shrink:0;"></span>
                                <span>Defect Crimson</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-color="#c25e2e" title="Warm Terracotta">
                                <span style="width:14px; height:14px; border-radius:50%; background:#c25e2e; display:inline-block; flex-shrink:0;"></span>
                                <span>Warm Terracotta</span>
                            </button>
                        </div>
                        <div class="color-picker-custom-row">
                            <input type="color" id="customTextColorPicker" class="color-picker-custom-input" value="#c7a66a">
                            <label for="customTextColorPicker" style="font-size:12px; cursor:pointer; color:var(--text-main); font-weight:500;">Custom Color...</label>
                        </div>
                    </div>
                </div>

                <!-- Text Highlight / Tint Dropdown -->
                <div class="word-color-dropdown-wrapper">
                    <button type="button" class="word-btn word-color-btn" id="textHighlightBtn" title="Highlight Text" onclick="toggleColorDropdown('textHighlightDropdown')">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        <span class="color-indicator-bar" id="highlightColorIndicator" style="background:#c7a66a;"></span>
                    </button>
                    <div class="word-color-dropdown" id="textHighlightDropdown" style="display:none; min-width:200px; padding:10px 8px;">
                        <div class="color-dropdown-header" style="font-size:11px; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:8px; color:var(--text-light); font-weight:700;">Highlight (Tint)</div>
                        <div style="display:flex; flex-direction:column; gap:4px;">
                            <button type="button" class="color-swatch-row" data-highlight="" title="Clear Highlight">
                                <span style="width:14px; height:14px; border-radius:50%; background:transparent; border:1px dashed var(--border-color); display:inline-flex; align-items:center; justify-content:center; color:#ef4444; font-size:11px; font-weight:700;">&times;</span>
                                <span style="font-weight:600;">Clear Highlight</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-highlight="rgba(199, 166, 106, 0.3)" title="Gold Glow Tint">
                                <span style="width:14px; height:14px; border-radius:3px; background:rgba(199, 166, 106, 0.45); border:1px solid #c7a66a; display:inline-block;"></span>
                                <span>Gold Glow Tint</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-highlight="rgba(220, 38, 38, 0.2)" title="Defect Pink Tint">
                                <span style="width:14px; height:14px; border-radius:3px; background:rgba(220, 38, 38, 0.3); border:1px solid #dc2626; display:inline-block;"></span>
                                <span>Defect Pink Tint</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-highlight="rgba(22, 163, 74, 0.22)" title="Success Green Tint">
                                <span style="width:14px; height:14px; border-radius:3px; background:rgba(22, 163, 74, 0.3); border:1px solid #16a34a; display:inline-block;"></span>
                                <span>Solution Mint Tint</span>
                            </button>
                            <button type="button" class="color-swatch-row" data-highlight="rgba(245, 158, 11, 0.25)" title="Amber Honey Tint">
                                <span style="width:14px; height:14px; border-radius:3px; background:rgba(245, 158, 11, 0.38); border:1px solid #f59e0b; display:inline-block;"></span>
                                <span>Amber Honey Tint</span>
                            </button>
                        </div>
                        <div class="color-picker-custom-row">
                            <input type="color" id="customHighlightPicker" class="color-picker-custom-input" value="#fef08a">
                            <label for="customHighlightPicker" style="font-size:12px; cursor:pointer; color:var(--text-main); font-weight:500;">Custom Tint...</label>
                        </div>
                    </div>
                </div>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="justifyLeft" title="Align Left">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h10M4 18h14"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="justifyCenter" title="Align Center">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M7 12h10M5 18h14"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="justifyRight" title="Align Right">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M10 12h10M6 18h14"/></svg>
                </button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="insertUnorderedList" title="Bullet List">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="insertOrderedList" title="Numbered List">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 6h11M10 12h11M10 18h11M4 6h1v4M4 10h2M3 18h3-3v-2a2 2 0 012-2h1"/></svg>
                </button>
                <button type="button" class="word-btn" id="insertQuoteBtn" data-action="toggleQuote" onclick="window.toggleWordQuote && window.toggleWordQuote()" title="Quote / Callout Box">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179zm10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311 1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 01-3.5 3.5c-1.073 0-2.099-.49-2.748-1.179z"/></svg>
                </button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" id="insertLinkBtn" title="Insert Link (Ctrl+K)">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                </button>
                <button type="button" class="word-btn word-btn-featured" id="insertImageBtn" onclick="window.openWordImageModal && window.openWordImageModal()" title="Insert Image into Case Study" style="display:inline-flex; align-items:center; gap:5px; padding:0 10px; font-weight:600;">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                    <span>Image</span>
                </button>
                <button type="button" class="word-btn word-btn-featured" id="insertTableBtn" onclick="window.openWordTableModal && window.openWordTableModal()" title="Insert Forensic Comparison Table" style="display:inline-flex; align-items:center; gap:5px; padding:0 10px; font-weight:600;">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/></svg>
                    <span>Table</span>
                </button>
                <button type="button" class="word-btn" id="insertYoutubeBtn" title="Insert YouTube Embed">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.25 29 29 0 00-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/></svg>
                </button>
                <button type="button" class="word-btn" data-action="insertHorizontalRule" title="Horizontal Divider">—</button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="removeFormat" title="Clear Formatting">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12M12 4v16M4 20l4-4"/></svg>
                </button>
                <button type="button" class="word-btn word-btn-scrub" id="scrubDocFormattingBtn" onclick="window.scrubDocumentFormatting && window.scrubDocumentFormatting(true)" title="Fix Text Formatting: Reset foreign styles to site theme" style="color:var(--gold, #C7A66A);">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    <span style="font-size:11.5px; margin-left:4px; font-weight:600;">Fix Text</span>
                </button>

                <div class="toolbar-sep"></div>

                <!-- Paste Mode Toggle -->
                <button type="button" class="word-btn word-paste-mode-btn active" id="pasteModeToggleBtn" onclick="window.togglePasteMode && window.togglePasteMode()" title="Toggle Paste Mode: Text Only vs Clean Format">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 12h6M9 16h4"/></svg>
                    <span class="paste-mode-label" id="pasteModeLabel" style="font-size:11.5px; margin-left:4px; font-weight:600;">Paste: Text Only</span>
                </button>

                <div class="toolbar-sep"></div>

                <button type="button" class="word-btn" data-action="undo" title="Undo (Ctrl+Z)">↶</button>
                <button type="button" class="word-btn" data-action="redo" title="Redo (Ctrl+Y)">↷</button>
            </div>

            <!-- Floating Selection Mini Bubble Toolbar -->
            <div class="editor-floating-bubble-toolbar" id="floatingBubbleToolbar">
                <button type="button" class="word-btn" data-bubble-action="bold" title="Bold (Ctrl+B)"><b>B</b></button>
                <button type="button" class="word-btn" data-bubble-action="italic" title="Italic (Ctrl+I)"><i>I</i></button>
                <button type="button" class="word-btn" data-bubble-action="underline" title="Underline (Ctrl+U)"><u>U</u></button>
                <div class="toolbar-sep" style="height:14px; margin:0 3px;"></div>
                <button type="button" class="word-btn" data-bubble-block="h2" title="Heading 2" style="font-weight:700; font-size:11px;">H2</button>
                <button type="button" class="word-btn" data-bubble-block="h3" title="Heading 3" style="font-weight:700; font-size:11px;">H3</button>
                <button type="button" class="word-btn" data-bubble-block="blockquote" title="Quote" style="font-size:14px; font-weight:700;">”</button>
                <div class="toolbar-sep" style="height:14px; margin:0 3px;"></div>
                <button type="button" class="word-btn" id="bubbleLinkBtn" title="Link (Ctrl+K)">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                </button>
                <button type="button" class="word-btn" data-bubble-color="#c7a66a" title="Gold Accent Color" style="color:#c7a66a; font-weight:700; font-size:13px;">A</button>
                <button type="button" class="word-btn" data-bubble-highlight="rgba(199, 166, 106, 0.35)" title="Gold Glow Tint" style="padding:0 5px;">
                    <span style="display:inline-block; width:12px; height:12px; background:rgba(199, 166, 106, 0.45); border:1px solid #c7a66a; border-radius:3px;"></span>
                </button>
                <button type="button" class="word-btn" data-bubble-action="removeFormat" title="Clear Formatting">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 4h12M12 4v16M4 20l4-4"/></svg>
                </button>
            </div>

            <!-- Single Continuous Editable Document Area -->
            <div class="word-editor-body" id="wordEditorDoc" contenteditable="true" spellcheck="true" placeholder="Document the post-mortem, anomaly observation, chocolate chemistry, and preventative standards..."></div>

            <!-- Stats Bar -->
            <div class="word-editor-footer">
                <span id="char-count">0 characters</span>
                <span class="footer-dot">•</span>
                <span id="word-count">0 words</span>
                <span class="footer-dot">•</span>
                <span id="read-time-calc">1 min read</span>
            </div>
        </div>

        <!-- Collapsible Settings Side-Drawer -->
        <div class="editor-settings-drawer" id="settingsDrawer">
            <div class="drawer-header">
                <h3>Case Study Settings</h3>
                <button type="button" class="topbar-btn" id="closeDrawerBtn" title="Close Drawer">&times;</button>
            </div>
            
            <div class="drawer-body">
                <div class="form-group">
                    <label class="static-label" for="title">Title *</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($fail['title']); ?>" required placeholder="e.g. Trial #04: Guava Seizure" oninput="syncTitleDrawer(this.value)">
                </div>

                <div class="form-group">
                    <label class="static-label" for="slug">URL Slug *</label>
                    <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($fail['slug']); ?>" required placeholder="e.g. guava-seizure" oninput="syncSlugPreview(this.value)">
                </div>

                <div class="form-group">
                    <label class="static-label" for="badge_label">Badge Label</label>
                    <input type="text" id="badge_label" name="badge_label" value="<?php echo htmlspecialchars($fail['badge_label']); ?>" placeholder="WHEN THINGS GO WRONG, WE LEARN.">
                </div>

                <!-- Structured Forensic Pillars (Used on Innovation Lab Overview Cards) -->
                <div style="border-top:1px solid var(--border-color); padding-top:16px; margin-top:16px;">
                    <span style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--gold, #C7A66A); display:block; margin-bottom:12px;">Quick Laboratory Summary (Lab Grid Cards)</span>

                    <div class="form-group">
                        <label class="static-label" for="hypothesis">1. What We Hoped For (Hypothesis)</label>
                        <textarea id="hypothesis" name="hypothesis" rows="2" placeholder="e.g. Test if freeze-dried guava powder can be stone-ground directly with cocoa..."><?php echo htmlspecialchars($fail['hypothesis']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="static-label" for="what_happened" style="color:#DC2626;">2. What Went Wrong (Anomaly)</label>
                        <textarea id="what_happened" name="what_happened" rows="2" placeholder="e.g. At hour 14, the batch suddenly seized into a stiff paste..." oninput="syncSeoSnippet(this.value)"><?php echo htmlspecialchars($fail['what_happened']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="static-label" for="why_it_happened">3. Why It Happened (Chemistry)</label>
                        <textarea id="why_it_happened" name="why_it_happened" rows="2" placeholder="e.g. Residual hygroscopic moisture in fruit powder absorbed free cocoa butter..."><?php echo htmlspecialchars($fail['why_it_happened']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="static-label" for="what_next" style="color:#16A34A;">4. What We Learned (Solution)</label>
                        <textarea id="what_next" name="what_next" rows="2" placeholder="e.g. Coat fruit powders with tempered cocoa butter before stone grinding..."><?php echo htmlspecialchars($fail['what_next']); ?></textarea>
                    </div>
                </div>

                <!-- Google SEO Search Listing Preview -->
                <div class="form-group" style="margin-top:16px;">
                    <label class="static-label">Google Search Listing Preview</label>
                    <div class="seo-preview-card" style="background:var(--bg-app); border:1px solid var(--border-color); border-radius:8px; padding:12px 14px;">
                        <div class="seo-title" id="seoPreviewTitle" style="color:#1a0dab; font-size:15px; font-weight:600; margin-bottom:2px; font-family:sans-serif;"><?php echo htmlspecialchars($fail['title'] ?: 'Failed Batch Case Study'); ?> | RT Chocos</div>
                        <div class="seo-url" id="seoPreviewUrl" style="color:#006621; font-size:12px; margin-bottom:4px; font-family:sans-serif;">https://www.rtchocos.com/failed-batch/<span id="seoSlugText"><?php echo htmlspecialchars($fail['slug'] ?: 'batch-slug'); ?></span></div>
                        <div class="seo-snippet" id="seoPreviewExcerpt" style="color:#545454; font-size:12px; line-height:1.4; font-family:sans-serif;"><?php echo htmlspecialchars($fail['what_happened'] ?: 'A transparent behind-the-scenes laboratory case study on what went wrong and how we fixed it.'); ?></div>
                    </div>
                </div>

                <!-- Cover Image Dropzone -->
                <div class="form-group">
                    <label class="static-label" for="header_image">Cover Photo</label>
                    <div class="file-dropzone" id="headerDropzone">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"></path></svg>
                        <p>Upload case study photo</p>
                        <input type="file" id="header_image" name="header_image" onchange="previewFile(this, 'header-preview')">
                        <div class="file-preview" id="header-preview" style="<?php echo !empty($fail['image_path']) ? '' : 'display:none;'; ?>">
                            <img src="<?php echo !empty($fail['image_path']) ? '../' . htmlspecialchars($fail['image_path']) : ''; ?>" alt="">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="display:flex; justify-content:space-between; align-items:center; margin-top:16px;">
                    <div>
                        <span class="custom-toggle-label" style="font-weight:600; font-size:12.5px; color:var(--text-muted); text-transform:uppercase; display:block;">★ Featured on Homepage</span>
                        <span style="font-size:11px; color:var(--text-light);">Showcase as prominent failure on home &amp; lab</span>
                    </div>
                    <label class="custom-toggle">
                        <input type="checkbox" name="is_featured" value="1" <?php echo $fail['is_featured'] ? 'checked' : ''; ?>>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="form-group">
                    <label class="static-label" for="sort_order">Display Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?php echo (int)$fail['sort_order']; ?>">
                </div>

                <div class="form-group" style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <span class="custom-toggle-label" style="font-weight:600; font-size:12.5px; color:var(--text-muted); text-transform:uppercase; display:block;">Public Visibility</span>
                        <span id="drawerVisibilityHint" style="font-size:11px; color:var(--text-light);"><?php echo $fail['is_active'] ? 'Visible to public on website' : 'Hidden from public (Draft)'; ?></span>
                    </div>
                    <label class="custom-toggle">
                        <input type="checkbox" id="drawerVisibilityToggle" <?php echo $fail['is_active'] ? 'checked' : ''; ?> onchange="syncDrawerVisibility(this.checked)">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <button type="button" onclick="submitFailEditorForm()" class="btn btn-primary" style="width:100%; justify-content:center; padding:12px; font-weight:700;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:18px;height:18px;"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    Save Case Study
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Word Editor Image Insert Modal -->
<div class="word-modal-overlay" id="wordEditorImageModal">
    <div class="word-modal-card word-modal-card-lg">
        <div class="word-modal-header">
            <h3>Insert Case Study Image</h3>
            <button type="button" class="word-modal-close" id="closeWordImageModal" onclick="document.getElementById('wordEditorImageModal').style.display='none'">&times;</button>
        </div>

        <div class="modal-tabs">
            <button type="button" class="modal-tab-btn active" data-tab="uploadTab" onclick="switchImageModalTab('uploadTab')">Upload File</button>
            <button type="button" class="modal-tab-btn" data-tab="mediaTab" onclick="switchImageModalTab('mediaTab'); loadEditorMediaLibrary();">Media Library</button>
            <button type="button" class="modal-tab-btn" data-tab="urlTab" onclick="switchImageModalTab('urlTab')">Image URL</button>
        </div>

        <div class="modal-tab-pane active" id="uploadTab">
            <div class="word-image-dropzone" id="wordImageDropzone">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z"/></svg>
                <p style="margin:0; font-size:14px; font-weight:600; color:var(--text-main);">Click or Drag &amp; Drop Image Here</p>
                <span style="font-size:11.5px; color:var(--text-light); margin-top:4px; display:block;">Supports JPG, PNG, WEBP (Max 5MB)</span>
                <input type="file" id="wordImageFileInput" accept="image/*" style="display:none;">
            </div>
            <div id="imageUploadStatus" style="font-size:12px; color:var(--gold); text-align:center; display:none; margin-bottom:12px;"></div>
        </div>

        <div class="modal-tab-pane" id="mediaTab" style="display:none;">
            <div style="margin-bottom:10px;">
                <input type="text" id="mediaSearchInput" placeholder="Search media by filename..." oninput="filterMediaLibrary(this.value)" style="font-size:12.5px; padding:8px 12px; width:100%; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-app); color:var(--text-main);">
            </div>
            <div class="modal-media-grid" id="modalMediaGrid">
                <div style="padding:30px; text-align:center; color:var(--text-light); font-size:13px; grid-column:1/-1;">Loading media items...</div>
            </div>
        </div>

        <div class="modal-tab-pane" id="urlTab" style="display:none;">
            <div class="form-group" style="margin-bottom:12px;">
                <label class="static-label" for="wordImageUrlInput">Direct Image URL</label>
                <input type="url" id="wordImageUrlInput" placeholder="https://example.com/image.jpg" style="font-size:13px;" oninput="updateSelectedPreview(this.value, 'Direct URL Image')">
            </div>
        </div>

        <div class="image-options-panel" style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border-color);">
            <div id="selectedImagePreviewBar" style="display:none; align-items:center; gap:12px; margin-bottom:14px; padding:8px 12px; background:var(--bg-app); border-radius:8px; border:1px solid var(--border-color);">
                <img id="modalSelectedImgThumb" src="" style="width:48px; height:48px; object-fit:cover; border-radius:6px;">
                <div style="flex:1; overflow:hidden;">
                    <div id="modalSelectedImgName" style="font-size:13px; font-weight:600; white-space:nowrap; text-overflow:ellipsis; overflow:hidden; color:var(--text-main);">Selected Image</div>
                    <div id="modalSelectedImgUrl" style="font-size:11px; color:var(--text-light); font-family:monospace; white-space:nowrap; text-overflow:ellipsis; overflow:hidden;"></div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-bottom:12px;">
                <div class="form-group">
                    <label class="static-label" for="wordImageAlignSelect">Alignment</label>
                    <select id="wordImageAlignSelect" style="font-size:13px; width:100%;">
                        <option value="center">Center Block</option>
                        <option value="left">Float Left</option>
                        <option value="right">Float Right</option>
                        <option value="wide">Wide Showcase</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="static-label" for="wordImageWidthSelect">Width</label>
                    <select id="wordImageWidthSelect" style="font-size:13px; width:100%;">
                        <option value="100%">100% (Full Width)</option>
                        <option value="75%">75% (Medium Large)</option>
                        <option value="50%">50% (Half Width)</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:12px;">
                <label class="static-label" for="wordImageCaptionInput">Caption (Displayed under image)</label>
                <input type="text" id="wordImageCaptionInput" placeholder="e.g. Fig 1. Seized pilot mass in melanger drum" style="font-size:13px;">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('wordEditorImageModal').style.display='none'">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="submitWordImageUrlBtn">Insert Image into Case Study</button>
            </div>
        </div>
    </div>
</div>

<!-- Word Editor Table Insert Modal -->
<div class="word-modal-overlay" id="wordEditorTableModal">
    <div class="word-modal-card">
        <div class="word-modal-header">
            <h3>Insert Forensic Table</h3>
            <button type="button" class="word-modal-close" onclick="document.getElementById('wordEditorTableModal').style.display='none'">&times;</button>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="form-group">
                <label class="static-label" for="tableRowsInput">Rows</label>
                <input type="number" id="tableRowsInput" value="3" min="1" max="50">
            </div>
            <div class="form-group">
                <label class="static-label" for="tableColsInput">Columns</label>
                <input type="number" id="tableColsInput" value="3" min="1" max="10">
            </div>
        </div>
        <div class="form-group" style="margin-bottom:20px;">
            <label class="static-label" for="tableStyleSelect">Table Visual Style</label>
            <select id="tableStyleSelect">
                <option value="artisan">Artisan (Cocoa Header, Gold Border)</option>
                <option value="minimal">Minimal (Clean Gold Line)</option>
                <option value="striped">Striped (Clean Alternating)</option>
            </select>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('wordEditorTableModal').style.display='none'">Cancel</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="confirmInsertTable()">Insert Table</button>
        </div>
    </div>
</div>

<!-- Immersive Full Preview Overlay -->
<div class="full-preview-overlay" id="fullPreviewOverlay">
    <button type="button" class="btn btn-outline full-preview-close" onclick="closeFullPreview()">Close Preview</button>
    <div class="full-preview-container">
        <h1 id="previewFullTitle" style="font-family:'Playfair Display', serif; font-size:42px; font-weight:700; color:var(--text-main); text-align:center; margin-bottom:12px;"></h1>
        <div id="previewFullBadge" style="text-align:center; font-size:12px; font-weight:700; text-transform:uppercase; color:#b45309; letter-spacing:2px; margin-bottom:32px;"></div>
        <div id="previewFullHeaderImg" style="border-radius:12px; overflow:hidden; border:1px solid var(--border-color); height:380px; margin-bottom:40px; background:var(--cream); display:none;">
            <img src="" style="width:100%; height:100%; object-fit:cover;">
        </div>
        <div class="blog-article-section" id="previewFullContent"></div>
    </div>
</div>

<script src="word-editor.js?v=<?php echo file_exists(__DIR__ . '/word-editor.js') ? filemtime(__DIR__ . '/word-editor.js') : '1'; ?>"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Word Document Editor Engine with full_content
    const initialContent = <?php echo json_encode($fail['full_content']); ?>;
    initWordEditor(initialContent);

    // 2. Settings Drawer Toggle
    const drawer = document.getElementById('settingsDrawer');
    const toggleBtn = document.getElementById('drawerToggle');
    const closeBtn = document.getElementById('closeDrawerBtn');
    
    if (toggleBtn && drawer) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            drawer.classList.toggle('collapsed');
        });
    }
    
    if (closeBtn && drawer) {
        closeBtn.addEventListener('click', function() {
            drawer.classList.add('collapsed');
        });
    }

    // 3. Auto-generate Slug from Title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    if (titleInput && slugInput && !<?php echo $isEdit ? 'true' : 'false'; ?>) {
        titleInput.addEventListener('input', function() {
            const clean = titleInput.value
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
            slugInput.value = clean;
            syncSlugPreview(clean);
        });
    }

    // 4. Ctrl+S / Cmd+S save shortcut
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            submitFailEditorForm();
        }
    });

    // 5. Ensure form submission always serializes clean HTML
    const failForm = document.getElementById('failEditorForm');
    if (failForm) {
        failForm.addEventListener('submit', function() {
            if (window.syncWordEditorContent) {
                window.syncWordEditorContent();
            }
        });
    }
});

function syncCanvasTitle(val) {
    document.getElementById('title').value = val;
    const seoTitle = document.getElementById('seoPreviewTitle');
    if (seoTitle) seoTitle.innerText = (val || 'Failed Batch Case Study') + ' | RT Chocos';
    if (!<?php echo $isEdit ? 'true' : 'false'; ?>) {
        const clean = val.toLowerCase().replace(/[^a-z0-9\s-]/g, '').trim().replace(/\s+/g, '-');
        document.getElementById('slug').value = clean;
        syncSlugPreview(clean);
    }
}

function syncTitleDrawer(val) {
    document.getElementById('canvasTitleInput').value = val;
    const seoTitle = document.getElementById('seoPreviewTitle');
    if (seoTitle) seoTitle.innerText = (val || 'Failed Batch Case Study') + ' | RT Chocos';
}

function syncSlugPreview(slug) {
    const preview = document.getElementById('permalinkSlugPreview');
    const seoSlug = document.getElementById('seoSlugText');
    if (preview) preview.innerText = slug || 'batch-slug';
    if (seoSlug) seoSlug.innerText = slug || 'batch-slug';
}

function syncSeoSnippet(val) {
    const seoExcerpt = document.getElementById('seoPreviewExcerpt');
    if (seoExcerpt) seoExcerpt.innerText = val || 'A transparent behind-the-scenes laboratory case study on what went wrong and how we fixed it.';
}

function syncDrawerVisibility(checked) {
    document.getElementById('isActiveHiddenInput').value = checked ? '1' : '0';
    document.getElementById('topbarVisibilityToggle').checked = checked;
    updateVisibilityBadges(checked);
}

function updateVisibilityBadges(isActive) {
    const badge = document.getElementById('topbarVisibilityBadge');
    const icon = document.getElementById('topbarVisibilityIcon');
    const label = document.getElementById('topbarVisibilityLabel');
    const hint = document.getElementById('drawerVisibilityHint');
    if (isActive) {
        badge.className = 'status-badge published';
        badge.style.background = '';
        badge.style.color = '';
        badge.style.borderColor = '';
        icon.innerText = '👁️';
        label.innerText = 'Visible (Public)';
        if (hint) hint.innerText = 'Visible to public on website';
    } else {
        badge.className = 'status-badge draft';
        badge.style.background = 'rgba(217, 119, 6, 0.15)';
        badge.style.color = '#d97706';
        badge.style.borderColor = 'rgba(217, 119, 6, 0.3)';
        icon.innerText = '🔒';
        label.innerText = 'Hidden (Draft)';
        if (hint) hint.innerText = 'Hidden from public (Draft)';
    }
}

function toggleFailedBatchActive(failId, checked) {
    document.getElementById('drawerVisibilityToggle').checked = checked;
    document.getElementById('isActiveHiddenInput').value = checked ? '1' : '0';
    updateVisibilityBadges(checked);

    if (failId > 0) {
        const fd = new FormData();
        fd.append('csrf_token', '<?php echo $csrfToken; ?>');
        fd.append('action', 'toggle_active');
        fd.append('failed_id', failId);
        fd.append('is_active', checked ? 1 : 0);
        fetch('failed-batch-editor.php', { method: 'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (!d.success) alert(d.message || 'Error updating status');
            })
            .catch(() => alert('Network error while toggling status'));
    }
}

function submitFailEditorForm(actionType) {
    if (window.syncWordEditorContent) {
        window.syncWordEditorContent();
    } else {
        const editorDoc = document.getElementById('wordEditorDoc');
        const hiddenContent = document.getElementById('content');
        if (editorDoc && hiddenContent) {
            hiddenContent.value = window.cleanHtmlForSave ? window.cleanHtmlForSave(editorDoc.innerHTML) : editorDoc.innerHTML;
        }
    }
    const form = document.getElementById('failEditorForm');
    if (form) {
        if (actionType === 'exit') {
            let exitInput = document.getElementById('saveAndExitInput');
            if (!exitInput) {
                exitInput = document.createElement('input');
                exitInput.type = 'hidden';
                exitInput.name = 'save_and_exit';
                exitInput.id = 'saveAndExitInput';
                exitInput.value = '1';
                form.appendChild(exitInput);
            }
        }
        form.submit();
    }
}

function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0] && preview) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            preview.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Immersive Preview Modal Handlers
function openFullPreview() {
    const overlay = document.getElementById('fullPreviewOverlay');
    const title = document.getElementById('canvasTitleInput').value || 'Failed Batch Case Study';
    const badge = document.getElementById('badge_label').value || 'BECAUSE FAILURE IS DATA.';
    const editorDoc = document.getElementById('wordEditorDoc');
    
    document.getElementById('previewFullTitle').innerText = title;
    document.getElementById('previewFullBadge').innerText = badge;
    document.getElementById('previewFullContent').innerHTML = editorDoc ? editorDoc.innerHTML : '';
    
    const previewImg = document.querySelector('#header-preview img');
    const headerWrap = document.getElementById('previewFullHeaderImg');
    if (previewImg && previewImg.src && previewImg.src !== window.location.href) {
        headerWrap.style.display = 'block';
        headerWrap.querySelector('img').src = previewImg.src;
    } else {
        headerWrap.style.display = 'none';
    }
    
    overlay.style.display = 'block';
}

function closeFullPreview() {
    document.getElementById('fullPreviewOverlay').style.display = 'none';
}
</script>

<?php
render_admin_footer();
?>
