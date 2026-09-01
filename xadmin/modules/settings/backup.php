<?php
/**
 * Database Backup & Restore
 * Create, download, restore, and delete database backups
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
requireRole(['super_admin']);

$pageTitle = 'Database Backup & Restore';
$activePage = 'backup';

// Backup directory
$backupDir = dirname(dirname(__DIR__)) . '/backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Handle actions
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Create new backup using pure PHP (works on shared hosting without exec)
        $timestamp = date('Y-m-d_H-i-s');
        $filename = 'backup_' . DB_NAME . '_' . $timestamp . '.sql';
        $filepath = $backupDir . $filename;
        
        try {
            $pdo = getDBConnection();
            $sqlDump = "-- Database Backup: " . DB_NAME . "\n";
            $sqlDump .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sqlDump .= "-- Server: " . DB_HOST . "\n";
            $sqlDump .= "-- PHP Backup (no mysqldump required)\n\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $sqlDump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            $sqlDump .= "SET AUTOCOMMIT = 0;\n";
            $sqlDump .= "START TRANSACTION;\n\n";
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Get CREATE TABLE statement
                $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
                $createSql = $createStmt['Create Table'] ?? $createStmt['Create View'] ?? '';
                
                $sqlDump .= "-- --------------------------------------------------------\n";
                $sqlDump .= "-- Table structure for table `{$table}`\n";
                $sqlDump .= "-- --------------------------------------------------------\n\n";
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $sqlDump .= $createSql . ";\n\n";
                
                // Get table data
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $sqlDump .= "-- Data for table `{$table}`\n";
                    
                    // Process in chunks of 100 rows for memory efficiency
                    $chunks = array_chunk($rows, 100);
                    foreach ($chunks as $chunk) {
                        $columns = array_keys($chunk[0]);
                        $columnList = implode('`, `', $columns);
                        $sqlDump .= "INSERT INTO `{$table}` (`{$columnList}`) VALUES\n";
                        
                        $valueRows = [];
                        foreach ($chunk as $row) {
                            $values = [];
                            foreach ($row as $value) {
                                if ($value === null) {
                                    $values[] = 'NULL';
                                } else {
                                    $values[] = $pdo->quote($value);
                                }
                            }
                            $valueRows[] = '(' . implode(', ', $values) . ')';
                        }
                        $sqlDump .= implode(",\n", $valueRows) . ";\n\n";
                    }
                }
            }
            
            $sqlDump .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            $sqlDump .= "COMMIT;\n";
            
            // Write to file
            if (file_put_contents($filepath, $sqlDump) !== false) {
                $filesize = round(filesize($filepath) / 1024, 1);
                logInfo("Database backup created: {$filename} ({$filesize} KB) by " . getAdminUsername());
                $success = "Backup created successfully: {$filename} ({$filesize} KB)";
            } else {
                $errors[] = 'Failed to write backup file. Check directory permissions.';
            }
            
        } catch (Exception $e) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
            $errors[] = 'Backup failed: ' . $e->getMessage();
            logError("Backup failed: " . $e->getMessage());
        }
    } elseif ($action === 'delete') {
        $filename = basename($_POST['filename'] ?? '');
        $filepath = $backupDir . $filename;
        
        if ($filename && file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'sql') {
            unlink($filepath);
            logInfo("Backup deleted: {$filename} by " . getAdminUsername());
            $success = "Backup deleted: {$filename}";
        } else {
            $errors[] = 'Invalid backup file';
        }
    } elseif ($action === 'restore') {
        $filename = basename($_POST['filename'] ?? '');
        $filepath = $backupDir . $filename;
        
        if (!$filename || !file_exists($filepath) || pathinfo($filename, PATHINFO_EXTENSION) !== 'sql') {
            $errors[] = 'Invalid backup file';
        } else {
            // Read SQL file and execute
            $sql = file_get_contents($filepath);
            
            if (empty($sql)) {
                $errors[] = 'Backup file is empty';
            } else {
                try {
                    $pdo = getDBConnection();
                    
                    // Disable foreign key checks during restore
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
                    
                    // Split by statement delimiter and execute each
                    $pdo->exec($sql);
                    
                    // Re-enable foreign key checks
                    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
                    
                    logInfo("Database restored from: {$filename} by " . getAdminUsername());
                    $success = "Database restored successfully from: {$filename}";
                } catch (Exception $e) {
                    $errors[] = 'Restore failed: ' . $e->getMessage();
                    logError("Restore failed from {$filename}: " . $e->getMessage());
                    
                    // Try to re-enable foreign key checks
                    try { $pdo->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $e2) {}
                }
            }
        }
    }
}

// Get backup files with pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$allFiles = glob($backupDir . 'backup_*.sql');
usort($allFiles, function($a, $b) { return filemtime($b) - filemtime($a); }); // newest first

$totalFiles = count($allFiles);
$totalPages = max(1, ceil($totalFiles / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$files = array_slice($allFiles, $offset, $perPage);

// Calculate total backup size
$totalSize = 0;
foreach ($allFiles as $f) {
    $totalSize += filesize($f);
}

include dirname(dirname(__DIR__)) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-database me-2"></i>Database Backup & Restore</h2>
                    <p class="text-muted mb-0">Create, download, restore, and manage database backups</p>
                </div>
                <form method="POST" class="d-inline" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin me-2\'></i>Creating...';">
                    <input type="hidden" name="action" value="create">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>Create Backup
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <strong><i class="fas fa-exclamation-circle me-2"></i>Error:</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Backups</h6>
                            <h3 class="mb-0"><?= $totalFiles ?></h3>
                        </div>
                        <i class="fas fa-archive fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Total Size</h6>
                            <h3 class="mb-0"><?= $totalSize > 1048576 ? round($totalSize / 1048576, 1) . ' MB' : round($totalSize / 1024, 1) . ' KB' ?></h3>
                        </div>
                        <i class="fas fa-hdd fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Database</h6>
                            <h3 class="mb-0"><?= DB_NAME ?></h3>
                        </div>
                        <i class="fas fa-database fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Backup Files</h5>
            <small class="text-muted">Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalFiles) ?> of <?= $totalFiles ?></small>
        </div>
        <div class="card-body p-0">
            <?php if (empty($files)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-database fa-4x text-muted mb-3"></i>
                    <h5>No Backups Yet</h5>
                    <p class="text-muted">Click "Create Backup" to create your first database backup.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Filename</th>
                                <th>Created At</th>
                                <th>Size</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $i => $file): 
                                $fname = basename($file);
                                $fsize = filesize($file);
                                $fdate = filemtime($file);
                            ?>
                            <tr>
                                <td class="text-muted"><?= $offset + $i + 1 ?></td>
                                <td>
                                    <i class="fas fa-file-code text-primary me-2"></i>
                                    <strong><?= htmlspecialchars($fname) ?></strong>
                                </td>
                                <td>
                                    <i class="fas fa-calendar-alt text-muted me-1"></i>
                                    <?= date('d M Y, h:i:s A', $fdate) ?>
                                </td>
                                <td>
                                    <?php if ($fsize > 1048576): ?>
                                        <span class="badge bg-warning"><?= round($fsize / 1048576, 1) ?> MB</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?= round($fsize / 1024, 1) ?> KB</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= BASE_URL ?>/modules/settings/backup_download.php?file=<?= urlencode($fname) ?>" 
                                       class="btn btn-sm btn-outline-primary me-1" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('⚠️ RESTORE WARNING\n\nThis will REPLACE your entire database with this backup:\n<?= htmlspecialchars($fname) ?>\n\nAll current data will be OVERWRITTEN.\n\nAre you absolutely sure?');">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="filename" value="<?= htmlspecialchars($fname) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-warning me-1" title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete backup:\n<?= htmlspecialchars($fname) ?>\n\nThis cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="filename" value="<?= htmlspecialchars($fname) ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($totalPages > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>

    <!-- Info Card -->
    <div class="card mt-3">
        <div class="card-body">
            <h6 class="mb-2"><i class="fas fa-info-circle text-info me-2"></i>Backup Information</h6>
            <ul class="small text-muted mb-0">
                <li>Backups are stored in: <code>xadmin/backups/</code></li>
                <li>Each backup contains the full database structure and data</li>
                <li><strong class="text-warning">Restore will OVERWRITE</strong> all current data — create a backup first!</li>
                <li>Download backups regularly and keep copies in a safe location</li>
            </ul>
        </div>
    </div>
</div>

<?php include dirname(dirname(__DIR__)) . '/includes/footer.php'; ?>
