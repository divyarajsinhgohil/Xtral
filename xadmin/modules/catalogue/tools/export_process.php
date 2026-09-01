<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: export.php");
    exit;
}

$categoryId = $_POST['category_id'] ?? 'all';
$productType = $_POST['product_type'] ?? 'simple';
$pdo = getDBConnection();

// ============================================================
// SIMPLE PRODUCTS EXPORT
// ============================================================
if ($productType === 'simple') {

    $sql = "SELECT
                c.name as Category,
                c.has_dual_price as CategoryDualPrice,
                COALESCE(sc.name, 'None') as SubCategory,
                s.name as Series,
                p.id as ProductId,
                p.name as ProductName,
                p.code as ProductCode,
                p.hsn_code as HSNCode,
                p.variant_type as ProductType,
                p.price as Price,
                p.price_zone2 as PriceZone2,
                p.display_order as DisplayOrder,
                p.dimensions as Dimensions,
                p.specifications as Specifications
            FROM catalogue_products p
            JOIN catalogue_series s ON p.series_id = s.id
            LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id
            JOIN catalogue_categories c ON s.category_id = c.id
            WHERE p.variant_type = 'none'";

    $params = [];
    if ($categoryId !== 'all') {
        $sql .= " AND c.id = ?";
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY c.name, sc.name, s.name, p.display_order, p.name";
    $products = fetchAll($sql, $params);

    // Check dual pricing
    $hasDualPricing = false;
    foreach ($products as $p) {
        if ($p['CategoryDualPrice']) { $hasDualPricing = true; break; }
    }

    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_simple_export_' . date('Y-m-d_H-i') . '.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    // Header
    $headerRow = [
        'ProductID(readonly)', 'Category(required)', 'SubCategory(optional)',
        'Series(required)', 'ProductName(required)', 'ProductCode(required)',
        'HSNCode(required)', 'ProductType(required)', 'Price(required)',
    ];
    if ($hasDualPricing) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'DisplayOrder(optional)';
    $headerRow = array_merge($headerRow, [
        'Dimensions(optional)', 'Specifications(optional)',
        'Image1(required)', 'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)'
    ]);
    fputcsv($output, $headerRow);

    // Data
    $stmtImages = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC LIMIT 5");

    foreach ($products as $p) {
        $stmtImages->execute([$p['ProductId']]);
        $images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

        $row = [
            $p['ProductId'], $p['Category'], $p['SubCategory'], $p['Series'],
            $p['ProductName'], $p['ProductCode'] ?? '', $p['HSNCode'] ?? '', 'simple', $p['Price'] ?? '',
        ];
        if ($hasDualPricing) $row[] = $p['PriceZone2'] ?? '';
        $row[] = $p['DisplayOrder'] ?? 0;
        $row = array_merge($row, [
            $p['Dimensions'], specsToPlainText($p['Specifications']),
            $images[0] ?? '', $images[1] ?? '', $images[2] ?? '', $images[3] ?? '', $images[4] ?? '',
        ]);
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

// ============================================================
// SIZE VARIANTS EXPORT
// ============================================================
if ($productType === 'size') {

    // Get parent products with variant_type = 'size'
    $sql = "SELECT
                p.id as ProductId,
                c.name as Category,
                c.has_dual_price as CategoryDualPrice,
                COALESCE(sc.name, 'None') as SubCategory,
                s.name as Series,
                p.name as ProductName,
                p.hsn_code as HSNCode,
                p.display_order as DisplayOrder,
                p.dimensions as Dimensions,
                p.specifications as Specifications
            FROM catalogue_products p
            JOIN catalogue_series s ON p.series_id = s.id
            LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id
            JOIN catalogue_categories c ON s.category_id = c.id
            WHERE p.variant_type = 'size'";

    $params = [];
    if ($categoryId !== 'all') {
        $sql .= " AND c.id = ?";
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY c.name, sc.name, s.name, p.display_order, p.name";
    $products = fetchAll($sql, $params);

    // Check dual pricing
    $hasDualPricing = false;
    foreach ($products as $p) {
        if ($p['CategoryDualPrice']) { $hasDualPricing = true; break; }
    }

    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_size_variants_export_' . date('Y-m-d_H-i') . '.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    // Header — Common + Variant fields
    $headerRow = [
        'ProductID(readonly)', 'Category(required)', 'SubCategory(optional)',
        'Series(required)', 'ProductName(required)', 'HSNCode(required)', 'ProductType(required)',
        'DisplayOrder(optional)', 'Dimensions(optional)', 'Specifications(optional)',
        'Image1(optional)', 'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)',
        'VariantID(readonly)', 'VariantName(optional)', 'VariantCode(required)', 'Size(required)', 'Price(required)',
    ];
    if ($hasDualPricing) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'VariantImage(optional)';
    fputcsv($output, $headerRow);

    // Prepared statements
    $stmtImages = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC LIMIT 5");
    $stmtVariants = $pdo->prepare("SELECT id, name, code, attribute_value, price, price_zone2, image_url FROM catalogue_product_variants WHERE product_id = ? ORDER BY display_order ASC, id ASC");

    foreach ($products as $p) {
        // Fetch parent gallery images
        $stmtImages->execute([$p['ProductId']]);
        $images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

        // Fetch size variants
        $stmtVariants->execute([$p['ProductId']]);
        $variants = $stmtVariants->fetchAll(PDO::FETCH_ASSOC);

        if (empty($variants)) continue; // skip products with no variants

        foreach ($variants as $v) {
            $row = [
                $p['ProductId'], $p['Category'], $p['SubCategory'], $p['Series'],
                $p['ProductName'], $p['HSNCode'] ?? '', 'size',
                $p['DisplayOrder'] ?? 0, $p['Dimensions'] ?? '', specsToPlainText($p['Specifications'] ?? ''),
                $images[0] ?? '', $images[1] ?? '', $images[2] ?? '', $images[3] ?? '', $images[4] ?? '',
                $v['id'], $v['name'] ?? '', $v['code'], $v['attribute_value'], $v['price'],
            ];
            if ($hasDualPricing) $row[] = $v['price_zone2'] ?? '';
            $row[] = $v['image_url'] ?? '';
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

// ============================================================
// COLOR VARIANTS EXPORT
// ============================================================
if ($productType === 'color') {

    // Get parent products with variant_type = 'color'
    $sql = "SELECT
                p.id as ProductId,
                c.name as Category,
                c.has_dual_price as CategoryDualPrice,
                COALESCE(sc.name, 'None') as SubCategory,
                s.name as Series,
                p.name as ProductName,
                p.hsn_code as HSNCode,
                p.display_order as DisplayOrder,
                p.dimensions as Dimensions,
                p.colour_label as ColourLabel,
                p.specifications as Specifications
            FROM catalogue_products p
            JOIN catalogue_series s ON p.series_id = s.id
            LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id
            JOIN catalogue_categories c ON s.category_id = c.id
            WHERE p.variant_type = 'color'";

    $params = [];
    if ($categoryId !== 'all') {
        $sql .= " AND c.id = ?";
        $params[] = $categoryId;
    }

    $sql .= " ORDER BY c.name, sc.name, s.name, p.display_order, p.name";
    $products = fetchAll($sql, $params);

    // Check dual pricing
    $hasDualPricing = false;
    foreach ($products as $p) {
        if ($p['CategoryDualPrice']) { $hasDualPricing = true; break; }
    }

    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_color_variants_export_' . date('Y-m-d_H-i') . '.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    // Header — Common + Color Variant fields
    $headerRow = [
        'ProductID(readonly)', 'Category(required)', 'SubCategory(optional)',
        'Series(required)', 'ProductName(required)', 'HSNCode(required)', 'ProductType(required)',
        'ColourLabel(optional)', 'DisplayOrder(optional)', 'Dimensions(optional)', 'Specifications(optional)',
        'Image1(optional)', 'Image2(optional)', 'Image3(optional)', 'Image4(optional)', 'Image5(optional)',
        'VariantID(readonly)', 'VariantName(optional)', 'VariantCode(required)', 'ColorName(required)', 'ColorDisplayName(optional)', 'Price(required)',
    ];
    if ($hasDualPricing) $headerRow[] = 'PriceZone2(optional)';
    $headerRow[] = 'VariantImage(optional)';
    fputcsv($output, $headerRow);

    // Prepared statements
    $stmtImages = $pdo->prepare("SELECT image_url FROM catalogue_product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC LIMIT 5");
    $stmtVariants = $pdo->prepare("SELECT v.id, v.name, v.code, v.attribute_value, v.color_id, v.price, v.price_zone2, v.image_url, COALESCE(cl.name, v.attribute_value) as color_library_name FROM catalogue_product_variants v LEFT JOIN catalogue_colors cl ON v.color_id = cl.id WHERE v.product_id = ? ORDER BY v.display_order ASC, v.id ASC");

    foreach ($products as $p) {
        // Fetch parent gallery images
        $stmtImages->execute([$p['ProductId']]);
        $images = $stmtImages->fetchAll(PDO::FETCH_COLUMN);

        // Fetch color variants
        $stmtVariants->execute([$p['ProductId']]);
        $variants = $stmtVariants->fetchAll(PDO::FETCH_ASSOC);

        if (empty($variants)) continue; // skip products with no variants

        foreach ($variants as $v) {
            $row = [
                $p['ProductId'], $p['Category'], $p['SubCategory'], $p['Series'],
                $p['ProductName'], $p['HSNCode'] ?? '', 'color',
                ucfirst($p['ColourLabel'] ?? 'colour'),
                $p['DisplayOrder'] ?? 0, $p['Dimensions'] ?? '', specsToPlainText($p['Specifications'] ?? ''),
                $images[0] ?? '', $images[1] ?? '', $images[2] ?? '', $images[3] ?? '', $images[4] ?? '',
                $v['id'], $v['name'] ?? '', $v['code'], $v['color_library_name'], $v['attribute_value'], $v['price'],
            ];
            if ($hasDualPricing) $row[] = $v['price_zone2'] ?? '';
            $row[] = $v['image_url'] ?? '';
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}

// Fallback — unsupported type
$_SESSION['error'] = "Unsupported product type: " . htmlspecialchars($productType);
header("Location: export.php");
exit;

/**
 * Convert HTML specifications to plain text with real newlines for CSV export.
 * Real newlines in a CSV cell = Alt+Enter multiline in Excel.
 */
function specsToPlainText(?string $html): string {
    if (empty($html)) return '';
    // Convert <br> / <br/> / <br /> to newlines
    $text = preg_replace('/<br\s*\/?>/i', "\n", $html);
    // Convert </p><p> and </li> to newlines
    $text = preg_replace('/<\/p>\s*<p>/i', "\n", $text);
    $text = preg_replace('/<\/li>/i', "\n", $text);
    // Strip all remaining HTML tags
    $text = strip_tags($text);
    // Decode HTML entities (e.g. &amp; &lt;)
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim($text);
}
