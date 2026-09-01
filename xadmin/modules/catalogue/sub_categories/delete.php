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
    $_SESSION['error'] = "Invalid sub-category ID.";
    header("Location: list.php");
    exit;
}

$pdo = getDBConnection();
try {
    // 1. Recursively cleanup all files (series images, product images, variant images)
    deleteCatalogueEntity($pdo, 'sub_category', $id);

    // 2. Delete Sub Category Record (CASCADE handles child series→products in DB)
    $stmt = $pdo->prepare("DELETE FROM catalogue_sub_categories WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Sub Category and all its contents deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
