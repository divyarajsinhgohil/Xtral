<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__) . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: list.php");
    exit;
}

$pdo = getDBConnection();
try {
    // 1. Cleanup Files (images, variants)
    deleteCatalogueEntity($pdo, 'product', $id);
    
    // 2. Delete DB Record (CASCADE handles remaining child records)
    $stmt = $pdo->prepare("DELETE FROM catalogue_products WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Product deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
