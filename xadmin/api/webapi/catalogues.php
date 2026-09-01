<?php
/**
 * Catalogue PDFs for the front side
 * GET /xadmin/api/webapi/catalogues.php
 *
 * Reads from the `whatsapp_catalogues` table — managed in xadmin under
 * Catalogue → Website Catalogues (modules/settings/whatsapp_catalogues.php).
 * Only active rows are returned, in the admin's chosen display order.
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $pdfDir = UPLOAD_PATH . 'catalogue/pdfs/';
    $rows = fetchAll("
        SELECT id, name, pdf_filename, thumb_filename
        FROM whatsapp_catalogues
        WHERE is_active = 1
        ORDER BY display_order ASC, id ASC
    ");

    $result = array_map(function ($row) use ($pdfDir) {
        $path = $pdfDir . $row['pdf_filename'];
        return [
            'id' => (int) $row['id'],
            'category' => $row['name'],
            'title' => $row['name'],
            'url' => uploadUrl($row['pdf_filename'], 'catalogue/pdfs'),
            'thumb_url' => uploadUrl($row['thumb_filename'], 'catalogue/pdfs/Thumb'),
            'size_mb' => file_exists($path) ? round(filesize($path) / 1048576, 1) : null,
        ];
    }, $rows);

    jsonResponse(true, $result, 'Catalogues loaded');
} catch (Exception $e) {
    logError('webapi/catalogues: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
