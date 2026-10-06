<?php
/**
 * Delete QR Code
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        try {
            execute("DELETE FROM catalogue_qr_codes WHERE id = ?", [$id]);
            $_SESSION['success'] = "QR code deleted successfully.";
        } catch (\Throwable $e) {
            $_SESSION['error'] = "Failed to delete QR code: " . $e->getMessage();
        }
    }
}

header('Location: list.php');
exit;
