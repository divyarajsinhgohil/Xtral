<?php
/**
 * Sub-categories for the front side (Category → Sub-category drill-down)
 * GET /xadmin/api/webapi/sub_categories.php
 * Optional filter: ?category_id=1
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $where = "sc.is_active = 1 AND c.is_active = 1";
    $params = [];

    if (!empty($_GET['category_id']) && ctype_digit($_GET['category_id'])) {
        $where .= " AND sc.category_id = ?";
        $params[] = (int) $_GET['category_id'];
    }

    $rows = fetchAll("
        SELECT sc.id, sc.category_id, sc.name, sc.image_url, sc.description
        FROM catalogue_sub_categories sc
        JOIN catalogue_categories c ON sc.category_id = c.id
        WHERE $where
        ORDER BY sc.display_order, sc.id
    ", $params);

    $subCategories = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'category_id' => (int) $row['category_id'],
            'name' => $row['name'],
            'image' => uploadUrl($row['image_url'], 'catalogue/sub_categories'),
            'description' => $row['description'] ?: null,
        ];
    }, $rows);

    jsonResponse(true, $subCategories, 'Sub-categories loaded');
} catch (Exception $e) {
    logError('webapi/sub_categories: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
