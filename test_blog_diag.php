<?php
// test_blog_diag.php - Check backup directories and permissions on Hostinger
header('Content-Type: text/plain; charset=UTF-8');

echo "=== Hostinger System & Backup Inspection ===\n";
echo "Current user: " . get_current_user() . " (UID: " . getmyuid() . ")\n";

$dirsToCheck = [
    '/home/u219698334',
    '/home/u219698334/.snapshots',
    '/home/u219698334/.backup',
    '/home/u219698334/.backups',
    '/home/u219698334/backups',
    '/home/u219698334/.trash',
    '/home/u219698334/domains/rtchocos.com',
    '/backup',
    '/var/backups',
];

foreach ($dirsToCheck as $d) {
    if (is_dir($d)) {
        echo "DIR FOUND: $d (Readable: " . (is_readable($d) ? 'YES' : 'NO') . ")\n";
        $sc = @scandir($d);
        if ($sc) {
            echo "  Entries: " . implode(', ', array_diff($sc, ['.', '..'])) . "\n";
        }
    } else {
        echo "Not found: $d\n";
    }
}

// Check shell_exec
if (function_exists('shell_exec')) {
    echo "\nshell_exec is AVAILABLE!\n";
    $ls = @shell_exec('ls -la /home/u219698334 2>&1');
    echo "ls -la /home/u219698334:\n$ls\n";
} else {
    echo "\nshell_exec is DISABLED.\n";
}
