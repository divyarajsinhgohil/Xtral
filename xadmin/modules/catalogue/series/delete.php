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
    $_SESSION['error'] = "Invalid series ID.";
    header("Location: list.php");
    exit;
}

$pdo = getDBConnection();
try {
    // 1. Recursively cleanup all product files (images, variants) under this series
    deleteCatalogueEntity($pdo, 'series', $id);

    // 2. Delete Series Record (CASCADE handles child products in DB)
    $stmt = $pdo->prepare("DELETE FROM catalogue_series WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Series and all its products deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
