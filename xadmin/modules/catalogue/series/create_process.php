<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Sanitize Input
    $category_id = (int)($_POST['category_id'] ?? 0);
    $sub_category_id = isset($_POST['sub_category_id']) && $_POST['sub_category_id'] !== '' ? (int)$_POST['sub_category_id'] : null;
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $display_order = (int)($_POST['display_order'] ?? 0);
    if ($display_order <= 0) {
        $maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_series");
        $display_order = ($maxOrder['max_order'] ?? 0) + 1;
    }
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;

    // 2. Validate
    if (empty($category_id) || empty($name)) {
        $_SESSION['error'] = "Main Category and Name are required.";
        header("Location: create.php");
        exit;
    }

    // 3. Handle Image Upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload(
            $_FILES['image'], 
            'catalogue/series', 
            ['image/jpeg', 'image/png', 'image/webp']
        );

        if ($uploadResult['success']) {
            $image_url = $uploadResult['filename'];
        } else {
            $_SESSION['error'] = "Image upload failed: " . $uploadResult['error'];
            header("Location: create.php");
            exit;
        }
    }

    // 4. Insert into Database
    try {
        $sql = "INSERT INTO catalogue_series (category_id, sub_category_id, name, image_url, description, display_order, is_active, is_new_arrival) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        execute($sql, [$category_id, $sub_category_id, $name, $image_url, $description, $display_order, $is_active, $is_new_arrival]);

        $_SESSION['success'] = "Series '$name' created successfully!";
        header("Location: list.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        if ($image_url) {
            deleteUploadedFile('catalogue/series/' . $image_url);
        }
        header("Location: create.php");
        exit;
    }

} else {
    header("Location: list.php");
    exit;
}
