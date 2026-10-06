<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

// Fetch Product + Hierarchy Info
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$sql = "SELECT p.*, {$productHierarchy['category']} AS category_id, {$productHierarchy['sub_category']} AS sub_category_id
        FROM catalogue_products p
        LEFT JOIN catalogue_series s ON p.series_id = s.id
        WHERE p.id = ?";
$item = fetchOne($sql, [$id]);

if (!$item) {
    $_SESSION['error'] = "Product not found.";
    header('Location: list.php');
    exit;
}

// If display_order is 0 or empty, automatically assign the next display order
if (empty($item['display_order']) || (int)$item['display_order'] <= 0) {
    $maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_products");
    $item['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;
}

// Fetch Inputs
// Fetch Active Main Categories
// Fetch Active Main Categories
$main_categories = fetchAll("SELECT id, name, has_dual_price FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");

$globalPriceLabel1 = getPriceLabel1();
$globalPriceLabel2 = getPriceLabel2();
$itemPriceLabel1 = !empty($item['price_label_1']) ? $item['price_label_1'] : (getSetting('prod_price_label_1_' . $item['id']) ?: '');
$itemPriceLabel2 = !empty($item['price_label_2']) ? $item['price_label_2'] : (getSetting('prod_price_label_2_' . $item['id']) ?: '');
$priceLabel1 = $itemPriceLabel1 !== '' ? $itemPriceLabel1 : $globalPriceLabel1;
$priceLabel2 = $itemPriceLabel2 !== '' ? $itemPriceLabel2 : $globalPriceLabel2;

// Dual Price is enabled for all products
$hasDualPrice = 1;
$dualPriceDisplay = '';
// Fetch Active Colors
$colors = fetchAll("SELECT id, name, type, hex_code, texture_image FROM catalogue_colors WHERE is_active = 1 ORDER BY name ASC");
// Fetch Active Features
$features = fetchAll("SELECT id, name, icon_url FROM catalogue_features WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
// Fetch Selected Features
$selected_features = fetchAll("SELECT feature_id FROM catalogue_product_features WHERE product_id = ?", [$id]);
$selected_feature_ids = array_column($selected_features, 'feature_id');
// Fetch Existing Images
$images = fetchAll("SELECT * FROM catalogue_product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC", [$id]);

$pageTitle = "Edit Product";
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

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
            <a href="../qr/list.php?search=<?= urlencode($item['code'] ?: $item['name']) ?>" class="btn btn-outline-dark btn-sm mb-2 ms-2">
                <i class="fas fa-qrcode me-1"></i>Catalogue QR Code
            </a>
            <h2><i class="fas fa-edit me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <form method="POST" action="edit_process.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $item['id'] ?>">
        
        <!-- Hidden fields for JS initialization -->
        <input type="hidden" id="initial_category_id" value="<?= $item['category_id'] ?>">
        <input type="hidden" id="initial_sub_category_id" value="<?= $item['sub_category_id'] ?? '' ?>">
        <input type="hidden" id="initial_series_id" value="<?= $item['series_id'] ?>">

        <div class="row">
            <div class="col-md-12">
                <!-- Product Details Card -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Product Details</h6>
                    </div>
                    <div class="card-body">
                        
                        <!-- Hierarchy Selection -->
                        <div class="row mb-4 p-3 bg-light rounded border">
                            <h6 class="text-muted mb-3"><i class="fas fa-sitemap me-2"></i>Classification</h6>
                            
                            <!-- Main Category -->
                            <div class="col-md-4 mb-3">
                                <label for="category_id" class="form-label">Main Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- Select --</option>
                                    <?php foreach ($main_categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= $item['category_id'] == $cat['id'] ? 'selected' : '' ?>
                                                data-dual-price="<?= $cat['has_dual_price'] ?>">
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sub Category -->
                            <div class="col-md-4 mb-3">
                                <label for="sub_category_id" class="form-label">Sub Category</label>
                                <select class="form-select" id="sub_category_id" name="sub_category_id">
                                    <option value="">-- Loading... --</option>
                                </select>
                            </div>

                            <!-- Series -->
                            <div class="col-md-4 mb-3">
                                <label for="series_id" class="form-label">Series <span class="text-muted small fw-normal">(Optional — Direct if none)</span></label>
                                <select class="form-select" id="series_id" name="series_id">
                                    <option value="">-- Direct (No Series) --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Variant Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Product Type</label>
                                <select class="form-select" id="variant_type" name="variant_type">
                                    <option value="none" <?= $item['variant_type'] === 'none' ? 'selected' : '' ?>>Simple Product</option>
                                    <option value="size" <?= $item['variant_type'] === 'size' ? 'selected' : '' ?>>Size Variants</option>
                                    <option value="color" <?= $item['variant_type'] === 'color' ? 'selected' : '' ?>>Color Variants</option>
                                </select>
                                <small class="text-muted">Select the type of product (Simple, Size, or Color Variants).</small>
                                <div id="variant_type_warning" class="text-warning small mt-1" style="display:none;">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Note: Changing the product type will remove variants of the previous type when you save.
                                </div>
                            </div>
                        </div>

                        <!-- Basic Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                <small class="text-muted" id="name_variant_hint" style="<?= $item['variant_type'] !== 'none' ? '' : 'display:none;' ?>">Group Title for Variants.</small>
                            </div>
                            
                            <div class="col-md-3 mb-3" id="product_code_wrapper">
                                <label for="code" class="form-label">Product Code <span class="text-danger" id="code_required_star" style="<?= $item['variant_type'] === 'none' ? '' : 'display:none;' ?>">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars(dashCode($item['code'] ?? '')) ?>" <?= $item['variant_type'] === 'none' ? 'required' : '' ?> placeholder="e.g., CR-101">
                                <small class="text-muted" id="code_variant_hint" style="<?= $item['variant_type'] !== 'none' ? '' : 'display:none;' ?>">Base code for product.</small>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="hsn_code" class="form-label">HSN Code</label>
                                <input type="text" class="form-control" id="hsn_code" name="hsn_code"
                                    maxlength="15" value="<?= htmlspecialchars($item['hsn_code'] ?? '') ?>"
                                    placeholder="e.g., 6912">
                                <small class="text-muted">Used on tax invoices.</small>
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
                                <input type="text" class="form-control price-label-input" id="price_label_1" name="price_label_1" value="<?= htmlspecialchars($itemPriceLabel1) ?>" placeholder="<?= htmlspecialchars($globalPriceLabel1) ?> (Default)">
                                <small class="text-muted">Default: "<?= htmlspecialchars($globalPriceLabel1) ?>"</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="price_label_2" class="form-label small fw-semibold">Price 2 Title / Name</label>
                                <input type="text" class="form-control price-label-input" id="price_label_2" name="price_label_2" value="<?= htmlspecialchars($itemPriceLabel2) ?>" placeholder="<?= htmlspecialchars($globalPriceLabel2) ?> (Default)">
                                <small class="text-muted">Default: "<?= htmlspecialchars($globalPriceLabel2) ?>"</small>
                            </div>
                            <div class="col-12 mt-2 d-flex align-items-center flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-primary" id="btnQuickSaveLabels" onclick="quickSaveProductLabels()">
                                    <i class="fas fa-check me-1"></i>Save Price Titles Now
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetProductLabelsToDefault()">
                                    <i class="fas fa-undo me-1"></i>Reset to Default (Zone 1 & Zone 2)
                                </button>
                                <span id="quickSaveLabelsMsg" class="small fw-semibold ms-2" style="display:none;"></span>
                            </div>
                        </div>

                        <!-- Simple Product Prices -->
                        <div class="row simple-field" style="<?= $item['variant_type'] === 'none' ? '' : 'display:none;' ?>">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">
                                    <span class="price-title-1 fw-bold text-dark"><?= htmlspecialchars($priceLabel1) ?></span> (₹) <span class="text-danger">*</span>
                                    <i class="fas fa-pencil-alt text-primary ms-1" style="font-size: 0.75rem; cursor: pointer;" onclick="openLabelEdit(1)" title="Click to rename"></i>
                                </label>
                                <input type="text" inputmode="numeric" class="form-control" id="price" name="price" value="<?= $item['price'] ?? '' ?>" placeholder="e.g., 1500 (<?= htmlspecialchars($priceLabel1) ?>)">
                            </div>
                            <div class="col-md-6 mb-3 dual-price-field">
                                <label for="price_zone2" class="form-label">
                                    <span class="price-title-2 fw-bold text-dark"><?= htmlspecialchars($priceLabel2) ?></span> (₹) <span class="text-muted small fw-normal">(Optional)</span>
                                    <i class="fas fa-pencil-alt text-primary ms-1" style="font-size: 0.75rem; cursor: pointer;" onclick="openLabelEdit(2)" title="Click to rename"></i>
                                </label>
                                <input type="text" inputmode="numeric" class="form-control" id="price_zone2" name="price_zone2" value="<?= $item['price_zone2'] ?? '' ?>" placeholder="e.g., 1500 (<?= htmlspecialchars($priceLabel2) ?>)">
                                <small class="text-muted">Leave empty if this product does not have a second price.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 dimensions-field" style="<?= $item['variant_type'] !== 'size' ? '' : 'display:none;' ?>">
                                <label for="dimensions" class="form-label">Dimensions / Size</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?= htmlspecialchars($item['dimensions'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $item['display_order'] ?>" min="1">
                            </div>
                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_new_arrival" name="is_new_arrival" value="1" <?= !empty($item['is_new_arrival']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_new_arrival">
                                        <i class="fas fa-star text-warning me-1"></i>New Arrival
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Variant Tables -->
                        <?php 
                        // Fetch Variants
                        $variants = fetchAll("SELECT * FROM catalogue_product_variants WHERE product_id = ? ORDER BY display_order ASC", [$id]);
                        ?>

                        <!-- Size Variants Table -->
                        <div id="size_variants_section" class="mb-4" style="<?= $item['variant_type'] === 'size' ? '' : 'display:none;' ?>">
                            <h6 class="border-bottom pb-2">Size Variants</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="size_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="20%">Name (Opt)</th>
                                            <th width="15%">Code <span class="text-danger">*</span></th>
                                            <th width="15%">Size <span class="text-danger">*</span></th>
                                            <th width="10%"><span class="price-title-1"><?= htmlspecialchars($priceLabel1) ?></span> <span class="text-danger">*</span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(1)" title="Click to rename"></i></th>
                                            <th width="10%" class="dual-price-col"><span class="price-title-2"><?= htmlspecialchars($priceLabel2) ?></span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(2)" title="Click to rename"></i></th>
                                            <th width="35%">Image</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($item['variant_type'] === 'size'): ?>
                                            <?php foreach ($variants as $idx => $v): ?>
                                            <tr>
                                                <input type="hidden" name="variants[size][<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                                                <td><input type="text" name="variants[size][<?= $idx ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['name']) ?>"></td>
                                                <td><input type="text" name="variants[size][<?= $idx ?>][code]" class="form-control form-control-sm" required value="<?= htmlspecialchars(dashCode($v['code'])) ?>"></td>
                                                <td><input type="text" name="variants[size][<?= $idx ?>][value]" class="form-control form-control-sm" required value="<?= htmlspecialchars($v['attribute_value']) ?>"></td>
                                                <td><input type="text" inputmode="numeric" name="variants[size][<?= $idx ?>][price]" class="form-control form-control-sm" required value="<?= $v['price'] ?>" placeholder="<?= htmlspecialchars($priceLabel1) ?>"></td>
                                                <td class="dual-price-col">
                                                    <input type="text" inputmode="numeric" name="variants[size][<?= $idx ?>][price_zone2]" class="form-control form-control-sm dual-price-input" value="<?= $v['price_zone2'] ?? '' ?>" placeholder="<?= htmlspecialchars($priceLabel2) ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2 bg-white border d-flex align-items-center justify-content-center" 
                                                             style="width: 38px; height: 38px; min-width: 38px; cursor: pointer;"
                                                             <?php if (!empty($v['image_url'])): ?>
                                                             onclick="window.open('../../../uploads/catalogue/products/<?= $v['image_url'] ?>', '_blank')"
                                                             title="View Image"
                                                             <?php endif; ?>
                                                        >
                                                            <?php if (!empty($v['image_url'])): ?>
                                                                <img src="../../../uploads/catalogue/products/<?= $v['image_url'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <?php else: ?>
                                                                <span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-grow-1 d-flex">
                                                            <input type="file" name="variant_images_size_<?= $idx ?>" class="form-control form-control-sm me-2" accept="image/*">
                                                            <input type="hidden" name="variants[size][<?= $idx ?>][delete_image]" class="delete-image-flag" value="0">
                                                            <button type="button" 
                                                                    class="btn btn-sm text-danger border-0 p-1 ms-1 remove-variant-image-btn" 
                                                                    title="Remove Image" 
                                                                    <?= empty($v['image_url']) ? 'disabled' : '' ?>>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-variant-btn" data-id="<?= $v['id'] ?>"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="addSizeRow()">
                                    <i class="fas fa-plus me-1"></i> Add Size Variant
                                </button>
                            </div>
                        </div>

                        <!-- Color Variants Table -->
                        <div id="color_variants_section" class="mb-4" style="<?= $item['variant_type'] === 'color' ? '' : 'display:none;' ?>">
                            <h6 class="border-bottom pb-2">Color Variants</h6>

                            <!-- Colour / Finish Label Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Colour Variant Label</label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colour_label" id="label_colour" value="colour"
                                            <?= ($item['colour_label'] ?? 'colour') === 'colour' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="label_colour">
                                            <i class="fas fa-palette me-1 text-primary"></i> Colour <span class="text-muted small">(default)</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="colour_label" id="label_finish" value="finish"
                                            <?= ($item['colour_label'] ?? 'colour') === 'finish' ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="label_finish">
                                            <i class="fas fa-brush me-1 text-warning"></i> Finish
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">Label shown in the mobile app (e.g., "Colour: Ivory White" or "Finish: Blue Boy").</small>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered" id="color_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th>Name (Opt)</th>
                                            <th width="120">Code <span class="text-danger">*</span></th>
                                            <th width="400">Color <span class="text-danger">*</span></th>
                                            <th width="80">Preview</th>
                                            <th width="100"><span class="price-title-1"><?= htmlspecialchars($priceLabel1) ?></span> <span class="text-danger">*</span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(1)" title="Click to rename"></i></th>
                                            <th width="100" class="dual-price-col"><span class="price-title-2"><?= htmlspecialchars($priceLabel2) ?></span> <i class="fas fa-pencil-alt text-primary ms-1" style="cursor:pointer; font-size:0.75rem;" onclick="openLabelEdit(2)" title="Click to rename"></i></th>
                                            <th>Image</th>
                                            <th width="50"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($item['variant_type'] === 'color'): ?>
                                            <?php foreach ($variants as $idx => $v): ?>
                                            <tr>
                                                <input type="hidden" name="variants[color][<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                                                <td><input type="text" name="variants[color][<?= $idx ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['name']) ?>" placeholder="Variant Name"></td>
                                                <td><input type="text" name="variants[color][<?= $idx ?>][code]" class="form-control form-control-sm" required value="<?= htmlspecialchars(dashCode($v['code'])) ?>" placeholder="Product Code"></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <select name="variants[color][<?= $idx ?>][color_id]" class="form-select form-select-sm" style="width: 50%;" required onchange="onColorSelect(this, <?= $idx ?>)">
                                                            <option value="">-- Select --</option>
                                                            <?php foreach ($colors as $c): ?>
                                                                <option value="<?= $c['id'] ?>" 
                                                                        data-name="<?= htmlspecialchars($c['name']) ?>"
                                                                        data-type="<?= $c['type'] ?>"
                                                                        data-hex="<?= $c['hex_code'] ?>"
                                                                        data-texture="<?= $c['texture_image'] ?>"
                                                                        <?= ($v['color_id'] == $c['id']) ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($c['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <input type="text" name="variants[color][<?= $idx ?>][value]" class="form-control form-control-sm" style="width: 50%;" required value="<?= htmlspecialchars($v['attribute_value']) ?>" placeholder="Display Name" id="color_name_<?= $idx ?>">
                                                    </div>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <?php 
                                                        // Determine initial preview style
                                                        $previewStyle = 'background: #f8f9fa;';
                                                        // Find the color object
                                                        $selectedColor = null;
                                                        foreach($colors as $ci) { if($ci['id'] == $v['color_id']) { $selectedColor = $ci; break; } }
                                                        
                                                        if ($selectedColor) {
                                                            if ($selectedColor['type'] === 'solid') {
                                                                $previewStyle = "background: {$selectedColor['hex_code']};";
                                                            } elseif ($selectedColor['type'] === 'texture' && $selectedColor['texture_image']) {
                                                                $previewStyle = "background: url('" . BASE_URL . "/uploads/catalogue/colors/{$selectedColor['texture_image']}') center/cover no-repeat;";
                                                            }
                                                        }
                                                    ?>
                                                    <div id="color_preview_<?= $idx ?>" class="border rounded d-inline-block" style="width: 30px; height: 30px; <?= $previewStyle ?>" title="<?= htmlspecialchars($v['attribute_value']) ?>"></div>
                                                </td>
                                                <td><input type="text" inputmode="numeric" name="variants[color][<?= $idx ?>][price]" class="form-control form-control-sm" required value="<?= $v['price'] ?>" placeholder="<?= htmlspecialchars($priceLabel1) ?>"></td>
                                                <td class="dual-price-col">
                                                    <input type="text" inputmode="numeric" name="variants[color][<?= $idx ?>][price_zone2]" class="form-control form-control-sm dual-price-input" value="<?= $v['price_zone2'] ?? '' ?>" placeholder="<?= htmlspecialchars($priceLabel2) ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2 bg-white border d-flex align-items-center justify-content-center" 
                                                             style="width: 38px; height: 38px; min-width: 38px; cursor: pointer;"
                                                             <?php if (!empty($v['image_url'])): ?>
                                                             onclick="window.open('<?= BASE_URL ?>/uploads/catalogue/products/<?= $v['image_url'] ?>', '_blank')"
                                                             title="View Image"
                                                             <?php endif; ?>
                                                        >
                                                            <?php if (!empty($v['image_url'])): ?>
                                                                <img src="<?= BASE_URL ?>/uploads/catalogue/products/<?= $v['image_url'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                            <?php else: ?>
                                                                <span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="flex-grow-1 d-flex">
                                                            <input type="file" name="variant_images_color_<?= $idx ?>" class="form-control form-control-sm me-2" accept="image/*">
                                                            <input type="hidden" name="variants[color][<?= $idx ?>][delete_image]" class="delete-image-flag" value="0">
                                                            <button type="button" 
                                                                    class="btn btn-sm text-danger border-0 p-1 ms-1 remove-variant-image-btn" 
                                                                    title="Remove Image" 
                                                                    <?= empty($v['image_url']) ? 'disabled' : '' ?>>
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger delete-variant-btn" data-id="<?= $v['id'] ?>"><i class="fas fa-trash"></i></button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="addColorRow()">
                                    <i class="fas fa-plus me-1"></i> Add Color Variant
                                </button>
                            </div>
                        </div>

                        <!-- Container for deleted variants -->
                        <div id="deleted_variants_container"></div>

                        <!-- Product Features -->
                        <div class="mb-4">
                            <label class="form-label">Product Features <span class="text-muted small">(Optional)</span></label>
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <?php if(empty($features)): ?>
                                            <div class="col-12 text-muted small">No features found in the library. <a href="../features/create.php">Create some first</a>.</div>
                                        <?php else: ?>
                                            <?php foreach ($features as $f): 
                                                $isChecked = in_array($f['id'], $selected_feature_ids) ? 'checked' : '';
                                            ?>
                                            <div class="col-md-3 col-sm-4 col-6">
                                                <div class="form-check custom-checkbox-card p-2 border rounded bg-white h-100 d-flex align-items-center">
                                                    <input class="form-check-input ms-1 me-2 mt-0" type="checkbox" name="feature_ids[]" value="<?= $f['id'] ?>" id="feature_<?= $f['id'] ?>" <?= $isChecked ?>>
                                                    <label class="form-check-label w-100 d-flex align-items-center mb-0" for="feature_<?= $f['id'] ?>" style="cursor: pointer;">
                                                        <?php if($f['icon_url']): ?>
                                                            <img src="../../../uploads/catalogue/features/<?= htmlspecialchars($f['icon_url']) ?>" alt="icon" style="width: 24px; height: 24px; object-fit: contain;" class="me-2">
                                                        <?php else: ?>
                                                            <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center text-white" style="width: 24px; height: 24px; font-size: 10px;">
                                                                <i class="fas fa-star"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <span class="small fw-medium text-truncate" title="<?= htmlspecialchars($f['name']) ?>"><?= htmlspecialchars($f['name']) ?></span>
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
                            <div id="specs-editor" style="height: 200px;"><?= $item['specifications'] ?></div>
                            <input type="hidden" id="specifications" name="specifications">
                        </div>
                        
                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $item['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Active (Visible)</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>
            
            <div class="row">
            <!-- Gallery Side Panel (Now Full Width Below) -->
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-images me-2 text-primary"></i>Image Gallery</h6>
                        <span class="badge bg-primary rounded-pill" id="gallery_count_badge"><?= count($images) ?></span>
                    </div>
                    <div class="card-body">
                        
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

                        <!-- Newly Selected Images Preview Container -->
                        <div id="new_images_container" class="mt-3 mb-3 p-3 bg-light rounded border border-primary border-opacity-25" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="text-primary mb-0 small fw-bold">
                                    <i class="fas fa-images me-1"></i> Newly Selected Images (Preview) <span class="badge bg-primary ms-1" id="new_images_count">0</span>
                                </h6>
                                <span class="text-muted small" style="font-size: 11px;">
                                    <i class="fas fa-grip-vertical me-1"></i>Drag to reorder &bull; Set any image as Primary
                                </span>
                            </div>
                            <div id="new_images_list"></div>
                        </div>
                        
                        <hr>

                        <!-- Container for deleted image IDs & image ordering -->
                        <div id="deleted_images_container"></div>
                        <div id="image_order_container"></div>

                        <!-- Existing Images List -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-muted small fw-bold mb-0">
                                <i class="fas fa-folder-open me-1"></i> Current Uploaded Images
                            </h6>
                            <span class="text-muted small" style="font-size: 11px;">
                                <i class="fas fa-grip-vertical me-1"></i>Drag <i class="fas fa-grip-vertical"></i> handle to reorder images
                            </span>
                        </div>
                        <div class="image-list existing-images-list" id="existing_images_sortable" style="max-height: 500px; overflow-y: auto;">
                            <?php if (empty($images)): ?>
                                <p class="text-center text-muted my-3 no-images-text">No images uploaded.</p>
                            <?php else: ?>
                                <?php foreach ($images as $img): ?>
                                    <div class="d-flex align-items-center border-bottom py-2 image-row existing-image-row" data-id="<?= $img['id'] ?>">
                                        <div class="me-2 text-muted drag-handle px-1" title="Drag to reorder">
                                            <i class="fas fa-grip-vertical fa-lg"></i>
                                        </div>
                                        <div class="me-2">
                                            <img src="../../../uploads/catalogue/products/<?= htmlspecialchars($img['image_url']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                                 onclick="window.open('../../../uploads/catalogue/products/<?= htmlspecialchars($img['image_url']) ?>', '_blank')"
                                                 title="Click to view full image">
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="form-check">
                                                <input class="form-check-input primary-radio" type="radio" name="primary_image" id="primary_<?= $img['id'] ?>" value="<?= $img['id'] ?>" <?= $img['is_primary'] ? 'checked' : '' ?>>
                                                <label class="form-check-label small fw-semibold" for="primary_<?= $img['id'] ?>" style="cursor: pointer;">
                                                    Primary Image
                                                </label>
                                            </div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-image-btn" title="Delete Image">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <!-- Product Video -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Product Video</h6>
                    </div>
                    <div class="card-body">
                        <?php 
                        $isExternalVideo = !empty($item['video_url']) && preg_match('#^https?://#i', $item['video_url']);
                        $isYoutube = !empty($item['video_url']) && preg_match('#(youtube\.com|youtu\.be|vimeo\.com)#i', $item['video_url']);
                        $videoSrc = $isExternalVideo ? $item['video_url'] : BASE_URL . '/uploads/catalogue/products/' . ($item['video_url'] ?? '');
                        
                        if (!empty($item['video_url'])): 
                        ?>
                            <div class="mb-4">
                                <label class="form-label d-block fw-semibold">Current Video</label>
                                <div class="position-relative d-inline-block border rounded p-2 bg-light">
                                    <?php if ($isYoutube): ?>
                                        <div class="text-center p-3 bg-white border rounded mb-2 shadow-sm" style="width: 280px;">
                                            <i class="fab fa-youtube text-danger fa-3x mb-2"></i>
                                            <div class="small fw-semibold text-truncate mb-2" style="max-width: 250px;" title="<?= htmlspecialchars($item['video_url']) ?>">
                                                <?= htmlspecialchars($item['video_url']) ?>
                                            </div>
                                            <a href="<?= htmlspecialchars($item['video_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                Open Video Link <i class="fas fa-external-link-alt ms-1"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <video width="280" height="158" controls class="rounded bg-black">
                                            <source src="<?= htmlspecialchars($videoSrc) ?>" type="video/<?= pathinfo($item['video_url'], PATHINFO_EXTENSION) ?: 'mp4' ?>">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php endif; ?>
                                    <div class="mt-2 text-center">
                                        <span class="badge bg-secondary mb-2"><?= $isExternalVideo ? 'External Link' : 'Uploaded File' ?></span><br>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" id="delete_video" name="delete_video" value="1">
                                            <label class="form-check-label text-danger small fw-semibold" for="delete_video">
                                                <i class="fas fa-trash-alt me-1"></i>Delete Current Video
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="video" class="form-label fw-semibold">Option A: Replace with Video File</label>
                                <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm,video/ogg,video/quicktime">
                                <div class="form-text">Max size: 10MB. Supported formats: MP4, WebM, Ogg, MOV.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="video_url_link" class="form-label fw-semibold">Option B: Or Enter External Video URL</label>
                                <input type="url" class="form-control" id="video_url_link" name="video_url_link" placeholder="e.g., https://www.youtube.com/watch?v=..." value="<?= $isExternalVideo ? htmlspecialchars($item['video_url']) : '' ?>">
                                <div class="form-text">Supports direct video URLs, YouTube, or Vimeo links.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Update Product <span class="badge bg-white text-primary ms-1 small fw-normal" style="font-size:0.75rem;">Shift + Enter</span>
                    </button>
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
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
                <h6 class="fw-bold mb-2 text-dark" id="deleteModalTitle">Are you sure you want to delete this image?</h6>
                <p class="text-muted small mb-0" id="deleteModalMessage">
                    This image will be permanently removed when you update the product.
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
$(document).ready(function() {
    var originalVariantType = '<?= $item['variant_type'] ?>';

    // 1. Variant Type Logic
    $('#variant_type').change(function () {
        var type = $(this).val();

        if (originalVariantType !== 'none' && type !== originalVariantType) {
            $('#variant_type_warning').show();
        } else {
            $('#variant_type_warning').hide();
        }

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

        // Helper for simple fields
        function toggleSimpleFields(show) {
            var fields = $('.simple-field');
            if (show) {
                fields.not('.dual-price-field').show();
                fields.find('input, select, textarea').prop('disabled', false);
                if (window.dualZoneEnabled) {
                    $('.dual-price-field').show();
                } else {
                    $('.dual-price-field').hide();
                }
            } else {
                fields.hide();
                fields.find('input, select, textarea').prop('disabled', true);
            }
        }

        if (type === 'none') {
            toggleSimpleFields(true);
            $('#code_required_star').show();
            $('#code_variant_hint').hide();
            $('#code').prop('required', true);
            $('.dimensions-field').show();
            toggleSection('#size_variants_section', false);
            toggleSection('#color_variants_section', false);
            $('#name_variant_hint').hide();
        } else if (type === 'size') {
            toggleSimpleFields(false);
            $('#code_required_star').hide();
            $('#code_variant_hint').show();
            $('#code').prop('required', false);
            $('.dimensions-field').hide();
            toggleSection('#size_variants_section', true);
            toggleSection('#color_variants_section', false);
            $('#name_variant_hint').show();
            toggleDualZonePricing(window.dualZoneEnabled);
            if ($('#size_table tbody tr').length === 0) addSizeRow();
        } else if (type === 'color') {
            toggleSimpleFields(false);
            $('#code_required_star').hide();
            $('#code_variant_hint').show();
            $('#code').prop('required', false);
            $('.dimensions-field').show();
            toggleSection('#size_variants_section', false);
            toggleSection('#color_variants_section', true);
            $('#name_variant_hint').show();
            toggleDualZonePricing(window.dualZoneEnabled);
            if ($('#color_table tbody tr').length === 0) addColorRow();
        }
    });

    // Initialize state on load
    $('#variant_type').trigger('change');

    var initialCategoryId = $('#initial_category_id').val();
    var initialSubCategoryId = $('#initial_sub_category_id').val();
    var initialSeriesId = $('#initial_series_id').val();

    // Select2 Init & Helpers
    var select2Opts = { theme: 'bootstrap-5', allowClear: true, width: '100%' };
    $('#category_id').select2($.extend({}, select2Opts, { placeholder: '-- Select --' }));

    function reinitSelect2(selector, placeholder) {
        if ($(selector).hasClass('select2-hidden-accessible')) $(selector).select2('destroy');
        $(selector).select2($.extend({}, select2Opts, { placeholder: placeholder }));
    }

    // Re-use logic from create.php but with pre-population capability
    
    function fetchSubCategories(categoryId, selectedId = null) {
        var subCatSelect = $('#sub_category_id');
        if (!categoryId) {
            subCatSelect.html('<option value="">-- Select Main Category First --</option>');
            return;
        }

        $.ajax({
            url: '../../catalogue/ajax/get_sub_categories.php',
            type: 'GET',
            data: { category_id: categoryId },
            dataType: 'json',
            success: function(response) {
                subCatSelect.empty();
                subCatSelect.append('<option value="">-- None (Direct / 3-Tier) --</option>');
                
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        var isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        subCatSelect.append('<option value="' + item.id + '" ' + isSelected + '>' + item.name + '</option>');
                    });
                }
                    reinitSelect2('#sub_category_id', '-- None (Direct / 3-Tier) --');
                // Determine if we should trigger series fetch
                // If we are initializing, we call fetchSeries manually
                // If user changed category, we might want to reset series
            },
            error: function() {
                console.error('Failed to fetch sub categories');
            }
        });
    }

    function fetchSeries(categoryId, subCategoryId, selectedId = null) {
        var seriesSelect = $('#series_id');
        
        seriesSelect.html('<option value="">-- Loading... --</option>');

        $.ajax({
            url: '../../catalogue/ajax/get_series.php',
            type: 'GET',
            data: { 
                category_id: categoryId,
                sub_category_id: subCategoryId // Pass empty string or ID
            },
            dataType: 'json',
            success: function(response) {
                seriesSelect.empty();
                seriesSelect.append('<option value="">-- Direct to Main Category (No Series) --</option>');
                
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        var isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        seriesSelect.append('<option value="' + item.id + '" ' + isSelected + '>' + item.name + '</option>');
                    });
                }
                reinitSelect2('#series_id', '-- Direct to Main Category (No Series) --');
            },
            error: function() {
                console.error('Failed to fetch series');
                seriesSelect.empty().append('<option value="">-- Direct to Main Category (No Series) --</option>');
                reinitSelect2('#series_id', '-- Direct to Main Category (No Series) --');
            }
        });
    }

    // Initialization
    if (initialCategoryId) {
        fetchSubCategories(initialCategoryId, initialSubCategoryId);
        fetchSeries(initialCategoryId, initialSubCategoryId, initialSeriesId);
    }
    
    // Initial Dual Price Check - enabled by default
    var initialDualPrice = true;
    toggleDualZonePricing(true);

    // Event Handlers
    $('#category_id').change(function() {
        var catId = $(this).val();
        
        // Dual Zone Logic - always enabled
        toggleDualZonePricing(true);

        // Reset subcat and series
        fetchSubCategories(catId, null);
        fetchSeries(catId, '', null);
    });

    $('#sub_category_id').change(function() {
        var catId = $('#category_id').val();
        var subCatId = $(this).val();
        fetchSeries(catId, subCatId, null);
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

    function updateGalleryBadge() {
        var existingCount = $('.existing-image-row').length;
        var newCount = newFilesDataTransfer.files.length;
        $('#gallery_count_badge').text(existingCount + newCount);
    }

    function updateImageOrderInputs() {
        var container = $('#image_order_container');
        container.empty();
        $('.existing-image-row').each(function(idx) {
            var imgId = $(this).data('id');
            container.append('<input type="hidden" name="image_order[]" value="' + imgId + '">');
        });
    }

    function ensureAtLeastOnePrimary() {
        if ($('input[name="primary_image"]:checked').length === 0) {
            var firstExisting = $('.existing-image-row .primary-radio').first();
            if (firstExisting.length > 0) {
                firstExisting.prop('checked', true);
            } else {
                var firstNew = $('.new-image-row .primary-radio').first();
                if (firstNew.length > 0) {
                    firstNew.prop('checked', true);
                }
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
            updateGalleryBadge();
            ensureAtLeastOnePrimary();
            return;
        }

        container.show();
        countBadge.text(files.length);

        var currentPrimaryVal = savedPrimaryVal !== null ? savedPrimaryVal : $('input[name="primary_image"]:checked').val();
        var hasExistingPrimary = $('.existing-image-row .primary-radio:checked').length > 0;

        files.forEach(function(file, idx) {
            var blobUrl = URL.createObjectURL(file);
            var sizeInKb = (file.size / 1024).toFixed(1) + ' KB';
            var val = 'new_' + idx;

            var isChecked = false;
            if (currentPrimaryVal === val) {
                isChecked = true;
            } else if (!hasExistingPrimary && currentPrimaryVal === undefined && idx === 0) {
                isChecked = true;
            }

            var rowHtml = `
                <div class="d-flex align-items-center border-bottom py-2 new-image-row" data-index="${idx}">
                    <div class="me-2 text-muted drag-handle px-1" title="Drag to reorder">
                        <i class="fas fa-grip-vertical fa-lg"></i>
                    </div>
                    <div class="me-2 position-relative">
                        <img src="${blobUrl}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" onclick="window.open('${blobUrl}', '_blank')" title="Click to view full preview">
                        <span class="badge bg-primary position-absolute top-0 start-0" style="font-size: 8px; transform: translate(-15%, -15%);">NEW</span>
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

        updateGalleryBadge();
        ensureAtLeastOnePrimary();
    }

    // Process selected files from drop or file dialog
    function processSelectedFiles(filesList) {
        var selectedFiles = Array.from(filesList);
        if (!selectedFiles.length) return;

        var existingImages = $('.existing-image-row').length;
        var currentNewImages = newFilesDataTransfer.files.length;

        var validFiles = [];
        var duplicateCount = 0;
        var nonImageCount = 0;

        selectedFiles.forEach(function(file) {
            if (!file.type.match('image.*')) {
                nonImageCount++;
                return;
            }
            var alreadyExists = Array.from(newFilesDataTransfer.files).some(function(f) {
                return f.name === file.name && f.size === file.size && f.lastModified === file.lastModified;
            });
            if (alreadyExists) {
                duplicateCount++;
                return;
            }
            validFiles.push(file);
        });

        if (nonImageCount > 0) {
            showGalleryAlert(nonImageCount + ' file(s) were skipped because they are not valid images.', 'warning');
        }

        if (existingImages + currentNewImages + validFiles.length > 5) {
            var allowed = Math.max(0, 5 - existingImages - currentNewImages);
            showGalleryAlert("You can have a maximum of 5 images in total (1 primary + 4 gallery images). You can add at most " + allowed + " more image(s).", 'warning');
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

    // Existing Image Deletion Logic (Using Styled Modal)
    $(document).on('click', '.delete-image-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var row = $(this).closest('.image-row');
        var imgId = row.data('id');
        var isPrimary = row.find('.primary-radio').is(':checked');
        var imgSrc = row.find('img').attr('src');

        var totalRemaining = ($('.existing-image-row').length - 1) + newFilesDataTransfer.files.length;
        if (totalRemaining === 0) {
            showGalleryAlert('A product must have at least one image. Please add or select a new image before deleting this one.', 'danger');
            return;
        }

        $('#deleteModalImgPreview').attr('src', imgSrc).show();
        $('#deleteModalImgWrapper').show();
        $('#deleteModalTitle').text('Are you sure you want to delete this image?');
        $('#deleteModalMessage').text('This image will be removed from the gallery when you update the product.');

        pendingDeleteAction = function() {
            $('#deleted_images_container').append('<input type="hidden" name="delete_images[]" value="' + imgId + '">');
            row.remove();
            
            if (isPrimary) {
                ensureAtLeastOnePrimary();
            }

            if ($('.existing-image-row').length === 0) {
                $('.existing-images-list').html('<p class="text-center text-muted my-3 no-images-text">No existing images.</p>');
            }

            updateGalleryBadge();
            updateImageOrderInputs();
        };

        var modal = getDeleteModalInstance();
        if (modal) modal.show();
    });

    // Remove single newly selected file (Using Styled Modal)
    $(document).on('click', '.remove-new-image-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var removeIndex = parseInt($(this).data('index'), 10);
        var row = $(this).closest('.new-image-row');
        var imgSrc = row.find('img').attr('src');

        var totalRemaining = $('.existing-image-row').length + (newFilesDataTransfer.files.length - 1);
        if (totalRemaining === 0) {
            showGalleryAlert('A product must have at least one image. Please add or select another image before removing this one.', 'danger');
            return;
        }

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

    // Keydown on Delete Modal: Enter key confirms deletion only inside the modal
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

    // Initialize SortableJS on Existing Images and Preview Images
    if (typeof Sortable !== 'undefined') {
        var existingListEl = document.getElementById('existing_images_sortable');
        if (existingListEl) {
            Sortable.create(existingListEl, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                onEnd: function() {
                    updateImageOrderInputs();
                }
            });
        }

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

    updateImageOrderInputs();
    ensureAtLeastOnePrimary();

    // Form Submission Validation + Quill Sync
    $('form').submit(function(e) {
        // Sync Quill content to hidden input
        if (typeof specsQuill !== 'undefined') {
            var specsHtml = specsQuill.root.innerHTML;
            if (specsHtml === '<p><br></p>') specsHtml = '';
            $('#specifications').val(specsHtml);
        }

        var vType = $('#variant_type').val();
        if (vType === 'size' && $('#size_table tbody tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one Size Variant.');
            return false;
        } else if (vType === 'color' && $('#color_table tbody tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one Color Variant.');
            return false;
        }

        var existingImages = $('.existing-image-row').length;
        var newImages = newFilesDataTransfer.files.length;
        
        if (existingImages + newImages === 0) {
            e.preventDefault();
            showGalleryAlert('At least one product image is required.', 'danger');
            return false;
        }

        if (existingImages + newImages > 5) {
            e.preventDefault();
            showGalleryAlert("You can have a maximum of 5 images in total (1 primary + 4 gallery images). Please remove some existing images or select fewer files to upload.", 'danger');
            return false;
        }

        // Sync DataTransfer files to input and ensure primary is marked
        $('#images')[0].files = newFilesDataTransfer.files;
        ensureAtLeastOnePrimary();
        updateImageOrderInputs();
    });
});
// Helper Variables
let sizeIndex = <?= ($item['variant_type'] === 'size' ? count($variants) : 0) ?>;
let colorIndex = <?= ($item['variant_type'] === 'color' ? count($variants) : 0) ?>;
let deletedVariantIds = [];
const availableColors = <?= json_encode($colors) ?>;
window.dualZoneEnabled = true;

// Dual Zone Helper
function toggleDualZonePricing(enable) {
    window.dualZoneEnabled = true;
    var isSimple = $('#variant_type').val() === 'none';
    if (isSimple) {
        $('.dual-price-field').show();
    }
    $('.dual-price-col').show();
    $('.dual-price-input').show();
}

function addSizeRow() {
    let displayStyle = '';
    
    const curLabel1 = $('#price_label_1').val().trim() || defaultLabel1;
    const curLabel2 = $('#price_label_2').val().trim() || defaultLabel2;
    
    let html = `
        <tr>
            <input type="hidden" name="variants[size][${sizeIndex}][id]" value="">
            <td><input type="text" name="variants[size][${sizeIndex}][name]" class="form-control form-control-sm" placeholder="Variant Name"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][code]" class="form-control form-control-sm" required placeholder="Product Code"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][value]" class="form-control form-control-sm" required placeholder="Size (e.g., 25MM)"></td>
            <td><input type="text" inputmode="numeric" name="variants[size][${sizeIndex}][price]" class="form-control form-control-sm" required placeholder="${curLabel1}"></td>
            <td class="dual-price-col" style="${displayStyle}">
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

    let displayStyle = '';
    const curLabel1 = $('#price_label_1').val().trim() || defaultLabel1;
    const curLabel2 = $('#price_label_2').val().trim() || defaultLabel2;

    let html = `
        <tr>
            <input type="hidden" name="variants[color][${colorIndex}][id]" value="">
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
            <td class="dual-price-col" style="${displayStyle}">
                <input type="text" inputmode="numeric" name="variants[color][${colorIndex}][price_zone2]" class="form-control form-control-sm dual-price-input" placeholder="${curLabel2}">
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="me-2 bg-white border d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; min-width: 38px;">
                        <span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>
                    </div>
                    <div class="flex-grow-1 d-flex">
                        <input type="file" name="variant_images_color_${colorIndex}" class="form-control form-control-sm me-2" accept="image/*">
                        <input type="hidden" name="variants[color][${colorIndex}][delete_image]" class="delete-image-flag" value="0">
                        <button type="button" 
                                class="btn btn-sm text-danger border-0 p-1 ms-1 remove-variant-image-btn" 
                                title="Remove Image" 
                                disabled>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="$(this).closest('tr').remove()"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
    `;
    $('#color_table tbody').append(html);
    colorIndex++;
}

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
    if(nameInput.value === '') {
         nameInput.value = name;
    }
}

$(document).on('click', '.delete-variant-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var btn = $(this);
    var row = btn.closest('tr');
    var id = btn.data('id');

    $('#deleteModalImgWrapper').hide();
    $('#deleteModalTitle').text('Delete this variant?');
    $('#deleteModalMessage').text('This variant will be removed when you update the product.');

    pendingDeleteAction = function() {
        if (id) {
            $('#deleted_variants_container').append('<input type="hidden" name="delete_variants[]" value="' + id + '">');
        }
        row.remove();
    };

    deleteModalInstance.show();
});

// Remove Variant Image Logic
$(document).on('click', '.remove-variant-image-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var btn = $(this);
    var container = btn.closest('.d-flex.align-items-center');
    var thumbnailDiv = container.find('.bg-white');
    var hiddenInput = btn.siblings('.delete-image-flag');
    var fileInput = btn.siblings('input[type="file"]');
    var currentImg = thumbnailDiv.find('img').attr('src');

    if (currentImg) {
        $('#deleteModalImgPreview').attr('src', currentImg);
        $('#deleteModalImgWrapper').show();
    } else {
        $('#deleteModalImgWrapper').hide();
    }
    $('#deleteModalTitle').text('Remove variant image?');
    $('#deleteModalMessage').text('This image will be removed from this variant.');

    pendingDeleteAction = function() {
        hiddenInput.val('1');
        fileInput.val('');
        thumbnailDiv.html('<span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>');
        thumbnailDiv.prop('title', '');
        thumbnailDiv.removeAttr('onclick');
        thumbnailDiv.css('cursor', 'default');
        btn.prop('disabled', true);
    };

    deleteModalInstance.show();
});

// -- Quill Rich Text Editor --
var specsQuill = new Quill('#specs-editor', {
    theme: 'snow',
    modules: {
        toolbar: [
            ['bold', 'italic', 'underline'],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['clean']
        ]
    },
    placeholder: 'Enter technical specifications...'
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

window.quickSaveProductLabels = function() {
    const l1 = $('#price_label_1').val().trim();
    const l2 = $('#price_label_2').val().trim();
    const btn = $('#btnQuickSaveLabels');
    const msg = $('#quickSaveLabelsMsg');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
    
    $.ajax({
        url: 'save_price_labels.php',
        method: 'POST',
        data: {
            is_ajax: 1,
            product_id: <?= (int)$item['id'] ?>,
            price_label_1: l1,
            price_label_2: l2
        },
        dataType: 'json',
        success: function(res) {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Save Price Titles Now');
            const saved1 = res.price_label_1 || 'Zone 1';
            const saved2 = res.price_label_2 || 'Zone 2';
            try {
                sessionStorage.removeItem('xtral_catalogue');
            } catch (e) {}
            msg.removeClass('text-danger').addClass('text-success').text('✓ Saved! (' + saved1 + ' & ' + saved2 + ')').fadeIn();
            updatePriceTitlesUI();
            setTimeout(function() { msg.fadeOut(); }, 3500);
        },
        error: function() {
            btn.prop('disabled', false).html('<i class="fas fa-check me-1"></i>Save Price Titles Now');
            msg.removeClass('text-success').addClass('text-danger').text('Error saving price titles.').fadeIn();
            setTimeout(function() { msg.fadeOut(); }, 3500);
        }
    });
};

window.resetProductLabelsToDefault = function() {
    $('#price_label_1').val('');
    $('#price_label_2').val('');
    updatePriceTitlesUI();
    quickSaveProductLabels();
};
</script>
