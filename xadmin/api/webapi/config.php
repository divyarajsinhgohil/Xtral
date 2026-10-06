<?php
/**
 * Common config/bootstrap for all Web APIs in api/webapi/
 * Include this at the top of every new API endpoint:
 *   require_once __DIR__ . '/config.php';
 */

if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    @ini_set('zlib.output_compression', '1');
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

define('API_JSON_RESPONSE', true);

require_once dirname(__DIR__, 2) . '/config/db.php';

/**
 * Read JSON body of the request
 * @return array
 */
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Require a specific HTTP method, otherwise respond 405
 * @param string $method e.g. 'POST'
 */
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        http_response_code(405);
        jsonResponse(false, null, 'Method not allowed');
    }
}

/**
 * Absolute base URL of the admin panel (scheme + host + BASE_URL).
 * Lets APIs return full image URLs that keep working when the
 * front side is deployed on another folder or domain.
 */
function baseUrlAbsolute(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . $_SERVER['HTTP_HOST'] . BASE_URL;
}

/**
 * Full public URL for an uploaded file
 * @param string|null $file Filename stored in DB (or already-absolute URL)
 * @param string $folder Folder under uploads/, e.g. 'catalogue/products'
 * @return string|null
 */
function uploadUrl(?string $file, string $folder): ?string
{
    if (empty($file)) {
        return null;
    }
    if (preg_match('#^https?://#i', $file)) {
        return $file;
    }
    return baseUrlAbsolute() . '/uploads/' . $folder . '/' . rawurlencode($file);
}
