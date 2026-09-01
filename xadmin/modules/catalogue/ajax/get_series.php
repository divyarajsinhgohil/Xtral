<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
// sub_category_id can be 'null' string if passed from JS, or empty.
// We need to handle 0 or null explicitly.
$sub_category_id = isset($_GET['sub_category_id']) && $_GET['sub_category_id'] !== '' && $_GET['sub_category_id'] !== 'null' ? (int)$_GET['sub_category_id'] : null;

if ($category_id > 0) {
    if ($sub_category_id) {
        // 4-Tier: Fetch series linked to this sub_category
        $sql = "SELECT id, name FROM catalogue_series WHERE sub_category_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC";
        $params = [$sub_category_id];
    } else {
        // 3-Tier: Fetch series linked directly to category (sub_category_id IS NULL)
        $sql = "SELECT id, name FROM catalogue_series WHERE category_id = ? AND sub_category_id IS NULL AND is_active = 1 ORDER BY display_order ASC, name ASC";
        $params = [$category_id];
    }
    
    $series = fetchAll($sql, $params);
    jsonResponse(true, $series);
} else {
    jsonResponse(false, [], 'Invalid Category ID');
}
?>
