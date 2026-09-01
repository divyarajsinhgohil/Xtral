<?php
/**
 * Backup Download Handler
 * Serves backup files for download
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
requireRole(['super_admin']);

$backupDir = dirname(dirname(__DIR__)) . '/backups/';
$filename = basename($_GET['file'] ?? '');
$filepath = $backupDir . $filename;

// Security: only allow .sql files from the backup directory
if (empty($filename) || !file_exists($filepath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
    header('HTTP/1.0 404 Not Found');
    echo 'File not found';
    exit;
}

// Serve the file
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filepath);
exit;
