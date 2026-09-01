<?php
/**
 * Series for the front side (Sub-category → Series drill-down)
 * GET /xadmin/api/webapi/series.php
 * Optional filters: ?category_id=1  ?sub_category_id=2
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $where = "s.is_active = 1 AND c.is_active = 1";
    $params = [];

    if (!empty($_GET['category_id']) && ctype_digit($_GET['category_id'])) {
        $where .= " AND s.category_id = ?";
        $params[] = (int) $_GET['category_id'];
    }
    if (!empty($_GET['sub_category_id']) && ctype_digit($_GET['sub_category_id'])) {
        $where .= " AND s.sub_category_id = ?";
        $params[] = (int) $_GET['sub_category_id'];
    }

    $rows = fetchAll("
        SELECT s.id, s.category_id, s.sub_category_id, s.name, s.image_url, s.description, s.is_new_arrival
        FROM catalogue_series s
        JOIN catalogue_categories c ON s.category_id = c.id
        WHERE $where
        ORDER BY s.display_order, s.id
    ", $params);

    $series = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'category_id' => (int) $row['category_id'],
            'sub_category_id' => $row['sub_category_id'] !== null ? (int) $row['sub_category_id'] : null,
            'name' => $row['name'],
            'image' => uploadUrl($row['image_url'], 'catalogue/series'),
            'description' => $row['description'] ?: null,
            'is_new_arrival' => (bool) $row['is_new_arrival'],
        ];
    }, $rows);

    jsonResponse(true, $series, 'Series loaded');
} catch (Exception $e) {
    logError('webapi/series: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
