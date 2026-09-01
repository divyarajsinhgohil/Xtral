<?php
/**
 * Products for the front side
 * GET /xadmin/api/webapi/products.php
 * Optional filters:
 *   ?category_id=1     only products of one category
 *   ?new_arrivals=1    only new-arrival products
 */
require_once __DIR__ . '/config.php';

requireMethod('GET');

try {
    $where = "p.is_active = 1 AND s.is_active = 1 AND c.is_active = 1";
    $params = [];

    if (!empty($_GET['category_id']) && ctype_digit($_GET['category_id'])) {
        $where .= " AND c.id = ?";
        $params[] = (int) $_GET['category_id'];
    }
    if (!empty($_GET['new_arrivals'])) {
        $where .= " AND p.is_new_arrival = 1";
    }

    $rows = fetchAll("
        SELECT
            p.id, p.name, p.code, p.price, p.dimensions,
            p.specifications, p.is_new_arrival,
            p.variant_type, p.colour_label, p.video_url,
            s.id AS series_id, s.name AS series_name, s.sub_category_id,
            c.id AS category_id, c.name AS category_name,
            (SELECT pi.image_url
             FROM catalogue_product_images pi
             WHERE pi.product_id = p.id
             ORDER BY pi.is_primary DESC, pi.display_order, pi.id
             LIMIT 1) AS image_url
        FROM catalogue_products p
        JOIN catalogue_series s ON p.series_id = s.id
        JOIN catalogue_categories c ON s.category_id = c.id
        WHERE $where
        ORDER BY p.display_order, p.id
    ", $params);

    // Load variants of all returned products in one query
    $variantsByProduct = [];
    $imagesByProduct = [];
    $productIds = array_column($rows, 'id');
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        // 1. Fetch variants
        $variantRows = fetchAll("
            SELECT v.product_id, v.code, v.price, v.attribute_value, v.image_url,
                   col.hex_code
            FROM catalogue_product_variants v
            LEFT JOIN catalogue_colors col ON v.color_id = col.id
            WHERE v.is_active = 1 AND v.product_id IN ($placeholders)
            ORDER BY v.product_id, v.display_order, v.id
        ", $productIds);
 
        foreach ($variantRows as $v) {
            $variantsByProduct[$v['product_id']][] = [
                'name' => $v['attribute_value'],
                'code' => $v['code'],
                'price' => (int) $v['price'],
                'color_hex' => $v['hex_code'] ?: null,
                'image' => uploadUrl($v['image_url'], 'catalogue/products'),
            ];
        }

        // 2. Fetch all product images
        $imageRows = fetchAll("
            SELECT product_id, image_url
            FROM catalogue_product_images
            WHERE product_id IN ($placeholders)
            ORDER BY is_primary DESC, display_order, id
        ", $productIds);

        foreach ($imageRows as $img) {
            $imagesByProduct[$img['product_id']][] = uploadUrl($img['image_url'], 'catalogue/products');
        }

        // 3. Fetch all product features
        $featuresByProduct = [];
        $featureRows = fetchAll("
            SELECT pf.product_id, f.name, f.icon_url
            FROM catalogue_product_features pf
            JOIN catalogue_features f ON pf.feature_id = f.id
            WHERE f.is_active = 1 AND pf.product_id IN ($placeholders)
            ORDER BY f.display_order, f.name
        ", $productIds);

        foreach ($featureRows as $f) {
            $featuresByProduct[$f['product_id']][] = [
                'name' => $f['name'],
                'icon' => uploadUrl($f['icon_url'], 'catalogue/features'),
            ];
        }
    }
 
    $products = array_map(function ($row) use ($variantsByProduct, $imagesByProduct, $featuresByProduct) {
        $primaryImg = uploadUrl($row['image_url'], 'catalogue/products');
        $allImages = $imagesByProduct[$row['id']] ?? [];
        if (empty($allImages) && $primaryImg) {
            $allImages[] = $primaryImg;
        }
        $variants = $variantsByProduct[$row['id']] ?? [];
        $code = $row['code'] ?: null;
        if (!$code && !empty($variants)) {
            $code = $variants[0]['code'] ?: null;
        }
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'code' => $code,
            'price' => (int) $row['price'],
            'dimensions' => $row['dimensions'] ?: null,
            'specifications' => $row['specifications'] ?: null,
            'is_new_arrival' => (bool) $row['is_new_arrival'],
            'series' => $row['series_name'],
            'series_id' => (int) $row['series_id'],
            'sub_category_id' => $row['sub_category_id'] !== null ? (int) $row['sub_category_id'] : null,
            'category_id' => (int) $row['category_id'],
            'category' => $row['category_name'],
            'image' => $primaryImg,
            'images' => $allImages,
            'variant_type' => $row['variant_type'],
            'colour_label' => $row['colour_label'],
            'variants' => $variants,
            'features' => $featuresByProduct[$row['id']] ?? [],
            'video' => uploadUrl($row['video_url'], 'catalogue/products'),
        ];
    }, $rows);

    jsonResponse(true, $products, 'Products loaded');
} catch (Exception $e) {
    logError('webapi/products: ' . $e->getMessage());
    http_response_code(500);
    jsonResponse(false, null, 'Server error');
}
