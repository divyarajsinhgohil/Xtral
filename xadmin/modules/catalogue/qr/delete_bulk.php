<?php
/**
 * Bulk Delete QR Codes
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

$qrIds = $_POST['qr_ids'] ?? [];
if (!is_array($qrIds) || empty($qrIds)) {
    $_SESSION['error'] = "No QR codes selected for deletion.";
    header('Location: list.php');
    exit;
}

$ids = array_filter(array_map('intval', $qrIds));
if (empty($ids)) {
    $_SESSION['error'] = "Invalid QR code IDs.";
    header('Location: list.php');
    exit;
}

try {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $count = execute("DELETE FROM catalogue_qr_codes WHERE id IN ($placeholders)", array_values($ids));
    $_SESSION['success'] = "Successfully deleted " . count($ids) . " QR code(s).";
} catch (\Throwable $e) {
    $_SESSION['error'] = "Failed to delete selected QR codes: " . $e->getMessage();
}

header('Location: list.php');
exit;
