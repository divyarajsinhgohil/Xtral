<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__) . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

$ids = $_POST['product_ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
    $_SESSION['error'] = "No products selected for deletion.";
    header("Location: list.php");
    exit;
}

$pdo = getDBConnection();
try {
    $pdo->beginTransaction();
    $deletedCount = 0;
    
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id > 0) {
            // 1. Cleanup Files (images, variants)
            deleteCatalogueEntity($pdo, 'product', $id);
            
            // 2. Delete DB Record (CASCADE handles remaining child records)
            $stmt = $pdo->prepare("DELETE FROM catalogue_products WHERE id = ?");
            if ($stmt->execute([$id])) {
                $deletedCount++;
            }
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "{$deletedCount} product(s) deleted successfully.";
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
