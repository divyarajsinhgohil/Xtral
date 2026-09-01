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
    $displayOrder = (int)($_POST['display_order'] ?? 0);
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name)) {
        $_SESSION['error'] = "Feature Name is required.";
        header("Location: edit.php?id=$id");
        exit;
    }

    $feature = fetchOne("SELECT * FROM catalogue_features WHERE id = ?", [$id]);
    if (!$feature) {
        $_SESSION['error'] = "Feature not found.";
        header("Location: list.php");
        exit;
    }

    $iconUrl = $feature['icon_url'];

    // Handle Upload if new file provided
    if (isset($_FILES['icon_image']) && $_FILES['icon_image']['error'] === UPLOAD_ERR_OK) {
        $upload = handleFileUpload($_FILES['icon_image'], 'catalogue/features', ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml']);
        if ($upload['success']) {
            if ($iconUrl) {
                deleteUploadedFile('catalogue/features/' . $iconUrl);
            }
            $iconUrl = $upload['filename'];
        } else {
            $_SESSION['error'] = $upload['error'];
            header("Location: edit.php?id=$id");
            exit;
        }
    }

    try {
        $sql = "UPDATE catalogue_features SET name = ?, icon_url = ?, display_order = ?, is_active = ? WHERE id = ?";
        execute($sql, [$name, $iconUrl, $displayOrder, $isActive, $id]);

        $_SESSION['success'] = "Feature updated successfully.";
        header("Location: list.php");
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Database Error: " . $e->getMessage();
        header("Location: edit.php?id=$id");
        exit;
    }
}
