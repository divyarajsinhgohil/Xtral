<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Sanitize Input
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    if ($display_order <= 0) {
        $maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_categories");
        $display_order = ($maxOrder['max_order'] ?? 0) + 1;
    }
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // 2. Validate
    if (empty($name)) {
        $_SESSION['error'] = "Category name is required.";
        header("Location: create.php");
        exit;
    }

    // 3. Handle Image Upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload(
            $_FILES['image'], 
            'catalogue/categories', 
            ['image/jpeg', 'image/png', 'image/webp']
        );

        if ($uploadResult['success']) {
            $image_url = $uploadResult['filename'];
            
            // Optional: Crop to square if needed
            // cropImageToSquare(UPLOAD_PATH . 'catalogue/categories/' . $image_url);
        } else {
            $_SESSION['error'] = "Image upload failed: " . $uploadResult['error'];
            header("Location: create.php");
            exit;
        }
    }

    // 4. Insert into Database
    try {
        $has_dual_price = isset($_POST['has_dual_price']) ? 1 : 0;
        $sql = "INSERT INTO catalogue_categories (name, image_url, description, display_order, is_active, has_dual_price) VALUES (?, ?, ?, ?, ?, ?)";
        execute($sql, [$name, $image_url, $description, $display_order, $is_active, $has_dual_price]);

        $_SESSION['success'] = "Category '$name' created successfully!";
        header("Location: list.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        
        // If upload succeeded but DB failed, delete the image
        if ($image_url) {
            deleteUploadedFile('catalogue/categories/' . $image_url);
        }
        
        header("Location: create.php");
        exit;
    }

} else {
    // Not a POST request
    header("Location: list.php");
    exit;
}
