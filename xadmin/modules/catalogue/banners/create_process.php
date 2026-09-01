<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

try {
    $pdo = getDBConnection();

    $title = sanitize($_POST['title'] ?? '');
    $banner_type = sanitize($_POST['banner_type'] ?? 'image');
    $link_url = sanitize($_POST['link_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) {
        throw new Exception("Banner title is required.");
    }

    $image_url = null;
    $video_url = null;

    if ($banner_type === 'image') {
        // Handle Image Upload
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Banner image is required.");
        }

        $uploadResult = handleFileUpload($_FILES['image'], 'catalogue/banners', ['image/jpeg', 'image/png', 'image/webp']);
        if (!$uploadResult['success']) {
            throw new Exception("Image upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
        }
        $image_url = $uploadResult['filename'];
    } else {
        // Handle Video Upload
        if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Banner video is required.");
        }

        $uploadResult = handleFileUpload($_FILES['video'], 'catalogue/banners', ['video/mp4', 'video/webm']);
        if (!$uploadResult['success']) {
            throw new Exception("Video upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
        }
        $video_url = $uploadResult['filename'];
    }

    // Insert
    $sql = "INSERT INTO catalogue_banners (title, banner_type, image_url, video_url, link_url, display_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $banner_type, $image_url, $video_url, $link_url ?: null, $display_order, $is_active]);

    $_SESSION['success'] = "Banner '$title' created successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
