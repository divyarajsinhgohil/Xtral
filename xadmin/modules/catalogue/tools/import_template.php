<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$type = $_GET['type'] ?? 'simple';

// Clean buffer
if (ob_get_length()) ob_end_clean();

// ============================================================
// SIMPLE PRODUCTS TEMPLATE
// ============================================================
if ($type === 'simple') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_import_template_simple.csv"');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'ProductCode(required)',
        'HSNCode(optional)',
        'ProductType(required)',
        'Price(required)',
        'PriceZone2(optional)',
        'DisplayOrder(optional)',
        'Dimensions(optional)',
        'Specifications(optional)',
        'Image1(required)',
        'Image2(optional)',
        'Image3(optional)',
        'Image4(optional)',
        'Image5(optional)'
    ]);

    // Sample Row
    fputcsv($output, [
        'Shower Systems',
        'None',
        'Thermostatic',
        'Rain Shower Head 200mm',
        'SH-RAIN-200',
        '84818090',
        'simple',
        '4500',
        '',
        '1',
        '200x200x50mm',
        'Stainless steel rain shower head',
        'C:/Images/rain_shower.jpg',
        '',
        '',
        '',
        ''
    ]);

    fclose($output);
    exit;
}

// ============================================================
// SIZE VARIANTS TEMPLATE
// ============================================================
if ($type === 'size') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_import_template_size_variants.csv"');

    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'ProductID(readonly)',
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'HSNCode(optional)',
        'ProductType(required)',
        'DisplayOrder(optional)',
        'Dimensions(optional)',
        'Specifications(optional)',
        'Image1(optional)',
        'Image2(optional)',
        'Image3(optional)',
        'Image4(optional)',
        'Image5(optional)',
        'VariantID(readonly)',
        'VariantName(optional)',
        'VariantCode(required)',
        'Size(required)',
        'Price(required)',
        'PriceZone2(optional)',
        'VariantImage(optional)'
    ]);

    // Sample Rows — 2 variants of same product
    fputcsv($output, [
        '', 'Faucets', 'Basin', 'Premium',
        'Basin Mixer', '84818090', 'size', '1', '',
        '', 'C:/Images/mixer.jpg', '', '', '', '',
        '', '15mm Model', 'BM-15', '15MM', '3500', '', ''
    ]);
    fputcsv($output, [
        '', 'Faucets', 'Basin', 'Premium',
        'Basin Mixer', '84818090', 'size', '1', '',
        '', 'C:/Images/mixer.jpg', '', '', '', '',
        '', '20mm Model', 'BM-20', '20MM', '4500', '', ''
    ]);

    fclose($output);
    exit;
}

// ==========================
// COLOR VARIANT TEMPLATE
// ==========================
if ($type === 'color') {
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="catalogue_color_variants_template.csv"');
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF");

    // Header
    fputcsv($output, [
        'ProductID(readonly)',
        'Category(required)',
        'SubCategory(optional)',
        'Series(required)',
        'ProductName(required)',
        'HSNCode(optional)',
        'ProductType(required)',
        'ColourLabel(optional)',
        'DisplayOrder(optional)',
        'Dimensions(optional)',
        'Specifications(optional)',
        'Image1(optional)',
        'Image2(optional)',
        'Image3(optional)',
        'Image4(optional)',
        'Image5(optional)',
        'VariantID(readonly)',
        'VariantName(optional)',
        'VariantCode(required)',
        'ColorName(required)',
        'ColorDisplayName(optional)',
        'Price(required)',
        'PriceZone2(optional)',
        'VariantImage(optional)'
    ]);

    // Sample Rows — 2 color variants of same product
    fputcsv($output, [
        '', 'Faucets', 'Basin', 'Premium',
        'Basin Mixer', '84818090', 'color', 'Colour', '1', '',
        'C:/Images/mixer.jpg', '', '', '', '',
        '', 'Chrome Model', 'BM-CHR', 'Chrome', '', '3500', '', ''
    ]);
    fputcsv($output, [
        '', 'Faucets', 'Basin', 'Premium',
        'Basin Mixer', '84818090', 'color', 'Colour', '1', '',
        'C:/Images/mixer.jpg', '', '', '', '',
        '', 'Black Model', 'BM-BLK', 'Matt Black', 'Matte Black Finish', '4500', '', ''
    ]);

    fclose($output);
    exit;
}

// Fallback
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="catalogue_import_template.csv"');
$output = fopen('php://output', 'w');
fputs($output, "\xEF\xBB\xBF");
fputcsv($output, ['Error: Unknown template type. Use ?type=simple, ?type=size, or ?type=color']);
fclose($output);
exit;
