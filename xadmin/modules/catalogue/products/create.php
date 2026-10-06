<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Fetch Active Main Categories
$main_categories = fetchAll("SELECT id, name, has_dual_price FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");
// Fetch Active Colors for Library
$colors = fetchAll("SELECT id, name, type, hex_code, texture_image FROM catalogue_colors WHERE is_active = 1 ORDER BY name ASC");
// Fetch Active Features
$features = fetchAll("SELECT id, name, icon_url FROM catalogue_features WHERE is_active = 1 ORDER BY display_order ASC, name ASC");

$globalPriceLabel1 = getPriceLabel1();
$globalPriceLabel2 = getPriceLabel2();
$priceLabel1 = $globalPriceLabel1;
$priceLabel2 = $globalPriceLabel2;

$pageTitle = "Add New Product";
$activePage = 'catalogue_products';
$additionalCSS = '<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.upload-dropzone {
    border: 2px dashed #0d6efd;
    background-color: #f8fbff;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    border-radius: 10px;
}
.upload-dropzone:hover, .upload-dropzone.dragover {
    background-color: #eaf2ff;
    border-color: #0a58ca;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.12);
}
.drag-handle {
    cursor: grab;
    user-select: none;
}
.drag-handle:active {
    cursor: grabbing;
}
.sortable-ghost {
    opacity: 0.35;
    background-color: #e2e6ea !important;
}
</style>';
$additionalJS = '<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>';

// Default values
$item = [
    'category_id' => '',
    'sub_category_id' => '',
    'series_id' => '',
    'name' => '',
    'code' => '',
    'price' => '',
    'price_zone2' => '',
    'dimensions' => '',
    'specifications' => '',
    'display_order' => 0,
    'is_active' => 1
];

// Get max display order
$maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_products");
$item['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
            <h2><i class="fas fa-plus-circle me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Product Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="create_process.php" enctype="multipart/form-data">

                        <!-- Hierarchy Selection -->
                        <div class="row mb-4 p-3 bg-light rounded border">
                            <h6 class="text-muted mb-3"><i class="fas fa-sitemap me-2"></i>Classification</h6>

                            <!-- Main Category -->
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">
                                    Main Category <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($main_categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" data-dual-price="<?= $cat['has_dual_price'] ?>">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sub Category -->
                            <div class="col-md-4 mb-3">
                                <label for="sub_category_id" class="form-label">
                                    Sub Category
                                </label>
                                <select class="form-select" id="sub_category_id" name="sub_category_id" disabled>
                                    <option value="">-- Select Main Category First --</option>
                                </select>
                            </div>

                            <!-- Series -->
                            <div class="col-md-4 mb-3">
                                <label for="series_id" class="form-label">
                                    Series <span class="text-muted small fw-normal">(Optional — Direct if none)</span>
                                </label>
                                <select class="form-select" id="series_id" name="series_id" disabled>
                                    <option value="">-- Select Main Category First --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Variant Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Product Type</label>
                                <select class="form-select" id="variant_type" name="variant_type">
                                    <option value="none" selected>Simple Product</option>
                                    <option value="size">Size Variants</option>
                                    <option value="color">Color Variants</option>
                                </select>
                                <small class="text-muted">Select the type of product you want to create.</small>
                            </div>
                        </div>

                        <!-- Basic Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Product Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required
                                    placeholder="e.g., Single Lever Basin Mixer">
                                <small class="text-muted">For variants, this acts as the Group Title.</small>
                            </div>

                            <!-- Simple Product Fields (Hidden for Variants) -->
                            <div class="col-md-3 mb-3 simple-field">
                                <label for="code" class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" placeholder="e.g., CR-101">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="hsn_code" class="form-label">HSN Code</label>
                                <input type="text" class="form-control" id="hsn_code" name="hsn_code" maxlength="15" placeholder="e.g., 6912">
                                <small class="text-muted">Tax HSN code (applies to all variants too).</small>
                            </div>
                        </div>

                        <!-- Price Titles (Custom per product) -->
                        <div class="row mb-3 p-3 bg-light rounded border">
                            <div class="col-12 mb-2">
                                <label class="form-label fw-bold text-dark mb-0">
                                    <i class="fas fa-tags me-1 text-primary"></i>Custom Price Titles (For this product)
                                    <span class="badge bg-secondary ms-2 fw-normal">Per-Product</span>
                                </label>
                                <small class="text-muted d-block">Set different price titles specifically for this product (e.g., "Zone 1" & "Zone 2" or "MRP" & "Offer Price"). Leave blank to use the default titles.</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="price_label_1" class="form-label small fw-semibold">Price 1 Title / Name</label>
                                <input type="text" class="form-control price-label-input" id="price_label_1" name="price_label_1" value="" placeholder="<?= htmlspecialchars($globalPriceLabel1) ?> (Default)">
                                <small class="text-muted">Default: "<?= htmlspecialchars($globalPriceLabel1) ?>"</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="price_label_2" class="form-label small fw-semibold">Price 2 Title / Name</label>
                                <input type="text" class="form-control price-label-input" id="price_label_2" name="price_label_2" value="" placeholder="<?= htmlspecialchars($globalPriceLabel2) ?> (Default)">
                                <small class="text-muted">Default: "<?= htmlspecialchars($globalPriceLabel2) ?>"</small>
                            </div>
                        </div>

                        <!-- Simple Product Prices -->
                        <div class="row simple-field">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    <span class="price-title-1 fw-bold text-dark"><?= htmlspecialchars($priceLabel1) ?></span> (₹) <span class="text-danger">*</span>
                                    <i class="fas fa-pencil-alt text-primary ms-1" style="font-size: 0.75rem; cursor: pointer;" onclick="openLabelEdit(1)" title="Click to rename"></i>
                                </label>
                                <input type="text" inputmode="numeric" class="form-control" id="price" name="price" placeholder="e.g., 1500 (<?= htmlspecialchars($priceLabel1) ?>)">
                            </div>
                            <div class="col-md-6 mb-3 dual-price-field">
                                <label for="price_zone2" class="form-label">
                                    <span class="price-title-2 fw-bold text-dark"><?= htmlspecialchars($priceLabel2) ?></span> (₹) <span class="text-muted small fw-normal">(Optional)</span>
                                    <i class="fas fa-pencil-alt text-primary ms-1" style="font-size: 0.75rem; cursor: pointer;" onclick="openLabelEdit(2)" title="Click to rename"></i>
                                </label>
                                <input type="text" inputmode="numeric" class="form-control" id="price_zone2" name="price_zone2" placeholder="e.g., 1500 (<?= htmlspecialchars($priceLabel2) ?>)">
                                <small class="text-muted">Leave empty if this product does not have a second price.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 dimensions-field">
                                <label for="dimensions" class="form-label">Dimensions / Size</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions"
                                    placeholder="e.g., 12 x 12 inches">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order"
                                    value="<?= $item['display_order'] ?>" min="1">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_new_arrival"
                                        name="is_new_arrival" value="1">
                                    <label class="form-check-label" for="is_new_arrival">
                                        <i class="fas fa-star text-warning me-1"></i>New Arrival
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Variant Tables (Hidden by default) -->

                        <!-- Size Variants Table -->
                        <div id="size_variants_section" class="mb-4" style="display:none;">
                            <h6 class="border-bottom pb-2">Size Variants</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="size_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="20%">Name (Opt)</th>
                                            <th width="15%">Code <span class="text-danger">*</span></th>
                                            <th width="15%">Size <span class="text-danger">*</span></th>
                                            <th width="12%"><span class="price-title-1"><?= htmlspecialchars($priceLabel1) ?></span> <span class="text-danger">*</span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(1)" title="Click to rename"></i></th>
                                            <th width="12%" class="dual-price-col"><span class="price-title-2"><?= htmlspecialchars($priceLabel2) ?></span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(2)" title="Click to rename"></i></th>
                                            <th width="21%">Image</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rows added via JS -->
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="addSizeRow()">
                                    <i class="fas fa-plus me-1"></i> Add Size Variant
                                </button>
                            </div>
                        </div>

                        <div id="color_variants_section" class="mb-4" style="display:none;">
                            <h6 class="border-bottom pb-2">Color Variants</h6>

                            <!-- Colour / Finish Label Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Colour Variant Label</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colour_label"
                                            id="label_colour" value="colour" checked>
                                        <label class="form-check-label" for="label_colour">
                                            <i class="fas fa-palette me-1 text-primary"></i> Colour <span
                                                class="text-muted small">(default)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colour_label"
                                            id="label_finish" value="finish">
                                        <label class="form-check-label" for="label_finish">
                                            <i class="fas fa-brush me-1 text-warning"></i> Finish
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">Label shown in the mobile app (e.g., "Colour: Ivory White" or
                                    "Finish: Blue Boy").</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="color_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Name (Opt)</th>
                                            <th width="120">Code <span class="text-danger">*</span></th>
                                            <th width="350">Color <span class="text-danger">*</span></th>
                                            <th width="80">Preview</th>
                                            <th width="120"><span class="price-title-1"><?= htmlspecialchars($priceLabel1) ?></span> <span class="text-danger">*</span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(1)" title="Click to rename"></i></th>
                                            <th width="120" class="dual-price-col"><span class="price-title-2"><?= htmlspecialchars($priceLabel2) ?></span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(2)" title="Click to rename"></i></th>
                                            <th>Image</th>
                                            <th width="80">Sizes <span class="text-muted small">(Opt)</span></th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Rows added via JS -->
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="addColorRow()">
                                    <i class="fas fa-plus me-1"></i> Add Color Variant
                                </button>
                            </div>
                        </div>

                        <!-- Product Features -->
                        <div class="mb-4">
                            <label class="form-label">Product Features <span
                                    class="text-muted small">(Optional)</span></label>
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php if (empty($features)): ?>
                                            <div class="col-12 text-muted small">No features found in the library. <a
                                                    href="../features/create.php">Create some first</a>.</div>
                                        <?php else: ?>
                                            <?php foreach ($features as $f): ?>
                                                <div class="col-md-3 col-sm-4 col-6">
                                                    <div
                                                        class="form-check custom-checkbox-card p-2 border rounded bg-white h-100 d-flex align-items-center">
                                                        <input class="form-check-input ms-1 me-2 mt-0" type="checkbox"
                                                            name="feature_ids[]" value="<?= $f['id'] ?>"
                                                            id="feature_<?= $f['id'] ?>">
                                                        <label class="form-check-label w-100 d-flex align-items-center mb-0"
                                                            for="feature_<?= $f['id'] ?>" style="cursor: pointer;">
                                                            <?php if ($f['icon_url']): ?>
                                                                <img src="../../../uploads/catalogue/features/<?= htmlspecialchars($f['icon_url']) ?>"
                                                                    alt="icon"
                                                                    style="width: 24px; height: 24px; object-fit: contain;"
                                                                    class="me-2">
                                                            <?php else: ?>
                                                                <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center text-white"
                                                                    style="width: 24px; height: 24px; font-size: 10px;">
                                                                    <i class="fas fa-star"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            <span class="small fw-medium text-truncate"
                                                                title="<?= htmlspecialchars($f['name']) ?>"><?= htmlspecialchars($f['name']) ?></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Technical Specifications (Rich Text) -->
                        <div class="mb-3">
                            <label class="form-label">Technical Specifications</label>
                            <div id="specs-editor" style="height: 200px;"></div>
                            <input type="hidden" id="specifications" name="specifications">
                        </div>

                        <!-- Image Gallery (Main) -->
                        <div class="mb-4">
                            <label for="images" class="form-label fw-semibold">Product Gallery Images (Shared / Main) <span class="text-danger">*</span></label>
                            
                            <!-- Drag & Drop Upload Zone -->
                            <div class="upload-dropzone p-4 text-center rounded-3 position-relative mb-3" id="dropzone_container" style="border: 2px dashed #0d6efd; background-color: #f8fbff; min-height: 130px; display: flex; align-items: center; justify-content: center; cursor: pointer; position: relative;">
                                <input type="file" id="images" name="images[]" multiple accept="image/*" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;">
                                <div class="dropzone-content py-2" style="pointer-events: none;">
                                    <div class="mb-2">
                                        <i class="fas fa-cloud-arrow-up fa-3x text-primary dropzone-icon"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Drag and drop product images here, or <span class="text-primary text-decoration-underline">Browse Files</span></h6>
                                    <p class="text-muted small mb-0">Supports JPG, JPEG, PNG, WEBP &bull; Max 5 images in total (1 primary + 4 gallery)</p>
                                </div>
                            </div>

                            <!-- Gallery Alert Container for notifications -->
                            <div id="gallery_alert_box"></div>

                            <!-- Selected Images Preview Container -->
                            <div id="new_images_container" class="mt-3 p-3 bg-light rounded border border-primary border-opacity-25" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-primary mb-0 small fw-bold">
                                        <i class="fas fa-images me-1"></i> Selected Images Preview <span class="badge bg-primary ms-1" id="new_images_count">0</span>
                                    </h6>
                                    <span class="text-muted small" style="font-size: 11px;">
                                        <i class="fas fa-grip-vertical me-1"></i>Drag to reorder &bull; Set any image as Primary
                                    </span>
                                </div>
                                <div id="new_images_list"></div>
                            </div>
                        </div>

                        <!-- Product Video (Optional) -->
                        <div class="mb-4 p-3 bg-light rounded border">
                            <h6 class="text-muted mb-3"><i class="fas fa-video me-2"></i>Product Video (Optional)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="video" class="form-label">Option A: Upload Video File</label>
                                    <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                                    <div class="form-text">Max size: 10MB. Supported formats: MP4, WebM, Ogg, MOV.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="video_url_link" class="form-label">Option B: Or Enter External Video URL</label>
                                    <input type="url" class="form-control" id="video_url_link" name="video_url_link" placeholder="e.g., https://www.youtube.com/watch?v=... or direct MP4 link">
                                    <div class="form-text">Supports direct video URLs, YouTube, or Vimeo links.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" checked>
                                <label class="form-check-label" for="is_active">Active (Visible)</label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Product <span class="badge bg-white text-primary ms-1 small fw-normal" style="font-size:0.75rem;">Shift + Enter</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                <ul class="small text-muted ps-3">
                    <li class="mb-2">Hierarchy must be selected in order: Category -> Sub Category -> Series.</li>
                    <li class="mb-2">If a Category has no Sub Categories, select 'None' in Sub Category to find 3-Tier
                        Series.</li>
                    <li class="mb-2">Product Code must be unique <strong>within the selected Series</strong>.</li>
                    <li class="mb-2"><strong>Dimensions</strong> field is useful for Sinks and Mirrors.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Delete Image Confirmation Modal -->
<div class="modal fade" id="deleteImageConfirmModal" tabindex="-1" aria-labelledby="deleteImageModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-2 px-3">
                <h6 class="modal-title fw-bold mb-0" id="deleteImageModalLabel">
                    <i class="fas fa-trash-alt me-2"></i>Delete Image Confirmation
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div class="mb-3" id="deleteModalImgWrapper">
                    <img id="deleteModalImgPreview" src="" class="img-thumbnail shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                </div>
                <h6 class="fw-bold mb-2 text-dark" id="deleteModalTitle">Remove this selected image?</h6>
                <p class="text-muted small mb-0" id="deleteModalMessage">
                    This image will be removed from your upload selection.
                </p>
            </div>
            <div class="modal-footer py-2 px-3 bg-light d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteImageBtn">
                    <i class="fas fa-trash-alt me-1"></i>Delete Image
                </button>
            </div>
        </div>
    </div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
    $(document).ready(function () {
        // 1. Variant Type Logic
        $('#variant_type').change(function () {
            var type = $(this).val();

            // Helper to toggle section inputs
            function toggleSection(selector, show) {
                var section = $(selector);
                if (show) {
                    section.show();
                    section.find('input, select, textarea').prop('disabled', false);
                } else {
                    section.hide();
                    section.find('input, select, textarea').prop('disabled', true);
                }
            }

            // Helper for simple fields (which are not wrapped in a single container)
            function toggleSimpleFields(show) {
                var fields = $('.simple-field');
                if (show) {
                    fields.show();
                    fields.find('input, select, textarea').prop('disabled', false);
                    $('#code').prop('required', true);
                } else {
                    fields.hide();
                    fields.find('input, select, textarea').prop('disabled', true);
                    $('#code').prop('required', false);
                }
            }

            if (type === 'none') {
                toggleSimpleFields(true);
                $('.dimensions-field').show();
                toggleSection('#size_variants_section', false);
                toggleSection('#color_variants_section', false);
            } else if (type === 'size') {
                toggleSimpleFields(false);
                $('.dimensions-field').hide();
                toggleSection('#size_variants_section', true);
                toggleSection('#color_variants_section', false);
                if ($('#size_table tbody tr').length === 0) addSizeRow();
            } else if (type === 'color') {
                toggleSimpleFields(false);
                $('.dimensions-field').show();
                toggleSection('#size_variants_section', false);
                toggleSection('#color_variants_section', true);
                if ($('#color_table tbody tr').length === 0) addColorRow();
            }
        });

        // Initialize state on load
        $('#variant_type').trigger('change');

        // 2. Select2 Init & Helpers
        var select2Opts = { theme: 'bootstrap-5', allowClear: true, width: '100%' };
        $('#category_id').select2($.extend({}, select2Opts, { placeholder: '-- Select --' }));

        function reinitSelect2(selector, placeholder) {
            if ($(selector).hasClass('select2-hidden-accessible')) $(selector).select2('destroy');
            $(selector).select2($.extend({}, select2Opts, { placeholder: placeholder }));
        }

        // 3. Load Sub Categories logic
        $('#category_id').change(function () {
            var categoryId = $(this).val();
            var subCatSelect = $('#sub_category_id');
            var seriesSelect = $('#series_id');

            // Dual Zone Logic
            var selectedOption = $(this).find('option:selected');
            var hasDualPrice = selectedOption.data('dual-price') == 1;
            toggleDualZonePricing(hasDualPrice);

            // Reset valid selects
            subCatSelect.html('<option value="">-- Loading... --</option>').prop('disabled', true);
            seriesSelect.html('<option value="">-- Select Sub Category First --</option>').prop('disabled', true);

            if (categoryId) {
                $.ajax({
                    url: '../../catalogue/ajax/get_sub_categories.php',
                    type: 'GET',
                    data: { category_id: categoryId },
                    dataType: 'json',
                    success: function (response) {
                        subCatSelect.empty();
                        // Always add "None" option as first choice
                        subCatSelect.append('<option value="">-- None (Direct / 3-Tier) --</option>');

                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function (index, item) {
                                subCatSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        }
                        subCatSelect.prop('disabled', false);
                        reinitSelect2('#sub_category_id', '-- None (Direct / 3-Tier) --');
                        // Trigger change to load 3-Tier series (where sub_cat is null)
                        subCatSelect.trigger('change');
                    },
                    error: function () {
                        console.error('Failed to fetch sub categories');
                        subCatSelect.html('<option value="">-- Error Loading --</option>');
                    }
                });
            } else {
                subCatSelect.html('<option value="">-- Select Main Category First --</option>');
                toggleDualZonePricing(false);
            }
        });

        // 3. Load Series logic
        $('#sub_category_id').change(function () {
            var subCategoryId = $(this).val(); // Can be empty string (None)
            var categoryId = $('#category_id').val();
            var seriesSelect = $('#series_id');

            seriesSelect.html('<option value="">-- Loading... --</option>').prop('disabled', true);

            if (categoryId) {
                $.ajax({
                    url: '../../catalogue/ajax/get_series.php',
                    type: 'GET',
                    data: {
                        category_id: categoryId,
                        sub_category_id: subCategoryId // Pass empty string or ID
                    },
                    dataType: 'json',
                    success: function (response) {
                        seriesSelect.empty();
                        seriesSelect.append('<option value="">-- Direct to Main Category (No Series) --</option>');

                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function (index, item) {
                                seriesSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                        }
                        seriesSelect.prop('disabled', false);
                        reinitSelect2('#series_id', '-- Direct to Main Category (No Series) --');
                    },
                    error: function () {
                        console.error('Failed to fetch series');
                        seriesSelect.empty().append('<option value="">-- Direct to Main Category (No Series) --</option>').prop('disabled', false);
                        reinitSelect2('#series_id', '-- Direct to Main Category (No Series) --');
                    }
                });
            }
        });

        // Limit product gallery images to a maximum of 5
        $('#images').change(function () {
            if (this.files.length > 5) {
                alert("You can upload a maximum of 5 images in total (1 primary + 4 gallery images).");
                this.value = ''; // clear input
            }
        });

        $('form').submit(function (e) {
            var filesCount = $('#images')[0].files.length;
            if (filesCount > 5) {
                e.preventDefault();
                alert("You can upload a maximum of 5 images in total (1 primary + 4 gallery images).");
                return false;
            }
        });
    });

    // Helper Variables
    let sizeIndex = 0;
    let colorIndex = 0;
    const availableColors = <?= json_encode($colors) ?>;

    // Dual Zone Helper - enabled by default
    window.dualZoneEnabled = true;
    function toggleDualZonePricing(enable) {
        window.dualZoneEnabled = true; // Always allow dual pricing
        $('.dual-price-field').show();
        $('.dual-price-col').show();
        $('.dual-price-input').show();
    }

    function addSizeRow() {
        let displayStyle = (window.dualZoneEnabled) ? '' : 'display:none;';
        const curLabel1 = $('#price_label_1').val().trim() || defaultLabel1;
        const curLabel2 = $('#price_label_2').val().trim() || defaultLabel2;

        let html = `
        <tr>
            <td><input type="text" name="variants[size][${sizeIndex}][name]" class="form-control form-control-sm" placeholder="Variant Name"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][code]" class="form-control form-control-sm" required placeholder="Product Code"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][value]" class="form-control form-control-sm" required placeholder="Size (e.g., 25MM)"></td>
            <td><input type="text" inputmode="numeric" name="variants[size][${sizeIndex}][price]" class="form-control form-control-sm" required placeholder="${curLabel1}"></td>
            <td class="dual-price-col">
                <input type="text" inputmode="numeric" name="variants[size][${sizeIndex}][price_zone2]" class="form-control form-control-sm dual-price-input" placeholder="${curLabel2}">
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2 bg-white border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                        <span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="variant_images_size_${sizeIndex}" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').remove()"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
        $('#size_table tbody').append(html);
        sizeIndex++;
    }

    function addColorRow() {
        let options = '<option value="">-- Select --</option>';
        availableColors.forEach(c => {
            options += `<option value="${c.id}" data-name="${c.name}" data-type="${c.type}" data-hex="${c.hex_code}" data-texture="${c.texture_image}">${c.name}</option>`;
        });

        let displayStyle = (window.dualZoneEnabled) ? '' : 'display:none;';
        const curLabel1 = $('#price_label_1').val().trim() || defaultLabel1;
        const curLabel2 = $('#price_label_2').val().trim() || defaultLabel2;

        let html = `
        <tr class="color-main-row" data-color-idx="${colorIndex}">
            <td><input type="text" name="variants[color][${colorIndex}][name]" class="form-control form-control-sm" placeholder="Variant Name"></td>
            <td><input type="text" name="variants[color][${colorIndex}][code]" class="form-control form-control-sm" required placeholder="Product Code"></td>
            <td>
                <div class="d-flex gap-2">
                    <select name="variants[color][${colorIndex}][color_id]" class="form-select form-select-sm" style="width: 50%;" required onchange="onColorSelect(this, ${colorIndex})">
                        ${options}
                    </select>
                    <input type="text" name="variants[color][${colorIndex}][value]" class="form-control form-control-sm" style="width: 50%;" required placeholder="Display Name" id="color_name_${colorIndex}">
                </div>
            </td>
            <td class="text-center align-middle">
                <div id="color_preview_${colorIndex}" class="border rounded d-inline-block" style="width: 30px; height: 30px; background: #f8f9fa;"></div>
            </td>
            <td><input type="text" inputmode="numeric" name="variants[color][${colorIndex}][price]" class="form-control form-control-sm" required placeholder="${curLabel1}"></td>
            <td class="dual-price-col">
                <input type="text" inputmode="numeric" name="variants[color][${colorIndex}][price_zone2]" class="form-control form-control-sm dual-price-input" placeholder="${curLabel2}">
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2 bg-white border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                        <span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>
                    </div>
                    <div class="flex-grow-1">
                        <input type="file" name="variant_images_color_${colorIndex}" class="form-control form-control-sm" accept="image/*">
                    </div>
                </div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-info toggle-color-sizes" data-color-idx="${colorIndex}" title="Add size dimensions for this colour">
                    <i class="fas fa-ruler-combined me-1"></i> Sizes
                </button>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').nextAll('tr.color-size-row[data-color-idx=${colorIndex}]').first().remove(); $(this).closest('tr').remove();"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <tr class="color-size-row" data-color-idx="${colorIndex}" style="display:none; background:#f8f9fb;">
            <td colspan="9" class="p-2">
                <div class="border rounded p-2" style="background:#fff;">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-ruler-combined text-info me-2"></i>
                        <span class="fw-semibold small text-info">Size Dimensions</span>
                        <span class="badge bg-secondary ms-2 small" style="font-size:10px;">Optional</span>
                        <span class="text-muted small ms-2">— Add size-specific dimensions &amp; prices for this colour</span>
                    </div>
                    <table class="table table-sm table-bordered mb-1" id="color_size_table_${colorIndex}">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width:140px;">Dimension / Size <span class="text-danger">*</span></th>
                                <th style="min-width:110px;">${curLabel1} <span class="text-danger">*</span></th>
                                <th style="min-width:110px;" class="dual-price-col">${curLabel2}</th>
                                <th style="width:40px;"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="addColorSizeRow(${colorIndex})">
                        <i class="fas fa-plus me-1"></i> Add Size
                    </button>
                </div>
            </td>
        </tr>
    `;
        $('#color_table tbody').append(html);
        colorIndex++;
    }

    // delegate toggle click (inside ready is fine for event delegation)
    $(document).on('click', '.toggle-color-sizes', function(e) {
        e.preventDefault();
        const cIdx = $(this).data('color-idx');
        const mainRow = $(this).closest('tr.color-main-row');
        let sizeRow = mainRow.next('tr.color-size-row');
        if (!sizeRow.length) {
            sizeRow = $(this).closest('table').find(`tr.color-size-row[data-color-idx="${cIdx}"]`).first();
        }
        const isVisible = sizeRow.is(':visible');
        if (isVisible) {
            sizeRow.hide();
            $(this).removeClass('btn-info text-white').addClass('btn-outline-info');
        } else {
            sizeRow.show();
            $(this).removeClass('btn-outline-info').addClass('btn-info text-white');
            const tbody = $(`#color_size_table_${cIdx} tbody`);
            if (tbody.find('tr').length === 0) {
                window.addColorSizeRow(cIdx);
            }
        }
    });

    function onColorSelect(select, index) {
        const option = select.options[select.selectedIndex];
        if (!option.value) return;

        const type = option.getAttribute('data-type');
        const hex = option.getAttribute('data-hex');
        const texture = option.getAttribute('data-texture');
        const name = option.getAttribute('data-name');

        const preview = document.getElementById(`color_preview_${index}`);
        const nameInput = document.getElementById(`color_name_${index}`);

        // Update Preview
        if (type === 'solid') {
            preview.style.background = hex;
            preview.title = name;
        } else if (type === 'texture') {
            preview.style.background = `url('${window.XADMIN_BASE}/uploads/catalogue/colors/${texture}') center/cover no-repeat`;
            preview.title = name;
        }

        // Auto-fill Name if empty
        if (nameInput.value === '') {
            nameInput.value = name;
        }
    }

    // -- Quill Rich Text Editor --
    var specsQuill = new Quill('#specs-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['clean']
            ]
        },
        placeholder: 'Enter technical specifications...'
    });

    // Image Gallery & Upload Handling
    var newFilesDataTransfer = new DataTransfer();

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showGalleryAlert(message, type = 'warning') {
        var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show py-2 px-3 small mt-2 mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-1"></i> ${message}
                <button type="button" class="btn-close py-2 px-3" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#gallery_alert_box').html(alertHtml);
        setTimeout(function() {
            $('#gallery_alert_box .alert').fadeOut(400, function() { $(this).remove(); });
        }, 5000);
    }

    function ensureAtLeastOnePrimary() {
        if ($('input[name="primary_image"]:checked').length === 0) {
            var firstNew = $('.new-image-row .primary-radio').first();
            if (firstNew.length > 0) {
                firstNew.prop('checked', true);
            }
        }
    }

    function renderNewImagesPreview(savedPrimaryVal = null) {
        var container = $('#new_images_container');
        var list = $('#new_images_list');
        var countBadge = $('#new_images_count');
        list.empty();

        var files = Array.from(newFilesDataTransfer.files);

        if (files.length === 0) {
            container.hide();
            countBadge.text('0');
            return;
        }

        container.show();
        countBadge.text(files.length);

        var currentPrimaryVal = savedPrimaryVal !== null ? savedPrimaryVal : $('input[name="primary_image"]:checked').val();

        files.forEach(function(file, idx) {
            var blobUrl = URL.createObjectURL(file);
            var sizeInKb = (file.size / 1024).toFixed(1) + ' KB';
            var val = 'new_' + idx;

            var isChecked = false;
            if (currentPrimaryVal === val) {
                isChecked = true;
            } else if (currentPrimaryVal === undefined && idx === 0) {
                isChecked = true;
            }

            var rowHtml = `
                <div class="d-flex align-items-center border-bottom py-2 new-image-row" data-index="${idx}">
                    <div class="me-2 text-muted drag-handle px-1" title="Drag to reorder">
                        <i class="fas fa-grip-vertical fa-lg"></i>
                    </div>
                    <div class="me-2 position-relative">
                        <img src="${blobUrl}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" onclick="window.open('${blobUrl}', '_blank')" title="Click to view preview">
                        <span class="badge bg-primary position-absolute top-0 start-0" style="font-size: 8px; transform: translate(-15%, -15%);">${idx === 0 ? 'IMG 1' : 'IMG ' + (idx + 1)}</span>
                    </div>
                    <div class="flex-grow-1 ms-1">
                        <div class="form-check mb-1">
                            <input class="form-check-input primary-radio" type="radio" name="primary_image" id="primary_new_${idx}" value="${val}" ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label small fw-bold text-dark" for="primary_new_${idx}" style="cursor: pointer;">
                                Set as Primary Image
                            </label>
                        </div>
                        <div class="text-muted text-truncate" style="font-size: 11px; max-width: 280px;" title="${escapeHtml(file.name)}">
                            ${escapeHtml(file.name)} <span class="badge bg-light text-secondary border ms-1">${sizeInKb}</span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-new-image-btn" data-index="${idx}" title="Remove file">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            list.append(rowHtml);
        });

        ensureAtLeastOnePrimary();
    }

    // Process selected files from drop or file dialog
    function processSelectedFiles(filesList) {
        var selectedFiles = Array.from(filesList);
        if (!selectedFiles.length) return;

        var currentNewImages = newFilesDataTransfer.files.length;
        var validFiles = [];
        var nonImageCount = 0;

        selectedFiles.forEach(function(file) {
            if (!file.type.match('image.*')) {
                nonImageCount++;
                return;
            }
            var alreadyExists = Array.from(newFilesDataTransfer.files).some(function(f) {
                return f.name === file.name && f.size === file.size && f.lastModified === file.lastModified;
            });
            if (alreadyExists) return;
            validFiles.push(file);
        });

        if (nonImageCount > 0) {
            showGalleryAlert(nonImageCount + ' file(s) were skipped because they are not valid images.', 'warning');
        }

        if (currentNewImages + validFiles.length > 5) {
            var allowed = Math.max(0, 5 - currentNewImages);
            showGalleryAlert("You can upload a maximum of 5 images in total (1 primary + 4 gallery images). You can add at most " + allowed + " more image(s).", 'warning');
            validFiles = validFiles.slice(0, allowed);
        }

        validFiles.forEach(function(file) {
            newFilesDataTransfer.items.add(file);
        });

        $('#images')[0].files = newFilesDataTransfer.files;
        renderNewImagesPreview();
    }

    // Dropzone Visual Highlight on Drag
    var dropzone = $('#dropzone_container');
    var fileInput = $('#images');

    fileInput.on('dragenter dragover', function() {
        dropzone.addClass('dragover');
    }).on('dragleave drop', function() {
        dropzone.removeClass('dragover');
    });

    fileInput.on('change', function() {
        if (this.files && this.files.length) {
            processSelectedFiles(this.files);
        }
    });

    // Delete Modal Setup & State
    function getDeleteModalInstance() {
        var el = document.getElementById('deleteImageConfirmModal');
        if (!el) return null;
        return bootstrap.Modal.getOrCreateInstance(el);
    }
    var pendingDeleteAction = null;

    // Remove single newly selected file (Using Styled Modal)
    $(document).on('click', '.remove-new-image-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var removeIndex = parseInt($(this).data('index'), 10);
        var row = $(this).closest('.new-image-row');
        var imgSrc = row.find('img').attr('src');

        $('#deleteModalImgPreview').attr('src', imgSrc).show();
        $('#deleteModalImgWrapper').show();
        $('#deleteModalTitle').text('Remove this selected image?');
        $('#deleteModalMessage').text('This image will be removed from your upload selection.');

        pendingDeleteAction = function() {
            var currentPrimary = $('input[name="primary_image"]:checked').val();
            var wasThisPrimary = (currentPrimary === ('new_' + removeIndex));

            var newDt = new DataTransfer();
            var allFiles = Array.from(newFilesDataTransfer.files);
            var newPrimaryValToKeep = null;

            var newIdx = 0;
            allFiles.forEach(function(file, idx) {
                if (idx !== removeIndex) {
                    newDt.items.add(file);
                    if (currentPrimary === ('new_' + idx)) {
                        newPrimaryValToKeep = 'new_' + newIdx;
                    }
                    newIdx++;
                }
            });

            newFilesDataTransfer = newDt;
            $('#images')[0].files = newFilesDataTransfer.files;

            if (wasThisPrimary) {
                renderNewImagesPreview(null);
            } else {
                renderNewImagesPreview(newPrimaryValToKeep);
            }
        };

        var modal = getDeleteModalInstance();
        if (modal) modal.show();
    });

    // Modal Confirmation Execution
    $('#confirmDeleteImageBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (typeof pendingDeleteAction === 'function') {
            pendingDeleteAction();
            pendingDeleteAction = null;
        }
        var modal = getDeleteModalInstance();
        if (modal) modal.hide();
    });

    // Keydown on Delete Modal: Enter key confirms deletion only, NEVER submits the product form
    $('#deleteImageConfirmModal').on('keydown', function(e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            $('#confirmDeleteImageBtn').trigger('click');
            return false;
        }
    });

    // Save Product when Shift + Enter is pressed
    $(document).on('keydown', function(e) {
        if ((e.key === 'Enter' || e.keyCode === 13) && e.shiftKey) {
            // If delete modal is open, let modal handle it
            if ($('#deleteImageConfirmModal').hasClass('show')) {
                return;
            }
            e.preventDefault();
            var form = $('form')[0];
            if (form && typeof form.reportValidity === 'function') {
                if (!form.reportValidity()) {
                    return;
                }
            }
            $('form').submit();
        }
    });

    // Prevent regular Enter key in inputs from accidentally submitting
    $('form input[type="text"], form input[type="number"], form input[type="url"]').on('keydown', function(e) {
        if ((e.key === 'Enter' || e.keyCode === 13) && !e.shiftKey) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize SortableJS on Preview Images
    if (typeof Sortable !== 'undefined') {
        var newListEl = document.getElementById('new_images_list');
        if (newListEl) {
            Sortable.create(newListEl, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    var newOrderIndices = [];
                    $('#new_images_list .new-image-row').each(function() {
                        newOrderIndices.push(parseInt($(this).data('index'), 10));
                    });

                    var currentFiles = Array.from(newFilesDataTransfer.files);
                    var currentPrimary = $('input[name="primary_image"]:checked').val();
                    var selectedFileObj = null;
                    if (currentPrimary && currentPrimary.indexOf('new_') === 0) {
                        var pIdx = parseInt(currentPrimary.replace('new_', ''), 10);
                        selectedFileObj = currentFiles[pIdx];
                    }

                    var newDt = new DataTransfer();
                    newOrderIndices.forEach(function(oldIdx) {
                        if (currentFiles[oldIdx]) {
                            newDt.items.add(currentFiles[oldIdx]);
                        }
                    });
                    newFilesDataTransfer = newDt;
                    $('#images')[0].files = newFilesDataTransfer.files;

                    var newPrimaryVal = null;
                    if (selectedFileObj) {
                        Array.from(newFilesDataTransfer.files).forEach(function(f, idx) {
                            if (f.name === selectedFileObj.name && f.size === selectedFileObj.size && f.lastModified === selectedFileObj.lastModified) {
                                newPrimaryVal = 'new_' + idx;
                            }
                        });
                    }
                    renderNewImagesPreview(newPrimaryVal);
                }
            });
        }
    }

    // Form Submission Validation + Quill Sync
    $('form').on('submit', function (e) {
        var specsHtml = specsQuill.root.innerHTML;
        if (specsHtml === '<p><br></p>') specsHtml = '';
        $('#specifications').val(specsHtml);

        var newImages = newFilesDataTransfer.files.length;
        if (newImages === 0) {
            e.preventDefault();
            showGalleryAlert('At least one product image is required.', 'danger');
            return false;
        }

        if (newImages > 5) {
            e.preventDefault();
            showGalleryAlert("You can have a maximum of 5 images in total (1 primary + 4 gallery images). Please remove some images.", 'danger');
            return false;
        }

        $('#images')[0].files = newFilesDataTransfer.files;
        ensureAtLeastOnePrimary();
    });

    // Per-Product Price Label UI Logic
    const defaultLabel1 = <?= json_encode($globalPriceLabel1) ?>;
    const defaultLabel2 = <?= json_encode($globalPriceLabel2) ?>;

    function updatePriceTitlesUI() {
        const val1 = $('#price_label_1').val().trim() || defaultLabel1;
        const val2 = $('#price_label_2').val().trim() || defaultLabel2;
        $('.price-title-1').text(val1);
        $('.price-title-2').text(val2);
        $('input[name*="[price]"]:not([name*="[price_zone2]"])').attr('placeholder', val1);
        $('input[name*="[price_zone2]"]').attr('placeholder', val2);
        $('#price').attr('placeholder', 'e.g., 1500 (' + val1 + ')');
        $('#price_zone2').attr('placeholder', 'e.g., 1500 (' + val2 + ')');
    }

    $('#price_label_1, #price_label_2').on('input change', updatePriceTitlesUI);

    window.openLabelEdit = function(num) {
        const target = $('#price_label_' + num);
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 120
            }, 200, function() {
                target.focus().select();
                target.addClass('border-primary shadow-sm bg-white');
                setTimeout(function() {
                    target.removeClass('border-primary shadow-sm bg-white');
                }, 1200);
            });
        }
    };

    // Global: needed so onclick="addColorSizeRow()" in injected HTML works
    window.addColorSizeRow = function(cIdx) {
        const curLabel1 = ($('#price_label_1').val() || '').trim() || defaultLabel1;
        const curLabel2 = ($('#price_label_2').val() || '').trim() || defaultLabel2;
        const tbody = $('#color_size_table_' + cIdx + ' tbody');
        const uId = Date.now() + '_' + Math.floor(Math.random() * 1000);
        const html = `
            <tr>
                <td><input type="text" name="variants[color][${cIdx}][sizes][${uId}][dimension]" class="form-control form-control-sm" placeholder="e.g. 12x12 in"></td>
                <td><input type="text" inputmode="numeric" name="variants[color][${cIdx}][sizes][${uId}][price]" class="form-control form-control-sm" placeholder="${curLabel1}"></td>
                <td class="dual-price-col"><input type="text" inputmode="numeric" name="variants[color][${cIdx}][sizes][${uId}][price_zone2]" class="form-control form-control-sm" placeholder="${curLabel2}"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="$(this).closest('tr').remove()"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
        tbody.append(html);
    };
    function addColorSizeRow(cIdx) { return window.addColorSizeRow(cIdx); }

</script>