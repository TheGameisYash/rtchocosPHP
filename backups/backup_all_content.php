<?php
// backups/backup_all_content.php — Full Database Content Backup
require_once __DIR__ . '/../includes/db.php';

$pdo = get_db();
$backupDir = __DIR__;
$sqlFile = $backupDir . '/rtchocos_all_content_database_backup.sql';

$tables = [
    'products',
    'product_categories',
    'blogs',
    'blog_tags',
    'blog_tag_map',
    'media',
    'site_settings',
    'faqs',
    'ai_ingredient_spotlights',
    'ai_insights',
    'ai_class_facts'
];

echo "Dumping database tables into {$sqlFile} ...\n";
$out = "-- ========================================================\n";
$out .= "-- RT Chocos: Full Content Database Backup\n";
$out .= "-- Tables: " . implode(', ', $tables) . "\n";
$out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$out .= "-- Host: srv1875.hstgr.io | Database: u219698334_RTchocos\n";
$out .= "-- ========================================================\n\n";
$out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$out .= "SET NAMES utf8mb4;\n\n";

foreach ($tables as $t) {
    echo "  Exporting table `{$t}` ... ";
    // Create Table statement
    $create = $pdo->query("SHOW CREATE TABLE `{$t}`")->fetch(PDO::FETCH_ASSOC);
    $createSql = $create['Create Table'] ?? '';
    
    $out .= "-- --------------------------------------------------------\n";
    $out .= "-- Table structure for `{$t}`\n";
    $out .= "-- --------------------------------------------------------\n";
    $out .= "DROP TABLE IF EXISTS `{$t}`;\n";
    $out .= $createSql . ";\n\n";

    // Dump Data
    $rows = $pdo->query("SELECT * FROM `{$t}`")->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($rows)) {
        $out .= "-- Dumping data for table `{$t}` (" . count($rows) . " rows)\n";
        foreach ($rows as $row) {
            $cols = array_keys($row);
            $escapedCols = array_map(function($c) { return "`$c`"; }, $cols);
            $values = [];
            foreach ($row as $val) {
                if ($val === null) {
                    $values[] = "NULL";
                } elseif (is_numeric($val) && !is_string($val)) {
                    $values[] = $val;
                } else {
                    $values[] = "'" . addslashes($val) . "'";
                }
            }
            $out .= "INSERT INTO `{$t}` (" . implode(', ', $escapedCols) . ") VALUES (" . implode(', ', $values) . ");\n";
        }
    }
    $out .= "\n";
    echo count($rows) . " rows exported.\n";
}

$out .= "SET FOREIGN_KEY_CHECKS = 1;\n";
file_put_contents($sqlFile, $out);
echo "\nSaved full database content backup to: {$sqlFile} (" . round(filesize($sqlFile)/1024, 1) . " KB)\n";
