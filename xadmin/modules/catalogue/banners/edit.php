<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: list.php');
    exit;
}

$banner = fetchOne("SELECT * FROM catalogue_banners WHERE id = ?", [$id]);
if (!$banner) {
    $_SESSION['error'] = "Banner not found.";
    header('Location: list.php');
    exit;
}

$pageTitle = "Edit Banner";
$activePage = 'catalogue_banners';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Banners
            </a>
            <h2><i class="fas fa-edit me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Banner Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="edit_process.php" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $banner['id'] ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Banner Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($banner['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Banner Type <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="banner_type" id="type_image" value="image" <?= ($banner['banner_type'] ?? 'image') === 'image' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_image">Image</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="banner_type" id="type_video" value="video" <?= ($banner['banner_type'] ?? 'image') === 'video' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="type_video">Video</label>
                            </div>
                        </div>

                        <div class="mb-3" id="imageGroup">
                            <label class="form-label">Current Image</label>
                            <div class="mb-2">
                                <?php if (!empty($banner['image_url'])): ?>
                                    <img src="<?= BASE_URL ?>/uploads/catalogue/banners/<?= $banner['image_url'] ?>" 
                                         alt="Banner" style="max-width: 400px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;">
                                <?php else: ?>
                                    <span class="text-muted">No image</span>
                                <?php endif; ?>
                            </div>
                            <label for="image" class="form-label">Replace Image (Optional)</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Supported: JPG, PNG, WebP.</small>
                        </div>

                        <div class="mb-3" id="videoGroup" style="display: none;">
                            <label class="form-label">Current Video</label>
                            <div class="mb-2">
                                <?php if (!empty($banner['video_url'])): ?>
                                    <video src="<?= BASE_URL ?>/uploads/catalogue/banners/<?= $banner['video_url'] ?>" 
                                           controls muted style="max-width: 400px; max-height: 200px; border-radius: 8px; border: 1px solid #ddd;"></video>
                                <?php else: ?>
                                    <span class="text-muted">No video</span>
                                <?php endif; ?>
                            </div>
                            <label for="video" class="form-label">Replace Video (Optional)</label>
                            <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm">
                            <small class="text-muted">Leave empty to keep current video. Max size: 10MB. Supported: MP4, WebM. Note: Videos will only play the first 10 seconds on the website.</small>
                        </div>

                        <div class="mb-3">
                            <label for="link_url" class="form-label">Link URL (Optional)</label>
                            <input type="text" class="form-control" id="link_url" name="link_url" value="<?= htmlspecialchars($banner['link_url'] ?? '') ?>" placeholder="e.g., /category/faucets">
                            <small class="text-muted">Deep link or URL to open when the banner is tapped in the app.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $banner['display_order'] ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= $banner['is_active'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_active">Active (Visible)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Banner
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeImage = document.getElementById('type_image');
    const typeVideo = document.getElementById('type_video');
    const imageGroup = document.getElementById('imageGroup');
    const videoGroup = document.getElementById('videoGroup');
    const videoInput = document.getElementById('video');

    function toggleFields() {
        if (typeImage.checked) {
            imageGroup.style.display = 'block';
            videoGroup.style.display = 'none';
        } else {
            imageGroup.style.display = 'none';
            videoGroup.style.display = 'block';
        }
    }

    typeImage.addEventListener('change', toggleFields);
    typeVideo.addEventListener('change', toggleFields);
    
    // Run initially
    toggleFields();
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
