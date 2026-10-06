<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

$category = fetchOne("SELECT * FROM catalogue_categories WHERE id = ?", [$id]);

if (!$category) {
    $_SESSION['error'] = "Category not found.";
    header('Location: list.php');
    exit;
}

// If display_order is 0 or empty, automatically assign the next display order
if (empty($category['display_order']) || (int)$category['display_order'] <= 0) {
    $maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_categories");
    $category['display_order'] = ($maxOrder['max_order'] ?? 0) + 1;
}

$pageTitle = "Edit Catalogue Category";
$activePage = 'catalogue_categories';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Categories
            </a>
            <h2><i class="fas fa-edit me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="edit_process.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($category['image_url'] ?? '') ?>">
                        
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
                                       value="<?= htmlspecialchars($category['name']) ?>"
                                       required>
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
                            
                            <?php if (!empty($category['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="../../../uploads/catalogue/categories/<?= htmlspecialchars($category['image_url']) ?>" 
                                         alt="Current Image" 
                                         class="img-thumbnail" 
                                         style="max-width: 150px">
                                </div>
                            <?php endif; ?>

                            <input type="file" 
                                   class="form-control" 
                                   id="image" 
                                   name="image" 
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">Leave blank to keep current image. Recommended size: 1100x1400 pixels.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?= $category['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Active (visible in catalogue)
                                </label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="has_dual_price" 
                                       name="has_dual_price" 
                                       value="1" 
                                       <?= ($category['has_dual_price'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="has_dual_price">
                                    Enable Dual Zone Pricing (Zone 1 & Zone 2)
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
