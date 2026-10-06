<?php
/**
 * Create New QR Code
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$activePage = 'catalogue_qr';
$pageTitle = 'Create New Permanent QR Code';

// Suggest next sequential code
$maxRow = fetchOne("SELECT MAX(CAST(SUBSTRING(code, 4) AS UNSIGNED)) as max_num 
                    FROM catalogue_qr_codes 
                    WHERE code LIKE 'XT-%'");
$suggestedNum = max(1001, ((int)($maxRow['max_num'] ?? 0)) + 1);
$suggestedCode = 'XT-' . $suggestedNum;

$allProducts = fetchAll("SELECT p.id, p.name, p.code, s.name as series_name, c.name as category_name 
                        FROM catalogue_products p 
                        JOIN catalogue_series s ON p.series_id = s.id 
                        JOIN catalogue_categories c ON s.category_id = c.id 
                        WHERE p.is_active = 1 
                        ORDER BY c.name ASC, s.name ASC, p.name ASC");

$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';

$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">

    <!-- Header -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to QR Codes
            </a>
            <h2><i class="fas fa-plus-circle text-primary me-2"></i>Create New Permanent QR Code</h2>
            <p class="text-muted small">
                Generates a permanent parent link for your physical catalogue. You can link it to a product now and re-point it anytime later.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-bold py-3">
                    <i class="fas fa-qrcode me-2"></i>New QR Details
                </div>
                <div class="card-body p-4">
                    <form action="save_process.php" method="POST">
                        <input type="hidden" name="action" value="create">

                        <!-- Permanent Code -->
                        <div class="mb-3">
                            <label for="code" class="form-label fw-bold">Permanent QR Code Identifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace fw-bold" id="code" name="code" 
                                   value="<?= htmlspecialchars($suggestedCode) ?>" required>
                            <div class="form-text text-muted">
                                This identifier is permanently encoded in the QR code image printed on paper. Example: <code><?= htmlspecialchars($suggestedCode) ?></code>
                            </div>
                        </div>

                        <!-- Target Product (Child link) -->
                        <div class="mb-3">
                            <label for="product_id" class="form-label fw-bold">Target Product Destination (Child Link)</label>
                            <select name="product_id" id="product_id" class="form-select select2-init" style="width: 100%;">
                                <option value="">-- No Product Linked (Or select below) --</option>
                                <?php foreach ($allProducts as $p): ?>
                                    <option value="<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['name']) ?> 
                                        <?= (!empty($p['code']) && $p['code'] !== '—' ? '[' . htmlspecialchars($p['code']) . ']' : '') ?>
                                        (<?= htmlspecialchars($p['category_name']) ?> - <?= htmlspecialchars($p['series_name']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">
                                When a user scans the QR code, they are redirected to this product. You can change this selection anytime later in admin!
                            </div>
                        </div>

                        <!-- Custom URL Override (Optional) -->
                        <div class="mb-3">
                            <label for="target_url" class="form-label fw-bold">Custom URL Override <small class="text-muted">(Optional)</small></label>
                            <input type="url" class="form-control" id="target_url" name="target_url" 
                                   placeholder="e.g. https://x-tral.com/catalogue.php">
                            <div class="form-text text-muted">
                                Leave blank to use the product destination selected above.
                            </div>
                        </div>

                        <!-- Label / Catalogue Section -->
                        <div class="mb-3">
                            <label for="label" class="form-label fw-bold">Label / Catalogue Note</label>
                            <input type="text" class="form-control" id="label" name="label" 
                                   placeholder="e.g. Page 14 - Table Basin section">
                        </div>

                        <!-- Status Active -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                <label class="form-check-label fw-bold" for="is_active">Active</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-check me-1"></i> Create QR Code
                            </button>
                            <a href="list.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    $('.select2-init').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Choose Product --'
    });
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
