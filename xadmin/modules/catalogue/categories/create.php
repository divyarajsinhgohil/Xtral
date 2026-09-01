<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Add New Catalogue Category";
$activePage = 'catalogue_categories';

// Default values
$category = [
    'name' => '',
    'description' => '',
    'display_order' => 0,
    'is_active' => 1
];

// Get max display order
$maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_categories");
$category['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Categories
            </a>
            <h2><i class="fas fa-plus-circle me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="create_process.php" enctype="multipart/form-data">
                        
                        <!-- Name & Order -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">
                                    Category Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       required
                                       placeholder="e.g., Sanitarywares, Bath Fittings">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="<?= $category['display_order'] ?>"
                                       min="0">
                            </div>
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Category Image</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Allowed formats: JPG, PNG, WEBP. Max size: 2MB. Recommended size: 1100x1400 pixels.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      placeholder="Brief description of this category"></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       checked>
                                <label class="form-check-label" for="is_active">
                                    Active (visible in catalogue)
                                </label>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="has_dual_price" 
                                       name="has_dual_price" 
                                       value="1">
                                <label class="form-check-label" for="has_dual_price">
                                    Enable Dual Zone Pricing (Zone 1 & Zone 2)
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Tips</h5>
                    <ul class="small text-muted ps-3">
                        <li class="mb-2">Use clear, concise names.</li>
                        <li class="mb-2">Images should be high quality but optimized (`1100x1400` is good).</li>
                        <li class="mb-2">Display order determines the sequence in the app (Low numbers first).</li>
                        <li class="mb-2">Inactive categories are hidden from the public view.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
