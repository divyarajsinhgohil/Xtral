<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $type = sanitize($_POST['type'] ?? 'solid');
    $hexCode = sanitize($_POST['hex_code'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $_SESSION['error'] = "Color Name is required";
        header("Location: create.php");
        exit;
    }

    $textureImage = null;

    if ($type === 'texture') {
        if (isset($_FILES['texture_image']) && $_FILES['texture_image']['error'] === UPLOAD_ERR_OK) {
            $upload = handleFileUpload($_FILES['texture_image'], 'catalogue/colors', ['image/jpeg', 'image/png', 'image/webp']);
            if ($upload['success']) {
                $textureImage = $upload['filename'];
                // Crop to Square
                cropImageToSquare(UPLOAD_PATH . 'catalogue/colors/' . $textureImage);
            } else {
                $_SESSION['error'] = $upload['error'];
                header("Location: create.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Texture Image is required for Texture type.";
            header("Location: create.php");
            exit;
        }
        $hexCode = null; // Reset hex if texture
    } else {
        if (empty($hexCode)) {
            $_SESSION['error'] = "Hex Code is required for Solid type.";
            header("Location: create.php");
            exit;
        }
    }

    try {
        $sql = "INSERT INTO catalogue_colors (name, type, hex_code, texture_image, is_active) VALUES (?, ?, ?, ?, ?)";
        insert($sql, [$name, $type, $hexCode, $textureImage, $isActive]);

        $_SESSION['success'] = "Color added successfully.";
        header("Location: list.php"); // Updated redirect to list.php
        exit;
    } catch (Exception $e) {
        // Cleanup image if insert failed
        if ($textureImage) {
            deleteUploadedFile('catalogue/colors/' . $textureImage);
        }
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: create.php");
        exit;
    }
}
?>
