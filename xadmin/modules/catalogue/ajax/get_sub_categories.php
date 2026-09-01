<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

if ($category_id > 0) {
    $sub_categories = fetchAll(
        "SELECT id, name FROM catalogue_sub_categories WHERE category_id = ? AND is_active = 1 ORDER BY display_order ASC, name ASC", 
        [$category_id]
    );
    jsonResponse(true, $sub_categories);
} else {
    jsonResponse(false, [], 'Invalid Category ID');
}
?>
