<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: export.php");
    exit;
}

$categoryId = $_POST['category_id'] ?? '';
$productType = $_POST['product_type'] ?? 'simple';

if (empty($categoryId) || $categoryId === 'all') {
    $_SESSION['error'] = "Please select a specific category to export structure.";
    header("Location: export.php");
    exit;
}

$pdo = getDBConnection();

// Fetch category name
$category = fetchOne("SELECT id, name, has_dual_price FROM catalogue_categories WHERE id = ?", [$categoryId]);
if (!$category) {
    $_SESSION['error'] = "Category not found.";
    header("Location: export.php");
    exit;
}

// Fetch all Series under this category (with optional sub_category)
$sql = "SELECT 
            c.name as Category,
            COALESCE(sc.name, 'None') as SubCategory,
            s.name as Series
        FROM catalogue_series s
        JOIN catalogue_categories c ON s.category_id = c.id
        LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id
        WHERE s.category_id = ? AND s.is_active = 1
        ORDER BY sc.name ASC, s.name ASC";

$rows = fetchAll($sql, [$categoryId]);

if (empty($rows)) {
    $_SESSION['error'] = "No series found for this category. Please create Series first in admin.";
    header("Location: export.php");
    exit;
}

// Clean buffer
if (ob_get_length()) ob_end_clean();

// Headers
$typeLabel = $productType === 'color' ? 'color_variants' : ($productType === 'size' ? 'size_variants' : 'simple');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="catalogue_structure_' . $typeLabel . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $category['name']) . '_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

// BOM for Excel
fputs($output, "\xEF\xBB\xBF");

// ============================================================
// SIMPLE PRODUCTS STRUCTURE
// ============================================================
if ($productType === 'simple') {
    $headerRow = [
        'ProductID(readonly)',
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'ProductCode(required)',
        'ProductType(required)',
        'Price(required)',
    ];
    if ($category['has_dual_price']) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'DisplayOrder(optional)';
    $headerRow = array_merge($headerRow, [
        'Dimensions(optional)',
        'Specifications(optional)',
        'Image1(required)',
        'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)'
    ]);
    fputcsv($output, $headerRow);

    // One row per Series — hierarchy pre-filled, product columns blank
    foreach ($rows as $r) {
        $row = [
            '', $r['Category'], $r['SubCategory'], $r['Series'],
            '', '', 'simple', '',
        ];
        if ($category['has_dual_price']) $row[] = '';
        $row[] = '';
        $row = array_merge($row, ['', '', '', '', '', '', '']);
        fputcsv($output, $row);
    }
}

// ============================================================
// SIZE VARIANTS STRUCTURE
// ============================================================
if ($productType === 'size') {
    $headerRow = [
        'ProductID(readonly)',
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'ProductType(required)',
        'DisplayOrder(optional)',
        'Specifications(optional)',
        'Image1(optional)', 'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)',
        'VariantName(optional)',
        'VariantCode(required)',
        'Size(required)',
        'Price(required)',
    ];
    if ($category['has_dual_price']) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'VariantImage(optional)';
    fputcsv($output, $headerRow);

    // One row per Series — hierarchy pre-filled, variant columns blank
    foreach ($rows as $r) {
        $row = [
            '', $r['Category'], $r['SubCategory'], $r['Series'],
            '', 'size', '', '',
            '', '', '', '', '',
            '', '', '', '',
        ];
        if ($category['has_dual_price']) $row[] = '';
        $row[] = '';
        fputcsv($output, $row);
    }
}

// ============================================================
// COLOR VARIANTS STRUCTURE
// ============================================================
if ($productType === 'color') {
    $headerRow = [
        'ProductID(readonly)',
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'ProductType(required)',
        'DisplayOrder(optional)',
        'Specifications(optional)',
        'Image1(optional)', 'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)',
        'VariantName(optional)',
        'VariantCode(required)',
        'ColorName(required)',
        'ColorDisplayName(optional)',
        'Price(required)',
    ];
    if ($category['has_dual_price']) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'VariantImage(optional)';
    fputcsv($output, $headerRow);

    // One row per Series — hierarchy pre-filled, variant columns blank
    foreach ($rows as $r) {
        $row = [
            '', $r['Category'], $r['SubCategory'], $r['Series'],
            '', 'color', '', '',
            '', '', '', '', '',
            '', '', '', '', '',
        ];
        if ($category['has_dual_price']) $row[] = '';
        $row[] = '';
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
