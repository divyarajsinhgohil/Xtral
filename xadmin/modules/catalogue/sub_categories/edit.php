<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

$item = fetchOne("SELECT * FROM catalogue_sub_categories WHERE id = ?", [$id]);

if (!$item) {
    $_SESSION['error'] = "Sub Category not found.";
    header('Location: list.php');
    exit;
}

// Fetch Active Main Categories for Dropdown
$main_categories = fetchAll("SELECT id, name FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");

$pageTitle = "Edit Sub Category";
$activePage = 'catalogue_sub_categories';
$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';
$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Sub Categories
            </a>
            <h2><i class="fas fa-edit me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="POST" action="edit_process.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $item['id'] ?>">
                        <input type="hidden" name="current_image" value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
                        
                        <!-- Parent Category -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                Parent Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Select Parent Category --</option>
                                <?php foreach ($main_categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $item['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Name & Order -->
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="name" class="form-label">
                                    Sub Category Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($item['name']) ?>"
                                       required>
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
                            <label for="image" class="form-label">Sub Category Image</label>
                            
                            <?php if (!empty($item['image_url'])): ?>
                                <div class="mb-2">
                                    <img src="../../../uploads/catalogue/sub_categories/<?= htmlspecialchars($item['image_url']) ?>" 
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
                            <div class="form-text">Leave blank to keep current image.</div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       <?= $item['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">
                                    Active (visible)
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Sub Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#category_id').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select Parent Category --',
        allowClear: true,
        width: '100%'
    });
});
</script>
