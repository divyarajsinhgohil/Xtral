<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    $_SESSION['error'] = "Invalid banner ID.";
    header("Location: list.php");
    exit;
}

$pdo = getDBConnection();
try {
    // Get media files to delete
    $banner = fetchOne("SELECT image_url, video_url FROM catalogue_banners WHERE id = ?", [$id]);
    if ($banner) {
        if ($banner['image_url']) {
            deleteUploadedFile('catalogue/banners/' . $banner['image_url']);
        }
        if (!empty($banner['video_url'])) {
            deleteUploadedFile('catalogue/banners/' . $banner['video_url']);
        }
    }

    // Delete DB Record
    $stmt = $pdo->prepare("DELETE FROM catalogue_banners WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['success'] = "Banner deleted successfully.";
} catch (PDOException $e) {
    $_SESSION['error'] = "Database Error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
