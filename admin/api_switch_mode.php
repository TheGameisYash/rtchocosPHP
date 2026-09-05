<?php
// admin/api_switch_mode.php - Fast AJAX Switcher for Store Business Focus (Retail vs Bulk vs Hybrid)
header('Content-Type: application/json');
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Verify admin login session
require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Support both JSON body and form-urlencoded POST
$input = json_decode(file_get_contents('php://input'), true);
$mode = trim($input['mode'] ?? $_POST['mode'] ?? '');

$validModes = ['retail', 'bulk', 'hybrid'];
if (!in_array($mode, $validModes, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid store mode specified. Must be retail, bulk, or hybrid.']);
    exit;
}

try {
    $pdo = get_db();
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES ('store_mode', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$mode, $mode]);

    $labels = [
        'retail' => 'Retail Mode (B2C Online Shop)',
        'bulk' => 'Bulk & B2B Orders Mode (Corporate Wholesale)',
        'hybrid' => 'Hybrid Mode (Retail + Bulk Switcher)'
    ];

    echo json_encode([
        'success' => true,
        'mode' => $mode,
        'label' => $labels[$mode] ?? $mode,
        'message' => 'Store mode successfully switched to ' . ($labels[$mode] ?? $mode) . '!'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update store mode: ' . $e->getMessage()
    ]);
}
