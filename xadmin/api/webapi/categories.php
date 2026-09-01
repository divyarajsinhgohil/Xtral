<?php
/**
 * Product categories for the front side
 * GET /xadmin/api/webapi/categories.php
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $rows = fetchAll("
        SELECT id, name, image_url, description
        FROM catalogue_categories
        WHERE is_active = 1
        ORDER BY display_order, id
    ");

    $categories = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'image' => uploadUrl($row['image_url'], 'catalogue/categories'),
            'description' => $row['description'] ?: null,
        ];
    }, $rows);

    jsonResponse(true, $categories, 'Categories loaded');
} catch (Exception $e) {
    logError('webapi/categories: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
