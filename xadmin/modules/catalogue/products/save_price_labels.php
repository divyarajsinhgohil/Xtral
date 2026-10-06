<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = !empty($_POST['is_ajax']) || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

    $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;

    if ($productId) {
        $label1 = isset($_POST['price_label_1']) ? trim($_POST['price_label_1']) : null;
        $label2 = isset($_POST['price_label_2']) ? trim($_POST['price_label_2']) : null;

        // Guaranteed storage via settings
        if ($label1 !== null) {
            updateSetting('prod_price_label_1_' . $productId, $label1 !== '' ? $label1 : '');
        }
        if ($label2 !== null) {
            updateSetting('prod_price_label_2_' . $productId, $label2 !== '' ? $label2 : '');
        }

        // Also update catalogue_products table
        try {
            if ($label1 !== null) {
                execute("UPDATE catalogue_products SET price_label_1 = ? WHERE id = ?", [$label1 !== '' ? $label1 : null, $productId]);
            }
            if ($label2 !== null) {
                execute("UPDATE catalogue_products SET price_label_2 = ? WHERE id = ?", [$label2 !== '' ? $label2 : null, $productId]);
            }
        } catch (\Throwable $t) {
            // Column may not exist or DB issue; settings storage provides guaranteed persistence
        }

        $prod = null;
        try {
            $prod = fetchOne("SELECT price_label_1, price_label_2 FROM catalogue_products WHERE id = ?", [$productId]);
        } catch (\Throwable $t) {}

        $currentLabel1 = getProductPriceLabel1($productId, $prod['price_label_1'] ?? null);
        $currentLabel2 = getProductPriceLabel2($productId, $prod['price_label_2'] ?? null);
    } else {
        if (isset($_POST['price_label_1'])) {
            $label1 = trim($_POST['price_label_1']);
            if ($label1 === '') $label1 = 'Zone 1';
            updateSetting('price_label_1', $label1);
        }
        if (isset($_POST['price_label_2'])) {
            $label2 = trim($_POST['price_label_2']);
            if ($label2 === '') $label2 = 'Zone 2';
            updateSetting('price_label_2', $label2);
        }

        $currentLabel1 = getPriceLabel1();
        $currentLabel2 = getPriceLabel2();
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'price_label_1' => $currentLabel1,
            'price_label_2' => $currentLabel2,
            'is_product_specific' => !empty($productId),
            'message' => !empty($productId) ? 'Product price titles updated.' : 'Global price titles updated.'
        ]);
        exit;
    }

    $_SESSION['success'] = "Price titles updated successfully to '{$currentLabel1}' and '{$currentLabel2}'.";

    $redirect = $_POST['redirect'] ?? 'list.php';
    if (!preg_match('/^[a-zA-Z0-9_\-\.\?&=]+$/', $redirect)) {
        $redirect = 'list.php';
    }

    header('Location: ' . $redirect);
    exit;
}

header('Location: list.php');
exit;
