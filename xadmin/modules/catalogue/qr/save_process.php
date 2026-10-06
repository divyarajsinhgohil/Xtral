<?php
/**
 * QR Code Save Handler (Create & Update)
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

try {
    $action = $_POST['action'] ?? 'create';
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
    
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $code = preg_replace('/[^A-Z0-9_-]/', '', $code);
    
    $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $targetUrl = trim($_POST['target_url'] ?? '');
    $label = trim($_POST['label'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($code)) {
        throw new Exception("Permanent QR code identifier is required.");
    }

    if ($action === 'create') {
        // Check uniqueness
        $existing = fetchOne("SELECT id FROM catalogue_qr_codes WHERE code = ? LIMIT 1", [$code]);
        if ($existing) {
            throw new Exception("The QR code '{$code}' already exists. Please choose a different code.");
        }

        $sql = "INSERT INTO catalogue_qr_codes (code, product_id, label, target_url, is_active) 
                VALUES (?, ?, ?, ?, ?)";
        execute($sql, [$code, $productId, $label, $targetUrl ?: null, $isActive]);
        $_SESSION['success'] = "Permanent QR code '{$code}' created successfully.";

    } elseif ($action === 'edit') {
        if (!$id) {
            throw new Exception("Invalid QR record ID.");
        }

        // Check if code was changed and is unique
        $existing = fetchOne("SELECT id FROM catalogue_qr_codes WHERE code = ? AND id != ? LIMIT 1", [$code, $id]);
        if ($existing) {
            throw new Exception("The QR code '{$code}' is already in use by another record.");
        }

        $sql = "UPDATE catalogue_qr_codes 
                SET code = ?, product_id = ?, label = ?, target_url = ?, is_active = ? 
                WHERE id = ?";
        execute($sql, [$code, $productId, $label, $targetUrl ?: null, $isActive, $id]);
        $_SESSION['success'] = "QR code '{$code}' updated successfully. Target product link changed without altering printed QR.";

    } elseif ($action === 'reassign_quick') {
        // Quick inline product switch from list modal
        if (!$id) {
            throw new Exception("Invalid QR record ID.");
        }
        $sql = "UPDATE catalogue_qr_codes SET product_id = ? WHERE id = ?";
        execute($sql, [$productId, $id]);
        $_SESSION['success'] = "Target product successfully updated for this QR code.";
    }

} catch (\Throwable $e) {
    $_SESSION['error'] = $e->getMessage();
    if ($action === 'edit' && !empty($id)) {
        header("Location: edit.php?id={$id}");
        exit;
    }
}

header('Location: list.php');
exit;
