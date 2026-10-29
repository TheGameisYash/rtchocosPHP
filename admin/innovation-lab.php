<?php
// admin/innovation-lab.php - Dynamic CMS Control Center for Innovation Lab
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/../includes/blog-uploads.php';

$pdo = get_db();
$error = '';
$success = '';
$csrfToken = generate_csrf();

$activeTab = $_GET['tab'] ?? 'experiments';

// Helper: Ensure specimen telemetry columns exist in lab_experiments table
function ensure_lab_columns($pdo) {
    static $done = false;
    if ($done) return;
    $cols = [
        'origin' => 'VARCHAR(255) NULL',
        'polymorph' => 'VARCHAR(255) NULL',
        'particle_size' => 'VARCHAR(150) NULL',
        'conching' => 'VARCHAR(255) NULL',
        'water_activity' => 'VARCHAR(150) NULL',
        'batch_ref' => 'VARCHAR(100) NULL'
    ];
    foreach ($cols as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE lab_experiments ADD COLUMN $col $def");
        } catch (Exception $e) {
            // Already exists or alter not permitted
        }
    }
    $done = true;
}
ensure_lab_columns($pdo);

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
    $isAjax = !empty($_POST['is_ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
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

                // Telemetry / science specs
                $origin = trim($_POST['origin'] ?? '');
                $polymorph = trim($_POST['polymorph'] ?? '');
                $particleSize = trim($_POST['particle_size'] ?? '');
                $conching = trim($_POST['conching'] ?? '');
                $waterActivity = trim($_POST['water_activity'] ?? '');
                $batchRef = trim($_POST['batch_ref'] ?? '');

                // Check for uploaded image or fallback to existing path
                $uploadedImg = handle_lab_image_upload('experiment_image');
                $imagePath = $uploadedImg ?: trim(!empty($_POST['existing_image_path_text']) ? $_POST['existing_image_path_text'] : ($_POST['existing_image_path'] ?? 'assets/images/placeholder.jpg'));

                // Slug generation
                $slug = trim($_POST['slug'] ?? '');
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
                }

                if (empty($title)) {
                    $error = 'Experiment title cannot be empty.';
                } else {
                    $checkCols = [];
                    try {
                        $q = $pdo->query("DESCRIBE lab_experiments");
                        $checkCols = $q->fetchAll(PDO::FETCH_COLUMN);
                    } catch (Exception $e) {}
                    $hasTelemetry = in_array('origin', $checkCols);

                    if ($id > 0) {
                        if ($hasTelemetry) {
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
                                $id
                            ]);
                        } else {
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
                        }
                        $success = "Experiment '{$title}' updated successfully!";
                    } else {
                        // Ensure unique slug
                        $checkSlug = $pdo->prepare("SELECT COUNT(*) FROM lab_experiments WHERE slug = ?");
                        $checkSlug->execute([$slug]);
                        if ($checkSlug->fetchColumn() > 0) {
                            $slug .= '-' . rand(10, 99);
                        }

                        if ($hasTelemetry) {
                            $stmt = $pdo->prepare("INSERT INTO lab_experiments 
                                (title, slug, category, status_badge, status_color, image_path, description, tags, hypothesis, methodology, findings, next_steps, full_content, sort_order, is_active, origin, polymorph, particle_size, conching, water_activity, batch_ref) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $title, $slug, $category, $statusBadge, $statusColor,
                                $imagePath, $description, $tags, $hypothesis, $methodology,
                                $findings, $nextSteps, $fullContent, $sortOrder, $isActive,
                                $origin, $polymorph, $particleSize, $conching, $waterActivity, $batchRef
                            ]);
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO lab_experiments 
                                (title, slug, category, status_badge, status_color, image_path, description, tags, hypothesis, methodology, findings, next_steps, full_content, sort_order, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $title, $slug, $category, $statusBadge, $statusColor,
                                $imagePath, $description, $tags, $hypothesis, $methodology,
                                $findings, $nextSteps, $fullContent, $sortOrder, $isActive
                            ]);
                        }
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
                    $stmt = $pdo->prepare("SELECT is_active, title FROM lab_experiments WHERE id = ?");
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isAct = (int)($row['is_active'] ?? 0);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'id' => $id,
                            'is_active' => $isAct,
                            'message' => "Experiment '" . ($row['title'] ?? 'Experiment') . "' is now " . ($isAct ? 'Visible' : 'Hidden') . "."
                        ]);
                        exit;
                    }
                    $success = "Experiment status toggled.";
                }
            } elseif ($action === 'toggle_failed_batch') {
                $activeTab = 'failed-batches';
                $id = (int)($_POST['failed_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE lab_failed_batches SET is_active = 1 - is_active WHERE id = ?");
                    $stmt->execute([$id]);
                    $stmt = $pdo->prepare("SELECT is_active, title FROM lab_failed_batches WHERE id = ?");
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isAct = (int)($row['is_active'] ?? 0);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'id' => $id,
                            'is_active' => $isAct,
                            'message' => "Failed batch '" . ($row['title'] ?? 'Batch') . "' is now " . ($isAct ? 'Visible' : 'Hidden') . "."
                        ]);
                        exit;
                    }
                    $success = "Failed batch status toggled.";
                }
            } elseif ($action === 'toggle_ingredient') {
                $activeTab = 'ingredients';
                $id = (int)($_POST['ing_id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("UPDATE lab_ingredient_categories SET is_active = 1 - is_active WHERE id = ?");
                    $stmt->execute([$id]);
                    $stmt = $pdo->prepare("SELECT is_active, title FROM lab_ingredient_categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $isAct = (int)($row['is_active'] ?? 0);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'id' => $id,
                            'is_active' => $isAct,
                            'message' => "Ingredient '" . ($row['title'] ?? 'Ingredient') . "' is now " . ($isAct ? 'Visible' : 'Hidden') . "."
                        ]);
                        exit;
                    }
                    $success = "Ingredient category status toggled.";
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
                $imagePath = $uploadedImg ?: trim(!empty($_POST['existing_image_path_text']) ? $_POST['existing_image_path_text'] : ($_POST['existing_image_path'] ?? 'assets/images/lab-failed-batch.jpg'));

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
                $keyVarietals = trim($_POST['key_varietals'] ?? '');
                $scienceBehavior = trim($_POST['science_behavior'] ?? '');
                $applications = trim($_POST['applications'] ?? '');
                $labNotes = trim($_POST['lab_notes'] ?? '');
                $sortOrder = (int)($_POST['sort_order'] ?? 0);
                $isActive = isset($_POST['is_active']) ? 1 : 0;

                $uploadedImg = handle_lab_image_upload('ingredient_image');
                $imagePath = $uploadedImg ?: trim(!empty($_POST['existing_image_path_text']) ? $_POST['existing_image_path_text'] : ($_POST['existing_image_path'] ?? 'assets/images/placeholder.jpg'));

                $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));

                if (empty($title)) {
                    $error = 'Ingredient title cannot be empty.';
                } else {
                    if ($id > 0) {
                        $stmt = $pdo->prepare("UPDATE lab_ingredient_categories SET title = ?, slug = ?, image_path = ?, description = ?, link_url = ?, key_varietals = ?, science_behavior = ?, applications = ?, lab_notes = ?, sort_order = ?, is_active = ? WHERE id = ?");
                        $stmt->execute([$title, $slug, $imagePath, $description, $linkUrl, $keyVarietals, $scienceBehavior, $applications, $labNotes, $sortOrder, $isActive, $id]);
                        $success = "Ingredient category '{$title}' updated!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO lab_ingredient_categories (title, slug, image_path, description, link_url, key_varietals, science_behavior, applications, lab_notes, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$title, $slug, $imagePath, $description, $linkUrl, $keyVarietals, $scienceBehavior, $applications, $labNotes, $sortOrder, $isActive]);
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
    'lab_hero_eyebrow' => get_site_setting('lab_hero_eyebrow', 'IDEAS · EXPERIMENTS · EVIDENCE'),
    'lab_hero_title' => get_site_setting('lab_hero_title', 'Innovation Lab'),
    'lab_hero_subtitle' => get_site_setting('lab_hero_subtitle', 'A working space where curious minds come together to explore new ingredients, techniques and ideas to shape a more thoughtful chocolate future.'),
    'lab_hero_cta_text' => get_site_setting('lab_hero_cta_text', 'STEP INTO THE LAB →'),
    'lab_hero_cta_link' => get_site_setting('lab_hero_cta_link', '#currently-in-lab'),
    'lab_sticky_note' => get_site_setting('lab_sticky_note', "Better Ingredients\nBigger Questions\nBrighter Chocolate"),
    'lab_hero_image' => get_site_setting('lab_hero_image', 'assets/images/lab-hero.jpg'),
    'lab_what_if_eyebrow' => get_site_setting('lab_what_if_eyebrow', 'IDEAS WE ARE TESTING NEXT.'),
    'lab_what_if_heading' => get_site_setting('lab_what_if_heading', 'What if?'),
    'lab_what_if_image' => get_site_setting('lab_what_if_image', 'assets/images/lab-what-if.jpg'),
    'lab_failed_batch_eyebrow' => get_site_setting('lab_failed_batch_eyebrow', 'WHEN THINGS GO WRONG, WE LEARN.'),
    'lab_failed_batch_heading' => get_site_setting('lab_failed_batch_heading', 'From a Failed Batch to a New Idea'),
    'lab_ingredient_eyebrow' => get_site_setting('lab_ingredient_eyebrow', 'LOCAL INGREDIENTS. FRESH FLAVORS. NEW IDEAS.'),
    'lab_ingredient_heading' => get_site_setting('lab_ingredient_heading', 'Ingredient Innovation'),
    'lab_future_eyebrow' => get_site_setting('lab_future_eyebrow', 'BIGGER DREAMS. BETTER CHOCOLATE.'),
    'lab_future_heading' => get_site_setting('lab_future_heading', 'Future of Chocolate'),
    'lab_future_text' => get_site_setting('lab_future_text', 'Exploring sustainable ideas that support cocoa farmers, care for our soil, and bring clean, honest chocolate to everyone.'),
    'lab_future_cta_text' => get_site_setting('lab_future_cta_text', 'EXPLORE FUTURE IDEAS →'),
    'lab_future_cta_link' => get_site_setting('lab_future_cta_link', '#future-ideas'),
    'lab_future_image' => get_site_setting('lab_future_image', 'assets/images/lab-future.jpg'),
];

$viewMode = $_COOKIE['lab_view_pref'] ?? 'grid';
render_admin_header('Innovation Lab', 'innovation-lab');
?>

<!-- Custom CSS for Innovation Lab Rich Text System -->
<style>
/* =========================================================================
   INNOVATION LAB ADMIN: LUXURY ATELIER STYLING & EMIL MOTION TOKENS
   ========================================================================= */

/* Modal Backdrop & Dialog Physics */
.lab-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(11, 8, 7, 0.85);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    padding: 20px;
    opacity: 0;
    transition: opacity 0.22s ease-out;
}
.lab-modal-backdrop.open {
    display: flex;
    opacity: 1;
}
.lab-modal-dialog {
    background: var(--bg-card);
    max-width: 920px;
    width: 100%;
    max-height: 94vh;
    display: flex;
    flex-direction: column;
    border-radius: 14px;
    box-shadow: 0 24px 60px rgba(0, 0, 0, 0.65), 0 0 0 1px var(--border-color);
    position: relative;
    overflow: hidden;
    transform: scale(0.96) translateY(12px);
    transition: transform 0.24s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease-out;
}
.lab-modal-backdrop.open .lab-modal-dialog {
    transform: scale(1) translateY(0);
}

/* Modal Internal Stepper Header */
.lab-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px 14px;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-card);
}
.lab-stepper-nav {
    display: flex;
    gap: 4px;
    padding: 4px 24px;
    background: var(--bg-subtle, rgba(0,0,0,0.03));
    border-bottom: 1px solid var(--border-color);
    overflow-x: auto;
}
.lab-step-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 700;
    color: var(--text-muted);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    white-space: nowrap;
    border-radius: 4px 4px 0 0;
    transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
}
.lab-step-tab:hover {
    color: var(--text-main);
    background: rgba(199, 166, 106, 0.06);
}
.lab-step-tab.active {
    color: var(--gold, #C7A66A);
    border-bottom-color: var(--gold, #C7A66A);
    background: rgba(199, 166, 106, 0.08);
}
.lab-step-tab:active {
    transform: scale(0.97);
}

/* Modal Body & Step Panels */
.lab-modal-body {
    padding: 22px 24px;
    overflow-y: auto;
    flex: 1;
}
.lab-step-panel {
    display: none;
    animation: labFadeIn 0.2s ease-out;
}
.lab-step-panel.active {
    display: block;
}
@keyframes labFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Modal Footer */
.lab-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-top: 1px solid var(--border-color);
    background: var(--bg-card);
}

/* Quick Fill Preset Chips */
.lab-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 5px;
    margin-bottom: 12px;
}
.lab-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    background: rgba(199, 166, 106, 0.08);
    border: 1px solid rgba(199, 166, 106, 0.24);
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    color: var(--gold, #C7A66A);
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.lab-chip:hover {
    background: rgba(199, 166, 106, 0.2);
    border-color: var(--gold, #C7A66A);
    transform: translateY(-1px);
}
.lab-chip:active {
    transform: scale(0.96);
}

/* Image Live Preview Box */
.lab-img-preview-box {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px;
    background: var(--bg-subtle, rgba(0,0,0,0.03));
    border: 1px solid var(--border-color);
    border-radius: 8px;
    margin-top: 8px;
}
.lab-img-preview-thumb {
    width: 80px;
    height: 56px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: #140F0C;
    flex-shrink: 0;
}

/* Table Search & Filter Bar */
.lab-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 18px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-bottom: none;
    border-radius: 10px 10px 0 0;
}
.lab-search-wrap {
    position: relative;
    flex: 1;
    min-width: 240px;
    max-width: 380px;
}
.lab-search-input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    font-size: 12.5px;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-app);
    color: var(--text-main);
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.lab-search-input:focus {
    border-color: var(--gold, #C7A66A);
    box-shadow: 0 0 0 2px rgba(199, 166, 106, 0.18);
}
.lab-search-icon {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 13px;
    pointer-events: none;
}
.lab-filter-select {
    padding: 8px 12px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid var(--border-color);
    border-radius: 6px;
    background: var(--bg-app);
    color: var(--text-main);
    cursor: pointer;
}

/* Rich Text Editor Styling */
.lab-rich-wrapper {
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-card);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.lab-rich-wrapper:focus-within {
    border-color: var(--gold, #C7A66A);
    box-shadow: 0 0 0 2px rgba(199, 166, 106, 0.2);
}
.lab-rich-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    background: var(--bg-subtle, rgba(0,0,0,0.03));
    border-bottom: 1px solid var(--border-color);
    user-select: none;
}
.lab-rich-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-width: 28px;
    height: 28px;
    padding: 0 7px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: 5px;
    color: var(--text-main);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
}
.lab-rich-btn:hover {
    background: rgba(199, 166, 106, 0.15);
    border-color: rgba(199, 166, 106, 0.3);
    color: var(--gold, #C7A66A);
}
.lab-rich-btn:active {
    transform: scale(0.96);
}
.lab-rich-btn.btn-bullet-highlight {
    background: rgba(22, 101, 52, 0.12);
    border-color: rgba(22, 101, 52, 0.25);
    color: #16A34A;
}
.lab-rich-btn.btn-bullet-highlight:hover {
    background: rgba(22, 101, 52, 0.22);
    color: #15803D;
}
.lab-rich-btn.btn-template-highlight {
    background: rgba(199, 166, 106, 0.14);
    border-color: rgba(199, 166, 106, 0.3);
    color: var(--gold, #C7A66A);
}
.lab-rich-select {
    height: 28px;
    padding: 0 8px;
    font-size: 11.5px;
    font-weight: 600;
    border: 1px solid var(--border-color);
    border-radius: 5px;
    background: var(--bg-card);
    color: var(--text-main);
    cursor: pointer;
}
.lab-rich-sep {
    width: 1px;
    height: 18px;
    background: var(--border-color);
    margin: 0 4px;
}
.lab-rich-body {
    padding: 10px 14px;
    min-height: 75px;
    max-height: 380px;
    overflow-y: auto;
    font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
    font-size: 13px;
    line-height: 1.6;
    color: var(--text-main);
    outline: none;
    background: var(--bg-card);
}
.lab-rich-body[contenteditable="true"]:empty:before {
    content: attr(placeholder);
    color: var(--text-muted);
    font-style: italic;
    pointer-events: none;
}
.lab-rich-body p {
    margin: 0 0 8px;
}
.lab-rich-body p:last-child {
    margin-bottom: 0;
}
.lab-rich-body ul, .lab-rich-body ol {
    margin: 6px 0 10px;
    padding-left: 22px;
}
.lab-rich-body li {
    margin-bottom: 4px;
    line-height: 1.55;
}
.lab-rich-body h3 {
    font-size: 15px;
    font-weight: 700;
    margin: 14px 0 6px;
    color: var(--gold, #C7A66A);
}
.lab-rich-body h4 {
    font-size: 13.5px;
    font-weight: 700;
    margin: 10px 0 4px;
}
.lab-rich-body blockquote {
    margin: 10px 0;
    padding: 8px 14px;
    background: rgba(199, 166, 106, 0.08);
    border-left: 3px solid var(--gold, #C7A66A);
    border-radius: 0 6px 6px 0;
    font-style: italic;
}
.lab-rich-textarea-source {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    line-height: 1.5;
    background: var(--bg-card);
    color: var(--text-main);
    border: none;
    outline: none;
    resize: vertical;
    display: none;
}
.lab-rich-mode-btn {
    margin-left: auto;
    font-size: 11px;
    font-weight: 700;
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-muted);
    border-radius: 4px;
    padding: 2px 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.lab-rich-mode-btn:hover {
    color: var(--gold, #C7A66A);
    border-color: var(--gold, #C7A66A);
}

/* Front-End Simulation Card in Step 5 Preview */
.lab-preview-card-wrap {
    background: var(--bg-subtle, #140F0C);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    display: grid;
    grid-template-columns: 340px 1fr;
    gap: 20px;
}
@media (max-width: 800px) {
    .lab-preview-card-wrap {
        grid-template-columns: 1fr;
    }
}
.lab-live-card-sim {
    background: #FFFFFF;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.08);
}
[data-theme="dark"] .lab-live-card-sim {
    background: #1C1511;
    border-color: rgba(212, 175, 55, 0.18);
}
.lab-live-card-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    display: block;
}
.lab-live-card-inner {
    padding: 16px;
}
.lab-live-card-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 3px 8px;
    border-radius: 4px;
    background: rgba(16, 185, 129, 0.12);
    color: #10B981;
    margin-bottom: 8px;
}
.lab-live-card-title {
    font-family: var(--font-heading);
    font-size: 18px;
    font-weight: 700;
    color: var(--text-main);
    margin-bottom: 6px;
}
.lab-live-card-q {
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.5;
    margin-bottom: 12px;
}
.lab-live-card-tags {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: var(--gold, #C7A66A);
    border-top: 1px solid var(--border-color);
    padding-top: 10px;
}
.lab-live-dossier-sim {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.lab-sim-quad {
    padding: 10px 12px;
    border-radius: 8px;
    background: var(--bg-subtle, rgba(0,0,0,0.02));
    border-left: 3px solid var(--gold, #C7A66A);
}
.lab-sim-quad-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-bottom: 4px;
}

/* Tactile Button Feedback */
.btn:active {
    transform: scale(0.98);
}

    /* Dropdown & Blog Card Enhancements from admin/blogs.php */
    .blog-card {
        overflow: visible !important;
    }
    .blog-card:hover,
    .blog-card:focus-within,
    .blog-card.menu-active {
        z-index: 100 !important;
    }
    .blog-card .more-actions-dropdown {
        display: none;
        position: absolute;
        right: 0;
        bottom: calc(100% + 8px) !important;
        top: auto !important;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.55), 0 4px 10px rgba(0, 0, 0, 0.3);
        z-index: 9999 !important;
        min-width: 155px;
        overflow: hidden;
    }
    .more-actions-dropdown button,
    .more-actions-dropdown a {
        display: flex;
        align-items: center;
        width: 100%;
        padding: 9px 16px;
        background: none;
        border: none;
        text-align: left;
        font-size: 13px;
        color: var(--text-muted);
        text-decoration: none;
        cursor: pointer;
        font-family: var(--font-sans);
        transition: background-color var(--transition), color var(--transition);
        white-space: nowrap;
    }
    .more-actions-dropdown button:hover,
    .more-actions-dropdown a:hover {
        background-color: var(--bg-subtle, var(--cream));
        color: var(--text-main);
    }
    .more-actions-dropdown button.delete-btn:hover {
        background-color: rgba(211, 47, 47, 0.08);
        color: #d32f2f;
    }
</style>

<div class="content-wrapper">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <span style="font-size: 22px;">🧪</span>
                <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading); margin: 0;">Innovation Lab Management</h1>
                <span class="status-badge" style="background: rgba(199, 166, 106, 0.15); color: var(--gold, #C7A66A); border: 1px solid rgba(199, 166, 106, 0.3); padding: 3px 10px; border-radius: 999px; font-size: 11.5px; font-weight: 700;">
                    LIVE ATELIER CMS
                </span>
            </div>
            <p style="color: var(--text-muted); font-size: 14px; margin: 0;">
                Full dynamic control over experiments, failed batches, exploration questions, native ingredients, and live dossiers.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="../innovation-lab.php" target="_blank" class="btn btn-outline" style="display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6M15 3h6v6M10 14L21 3"/></svg>
                <span>View Live Lab Page</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if (!empty($success)): ?>
        <div style="background: #EBF8F2; border-left: 4px solid #10B981; color: #065F46; padding: 14px 20px; border-radius: 6px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
            <span>✓ <?php echo htmlspecialchars($success); ?></span>
            <span style="cursor: pointer;" onclick="this.parentElement.style.display='none'">✕</span>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div style="background: #FEF2F2; border-left: 4px solid #EF4444; color: #991B1B; padding: 14px 20px; border-radius: 6px; margin-bottom: 24px; font-weight: 500; display: flex; align-items: center; justify-content: space-between;">
            <span>✕ <?php echo htmlspecialchars($error); ?></span>
            <span style="cursor: pointer;" onclick="this.parentElement.style.display='none'">✕</span>
        </div>
    <?php endif; ?>

    <!-- KPI Metric Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 28px;">
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); transition: transform 0.2s ease, border-color 0.2s ease;">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Total Experiments</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($experiments); ?></div>
            <div style="font-size: 12px; color: var(--status-active, #10B981); font-weight: 600; margin-top: 4px;">Currently in the Lab</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); transition: transform 0.2s ease, border-color 0.2s ease;">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Failed Batches Logged</div>
            <div style="font-size: 32px; font-weight: 700; color: #B45309; font-family: var(--font-heading);"><?php echo count($failedBatches); ?></div>
            <div style="font-size: 12px; color: #B45309; font-weight: 600; margin-top: 4px;">From a Failed Batch to a New Idea</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); transition: transform 0.2s ease, border-color 0.2s ease;">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">What If? Inquiries</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($whatIfList); ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Future Curiosities</div>
        </div>
        <div class="card" style="padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); background: var(--bg-card); transition: transform 0.2s ease, border-color 0.2s ease;">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;">Ingredient Categories</div>
            <div style="font-size: 32px; font-weight: 700; color: var(--text-main); font-family: var(--font-heading);"><?php echo count($ingredientsList); ?></div>
            <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Frontiers of Taste</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid var(--border-color); margin-bottom: 24px; overflow-x: auto; padding-bottom: 2px;">
        <a href="?tab=experiments" class="btn <?php echo $activeTab === 'experiments' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🧪 Currently in the Lab (<?php echo count($experiments); ?>)
        </a>
        <a href="?tab=failed-batches" class="btn <?php echo $activeTab === 'failed-batches' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            💥 From a Failed Batch to a New Idea (<?php echo count($failedBatches); ?>)
        </a>
        <a href="?tab=what-if" class="btn <?php echo $activeTab === 'what-if' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            💡 What if? (<?php echo count($whatIfList); ?>)
        </a>
        <a href="?tab=ingredients" class="btn <?php echo $activeTab === 'ingredients' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🌱 Ingredient Innovation (<?php echo count($ingredientsList); ?>)
        </a>
        <a href="?tab=future" class="btn <?php echo $activeTab === 'future' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius: 8px 8px 0 0; padding: 10px 18px;">
            🌍 Future of Chocolate &amp; Settings
        </a>
    </div>

    <!-- =====================================================================
         TAB 1: EXPERIMENTS ("CURRENTLY IN THE LAB")
         ===================================================================== -->
    <?php if ($activeTab === 'experiments'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.1em; color: var(--gold, #C7A66A); text-transform: uppercase; margin-bottom: 2px;">WHERE CURIOSITY TAKES SHAPE.</div>
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text-main); margin: 0 0 4px; font-family: 'Playfair Display', Georgia, serif;">Currently in the Lab</h2>
                <p style="font-size: 13.5px; color: var(--text-muted); margin: 0;">Active recipe tests, ingredient blends, and kitchen notes from our chocolate makers.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewExperimentModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path d="M12 4v16m8-8H4"></path></svg>
                New Experiment
            </button>
        </div>

        <!-- Action Bar: Live Search, Category Filter Chips, Status Dropdown, View Toggle -->
        <div class="action-bar" style="margin-bottom: 24px;">
            <div class="action-bar-left">
                <!-- Search input -->
                <div class="topbar-search" style="display: block; width: 280px; position: relative;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted); pointer-events: none;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="expSearchInput" placeholder="Search experiments, tags, batches..." oninput="filterExperimentsView()" style="width: 100%; border-radius: 8px; padding-left: 36px; padding-top: 9px; padding-bottom: 9px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px;">
                </div>

                <!-- Category Filters -->
                <button type="button" class="filter-chip active" data-cat="" onclick="setExpCategoryFilter('', this)">All Categories</button>
                <button type="button" class="filter-chip" data-cat="ingredient-innovation" onclick="setExpCategoryFilter('ingredient-innovation', this)">Ingredient Innovation</button>
                <button type="button" class="filter-chip" data-cat="process-exploration" onclick="setExpCategoryFilter('process-exploration', this)">Process Exploration</button>
                <button type="button" class="filter-chip" data-cat="formulation-lab" onclick="setExpCategoryFilter('formulation-lab', this)">Formulation Lab</button>
                <button type="button" class="filter-chip" data-cat="sensory-insights" onclick="setExpCategoryFilter('sensory-insights', this)">Sensory Insights</button>
                <button type="button" class="filter-chip" data-cat="stability-shelf-life" onclick="setExpCategoryFilter('stability-shelf-life', this)">Stability &amp; Shelf Life</button>
            </div>

            <div class="action-bar-right">
                <!-- Status Filter -->
                <select id="expStatusSelect" onchange="filterExperimentsView()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px; font-family: var(--font-sans); outline: none;">
                    <option value="">All Statuses</option>
                    <option value="published">Visible (Active)</option>
                    <option value="draft">Hidden (Inactive)</option>
                </select>

                <!-- Layout Selector Toggle -->
                <div class="view-toggle">
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" onclick="setLabViewMode('grid')" title="Grid View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'list' ? 'active' : ''; ?>" onclick="setLabViewMode('list')" title="List View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

                <span id="expCountBadge" style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; white-space: nowrap;">
                    Showing <?php echo count($experiments); ?> items
                </span>
            </div>
        </div>

        <?php if (empty($experiments)): ?>
            <div class="form-card" style="padding: 60px; text-align: center; color: var(--text-light);">
                <p style="font-size: 16px; font-weight: 500;">No experiments found. Click "+ New Experiment" to add one!</p>
            </div>
        <?php else: ?>
            <!-- GRID VIEW (Matching admin/blogs.php) -->
            <div class="blogs-grid-view" id="expGridContainer" style="<?php echo $viewMode === 'grid' ? '' : 'display:none;'; ?>">
                <?php foreach ($experiments as $exp): 
                    $isPub = (int)$exp['is_active'];
                    $thumbUrl = !empty($exp['image_path']) ? '../' . $exp['image_path'] : '../assets/images/placeholder.jpg';
                ?>
                    <div class="blog-card exp-card-item" id="exp-card-<?php echo $exp['id']; ?>"
                         data-title="<?php echo strtolower(htmlspecialchars($exp['title'])); ?>"
                         data-desc="<?php echo strtolower(htmlspecialchars($exp['description'])); ?>"
                         data-tags="<?php echo strtolower(htmlspecialchars($exp['tags'])); ?>"
                         data-cat="<?php echo strtolower(htmlspecialchars($exp['category'])); ?>"
                         data-status="<?php echo $isPub ? 'published' : 'draft'; ?>">
                        <div class="blog-card-image">
                            <div class="blog-card-badges">
                                <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                      id="badge-exp-grid-<?php echo $exp['id']; ?>"
                                      style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                    <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                    <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                </span>
                            </div>
                            <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" onerror="this.onerror=null; this.src='../assets/images/placeholder.jpg';" loading="lazy">
                            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.65); color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo htmlspecialchars($exp['status_badge'] ?: 'TEST BATCH'); ?>
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-card-category"><?php echo htmlspecialchars($exp['category']); ?></span>
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($exp['title']); ?></h3>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars($exp['description'] ?: 'No research question summary entered.'); ?></p>
                            <div class="blog-card-footer">
                                <span class="blog-card-date">Batch #<?php echo htmlspecialchars($exp['batch_ref'] ?: $exp['id']); ?></span>
                                <div class="blog-card-actions">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:6px 12px;" onclick='editExperiment(<?php echo json_encode($exp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-outline btn-sm btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="experiment"
                                            data-id="<?php echo $exp['id']; ?>" 
                                            onclick="toggleLabItem('experiment', <?php echo $exp['id']; ?>, this)" 
                                            title="<?php echo $isPub ? 'Click to Hide from site' : 'Click to Make Visible on site'; ?>"
                                            style="padding:6px 10px; display:inline-flex; align-items:center; gap:5px; font-weight:600; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <div style="position: relative; display: inline-block;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleMoreMenu(event, this)" style="padding: 6px 8px; min-width: 0;">&bull;&bull;&bull;</button>
                                        <div class="more-actions-dropdown">
                                            <a href="../innovation-lab.php#exp-<?php echo htmlspecialchars($exp['slug'] ?: $exp['id']); ?>" target="_blank">View on Site &nearr;</a>
                                            <button type="button" onclick='cloneExperiment(<?php echo json_encode($exp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Duplicate</button>
                                            <form method="POST" onsubmit="return confirm('Delete this experiment?');" class="delete-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete_experiment">
                                                <input type="hidden" name="experiment_id" value="<?php echo (int)$exp['id']; ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- LIST VIEW (Wrapped in #expListContainer) -->
            <div class="table-container" id="expListContainer" style="<?php echo $viewMode === 'list' ? '' : 'display:none;'; ?> background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 30px;">
                <table id="experimentsTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                    <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th style="padding: 12px 18px; width: 64px;">Image</th>
                            <th style="padding: 12px 18px;">Title &amp; Question</th>
                            <th style="padding: 12px 18px;">Category</th>
                            <th style="padding: 12px 18px;">Status Badge</th>
                            <th style="padding: 12px 18px; width: 80px;">Order</th>
                            <th style="padding: 12px 18px; width: 110px;">Status</th>
                            <th style="padding: 12px 18px; width: 220px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($experiments as $exp): 
                            $isPub = (int)$exp['is_active'];
                        ?>
                            <tr class="exp-row" id="exp-row-<?php echo $exp['id']; ?>"
                                data-title="<?php echo strtolower(htmlspecialchars($exp['title'])); ?>" 
                                data-desc="<?php echo strtolower(htmlspecialchars($exp['description'])); ?>" 
                                data-tags="<?php echo strtolower(htmlspecialchars($exp['tags'])); ?>" 
                                data-cat="<?php echo strtolower(htmlspecialchars($exp['category'])); ?>" 
                                data-status="<?php echo $isPub ? 'published' : 'draft'; ?>"
                                style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;">
                                <td style="padding: 12px 18px;">
                                    <img src="../<?php echo htmlspecialchars($exp['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 52px; height: 42px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="" onerror="this.src='../assets/images/placeholder.jpg'">
                                </td>
                                <td style="padding: 12px 18px;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 14px;"><?php echo htmlspecialchars($exp['title']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted); max-width: 340px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                        <?php echo htmlspecialchars($exp['description']); ?>
                                    </div>
                                </td>
                                <td style="padding: 12px 18px; font-weight: 600; text-transform: uppercase; font-size: 11px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($exp['category']); ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <span class="status-badge" style="background: rgba(199, 166, 106, 0.1); border: 1px solid rgba(199, 166, 106, 0.25); color: var(--text-main); padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; white-space: nowrap;">
                                        ● <?php echo htmlspecialchars($exp['status_badge']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; font-weight: 600;">
                                    <?php echo (int)$exp['sort_order']; ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                          id="badge-exp-list-<?php echo $exp['id']; ?>"
                                          style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                        <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px; font-size: 12px;" onclick='editExperiment(<?php echo json_encode($exp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="experiment"
                                            data-id="<?php echo $exp['id']; ?>" 
                                            onclick="toggleLabItem('experiment', <?php echo $exp['id']; ?>, this)" 
                                            style="padding: 4px 8px; font-size: 12px; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline" style="padding: 4px 8px; font-size: 12px; color: var(--gold, #C7A66A);" onclick='cloneExperiment(<?php echo json_encode($exp, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' title="Clone in 1 click">Clone</button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Are you sure you want to delete experiment \'<?php echo addslashes($exp['title']); ?>\'?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_experiment">
                                        <input type="hidden" name="experiment_id" value="<?php echo (int)$exp['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" style="padding: 4px 8px; font-size: 12px;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php elseif ($activeTab === 'failed-batches'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.1em; color: var(--gold, #C7A66A); text-transform: uppercase; margin-bottom: 2px;">WHEN THINGS GO WRONG, WE LEARN.</div>
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text-main); margin: 0 0 4px; font-family: 'Playfair Display', Georgia, serif;">From a Failed Batch to a New Idea</h2>
                <p style="font-size: 13.5px; color: var(--text-muted); margin: 0;">How we turn unexpected recipe mistakes into better, more delicious chocolate.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewFailedBatchModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path d="M12 4v16m8-8H4"></path></svg>
                Log Failed Batch
            </button>
        </div>

        <!-- Action Bar -->
        <div class="action-bar" style="margin-bottom: 24px;">
            <div class="action-bar-left">
                <div class="topbar-search" style="display: block; width: 280px; position: relative;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted); pointer-events: none;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="failedSearchInput" placeholder="Search failed batch case studies..." oninput="filterFailedBatchesView()" style="width: 100%; border-radius: 8px; padding-left: 36px; padding-top: 9px; padding-bottom: 9px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px;">
                </div>
            </div>

            <div class="action-bar-right">
                <select id="failedStatusSelect" onchange="filterFailedBatchesView()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px; font-family: var(--font-sans); outline: none;">
                    <option value="">All Statuses</option>
                    <option value="published">Visible</option>
                    <option value="draft">Hidden</option>
                    <option value="featured">Featured Only</option>
                </select>

                <div class="view-toggle">
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" onclick="setLabViewMode('grid')" title="Grid View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'list' ? 'active' : ''; ?>" onclick="setLabViewMode('list')" title="List View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

                <span id="failedCountBadge" style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; white-space: nowrap;">
                    Showing <?php echo count($failedBatches); ?> items
                </span>
            </div>
        </div>

        <?php if (empty($failedBatches)): ?>
            <div class="form-card" style="padding: 60px; text-align: center; color: var(--text-light);">
                <p style="font-size: 16px; font-weight: 500;">No failed batch case studies logged yet. Click "+ Log Failed Batch" to add one!</p>
            </div>
        <?php else: ?>
            <!-- GRID VIEW -->
            <div class="blogs-grid-view" id="failedGridContainer" style="<?php echo $viewMode === 'grid' ? '' : 'display:none;'; ?>">
                <?php foreach ($failedBatches as $fb): 
                    $isPub = (int)$fb['is_active'];
                    $thumbUrl = !empty($fb['image_path']) ? '../' . $fb['image_path'] : '../assets/images/lab-failed-batch.jpg';
                ?>
                    <div class="blog-card failed-card-item" id="failed-card-<?php echo $fb['id']; ?>"
                         data-title="<?php echo strtolower(htmlspecialchars($fb['title'])); ?>"
                         data-hypo="<?php echo strtolower(htmlspecialchars(strip_tags($fb['hypothesis']))); ?>"
                         data-happened="<?php echo strtolower(htmlspecialchars(strip_tags($fb['what_happened']))); ?>"
                         data-next="<?php echo strtolower(htmlspecialchars(strip_tags($fb['what_next']))); ?>"
                         data-featured="<?php echo $fb['is_featured'] ? 'featured' : ''; ?>"
                         data-status="<?php echo $isPub ? 'published' : 'draft'; ?>">
                        <div class="blog-card-image">
                            <div class="blog-card-badges">
                                <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                      id="badge-failed-grid-<?php echo $fb['id']; ?>"
                                      style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                    <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                    <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                </span>
                                <?php if ($fb['is_featured']): ?>
                                    <span class="status-badge published" style="background: #FEF3C7; color: #92400E; border-color: #FCD34D;">
                                        <span class="badge-icon">★</span>
                                        <span class="badge-label">Featured</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="<?php echo htmlspecialchars($fb['title']); ?>" onerror="this.onerror=null; this.src='../assets/images/lab-failed-batch.jpg';" loading="lazy">
                            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.65); color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo htmlspecialchars($fb['badge_label'] ?: 'FAILURE IS DATA'); ?>
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-card-category"><?php echo htmlspecialchars($fb['badge_label'] ?: 'Case Study'); ?></span>
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($fb['title']); ?></h3>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars(strip_tags($fb['hypothesis']) ?: 'No hypothesis entered.'); ?></p>
                            <div class="blog-card-footer">
                                <span class="blog-card-date"><?php echo $fb['is_featured'] ? '★ Home Featured' : 'Case Study'; ?></span>
                                <div class="blog-card-actions">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:6px 12px;" onclick='editFailedBatch(<?php echo json_encode($fb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-outline btn-sm btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="failed_batch"
                                            data-id="<?php echo $fb['id']; ?>" 
                                            onclick="toggleLabItem('failed_batch', <?php echo $fb['id']; ?>, this)" 
                                            title="<?php echo $isPub ? 'Click to Hide from site' : 'Click to Make Visible on site'; ?>"
                                            style="padding:6px 10px; display:inline-flex; align-items:center; gap:5px; font-weight:600; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <div style="position: relative; display: inline-block;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleMoreMenu(event, this)" style="padding: 6px 8px; min-width: 0;">&bull;&bull;&bull;</button>
                                        <div class="more-actions-dropdown">
                                            <?php if (!$fb['is_featured']): ?>
                                                <form method="POST">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                    <input type="hidden" name="action" value="set_featured_failed_batch">
                                                    <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                                    <button type="submit">★ Make Featured</button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="../innovation-lab.php#failed-experiments" target="_blank">View on Site &nearr;</a>
                                            <form method="POST" onsubmit="return confirm('Delete this failed batch case study?');" class="delete-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete_failed_batch">
                                                <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- LIST VIEW -->
            <div class="table-container" id="failedListContainer" style="<?php echo $viewMode === 'list' ? '' : 'display:none;'; ?> background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 30px;">
                <table id="failedBatchesTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                    <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th style="padding: 12px 18px; width: 64px;">Image</th>
                            <th style="padding: 12px 18px;">Case Study Title</th>
                            <th style="padding: 12px 18px;">1. What We Hoped For</th>
                            <th style="padding: 12px 18px;">2. What Went Wrong</th>
                            <th style="padding: 12px 18px;">4. What We Learned</th>
                            <th style="padding: 12px 18px; width: 110px;">Featured</th>
                            <th style="padding: 12px 18px; width: 110px;">Status</th>
                            <th style="padding: 12px 18px; width: 170px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($failedBatches as $fb): 
                            $isPub = (int)$fb['is_active'];
                        ?>
                            <tr class="failed-row" id="failed-row-<?php echo $fb['id']; ?>"
                                data-title="<?php echo strtolower(htmlspecialchars($fb['title'])); ?>" 
                                data-hypo="<?php echo strtolower(htmlspecialchars(strip_tags($fb['hypothesis']))); ?>"
                                data-happened="<?php echo strtolower(htmlspecialchars(strip_tags($fb['what_happened']))); ?>"
                                data-next="<?php echo strtolower(htmlspecialchars(strip_tags($fb['what_next']))); ?>"
                                data-featured="<?php echo $fb['is_featured'] ? 'featured' : ''; ?>"
                                data-status="<?php echo $isPub ? 'published' : 'draft'; ?>"
                                style="border-bottom: 1px solid var(--border-color); <?php echo $fb['is_featured'] ? 'background: rgba(212, 175, 55, 0.05);' : ''; ?>">
                                <td style="padding: 12px 18px;">
                                    <img src="../<?php echo htmlspecialchars($fb['image_path'] ?: 'assets/images/lab-failed-batch.jpg'); ?>" style="width: 52px; height: 52px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="" onerror="this.src='../assets/images/lab-failed-batch.jpg'">
                                </td>
                                <td style="padding: 12px 18px;">
                                    <div style="font-weight: 700; color: var(--text-main); font-size: 14px;"><?php echo htmlspecialchars($fb['title']); ?></div>
                                    <div style="font-size: 11px; color: #B45309; font-weight: 700; text-transform: uppercase;"><?php echo htmlspecialchars($fb['badge_label']); ?></div>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars(strip_tags($fb['hypothesis'])); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: #DC2626;">
                                    <?php echo htmlspecialchars(strip_tags($fb['what_happened'])); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12.5px; max-width: 200px; color: #16A34A; font-weight: 500;">
                                    <?php echo htmlspecialchars(strip_tags($fb['what_next'])); ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <?php if ($fb['is_featured']): ?>
                                        <span style="background: #FEF3C7; color: #92400E; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700;">★ Featured</span>
                                    <?php else: ?>
                                        <form method="POST" style="margin: 0;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                            <input type="hidden" name="action" value="set_featured_failed_batch">
                                            <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline" style="font-size: 11px; padding: 2px 8px;">Make Feature</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 18px;">
                                    <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                          id="badge-failed-list-<?php echo $fb['id']; ?>"
                                          style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                        <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick='editFailedBatch(<?php echo json_encode($fb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="failed_batch"
                                            data-id="<?php echo $fb['id']; ?>" 
                                            onclick="toggleLabItem('failed_batch', <?php echo $fb['id']; ?>, this)" 
                                            style="padding: 4px 8px; font-size: 12px; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this failed batch case study?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_failed_batch">
                                        <input type="hidden" name="failed_id" value="<?php echo (int)$fb['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php elseif ($activeTab === 'what-if'): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.1em; color: var(--gold, #C7A66A); text-transform: uppercase; margin-bottom: 2px;">IDEAS WE ARE TESTING NEXT.</div>
                <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main); margin: 0 0 4px;">What if?</h2>
                <p style="font-size: 13px; color: var(--text-muted); margin: 0;">The creative questions guiding our next batch of experiments.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewWhatIfModal()" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; font-weight: 700;">
                <span style="font-size: 16px;">+</span> Add Question
            </button>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 30px;">
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
                                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.1em; color: var(--gold, #C7A66A); text-transform: uppercase; margin-bottom: 2px;">LOCAL INGREDIENTS. FRESH FLAVORS. NEW IDEAS.</div>
                <h2 style="font-size: 22px; font-weight: 700; color: var(--text-main); margin: 0 0 4px; font-family: 'Playfair Display', Georgia, serif;">Ingredient Innovation</h2>
                <p style="font-size: 13.5px; color: var(--text-muted); margin: 0;">Discover local fruits, spices, and natural sweeteners we test in our kitchen.</p>
            </div>
            <button type="button" class="btn btn-primary" onclick="openNewIngredientModal()" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; font-weight: 700;">
                <svg fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path d="M12 4v16m8-8H4"></path></svg>
                Add Ingredient Category
            </button>
        </div>

        <!-- Action Bar -->
        <div class="action-bar" style="margin-bottom: 24px;">
            <div class="action-bar-left">
                <div class="topbar-search" style="display: block; width: 280px; position: relative;">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--text-muted); pointer-events: none;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="ingSearchInput" placeholder="Search ingredient categories..." oninput="filterIngredientsView()" style="width: 100%; border-radius: 8px; padding-left: 36px; padding-top: 9px; padding-bottom: 9px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px;">
                </div>
            </div>

            <div class="action-bar-right">
                <select id="ingStatusSelect" onchange="filterIngredientsView()" style="padding: 7px 12px; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-main); font-size: 13px; font-family: var(--font-sans); outline: none;">
                    <option value="">All Statuses</option>
                    <option value="published">Visible</option>
                    <option value="draft">Hidden</option>
                </select>

                <div class="view-toggle">
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'grid' ? 'active' : ''; ?>" onclick="setLabViewMode('grid')" title="Grid View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </button>
                    <button type="button" class="view-toggle-btn <?php echo $viewMode === 'list' ? 'active' : ''; ?>" onclick="setLabViewMode('list')" title="List View">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>

                <span id="ingCountBadge" style="font-size: 12.5px; color: var(--text-muted); font-weight: 600; white-space: nowrap;">
                    Showing <?php echo count($ingredientsList); ?> items
                </span>
            </div>
        </div>

        <?php if (empty($ingredientsList)): ?>
            <div class="form-card" style="padding: 60px; text-align: center; color: var(--text-light);">
                <p style="font-size: 16px; font-weight: 500;">No ingredient categories found. Click "+ Add Ingredient Category" to create one!</p>
            </div>
        <?php else: ?>
            <!-- GRID VIEW -->
            <div class="blogs-grid-view" id="ingGridContainer" style="<?php echo $viewMode === 'grid' ? '' : 'display:none;'; ?>">
                <?php foreach ($ingredientsList as $ing): 
                    $isPub = (int)$ing['is_active'];
                    $thumbUrl = !empty($ing['image_path']) ? '../' . $ing['image_path'] : '../assets/images/placeholder.jpg';
                ?>
                    <div class="blog-card ing-card-item" id="ing-card-<?php echo $ing['id']; ?>"
                         data-title="<?php echo strtolower(htmlspecialchars($ing['title'])); ?>"
                         data-desc="<?php echo strtolower(htmlspecialchars(strip_tags($ing['description']))); ?>"
                         data-varietals="<?php echo strtolower(htmlspecialchars($ing['key_varietals'])); ?>"
                         data-status="<?php echo $isPub ? 'published' : 'draft'; ?>">
                        <div class="blog-card-image">
                            <div class="blog-card-badges">
                                <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                      id="badge-ing-grid-<?php echo $ing['id']; ?>"
                                      style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                    <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                    <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                </span>
                            </div>
                            <img src="<?php echo htmlspecialchars($thumbUrl); ?>" alt="<?php echo htmlspecialchars($ing['title']); ?>" onerror="this.onerror=null; this.src='../assets/images/placeholder.jpg';" loading="lazy">
                            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.65); color: #fff; padding: 2px 7px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                <?php echo htmlspecialchars($ing['key_varietals'] ?: 'INNOVATION'); ?>
                            </div>
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-card-category">Ingredient Category</span>
                            <h3 class="blog-card-title"><?php echo htmlspecialchars($ing['title']); ?></h3>
                            <p class="blog-card-excerpt"><?php echo htmlspecialchars(strip_tags($ing['description']) ?: 'No description entered.'); ?></p>
                            <div class="blog-card-footer">
                                <span class="blog-card-date">Sort Order: #<?php echo (int)$ing['sort_order']; ?></span>
                                <div class="blog-card-actions">
                                    <button type="button" class="btn btn-outline btn-sm" style="padding:6px 12px;" onclick='editIngredient(<?php echo json_encode($ing, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-outline btn-sm btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="ingredient"
                                            data-id="<?php echo $ing['id']; ?>" 
                                            onclick="toggleLabItem('ingredient', <?php echo $ing['id']; ?>, this)" 
                                            title="<?php echo $isPub ? 'Click to Hide from site' : 'Click to Make Visible on site'; ?>"
                                            style="padding:6px 10px; display:inline-flex; align-items:center; gap:5px; font-weight:600; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <div style="position: relative; display: inline-block;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleMoreMenu(event, this)" style="padding: 6px 8px; min-width: 0;">&bull;&bull;&bull;</button>
                                        <div class="more-actions-dropdown">
                                            <a href="../innovation-lab.php#ingredient-innovation" target="_blank">View on Site &nearr;</a>
                                            <form method="POST" onsubmit="return confirm('Delete this ingredient category?');" class="delete-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                                <input type="hidden" name="action" value="delete_ingredient">
                                                <input type="hidden" name="ing_id" value="<?php echo (int)$ing['id']; ?>">
                                                <button type="submit" class="delete-btn">Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- LIST VIEW -->
            <div class="table-container" id="ingListContainer" style="<?php echo $viewMode === 'list' ? '' : 'display:none;'; ?> background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; margin-bottom: 30px;">
                <table id="ingredientsTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px;">
                    <thead style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                        <tr>
                            <th style="padding: 12px 18px; width: 64px;">Image</th>
                            <th style="padding: 12px 18px;">Category Title</th>
                            <th style="padding: 12px 18px;">Description</th>
                            <th style="padding: 12px 18px;">Link / Action</th>
                            <th style="padding: 12px 18px; width: 80px;">Order</th>
                            <th style="padding: 12px 18px; width: 110px;">Status</th>
                            <th style="padding: 12px 18px; width: 170px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredientsList as $ing): 
                            $isPub = (int)$ing['is_active'];
                        ?>
                            <tr class="ing-row" id="ing-row-<?php echo $ing['id']; ?>"
                                data-title="<?php echo strtolower(htmlspecialchars($ing['title'])); ?>"
                                data-desc="<?php echo strtolower(htmlspecialchars(strip_tags($ing['description']))); ?>"
                                data-varietals="<?php echo strtolower(htmlspecialchars($ing['key_varietals'])); ?>"
                                data-status="<?php echo $isPub ? 'published' : 'draft'; ?>"
                                style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 12px 18px;">
                                    <img src="../<?php echo htmlspecialchars($ing['image_path'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-color);" alt="" onerror="this.src='../assets/images/placeholder.jpg'">
                                </td>
                                <td style="padding: 12px 18px; font-weight: 700; color: var(--text-main);">
                                    <?php echo htmlspecialchars($ing['title']); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12.5px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars(strip_tags($ing['description'])); ?>
                                </td>
                                <td style="padding: 12px 18px; font-size: 12px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($ing['link_url']); ?>
                                </td>
                                <td style="padding: 12px 18px;"><?php echo (int)$ing['sort_order']; ?></td>
                                <td style="padding: 12px 18px;">
                                    <span class="status-badge <?php echo $isPub ? 'published' : 'draft'; ?>" 
                                          id="badge-ing-list-<?php echo $ing['id']; ?>"
                                          style="<?php echo $isPub ? '' : 'background:rgba(217, 119, 6, 0.15); color:#d97706; border-color:rgba(217, 119, 6, 0.3);'; ?>">
                                        <span class="badge-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="badge-label"><?php echo $isPub ? 'Visible' : 'Hidden'; ?></span>
                                    </span>
                                </td>
                                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-sm btn-outline" onclick='editIngredient(<?php echo json_encode($ing, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)'>Edit</button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline btn-visibility-toggle <?php echo $isPub ? 'btn-status-visible' : 'btn-status-hidden'; ?>" 
                                            data-item-type="ingredient"
                                            data-id="<?php echo $ing['id']; ?>" 
                                            onclick="toggleLabItem('ingredient', <?php echo $ing['id']; ?>, this)" 
                                            style="padding: 4px 8px; font-size: 12px; <?php echo $isPub ? 'color:#15803d;' : 'color:#d97706;'; ?>">
                                        <span class="toggle-icon"><?php echo $isPub ? '👁️' : '🔒'; ?></span>
                                        <span class="toggle-text"><?php echo $isPub ? 'Hide' : 'Show'; ?></span>
                                    </button>
                                    <form method="POST" style="display: inline-block; margin: 0;" onsubmit="return confirm('Delete this ingredient category?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                                        <input type="hidden" name="action" value="delete_ingredient">
                                        <input type="hidden" name="ing_id" value="<?php echo (int)$ing['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php elseif ($activeTab === 'future'): ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_settings">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px;">
                
                <!-- Hero Section Settings Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">1. Hero Banner — Innovation Lab</h3>
                    
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hero Eyebrow</label>
                        <input type="text" name="lab_hero_eyebrow" value="<?php echo htmlspecialchars($settings['lab_hero_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Main Title</label>
                        <input type="text" name="lab_hero_title" value="<?php echo htmlspecialchars($settings['lab_hero_title']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Subtitle Description</label>
                        <textarea name="lab_hero_subtitle" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);"><?php echo htmlspecialchars($settings['lab_hero_subtitle']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Text</label>
                            <input type="text" name="lab_hero_cta_text" value="<?php echo htmlspecialchars($settings['lab_hero_cta_text']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Link</label>
                            <input type="text" name="lab_hero_cta_link" value="<?php echo htmlspecialchars($settings['lab_hero_cta_link']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sticky Note Text (Mantra pinned on wall)</label>
                        <textarea name="lab_sticky_note" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-style: italic; background: var(--bg-card); color: var(--text-main);"><?php echo htmlspecialchars($settings['lab_sticky_note']); ?></textarea>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Hero Image</label>
                        <input type="text" name="lab_hero_image" value="<?php echo htmlspecialchars($settings['lab_hero_image']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 6px; background: var(--bg-card); color: var(--text-main);">
                        <input type="file" name="hero_image_file" accept="image/*" style="font-size: 12px;">
                    </div>
                </div>

                <!-- Future of Chocolate Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">2. Future of Chocolate Section</h3>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Eyebrow</label>
                        <input type="text" name="lab_future_eyebrow" value="<?php echo htmlspecialchars($settings['lab_future_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Section Heading</label>
                        <input type="text" name="lab_future_heading" value="<?php echo htmlspecialchars($settings['lab_future_heading']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Paragraph Content</label>
                        <textarea name="lab_future_text" rows="3" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);"><?php echo htmlspecialchars($settings['lab_future_text']); ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Button Text</label>
                            <input type="text" name="lab_future_cta_text" value="<?php echo htmlspecialchars($settings['lab_future_cta_text']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">CTA Link</label>
                            <input type="text" name="lab_future_cta_link" value="<?php echo htmlspecialchars($settings['lab_future_cta_link']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Landscape Photo</label>
                        <input type="text" name="lab_future_image" value="<?php echo htmlspecialchars($settings['lab_future_image']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; margin-bottom: 6px; background: var(--bg-card); color: var(--text-main);">
                        <input type="file" name="future_image_file" accept="image/*" style="font-size: 12px;">
                    </div>
                </div>

                <!-- Ingredient Innovation Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">3. Ingredient Innovation Section</h3>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Eyebrow</label>
                        <input type="text" name="lab_ingredient_eyebrow" value="<?php echo htmlspecialchars($settings['lab_ingredient_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Section Heading</label>
                        <input type="text" name="lab_ingredient_heading" value="<?php echo htmlspecialchars($settings['lab_ingredient_heading']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>
                </div>

                <!-- What If? & Failed Batches Card -->
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: 10px; background: var(--bg-card);">
                    <h3 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0 0 16px;">4. What if? &amp; From a Failed Batch to a New Idea Headers</h3>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">What If? Eyebrow</label>
                        <input type="text" name="lab_what_if_eyebrow" value="<?php echo htmlspecialchars($settings['lab_what_if_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">What If? Section Heading</label>
                        <input type="text" name="lab_what_if_heading" value="<?php echo htmlspecialchars($settings['lab_what_if_heading']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Failed Batch Eyebrow</label>
                        <input type="text" name="lab_failed_batch_eyebrow" value="<?php echo htmlspecialchars($settings['lab_failed_batch_eyebrow']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Failed Batch Section Heading</label>
                        <input type="text" name="lab_failed_batch_heading" value="<?php echo htmlspecialchars($settings['lab_failed_batch_heading']); ?>" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
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
                                <td style="padding: 12px 18px; text-align: right; white-space: nowrap;">
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
     MODAL: ADD / EDIT EXPERIMENT (STEPPER-TABBED & LIVE PREVIEW SYSTEM)
     ========================================================================= -->
<div id="experimentModal" class="lab-modal-backdrop" onclick="handleBackdropClick(event, 'experimentModal')">
    <div class="lab-modal-dialog">
        <!-- Modal Top Header -->
        <div class="lab-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(199, 166, 106, 0.15); color: var(--gold, #C7A66A); display: flex; align-items: center; justify-content: center; font-size: 16px;">🧪</div>
                <div>
                    <h2 id="expModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0; color: var(--text-main);">Create New Lab Experiment</h2>
                    <div style="font-size: 12px; color: var(--text-muted);">Step-by-step formulation studio &amp; live dossier editor</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('experimentModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light); transition: color 0.15s;" title="Close Modal (Esc)">✕</button>
        </div>

        <!-- Stepper Navigation Bar -->
        <div class="lab-stepper-nav">
            <button type="button" class="lab-step-tab active" data-step="0" onclick="switchExpStep(0)">
                <span>🏷️ 1. Identity &amp; Card</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="1" onclick="switchExpStep(1)">
                <span>🔬 2. Research Pillars</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="2" onclick="switchExpStep(2)">
                <span>⚙️ 3. Science Telemetry</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="3" onclick="switchExpStep(3)">
                <span>📋 4. Full Lab Dossier</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="4" onclick="switchExpStep(4)">
                <span>👁️ 5. Live Preview</span>
            </button>
        </div>
        
        <form method="POST" enctype="multipart/form-data" id="expForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_experiment">
            <input type="hidden" name="experiment_id" id="exp_id" value="0">
            <input type="hidden" name="existing_image_path" id="exp_existing_image" value="">

            <div class="lab-modal-body">
                <!-- STEP 1: IDENTITY & CARD FRONT -->
                <div class="lab-step-panel active" id="exp_step_0">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Experiment Title *</label>
                            <input type="text" name="title" id="exp_title" required placeholder="e.g. CBE × Bean-to-Bar" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" oninput="updateLiveExpPreview()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Category *</label>
                            <select name="category" id="exp_category" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" onchange="updateLiveExpPreview()">
                                <option value="ingredient-innovation">Ingredient Innovation</option>
                                <option value="process-exploration">Process Exploration</option>
                                <option value="formulation-lab" selected>Formulation Lab</option>
                                <option value="sensory-insights">Sensory Insights</option>
                                <option value="stability-shelf-life">Stability &amp; Shelf Life</option>
                                <option value="scale-applications">Scale &amp; Applications</option>
                                <option value="future-of-chocolate">Future of Chocolate</option>
                            </select>
                        </div>
                    </div>

                    <!-- Status Badge & Color -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Status Badge Label</label>
                            <input type="text" name="status_badge" id="exp_status_badge" value="IN PROGRESS" placeholder="e.g. IN PROGRESS, SENSORY TRIALS" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" oninput="updateLiveExpPreview()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Badge Dot Color</label>
                            <select name="status_color" id="exp_status_color" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" onchange="updateLiveExpPreview()">
                                <option value="green">🟢 Green (In Progress / Formulation)</option>
                                <option value="amber">🟡 Amber (Sensory / Stability Trials)</option>
                                <option value="purple">🟣 Purple (Analysis / Insights)</option>
                                <option value="blue">🔵 Blue (Concluded / Scale)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Card Summary / Inquiry Question -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Card Summary / Inquiry Question * (Front of Card)</label>
                        <textarea name="description" id="exp_desc" rows="2" required placeholder="e.g. Can cocoa butter equivalents deliver great texture, snap and flavour in high-cocoa chocolate?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" oninput="updateLiveExpPreview()"></textarea>
                    </div>

                    <!-- Tags & Sort Order -->
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Tags (Card Footer)</label>
                            <input type="text" name="tags" id="exp_tags" placeholder="e.g. FAT SYSTEMS | FORMULATION" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);" oninput="updateLiveExpPreview()">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Display Sort Order</label>
                            <input type="number" name="sort_order" id="exp_sort_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                    </div>

                    <!-- Card Image with Live Thumbnail & Quick Presets -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Card Cover Image</label>
                        <div class="lab-img-preview-box">
                            <img id="exp_preview_thumb" src="../assets/images/placeholder.jpg" class="lab-img-preview-thumb" alt="Preview" onerror="this.src='../assets/images/placeholder.jpg'">
                            <div style="flex: 1;">
                                <input type="file" name="experiment_image" id="exp_file_input" accept="image/*" style="font-size: 12px; margin-bottom: 6px;" onchange="handleFilePreview(this, 'exp_preview_thumb', 'exp_img_text')">
                                <input type="text" name="existing_image_path_text" id="exp_img_text" placeholder="Or enter image URL (e.g. assets/images/lab-cbe.jpg)" style="width: 100%; padding: 7px 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; background: var(--bg-card); color: var(--text-main);" oninput="updateImgPreview(this.value, 'exp_preview_thumb')">
                                
                                <div style="display: flex; align-items: center; gap: 6px; margin-top: 6px; flex-wrap: wrap;">
                                    <span style="font-size: 11px; color: var(--text-muted);">Quick Presets:</span>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('exp_img_text', 'exp_preview_thumb', 'assets/images/lab-cbe.jpg')">CBE Fat</button>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('exp_img_text', 'exp_preview_thumb', 'assets/images/lab-fruits.jpg')">Dried Fruits</button>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('exp_img_text', 'exp_preview_thumb', 'assets/images/lab-khandsari.jpg')">Sugar Trials</button>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('exp_img_text', 'exp_preview_thumb', 'assets/images/lab-spread.jpg')">Spreads</button>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('exp_img_text', 'exp_preview_thumb', 'assets/images/lab-failed-batch.jpg')">Failed Batch</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: 4 RESEARCH PILLARS (WYSIWYG) -->
                <div class="lab-step-panel" id="exp_step_1">
                    <div style="background: var(--bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px; display: flex; align-items: center; justify-content: space-between;">
                            <span>🔬 The 4 Cards (Matching the Website Modal)</span>
                            <span style="font-size: 11px; color: var(--gold, #C7A66A);">Displayed on Live Dossier Popup</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
                            Click <b>• Bullet List</b> or <b>+ 3 Bullets Template</b> to instantly structure observations into crisp, readable points.
                        </div>

                        <!-- 01. Research Question -->
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 4px;">01. Research Question (What question are we trying to answer?)</label>
                            <div id="mount_exp_hypothesis"></div>
                        </div>

                        <!-- 02. What We Discovered -->
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #9A6A38; margin-bottom: 4px;">02. What We Discovered (What happened when we made and tasted the chocolate?)</label>
                            <div id="mount_exp_findings"></div>
                        </div>

                        <!-- 03. Why It Matters -->
                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 4px;">03. Why It Matters (How this helps make better, more delicious chocolate for you)</label>
                            <div id="mount_exp_methodology"></div>
                        </div>

                        <!-- 04. Key Takeaway -->
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #9A6A38; margin-bottom: 4px;">04. Key Takeaway (The main lesson from this test batch)</label>
                            <div id="mount_exp_next_steps"></div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: SCIENCE TELEMETRY -->
                <div class="lab-step-panel" id="exp_step_2">
                    <div style="background: var(--bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                            ⚙️ Chocolate Making Details &amp; Recipe Specs
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
                            Displayed in the top details strip on the live website modal. Click any chip to quick-fill!
                        </div>

                        <!-- Row 1: Terroir Origin & Crystal Polymorph -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Terroir Origin</label>
                                <input type="text" name="origin" id="exp_origin" placeholder="e.g. Idukki Single Estate, Kerala (70% Cacao)" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_origin', 'Idukki Single Estate, Kerala (70% Cacao)')">Kerala Single Estate</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_origin', 'Wayanad Agroforest Organic (75% Cacao)')">Wayanad Organic</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_origin', 'Anamalai Hills Smallholder Harvest')">Anamalai Hills</button>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Melt &amp; Snap (Crystal Polymorph Matrix)</label>
                                <input type="text" name="polymorph" id="exp_polymorph" placeholder="e.g. Beta-V (Form V) Triglyceride Matrix (Tm 34.2°C)" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_polymorph', 'Beta-V (Form V) Triglyceride Matrix (Tm 34.2°C)')">Beta-V (Form V)</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_polymorph', 'Form V High-Stability Seed Matrix')">High-Stability Seed</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_polymorph', 'Beta-VI Annealed Crystal Grid')">Beta-VI Annealed</button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Particle Size Micron & Water Activity -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Smoothness (Particle Size Micron)</label>
                                <input type="text" name="particle_size" id="exp_particle_size" placeholder="e.g. < 18.2 µm (D90 Laser Diffraction)" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_particle_size', '< 18.2 µm (D90 Laser Diffraction)')">&lt; 18.2 µm Laser</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_particle_size', '< 15.0 µm (Ultra-Refined Silky)')">&lt; 15 µm Ultra-Refined</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_particle_size', '20-22 µm (Artisanal Stone Melange)')">20-22 µm Stone Melange</button>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Moisture Level (Water Activity Aw)</label>
                                <input type="text" name="water_activity" id="exp_water_activity" placeholder="e.g. 0.31 Aw (Optimal Moisture Inactivity)" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_water_activity', '0.31 Aw (Optimal Moisture Inactivity)')">0.31 Aw Optimal</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_water_activity', '0.28 Aw (Bone Dry Grid)')">0.28 Aw Bone Dry</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_water_activity', '0.35 Aw (Upper Microbial Limit)')">0.35 Aw Upper Bound</button>
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Conching & Batch Reference -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Conching &amp; Refining Protocol</label>
                                <input type="text" name="conching" id="exp_conching" placeholder="e.g. 36 Hours Longitudinal Dry Conche @ 48°C" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_conching', '36 Hours Longitudinal Dry Conche @ 48°C')">36h Dry Conche @ 48°C</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_conching', '48 Hours Two-Stage Aerated Conche @ 52°C')">48h Two-Stage Conche</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_conching', '24 Hours Low-Temperature Stone Grind @ 42°C')">24h Low-Temp Stone</button>
                                </div>
                            </div>
                            <div>
                                <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Batch Number / Reference ID</label>
                                <input type="text" name="batch_ref" id="exp_batch_ref" placeholder="e.g. RT-EXP-01 · CBE-POLY" style="width: 100%; padding: 7px 10px; font-size: 12.5px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                                <div class="lab-chip-group">
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_batch_ref', 'RT-EXP-01 · CBE-POLY')">RT-EXP-01</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_batch_ref', 'RT-EXP-02 · FERMENT')">RT-EXP-02</button>
                                    <button type="button" class="lab-chip" onclick="setTelemetryPreset('exp_batch_ref', 'RT-EXP-03 · INCLUSION')">RT-EXP-03</button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- STEP 4: DETAILED KITCHEN NOTES (FULL DOSSIER) -->
                <div class="lab-step-panel" id="exp_step_3">
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="display: block; font-size: 13px; font-weight: 700; color: var(--gold, #C7A66A);">
                                📋 Detailed Kitchen Notes &amp; Story (Live HTML)
                            </label>
                            <span style="font-size: 11px; color: var(--text-muted);">Rendered in the detailed dossier card on the website</span>
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 12px;">
                            Use headings, lists, quotes, and colors to format recipe summaries, kitchen measurements, and tasting notes.
                        </div>
                        <div id="mount_exp_full_content"></div>
                    </div>
                </div>

                <!-- STEP 5: LIVE FRONT-END PREVIEW -->
                <div class="lab-step-panel" id="exp_step_4">
                    <div style="font-size: 13px; font-weight: 700; color: var(--gold, #C7A66A); margin-bottom: 4px;">
                        👁️ Live Real-Time Front-End Simulation
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
                        This simulates how your experiment card will appear on the live website before you click Save.
                    </div>

                    <div class="lab-preview-card-wrap">
                        <!-- Front Card Sim -->
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">1. Front-End Grid Card:</div>
                            <div class="lab-live-card-sim">
                                <img id="sim_card_img" src="../assets/images/placeholder.jpg" class="lab-live-card-thumb" alt="" onerror="this.src='../assets/images/placeholder.jpg'">
                                <div class="lab-live-card-inner">
                                    <div id="sim_card_badge" class="lab-live-card-badge">● IN PROGRESS</div>
                                    <div id="sim_card_title" class="lab-live-card-title">CBE × Bean-to-Bar</div>
                                    <div id="sim_card_desc" class="lab-live-card-q">Can cocoa butter equivalents deliver great texture in chocolate?</div>
                                    <div id="sim_card_tags" class="lab-live-card-tags">FAT SYSTEMS | FORMULATION</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Dossier Sim -->
                        <div>
                            <div style="font-size: 11px; font-weight: 700; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase;">2. Dossier Popup Overview:</div>
                            <div class="lab-live-dossier-sim">
                                <div class="lab-sim-quad" style="border-left-color: #166534;">
                                    <div class="lab-sim-quad-title" style="color: #166534;">01. Research Question</div>
                                    <div id="sim_quad_1" style="font-size: 12px; line-height: 1.5;"></div>
                                </div>
                                <div class="lab-sim-quad" style="border-left-color: #9A6A38;">
                                    <div class="lab-sim-quad-title" style="color: #9A6A38;">02. What We Discovered</div>
                                    <div id="sim_quad_2" style="font-size: 12px; line-height: 1.5;"></div>
                                </div>
                                <div class="lab-sim-quad" style="border-left-color: #166534;">
                                    <div class="lab-sim-quad-title" style="color: #166534;">03. Why It Matters</div>
                                    <div id="sim_quad_3" style="font-size: 12px; line-height: 1.5;"></div>
                                </div>
                                <div class="lab-sim-quad" style="border-left-color: #9A6A38;">
                                    <div class="lab-sim-quad-title" style="color: #9A6A38;">04. Key Takeaway</div>
                                    <div id="sim_quad_4" style="font-size: 12px; line-height: 1.5;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sticky Modal Footer -->
            <div class="lab-modal-footer">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <button type="button" id="exp_prev_btn" class="btn btn-outline" style="display: none; padding: 7px 14px; font-size: 12px;" onclick="stepExp(-1)">← Prev Step</button>
                    <button type="button" id="exp_next_btn" class="btn btn-outline" style="padding: 7px 14px; font-size: 12px;" onclick="stepExp(1)">Next Step →</button>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" id="exp_active" value="1" checked>
                        <span>Publish live on site</span>
                    </label>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('experimentModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 22px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                        <span>💾 Save Experiment</span>
                        <span style="font-size: 10px; opacity: 0.7;">(Ctrl+S)</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT FAILED BATCH (UPGRADED STEPPER MODAL)
     ========================================================================= -->
<div id="failedModal" class="lab-modal-backdrop" onclick="handleBackdropClick(event, 'failedModal')">
    <div class="lab-modal-dialog" style="max-width: 880px;">
        <div class="lab-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(220, 38, 38, 0.15); color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 16px;">💥</div>
                <div>
                    <h2 id="failedModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0; color: var(--text-main);">Log Failed Batch — From a Failed Batch to a New Idea</h2>
                    <div style="font-size: 12px; color: var(--text-muted);">Document what went wrong and what we learned to turn mistakes into better chocolate</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('failedModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);" title="Close Modal (Esc)">✕</button>
        </div>

        <div class="lab-stepper-nav">
            <button type="button" class="lab-step-tab active" data-step="0" onclick="switchFailedStep(0)">
                <span>💥 1. Case Study &amp; Photo</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="1" onclick="switchFailedStep(1)">
                <span>🔬 2. The 4 Stages</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="2" onclick="switchFailedStep(2)">
                <span>📖 3. The Full Story</span>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="failedForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_failed_batch">
            <input type="hidden" name="failed_id" id="failed_id" value="0">
            <input type="hidden" name="existing_image_path" id="failed_existing_image" value="">

            <div class="lab-modal-body">
                <!-- STEP 1: Case Identity -->
                <div class="lab-step-panel active" id="failed_step_0">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Case Study Title *</label>
                            <input type="text" name="title" id="failed_title" required placeholder="e.g. Batch 047: Water-Activity Seizure" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Badge Banner</label>
                            <input type="text" name="badge_label" id="failed_badge" value="BECAUSE FAILURE IS DATA." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                    </div>

                    <!-- Photo with live preview & presets -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Case Study Photo</label>
                        <div class="lab-img-preview-box">
                            <img id="failed_preview_thumb" src="../assets/images/lab-failed-batch.jpg" class="lab-img-preview-thumb" alt="Preview" onerror="this.src='../assets/images/lab-failed-batch.jpg'">
                            <div style="flex: 1;">
                                <input type="file" name="failed_image" accept="image/*" style="font-size: 12px; margin-bottom: 6px;" onchange="handleFilePreview(this, 'failed_preview_thumb', 'failed_img_text')">
                                <input type="text" name="existing_image_path_text" id="failed_img_text" placeholder="Or enter image URL (e.g. assets/images/lab-failed-batch.jpg)" style="width: 100%; padding: 7px 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; background: var(--bg-card); color: var(--text-main);" oninput="updateImgPreview(this.value, 'failed_preview_thumb')">
                                <div style="display: flex; gap: 6px; margin-top: 6px;">
                                    <button type="button" class="lab-chip" onclick="setImgPreset('failed_img_text', 'failed_preview_thumb', 'assets/images/lab-failed-batch.jpg')">Failed Batch Macro</button>
                                    <button type="button" class="lab-chip" onclick="setImgPreset('failed_img_text', 'failed_preview_thumb', 'assets/images/lab-cbe.jpg')">CBE Sample</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 20px; align-items: center; margin-top: 14px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_featured" id="failed_featured" value="1">
                            <span>★ Feature on Innovation Lab Homepage</span>
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                            <input type="checkbox" name="is_active" id="failed_active" value="1" checked>
                            <span>Publish live</span>
                        </label>
                    </div>
                </div>

                <!-- STEP 2: The 4 Stages -->
                <div class="lab-step-panel" id="failed_step_1">
                    <div style="background: var(--bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                            🔬 The 4 Stages (Matching the Client Modal Cards)
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
                            1. What We Hoped For → 2. What Went Wrong → 3. Why It Happened → 4. What We Learned
                        </div>

                        <!-- 1. The Goal -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 2px;">1. What We Hoped For (The Intended Goal) *</label>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">What did you set out to achieve with flavor and texture?</div>
                            <div id="mount_failed_hypothesis"></div>
                        </div>

                        <!-- 2. What Went Wrong -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #DC2626; margin-bottom: 2px;">2. What Went Wrong (What Happened?) *</label>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">How the chocolate changed unexpectedly (e.g. flavor disappeared, paste seized, bloom)</div>
                            <div id="mount_failed_what_happened"></div>
                        </div>

                        <!-- 3. Why It Happened -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #D97706; margin-bottom: 2px;">3. Why It Happened (The Simple Science) *</label>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">The physical reason behind the problem (e.g. moisture pulled into fat matrix)</div>
                            <div id="mount_failed_why"></div>
                        </div>

                        <!-- 4. What We Learned -->
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #059669; margin-bottom: 2px;">4. What We Learned (The Lesson &amp; The Fix) *</label>
                            <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">How we fixed the recipe for our next batch (e.g. pre-coat inclusions with cocoa butter)</div>
                            <div id="mount_failed_what_next"></div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Full Narrative Story -->
                <div class="lab-step-panel" id="failed_step_2">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 13px; font-weight: 700; color: var(--gold, #C7A66A); margin-bottom: 2px;">
                            📖 The Full Story Behind This Test Batch (Field Notes &amp; Story)
                        </label>
                        <div style="font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">The complete story and kitchen notes behind this test batch as displayed in the full modal dossier.</div>
                        <div id="mount_failed_full_story"></div>
                    </div>
                </div>
            </div>

            <div class="lab-modal-footer">
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="failed_prev_btn" class="btn btn-outline" style="display: none; padding: 7px 14px; font-size: 12px;" onclick="stepFailed(-1)">← Prev Step</button>
                    <button type="button" id="failed_next_btn" class="btn btn-outline" style="padding: 7px 14px; font-size: 12px;" onclick="stepFailed(1)">Next Step →</button>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('failedModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 22px; font-weight: 700;">💾 Save Failed Batch</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT WHAT IF? QUESTION
     ========================================================================= -->
<div id="whatIfModal" class="lab-modal-backdrop" onclick="handleBackdropClick(event, 'whatIfModal')">
    <div class="lab-modal-dialog" style="max-width: 540px;">
        <div class="lab-modal-header">
            <h2 id="whatIfModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0; color: var(--text-main);">Add What If? Question</h2>
            <button type="button" onclick="closeModal('whatIfModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        </div>
        
        <form method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_what_if">
            <input type="hidden" name="whatif_id" id="whatif_id" value="0">

            <div class="lab-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Number Label *</label>
                        <input type="text" name="num_label" id="whatif_num" value="01" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Display Sort Order</label>
                        <input type="number" name="sort_order" id="whatif_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Question Text *</label>
                    <textarea name="question" id="whatif_question" rows="3" required placeholder="e.g. Can jackfruit flour replace refined sugar without altering the snap of dark chocolate?" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);"></textarea>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Link URL / Section Anchor</label>
                    <input type="text" name="link_url" id="whatif_link" value="#currently-in-lab" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                </div>

                <label style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="whatif_active" value="1" checked>
                    <span>Publish live in "What If?" exploration grid</span>
                </label>
            </div>

            <div class="lab-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('whatIfModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT INGREDIENT CATEGORY (STEPPER MODAL)
     ========================================================================= -->
<div id="ingredientModal" class="lab-modal-backdrop" onclick="handleBackdropClick(event, 'ingredientModal')">
    <div class="lab-modal-dialog" style="max-width: 820px;">
        <div class="lab-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(22, 101, 52, 0.15); color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 16px;">🌱</div>
                <div>
                    <h2 id="ingredientModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0; color: var(--text-main);">Ingredient Innovation Category</h2>
                    <div style="font-size: 12px; color: var(--text-muted);">Explore regional botanicals, alternative sugars, and indigenous fat systems</div>
                </div>
            </div>
            <button type="button" onclick="closeModal('ingredientModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        </div>

        <div class="lab-stepper-nav">
            <button type="button" class="lab-step-tab active" data-step="0" onclick="switchIngStep(0)">
                <span>🌱 1. Botanical Identity</span>
            </button>
            <button type="button" class="lab-step-tab" data-step="1" onclick="switchIngStep(1)">
                <span>🔬 2. 4 Botanical Quadrants</span>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data" id="ingForm" style="display: flex; flex-direction: column; flex: 1; overflow: hidden; margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_ingredient">
            <input type="hidden" name="ing_id" id="ing_id" value="0">
            <input type="hidden" name="existing_image_path" id="ing_existing_image" value="">

            <div class="lab-modal-body">
                <!-- STEP 1: Botanical Identity -->
                <div class="lab-step-panel active" id="ing_step_0">
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Category Title *</label>
                            <input type="text" name="title" id="ing_title" required placeholder="e.g. Native Fruits &amp; Inclusions" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sort Order</label>
                            <input type="number" name="sort_order" id="ing_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                        </div>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Link URL / Hash</label>
                        <input type="text" name="link_url" id="ing_link" value="#currently-in-lab" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Category Image</label>
                        <div class="lab-img-preview-box">
                            <img id="ing_preview_thumb" src="../assets/images/placeholder.jpg" class="lab-img-preview-thumb" alt="Preview" onerror="this.src='../assets/images/placeholder.jpg'">
                            <div style="flex: 1;">
                                <input type="file" name="ingredient_image" accept="image/*" style="font-size: 12px; margin-bottom: 6px;" onchange="handleFilePreview(this, 'ing_preview_thumb', 'ing_img_text')">
                                <input type="text" name="existing_image_path_text" id="ing_img_text" placeholder="Or enter image URL (e.g. assets/images/lab-fruits.jpg)" style="width: 100%; padding: 7px 10px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 12px; background: var(--bg-card); color: var(--text-main);" oninput="updateImgPreview(this.value, 'ing_preview_thumb')">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: 4 Botanical Quadrants -->
                <div class="lab-step-panel" id="ing_step_1">
                    <div style="background: var(--bg-subtle, rgba(0,0,0,0.02)); border: 1px solid var(--border-color); border-radius: 10px; padding: 18px; margin-bottom: 16px;">
                        <div style="font-size: 14px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">
                            🔬 4 Botanical Modal Quadrants
                        </div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
                            Configure the 4 structured scientific cards displayed in the pop-up modal.
                        </div>

                        <!-- 1. Description -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 4px;">01. Ingredient Profile (What makes this ingredient special?)</label>
                            <div id="mount_ing_desc"></div>
                        </div>

                        <!-- 2. Applications -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #9A6A38; margin-bottom: 4px;">02. Ways to Use in Chocolate (Where and how we use it in bars, ganaches, and spreads)</label>
                            <div id="mount_ing_applications"></div>
                        </div>

                        <!-- 3. Science Behavior -->
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #166534; margin-bottom: 4px;">03. What to Watch Out For (What challenges arise when blending with melted chocolate?)</label>
                            <div id="mount_ing_science_behavior"></div>
                        </div>

                        <!-- 4. Lab Notes -->
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 700; color: #9A6A38; margin-bottom: 4px;">04. Key Takeaway (The single most important tip for working with this ingredient)</label>
                            <div id="mount_ing_lab_notes"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lab-modal-footer">
                <div style="display: flex; gap: 8px;">
                    <button type="button" id="ing_prev_btn" class="btn btn-outline" style="display: none; padding: 7px 14px; font-size: 12px;" onclick="stepIng(-1)">← Prev Step</button>
                    <button type="button" id="ing_next_btn" class="btn btn-outline" style="padding: 7px 14px; font-size: 12px;" onclick="stepIng(1)">Next Step →</button>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('ingredientModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding: 8px 22px; font-weight: 700;">💾 Save Ingredient</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     MODAL: ADD / EDIT FUTURE PILLAR
     ========================================================================= -->
<div id="pillarModal" class="lab-modal-backdrop" onclick="handleBackdropClick(event, 'pillarModal')">
    <div class="lab-modal-dialog" style="max-width: 500px;">
        <div class="lab-modal-header">
            <h2 id="pillarModalTitle" style="font-family: var(--font-heading); font-size: 22px; margin: 0; color: var(--text-main);">Future Pillar</h2>
            <button type="button" onclick="closeModal('pillarModal')" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-light);">✕</button>
        </div>
        
        <form method="POST" style="margin: 0;">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="action" value="save_pillar">
            <input type="hidden" name="pillar_id" id="pillar_id" value="0">

            <div class="lab-modal-body">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Pillar Title *</label>
                    <input type="text" name="title" id="pillar_title" required placeholder="e.g. REGENERATIVE CACAO" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Description / Context</label>
                    <textarea name="description" id="pillar_desc" rows="2" placeholder="Agroforestry initiatives, biodiversity..." style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);"></textarea>
                </div>

                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Link URL</label>
                    <input type="text" name="link_url" id="pillar_link" value="#future-ideas" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                </div>

                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: var(--text-muted); margin-bottom: 4px;">Sort Order</label>
                    <input type="number" name="sort_order" id="pillar_order" value="0" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--bg-card); color: var(--text-main);">
                </div>
            </div>

            <div class="lab-modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('pillarModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Pillar</button>
            </div>
        </form>
    </div>
</div>

<!-- =========================================================================
     JAVASCRIPT: RICH EDITORS, TELEMETRY, PREVIEWS & STEPPER CONTROLLERS
     ========================================================================= -->
<script>
// Global Registry of all Rich Text Editors
window.LAB_RICH_EDITORS = {};

/**
 * Creates an enhanced native WYSIWYG editor with bullet formatting, templates, and color swatches
 */
function createRichEditor(mountId, textareaName, textareaId, options = {}) {
    const mountEl = document.getElementById(mountId);
    if (!mountEl) return null;

    const isFull = options.isFull !== false;
    const minHeight = options.minHeight || (isFull ? '180px' : '75px');
    const placeholder = options.placeholder || 'Enter text... Use • Bullet List for clean points.';

    const wrapper = document.createElement('div');
    wrapper.className = 'lab-rich-wrapper';

    // Build Toolbar
    let toolbarHtml = '<div class="lab-rich-toolbar">';

    if (isFull) {
        toolbarHtml += `
            <select class="lab-rich-select" data-role="formatBlock" title="Heading & Style">
                <option value="p">Normal Text</option>
                <option value="h3">Heading 3 (Section)</option>
                <option value="h4">Heading 4 (Subhead)</option>
                <option value="blockquote">Quote / Callout</option>
            </select>
            <div class="lab-rich-sep"></div>
        `;
    }

    toolbarHtml += `
        <button type="button" class="lab-rich-btn btn-bullet-highlight" data-cmd="insertUnorderedList" title="• Bullet Points List">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><circle cx="3.5" cy="6" r="1.5" fill="currentColor"></circle><circle cx="3.5" cy="12" r="1.5" fill="currentColor"></circle><circle cx="3.5" cy="18" r="1.5" fill="currentColor"></circle></svg>
            <span>• Bullet List</span>
        </button>
        <button type="button" class="lab-rich-btn" data-cmd="insertOrderedList" title="1. Numbered List">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="10" y1="6" x2="21" y2="6"></line><line x1="10" y1="12" x2="21" y2="12"></line><line x1="10" y1="18" x2="21" y2="18"></line><path d="M4 6h1v4M4 10h2"></path></svg>
            <span>1. Numbers</span>
        </button>
        <button type="button" class="lab-rich-btn btn-template-highlight" data-role="insertBulletsTemplate" title="Insert 3-Bullet Points Template">
            <span>+ 3 Bullets</span>
        </button>
        <div class="lab-rich-sep"></div>

        <button type="button" class="lab-rich-btn" data-cmd="bold" title="Bold (Ctrl+B)"><b>B</b></button>
        <button type="button" class="lab-rich-btn" data-cmd="italic" title="Italic (Ctrl+I)"><i>I</i></button>
        <button type="button" class="lab-rich-btn" data-cmd="underline" title="Underline (Ctrl+U)"><u>U</u></button>
    `;

    if (isFull) {
        toolbarHtml += `
            <button type="button" class="lab-rich-btn" data-cmd="strikeThrough" title="Strikethrough"><s>S</s></button>
            <div class="lab-rich-sep"></div>
            <button type="button" class="lab-rich-btn" data-color="#C7A66A" title="Gold Accent" style="color:#C7A66A; font-weight:800;">Gold</button>
            <button type="button" class="lab-rich-btn" data-color="#166534" title="Forest Green Accent" style="color:#166534; font-weight:800;">Green</button>
            <button type="button" class="lab-rich-btn" data-color="#9A6A38" title="Bronze Accent" style="color:#9A6A38; font-weight:800;">Bronze</button>
            <div class="lab-rich-sep"></div>
            <button type="button" class="lab-rich-btn" data-role="link" title="Insert Hyperlink">🔗 Link</button>
        `;
    }

    toolbarHtml += `
        <button type="button" class="lab-rich-btn" data-cmd="removeFormat" title="Clear Formatting">Tx</button>
        
        <button type="button" class="lab-rich-mode-btn" data-role="toggleMode" title="Switch between Visual WYSIWYG and HTML Source Code">
            👁️ Visual
        </button>
    </div>
    `;

    wrapper.innerHTML = toolbarHtml;

    // Body: Visual ContentEditable Div
    const visualBody = document.createElement('div');
    visualBody.className = 'lab-rich-body';
    visualBody.contentEditable = 'true';
    visualBody.spellcheck = true;
    visualBody.style.minHeight = minHeight;
    visualBody.setAttribute('placeholder', placeholder);

    // Backing Textarea (sent with POST form)
    const sourceTextarea = document.createElement('textarea');
    sourceTextarea.className = 'lab-rich-textarea-source';
    sourceTextarea.name = textareaName;
    sourceTextarea.id = textareaId;
    sourceTextarea.style.minHeight = minHeight;

    wrapper.appendChild(visualBody);
    wrapper.appendChild(sourceTextarea);
    mountEl.innerHTML = '';
    mountEl.appendChild(wrapper);

    let isCodeMode = false;
    const modeBtn = wrapper.querySelector('[data-role="toggleMode"]');

    // Toolbar Event Delegation
    wrapper.querySelector('.lab-rich-toolbar').addEventListener('click', function(e) {
        const btn = e.target.closest('button');
        if (!btn) return;

        // Mode switch
        if (btn.dataset.role === 'toggleMode') {
            e.preventDefault();
            isCodeMode = !isCodeMode;
            if (isCodeMode) {
                sourceTextarea.value = visualBody.innerHTML;
                visualBody.style.display = 'none';
                sourceTextarea.style.display = 'block';
                sourceTextarea.focus();
                btn.innerHTML = '&lt;/&gt; Code';
                btn.style.color = 'var(--gold, #C7A66A)';
            } else {
                visualBody.innerHTML = sourceTextarea.value;
                sourceTextarea.style.display = 'none';
                visualBody.style.display = 'block';
                visualBody.focus();
                btn.innerHTML = '👁️ Visual';
                btn.style.color = '';
            }
            return;
        }

        // Insert Bullets Template
        if (btn.dataset.role === 'insertBulletsTemplate') {
            e.preventDefault();
            visualBody.focus();
            const templateHtml = '<ul><li>First key observation or metric...</li><li>Second key finding or measurement...</li><li>Third actionable takeaway...</li></ul>';
            if (isCodeMode) {
                sourceTextarea.value += templateHtml;
                visualBody.innerHTML = sourceTextarea.value;
            } else {
                document.execCommand('insertHTML', false, templateHtml);
                sourceTextarea.value = visualBody.innerHTML;
            }
            updateLiveExpPreview();
            return;
        }

        if (isCodeMode) {
            alert('Switch to Visual mode to use toolbar formatting buttons, or edit HTML tags directly in code view.');
            return;
        }

        visualBody.focus();

        // Commands
        if (btn.dataset.cmd) {
            e.preventDefault();
            document.execCommand(btn.dataset.cmd, false, null);
            sourceTextarea.value = visualBody.innerHTML;
            updateLiveExpPreview();
            return;
        }

        // Color swatches
        if (btn.dataset.color) {
            e.preventDefault();
            document.execCommand('foreColor', false, btn.dataset.color);
            sourceTextarea.value = visualBody.innerHTML;
            updateLiveExpPreview();
            return;
        }

        // Link
        if (btn.dataset.role === 'link') {
            e.preventDefault();
            const url = prompt('Enter destination link URL (e.g. #currently-in-lab or https://):', 'https://');
            if (url) {
                document.execCommand('createLink', false, url);
                sourceTextarea.value = visualBody.innerHTML;
                updateLiveExpPreview();
            }
            return;
        }
    });

    // Format block dropdown
    const formatSelect = wrapper.querySelector('[data-role="formatBlock"]');
    if (formatSelect) {
        formatSelect.addEventListener('change', function() {
            if (isCodeMode) return;
            visualBody.focus();
            const tag = this.value;
            if (tag === 'p') {
                document.execCommand('formatBlock', false, '<p>');
            } else if (tag === 'h3') {
                document.execCommand('formatBlock', false, '<h3>');
            } else if (tag === 'h4') {
                document.execCommand('formatBlock', false, '<h4>');
            } else if (tag === 'blockquote') {
                document.execCommand('formatBlock', false, '<blockquote>');
            }
            sourceTextarea.value = visualBody.innerHTML;
            updateLiveExpPreview();
        });
    }

    // Input Sync
    visualBody.addEventListener('input', function() {
        sourceTextarea.value = visualBody.innerHTML;
        updateLiveExpPreview();
    });

    sourceTextarea.addEventListener('input', function() {
        visualBody.innerHTML = sourceTextarea.value;
        updateLiveExpPreview();
    });

    const editorObj = {
        textareaId,
        textareaName,
        visualBody,
        sourceTextarea,
        setHtml: function(html) {
            visualBody.innerHTML = html || '';
            sourceTextarea.value = html || '';
            if (isCodeMode) {
                isCodeMode = false;
                visualBody.style.display = 'block';
                sourceTextarea.style.display = 'none';
                if (modeBtn) {
                    modeBtn.innerHTML = '👁️ Visual';
                    modeBtn.style.color = '';
                }
            }
        },
        getHtml: function() {
            if (isCodeMode) return sourceTextarea.value;
            return visualBody.innerHTML;
        },
        sync: function() {
            if (isCodeMode) {
                visualBody.innerHTML = sourceTextarea.value;
            } else {
                sourceTextarea.value = visualBody.innerHTML;
            }
        }
    };

    window.LAB_RICH_EDITORS[textareaId] = editorObj;
    return editorObj;
}

// Helper to set editor content safely
function setRichEditorContent(editorId, html) {
    if (window.LAB_RICH_EDITORS[editorId]) {
        window.LAB_RICH_EDITORS[editorId].setHtml(html);
    } else {
        const el = document.getElementById(editorId);
        if (el) el.value = html || '';
    }
}

function getRichEditorContent(editorId) {
    if (window.LAB_RICH_EDITORS[editorId]) {
        return window.LAB_RICH_EDITORS[editorId].getHtml();
    }
    const el = document.getElementById(editorId);
    return el ? el.value : '';
}

// Initialize Editors on DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    // 1. Experiment Editors (Matching Currently in the Lab Modal Cards)
    createRichEditor('mount_exp_hypothesis', 'hypothesis', 'exp_hypothesis', { isFull: false, minHeight: '68px', placeholder: 'What question are we trying to answer with this test batch?' });
    createRichEditor('mount_exp_findings', 'findings', 'exp_findings', { isFull: false, minHeight: '68px', placeholder: 'What happened when we made and tasted the chocolate?' });
    createRichEditor('mount_exp_methodology', 'methodology', 'exp_methodology', { isFull: false, minHeight: '68px', placeholder: 'How this helps make better, more delicious chocolate for you...' });
    createRichEditor('mount_exp_next_steps', 'next_steps', 'exp_next_steps', { isFull: false, minHeight: '68px', placeholder: 'The main lesson from this test batch...' });
    createRichEditor('mount_exp_full_content', 'full_content', 'exp_full_content', { isFull: true, minHeight: '220px', placeholder: 'Full kitchen notes, recipe details, tasting notes, bullet points...' });

    // 2. Failed Batch Editors (Matching From a Failed Batch to a New Idea Cards)
    createRichEditor('mount_failed_hypothesis', 'hypothesis', 'failed_hypothesis', { isFull: false, minHeight: '60px', placeholder: 'What we wanted to achieve with flavor and texture...' });
    createRichEditor('mount_failed_what_happened', 'what_happened', 'failed_what_happened', { isFull: false, minHeight: '60px', placeholder: 'What happened unexpectedly (flavor lost, chocolate seized, bloom)...' });
    createRichEditor('mount_failed_why', 'why_it_happened', 'failed_why', { isFull: false, minHeight: '60px', placeholder: 'The simple science of why it happened (moisture in fat matrix, sugar crystallization)...' });
    createRichEditor('mount_failed_what_next', 'what_next', 'failed_what_next', { isFull: false, minHeight: '60px', placeholder: 'The lesson and how we fixed the recipe for our next batch...' });
    createRichEditor('mount_failed_full_story', 'full_story', 'failed_full_story', { isFull: true, minHeight: '200px', placeholder: 'The full story and kitchen notes behind this test batch...' });

    // 3. Ingredient Category Editors (Matching Ingredient Innovation Cards)
    createRichEditor('mount_ing_desc', 'description', 'ing_desc', { isFull: false, minHeight: '65px', placeholder: 'What makes this ingredient special and unique...' });
    createRichEditor('mount_ing_applications', 'applications', 'ing_applications', { isFull: false, minHeight: '65px', placeholder: 'Where and how we use it in bars, ganaches, and spreads...' });
    createRichEditor('mount_ing_science_behavior', 'science_behavior', 'ing_science_behavior', { isFull: false, minHeight: '65px', placeholder: 'What challenges arise when blending with melted chocolate...' });
    createRichEditor('mount_ing_lab_notes', 'lab_notes', 'ing_lab_notes', { isFull: false, minHeight: '65px', placeholder: 'The single most important tip for working with this ingredient...' });

    // Pre-submit sync
    ['expForm', 'failedForm', 'ingForm'].forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function() {
                Object.values(window.LAB_RICH_EDITORS).forEach(ed => {
                    if (ed && ed.sync) ed.sync();
                });
            });
        }
    });

    // Keyboard Shortcuts: Ctrl+S saves active form, Esc closes modal
    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            const openModal = document.querySelector('.lab-modal-backdrop.open');
            if (openModal) {
                e.preventDefault();
                const form = openModal.querySelector('form');
                if (form) {
                    Object.values(window.LAB_RICH_EDITORS).forEach(ed => {
                        if (ed && ed.sync) ed.sync();
                    });
                    form.submit();
                }
            }
        }
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.lab-modal-backdrop.open');
            if (openModal) closeModal(openModal.id);
        }
    });
});

/* =========================================================================
   STEPPER NAVIGATION CONTROLLERS
   ========================================================================= */
let currentExpStep = 0;
function switchExpStep(stepIndex) {
    currentExpStep = stepIndex;
    for (let i = 0; i <= 4; i++) {
        const panel = document.getElementById('exp_step_' + i);
        if (panel) panel.classList.toggle('active', i === stepIndex);
        const tab = document.querySelector('#experimentModal .lab-step-tab[data-step="' + i + '"]');
        if (tab) tab.classList.toggle('active', i === stepIndex);
    }
    const prevBtn = document.getElementById('exp_prev_btn');
    const nextBtn = document.getElementById('exp_next_btn');
    if (prevBtn) prevBtn.style.display = stepIndex > 0 ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = stepIndex < 4 ? 'inline-flex' : 'none';
    if (stepIndex === 4) updateLiveExpPreview();
}

function stepExp(delta) {
    const target = Math.max(0, Math.min(4, currentExpStep + delta));
    switchExpStep(target);
}

let currentFailedStep = 0;
function switchFailedStep(stepIndex) {
    currentFailedStep = stepIndex;
    for (let i = 0; i <= 2; i++) {
        const panel = document.getElementById('failed_step_' + i);
        if (panel) panel.classList.toggle('active', i === stepIndex);
        const tab = document.querySelector('#failedModal .lab-step-tab[data-step="' + i + '"]');
        if (tab) tab.classList.toggle('active', i === stepIndex);
    }
    const prevBtn = document.getElementById('failed_prev_btn');
    const nextBtn = document.getElementById('failed_next_btn');
    if (prevBtn) prevBtn.style.display = stepIndex > 0 ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = stepIndex < 2 ? 'inline-flex' : 'none';
}

function stepFailed(delta) {
    const target = Math.max(0, Math.min(2, currentFailedStep + delta));
    switchFailedStep(target);
}

let currentIngStep = 0;
function switchIngStep(stepIndex) {
    currentIngStep = stepIndex;
    for (let i = 0; i <= 1; i++) {
        const panel = document.getElementById('ing_step_' + i);
        if (panel) panel.classList.toggle('active', i === stepIndex);
        const tab = document.querySelector('#ingredientModal .lab-step-tab[data-step="' + i + '"]');
        if (tab) tab.classList.toggle('active', i === stepIndex);
    }
    const prevBtn = document.getElementById('ing_prev_btn');
    const nextBtn = document.getElementById('ing_next_btn');
    if (prevBtn) prevBtn.style.display = stepIndex > 0 ? 'inline-flex' : 'none';
    if (nextBtn) nextBtn.style.display = stepIndex < 1 ? 'inline-flex' : 'none';
}

function stepIng(delta) {
    const target = Math.max(0, Math.min(1, currentIngStep + delta));
    switchIngStep(target);
}

/* =========================================================================
   LIVE PREVIEWS & PRESET CHIPS
   ========================================================================= */
function updateLiveExpPreview() {
    const title = document.getElementById('exp_title')?.value || 'Experiment Title';
    const desc = document.getElementById('exp_desc')?.value || 'Inquiry question here...';
    const tags = document.getElementById('exp_tags')?.value || 'TAGS';
    const badge = document.getElementById('exp_status_badge')?.value || 'IN PROGRESS';
    const imgPath = document.getElementById('exp_img_text')?.value || 'assets/images/placeholder.jpg';

    const simTitle = document.getElementById('sim_card_title');
    const simDesc = document.getElementById('sim_card_desc');
    const simTags = document.getElementById('sim_card_tags');
    const simBadge = document.getElementById('sim_card_badge');
    const simImg = document.getElementById('sim_card_img');

    if (simTitle) simTitle.innerText = title;
    if (simDesc) simDesc.innerText = desc;
    if (simTags) simTags.innerText = tags;
    if (simBadge) simBadge.innerText = '● ' + badge;
    if (simImg) simImg.src = imgPath.startsWith('http') ? imgPath : ('../' + imgPath);

    // Quad text
    const q1 = document.getElementById('sim_quad_1');
    const q2 = document.getElementById('sim_quad_2');
    const q3 = document.getElementById('sim_quad_3');
    const q4 = document.getElementById('sim_quad_4');

    if (q1) q1.innerHTML = getRichEditorContent('exp_hypothesis') || '<em>Not set</em>';
    if (q2) q2.innerHTML = getRichEditorContent('exp_findings') || '<em>Not set</em>';
    if (q3) q3.innerHTML = getRichEditorContent('exp_methodology') || '<em>Not set</em>';
    if (q4) q4.innerHTML = getRichEditorContent('exp_next_steps') || '<em>Not set</em>';
}

function updateImgPreview(src, thumbId) {
    const thumb = document.getElementById(thumbId);
    if (!thumb) return;
    if (!src) {
        thumb.src = '../assets/images/placeholder.jpg';
        return;
    }
    thumb.src = src.startsWith('http') ? src : ('../' + src);
}

function setImgPreset(textInputId, thumbId, path) {
    const input = document.getElementById(textInputId);
    if (input) input.value = path;
    updateImgPreview(path, thumbId);
    updateLiveExpPreview();
}

function handleFilePreview(inputEl, thumbId, textInputId) {
    if (inputEl.files && inputEl.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const thumb = document.getElementById(thumbId);
            if (thumb) thumb.src = e.target.result;
            const textInput = document.getElementById(textInputId);
            if (textInput) textInput.value = 'File selected: ' + inputEl.files[0].name;
            updateLiveExpPreview();
        };
        reader.readAsDataURL(inputEl.files[0]);
    }
}

function setTelemetryPreset(fieldId, val) {
    const el = document.getElementById(fieldId);
    if (el) {
        el.value = val;
        el.style.borderColor = 'var(--gold, #C7A66A)';
        setTimeout(() => el.style.borderColor = '', 600);
    }
}

/* =========================================================================
   TABLE INSTANT FILTERING (OPERATE MODE)
   ========================================================================= */

/* =========================================================================
   VIEW TOGGLE & INSTANT FILTERING (BLOG & LIST HYBRID MODE)
   ========================================================================= */
function toggleMoreMenu(e, btn) {
    e.stopPropagation();
    const dropdown = btn.nextElementSibling;
    const isVisible = dropdown.style.display === 'block';
    
    document.querySelectorAll('.more-actions-dropdown').forEach(d => {
        d.style.display = 'none';
        const card = d.closest('.blog-card');
        if (card) card.classList.remove('menu-active');
    });

    if (!isVisible) {
        dropdown.style.display = 'block';
        const card = btn.closest('.blog-card');
        if (card) card.classList.add('menu-active');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.more-actions-dropdown') && !e.target.closest('button[onclick*="toggleMoreMenu"]')) {
        document.querySelectorAll('.more-actions-dropdown').forEach(d => {
            d.style.display = 'none';
            const card = d.closest('.blog-card');
            if (card) card.classList.remove('menu-active');
        });
    }
});

function setLabViewMode(mode) {
    document.cookie = "lab_view_pref=" + mode + "; path=/; max-age=" + (60*60*24*30);
    
    document.querySelectorAll('.view-toggle-btn').forEach(btn => {
        const isCurrent = (mode === 'grid' && btn.title === 'Grid View') || (mode === 'list' && btn.title === 'List View');
        btn.classList.toggle('active', isCurrent);
    });

    const expGrid = document.getElementById('expGridContainer');
    const expList = document.getElementById('expListContainer');
    if (expGrid && expList) {
        expGrid.style.display = mode === 'grid' ? '' : 'none';
        expList.style.display = mode === 'list' ? '' : 'none';
    }

    const failedGrid = document.getElementById('failedGridContainer');
    const failedList = document.getElementById('failedListContainer');
    if (failedGrid && failedList) {
        failedGrid.style.display = mode === 'grid' ? '' : 'none';
        failedList.style.display = mode === 'list' ? '' : 'none';
    }

    const ingGrid = document.getElementById('ingGridContainer');
    const ingList = document.getElementById('ingListContainer');
    if (ingGrid && ingList) {
        ingGrid.style.display = mode === 'grid' ? '' : 'none';
        ingList.style.display = mode === 'list' ? '' : 'none';
    }
}

function toggleLabItem(type, id, btnEl) {
    if (btnEl) {
        btnEl.disabled = true;
        btnEl.style.opacity = '0.6';
    }

    let actionName = 'toggle_' + type;
    let idParam = type + '_id';
    if (type === 'failed_batch') {
        idParam = 'failed_id';
    } else if (type === 'ingredient') {
        idParam = 'ing_id';
    }

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo $csrfToken; ?>');
    formData.append('action', actionName);
    formData.append(idParam, id);
    formData.append('is_ajax', '1');

    fetch('innovation-lab.php', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.style.opacity = '1';
        }

        if (data.success) {
            const isAct = data.is_active === 1;

            document.querySelectorAll(`.btn-visibility-toggle[data-item-type="${type}"][data-id="${id}"]`).forEach(btn => {
                const iconSpan = btn.querySelector('.toggle-icon');
                const textSpan = btn.querySelector('.toggle-text');
                if (isAct) {
                    btn.classList.remove('btn-status-hidden');
                    btn.classList.add('btn-status-visible');
                    btn.style.color = '#15803d';
                    btn.title = 'Click to Hide from site';
                    if (iconSpan) iconSpan.innerText = '👁️';
                    if (textSpan) textSpan.innerText = 'Hide';
                } else {
                    btn.classList.remove('btn-status-visible');
                    btn.classList.add('btn-status-hidden');
                    btn.style.color = '#d97706';
                    btn.title = 'Click to Make Visible on site';
                    if (iconSpan) iconSpan.innerText = '🔒';
                    if (textSpan) textSpan.innerText = 'Show';
                }
            });

            ['grid', 'list'].forEach(viewType => {
                const badge = document.getElementById(`badge-${type === 'failed_batch' ? 'failed' : (type === 'ingredient' ? 'ing' : 'exp')}-${viewType}-${id}`);
                if (badge) {
                    const iconSpan = badge.querySelector('.badge-icon');
                    const labelSpan = badge.querySelector('.badge-label');
                    if (isAct) {
                        badge.className = 'status-badge published';
                        badge.style.background = '';
                        badge.style.color = '';
                        badge.style.borderColor = '';
                        if (iconSpan) iconSpan.innerText = '👁️';
                        if (labelSpan) labelSpan.innerText = 'Visible';
                    } else {
                        badge.className = 'status-badge draft';
                        badge.style.background = 'rgba(217, 119, 6, 0.15)';
                        badge.style.color = '#d97706';
                        badge.style.borderColor = 'rgba(217, 119, 6, 0.3)';
                        if (iconSpan) iconSpan.innerText = '🔒';
                        if (labelSpan) labelSpan.innerText = 'Hidden';
                    }
                }
            });

            const card = document.getElementById(`${type === 'failed_batch' ? 'failed' : (type === 'ingredient' ? 'ing' : 'exp')}-card-${id}`);
            if (card) card.dataset.status = isAct ? 'published' : 'draft';
            const row = document.getElementById(`${type === 'failed_batch' ? 'failed' : (type === 'ingredient' ? 'ing' : 'exp')}-row-${id}`);
            if (row) row.dataset.status = isAct ? 'published' : 'draft';

            if (window.showToast) {
                showToast(data.message, isAct ? 'success' : 'warning');
            }
        } else {
            alert('Error: ' + (data.message || 'Action failed'));
        }
    })
    .catch(err => {
        if (btnEl) {
            btnEl.disabled = false;
            btnEl.style.opacity = '1';
        }
        alert('Network error: ' + err.message);
    });
}

let currentExpCat = '';
function setExpCategoryFilter(cat, btn) {
    currentExpCat = (cat || '').toLowerCase().trim();
    document.querySelectorAll('.action-bar-left .filter-chip').forEach(c => c.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterExperimentsView();
}

function filterExperimentsView() {
    const query = (document.getElementById('expSearchInput')?.value || '').toLowerCase().trim();
    const status = (document.getElementById('expStatusSelect')?.value || '').toLowerCase().trim();

    const cards = document.querySelectorAll('.exp-card-item');
    const rows = document.querySelectorAll('#experimentsTable tbody .exp-row');
    let visible = 0;

    cards.forEach(c => {
        const title = c.dataset.title || '';
        const desc = c.dataset.desc || '';
        const tags = c.dataset.tags || '';
        const cat = c.dataset.cat || '';
        const stat = c.dataset.status || '';

        const matchQ = !query || title.includes(query) || desc.includes(query) || tags.includes(query);
        const matchCat = !currentExpCat || cat === currentExpCat;
        const matchStatus = !status || stat === status;

        if (matchQ && matchCat && matchStatus) {
            c.style.display = '';
            visible++;
        } else {
            c.style.display = 'none';
        }
    });

    rows.forEach(r => {
        const title = r.dataset.title || '';
        const desc = r.dataset.desc || '';
        const tags = r.dataset.tags || '';
        const cat = r.dataset.cat || '';
        const stat = r.dataset.status || '';

        const matchQ = !query || title.includes(query) || desc.includes(query) || tags.includes(query);
        const matchCat = !currentExpCat || cat === currentExpCat;
        const matchStatus = !status || stat === status;

        r.style.display = (matchQ && matchCat && matchStatus) ? '' : 'none';
    });

    const badge = document.getElementById('expCountBadge');
    if (badge) badge.innerText = `Showing ${visible} items`;
}

function filterFailedBatchesView() {
    const query = (document.getElementById('failedSearchInput')?.value || '').toLowerCase().trim();
    const status = (document.getElementById('failedStatusSelect')?.value || '').toLowerCase().trim();

    const cards = document.querySelectorAll('.failed-card-item');
    const rows = document.querySelectorAll('#failedBatchesTable tbody .failed-row');
    let visible = 0;

    cards.forEach(c => {
        const title = c.dataset.title || '';
        const hypo = c.dataset.hypo || '';
        const happened = c.dataset.happened || '';
        const next = c.dataset.next || '';
        const stat = c.dataset.status || '';
        const feat = c.dataset.featured || '';

        const matchQ = !query || title.includes(query) || hypo.includes(query) || happened.includes(query) || next.includes(query);
        const matchStatus = !status || (status === 'featured' ? feat === 'featured' : stat === status);

        if (matchQ && matchStatus) {
            c.style.display = '';
            visible++;
        } else {
            c.style.display = 'none';
        }
    });

    rows.forEach(r => {
        const title = r.dataset.title || '';
        const hypo = r.dataset.hypo || '';
        const happened = r.dataset.happened || '';
        const next = r.dataset.next || '';
        const stat = r.dataset.status || '';
        const feat = r.dataset.featured || '';

        const matchQ = !query || title.includes(query) || hypo.includes(query) || happened.includes(query) || next.includes(query);
        const matchStatus = !status || (status === 'featured' ? feat === 'featured' : stat === status);

        r.style.display = (matchQ && matchStatus) ? '' : 'none';
    });

    const badge = document.getElementById('failedCountBadge');
    if (badge) badge.innerText = `Showing ${visible} items`;
}

function filterIngredientsView() {
    const query = (document.getElementById('ingSearchInput')?.value || '').toLowerCase().trim();
    const status = (document.getElementById('ingStatusSelect')?.value || '').toLowerCase().trim();

    const cards = document.querySelectorAll('.ing-card-item');
    const rows = document.querySelectorAll('#ingredientsTable tbody .ing-row');
    let visible = 0;

    cards.forEach(c => {
        const title = c.dataset.title || '';
        const desc = c.dataset.desc || '';
        const varietals = c.dataset.varietals || '';
        const stat = c.dataset.status || '';

        const matchQ = !query || title.includes(query) || desc.includes(query) || varietals.includes(query);
        const matchStatus = !status || stat === status;

        if (matchQ && matchStatus) {
            c.style.display = '';
            visible++;
        } else {
            c.style.display = 'none';
        }
    });

    rows.forEach(r => {
        const title = r.dataset.title || '';
        const desc = r.dataset.desc || '';
        const varietals = r.dataset.varietals || '';
        const stat = r.dataset.status || '';

        const matchQ = !query || title.includes(query) || desc.includes(query) || varietals.includes(query);
        const matchStatus = !status || stat === status;

        r.style.display = (matchQ && matchStatus) ? '' : 'none';
    });

    const badge = document.getElementById('ingCountBadge');
    if (badge) badge.innerText = `Showing ${visible} items`;
}

function openModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.style.display = 'flex';
    requestAnimationFrame(() => m.classList.add('open'));
}

function closeModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('open');
    setTimeout(() => { m.style.display = 'none'; }, 220);
}

function handleBackdropClick(e, id) {
    if (e.target.id === id) closeModal(id);
}

function openNewExperimentModal() {
    document.getElementById('expModalTitle').innerText = 'New Experiment — Currently in the Lab';
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
    updateImgPreview('assets/images/placeholder.jpg', 'exp_preview_thumb');

    // Clear telemetry
    ['origin', 'polymorph', 'particle_size', 'conching', 'water_activity', 'batch_ref'].forEach(f => {
        const el = document.getElementById('exp_' + f);
        if (el) el.value = '';
    });

    // Clear rich text
    setRichEditorContent('exp_hypothesis', '');
    setRichEditorContent('exp_findings', '');
    setRichEditorContent('exp_methodology', '');
    setRichEditorContent('exp_next_steps', '');
    setRichEditorContent('exp_full_content', '');

    document.getElementById('exp_active').checked = true;
    switchExpStep(0);
    openModal('experimentModal');
}

function editExperiment(exp) {
    document.getElementById('expModalTitle').innerText = 'Edit Lab Experiment: ' + (exp.title || '');
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
    updateImgPreview(exp.image_path || '', 'exp_preview_thumb');

    // Telemetry fields
    document.getElementById('exp_origin').value = exp.origin || '';
    document.getElementById('exp_polymorph').value = exp.polymorph || '';
    document.getElementById('exp_particle_size').value = exp.particle_size || '';
    document.getElementById('exp_conching').value = exp.conching || '';
    document.getElementById('exp_water_activity').value = exp.water_activity || '';
    document.getElementById('exp_batch_ref').value = exp.batch_ref || '';

    // Populate rich text
    setRichEditorContent('exp_hypothesis', exp.hypothesis || '');
    setRichEditorContent('exp_findings', exp.findings || '');
    setRichEditorContent('exp_methodology', exp.methodology || '');
    setRichEditorContent('exp_next_steps', exp.next_steps || '');
    setRichEditorContent('exp_full_content', exp.full_content || '');

    document.getElementById('exp_active').checked = Number(exp.is_active) === 1;
    switchExpStep(0);
    openModal('experimentModal');
}

function cloneExperiment(exp) {
    editExperiment(exp);
    document.getElementById('expModalTitle').innerText = 'Clone Experiment: ' + (exp.title || '') + ' (Copy)';
    document.getElementById('exp_id').value = '0';
    document.getElementById('exp_title').value = (exp.title || '') + ' (Copy)';
    switchExpStep(0);
    openModal('experimentModal');
}

function openNewFailedBatchModal() {
    document.getElementById('failedModalTitle').innerText = 'Log Failed Batch — From a Failed Batch to a New Idea';
    document.getElementById('failed_id').value = '0';
    document.getElementById('failed_title').value = '';
    document.getElementById('failed_badge').value = 'BECAUSE FAILURE IS DATA.';
    document.getElementById('failed_existing_image').value = 'assets/images/lab-failed-batch.jpg';
    document.getElementById('failed_img_text').value = '';
    updateImgPreview('assets/images/lab-failed-batch.jpg', 'failed_preview_thumb');

    setRichEditorContent('failed_hypothesis', '');
    setRichEditorContent('failed_what_happened', '');
    setRichEditorContent('failed_why', '');
    setRichEditorContent('failed_what_next', '');
    setRichEditorContent('failed_full_story', '');

    document.getElementById('failed_featured').checked = false;
    document.getElementById('failed_active').checked = true;
    switchFailedStep(0);
    openModal('failedModal');
}

function editFailedBatch(fb) {
    document.getElementById('failedModalTitle').innerText = 'Edit Failed Batch: ' + fb.title;
    document.getElementById('failed_id').value = fb.id;
    document.getElementById('failed_title').value = fb.title || '';
    document.getElementById('failed_badge').value = fb.badge_label || 'BECAUSE FAILURE IS DATA.';
    document.getElementById('failed_existing_image').value = fb.image_path || '';
    document.getElementById('failed_img_text').value = fb.image_path || '';
    updateImgPreview(fb.image_path || '', 'failed_preview_thumb');

    setRichEditorContent('failed_hypothesis', fb.hypothesis || '');
    setRichEditorContent('failed_what_happened', fb.what_happened || '');
    setRichEditorContent('failed_why', fb.why_it_happened || '');
    setRichEditorContent('failed_what_next', fb.what_next || '');
    setRichEditorContent('failed_full_story', fb.full_story || '');

    document.getElementById('failed_featured').checked = Number(fb.is_featured) === 1;
    document.getElementById('failed_active').checked = Number(fb.is_active) === 1;
    switchFailedStep(0);
    openModal('failedModal');
}

function openNewWhatIfModal() {
    document.getElementById('whatIfModalTitle').innerText = 'Add Question — What if?';
    document.getElementById('whatif_id').value = '0';
    document.getElementById('whatif_num').value = '0' + (<?php echo count($whatIfList); ?> + 1);
    document.getElementById('whatif_question').value = '';
    document.getElementById('whatif_link').value = '#currently-in-lab';
    document.getElementById('whatif_order').value = (<?php echo count($whatIfList); ?> + 1);
    document.getElementById('whatif_active').checked = true;
    openModal('whatIfModal');
}

function editWhatIf(q) {
    document.getElementById('whatIfModalTitle').innerText = 'Edit Question: ' + q.num_label;
    document.getElementById('whatif_id').value = q.id;
    document.getElementById('whatif_num').value = q.num_label || '';
    document.getElementById('whatif_question').value = q.question || '';
    document.getElementById('whatif_link').value = q.link_url || '';
    document.getElementById('whatif_order').value = q.sort_order || 0;
    document.getElementById('whatif_active').checked = Number(q.is_active) === 1;
    openModal('whatIfModal');
}

function openNewIngredientModal() {
    document.getElementById('ingredientModalTitle').innerText = 'New Category — Ingredient Innovation';
    document.getElementById('ing_id').value = '0';
    document.getElementById('ing_title').value = '';
    document.getElementById('ing_existing_image').value = 'assets/images/placeholder.jpg';
    document.getElementById('ing_img_text').value = '';
    document.getElementById('ing_link').value = '#currently-in-lab';
    document.getElementById('ing_order').value = (<?php echo count($ingredientsList); ?> + 1);
    updateImgPreview('assets/images/placeholder.jpg', 'ing_preview_thumb');

    setRichEditorContent('ing_desc', '');
    setRichEditorContent('ing_science_behavior', '');
    setRichEditorContent('ing_applications', '');
    setRichEditorContent('ing_lab_notes', '');

    switchIngStep(0);
    openModal('ingredientModal');
}

function editIngredient(ing) {
    document.getElementById('ingredientModalTitle').innerText = 'Edit Ingredient: ' + ing.title;
    document.getElementById('ing_id').value = ing.id;
    document.getElementById('ing_title').value = ing.title || '';
    document.getElementById('ing_existing_image').value = ing.image_path || '';
    document.getElementById('ing_img_text').value = ing.image_path || '';
    document.getElementById('ing_link').value = ing.link_url || '#currently-in-lab';
    document.getElementById('ing_order').value = ing.sort_order || 0;
    updateImgPreview(ing.image_path || '', 'ing_preview_thumb');

    setRichEditorContent('ing_desc', ing.description || '');
    setRichEditorContent('ing_science_behavior', ing.science_behavior || '');
    setRichEditorContent('ing_applications', ing.applications || '');
    setRichEditorContent('ing_lab_notes', ing.lab_notes || '');

    switchIngStep(0);
    openModal('ingredientModal');
}

function openNewPillarModal() {
    document.getElementById('pillarModalTitle').innerText = 'Add Future Pillar';
    document.getElementById('pillar_id').value = '0';
    document.getElementById('pillar_title').value = '';
    document.getElementById('pillar_desc').value = '';
    document.getElementById('pillar_link').value = '#future-ideas';
    document.getElementById('pillar_order').value = (<?php echo count($pillarsList); ?> + 1);
    openModal('pillarModal');
}

function editPillar(pl) {
    document.getElementById('pillarModalTitle').innerText = 'Edit Pillar: ' + pl.title;
    document.getElementById('pillar_id').value = pl.id;
    document.getElementById('pillar_title').value = pl.title || '';
    document.getElementById('pillar_desc').value = pl.description || '';
    document.getElementById('pillar_link').value = pl.link_url || '#future-ideas';
    document.getElementById('pillar_order').value = pl.sort_order || 0;
    openModal('pillarModal');
}
</script>

<?php
render_admin_footer();
