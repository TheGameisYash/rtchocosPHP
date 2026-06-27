<?php
// api_comments.php - Backend comments API with rate limiting
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $slug = trim($_GET['slug'] ?? '');
    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Article slug parameter is required.']);
        exit;
    }
    
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("SELECT name, comment, created_at FROM comments WHERE blog_slug = ? AND is_approved = 1 ORDER BY created_at DESC");
        $stmt->execute([$slug]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response = [];
        foreach ($comments as $c) {
            $response[] = [
                'name' => $c['name'],
                'text' => $c['comment'],
                'date' => date('d M, Y', strtotime($c['created_at']))
            ];
        }
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error loading comments.']);
    }
    exit;
}

if ($method === 'POST') {
    // 1. Rate Limiting check (max 3 comments per 1 minute)
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $now = time();
    if (!isset($_SESSION['last_comment_times'])) {
        $_SESSION['last_comment_times'] = [];
    }
    
    // Filter times within last 60 seconds
    $_SESSION['last_comment_times'] = array_filter($_SESSION['last_comment_times'], function($t) use ($now) {
        return ($now - $t) < 60;
    });
    
    if (count($_SESSION['last_comment_times']) >= 3) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many comments. Please wait a minute before posting again.']);
        exit;
    }
    
    // Read input parameters
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = trim($input['slug'] ?? '');
    $name = trim($input['name'] ?? '');
    $comment = trim($input['comment'] ?? '');
    
    // Validate parameters
    if (empty($slug) || empty($name) || empty($comment)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields (name, comment, article slug) are required.']);
        exit;
    }
    
    // Sanitize parameters
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');
    
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare("INSERT INTO comments (blog_slug, name, comment, is_approved) VALUES (?, ?, ?, 1)");
        $stmt->execute([$slug, $name, $comment]);
        
        // Log comment time
        $_SESSION['last_comment_times'][] = $now;
        
        echo json_encode(['success' => true, 'message' => 'Comment posted successfully.']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error saving comment.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>
