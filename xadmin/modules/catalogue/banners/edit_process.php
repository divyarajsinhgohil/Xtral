<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: list.php");
    exit;
}

try {
    $pdo = getDBConnection();

    $id = $_POST['id'] ?? null;
    if (!$id) throw new Exception("Invalid banner ID.");

    $banner = fetchOne("SELECT * FROM catalogue_banners WHERE id = ?", [$id]);
    if (!$banner) throw new Exception("Banner not found.");

    $title = sanitize($_POST['title'] ?? '');
    $link_url = sanitize($_POST['link_url'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($title)) {
        throw new Exception("Banner title is required.");
    }

    $banner_type = sanitize($_POST['banner_type'] ?? 'image');
    $image_url = $banner['image_url'];
    $video_url = $banner['video_url'] ?? null;

    if ($banner_type === 'image') {
        $hasNewUpload = (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK);
        if (!$image_url && !$hasNewUpload) {
            throw new Exception("Please upload an image for this image banner.");
        }
        
        if ($hasNewUpload) {
            $uploadResult = handleFileUpload($_FILES['image'], 'catalogue/banners', ['image/jpeg', 'image/png', 'image/webp']);
            if ($uploadResult['success']) {
                if ($image_url) {
                    deleteUploadedFile('catalogue/banners/' . $image_url);
                }
                $image_url = $uploadResult['filename'];
            } else {
                throw new Exception("Image upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
            }
        }
        
        // If switched from video, delete old video file and set URL to null
        if ($video_url) {
            deleteUploadedFile('catalogue/banners/' . $video_url);
            $video_url = null;
        }
    } else {
        $hasNewUpload = (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK);
        if (!$video_url && !$hasNewUpload) {
            throw new Exception("Please upload a video for this video banner.");
        }
        
        if ($hasNewUpload) {
            $uploadResult = handleFileUpload($_FILES['video'], 'catalogue/banners', ['video/mp4', 'video/webm']);
            if ($uploadResult['success']) {
                if ($video_url) {
                    deleteUploadedFile('catalogue/banners/' . $video_url);
                }
                $video_url = $uploadResult['filename'];
            } else {
                throw new Exception("Video upload failed: " . ($uploadResult['error'] ?? 'Unknown error'));
            }
        }
        
        // If switched from image, delete old image file and set URL to null
        if ($image_url) {
            deleteUploadedFile('catalogue/banners/' . $image_url);
            $image_url = null;
        }
    }

    // Update
    $sql = "UPDATE catalogue_banners SET title=?, banner_type=?, image_url=?, video_url=?, link_url=?, display_order=?, is_active=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$title, $banner_type, $image_url, $video_url, $link_url ?: null, $display_order, $is_active, $id]);

    $_SESSION['success'] = "Banner '$title' updated successfully!";
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

header("Location: list.php");
exit;
