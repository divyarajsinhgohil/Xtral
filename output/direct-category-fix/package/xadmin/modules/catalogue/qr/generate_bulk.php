<?php
/**
 * Bulk QR Code Generator
 * Supports generating for All Products, Category-wise, or Series-wise
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$productCategorySql = $productHierarchy['category'];
$productSubCategorySql = $productHierarchy['sub_category'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    $scope = $_POST['scope'] ?? 'all'; // all, category, series
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $seriesId = !empty($_POST['series_id']) ? (int)$_POST['series_id'] : null;
    $prefix = trim($_POST['prefix'] ?? 'XT-');
    if (empty($prefix)) $prefix = 'XT-';
    $prefix = strtoupper(preg_replace('/[^A-Za-z0-9_-]/', '', $prefix));
    if (!str_ends_with($prefix, '-') && !str_ends_with($prefix, '_')) {
        $prefix .= '-';
    }

    $where = ["p.is_active = 1"];
    $params = [];

    // Filter scope
    if ($scope === 'series' && $seriesId) {
        $where[] = "p.series_id = ?";
        $params[] = $seriesId;
    } elseif ($scope === 'category' && $categoryId) {
        $where[] = "{$productCategorySql} = ?";
        $params[] = $categoryId;
    }

    // Exclude products that already have a linked QR code
    $where[] = "p.id NOT IN (SELECT product_id FROM catalogue_qr_codes WHERE product_id IS NOT NULL)";

    $whereSql = implode(" AND ", $where);
    $sql = "SELECT p.id, p.name, p.code, s.name as series_name, c.name as category_name
            FROM catalogue_products p
            LEFT JOIN catalogue_series s ON p.series_id = s.id
            JOIN catalogue_categories c ON {$productCategorySql} = c.id
            WHERE {$whereSql}
            ORDER BY c.name ASC, s.name ASC, p.id ASC";
    $products = fetchAll($sql, $params);

    if (empty($products)) {
        $_SESSION['info'] = "No missing products found for the selected scope. All matching products already have QR codes.";
        header('Location: list.php');
        exit;
    }

    // Determine current highest numeric code with this prefix
    $cleanPrefix = rtrim($prefix, '-_');
    $maxRow = fetchOne("SELECT MAX(CAST(SUBSTRING(code, " . (strlen($prefix) + 1) . ") AS UNSIGNED)) as max_num 
                        FROM catalogue_qr_codes 
                        WHERE code LIKE ?", [$prefix . '%']);
    $nextNum = max(1001, ((int)($maxRow['max_num'] ?? 0)) + 1);

    $stmt = $pdo->prepare("INSERT INTO catalogue_qr_codes (code, product_id, label, is_active) VALUES (?, ?, ?, 1)");

    $count = 0;
    foreach ($products as $p) {
        $code = $prefix . $nextNum;
        $label = $p['name'] . (!empty($p['code']) && $p['code'] !== '—' ? ' (' . $p['code'] . ')' : '');
        
        while (fetchOne("SELECT id FROM catalogue_qr_codes WHERE code = ?", [$code])) {
            $nextNum++;
            $code = $prefix . $nextNum;
        }

        $stmt->execute([$code, $p['id'], $label]);
        $nextNum++;
        $count++;
    }

    $scopeText = $scope === 'series' ? 'series' : ($scope === 'category' ? 'category' : 'catalogue');
    $_SESSION['success'] = "Generated {$count} permanent QR codes for your selected {$scopeText}.";
} catch (\Throwable $e) {
    $_SESSION['error'] = "Error generating QR codes: " . $e->getMessage();
}

header('Location: list.php');
exit;
