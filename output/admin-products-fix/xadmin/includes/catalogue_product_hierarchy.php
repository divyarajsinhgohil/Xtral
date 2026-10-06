<?php
/**
 * Read product ownership on both the legacy series-only schema and the
 * newer schema that allows products directly inside a category.
 * The returned expressions use the product/series aliases p and s.
 */
function catalogueProductHierarchySql(PDO $pdo)
{
    $columns = $pdo->query('SHOW COLUMNS FROM catalogue_products')->fetchAll(PDO::FETCH_COLUMN);

    return [
        'category' => in_array('category_id', $columns, true)
            ? 'COALESCE(p.category_id, s.category_id)'
            : 's.category_id',
        'sub_category' => in_array('sub_category_id', $columns, true)
            ? 'COALESCE(p.sub_category_id, s.sub_category_id)'
            : 's.sub_category_id',
    ];
}
