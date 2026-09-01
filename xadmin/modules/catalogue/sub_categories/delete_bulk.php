<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__) . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

$ids = $_POST['sub_category_ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
    $_SESSION['error'] = "No sub-categories selected for deletion.";
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
            // 1. Cleanup Files (sub-category images, and underlying series/product/variant images)
            deleteCatalogueEntity($pdo, 'sub_category', $id);
            
            // 2. Delete DB Record (CASCADE handles remaining child records)
            $stmt = $pdo->prepare("DELETE FROM catalogue_sub_categories WHERE id = ?");
            if ($stmt->execute([$id])) {
                $deletedCount++;
            }
        }
    }

    $pdo->commit();
    $_SESSION['success'] = "{$deletedCount} sub-category(s) deleted successfully.";
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
