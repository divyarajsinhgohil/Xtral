<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
require_once dirname(__DIR__) . '/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        $_SESSION['error'] = "Invalid category ID.";
        header("Location: list.php");
        exit;
    }

    $pdo = getDBConnection();

    try {
        // 1. Cleanup Files
        deleteCatalogueEntity($pdo, 'category', $id);

        // 2. Delete Record
        // Cascading delete in DB should handle sub-records.
        $stmt = $pdo->prepare("DELETE FROM catalogue_categories WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['success'] = "Category deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
    header("Location: list.php");
    exit;
} else {
    header("Location: list.php");
    exit;
}
