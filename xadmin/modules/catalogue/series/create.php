<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Fetch Active Main Categories
$main_categories = fetchAll("SELECT id, name FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");

$pageTitle = "Add New Series";
$activePage = 'catalogue_series';
$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';
$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

// Default values
$item = [
    'category_id' => '',
    'sub_category_id' => '',
    'name' => '',
    'description' => '',
    'display_order' => 0,
    'is_active' => 1
];

// Get max display order
$maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_series");
$item['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Series
            </a>
            <h2><i class="fas fa-plus-circle me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="create_process.php" enctype="multipart/form-data">
                        
                        <!-- Main Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                Main Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Select Main Category --</option>
                                <?php foreach ($main_categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sub Category (Dynamic) -->
                        <div class="mb-3" id="sub_category_container" style="display:none;">
                            <label for="sub_category_id" class="form-label">
                                Sub Category <small class="text-muted">(Optional - Select 'None' for 3-Tier)</small>
                            </label>
                            <select class="form-select" id="sub_category_id" name="sub_category_id">
                                <option value="">-- None (Direct under Main Category) --</option>
                                <!-- Populated via AJAX -->
                            </select>
                        </div>

                        <!-- Name & Order -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">
                                    Series Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       required
                                       placeholder="e.g., Thermostatic, Premium Collection">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="<?= $item['display_order'] ?>"
                                       min="0">
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Series Image</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Allowed formats: JPG, PNG, WEBP. Max size: 2MB.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"></textarea>
                        </div>

                        <!-- Status & Option -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_active" 
                                           name="is_active" 
                                           value="1" 
                                           checked>
                                    <label class="form-check-label" for="is_active">
                                        Active (visible)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           id="is_new_arrival" 
                                           name="is_new_arrival" 
                                           value="1">
                                    <label class="form-check-label" for="is_new_arrival">
                                        New Arrival (show in New Collections on home page)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Series
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
                    <h5 class="card-title"><i class="fas fa-sitemap me-2"></i>Hierarchy Info</h5>
                    <p class="small text-muted mb-2">
                        <strong>3-Tier Structure:</strong><br>
                        Category -> Series -> Product<br>
                        (Leave Sub Category as 'None')
                    </p>
                    <p class="small text-muted">
                        <strong>4-Tier Structure:</strong><br>
                        Category -> Sub Category -> Series -> Product
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize Select2 on category dropdown
    $('#category_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Main Category --',
        allowClear: true,
        width: '100%'
    });

    // Helper to reinit Select2 on sub_category after AJAX
    function initSubCatSelect2() {
        if ($('#sub_category_id').hasClass('select2-hidden-accessible')) {
            $('#sub_category_id').select2('destroy');
        }
        $('#sub_category_id').select2({
            theme: 'bootstrap-5',
            placeholder: '-- None (Direct under Main Category) --',
            allowClear: true,
            width: '100%'
        });
    }

    $('#category_id').change(function() {
        var categoryId = $(this).val();
        var subCatSelect = $('#sub_category_id');
        var wrapper = $('#sub_category_container');

        if (categoryId) {
            $.ajax({
                url: '../../catalogue/ajax/get_sub_categories.php',
                type: 'GET',
                data: { category_id: categoryId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        subCatSelect.html('<option value="">-- None (Direct under Main Category) --</option>');
                        $.each(response.data, function(index, item) {
                            subCatSelect.append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                        wrapper.slideDown();
                        initSubCatSelect2();
                    } else {
                        subCatSelect.html('<option value="">-- No Sub Categories Available --</option>');
                        wrapper.hide();
                    }
                },
                error: function() {
                    console.error('Failed to fetch sub categories');
                }
            });
        } else {
            wrapper.slideUp();
            subCatSelect.html('<option value="">-- None --</option>');
        }
    });
});
</script>
