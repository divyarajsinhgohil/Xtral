<?php
/**
 * Products for the front side
 * GET /xadmin/api/webapi/products.php
 * Optional filters:
 *   ?category_id=1     only products of one category
 *   ?new_arrivals=1    only new-arrival products
 */
require_once __DIR__ . '/config.php';
require_once dirname(__DIR__, 2) . '/includes/catalogue_product_hierarchy.php';

requireMethod('GET');

try {
    $productHierarchy = catalogueProductHierarchySql(getDBConnection());
    $productCategorySql = $productHierarchy['category'];
    $productSubCategorySql = $productHierarchy['sub_category'];
    $where = "p.is_active = 1 AND (s.is_active = 1 OR s.id IS NULL) AND c.is_active = 1";
    $params = [];

    if (!empty($_GET['category_id']) && ctype_digit($_GET['category_id'])) {
        $where .= " AND c.id = ?";
        $params[] = (int) $_GET['category_id'];
    }
    if (!empty($_GET['new_arrivals'])) {
        $where .= " AND p.is_new_arrival = 1";
    }
    if (!empty($_GET['search']) || !empty($_GET['q'])) {
        $searchTerm = trim($_GET['search'] ?? $_GET['q']);
        $cleanSearch = preg_replace('/[^a-zA-Z0-9]/', '', $searchTerm);
        $cleanLike = '%' . ($cleanSearch !== '' ? $cleanSearch : $searchTerm) . '%';
        $like = "%{$searchTerm}%";
        $where .= " AND (p.name LIKE ? OR p.code LIKE ? OR REPLACE(p.code, '-', '') LIKE ? OR EXISTS (
            SELECT 1 FROM catalogue_product_variants v 
            WHERE v.product_id = p.id AND (v.code LIKE ? OR REPLACE(v.code, '-', '') LIKE ? OR v.attribute_value LIKE ?)
        ))";
        $params[] = $like;
        $params[] = $like;
        $params[] = $cleanLike;
        $params[] = $like;
        $params[] = $cleanLike;
        $params[] = $like;
    }

    $rows = fetchAll("
        SELECT
            p.id, p.name, p.code, p.price, p.price_zone2, p.price_label_1, p.price_label_2, p.dimensions,
            p.specifications, p.is_new_arrival,
            p.variant_type, p.colour_label, p.video_url, p.display_order,
            p.series_id AS series_id, s.name AS series_name, {$productSubCategorySql} AS sub_category_id,
            c.id AS category_id, c.name AS category_name
        FROM catalogue_products p
        LEFT JOIN catalogue_series s ON p.series_id = s.id
        JOIN catalogue_categories c ON {$productCategorySql} = c.id
        WHERE $where
        ORDER BY p.id ASC
    ", $params);

    // Load variants of all returned products in one query
    $variantsByProduct = [];
    $imagesByProduct = [];
    $productIds = array_column($rows, 'id');
    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        // 1. Fetch variants
        $variantRows = fetchAll("
            SELECT v.*, col.hex_code, col.texture_image
            FROM catalogue_product_variants v
            LEFT JOIN catalogue_colors col ON v.color_id = col.id
            WHERE v.is_active = 1 AND v.product_id IN ($placeholders)
            ORDER BY v.product_id, v.display_order, v.id
        ", $productIds);
 
        foreach ($variantRows as $v) {
            $rawSizes = !empty($v['size_dimensions']) ? json_decode($v['size_dimensions'], true) : [];
            $sizes = [];
            if (is_array($rawSizes)) {
                foreach ($rawSizes as $rs) {
                    if (!empty($rs['dimension'])) {
                        $sizes[] = [
                            'dimension'   => $rs['dimension'],
                            'price'       => (isset($rs['price']) && $rs['price'] !== '' && $rs['price'] !== null) ? (int)$rs['price'] : null,
                            'price_zone2' => (isset($rs['price_zone2']) && $rs['price_zone2'] !== '' && $rs['price_zone2'] !== null) ? (int)$rs['price_zone2'] : null,
                        ];
                    }
                }
            }
            $variantsByProduct[$v['product_id']][] = [
                'name'          => $v['attribute_value'],
                'code'          => $v['code'],
                'price'         => (int) $v['price'],
                'price_zone2'   => !empty($v['price_zone2']) ? (int) $v['price_zone2'] : null,
                'color_hex'     => $v['hex_code'] ?: null,
                'texture_image' => uploadUrl($v['texture_image'], 'catalogue/colors'),
                'image'         => uploadUrl($v['image_url'], 'catalogue/products'),
                'sizes'         => $sizes,
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
        $allImages = $imagesByProduct[$row['id']] ?? [];
        $primaryImg = !empty($allImages) ? $allImages[0] : null;
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
            'price_zone2' => !empty($row['price_zone2']) ? (int) $row['price_zone2'] : null,
            'price_label_1' => !empty($row['price_label_1']) ? $row['price_label_1'] : (getSetting('prod_price_label_1_' . $row['id']) ?: null),
            'price_label_2' => !empty($row['price_label_2']) ? $row['price_label_2'] : (getSetting('prod_price_label_2_' . $row['id']) ?: null),
            'dimensions' => $row['dimensions'] ?: null,
            'specifications' => $row['specifications'] ?: null,
            'is_new_arrival' => (bool) $row['is_new_arrival'],
            'series' => $row['series_name'],
            'series_id' => $row['series_id'] === null ? null : (int) $row['series_id'],
            'sub_category_id' => $row['sub_category_id'] !== null ? (int) $row['sub_category_id'] : null,
            'category_id' => (int) $row['category_id'],
            'category' => $row['category_name'],
            'display_order' => (int) $row['display_order'],
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
