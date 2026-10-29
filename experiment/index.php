<?php
// experiment/index.php - Route direct /experiment/ directory requests
if (!empty($_GET['slug'])) {
    require_once __DIR__ . '/../experiment.php';
} else {
    header('Location: ../innovation-lab.php#currently-in-lab');
    exit;
}
