<?php
/**
 * X-Tral — Dynamic QR Code Redirection Handler
 * Permanent Parent Link for printed catalogues.
 * Usage: https://yourdomain.com/q/QR_CODE
 */

require_once __DIR__ . '/xadmin/config/db.php';

$frontDocRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$frontDir = str_replace('\\', '/', realpath(__DIR__));
$frontBasePath = '';
if ($frontDocRoot !== '' && stripos($frontDir, $frontDocRoot) === 0) {
    $frontBasePath = rtrim(substr($frontDir, strlen($frontDocRoot)), '/');
}

function qrRedirect($path, $basePath) {
    if (!preg_match('#^https?://#i', $path) && strpos($path, '/') !== 0) {
        $path = ($basePath !== '' ? $basePath : '') . '/' . ltrim($path, '/');
    }
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header("Location: " . $path, true, 302);
    exit;
}

$code = trim($_GET['c'] ?? $_GET['code'] ?? $_GET['id'] ?? '');

if ($code === '') {
    qrRedirect('products', $frontBasePath);
}

try {
    $sql = "SELECT q.*, p.id AS prod_id, p.code AS prod_code, p.is_active AS prod_active 
            FROM catalogue_qr_codes q
            LEFT JOIN catalogue_products p ON q.product_id = p.id
            WHERE q.code = ? AND q.is_active = 1
            LIMIT 1";
    $qr = fetchOne($sql, [$code]);

    if (!$qr) {
        $cleanCode = preg_replace('/[^a-zA-Z0-9]/', '', $code);
        // Fallback: If code itself matches a product code or ID directly, go to it
        $pWhere = ["code = ?", "REPLACE(code, ' ', '-') = ?", "REPLACE(code, '-', ' ') = ?"];
        $pParams = [$code, $code, $code];
        if ($cleanCode !== '') {
            $pWhere[] = "REPLACE(code, '-', '') = ?";
            $pParams[] = $cleanCode;
        }
        $p = fetchOne("SELECT id, code FROM catalogue_products WHERE (" . implode(' OR ', $pWhere) . ") AND is_active = 1 LIMIT 1", $pParams);
        if ($p) {
            $slug = !empty($p['code']) && $p['code'] !== '—' ? preg_replace('/[^a-zA-Z0-9]/', '', $p['code']) : $p['id'];
            qrRedirect('products/' . rawurlencode($slug), $frontBasePath);
        }

        $vWhere = ["v.code = ?", "REPLACE(v.code, ' ', '-') = ?", "REPLACE(v.code, '-', ' ') = ?"];
        $vParams = [$code, $code, $code];
        if ($cleanCode !== '') {
            $vWhere[] = "REPLACE(v.code, '-', '') = ?";
            $vParams[] = $cleanCode;
        }
        $v = fetchOne("SELECT p.id, p.code, v.code as var_code FROM catalogue_product_variants v JOIN catalogue_products p ON v.product_id = p.id WHERE (" . implode(' OR ', $vWhere) . ") AND v.is_active = 1 AND p.is_active = 1 LIMIT 1", $vParams);
        if ($v) {
            $slug = !empty($v['var_code']) ? preg_replace('/[^a-zA-Z0-9]/', '', $v['var_code']) : (!empty($v['code']) ? preg_replace('/[^a-zA-Z0-9]/', '', $v['code']) : $v['id']);
            qrRedirect('products/' . rawurlencode($slug), $frontBasePath);
        }
        qrRedirect('products', $frontBasePath);
    }

    // Increment scan analytics count (silent, non-blocking)
    try {
        execute("UPDATE catalogue_qr_codes SET scan_count = scan_count + 1 WHERE id = ?", [$qr['id']]);
    } catch (\Throwable $t) {
        // Non-fatal if scan logging fails
    }

    // Determine target destination (Child Link) - Clean extensionless URL
    $destination = 'products';

    if (!empty($qr['target_url'])) {
        $destination = $qr['target_url'];
    } elseif (!empty($qr['prod_id'])) {
        $slug = !empty($qr['prod_code']) && $qr['prod_code'] !== '—'
            ? preg_replace('/[^a-zA-Z0-9]/', '', $qr['prod_code'])
            : $qr['prod_id'];
        $destination = 'products/' . rawurlencode($slug);
    }

    qrRedirect($destination, $frontBasePath);

} catch (\Throwable $e) {
    qrRedirect('products', $frontBasePath);
}
