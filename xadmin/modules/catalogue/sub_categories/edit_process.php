<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Sanitize Input
    $id = $_POST['id'] ?? null;
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $current_image = $_POST['current_image'] ?? null;

    if (!$id) {
        $_SESSION['error'] = "Invalid ID.";
        header("Location: list.php");
        exit;
    }

    // 2. Validate
    if (empty($category_id) || empty($name)) {
        $_SESSION['error'] = "Parent Category and Name are required.";
        header("Location: edit.php?id=$id");
        exit;
    }

    // 3. Handle Image Logic
    $image_url = $current_image;

    // New Image Uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload(
            $_FILES['image'], 
            'catalogue/sub_categories', 
            ['image/jpeg', 'image/png', 'image/webp']
        );

        if ($uploadResult['success']) {
            // Delete old image if exists
            if ($current_image) {
                deleteUploadedFile('catalogue/sub_categories/' . $current_image);
            }
            $image_url = $uploadResult['filename'];
        } else {
            $_SESSION['error'] = "Image upload failed: " . $uploadResult['error'];
            header("Location: edit.php?id=$id");
            exit;
        }
    }

    // 4. Update Database
    try {
        $sql = "UPDATE catalogue_sub_categories 
                SET category_id=?, name=?, image_url=?, description=?, display_order=?, is_active=? 
                WHERE id=?";
        execute($sql, [$category_id, $name, $image_url, $description, $display_order, $is_active, $id]);

        $_SESSION['success'] = "Sub Category updated successfully!";
        header("Location: list.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: edit.php?id=$id");
        exit;
    }

} else {
    // Not a POST request
    header("Location: list.php");
    exit;
}
