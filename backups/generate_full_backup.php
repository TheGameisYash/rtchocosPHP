<?php
// backups/generate_full_backup.php — Comprehensive SQL Dump of All Tables
require_once __DIR__ . '/../includes/db.php';

$pdo = get_db();
$backupDir = __DIR__;
$timestamp = date('Ymd_His');
$sqlFile = $backupDir . "/rtchocos_full_database_backup_{$timestamp}.sql";

echo "Fetching all tables from database...\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

$out = "-- ========================================================\n";
$out .= "-- RT Chocos: Full Production Database Backup\n";
$out .= "-- Total Tables: " . count($tables) . "\n";
$out .= "-- Tables List: " . implode(', ', $tables) . "\n";
$out .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$out .= "-- Host: srv1875.hstgr.io | Database: u219698334_RTchocos\n";
$out .= "-- ========================================================\n\n";
$out .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$out .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$out .= "SET NAMES utf8mb4;\n\n";

$totalRows = 0;

foreach ($tables as $t) {
    echo "  Backing up `{$t}`... ";
    
    // 1. Table schema
    $create = $pdo->query("SHOW CREATE TABLE `{$t}`")->fetch(PDO::FETCH_ASSOC);
    $createSql = $create['Create Table'] ?? '';
    
    $out .= "-- --------------------------------------------------------\n";
    $out .= "-- Table structure for `{$t}`\n";
    $out .= "-- --------------------------------------------------------\n";
    $out .= "DROP TABLE IF EXISTS `{$t}`;\n";
    $out .= $createSql . ";\n\n";

    // 2. Table data
    $rows = $pdo->query("SELECT * FROM `{$t}`")->fetchAll(PDO::FETCH_ASSOC);
    $rowCount = count($rows);
    $totalRows += $rowCount;
    echo "({$rowCount} rows)\n";

    if (!empty($rows)) {
        $out .= "-- Dumping data for table `{$t}` ({$rowCount} rows)\n";
        
        // Chunk inserts for efficiency
        $chunks = array_chunk($rows, 50);
        foreach ($chunks as $chunk) {
            $cols = array_keys($chunk[0]);
            $escapedCols = implode(', ', array_map(function($c) { return "`$c`"; }, $cols));
            
            $valuesSql = [];
            foreach ($chunk as $row) {
                $rowVals = [];
                foreach ($row as $val) {
                    if ($val === null) {
                        $rowVals[] = "NULL";
                    } elseif (is_numeric($val) && !is_string($val)) {
                        $rowVals[] = $val;
                    } else {
                        $rowVals[] = $pdo->quote($val);
                    }
                }
                $valuesSql[] = "(" . implode(', ', $rowVals) . ")";
            }
            $out .= "INSERT INTO `{$t}` ({$escapedCols}) VALUES\n" . implode(",\n", $valuesSql) . ";\n";
        }
        $out .= "\n";
    }
}

$out .= "SET FOREIGN_KEY_CHECKS = 1;\n";

file_put_contents($sqlFile, $out);
$fileSize = filesize($sqlFile);
echo "\nBackup completed successfully!\n";
echo "File: {$sqlFile}\n";
echo "Size: " . round($fileSize / 1024, 2) . " KB\n";
echo "Total Rows Dumped: {$totalRows}\n";
