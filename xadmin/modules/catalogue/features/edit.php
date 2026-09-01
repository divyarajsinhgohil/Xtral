<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: list.php");
    exit;
}

$feature = fetchOne("SELECT * FROM catalogue_features WHERE id = ?", [$id]);
if (!$feature) {
    $_SESSION['error'] = "Feature not found.";
    header("Location: list.php");
    exit;
}

$pageTitle = "Edit Feature";
$activePage = "catalogue_features";

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Library
            </a>
            <h2><i class="fas fa-edit me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Feature Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="edit_process.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $feature['id'] ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Feature Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                value="<?= htmlspecialchars($feature['name']) ?>">
                        </div>

                        <div class="mb-3 border p-3 rounded bg-light">
                            <label for="icon_image" class="form-label d-block">Feature Icon</label>

                            <?php if ($feature['icon_url']): ?>
                                <div class="mb-3 text-center">
                                    <div
                                        style="background: #fff; padding: 10px; display: inline-block; border-radius: 8px; border: 1px solid #ddd; width: 100px; height: 100px;">
                                        <img src="<?= BASE_URL ?>/uploads/catalogue/features/<?= htmlspecialchars($feature['icon_url']) ?>"
                                            style="width: 100%; height: 100%; object-fit: contain;" alt="Feature Icon">
                                    </div>
                                    <small class="d-block text-muted mt-1">Current Icon</small>
                                </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="icon_image" name="icon_image" accept="image/*">
                            <small class="text-muted">Upload a new file to replace the current icon. Leave blank to keep
                                existing.</small>
                        </div>

                        <div class="mb-3">
                            <label for="display_order" class="form-label">Display Order</label>
                            <input type="number" class="form-control" id="display_order" name="display_order"
                                value="<?= htmlspecialchars($feature['display_order']) ?>" min="0">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                <?= $feature['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Feature</button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>