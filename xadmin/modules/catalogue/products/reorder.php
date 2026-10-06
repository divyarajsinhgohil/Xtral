<?php
/**
 * AJAX Endpoint: Reorder products within a series / catalogue
 * POST /xadmin/modules/catalogue/products/reorder.php
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

try {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    $productIds = [];
    if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
        $productIds = $data['product_ids'];
    } elseif (!empty($_POST['product_ids'])) {
        $productIds = is_array($_POST['product_ids']) 
            ? $_POST['product_ids'] 
            : json_decode($_POST['product_ids'], true);
    }

    if (empty($productIds) || !is_array($productIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or empty product list provided']);
        exit;
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE catalogue_products SET display_order = :order WHERE id = :id");

    $order = 1;
    foreach ($productIds as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $stmt->execute([
                ':order' => $order,
                ':id'    => $id
            ]);
            $order++;
        }
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Product order updated successfully',
        'updated_count' => count($productIds)
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError('product reorder failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error while updating order: ' . $e->getMessage()
    ]);
    exit;
}
