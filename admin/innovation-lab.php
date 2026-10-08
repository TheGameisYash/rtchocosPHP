<?php
// admin/innovation-lab.php - Dynamic CMS Control Center for Innovation Lab
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/blog-uploads.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

$activeTab = $_GET['tab'] ?? 'experiments';

// Helper: Handle file upload if present
function handle_lab_image_upload($fileKey) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES[$fileKey]['tmp_name'];
        $name = $_FILES[$fileKey]['name'];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $destName = 'lab-' . time() . '-' . rand(100, 999) . '.' . $ext;
            $webDir = dirname(__DIR__) . '/assets/images';
            if (!file_exists($webDir)) @mkdir($webDir, 0755, true);
            $target = $webDir . '/' . $destName;
            if (move_uploaded_file($tmp, $target)) {
                return 'assets/images/' . $destName;
            }
        }
    }
    return null;
}

// =========================================================================
// POST REQUEST PROCESSING (CRUD ACTIONS)
// =========================================================================
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!verify_csrf($token)) {
        $error = 'Invalid security token. Please refresh and try again.';
    } else {
        try {
            // -------------------------------------------------------------
            // 1. EXPERIMENT ACTIONS
            // -------------------------------------------------------------
            if ($action === 'save_experiment') {
                $activeTab = 'experiments';
                $id = (int)($_POST['experiment_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $category = trim($_POST['category'] ?? 'formulation-lab');
                $statusBadge = trim($_POST['status_badge'] ?? 'IN PROGRESS');
                $statusColor = trim($_POST['status_color'] ?? 'green');
                $description = trim($_POST['description'] ?? '');
                $tags = trim($_POST['tags'] ?? '');
                $hypothesis = trim($_POST['hypothesis'] ?? '');
                $methodology = trim($_POST['methodology'] ?? '');
                $findings = trim($_POST['findings'] ?? '');
                $nextSteps = trim($_POST['next_steps'] ?? '');
                $fullContent = trim($_POST['full_content'] ?? '');
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                // Check for uploaded image or fallback to existing path
                $uploadedImg = handle_lab_image_upload('experiment_image');
                $imagePath = $uploadedImg ?: trim($_POST['existing_image_path'] ?? 'assets/images/placeholder.jpg');

                // Slug generation
                $slug = trim($_POST['slug'] ?? '');
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
                }

                if (empty($title)) {
                    $error = 'Experiment title cannot be empty.';
                } else {
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_experiments SET 
                            title = ?, slug = ?, category = ?, status_badge = ?, status_color = ?, 
                            image_path = ?, description = ?, tags = ?, hypothesis = ?, methodology = ?, 
                            findings = ?, next_steps = ?, full_content = ?, sort_order = ?, is_active = ?, 
                            updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $slug, $category, $statusBadge, $statusColor,
                            $imagePath, $description, $tags, $hypothesis, $methodology,
                            $findings, $nextSteps, $fullContent, $sortOrder, $isActive, $id
                        ]);
                        $success = "Experiment '{$title}' updated successfully!";
                    } else {
                        // Ensure unique slug
                        $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM lab_experiments WHERE slug = ?");
                        $checkSlug->execute([$slug]);
                        if ($checkSlug->fetchColumn() > 0) {
                            $slug .= '-' . rand(10, 99);
                        }

                        $stmt = $pdo->prepare("INSERT INTO lab_experiments 
                            (title, slug, category, status_badge, status_color, image_path, description, tags, hypothesis, methodology, findings, next_steps, full_content, sort_order, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title, $slug, $category, $statusBadge, $statusColor,
                            $imagePath, $description, $tags, $hypothesis, $methodology,
                            $findings, $nextSteps, $fullContent, $sortOrder, $isActive
                        ]);
                        $success = "New experiment '{$title}' created successfully!";
                    }
                }
            } elseif ($action === 'delete_experiment') {
                $activeTab = 'experiments';
                $id = (int)($_POST['experiment_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM lab_experiments WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Experiment removed successfully.";
                }
            } elseif ($action === 'toggle_experiment') {
                $activeTab = 'experiments';
                $id = (int)($_POST['experiment_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE lab_experiments SET is_active = 1 - is_active WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Experiment status toggled.";
                }
            }

            // -------------------------------------------------------------
            // 2. FAILED BATCH ACTIONS
            // -------------------------------------------------------------
            elseif ($action === 'save_failed_batch') {
                $activeTab = 'failed-batches';
                $id = (int)($_POST['failed_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $badgeLabel = trim($_POST['badge_label'] ?? 'BECAUSE FAILURE IS DATA.');
                $hypothesis = trim($_POST['hypothesis'] ?? '');
                $whatHappened = trim($_POST['what_happened'] ?? '');
                $whyItHappened = trim($_POST['why_it_happened'] ?? '');
                $whatNext = trim($_POST['what_next'] ?? '');
                $fullStory = trim($_POST['full_story'] ?? '');
                $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $uploadedImg = handle_lab_image_upload('failed_image');
                $imagePath = $uploadedImg ?: trim($_POST['existing_image_path'] ?? 'assets/images/lab-failed-batch.jpg');

                $slug = trim($_POST['slug'] ?? '');
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
                }

                if (empty($title) || empty($hypothesis) || empty($whatHappened)) {
                    $error = 'Experiment title, hypothesis, and failure details are required.';
                } else {
                    if ($isFeatured === 1) {
                        $pdo->exec("UPDATE lab_failed_batches SET is_featured = 0");
                    }

                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_failed_batches SET 
                            title = ?, slug = ?, badge_label = ?, image_path = ?, hypothesis = ?, 
                            what_happened = ?, why_it_happened = ?, what_next = ?, full_story = ?, 
                            is_featured = ?, sort_order = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([
                            $title, $slug, $badgeLabel, $imagePath, $hypothesis,
                            $whatHappened, $whyItHappened, $whatNext, $fullStory,
                            $isFeatured, $sortOrder, $isActive, $id
                        ]);
                        $success = "Failed batch case study '{$title}' updated successfully!";
                    } else {
                        $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM lab_failed_batches WHERE slug = ?");
                        $checkSlug->execute([$slug]);
                        if ($checkSlug->fetchColumn() > 0) {
                            $slug .= '-' . rand(10, 99);
                        }

                        $stmt = $pdo->prepare("INSERT INTO lab_failed_batches 
                            (title, slug, badge_label, image_path, hypothesis, what_happened, why_it_happened, what_next, full_story, is_featured, sort_order, is_active) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $title, $slug, $badgeLabel, $imagePath, $hypothesis,
                            $whatHappened, $whyItHappened, $whatNext, $fullStory,
                            $isFeatured, $sortOrder, $isActive
                        ]);
                        $success = "New failed batch case study '{$title}' logged successfully!";
                    }
                }
            } elseif ($action === 'delete_failed_batch') {
                $activeTab = 'failed-batches';
                $id = (int)($_POST['failed_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM lab_failed_batches WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Failed batch case study deleted.";
                }
            } elseif ($action === 'set_featured_failed_batch') {
                $activeTab = 'failed-batches';
                $id = (int)($_POST['failed_id'] ?? 0);
                if ($id > 0) {
                    $pdo->exec("UPDATE lab_failed_batches SET is_featured = 0");
                    $stmt = $pdo->prepare("UPDATE lab_failed_batches SET is_featured = 1 WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Featured failed batch updated!";
                }
            }

            // -------------------------------------------------------------
            // 3. WHAT-IF QUESTIONS ACTIONS
            // -------------------------------------------------------------
            elseif ($action === 'save_what_if') {
                $activeTab = 'what-if';
                $id = (int)($_POST['whatif_id'] ?? 0);
                $numLabel = trim($_POST['num_label'] ?? '01');
                $question = trim($_POST['question'] ?? '');
                $linkUrl = trim($_POST['link_url'] ?? '#currently-in-lab');
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                if (empty($question)) {
                    $error = 'Question cannot be empty.';
                } else {
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_what_if_questions SET num_label = ?, question = ?, link_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$numLabel, $question, $linkUrl, $sortOrder, $isActive, $id]);
                        $success = "What-If question updated!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_what_if_questions (num_label, question, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$numLabel, $question, $linkUrl, $sortOrder, $isActive]);
                        $success = "New What-If question added!";
                    }
                }
            } elseif ($action === 'delete_what_if') {
                $activeTab = 'what-if';
                $id = (int)($_POST['whatif_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM lab_what_if_questions WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Question removed.";
                }
            }

            // -------------------------------------------------------------
            // 4. INGREDIENT ACTIONS
            // -------------------------------------------------------------
            elseif ($action === 'save_ingredient') {
                $activeTab = 'ingredients';
                $id = (int)($_POST['ing_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $linkUrl = trim($_POST['link_url'] ?? '');
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $uploadedImg = handle_lab_image_upload('ingredient_image');
                $imagePath = $uploadedImg ?: trim($_POST['existing_image_path'] ?? 'assets/images/placeholder.jpg');

                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

                if (empty($title)) {
                    $error = 'Ingredient title cannot be empty.';
                } else {
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_ingredient_categories SET title = ?, slug = ?, image_path = ?, description = ?, link_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$title, $slug, $imagePath, $description, $linkUrl, $sortOrder, $isActive, $id]);
                        $success = "Ingredient category '{$title}' updated!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_ingredient_categories (title, slug, image_path, description, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $slug, $imagePath, $description, $linkUrl, $sortOrder, $isActive]);
                        $success = "New ingredient category '{$title}' added!";
                    }
                }
            } elseif ($action === 'delete_ingredient') {
                $activeTab = 'ingredients';
                $id = (int)($_POST['ing_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM lab_ingredient_categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Ingredient category deleted.";
                }
            }

            // -------------------------------------------------------------
            // 5. FUTURE PILLARS ACTIONS
            // -------------------------------------------------------------
            elseif ($action === 'save_pillar') {
                $activeTab = 'future';
                $id = (int)($_POST['pillar_id'] ?? 0);
                $title = trim($_POST['title'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $linkUrl = trim($_POST['link_url'] ?? '#future-ideas');
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                if (empty($title)) {
                    $error = 'Pillar title cannot be empty.';
                } else {
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_future_pillars SET title = ?, description = ?, link_url = ?, sort_order = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$title, $description, $linkUrl, $sortOrder, $isActive, $id]);
                        $success = "Future pillar updated!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_future_pillars (title, description, link_url, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $description, $linkUrl, $sortOrder, $isActive]);
                        $success = "Future pillar added!";
                    }
                }
            } elseif ($action === 'delete_pillar') {
                $activeTab = 'future';
                $id = (int)($_POST['pillar_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("DELETE FROM lab_future_pillars WHERE id = ?");
                    $stmt->execute([$id]);
                    $success = "Future pillar removed.";
                }
            }

            // -------------------------------------------------------------
            // 6. GENERAL LAB SETTINGS ACTIONS
            // -------------------------------------------------------------
            elseif ($action === 'save_settings') {
                $activeTab = 'future';
                
                // Handle image uploads if present
                $heroImg = handle_lab_image_upload('hero_image_file');
                $whatIfImg = handle_lab_image_upload('what_if_image_file');
                $futureImg = handle_lab_image_upload('future_image_file');

                $settingsToSave = [
                    'lab_hero_eyebrow' => trim($_POST['lab_hero_eyebrow'] ?? ''),
                    'lab_hero_title' => trim($_POST['lab_hero_title'] ?? ''),
                    'lab_hero_subtitle' => trim($_POST['lab_hero_subtitle'] ?? ''),
                    'lab_hero_cta_text' => trim($_POST['lab_hero_cta_text'] ?? ''),
                    'lab_hero_cta_link' => trim($_POST['lab_hero_cta_link'] ?? ''),
                    'lab_sticky_note' => trim($_POST['lab_sticky_note'] ?? ''),
                    'lab_what_if_eyebrow' => trim($_POST['lab_what_if_eyebrow'] ?? ''),
                    'lab_what_if_heading' => trim($_POST['lab_what_if_heading'] ?? ''),
                    'lab_failed_batch_eyebrow' => trim($_POST['lab_failed_batch_eyebrow'] ?? ''),
                    'lab_failed_batch_heading' => trim($_POST['lab_failed_batch_heading'] ?? ''),
                    'lab_ingredient_eyebrow' => trim($_POST['lab_ingredient_eyebrow'] ?? ''),
                    'lab_ingredient_heading' => trim($_POST['lab_ingredient_heading'] ?? ''),
                    'lab_future_eyebrow' => trim($_POST['lab_future_eyebrow'] ?? ''),
                    'lab_future_heading' => trim($_POST['lab_future_heading'] ?? ''),
                    'lab_future_text' => trim($_POST['lab_future_text'] ?? ''),
                    'lab_future_cta_text' => trim($_POST['lab_future_cta_text'] ?? ''),
                    'lab_future_cta_link' => trim($_POST['lab_future_cta_link'] ?? ''),
                ];

                if ($heroImg) {
                    $settingsToSave['lab_hero_image'] = $heroImg;
                } elseif (!empty($_POST['lab_hero_image'])) {
                    $settingsToSave['lab_hero_image'] = trim($_POST['lab_hero_image']);
                }

                if ($whatIfImg) {
                    $settingsToSave['lab_what_if_image'] = $whatIfImg;
                } elseif (!empty($_POST['lab_what_if_image'])) {
                    $settingsToSave['lab_what_if_image'] = trim($_POST['lab_what_if_image']);
                }

                if ($futureImg) {
                    $settingsToSave['lab_future_image'] = $futureImg;
                } elseif (!empty($_POST['lab_future_image'])) {
                    $settingsToSave['lab_future_image'] = trim($_POST['lab_future_image']);
                }

                $setStmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
                foreach ($settingsToSave as $k => $v) {
                    $setStmt->execute([$k, $v]);
                }

                clear_site_settings_cache();
                $success = "Innovation Lab settings saved successfully!";
            }

        } catch (Exception $e) {
            $error = 'Error performing action: ' . $e->getMessage();
        }
    }
}

// =========================================================================
// FETCH DATA FOR DISPLAY
// =========================================================================
$experiments = $pdo->query("SELECT * FROM lab_experiments ORDER BY sort_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$failedBatches = $pdo->query("SELECT * FROM lab_failed_batches ORDER BY is_featured DESC, sort_order ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
$whatIfList = $pdo->query("SELECT * FROM lab_what_if_questions ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$ingredientsList = $pdo->query("SELECT * FROM lab_ingredient_categories ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$pillarsList = $pdo->query("SELECT * FROM lab_future_pillars ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Settings
$settings = [
    'lab_hero_eyebrow' => get_site_setting('lab_hero_eyebrow', 'IDEAS × EXPERIMENTS × EVIDENCE'),
    'lab_hero_title' => get_site_setting('lab_hero_title', 'Innovation Lab'),
    'lab_hero_subtitle' => get_site_setting('lab_hero_subtitle', 'A working space where curious minds come together to explore new ingredients, techniques and ideas to shape a more thoughtful chocolate future.'),
    'lab_hero_cta_text' => get_site_setting('lab_hero_cta_text', 'STEP INTO THE LAB →'),
    'lab_hero_cta_link' => get_site_setting('lab_hero_cta_link', '#currently-in-lab'),
    'lab_sticky_note' => get_site_setting('lab_sticky_note', "Better Ingredients\nBigger Questions\nBrighter Chocolate"),
    'lab_hero_image' => get_site_setting('lab_hero_image', 'assets/images/lab-hero.jpg'),
    'lab_what_if_eyebrow' => get_site_setting('lab_what_if_eyebrow', 'IDEAS WE ARE EXPLORING NEXT.'),
    'lab_what_if_heading' => get_site_setting('lab_what_if_heading', 'What if?'),
    'lab_what_if_image' => get_site_setting('lab_what_if_image', 'assets/images/lab-what-if.jpg'),
    'lab_failed_batch_eyebrow' => get_site_setting('lab_failed_batch_eyebrow', 'BECAUSE FAILURE IS DATA.'),
    'lab_failed_batch_heading' => get_site_setting('lab_failed_batch_heading', 'From a Failed Batch to a New Insight'),
    'lab_ingredient_eyebrow' => get_site_setting('lab_ingredient_eyebrow', 'NEW INGREDIENTS. NEW BEHAVIOURS. NEW POSSIBILITIES.'),
    'lab_ingredient_heading' => get_site_setting('lab_ingredient_heading', 'Ingredient Innovation'),
    'lab_future_eyebrow' => get_site_setting('lab_future_eyebrow', 'BOLDER QUESTIONS. BIGGER IMPACT.'),
    'lab_future_heading' => get_site_setting('lab_future_heading', 'Future of Chocolate'),
    'lab_future_text' => get_site_setting('lab_future_text', 'Exploring ideas and technologies that can make chocolate more inclusive, resilient and regenerative — for people, communities and the planet.'),
    'lab_future_cta_text' => get_site_setting('lab_future_cta_text', 'EXPLORE FUTURE IDEAS →'),
    'lab_future_cta_link' => get_site_setting('lab_future_cta_link', '#future-ideas'),
    'lab_future_image' => get_site_setting('lab_future_image', 'assets/images/lab-future.jpg'),
];

render_admin_header('Innovation Lab', 'innovation-lab');
?>

<div class="content-body" style="padding: 28px 36px;">

    <!-- Top Action Bar & Page Title -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-family: var(--font-heading); font-size: 32px; font-weight: 700; color: var(--text-main); margin: 0 0 6px;">
                Innovation Lab Management
            </h1>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Full dynamic control over experiments, failed batches, exploratory questions, and R&D pillars.
            </p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <a href="../innovation-lab.php" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                <span>View Live Lab Page</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($success)): ?>
        <div style="background: #EBF8F2; border-left: 4px solid #10B981; color: #065F46; padding: 14px 20px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
            ✓ <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div style="background: #FEF2F2; border-left: 4px solid #EF4444; color: #991B1B; padding: 14px 20px; border-radius: 6px; margin-bottom: 24px; font-weight: 500;">
            ✕ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px;">
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Total Experiments</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($experiments); ?></div>
            <div style="font-size: 12px; color: var(--status-active); font-weight: 600; margin-top: 4px;">Active R&D in Lab</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Failed Batches Logged</div>
            <div style="font-size: 32px; font-weight: 700; color: #B45309; font-family: var(--font-heading);"><?php echo count($failedBatches); ?></div>
            <div style="font-size: 12px; color: #B45309; font-weight: 600; margin-top: 4px;">Failures Converted to Data</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">What If? Inquiries</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($whatIfList); ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Future Curiosities</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card);">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Ingredient Categories</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($ingredientsList); ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Frontiers of Taste</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); margin-bottom: 28px; overflow-x: auto; padding-bottom: 2px;">
        <a href="?tab=experiments" class="btn <?php echo $activeTab === 'experiments' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🧪 Lab Experiments (<?php echo count($experiments); ?>)
        </a>
        <a href="?tab=failed-batches" class="btn <?php echo $activeTab === 'failed-batches' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            💥 Failed Batches (<?php echo count($failedBatches); ?>)
        </a>
        <a href="?tab=what-if" class="btn <?php echo $activeTab === 'what-if' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            💡 "What If?" Questions (<?php echo count($whatIfList); ?>)
        </a>
        <a href="?tab=ingredients" class="btn <?php echo $activeTab === 'ingredients' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🌱 Ingredient Categories (<?php echo count($ingredientsList); ?>)
        </a>
        <a href="?tab=future" class="btn <?php echo $activeTab === 'future' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🌍 Future of Chocolate &amp; Settings
        </a>
    </div>

    <!-- =====================================================================
         TAB 1: EXPERIMENTS ("CURRENTLY IN THE LAB")
         ===================================================================== -->
    <?php if ($activeTab === 'experiments'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0;">Currently in the Lab Experiments</h2>
            <button type="button" class="btn btn-primary" onclick="openNewExperimentModal()">
                + New Experiment
            </button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                    <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th style="padding: 12px 18px; width: 64px;">Image</th>
                            <th style="padding: 12px 18px;">Title &amp; Question</th>
                            <th style="padding: 12px 18px;">Category</th>
                            <th style="padding: 12px 18px;">Status Badge</th>
                            <th style="padding: 12px 18px;">Tags</th>
                            <th style="padding: 12px 18px; width: 80px;">Order</th>
                            <th style="padding: 12px 18px; width: 90px;">Active</th>
                            <th style="padding: 12px 18px; width: 140px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($experiments)): ?>
                            <?php foreach ($experiments as $exp): ?>
                                <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                                    <td style="padding: 12px 18px;">
                                        <img src="../<?php echo htmlspecialchars($exp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 52px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="">
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <div style="font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($exp['title']); ?></div>
                                        <div style="font-size: 12px; color: var(--text-muted); max-width: 320px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                            <?php echo htmlspecialchars($exp['description']); ?>
                                        </div>
                                    </td>
                                    <td style="padding: 12px 18px; font-weight: 600; text-transform: uppercase; font-size: 11px; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($exp['category']); ?>
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <span class="status-badge" style="background: rgba(0,0,0,0.05); padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700;">
                                            ● <?php echo htmlspecialchars($exp['status_badge']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 11px; color: var(--text-light);">
                                        <?php echo htmlspecialchars($exp['tags']); ?>
                                    </td>
                                    <td style="padding: 12px 18px; font-weight: 600;">
                                        <?php echo (int)$exp['sort_order']; ?>
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="toggle_experiment">
                                            <input type="hidden" name="experiment_id" value="<?php echo (int)$exp['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $exp['is_active'] ? 'btn-primary' : 'btn-outline'; ?>" style="padding: 2px 8px; font-size: 11px;">
                                                <?php echo $exp['is_active'] ? 'Active' : 'Hidden'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td style="padding: 12px 18px; text-align: right;">
                                        <button type="button" class="btn btn-sm btn-outline" onclick='editExperiment(<?php echo json_encode($exp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                        <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to delete this experiment?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete_experiment">
                                            <input type="hidden" name="experiment_id" value="<?php echo (int)$exp['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="padding: 32px; text-align: center; color: var(--text-light);">No experiments found. Click "+ New Experiment" to add one!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- =====================================================================
         TAB 2: FAILED BATCHES ("FROM A FAILED BATCH TO A NEW INSIGHT")
         ===================================================================== -->
    <?php elseif ($activeTab === 'failed-batches'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">Failed Batch Case Studies ("Failure is Data")</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Log failed formulation attempts and the scientific breakthroughs they unlocked.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewFailedBatchModal()">
                + Log Failed Batch
            </button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                    <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th style="padding: 12px 18px; width: 64px;">Image</th>
                            <th style="padding: 12px 18px;">Case Study Name</th>
                            <th style="padding: 12px 18px;">Hypothesis</th>
                            <th style="padding: 12px 18px;">What Happened?</th>
                            <th style="padding: 12px 18px;">What Next?</th>
                            <th style="padding: 12px 18px; width: 100px;">Featured</th>
                            <th style="padding: 12px 18px; width: 140px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($failedBatches)): ?>
                            <?php foreach ($failedBatches as $fb): ?>
                                <tr style="border-bottom: 1px solid var(--border-color); <?php echo $fb['is_featured'] ? 'background: rgba(212, 175, 55, 0.05);' : ''; ?>">
                                    <td style="padding: 12px 18px;">
                                        <img src="../<?php echo htmlspecialchars($fb['image_path'] ?: 'assets/images/lab-failed-batch.jpg'); ?>" style="width: 52px; height: 52px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="">
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <div style="font-weight: 700; color: var(--text-main); font-size: 14px;"><?php echo htmlspecialchars($fb['title']); ?></div>
                                        <div style="font-size: 11px; color: #B45309; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($fb['badge_label']); ?></div>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: var(--text-muted);">
                                        <?php echo htmlspecialchars($fb['hypothesis']); ?>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: #DC2626;">
                                        <?php echo htmlspecialchars($fb['what_happened']); ?>
                                    </td>
                                    <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: #16A34A; font-weight: 500;">
                                        <?php echo htmlspecialchars($fb['what_next']); ?>
                                    </td>
                                    <td style="padding: 12px 18px;">
                                        <?php if ($fb['is_featured']): ?>
                                            <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">★ Home Featured</span>
                                        <?php else: ?>
                                            <form method="POST" style="margin: 0;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="set_featured_failed_batch">
                                                <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 2px 8px;">Make Feature</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 18px; text-align: right;">
                                        <button type="button" class="btn btn-sm btn-outline" onclick='editFailedBatch(<?php echo json_encode($fb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                        <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this failed batch case study?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="delete_failed_batch">
                                            <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="padding: 32px; text-align: center; color: var(--text-light);">No failed batch case studies logged yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- =====================================================================
         TAB 3: "WHAT IF?" QUESTIONS
         ===================================================================== -->
    <?php elseif ($activeTab === 'what-if'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">"What If?" Exploration Questions</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Curated list of bold questions guiding upcoming research.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewWhatIfModal()">
                + Add Question
            </button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 12px 18px; width: 60px;">#</th>
                        <th style="padding: 12px 18px;">Question Text</th>
                        <th style="padding: 12px 18px;">Link URL / Target</th>
                        <th style="padding: 12px 18px; width: 80px;">Order</th>
                        <th style="padding: 12px 18px; width: 80px;">Active</th>
                        <th style="padding: 12px 18px; width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($whatIfList)): ?>
                        <?php foreach ($whatIfList as $q): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 18px; font-weight: 700; font-family: var(--font-heading); font-size: 16px; color: var(--text-light);">
                                    <?php echo htmlspecialchars($q['num_label']); ?>
                                </td>
                                <td style="padding: 12px 18px; font-weight: 600; color: var(--text-main);">
                                    <?php echo htmlspecialchars($q['question']); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($q['link_url']); ?>
                                </td>
                                <td style="padding: 12px 18px;"><?php echo (int)$q['sort_order']; ?></td>
                                <td style="padding: 12px 18px;">
                                    <span style="font-size: 11px; padding: 2px 6px; border-radius: 4px; background: <?php echo $q['is_active'] ? '#DCFCE7; color: #166534;' : '#F3F4F6; color: #6B7280;'; ?>">
                                        <?php echo $q['is_active'] ? 'Active' : 'Hidden'; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick='editWhatIf(<?php echo json_encode($q, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this question?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_what_if">
                                        <input type="hidden" name="whatif_id" value="<?php echo (int)$q['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <!-- =====================================================================
         TAB 4: INGREDIENT CATEGORIES
         ===================================================================== -->
    <?php elseif ($activeTab === 'ingredients'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <div>
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">Ingredient Innovation Categories</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Featured materials explored across our test melangers.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewIngredientModal()">
                + Add Ingredient Category
            </button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 12px 18px; width: 64px;">Image</th>
                        <th style="padding: 12px 18px;">Category Title</th>
                        <th style="padding: 12px 18px;">Description</th>
                        <th style="padding: 12px 18px;">Link / Action</th>
                        <th style="padding: 12px 18px; width: 80px;">Order</th>
                        <th style="padding: 12px 18px; width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ingredientsList)): ?>
                        <?php foreach ($ingredientsList as $ing): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 18px;">
                                    <img src="../<?php echo htmlspecialchars($ing['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="">
                                </td>
                                <td style="padding: 12px 18px; font-weight: 700; color: var(--text-main);">
                                    <?php echo htmlspecialchars($ing['title']); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12.5px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($ing['description']); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($ing['link_url']); ?>
                                </td>
                                <td style="padding: 12px 18px;"><?php echo (int)$ing['sort_order']; ?></td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick='editIngredient(<?php echo json_encode($ing, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this ingredient category?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_ingredient">
                                        <input type="hidden" name="ing_id" value="<?php echo (int)$ing['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <!-- =====================================================================
         TAB 5: FUTURE OF CHOCOLATE & GENERAL HERO SETTINGS
         ===================================================================== -->
    <?php elseif ($activeTab === 'future'): ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_settings">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                
                <!-- Hero Section Settings Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">1. Hero Banner Settings</h3>
                    
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hero Eyebrow</label>
                        <input type="text" name="lab_hero_eyebrow" value="<?php echo htmlspecialchars($settings['lab_hero_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Main Title</label>
                        <input type="text" name="lab_hero_title" value="<?php echo htmlspecialchars($settings['lab_hero_title']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Subtitle Description</label>
                        <textarea name="lab_hero_subtitle" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"><?php echo htmlspecialchars($settings['lab_hero_subtitle']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Text</label>
                            <input type="text" name="lab_hero_cta_text" value="<?php echo htmlspecialchars($settings['lab_hero_cta_text']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Link</label>
                            <input type="text" name="lab_hero_cta_link" value="<?php echo htmlspecialchars($settings['lab_hero_cta_link']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sticky Note Text (Mantra pinned on wall)</label>
                        <textarea name="lab_sticky_note" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-style: italic;"><?php echo htmlspecialchars($settings['lab_sticky_note']); ?></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hero Image</label>
                        <input type="text" name="lab_hero_image" value="<?php echo htmlspecialchars($settings['lab_hero_image']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 6px;">
                        <input type="file" name="hero_image_file" accept="image/*" style="font-size: 12px;">
                    </div>
                </div>

                <!-- Future of Chocolate Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">2. Future of Chocolate Section</h3>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Eyebrow</label>
                        <input type="text" name="lab_future_eyebrow" value="<?php echo htmlspecialchars($settings['lab_future_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Section Heading</label>
                        <input type="text" name="lab_future_heading" value="<?php echo htmlspecialchars($settings['lab_future_heading']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Paragraph Content</label>
                        <textarea name="lab_future_text" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"><?php echo htmlspecialchars($settings['lab_future_text']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Text</label>
                            <input type="text" name="lab_future_cta_text" value="<?php echo htmlspecialchars($settings['lab_future_cta_text']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Link</label>
                            <input type="text" name="lab_future_cta_link" value="<?php echo htmlspecialchars($settings['lab_future_cta_link']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Landscape Photo</label>
                        <input type="text" name="lab_future_image" value="<?php echo htmlspecialchars($settings['lab_future_image']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 6px;">
                        <input type="file" name="future_image_file" accept="image/*" style="font-size: 12px;">
                    </div>
                </div>

            </div>

            <div style="text-align: right; margin-bottom: 40px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 14px; font-weight: 700;">
                    Save All Page Settings
                </button>
            </div>
        </form>

        <!-- Future Pillars List Section -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0;">Future of Chocolate Pillars</h3>
            <button type="button" class="btn btn-outline" onclick="openNewPillarModal()">+ Add Pillar</button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 32px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                    <tr>
                        <th style="padding: 12px 18px;">Pillar Title</th>
                        <th style="padding: 12px 18px;">Description</th>
                        <th style="padding: 12px 18px;">Target Link</th>
                        <th style="padding: 12px 18px; width: 80px;">Order</th>
                        <th style="padding: 12px 18px; width: 140px; text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pillarsList)): ?>
                        <?php foreach ($pillarsList as $pl): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 18px; font-weight: 700; color: var(--text-main);"><?php echo htmlspecialchars($pl['title']); ?></td>
                                <td style="padding: 12px 18px; font-size: 12.5px; color: var(--text-muted);"><?php echo htmlspecialchars($pl['description']); ?></td>
                                <td style="padding: 12px 18px; font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($pl['link_url']); ?></td>
                                <td style="padding: 12px 18px;"><?php echo (int)$pl['sort_order']; ?></td>
                                <td style="padding: 12px 18px; text-align: right;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick='editPillar(<?php echo json_encode($pl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this pillar?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_pillar">
                                        <input type="hidden" name="pillar_id" value="<?php echo (int)$pl['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</div>

<!-- =========================================================================
     MODAL: ADD / EDIT EXPERIMENT
     ========================================================================= -->
<div id="experimentModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); max-width: 760px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;">
        <button type="button" onclick="closeModal('experimentModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        
        <h2 id="expModalTitle" style="font-family: var(--font-heading); font-size: 24px; margin: 0 0 20px; color: var(--text-main);">Create New Lab Experiment</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_experiment">
            <input type="hidden" name="experiment_id" id="exp_id" value="0">
            <input type="hidden" name="existing_image_path" id="exp_existing_image" value="">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Experiment Title *</label>
                    <input type="text" name="title" id="exp_title" required placeholder="e.g. CBE × Bean-to-Bar" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Category *</label>
                    <select name="category" id="exp_category" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        <option value="ingredient-innovation">Ingredient Innovation</option>
                        <option value="process-exploration">Process Exploration</option>
                        <option value="formulation-lab" selected>Formulation Lab</option>
                        <option value="sensory-insights">Sensory Insights</option>
                        <option value="stability-shelf-life">Stability & Shelf Life</option>
                        <option value="scale-applications">Scale & Applications</option>
                        <option value="future-of-chocolate">Future of Chocolate</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Status Badge Label</label>
                    <input type="text" name="status_badge" id="exp_status_badge" value="IN PROGRESS" placeholder="e.g. IN PROGRESS, SENSORY TRIALS" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Badge Dot Color</label>
                    <select name="status_color" id="exp_status_color" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                        <option value="green">🟢 Green (In Progress / Formulation)</option>
                        <option value="amber">🟡 Amber (Sensory / Stability Trials)</option>
                        <option value="purple">🟣 Purple (Analysis / Insights)</option>
                        <option value="blue">🔵 Blue (Concluded / Scale)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Card Summary / Inquiry Question *</label>
                <textarea name="description" id="exp_desc" rows="2" required placeholder="e.g. Can cocoa butter equivalents deliver great texture, snap and flavour in high-cocoa chocolate?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Tags (Card Footer)</label>
                    <input type="text" name="tags" id="exp_tags" placeholder="e.g. FAT SYSTEMS | FORMULATION" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Display Sort Order</label>
                    <input type="number" name="sort_order" id="exp_sort_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Card Image</label>
                <input type="file" name="experiment_image" accept="image/*" style="display: block; margin-bottom: 6px; font-size: 12px;">
                <input type="text" name="existing_image_path_text" id="exp_img_text" placeholder="Or enter image URL / assets path" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hypothesis</label>
                <textarea name="hypothesis" id="exp_hypothesis" rows="2" placeholder="What core theory is being tested?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Methodology &amp; Parameters</label>
                <textarea name="methodology" id="exp_methodology" rows="2" placeholder="Melanging hours, temper curve, temperatures, ratios..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Key Findings &amp; Observations</label>
                    <textarea name="findings" id="exp_findings" rows="2" placeholder="What did the sensory panel / instruments show?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Next Iteration Steps</label>
                    <textarea name="next_steps" id="exp_next_steps" rows="2" placeholder="Next phase of tests..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Detailed Lab Notes (Full HTML Dossier)</label>
                <textarea name="full_content" id="exp_full_content" rows="4" placeholder="Deep research logs, charts, recipes, technical breakdown..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-family: monospace; font-size: 12px;"></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="exp_active" value="1" checked>
                    <span>Publish live on Innovation Lab page</span>
                </label>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('experimentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Experiment</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT FAILED BATCH
     ========================================================================= -->
<div id="failedModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); max-width: 760px; width: 100%; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;">
        <button type="button" onclick="closeModal('failedModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        
        <h2 id="failedModalTitle" style="font-family: var(--font-heading); font-size: 24px; margin: 0 0 20px; color: var(--text-main);">Log Failed Batch Case Study</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_failed_batch">
            <input type="hidden" name="failed_id" id="failed_id" value="0">
            <input type="hidden" name="existing_image_path" id="failed_existing_image" value="">

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Experiment Name / Title *</label>
                    <input type="text" name="title" id="failed_title" required placeholder="e.g. THE GUAVA EXPERIMENT" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Badge Eyebrow</label>
                    <input type="text" name="badge_label" id="failed_badge" value="BECAUSE FAILURE IS DATA." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hypothesis * (What did you set out to test?)</label>
                <textarea name="hypothesis" id="failed_hypothesis" rows="2" required placeholder="e.g. Freeze-dried guava will create an intense natural fruit note." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #DC2626; margin-bottom: 4px;">What Happened? * (The Failure)</label>
                <textarea name="what_happened" id="failed_what_happened" rows="2" required placeholder="e.g. Flavour disappeared after incorporation." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #D97706; margin-bottom: 4px;">Why? * (Scientific root cause)</label>
                <textarea name="why_it_happened" id="failed_why" rows="2" required placeholder="e.g. Possible volatile loss and fat interaction." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: #16A34A; margin-bottom: 4px;">What Next? * (Breakthrough / Remediation)</label>
                <textarea name="what_next" id="failed_what_next" rows="2" required placeholder="e.g. Test cocoa butter pre-coating." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Case Study Photo</label>
                <input type="file" name="failed_image" accept="image/*" style="display: block; margin-bottom: 6px; font-size: 12px;">
                <input type="text" name="existing_image_path_text" id="failed_img_text" placeholder="Or enter image URL / assets path" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Full Story &amp; Science (Detailed write-up shown in modal)</label>
                <textarea name="full_story" id="failed_full_story" rows="4" placeholder="Complete article explaining the trial, chromatography checks, lessons learned..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <div style="display: flex; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_featured" id="failed_featured" value="1" checked>
                        <span>Feature on main page</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="failed_active" value="1" checked>
                        <span>Active</span>
                    </label>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('failedModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Failed Batch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT WHAT IF QUESTION
     ========================================================================= -->
<div id="whatIfModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); max-width: 540px; width: 100%; border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;">
        <button type="button" onclick="closeModal('whatIfModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        
        <h2 id="whatIfModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0 0 18px; color: var(--text-main);">What If? Exploration Question</h2>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_what_if">
            <input type="hidden" name="whatif_id" id="whatif_id" value="0">

            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Number</label>
                    <input type="text" name="num_label" id="whatif_num" value="01" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Question *</label>
                    <input type="text" name="question" id="whatif_question" required placeholder="e.g. Can we reduce sugar without losing melt?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Target Link URL / Anchor</label>
                <input type="text" name="link_url" id="whatif_link" value="#currently-in-lab" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sort Order</label>
                    <input type="number" name="sort_order" id="whatif_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div style="display: flex; align-items: flex-end; padding-bottom: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="whatif_active" value="1" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('whatIfModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT INGREDIENT CATEGORY
     ========================================================================= -->
<div id="ingredientModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); max-width: 540px; width: 100%; border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;">
        <button type="button" onclick="closeModal('ingredientModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        
        <h2 id="ingModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0 0 18px; color: var(--text-main);">Ingredient Innovation Category</h2>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_ingredient">
            <input type="hidden" name="ing_id" id="ing_id" value="0">
            <input type="hidden" name="existing_image_path" id="ing_existing_image" value="">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Category Title *</label>
                <input type="text" name="title" id="ing_title" required placeholder="e.g. INDIAN FRUITS" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Photo</label>
                <input type="file" name="ingredient_image" accept="image/*" style="display: block; margin-bottom: 6px; font-size: 12px;">
                <input type="text" name="existing_image_path_text" id="ing_img_text" placeholder="Or enter image URL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Description / Research Focus</label>
                <textarea name="description" id="ing_desc" rows="2" placeholder="e.g. Alphonso mango powders, Kokum..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sort Order</label>
                    <input type="number" name="sort_order" id="ing_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
                </div>
                <div style="display: flex; align-items: flex-end; padding-bottom: 8px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="ing_active" value="1" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('ingredientModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT FUTURE PILLAR
     ========================================================================= -->
<div id="pillarModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 99999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: var(--bg-card); max-width: 500px; width: 100%; border-radius: 12px; padding: 28px; box-shadow: var(--shadow-lg); border: 1px solid var(--border-color); position: relative;">
        <button type="button" onclick="closeModal('pillarModal')" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        
        <h2 id="pillarModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0 0 18px; color: var(--text-main);">Future Pillar</h2>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_pillar">
            <input type="hidden" name="pillar_id" id="pillar_id" value="0">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Pillar Title *</label>
                <input type="text" name="title" id="pillar_title" required placeholder="e.g. REGENERATIVE CACAO" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Description / Context</label>
                <textarea name="description" id="pillar_desc" rows="2" placeholder="Agroforestry initiatives, biodiversity..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Link URL</label>
                <input type="text" name="link_url" id="pillar_link" value="#future-ideas" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sort Order</label>
                <input type="number" name="sort_order" id="pillar_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                <button type="button" class="btn btn-outline" onclick="closeModal('pillarModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Pillar</button>
            </div>
        </form>
    </div>
</div>

<script>
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openNewExperimentModal() {
    document.getElementById('expModalTitle').innerText = 'Create New Lab Experiment';
    document.getElementById('exp_id').value = '0';
    document.getElementById('exp_title').value = '';
    document.getElementById('exp_category').value = 'formulation-lab';
    document.getElementById('exp_status_badge').value = 'IN PROGRESS';
    document.getElementById('exp_status_color').value = 'green';
    document.getElementById('exp_desc').value = '';
    document.getElementById('exp_tags').value = 'FAT SYSTEMS | FORMULATION';
    document.getElementById('exp_sort_order').value = '0';
    document.getElementById('exp_existing_image').value = 'assets/images/placeholder.jpg';
    document.getElementById('exp_img_text').value = '';
    document.getElementById('exp_hypothesis').value = '';
    document.getElementById('exp_methodology').value = '';
    document.getElementById('exp_findings').value = '';
    document.getElementById('exp_next_steps').value = '';
    document.getElementById('exp_full_content').value = '';
    document.getElementById('exp_active').checked = true;
    document.getElementById('experimentModal').style.display = 'flex';
}

function editExperiment(exp) {
    document.getElementById('expModalTitle').innerText = 'Edit Lab Experiment: ' + exp.title;
    document.getElementById('exp_id').value = exp.id;
    document.getElementById('exp_title').value = exp.title || '';
    document.getElementById('exp_category').value = exp.category || 'formulation-lab';
    document.getElementById('exp_status_badge').value = exp.status_badge || 'IN PROGRESS';
    document.getElementById('exp_status_color').value = exp.status_color || 'green';
    document.getElementById('exp_desc').value = exp.description || '';
    document.getElementById('exp_tags').value = exp.tags || '';
    document.getElementById('exp_sort_order').value = exp.sort_order || '0';
    document.getElementById('exp_existing_image').value = exp.image_path || '';
    document.getElementById('exp_img_text').value = exp.image_path || '';
    document.getElementById('exp_hypothesis').value = exp.hypothesis || '';
    document.getElementById('exp_methodology').value = exp.methodology || '';
    document.getElementById('exp_findings').value = exp.findings || '';
    document.getElementById('exp_next_steps').value = exp.next_steps || '';
    document.getElementById('exp_full_content').value = exp.full_content || '';
    document.getElementById('exp_active').checked = Number(exp.is_active) === 1;
    document.getElementById('experimentModal').style.display = 'flex';
}

function openNewFailedBatchModal() {
    document.getElementById('failedModalTitle').innerText = 'Log Failed Batch Case Study';
    document.getElementById('failed_id').value = '0';
    document.getElementById('failed_title').value = '';
    document.getElementById('failed_badge').value = 'BECAUSE FAILURE IS DATA.';
    document.getElementById('failed_hypothesis').value = '';
    document.getElementById('failed_what_happened').value = '';
    document.getElementById('failed_why').value = '';
    document.getElementById('failed_what_next').value = '';
    document.getElementById('failed_existing_image').value = 'assets/images/lab-failed-batch.jpg';
    document.getElementById('failed_img_text').value = '';
    document.getElementById('failed_full_story').value = '';
    document.getElementById('failed_featured').checked = true;
    document.getElementById('failed_active').checked = true;
    document.getElementById('failedModal').style.display = 'flex';
}

function editFailedBatch(fb) {
    document.getElementById('failedModalTitle').innerText = 'Edit Failed Batch: ' + fb.title;
    document.getElementById('failed_id').value = fb.id;
    document.getElementById('failed_title').value = fb.title || '';
    document.getElementById('failed_badge').value = fb.badge_label || 'BECAUSE FAILURE IS DATA.';
    document.getElementById('failed_hypothesis').value = fb.hypothesis || '';
    document.getElementById('failed_what_happened').value = fb.what_happened || '';
    document.getElementById('failed_why').value = fb.why_it_happened || '';
    document.getElementById('failed_what_next').value = fb.what_next || '';
    document.getElementById('failed_existing_image').value = fb.image_path || '';
    document.getElementById('failed_img_text').value = fb.image_path || '';
    document.getElementById('failed_full_story').value = fb.full_story || '';
    document.getElementById('failed_featured').checked = Number(fb.is_featured) === 1;
    document.getElementById('failed_active').checked = Number(fb.is_active) === 1;
    document.getElementById('failedModal').style.display = 'flex';
}

function openNewWhatIfModal() {
    document.getElementById('whatIfModalTitle').innerText = 'Add What If? Question';
    document.getElementById('whatif_id').value = '0';
    document.getElementById('whatif_num').value = '0' + (<?php echo count($whatIfList); ?> + 1);
    document.getElementById('whatif_question').value = '';
    document.getElementById('whatif_link').value = '#currently-in-lab';
    document.getElementById('whatif_order').value = (<?php echo count($whatIfList); ?> + 1);
    document.getElementById('whatif_active').checked = true;
    document.getElementById('whatIfModal').style.display = 'flex';
}

function editWhatIf(q) {
    document.getElementById('whatIfModalTitle').innerText = 'Edit Question: ' + q.num_label;
    document.getElementById('whatif_id').value = q.id;
    document.getElementById('whatif_num').value = q.num_label || '';
    document.getElementById('whatif_question').value = q.question || '';
    document.getElementById('whatif_link').value = q.link_url || '';
    document.getElementById('whatif_order').value = q.sort_order || 0;
    document.getElementById('whatif_active').checked = Number(q.is_active) === 1;
    document.getElementById('whatIfModal').style.display = 'flex';
}

function openNewIngredientModal() {
    document.getElementById('ingModalTitle').innerText = 'Add Ingredient Category';
    document.getElementById('ing_id').value = '0';
    document.getElementById('ing_title').value = '';
    document.getElementById('ing_desc').value = '';
    document.getElementById('ing_existing_image').value = 'assets/images/placeholder.jpg';
    document.getElementById('ing_img_text').value = '';
    document.getElementById('ing_order').value = (<?php echo count($ingredientsList); ?> + 1);
    document.getElementById('ing_active').checked = true;
    document.getElementById('ingredientModal').style.display = 'flex';
}

function editIngredient(ing) {
    document.getElementById('ingModalTitle').innerText = 'Edit Ingredient: ' + ing.title;
    document.getElementById('ing_id').value = ing.id;
    document.getElementById('ing_title').value = ing.title || '';
    document.getElementById('ing_desc').value = ing.description || '';
    document.getElementById('ing_existing_image').value = ing.image_path || '';
    document.getElementById('ing_img_text').value = ing.image_path || '';
    document.getElementById('ing_order').value = ing.sort_order || 0;
    document.getElementById('ing_active').checked = Number(ing.is_active) === 1;
    document.getElementById('ingredientModal').style.display = 'flex';
}

function openNewPillarModal() {
    document.getElementById('pillarModalTitle').innerText = 'Add Future Pillar';
    document.getElementById('pillar_id').value = '0';
    document.getElementById('pillar_title').value = '';
    document.getElementById('pillar_desc').value = '';
    document.getElementById('pillar_link').value = '#future-ideas';
    document.getElementById('pillar_order').value = (<?php echo count($pillarsList); ?> + 1);
    document.getElementById('pillarModal').style.display = 'flex';
}

function editPillar(pl) {
    document.getElementById('pillarModalTitle').innerText = 'Edit Pillar: ' + pl.title;
    document.getElementById('pillar_id').value = pl.id;
    document.getElementById('pillar_title').value = pl.title || '';
    document.getElementById('pillar_desc').value = pl.description || '';
    document.getElementById('pillar_link').value = pl.link_url || '';
    document.getElementById('pillar_order').value = pl.sort_order || 0;
    document.getElementById('pillarModal').style.display = 'flex';
}
</script>

<?php render_admin_footer(); ?>
