<?php
/** SQL ownership expressions for old and new catalogue databases. */
function catalogueProductHierarchySql(PDO $pdo)
{
    $columns = $pdo->query('SHOW COLUMNS FROM catalogue_products')->fetchAll(PDO::FETCH_COLUMN);
    // A selected series owns the hierarchy. Direct products use their own fields.
    return [
        'category' => in_array('category_id', $columns, true)
            ? 'COALESCE(s.category_id, p.category_id)' : 's.category_id',
        'sub_category' => in_array('sub_category_id', $columns, true)
            ? 'CASE WHEN s.id IS NOT NULL THEN s.sub_category_id ELSE p.sub_category_id END' : 's.sub_category_id',
    ];
}

/** Run before a write transaction: MySQL ALTER TABLE implicitly commits. */
function ensureCatalogueProductHierarchySchema(PDO $pdo)
{
    $inspect = function () use ($pdo) {
        return array_column($pdo->query('SHOW COLUMNS FROM catalogue_products')->fetchAll(PDO::FETCH_ASSOC), null, 'Field');
    };

    $columns = $inspect();
    if (isset($columns['category_id'], $columns['sub_category_id']) && $columns['series_id']['Null'] === 'YES') return;
    if ($pdo->inTransaction()) throw new RuntimeException('Product hierarchy upgrade must run before saving begins.');
    if ((int)$pdo->query("SELECT GET_LOCK('xtral_product_hierarchy_v1', 15)")->fetchColumn() !== 1) {
        throw new RuntimeException('The catalogue is being upgraded. Please try saving again shortly.');
    }
    try {
        $columns = $inspect();
        if (!isset($columns['category_id'])) {
            $pdo->exec('ALTER TABLE catalogue_products ADD COLUMN category_id INT NULL, ADD INDEX idx_product_category (category_id)');
        }
        if (!isset($columns['sub_category_id'])) {
            $pdo->exec('ALTER TABLE catalogue_products ADD COLUMN sub_category_id INT NULL, ADD INDEX idx_product_sub_category (sub_category_id)');
        }
        if ($columns['series_id']['Null'] !== 'YES') {
            $type = $columns['series_id']['Type'];
            if (!preg_match('/^(?:tinyint|smallint|mediumint|int|bigint)(?:\(\d+\))?(?: unsigned)?$/i', $type)) {
                throw new RuntimeException('Unsupported series ID column type.');
            }
            $pdo->exec("ALTER TABLE catalogue_products MODIFY series_id $type NULL DEFAULT NULL");
        }
    } finally {
        $pdo->query("SELECT RELEASE_LOCK('xtral_product_hierarchy_v1')");
    }
}

/** Blank Series means NULL. Never create or infer a series from its name. */
function resolveCatalogueProductClassification(PDO $pdo, $categoryId, $subCategoryId, $seriesId)
{
    $categoryId = (int)$categoryId;
    $subCategoryId = (int)$subCategoryId ?: null;
    $seriesId = (int)$seriesId ?: null;
    if ($seriesId !== null) {
        $q = $pdo->prepare('SELECT category_id, sub_category_id FROM catalogue_series WHERE id = ?');
        $q->execute([$seriesId]);
        $series = $q->fetch(PDO::FETCH_ASSOC);
        if (!$series) throw new InvalidArgumentException('Selected series does not exist.');
        $categoryId = (int)$series['category_id'];
        $subCategoryId = $series['sub_category_id'] === null ? null : (int)$series['sub_category_id'];
    }
    $q = $pdo->prepare('SELECT id FROM catalogue_categories WHERE id = ?');
    $q->execute([$categoryId]);
    if (!$q->fetchColumn()) throw new InvalidArgumentException('Please select a valid Main Category.');
    if ($subCategoryId !== null) {
        $q = $pdo->prepare('SELECT id FROM catalogue_sub_categories WHERE id = ? AND category_id = ?');
        $q->execute([$subCategoryId, $categoryId]);
        if (!$q->fetchColumn()) throw new InvalidArgumentException('Sub Category does not belong to the selected Main Category.');
    }
    return [$categoryId, $subCategoryId, $seriesId];
}

/** Convert only an explicitly selected, category-named placeholder. */
function moveCataloguePlaceholderToCategory(PDO $pdo, $seriesId, callable $saveBackup)
{
    if (!$pdo->inTransaction()) throw new RuntimeException('Repair requires a transaction.');
    $q = $pdo->prepare('SELECT * FROM catalogue_series WHERE id = ? FOR UPDATE');
    $q->execute([(int)$seriesId]);
    $series = $q->fetch(PDO::FETCH_ASSOC);
    if (!$series) throw new RuntimeException('The selected series no longer exists. Refresh the repair page.');
    $q = $pdo->prepare('SELECT name FROM catalogue_categories WHERE id = ?');
    $q->execute([$series['category_id']]);
    $categoryName = $q->fetchColumn();
    if ($series['sub_category_id'] !== null || strcasecmp(trim($series['name']), trim((string)$categoryName)) !== 0) {
        throw new RuntimeException('Only an explicitly selected category-named placeholder can be repaired here.');
    }
    $q = $pdo->prepare('SELECT id, category_id, sub_category_id, series_id FROM catalogue_products WHERE series_id = ? FOR UPDATE');
    $q->execute([$series['id']]);
    $products = $q->fetchAll(PDO::FETCH_ASSOC);
    $q = $pdo->prepare("SELECT code FROM catalogue_products WHERE (series_id = ? OR (series_id IS NULL AND category_id = ?)) AND code IS NOT NULL AND code <> '' GROUP BY code HAVING COUNT(*) > 1 LIMIT 1");
    $q->execute([$series['id'], $series['category_id']]);
    if ($q->fetchColumn() !== false) throw new RuntimeException('Duplicate product codes would result. Resolve them before moving this series.');
    // Refuse deletion if another feature links to this series; avoid cascade loss.
    $references = $pdo->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = 'catalogue_series' AND REFERENCED_COLUMN_NAME = 'id'")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($references as $reference) {
        if ($reference['TABLE_NAME'] === 'catalogue_products' && $reference['COLUMN_NAME'] === 'series_id') continue;
        $table = str_replace('`', '``', $reference['TABLE_NAME']);
        $column = str_replace('`', '``', $reference['COLUMN_NAME']);
        $q = $pdo->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = ?");
        $q->execute([$series['id']]);
        if ((int)$q->fetchColumn() > 0) throw new RuntimeException('Another catalogue feature references this series. Its links must be moved first.');
    }
    $saveBackup(['series' => $series, 'products' => $products]);
    $q = $pdo->prepare('UPDATE catalogue_products SET category_id = ?, sub_category_id = NULL, series_id = NULL WHERE series_id = ?');
    $q->execute([$series['category_id'], $series['id']]);
    if ($q->rowCount() !== count($products)) throw new RuntimeException('Product count changed during repair. Nothing was committed.');
    $q = $pdo->prepare('SELECT COUNT(*) FROM catalogue_products WHERE series_id = ?');
    $q->execute([$series['id']]);
    if ((int)$q->fetchColumn() !== 0) throw new RuntimeException('Series still has products; removal cancelled.');
    $q = $pdo->prepare('DELETE FROM catalogue_series WHERE id = ?');
    $q->execute([$series['id']]);
    return count($products);
}
