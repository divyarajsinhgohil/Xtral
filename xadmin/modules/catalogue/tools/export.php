<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$activePage = 'catalogue_tools';
$pageTitle = "Export Products";

// Fetch Main Categories for filtering
$categories = fetchAll("SELECT id, name, has_dual_price FROM catalogue_categories ORDER BY name ASC");

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Tools
            </a>
            <h2><i class="fas fa-file-export me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Export Structure Template -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="custom-card-title mb-0"><i class="fas fa-sitemap me-2"></i>Export Structure (Blank Template)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Download a CSV with your <strong>Category → SubCategory → Series</strong> names pre-filled, and product columns left blank. Use this to fill in product data.</p>
                    <div class="alert alert-light border small mb-3">
                        <strong>Workflow:</strong>
                        <ol class="mb-0 mt-1">
                            <li>Create hierarchy in admin (with images)</li>
                            <li>Export structure here</li>
                            <li>Fill in product data in Excel</li>
                            <li>Import via Bulk Import tool</li>
                        </ol>
                    </div>
                    <form method="POST" action="export_structure_process.php">
                        <div class="mb-3">
                            <label for="structure_category_id" class="form-label">Select Category</label>
                            <select class="form-select" id="structure_category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="structure_product_type" class="form-label">Product Type</label>
                            <select class="form-select" id="structure_product_type" name="product_type" required>
                                <option value="simple" selected>Simple Products</option>
                                <option value="size">Size Variants</option>
                                <option value="color">Color Variants</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="fas fa-download me-2"></i>Download Structure Template
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Export Products (with data) -->
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="custom-card-title mb-0"><i class="fas fa-file-csv me-2"></i>Export Products (with Data)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Download existing products as CSV. Use this to <strong>update prices</strong>, specifications, or other fields — then re-import.</p>
                    <div class="alert alert-light border small mb-3">
                        <strong>Price Update Workflow:</strong>
                        <ol class="mb-0 mt-1">
                            <li>Export products for a category</li>
                            <li>Edit Price / PriceZone2 in Excel</li>
                            <li>Save and re-import via Bulk Import tool</li>
                        </ol>
                    </div>
                    <form method="POST" action="export_process.php">
                        <div class="mb-3">
                            <label for="products_category_id" class="form-label">Select Category</label>
                            <select class="form-select" id="products_category_id" name="category_id" required>
                                <option value="">-- Select Category --</option>
                                <option value="all">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="products_type" class="form-label">Product Type</label>
                            <select class="form-select" id="products_type" name="product_type" required>
                                <option value="simple" selected>Simple Products</option>
                                <option value="size">Size Variants</option>
                                <option value="color">Color Variants</option>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Download Products CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
