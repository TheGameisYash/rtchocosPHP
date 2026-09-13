<?php
// api_generate_spotlight.php - API endpoint for dynamic ingredient spotlight
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
header('Content-Type: application/json');

require_once __DIR__ . '/includes/ingredient_spotlight.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';
$force = false;

if ($action === 'regenerate') {
    // Check if session has admin or dev login, or accept CSRF / secret token
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['dev_logged_in']);
    if (!$isAdmin) {
        // Rate limit public manual triggers: only allow if at least 15 minutes passed since last generation
        $pdo = get_db();
        $stmt = $pdo->query("SELECT created_at FROM ai_ingredient_spotlights ORDER BY id DESC LIMIT 1");
        $lastRow = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($lastRow && (time() - strtotime($lastRow['created_at'])) < 900) {
            echo json_encode([
                'success' => false,
                'message' => 'Spotlight was recently generated. Please wait before triggering again.',
                'spotlight' => get_current_ingredient_spotlight(false)
            ]);
            exit;
        }
    }
    $force = true;
}

try {
    $spotlight = get_current_ingredient_spotlight($force);
    echo json_encode([
        'success' => true,
        'spotlight' => $spotlight
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'spotlight' => get_curated_spotlight_fallback()
    ]);
}
