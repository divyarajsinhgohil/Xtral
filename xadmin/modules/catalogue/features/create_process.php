<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $_SESSION['error'] = "Feature Name is required";
        header("Location: create.php");
        exit;
    }

    if (!isset($_FILES['icon_image']) || $_FILES['icon_image']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "Icon image is required.";
        header("Location: create.php");
        exit;
    }

    // Handle Upload
    // Note: We'll allow the same image formats (jpeg, png, webp, and maybe svg if supported by handleFileUpload, but let's stick to reliable image formats).
    $upload = handleFileUpload($_FILES['icon_image'], 'catalogue/features', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
    
    if (!$upload['success']) {
        $_SESSION['error'] = $upload['error'];
        header("Location: create.php");
        exit;
    }

    $iconUrl = $upload['filename'];

    try {
        $sql = "INSERT INTO catalogue_features (name, icon_url, display_order, is_active) VALUES (?, ?, ?, ?)";
        execute($sql, [$name, $iconUrl, $displayOrder, $isActive]);

        $_SESSION['success'] = "Feature created successfully.";
        header("Location: list.php");
        exit;
    } catch (Exception $e) {
        // Cleanup uploaded file on DB error
        deleteUploadedFile('catalogue/features/' . $iconUrl);
        
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: create.php");
        exit;
    }
}
