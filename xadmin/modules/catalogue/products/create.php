<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Fetch Active Main Categories
$main_categories = fetchAll("SELECT id, name, has_dual_price FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");
// Fetch Active Colors for Library
$colors = fetchAll("SELECT id, name, type, hex_code, texture_image FROM catalogue_colors WHERE is_active = 1 ORDER BY name ASC");
// Fetch Active Features
$features = fetchAll("SELECT id, name, icon_url FROM catalogue_features WHERE is_active = 1 ORDER BY display_order ASC, name ASC");

$pageTitle = "Add New Product";
$activePage = 'catalogue_products';
$additionalCSS = '<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';
$additionalJS = '<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

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
                                    Series <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="series_id" name="series_id" required disabled>
                                    <option value="">-- Select Sub Category First --</option>
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
                                <label for="code" class="form-label">Product Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code"
                                    placeholder="e.g., CR-101">
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="hsn_code" class="form-label">HSN Code</label>
                                <input type="text" class="form-control" id="hsn_code" name="hsn_code"
                                    maxlength="15" placeholder="e.g., 6912">
                                <small class="text-muted">Tax HSN code (applies to all variants too).</small>
                            </div>
                            <div class="col-md-2 mb-3 simple-field">
                                <label for="price" class="form-label">Price Z1 (₹)</label>
                                <input type="text" inputmode="numeric" class="form-control" id="price" name="price"
                                    placeholder="e.g., 1500">
                            </div>
                            <div class="col-md-2 mb-3 simple-field dual-price-field" style="display:none;">
                                <label for="price_zone2" class="form-label">Price Z2 (₹)</label>
                                <input type="text" inputmode="numeric" class="form-control" id="price_zone2"
                                    name="price_zone2" placeholder="e.g., 1500">
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
                                    value="<?= $item['display_order'] ?>">
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
                                            <th width="10%">Price <span class="text-danger">*</span></th>
                                            <th width="10%" class="dual-price-col" style="display:none;">Price (Z2)</th>
                                            <th width="35%">Image</th>
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
                                            <th width="400">Color <span class="text-danger">*</span></th>
                                            <th width="80">Preview</th>
                                            <th width="100">Price <span class="text-danger">*</span></th>
                                            <th width="100" class="dual-price-col" style="display:none;">Price (Z2)</th>
                                            <th>Image</th>
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
                            <label for="images" class="form-label">Product Gallery Images (Shared / Main)</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple
                                accept="image/*">
                            <div class="form-text text-warning fw-semibold">
                                <i class="fas fa-exclamation-triangle me-1"></i>You can upload a maximum of 5 images in total (1 primary + 4 gallery images).
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
                                <i class="fas fa-save me-2"></i>Create Product
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
                        seriesSelect.append('<option value="">-- Select Series --</option>');

                        if (response.success && response.data.length > 0) {
                            $.each(response.data, function (index, item) {
                                seriesSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                            });
                            seriesSelect.prop('disabled', false);
                            reinitSelect2('#series_id', '-- Select Series --');
                        } else {
                            seriesSelect.html('<option value="">-- No Series Found --</option>');
                        }
                    },
                    error: function () {
                        console.error('Failed to fetch series');
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
                    <div class="flex-grow-1">
                        <input type="file" name="variant_images_color_${colorIndex}" class="form-control form-control-sm" accept="image/*">
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

    // Sync Quill content to hidden input on form submit
    $('form').on('submit', function () {
        var specsHtml = specsQuill.root.innerHTML;
        if (specsHtml === '<p><br></p>') specsHtml = '';
        $('#specifications').val(specsHtml);
    });
</script>