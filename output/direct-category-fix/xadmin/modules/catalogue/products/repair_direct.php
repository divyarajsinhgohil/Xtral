<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$pdo = getDBConnection();
if (empty($_SESSION['direct_product_repair_token'])) $_SESSION['direct_product_repair_token'] = bin2hex(random_bytes(32));
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['direct_product_repair_token'], (string)($_POST['csrf_token'] ?? ''))) throw new RuntimeException('Please reload this page and try again.');
        ensureCatalogueProductHierarchySchema($pdo);
        if (($_POST['action'] ?? '') === 'move') {
            $ids = array_values(array_unique(array_filter(array_map('intval', (array)($_POST['series_ids'] ?? [])))));
            if (!$ids) throw new RuntimeException('Select the placeholder series to move.');
            $pdo->beginTransaction();
            $count = 0;
            foreach ($ids as $id) {
                $count += moveCataloguePlaceholderToCategory($pdo, $id, function ($backup) {
                    $backup['created_at'] = date(DATE_ATOM);
                    $backup['admin_id'] = getAdminId();
                    $path = LOG_PATH . 'direct-category-backup-' . date('Ymd-His') . '-' . bin2hex(random_bytes(6)) . '.json';
                    if (file_put_contents($path, json_encode($backup, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR), LOCK_EX) === false) throw new RuntimeException('Could not save the repair backup. No products were moved.');
                });
            }
            $pdo->commit();
            $success = "$count products moved directly into their main categories. Selected empty placeholder series removed. Backup saved in the protected admin logs folder.";
        } else {
            $success = 'The database supports products directly in a category. You can now create and save products with Series left blank.';
        }
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}
$candidates = fetchAll("SELECT s.id, s.name, c.name AS category_name, COUNT(p.id) AS product_count FROM catalogue_series s JOIN catalogue_categories c ON c.id = s.category_id LEFT JOIN catalogue_products p ON p.series_id = s.id WHERE s.sub_category_id IS NULL AND LOWER(TRIM(s.name)) = LOWER(TRIM(c.name)) GROUP BY s.id, s.name, c.name ORDER BY c.name, s.id");
$pageTitle = 'Repair Direct Category Products';
$activePage = 'catalogue_products';
include dirname(__DIR__, 3) . '/includes/header.php';
?>
<div class="container-fluid py-4">
    <a class="btn btn-outline-secondary mb-3" href="list.php">Back to Products</a>
    <h2>Repair Direct Category Products</h2>
    <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success" role="alert"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <p>Products saved without a Series belong directly to their selected category.</p>
    <form method="post" class="mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['direct_product_repair_token']) ?>">
        <button type="submit" name="action" value="upgrade" class="btn btn-primary">Prepare database for direct products</button>
    </form>
    <h4>Existing category-named series</h4>
    <p>Select only automatically created placeholder series. Their products will keep their IDs, codes, prices, images and QR links. The selected series will be deleted after all its products have been moved; a recovery backup is saved first. Real series should remain unselected.</p>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['direct_product_repair_token']) ?>">
        <table class="table table-bordered"><thead><tr><th>Select</th><th>Main category</th><th>Existing series</th><th>Products</th></tr></thead><tbody>
        <?php foreach ($candidates as $candidate): ?>
            <tr><td><input type="checkbox" name="series_ids[]" value="<?= (int)$candidate['id'] ?>" aria-label="Move <?= htmlspecialchars($candidate['name']) ?> series <?= (int)$candidate['id'] ?>"></td><td><?= htmlspecialchars($candidate['category_name']) ?></td><td><?= htmlspecialchars($candidate['name']) ?> (ID <?= (int)$candidate['id'] ?>)</td><td><?= (int)$candidate['product_count'] ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$candidates): ?><tr><td colspan="4">No category-named placeholder series remain.</td></tr><?php endif; ?>
        </tbody></table>
        <button type="submit" name="action" value="move" class="btn btn-warning" <?= !$candidates ? 'disabled' : '' ?>>Move selected products and delete their empty placeholder series</button>
    </form>
</div>
<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
