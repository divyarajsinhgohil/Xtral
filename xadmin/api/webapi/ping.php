<?php
/**
 * Health-check endpoint
 * GET <base>/api/webapi/ping.php
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $db = fetchOne('SELECT DATABASE() AS db, NOW() AS server_time');
    jsonResponse(true, [
        'app' => 'Xtral Web API',
        'database' => $db['db'],
        'server_time' => $db['server_time'],
    ], 'API is working');
} catch (Exception $e) {
    http_response_code(500);
    jsonResponse(false, null, 'Database connection failed');
}
