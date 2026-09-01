<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

// Fetch Product + Hierarchy Info
$sql = "SELECT p.*, s.category_id, s.sub_category_id 
        FROM catalogue_products p
        JOIN catalogue_series s ON p.series_id = s.id
        WHERE p.id = ?";
$item = fetchOne($sql, [$id]);

if (!$item) {
    $_SESSION['error'] = "Product not found.";
    header('Location: list.php');
    exit;
}

// Fetch Inputs
// Fetch Active Main Categories
// Fetch Active Main Categories
$main_categories = fetchAll("SELECT id, name, has_dual_price FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");

// Determine Dual Price State
$currentCategory = fetchOne("SELECT has_dual_price FROM catalogue_categories WHERE id = ?", [$item['category_id']]);
$hasDualPrice = $currentCategory['has_dual_price'] ?? 0;
$dualPriceDisplay = $hasDualPrice ? '' : 'display:none;';
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
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';
$additionalJS = '<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
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
                                <label for="series_id" class="form-label">Series <span class="text-danger">*</span></label>
                                <select class="form-select" id="series_id" name="series_id" required>
                                    <option value="">-- Loading... --</option>
                                </select>
                            </div>
                        </div>

                        <!-- Variant Type (Read-Only to prevent data loss complexity for now, or allow upgrade) -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Product Type</label>
                                <input type="hidden" name="variant_type" id="variant_type" value="<?= $item['variant_type'] ?>">
                                <div>
                                    <span class="badge bg-<?= $item['variant_type'] === 'none' ? 'secondary' : 'primary' ?> p-2">
                                        <?= $item['variant_type'] === 'none' ? 'Simple Product' : ucfirst($item['variant_type']) . ' Variants' ?>
                                    </span>
                                    <?php if ($item['variant_type'] === 'none'): ?>
                                        <small class="text-muted ms-2">To convert to variants, please create a new product (Feature to convert coming soon).</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Details -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                                <?php if ($item['variant_type'] !== 'none'): ?>
                                    <small class="text-muted">Group Title for Variants.</small>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($item['variant_type'] === 'none'): ?>
                            <div class="col-md-3 mb-3">
                                <label for="code" class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" value="<?= htmlspecialchars(dashCode($item['code'])) ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="price" class="form-label">Price (Zone 1) (₹)</label>
                                <input type="text" inputmode="numeric" class="form-control" id="price" name="price" value="<?= $item['price'] ?>" placeholder="e.g., 1500">
                            </div>
                            <!-- Zone 2 Price -->
                            <div class="col-md-3 mb-3 dual-price-field" style="<?= $dualPriceDisplay ?>">
                                <label for="price_zone2" class="form-label">Price (Zone 2) (₹)</label>
                                <input type="text" inputmode="numeric" class="form-control" id="price_zone2" name="price_zone2" value="<?= $item['price_zone2'] ?? '' ?>" placeholder="e.g., 1500">
                            </div>
                            <?php endif; ?>
                            <div class="col-md-3 mb-3">
                                <label for="hsn_code" class="form-label">HSN Code</label>
                                <input type="text" class="form-control" id="hsn_code" name="hsn_code"
                                    maxlength="15" value="<?= htmlspecialchars($item['hsn_code'] ?? '') ?>"
                                    placeholder="e.g., 6912">
                                <small class="text-muted">Used on tax invoices.</small>
                            </div>
                        </div>

                        <div class="row">
                            <?php if ($item['variant_type'] !== 'size'): ?>
                            <div class="col-md-6 mb-3">
                                <label for="dimensions" class="form-label">Dimensions / Size</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?= htmlspecialchars($item['dimensions'] ?? '') ?>">
                            </div>
                            <?php endif; ?>
                            <div class="col-md-3 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $item['display_order'] ?>">
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
                        <?php if ($item['variant_type'] === 'size'): ?>
                        <div class="mb-4">
                            <h6 class="border-bottom pb-2">Size Variants</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="size_table">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="20%">Name (Opt)</th>
                                            <th width="15%">Code <span class="text-danger">*</span></th>
                                            <th width="15%">Size <span class="text-danger">*</span></th>
                                            <th width="10%">Price <span class="text-danger">*</span></th>
                                            <th width="10%" class="dual-price-col" style="<?= $dualPriceDisplay ?>">Price (Z2)</th>
                                            <th width="35%">Image</th>
                                            <th width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($variants as $idx => $v): ?>
                                        <tr>
                                            <input type="hidden" name="variants[size][<?= $idx ?>][id]" value="<?= $v['id'] ?>">
                                            <td><input type="text" name="variants[size][<?= $idx ?>][name]" class="form-control form-control-sm" value="<?= htmlspecialchars($v['name']) ?>"></td>
                                            <td><input type="text" name="variants[size][<?= $idx ?>][code]" class="form-control form-control-sm" required value="<?= htmlspecialchars(dashCode($v['code'])) ?>"></td>
                                            <td><input type="text" name="variants[size][<?= $idx ?>][value]" class="form-control form-control-sm" required value="<?= htmlspecialchars($v['attribute_value']) ?>"></td>
                                            <td><input type="text" inputmode="numeric" name="variants[size][<?= $idx ?>][price]" class="form-control form-control-sm" required value="<?= $v['price'] ?>"></td>
                                            <td class="dual-price-col" style="<?= $dualPriceDisplay ?>">
                                                <input type="text" inputmode="numeric" name="variants[size][<?= $idx ?>][price_zone2]" class="form-control form-control-sm dual-price-input" value="<?= $v['price_zone2'] ?? '' ?>" placeholder="Price Z2">
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
                                    </tbody>
                                </table>
                                <button type="button" class="btn btn-sm btn-success" onclick="addSizeRow()">
                                    <i class="fas fa-plus me-1"></i> Add Size Variant
                                </button>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Color Variants Table -->
                        <?php if ($item['variant_type'] === 'color'): ?>
                        <div class="mb-4">
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
                                            <th width="100">Price <span class="text-danger">*</span></th>
                                            <th width="100" class="dual-price-col" style="<?= $dualPriceDisplay ?>">Price (Z2)</th>
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
                                                <td><input type="text" inputmode="numeric" name="variants[color][<?= $idx ?>][price]" class="form-control form-control-sm" required value="<?= $v['price'] ?>" placeholder="Price"></td>
                                            <td class="dual-price-col" style="<?= $dualPriceDisplay ?>">
                                                <input type="text" inputmode="numeric" name="variants[color][<?= $idx ?>][price_zone2]" class="form-control form-control-sm dual-price-input" value="<?= $v['price_zone2'] ?? '' ?>" placeholder="Price Z2">
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
                        <?php endif; ?>

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
                        <h6 class="mb-0">Image Gallery</h6>
                        <span class="badge bg-primary rounded-pill"><?= count($images) ?></span>
                    </div>
                    <div class="card-body">
                        
                        <!-- Add New Images -->
                        <div class="mb-3">
                            <label class="form-label">Add New Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <div class="form-text text-warning fw-semibold mt-1">
                                <i class="fas fa-exclamation-triangle me-1"></i>You can have a maximum of 5 images in total (1 primary + 4 gallery images).
                            </div>
                        </div>
                        
                        <hr>

                        <!-- Container for deleted image IDs -->
                        <div id="deleted_images_container"></div>

                        <!-- Existing Images List -->
                        <div class="image-list" style="max-height: 500px; overflow-y: auto;">
                            <?php if (empty($images)): ?>
                                <p class="text-center text-muted my-3">No images uploaded.</p>
                            <?php else: ?>
                                <?php foreach ($images as $img): ?>
                                    <div class="d-flex align-items-center border-bottom py-2 image-row" data-id="<?= $img['id'] ?>">
                                        <div class="me-2">
                                            <img src="../../../uploads/catalogue/products/<?= htmlspecialchars($img['image_url']) ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="form-check">
                                                <input class="form-check-input primary-radio" type="radio" name="primary_image" id="primary_<?= $img['id'] ?>" value="<?= $img['id'] ?>" <?= $img['is_primary'] ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="primary_<?= $img['id'] ?>">
                                                    Primary
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
                        <i class="fas fa-save me-2"></i>Update Product
                    </button>
                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
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
                seriesSelect.append('<option value="">-- Select Series --</option>');
                
                if (response.success && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        var isSelected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        seriesSelect.append('<option value="' + item.id + '" ' + isSelected + '>' + item.name + '</option>');
                    });
                } else {
                    seriesSelect.html('<option value="">-- No Series Found --</option>');
                }
                reinitSelect2('#series_id', '-- Select Series --');
            },
            error: function() {
                console.error('Failed to fetch series');
            }
        });
    }

    // Initialization
    if (initialCategoryId) {
        fetchSubCategories(initialCategoryId, initialSubCategoryId);
        fetchSeries(initialCategoryId, initialSubCategoryId, initialSeriesId);
    }
    
    // Initial Dual Price Check
    var initialDualPrice = $('#category_id').find('option:selected').data('dual-price') == 1;
    toggleDualZonePricing(initialDualPrice);

    // Event Handlers
    $('#category_id').change(function() {
        var catId = $(this).val();
        
        // Dual Zone Logic
        var selectedOption = $(this).find('option:selected');
        var hasDualPrice = selectedOption.data('dual-price') == 1;
        toggleDualZonePricing(hasDualPrice);

        // Reset subcat and series
        fetchSubCategories(catId, null);
        $('#series_id').html('<option value="">-- Select Sub Category (or None) --</option>');
    });

    $('#sub_category_id').change(function() {
        var catId = $('#category_id').val();
        var subCatId = $(this).val();
        fetchSeries(catId, subCatId, null);
    });

    // Image Deletion Logic
    $('.delete-image-btn').click(function() {
        var row = $(this).closest('.image-row');
        var imgId = row.data('id');
        var isPrimary = row.find('.primary-radio').is(':checked');

        if (isPrimary) {
            alert('You cannot delete the Primary image. Please select another image as Primary first.');
            return;
        }

        if (confirm('Are you sure you want to delete this image?')) {
            // Add to hidden input
            $('#deleted_images_container').append('<input type="hidden" name="delete_images[]" value="' + imgId + '">');
            // Remove from view
            row.remove();
            
            // Re-check count (optional UI feedback)
            var remaining = $('.image-row').length;
            if (remaining === 0) {
                 // Warning or styling
            }
        }
    });

    // Limit product gallery images to a maximum of 5 in total (existing + new)
    $('#images').change(function() {
        var existingImages = $('.image-row').length;
        var newImages = this.files.length;
        if (existingImages + newImages > 5) {
            alert("You can have a maximum of 5 images in total (1 primary + 4 gallery images). You currently have " + existingImages + " existing images, so you can upload at most " + (5 - existingImages) + " new images.");
            this.value = ''; // clear input
        }
    });

    // Form Submission Validation + Quill Sync
    $('form').submit(function(e) {
        // Sync Quill content to hidden input
        if (typeof specsQuill !== 'undefined') {
            var specsHtml = specsQuill.root.innerHTML;
            if (specsHtml === '<p><br></p>') specsHtml = '';
            $('#specifications').val(specsHtml);
        }

        var existingImages = $('.image-row').length;
        var newImages = $('#images')[0].files.length;
        
        if (existingImages + newImages === 0) {
            e.preventDefault();
            alert('At least one product image is required.');
            return false;
        }

        if (existingImages + newImages > 5) {
            e.preventDefault();
            alert("You can have a maximum of 5 images in total (1 primary + 4 gallery images). Please remove some existing images or select fewer files to upload.");
            return false;
        }
    });
});
// Helper Variables
let sizeIndex = <?= ($item['variant_type'] === 'size' ? count($variants) : 0) ?>;
let colorIndex = <?= ($item['variant_type'] === 'color' ? count($variants) : 0) ?>;
let deletedVariantIds = [];
const availableColors = <?= json_encode($colors) ?>;

// Dual Zone Helper
function toggleDualZonePricing(enable) {
    if (enable) {
        $('.dual-price-field').show();
        $('.dual-price-col').show();
        $('.dual-price-input').show();
    } else {
        $('.dual-price-field').hide();
        $('.dual-price-col').hide();
        $('.dual-price-input').hide();
    }
    // Store state for new rows
    window.dualZoneEnabled = enable;
}

function addSizeRow() {
    let displayStyle = (window.dualZoneEnabled) ? '' : 'display:none;';
    
    let html = `
        <tr>
            <input type="hidden" name="variants[size][${sizeIndex}][id]" value="">
            <td><input type="text" name="variants[size][${sizeIndex}][name]" class="form-control form-control-sm" placeholder="Variant Name"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][code]" class="form-control form-control-sm" required placeholder="Product Code"></td>
            <td><input type="text" name="variants[size][${sizeIndex}][value]" class="form-control form-control-sm" required placeholder="Size (e.g., 25MM)"></td>
            <td><input type="text" inputmode="numeric" name="variants[size][${sizeIndex}][price]" class="form-control form-control-sm" required placeholder="Price"></td>
            <td class="dual-price-col" style="${displayStyle}">
                <input type="text" inputmode="numeric" name="variants[size][${sizeIndex}][price_zone2]" class="form-control form-control-sm dual-price-input" placeholder="Price Z2">
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
            <td><input type="text" inputmode="numeric" name="variants[color][${colorIndex}][price]" class="form-control form-control-sm" required placeholder="Price"></td>
            <td class="dual-price-col" style="${displayStyle}">
                <input type="text" inputmode="numeric" name="variants[color][${colorIndex}][price_zone2]" class="form-control form-control-sm dual-price-input" placeholder="Price Z2">
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

$(document).on('click', '.delete-variant-btn', function() {
    var id = $(this).data('id');
    if (confirm('Delete this variant?')) {
        $('#deleted_variants_container').append('<input type="hidden" name="delete_variants[]" value="' + id + '">');
        $(this).closest('tr').remove();
    }
});

// Remove Variant Image Logic
$(document).on('click', '.remove-variant-image-btn', function() {
    var btn = $(this);
    var container = btn.closest('.d-flex.align-items-center'); // The parent row cell container
    var thumbnailDiv = container.find('.bg-white'); // The thumbnail container
    var hiddenInput = btn.siblings('.delete-image-flag');
    var fileInput = btn.siblings('input[type="file"]');

    if (confirm('Are you sure you want to remove this image?')) {
        // 1. Set Flag
        hiddenInput.val('1');

        // 2. Clear File Input
        fileInput.val('');

        // 3. Update Thumbnail to placeholder
        thumbnailDiv.html('<span class="text-muted" style="opacity:0.3"><i class="fas fa-image"></i></span>');
        thumbnailDiv.prop('title', '');
        thumbnailDiv.removeAttr('onclick');
        thumbnailDiv.css('cursor', 'default');

        // 4. Disable Button
        btn.prop('disabled', true);
    }
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
</script>
