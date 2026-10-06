<?php
/**
 * AJAX Endpoint: Reorder series within a category / catalogue
 * POST /xadmin/modules/catalogue/series/reorder.php
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

    $seriesIds = [];
    if (!empty($data['series_ids']) && is_array($data['series_ids'])) {
        $seriesIds = $data['series_ids'];
    } elseif (!empty($_POST['series_ids'])) {
        $seriesIds = is_array($_POST['series_ids']) 
            ? $_POST['series_ids'] 
            : json_decode($_POST['series_ids'], true);
    }

    if (empty($seriesIds) || !is_array($seriesIds)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or empty series list provided']);
        exit;
    }

    $pdo = getDbConnection();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE catalogue_series SET display_order = :order WHERE id = :id");

    $order = 1;
    foreach ($seriesIds as $id) {
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
        'message' => 'Series order updated successfully',
        'updated_count' => count($seriesIds)
    ]);
    exit;

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logError('series reorder failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error while updating series order: ' . $e->getMessage()
    ]);
    exit;
}
