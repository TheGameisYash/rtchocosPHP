<?php
// admin/experiment-editor.php - Dedicated Full Editor for RT Chocos Innovation Lab Experiments
require_once __DIR__ . '/layout.php';

$pdo = get_db();
$error = '';
$success = '';

// 1. AJAX INSTANT VISIBILITY TOGGLE (Instant Active / Draft)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_active') {
    header('Content-Type: application/json');
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf($token)) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }
    $expId = (int)($_POST['experiment_id'] ?? 0);
    $targetState = isset($_POST['is_active']) ? ((int)$_POST['is_active'] === 1 ? 1 : 0) : null;
    if ($expId > 0) {
        if ($targetState !== null) {
            $stmt = $pdo->prepare("UPDATE lab_experiments SET is_active = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$targetState, $expId]);
        } else {
            $stmt = $pdo->prepare("UPDATE lab_experiments SET is_active = 1 - is_active, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$expId]);
        }
        $stmt = $pdo->prepare("SELECT id, slug, title, is_active FROM lab_experiments WHERE id = ?");
        $stmt->execute([$expId]);
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        $isActive = $updated ? (int)$updated['is_active'] : 0;
        echo json_encode([
            'success' => true,
            'experiment_id' => $expId,
            'is_active' => $isActive,
            'status_label' => $isActive ? 'Visible (Active)' : 'Hidden (Draft)',
            'message' => 'Experiment "' . ($updated['title'] ?? 'Trial') . '" is now ' . ($isActive ? 'Visible to public visitors' : 'Hidden from public visitors (Draft)') . '.'
        ]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Experiment not saved in database yet. Save the experiment first to toggle visibility.']);
    exit;
}

$expId = (int)($_GET['id'] ?? 0);
$isEdit = $expId > 0;

// Default empty values for new experiment
$exp = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'category' => 'Formulation Lab',
    'status_badge' => 'IN PROGRESS',
    'status_color' => 'green',
    'description' => '',
    'tags' => 'FAT SYSTEMS | FORMULATION',
    'image_path' => 'assets/images/placeholder.jpg',
    'batch_ref' => 'RT-EXP-' . date('ym') . '-01',
    'hypothesis' => '',
    'findings' => '',
    'methodology' => '',
    'next_steps' => '',
    'full_content' => '',
    'origin' => 'Idukki Single Estate, Kerala',
    'polymorph' => 'Beta-V Matrix',
    'particle_size' => '< 18.2 µm',
    'water_activity' => '0.31 Aw',
    'conching' => '72 hrs longitudinal conching',
    'sort_order' => 0,
    'is_active' => 1
];

// If Edit, fetch from database
if ($isEdit) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM lab_experiments WHERE id = ?");
        $stmt->execute([$expId]);
        $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$fetched) {
            header('Location: innovation-lab.php?tab=experiments&error=' . urlencode('Experiment not found.'));
            exit;
        }
        $exp = array_merge($exp, $fetched);
    } catch (Exception $e) {
        $error = 'Error fetching experiment: ' . $e->getMessage();
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_experiment') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $category = trim($_POST['category'] ?? 'Formulation Lab');
    $statusBadge = trim($_POST['status_badge'] ?? 'IN PROGRESS');
    $statusColor = trim($_POST['status_color'] ?? 'green');
    $description = trim($_POST['description'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $batchRef = trim($_POST['batch_ref'] ?? '');
    $hypothesis = trim($_POST['hypothesis'] ?? '');
    $findings = trim($_POST['findings'] ?? '');
    $methodology = trim($_POST['methodology'] ?? '');
    $nextSteps = trim($_POST['next_steps'] ?? '');
    $fullContent = trim($_POST['full_content'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $polymorph = trim($_POST['polymorph'] ?? '');
    $particleSize = trim($_POST['particle_size'] ?? '');
    $waterActivity = trim($_POST['water_activity'] ?? '');
    $conching = trim($_POST['conching'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);
    $isActive = (!empty($_POST['is_active']) && $_POST['is_active'] !== '0') ? 1 : 0;
    $existingImg = trim($_POST['existing_image_path'] ?? $exp['image_path']);

    // Retain form input on validation error
    $exp['title'] = $title;
    $exp['slug'] = $slug;
    $exp['category'] = $category;
    $exp['status_badge'] = $statusBadge;
    $exp['status_color'] = $statusColor;
    $exp['description'] = $description;
    $exp['tags'] = $tags;
    $exp['batch_ref'] = $batchRef;
    $exp['hypothesis'] = $hypothesis;
    $exp['findings'] = $findings;
    $exp['methodology'] = $methodology;
    $exp['next_steps'] = $nextSteps;
    $exp['full_content'] = $fullContent;
    $exp['origin'] = $origin;
    $exp['polymorph'] = $polymorph;
    $exp['particle_size'] = $particleSize;
    $exp['water_activity'] = $waterActivity;
    $exp['conching'] = $conching;
    $exp['sort_order'] = $sortOrder;
    $exp['is_active'] = $isActive;

    if (!verify_csrf($csrfToken)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } elseif (empty($title)) {
        $error = 'Experiment title is required.';
    } else {
        // Auto-generate or sanitize slug
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        } else {
            $slug = preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', strtolower($slug)));
        }
        $exp['slug'] = $slug;

        try {
            // Check for slug uniqueness
            $slugCheckSql = $isEdit ? "SELECT id FROM lab_experiments WHERE slug = ? AND id != ?" : "SELECT id FROM lab_experiments WHERE slug = ?";
            $slugCheckParams = $isEdit ? [$slug, $expId] : [$slug];
            $stmt = $pdo->prepare($slugCheckSql);
            $stmt->execute($slugCheckParams);
            if ($stmt->fetch()) {
                $error = "The URL slug '{$slug}' is already in use by another experiment. URL slugs must be unique.";
            } else {
                $imagePath = $existingImg;

                // Handle file upload
                if (isset($_FILES['experiment_image']) && $_FILES['experiment_image']['error'] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['experiment_image']['tmp_name'];
                    $fileName = $_FILES['experiment_image']['name'];
                    $fileSize = $_FILES['experiment_image']['size'];
                    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
                    $maxSize = 5 * 1024 * 1024; // 5MB

                    if (!in_array($fileExt, $allowedExts)) {
                        $error = 'Cover image must be a JPG, PNG, or WEBP file.';
                    } elseif ($fileSize > $maxSize) {
                        $error = 'Cover image must be less than 5MB.';
                    } else {
                        $destName = 'exp-' . $slug . '-' . time() . '.' . $fileExt;
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
                    if ($isEdit) {
                        $stmt = $pdo->prepare("UPDATE lab_experiments SET 
                            title = ?, slug = ?, category = ?, status_badge = ?, status_color = ?, 
                            image_path = ?, description = ?, tags = ?, hypothesis = ?, methodology = ?, 
                            findings = ?, next_steps = ?, full_content = ?, sort_order = ?, is_active = ?, 
                            origin = ?, polymorph = ?, particle_size = ?, conching = ?, water_activity = ?, batch_ref = ?, 
                            updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $slug, $category, $statusBadge, $statusColor,
                            $imagePath, $description, $tags, $hypothesis, $methodology,
                            $findings, $nextSteps, $fullContent, $sortOrder, $isActive,
                            $origin, $polymorph, $particleSize, $conching, $waterActivity, $batchRef,
                            $expId
                        ]);
                        $success = 'Experiment updated successfully!';
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_experiments 
                            (title, slug, category, status_badge, status_color, image_path, description, tags, 
                             hypothesis, methodology, findings, next_steps, full_content, sort_order, is_active, 
                             origin, polymorph, particle_size, conching, water_activity, batch_ref, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                        $stmt->execute([
                            $title, $slug, $category, $statusBadge, $statusColor,
                            $imagePath, $description, $tags,
                            $hypothesis, $methodology, $findings, $nextSteps, $fullContent,
                            $sortOrder, $isActive,
                            $origin, $polymorph, $particleSize, $conching, $waterActivity, $batchRef
                        ]);
                        $expId = (int)$pdo->lastInsertId();
                        $isEdit = true;
                        $success = 'New experiment created successfully!';
                    }

                    // Refresh $exp array
                    $stmt = $pdo->prepare("SELECT * FROM lab_experiments WHERE id = ?");
                    $stmt->execute([$expId]);
                    $exp = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Redirect back to list with success parameter or stay on editor
                    if (isset($_POST['save_and_exit'])) {
                        header('Location: innovation-lab.php?tab=experiments&success=' . urlencode($success));
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
render_admin_header($isEdit ? "Edit Experiment — " . htmlspecialchars($exp['title']) : "New Experiment", "innovation-lab");
?>

<link rel="stylesheet" href="editor-style.css?v=<?php echo file_exists(__DIR__ . '/editor-style.css') ? filemtime(__DIR__ . '/editor-style.css') : '1'; ?>">

<style>
/* Specific styling overrides for Experiment Editor */
.exp-editor-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 32px;
    align-items: start;
    width: 100%;
}
@media (max-width: 1024px) {
    .exp-editor-grid {
        grid-template-columns: 1fr;
    }
}
.exp-main-card {
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border-color, #E2D9CC);
    border-radius: 12px;
    padding: 36px 40px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    margin-bottom: 24px;
}
.exp-sidebar-card {
    background: var(--bg-card, #FFFFFF);
    border: 1px solid var(--border-color, #E2D9CC);
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    margin-bottom: 24px;
}
.exp-sidebar-title {
    font-size: 13.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--text-main, #122217);
    margin: 0 0 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border-color, #EBE4D8);
    display: flex;
    align-items: center;
    gap: 8px;
}
.exp-title-input {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 26px;
    font-weight: 700;
    width: 100%;
    border: 1.5px solid var(--border-color, #D8CFC1);
    border-radius: 8px;
    padding: 14px 18px;
    margin-bottom: 16px;
    background: var(--bg-input, #FFFFFF);
    color: var(--text-main, #122217);
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.exp-title-input:focus {
    outline: none;
    border-color: var(--gold, #C7A66A);
}
.exp-slug-bar {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--bg-subtle, #FAF7F2);
    border: 1px solid var(--border-color, #EBE4D8);
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    color: var(--text-muted, #6C7E72);
    margin-bottom: 28px;
    flex-wrap: wrap;
}
.exp-slug-input {
    border: 1px solid var(--border-color, #D8CFC1);
    background: #FFF;
    border-radius: 4px;
    padding: 4px 8px;
    font-family: monospace;
    font-size: 12.5px;
    color: var(--text-main, #122217);
    flex: 1;
    min-width: 180px;
}
.exp-discovery-2x2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 20px;
    margin-bottom: 28px;
}
@media (max-width: 768px) {
    .exp-discovery-2x2 {
        grid-template-columns: 1fr;
    }
}
.exp-quad-box {
    background: var(--bg-subtle, #FAF7F2);
    border: 1px solid var(--border-color, #E6DED2);
    border-radius: 10px;
    padding: 18px;
    display: flex;
    flex-direction: column;
}
.exp-quad-box-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.exp-quad-box-num {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    color: var(--gold, #C7A66A);
}
.exp-quad-box-title {
    font-size: 14px;
    font-weight: 700;
    color: var(--text-main, #122217);
}
.exp-quad-box-sub {
    font-size: 11.5px;
    color: var(--text-muted, #7A8B7E);
    margin-bottom: 8px;
    line-height: 1.4;
}
.exp-textarea {
    width: 100%;
    border: 1px solid var(--border-color, #D8CFC1);
    border-radius: 6px;
    padding: 10px 12px;
    font-size: 13.5px;
    line-height: 1.55;
    background: #FFFFFF;
    color: var(--text-main, #122217);
    box-sizing: border-box;
    font-family: inherit;
    resize: vertical;
    transition: border-color 0.2s;
}
.exp-textarea:focus {
    outline: none;
    border-color: var(--gold, #C7A66A);
}
.exp-img-preview-box {
    width: 100%;
    aspect-ratio: 16 / 10;
    background: #ECE4D7;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    border: 1px solid var(--border-color, #E2D9CC);
    margin-bottom: 12px;
}
.exp-img-preview-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.color-dot-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
    vertical-align: middle;
    margin-right: 6px;
}
.color-dot-indicator.green { background: #16A34A; }
.color-dot-indicator.amber { background: #D97706; }
.color-dot-indicator.purple { background: #9333EA; }
.color-dot-indicator.blue { background: #2563EB; }
</style>

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

<!-- Top Action Bar -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
        <a href="innovation-lab.php?tab=experiments" class="btn btn-outline" style="display:inline-flex; align-items:center; gap:6px;">
            <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width:14px;height:14px;"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Back to Innovation Lab
        </a>

        <!-- Live Instant Visibility Toggle -->
        <?php if ($isEdit): ?>
            <div style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:8px; background:var(--bg-card); border:1px solid var(--border-color);">
                <span style="font-size:12px; font-weight:700; text-transform:uppercase; color:var(--text-light); letter-spacing:0.5px;">Visibility:</span>
                <label class="custom-toggle" style="transform:scale(0.85); margin:0; cursor:pointer;">
                    <input type="checkbox" id="topbarVisibilityToggle" <?php echo $exp['is_active'] ? 'checked' : ''; ?> onchange="toggleExperimentVisibility(<?php echo $exp['id']; ?>, this.checked)">
                    <span class="toggle-slider"></span>
                </label>
                <span id="topbarVisibilityBadge" class="status-badge <?php echo $exp['is_active'] ? 'published' : 'draft'; ?>" style="font-size:11px; padding:3px 8px; <?php echo $exp['is_active'] ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                    <span id="topbarVisibilityIcon"><?php echo $exp['is_active'] ? '●' : '○'; ?></span>
                    <span id="topbarVisibilityLabel"><?php echo $exp['is_active'] ? 'Visible (Active)' : 'Hidden (Draft)'; ?></span>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <div style="display:flex; align-items:center; gap:10px;">
        <?php if ($isEdit && !empty($exp['slug'])): ?>
            <a href="../experiment/<?php echo htmlspecialchars($exp['slug']); ?>" target="_blank" class="btn btn-outline" style="display:inline-flex; align-items:center; gap:6px;">
                <span>View on Site</span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
            </a>
        <?php endif; ?>
        <button type="submit" form="expEditorForm" name="save_and_exit" value="1" class="btn btn-outline" style="font-weight:600;">
            Save &amp; Return
        </button>
        <button type="submit" form="expEditorForm" class="btn btn-primary" style="font-weight:700;">
            💾 Save Experiment
        </button>
    </div>
</div>

<!-- Main Two-Column Layout Form -->
<form id="expEditorForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
    <input type="hidden" name="action" value="save_experiment">
    <input type="hidden" name="experiment_id" value="<?php echo (int)$exp['id']; ?>">
    <input type="hidden" name="existing_image_path" id="existing_image_path" value="<?php echo htmlspecialchars($exp['image_path']); ?>">

    <div class="exp-editor-grid">
        
        <!-- Left Column: Core Dossier Content (70%) -->
        <main>
            <div class="exp-main-card">
                
                <!-- Experiment Title -->
                <label style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-light); display:block; margin-bottom:6px;">Experiment Title *</label>
                <input type="text" name="title" id="expTitleInput" class="exp-title-input" value="<?php echo htmlspecialchars($exp['title']); ?>" placeholder="e.g. CBE × Bean-to-Bar" required oninput="handleTitleInput(this.value)">

                <!-- URL Slug Bar -->
                <div class="exp-slug-bar">
                    <span style="font-weight:600; color:var(--text-main);">Permalink:</span>
                    <span>https://www.rtchocos.com/experiment/</span>
                    <input type="text" name="slug" id="expSlugInput" class="exp-slug-input" value="<?php echo htmlspecialchars($exp['slug']); ?>" placeholder="cbe-bean-to-bar" required>
                    <button type="button" class="btn btn-sm btn-outline" style="padding:2px 8px; font-size:11.5px;" onclick="regenerateSlug()">Reset Slug</button>
                </div>

                <!-- Excerpt / Kitchen Summary -->
                <div style="margin-bottom:28px;">
                    <label style="font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:var(--text-light); display:block; margin-bottom:6px;">
                        Kitchen Summary / Card Excerpt *
                    </label>
                    <textarea name="description" rows="3" class="exp-textarea" placeholder="A concise 1-2 sentence overview of what is being tested..." required><?php echo htmlspecialchars($exp['description']); ?></textarea>
                    <div style="font-size:11.5px; color:var(--text-muted); margin-top:4px;">Appears on the Innovation Lab grid card and the article hero summary.</div>
                </div>

                <!-- 4 Core Editorial Discovery Sections -->
                <div style="margin-top:36px; padding-top:28px; border-top:1.5px solid var(--border-color, #EBE4D8);">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-size:16px;">🔬</span>
                        <h3 style="font-family:'Playfair Display', Georgia, serif; font-size:20px; font-weight:700; color:var(--text-main); margin:0;">
                            4 Core Editorial Discovery Blocks
                        </h3>
                    </div>
                    <p style="font-size:13px; color:var(--text-muted); margin:0 0 16px;">
                        These 4 structured modules create the editorial dossier on the single experiment article page.
                    </p>

                    <div class="exp-discovery-2x2">
                        
                        <!-- 01. Research Question -->
                        <div class="exp-quad-box" style="border-top: 3px solid #16A34A;">
                            <div class="exp-quad-box-header">
                                <span class="exp-quad-box-num">01</span>
                                <span class="exp-quad-box-title">Research Question</span>
                            </div>
                            <div class="exp-quad-box-sub">What question are we trying to answer in this trial?</div>
                            <textarea name="hypothesis" rows="4" class="exp-textarea" placeholder="e.g. Non-lauric cocoa butter equivalents (CBE) blended at 5% will maintain Beta-V crystal stability..."><?php echo htmlspecialchars($exp['hypothesis']); ?></textarea>
                        </div>

                        <!-- 02. What We Discovered -->
                        <div class="exp-quad-box" style="border-top: 3px solid #D97706;">
                            <div class="exp-quad-box-header">
                                <span class="exp-quad-box-num">02</span>
                                <span class="exp-quad-box-title">What We Discovered</span>
                            </div>
                            <div class="exp-quad-box-sub">What happened when we made and tasted the chocolate?</div>
                            <textarea name="findings" rows="4" class="exp-textarea" placeholder="e.g. Temper curves showed sharp Beta-V peaks. Melting point increased by +1.8°C with zero waxiness..."><?php echo htmlspecialchars($exp['findings']); ?></textarea>
                        </div>

                        <!-- 03. Why It Matters -->
                        <div class="exp-quad-box" style="border-top: 3px solid #15803D;">
                            <div class="exp-quad-box-header">
                                <span class="exp-quad-box-num">03</span>
                                <span class="exp-quad-box-title">Why It Matters</span>
                            </div>
                            <div class="exp-quad-box-sub">How this helps make better, more delicious chocolate.</div>
                            <textarea name="methodology" rows="4" class="exp-textarea" placeholder="e.g. Formulated 70% single-origin Kerala cacao with 5% CBE fat replacement. Monitored temper curves..."><?php echo htmlspecialchars($exp['methodology']); ?></textarea>
                        </div>

                        <!-- 04. Key Takeaway -->
                        <div class="exp-quad-box" style="border-top: 3px solid #9A6A38;">
                            <div class="exp-quad-box-header">
                                <span class="exp-quad-box-num">04</span>
                                <span class="exp-quad-box-title">Key Takeaway</span>
                            </div>
                            <div class="exp-quad-box-sub">The main lesson and formula takeaway from this test batch.</div>
                            <textarea name="next_steps" rows="4" class="exp-textarea" placeholder="e.g. Conduct 60-day fat bloom cycling tests at 28°C-32°C before final confectionery approval..."><?php echo htmlspecialchars($exp['next_steps']); ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- Detailed Recipe Notes & In-Depth Kitchen Log -->
                <div style="margin-top:24px; padding-top:28px; border-top:1.5px solid var(--border-color, #EBE4D8);">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-size:16px;">📝</span>
                        <h3 style="font-family:'Playfair Display', Georgia, serif; font-size:20px; font-weight:700; color:var(--text-main); margin:0;">
                            Detailed Recipe Notes &amp; Tasting Log (Optional)
                        </h3>
                    </div>
                    <p style="font-size:13px; color:var(--text-muted); margin:0 0 16px;">
                        Extended long-form kitchen notes, formulas, batch observations, or tasting panel results. Supports markdown &amp; HTML.
                    </p>
                    <textarea name="full_content" rows="10" class="exp-textarea" style="font-family:monospace; font-size:13px;" placeholder="Write detailed batch notes, tempering protocol, or tasting feedback..."><?php echo htmlspecialchars($exp['full_content']); ?></textarea>
                </div>

            </div>
        </main>

        <!-- Right Column: Sidebar Settings (30%) -->
        <aside>
            
            <!-- 1. Save & Publish Card -->
            <div class="exp-sidebar-card">
                <div class="exp-sidebar-title">
                    <span>⚡</span>
                    <span>Status &amp; Visibility</span>
                </div>
                
                <div style="margin-bottom:18px;">
                    <label style="font-size:12px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:6px;">Public Visibility</label>
                    <select name="is_active" class="exp-textarea" style="height:40px; padding:6px 12px;">
                        <option value="1" <?php echo $exp['is_active'] ? 'selected' : ''; ?>>🟢 Public (Live on site)</option>
                        <option value="0" <?php echo !$exp['is_active'] ? 'selected' : ''; ?>>🟡 Draft (Hidden / Admin preview)</option>
                    </select>
                </div>

                <div style="margin-bottom:18px;">
                    <label style="font-size:12px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:6px;">Display Order</label>
                    <input type="number" name="sort_order" class="exp-textarea" style="height:40px; padding:6px 12px;" value="<?php echo (int)$exp['sort_order']; ?>">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Lower numbers appear first on the lab grid.</div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; font-weight:700; padding:10px 16px; margin-top:8px;">
                    Save Changes
                </button>
            </div>

            <!-- 2. Cover / Specimen Photo Card -->
            <div class="exp-sidebar-card">
                <div class="exp-sidebar-title">
                    <span>📷</span>
                    <span>Specimen Image</span>
                </div>

                <div class="exp-img-preview-box">
                    <img id="expPreviewImg" src="../<?php echo htmlspecialchars($exp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" alt="Cover Preview" onerror="this.src='../assets/images/placeholder.jpg';">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Upload New Photo</label>
                    <input type="file" name="experiment_image" accept="image/jpeg,image/png,image/webp" class="exp-textarea" style="padding:6px; font-size:12px;" onchange="previewSelectedImage(this)">
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Or Image Path</label>
                    <input type="text" class="exp-textarea" style="padding:8px 10px; font-size:12px;" value="<?php echo htmlspecialchars($exp['image_path']); ?>" oninput="updateImagePreviewPath(this.value)">
                </div>
            </div>

            <!-- 3. Experiment Classification & Badges -->
            <div class="exp-sidebar-card">
                <div class="exp-sidebar-title">
                    <span>🏷️</span>
                    <span>Classification &amp; Badges</span>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Category *</label>
                    <input type="text" name="category" list="expCategoryDatalist" class="exp-textarea" style="padding:8px 10px;" value="<?php echo htmlspecialchars($exp['category']); ?>" required placeholder="e.g. Formulation Lab">
                    <datalist id="expCategoryDatalist">
                        <option value="Fat Systems">
                        <option value="Ingredient Innovation">
                        <option value="Formulation Lab">
                        <option value="Inclusions">
                        <option value="Sugar Systems">
                        <option value="Emulsion">
                        <option value="Stability &amp; Shelf Life">
                        <option value="Process Exploration">
                        <option value="Sensory Insights">
                        <option value="Future of Chocolate">
                    </datalist>
                </div>

                <div style="display:grid; grid-template-columns:1.2fr 0.8fr; gap:10px; margin-bottom:14px;">
                    <div>
                        <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Status Badge</label>
                        <input type="text" name="status_badge" class="exp-textarea" style="padding:8px 10px;" value="<?php echo htmlspecialchars($exp['status_badge']); ?>" placeholder="IN PROGRESS">
                    </div>
                    <div>
                        <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Dot Color</label>
                        <select name="status_color" id="statusColorSelect" class="exp-textarea" style="height:38px; padding:6px;" onchange="updateDotIndicator(this.value)">
                            <option value="green" <?php echo $exp['status_color'] === 'green' ? 'selected' : ''; ?>>🟢 Green</option>
                            <option value="amber" <?php echo $exp['status_color'] === 'amber' ? 'selected' : ''; ?>>🟠 Amber</option>
                            <option value="purple" <?php echo $exp['status_color'] === 'purple' ? 'selected' : ''; ?>>🟣 Purple</option>
                            <option value="blue" <?php echo $exp['status_color'] === 'blue' ? 'selected' : ''; ?>>🔵 Blue</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Batch Code Reference</label>
                    <input type="text" name="batch_ref" class="exp-textarea" style="padding:8px 10px; font-family:monospace;" value="<?php echo htmlspecialchars($exp['batch_ref']); ?>" placeholder="RT-EXP-01-CBE-POLY">
                </div>

                <div>
                    <label style="font-size:11.5px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:4px;">Focus Tags</label>
                    <input type="text" name="tags" class="exp-textarea" style="padding:8px 10px;" value="<?php echo htmlspecialchars($exp['tags']); ?>" placeholder="FAT SYSTEMS | FORMULATION">
                    <div style="font-size:11px; color:var(--text-muted); margin-top:3px;">Separated by pipes, e.g. "FAT SYSTEMS | FORMULATION"</div>
                </div>
            </div>

            <!-- 4. Specimen Passport Telemetry Card -->
            <div class="exp-sidebar-card">
                <div class="exp-sidebar-title">
                    <span>📋</span>
                    <span>Specimen Passport</span>
                </div>
                <p style="font-size:12px; color:var(--text-muted); margin:0 0 14px;">
                    Technical chocolate metrics rendered in the article passport sidebar.
                </p>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:3px;">Terroir Origin</label>
                    <input type="text" name="origin" class="exp-textarea" style="padding:7px 10px; font-size:12.5px;" value="<?php echo htmlspecialchars($exp['origin']); ?>" placeholder="Idukki Single Estate, Kerala">
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:3px;">Melt &amp; Snap Polymorph</label>
                    <input type="text" name="polymorph" class="exp-textarea" style="padding:7px 10px; font-size:12.5px;" value="<?php echo htmlspecialchars($exp['polymorph']); ?>" placeholder="Beta-V Matrix">
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:3px;">Smoothness / Particle Size</label>
                    <input type="text" name="particle_size" class="exp-textarea" style="padding:7px 10px; font-size:12.5px;" value="<?php echo htmlspecialchars($exp['particle_size']); ?>" placeholder="< 18.2 µm">
                </div>

                <div style="margin-bottom:12px;">
                    <label style="font-size:11px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:3px;">Moisture Level / Water Activity</label>
                    <input type="text" name="water_activity" class="exp-textarea" style="padding:7px 10px; font-size:12.5px;" value="<?php echo htmlspecialchars($exp['water_activity']); ?>" placeholder="0.31 Aw">
                </div>

                <div>
                    <label style="font-size:11px; font-weight:700; color:var(--text-light); text-transform:uppercase; letter-spacing:0.06em; display:block; margin-bottom:3px;">Conching / Process Duration</label>
                    <input type="text" name="conching" class="exp-textarea" style="padding:7px 10px; font-size:12.5px;" value="<?php echo htmlspecialchars($exp['conching']); ?>" placeholder="72 hrs longitudinal">
                </div>
            </div>

        </aside>

    </div>
</form>

<script>
// Auto-slug generator on title input (only when creating new or slug is empty)
const isEditMode = <?php echo $isEdit ? 'true' : 'false'; ?>;
let userEditedSlug = isEditMode && document.getElementById('expSlugInput').value.trim() !== '';

function handleTitleInput(title) {
    if (!userEditedSlug) {
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('expSlugInput').value = slug;
    }
}

document.getElementById('expSlugInput').addEventListener('input', function() {
    userEditedSlug = this.value.trim() !== '';
});

function regenerateSlug() {
    const title = document.getElementById('expTitleInput').value;
    const slug = title.toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('expSlugInput').value = slug;
    userEditedSlug = false;
}

// Live Image Preview Handlers
function previewSelectedImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('expPreviewImg').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function updateImagePreviewPath(path) {
    if (path.trim()) {
        const full = path.startsWith('http') || path.startsWith('/') ? path : ('../' + path);
        document.getElementById('expPreviewImg').src = full;
        document.getElementById('existing_image_path').value = path.trim();
    }
}

// Instant AJAX Visibility Toggle
function toggleExperimentVisibility(expId, checked) {
    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrfToken; ?>');
    formData.append('action', 'toggle_active');
    formData.append('experiment_id', expId);
    formData.append('is_active', checked ? 1 : 0);

    fetch('experiment-editor.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('topbarVisibilityBadge');
            const icon = document.getElementById('topbarVisibilityIcon');
            const label = document.getElementById('topbarVisibilityLabel');
            if (data.is_active) {
                badge.className = 'status-badge published';
                badge.style.background = '';
                badge.style.color = '';
                badge.style.borderColor = '';
                icon.innerText = '●';
                label.innerText = 'Visible (Active)';
            } else {
                badge.className = 'status-badge draft';
                badge.style.background = 'rgba(217, 119, 6, 0.15)';
                badge.style.color = '#d97706';
                badge.style.borderColor = 'rgba(217, 119, 6, 0.3)';
                icon.innerText = '○';
                label.innerText = 'Hidden (Draft)';
            }
        } else {
            alert(data.message || 'Error updating status');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error while updating visibility');
    });
}
</script>

<?php
render_admin_footer();
?>
