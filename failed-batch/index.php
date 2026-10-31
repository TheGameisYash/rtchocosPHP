<?php
// failed-batch/index.php - Route direct /failed-batch/ directory requests
if (empty($_GET['slug'])) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if (preg_match('#^/(?:beta/)?(?:failed-batch|case-study)/([^/]+)/?$#', $requestUri, $matches)) {
        if ($matches[1] !== 'index.php') {
            $_GET['slug'] = $matches[1];
        }
    }
}

if (!empty($_GET['slug'])) {
    require_once __DIR__ . '/../failed-batch.php';
} else {
    header('Location: ../innovation-lab.php#failed-experiments');
    exit;
}

