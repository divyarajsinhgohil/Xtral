<?php
/**
 * Home banners for the front side
 * GET /xadmin/api/webapi/banners.php
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $rows = fetchAll("
        SELECT id, title, banner_type, image_url, video_url, link_url
        FROM catalogue_banners
        WHERE is_active = 1
        ORDER BY display_order, id
    ");

    $banners = array_map(function ($row) {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'banner_type' => $row['banner_type'] ?? 'image',
            'image' => uploadUrl($row['image_url'], 'catalogue/banners'),
            'video' => uploadUrl($row['video_url'], 'catalogue/banners'),
            'link' => $row['link_url'] ?: null,
        ];
    }, $rows);

    jsonResponse(true, $banners, 'Banners loaded');
} catch (Exception $e) {
    logError('webapi/banners: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
