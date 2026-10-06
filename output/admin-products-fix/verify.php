<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../../xadmin/includes/catalogue_product_hierarchy.php';
$pdo = new PDO('mysql:host=localhost;dbname=xtral;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); echo "PASS: $message\n"; }
function pageQuery($file, $hierarchy) {
    $source = file_get_contents(__DIR__ . '/../../xadmin/modules/catalogue/products/' . $file);
    if (!preg_match('/\$sql = "(.*?)";/s', $source, $match)) throw new RuntimeException('Query not found');
    return strtr($match[1], [
        '{$productCategorySql}' => $hierarchy['category'],
        '{$productSubCategorySql}' => $hierarchy['sub_category'],
        '{$productHierarchy[\'category\']}' => $hierarchy['category'],
        '{$productHierarchy[\'sub_category\']}' => $hierarchy['sub_category'],
    ]);
}
// Only connection-scoped temporary tables are changed; real catalog data is untouched.
$pdo->exec('CREATE TEMPORARY TABLE catalogue_products AS SELECT * FROM catalogue_products');
$pdo->exec('CREATE TEMPORARY TABLE catalogue_series AS SELECT * FROM catalogue_series');
$pdo->exec('CREATE TEMPORARY TABLE catalogue_categories AS SELECT * FROM catalogue_categories');
$pdo->exec('CREATE TEMPORARY TABLE catalogue_sub_categories AS SELECT * FROM catalogue_sub_categories');
$direct = $pdo->query('SELECT id,category_id FROM catalogue_products WHERE series_id IS NULL LIMIT 1')->fetch();
$legacy = $pdo->query('SELECT p.id,s.category_id,s.sub_category_id FROM catalogue_products p JOIN catalogue_series s ON s.id=p.series_id LIMIT 1')->fetch();
check((bool)$direct && (bool)$legacy, 'Direct and series product fixtures available');
$h = catalogueProductHierarchySql($pdo);
$list = pageQuery('list.php', $h);
$rows = $pdo->query($list)->fetchAll();
check(in_array($direct['id'], array_column($rows,'id')), 'Direct-category product appears in product list');
$q=$pdo->prepare(pageQuery('edit.php',$h));$q->execute([$direct['id']]);$row=$q->fetch();
check($row && $row['category_id']==$direct['category_id'] && $row['series_id']===null, 'Direct-category product opens for editing with no series');
$q->execute([$legacy['id']]);$row=$q->fetch();
check($row && $row['category_id']==$legacy['category_id'], 'Series product still opens for editing');
$q=$pdo->prepare($list.' WHERE '.$h['category'].' = ?');$q->execute([$direct['category_id']]);
check(in_array($direct['id'],array_column($q->fetchAll(),'id')), 'Main-category filter includes direct products');
$pdo->exec('ALTER TABLE catalogue_products DROP COLUMN category_id, DROP COLUMN sub_category_id');
try { $pdo->query($list); throw new RuntimeException('Expected old query to fail'); }
catch(PDOException $e) { check(($e->errorInfo[1]??null)==1054, 'Reproduced unknown-column failure on legacy schema'); }
$h=catalogueProductHierarchySql($pdo);$list=pageQuery('list.php',$h);
check(count($pdo->query($list)->fetchAll())>0, 'Product list loads on legacy schema without category columns');
$q=$pdo->prepare(pageQuery('edit.php',$h));$q->execute([$legacy['id']]);$row=$q->fetch();
check($row && $row['category_id']==$legacy['category_id'], 'Editor resolves legacy product category from series');
$sub=$pdo->query('SELECT p.id,s.sub_category_id FROM catalogue_products p JOIN catalogue_series s ON s.id=p.series_id WHERE s.sub_category_id IS NOT NULL LIMIT 1')->fetch();
if($sub){$q=$pdo->prepare($list.' WHERE '.$h['sub_category'].' = ?');$q->execute([$sub['sub_category_id']]);check(in_array($sub['id'],array_column($q->fetchAll(),'id')), 'Sub-category filter works on legacy schema');}
echo "All checks passed. No persistent database changes.\n";
