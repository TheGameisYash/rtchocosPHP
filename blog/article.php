<?php
// blog/article.php - Dynamic blog article routing catch-all
$articleKey = $_GET['slug'] ?? ($_GET['article'] ?? '');
if (empty($articleKey)) {
    header('Location: ../blog.php');
    exit;
}
include __DIR__ . '/../blog-article.php';
?>
