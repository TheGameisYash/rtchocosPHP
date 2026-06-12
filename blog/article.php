<?php
// blog/article.php - Dynamic blog article routing catch-all
$articleKey = $_GET['slug'] ?? '';
if (empty($articleKey)) {
    header('Location: ../blog.php');
    exit;
}
include '../blog-article.php';
?>
