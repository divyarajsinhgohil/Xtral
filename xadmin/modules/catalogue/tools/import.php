<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Bulk Import Products";
$activePage = 'catalogue_tools';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="index.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Tools
            </a>
            <h2><i class="fas fa-file-import me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="custom-card-title mb-0">Upload CSV File</h5>
                </div>
                <div class="card-body">
                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Instructions</h6>
                        <ul class="mb-0 small">
                            <li><strong>Before importing:</strong> Create Categories, SubCategories, and Series in admin first (with images).</li>
                            <li>Use <strong>Export → Structure Template</strong> to get a CSV with exact hierarchy names pre-filled.</li>
                            <li>Fill in product data (Name, Code, Price, Images) and import here.</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-light border">
                        <h6><i class="fas fa-columns me-2"></i>CSV Columns</h6>
                        <div id="columns_simple">
                            <table class="table table-sm table-borderless mb-0 small">
                                <tr><td><code>Category</code></td><td>✅ Required — must match existing category name</td></tr>
                                <tr><td><code>SubCategory</code></td><td>Optional — use <code>None</code> or leave blank for 3-Tier</td></tr>
                                <tr><td><code>Series</code></td><td>✅ Required — must match existing series name</td></tr>
                                <tr><td><code>ProductName</code></td><td>✅ Required</td></tr>
                                <tr><td><code>ProductCode</code></td><td>✅ Required — can be changed on re-import when matched by <code>ProductID</code></td></tr>
                                <tr><td><code>HSNCode</code></td><td>Optional — GST HSN classification code</td></tr>
                                <tr><td><code>ProductType</code></td><td>✅ Required — <code>simple</code></td></tr>
                                <tr><td><code>Price</code></td><td>✅ Required — integer (Zone 1 price)</td></tr>
                                <tr><td><code>PriceZone2</code></td><td>Optional — for Dual Zone Pricing categories</td></tr>
                                <tr><td><code>DisplayOrder</code></td><td>Optional — integer sort order (default: 0)</td></tr>
                                <tr><td><code>Dimensions</code></td><td>Optional</td></tr>
                                <tr><td><code>Specifications</code></td><td>Optional — plain text or HTML</td></tr>
                                <tr><td><code>Image1</code> to <code>Image5</code></td><td>Image1 required — filename, path, or URL</td></tr>
                                <tr><td><code>ProductID</code></td><td>Read-only — auto-included in exports for faster re-import matching</td></tr>
                            </table>
                        </div>
                        <div id="columns_size" style="display:none;">
                            <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>One row per variant. Parent fields are repeated on every row.</p>
                            <p class="small fw-bold mb-1">Parent Fields (repeated)</p>
                            <table class="table table-sm table-borderless mb-2 small">
                                <tr><td><code>Category</code></td><td>✅ Required</td></tr>
                                <tr><td><code>SubCategory</code></td><td>Optional</td></tr>
                                <tr><td><code>Series</code></td><td>✅ Required</td></tr>
                                <tr><td><code>ProductName</code></td><td>✅ Required — group title</td></tr>
                                <tr><td><code>HSNCode</code></td><td>Optional — GST HSN classification code (from first row of each group)</td></tr>
                                <tr><td><code>ProductType</code></td><td>✅ Always <code>size</code></td></tr>
                                <tr><td><code>DisplayOrder</code></td><td>Optional — parent product sort order</td></tr>
                                <tr><td><code>Dimensions</code></td><td>Optional — parent dimensions/size</td></tr>
                                <tr><td><code>Specifications</code></td><td>Optional — from first row of each group</td></tr>
                                <tr><td><code>Image1</code> to <code>Image5</code></td><td>Optional — parent gallery images</td></tr>
                                <tr><td><code>ProductID</code></td><td>Read-only — from exports</td></tr>
                            </table>
                            <p class="small fw-bold mb-1">Variant Fields (per row)</p>
                            <table class="table table-sm table-borderless mb-0 small">
                                <tr><td><code>VariantID</code></td><td>Read-only — from exports. When present, lets you change <code>Size</code> on re-import</td></tr>
                                <tr><td><code>VariantName</code></td><td>Optional — variant display name</td></tr>
                                <tr><td><code>VariantCode</code></td><td>✅ Required — unique code per variant</td></tr>
                                <tr><td><code>Size</code></td><td>✅ Required — e.g. <code>25MM</code></td></tr>
                                <tr><td><code>Price</code></td><td>✅ Required — variant price</td></tr>
                                <tr><td><code>PriceZone2</code></td><td>Optional — variant Zone 2 price</td></tr>
                                <tr><td><code>VariantImage</code></td><td>Optional — variant-specific image</td></tr>
                            </table>
                        </div>
                        <div id="columns_color" style="display:none;">
                            <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i>One row per variant. Parent fields are repeated on every row.</p>
                            <div class="alert alert-warning small py-2 mb-2">
                                <i class="fas fa-palette me-1"></i><strong>Color Library Required:</strong> Colors must exist in the <strong>Color Library</strong> before import. <code>ColorName</code> must exactly match a Color Library entry.
                            </div>
                            <p class="small fw-bold mb-1">Parent Fields (repeated)</p>
                            <table class="table table-sm table-borderless mb-2 small">
                                <tr><td><code>Category</code></td><td>✅ Required</td></tr>
                                <tr><td><code>SubCategory</code></td><td>Optional</td></tr>
                                <tr><td><code>Series</code></td><td>✅ Required</td></tr>
                                <tr><td><code>ProductName</code></td><td>✅ Required — group title</td></tr>
                                <tr><td><code>HSNCode</code></td><td>Optional — GST HSN classification code (from first row of each group)</td></tr>
                                <tr><td><code>ProductType</code></td><td>✅ Always <code>color</code></td></tr>
                                <tr><td><code>ColourLabel</code></td><td>Optional — <code>Colour</code> or <code>Finish</code> (default: <code>Colour</code>) — sets the label shown in the mobile app</td></tr>
                                <tr><td><code>DisplayOrder</code></td><td>Optional — parent product sort order</td></tr>
                                <tr><td><code>Dimensions</code></td><td>Optional — parent dimensions/size</td></tr>
                                <tr><td><code>Specifications</code></td><td>Optional — from first row of each group</td></tr>
                                <tr><td><code>Image1</code> to <code>Image5</code></td><td>Optional — parent gallery images</td></tr>
                                <tr><td><code>ProductID</code></td><td>Read-only — from exports</td></tr>
                            </table>
                            <p class="small fw-bold mb-1">Variant Fields (per row)</p>
                            <table class="table table-sm table-borderless mb-0 small">
                                <tr><td><code>VariantID</code></td><td>Read-only — from exports. When present, lets you change <code>ColorDisplayName</code> on re-import</td></tr>
                                <tr><td><code>VariantName</code></td><td>Optional — variant display name</td></tr>
                                <tr><td><code>VariantCode</code></td><td>✅ Required — unique code per variant</td></tr>
                                <tr><td><code>ColorName</code></td><td>✅ Required — must match Color Library entry (case-insensitive)</td></tr>
                                <tr><td><code>ColorDisplayName</code></td><td>Optional — override display name (defaults to ColorName if blank)</td></tr>
                                <tr><td><code>Price</code></td><td>✅ Required — variant price</td></tr>
                                <tr><td><code>PriceZone2</code></td><td>Optional — variant Zone 2 price</td></tr>
                                <tr><td><code>VariantImage</code></td><td>Optional — variant-specific image</td></tr>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Match Logic:</strong><br>
                        <strong>Simple:</strong> Priority 1 — match by <code>ProductID</code> (from export) → <strong>UPDATE</strong> (you can change <code>ProductCode</code> here). Priority 2 — match by <code>ProductCode</code> in same Series → UPDATE. Otherwise → NEW.<br>
                        <strong>Size / Color Variants:</strong> Parent matched by <code>ProductID</code> first, then by <code>ProductName + Series</code> → UPDATE or NEW. Size variants matched by <code>Size</code>, Color variants matched by their display name within the same product → UPDATE or NEW.
                    </div>

                    <?php if (isset($_SESSION['import_report'])): ?>
                        <div class="alert alert-success">
                            <?= $_SESSION['import_report'] ?>
                        </div>
                        <?php unset($_SESSION['import_report']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['import_errors']) && !empty($_SESSION['import_errors'])): ?>
                        <div class="alert alert-danger">
                            <h6>Errors occurred:</h6>
                            <ul class="mb-0 small" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($_SESSION['import_errors'] as $err): ?>
                                    <li><?= htmlspecialchars($err) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php unset($_SESSION['import_errors']); ?>
                    <?php endif; ?>

                    <form method="POST" action="import_process.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="product_type" class="form-label">Product Type</label>
                            <select class="form-select" id="product_type" name="product_type" required>
                                <option value="simple" selected>Simple Products</option>
                                <option value="size">Size Variants</option>
                                <option value="color">Color Variants</option>
                            </select>
                            <small class="text-muted">Each product type uses a different CSV format</small>
                        </div>
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        </div>
                        <div class="mb-3">
                            <label for="image_zip" class="form-label">Image ZIP File <span class="badge bg-secondary">Optional</span></label>
                            <input type="file" class="form-control" id="image_zip" name="image_zip" accept=".zip">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Upload a ZIP file containing product images if your CSV has local file paths (e.g., <code>C:\Users\...\X-Tral Upload\PTMT\...</code>). 
                                ZIP the category folder (e.g., <code>PTMT</code>) or the entire <code>X-Tral Upload</code> folder — the system will automatically match images by folder structure. 
                                <strong>Not needed</strong> if images are already on this server.
                            </small>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="simulate" name="simulate" value="1">
                            <label class="form-check-label" for="simulate">
                                <i class="fas fa-flask me-1"></i>Test Run (Simulate only — validates data without saving)
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload me-2"></i>Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('product_type').addEventListener('change', function() {
    const val = this.value;
    document.getElementById('columns_simple').style.display = val === 'simple' ? '' : 'none';
    document.getElementById('columns_size').style.display = val === 'size' ? '' : 'none';
    document.getElementById('columns_color').style.display = val === 'color' ? '' : 'none';
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
