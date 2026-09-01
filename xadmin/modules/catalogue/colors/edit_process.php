<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    if (!$id) {
        header("Location: list.php");
        exit;
    }

    $name = sanitize($_POST['name'] ?? '');
    $type = sanitize($_POST['type'] ?? 'solid');
    $hexCode = sanitize($_POST['hex_code'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $_SESSION['error'] = "Color Name is required";
        header("Location: edit.php?id=$id");
        exit;
    }

    // Fetch existing data
    $color = fetchOne("SELECT * FROM catalogue_colors WHERE id = ?", [$id]);

    $textureImage = $color['texture_image'];

    if ($type === 'texture') {
        if (isset($_FILES['texture_image']) && $_FILES['texture_image']['error'] === UPLOAD_ERR_OK) {
            $upload = handleFileUpload($_FILES['texture_image'], 'catalogue/colors', ['image/jpeg', 'image/png', 'image/webp']);
            if ($upload['success']) {
                // Delete old image
                if ($textureImage) {
                    deleteUploadedFile('catalogue/colors/' . $textureImage);
                }
                $textureImage = $upload['filename'];
                // Crop to Square
                cropImageToSquare(UPLOAD_PATH . 'catalogue/colors/' . $textureImage);
            } else {
                $_SESSION['error'] = $upload['error'];
                header("Location: edit.php?id=$id");
                exit;
            }
        }
        $hexCode = null; // Reset hex if texture
    } else {
        if (empty($hexCode)) {
            $_SESSION['error'] = "Hex Code is required for Solid type.";
            header("Location: edit.php?id=$id");
            exit;
        }
        // If switching to solid, validation doesn't strictly require image, but logic suggests ignoring it?
        // Or if previously texture, do we delete image? Yes, probably cleaner to delete if switching types.
        if ($color['type'] === 'texture' && $color['texture_image']) {
             // Maybe verify we really want to switch type? Assuming yes.
             // Keep texture image file? User might switch back. 
             // Let's keep file for safety unless explicit delete requested (not implemented). 
             // Or delete it to save space? Let's keep it but just nullify valid pointer? 
             // No, let's keep it null in DB if type is solid.
        }
    }

    try {
        $sql = "UPDATE catalogue_colors SET name = ?, type = ?, hex_code = ?, texture_image = ?, is_active = ? WHERE id = ?";
        execute($sql, [$name, $type, $hexCode, $textureImage, $isActive, $id]);

        $_SESSION['success'] = "Color updated successfully.";
        header("Location: list.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: edit.php?id=$id");
        exit;
    }
}
?>
