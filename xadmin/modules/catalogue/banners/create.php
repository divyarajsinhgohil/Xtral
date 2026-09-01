<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Add New Banner";
$activePage = 'catalogue_banners';

// Get max display order
$maxOrder = fetchOne("SELECT MAX(display_order) as max_order FROM catalogue_banners");
$nextOrder = ($maxOrder['max_order'] ?? 0) + 1;

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="list.php" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Banners
            </a>
            <h2><i class="fas fa-plus-circle me-2"></i><?= $pageTitle ?></h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Banner Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="create_process.php" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Banner Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., Summer Sale 2026">
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-block">Banner Type <span class="text-danger">*</span></label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="banner_type" id="type_image" value="image" checked>
                                <label class="form-check-label" for="type_image">Image</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="banner_type" id="type_video" value="video">
                                <label class="form-check-label" for="type_video">Video</label>
                            </div>
                        </div>

                        <div class="mb-3" id="imageGroup">
                            <label for="image" class="form-label">Banner Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="image" name="image" required accept="image/*">
                            <small class="text-muted">Recommended size: 1200 x 600 pixels. Supported: JPG, PNG, WebP.</small>
                        </div>

                        <div class="mb-3" id="videoGroup" style="display: none;">
                            <label for="video" class="form-label">Banner Video <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="video" name="video" accept="video/mp4,video/webm">
                            <small class="text-muted">Max size: 10MB. Supported: MP4, WebM. Note: Videos will only play the first 10 seconds on the website.</small>
                        </div>

                        <div class="mb-3">
                            <label for="link_url" class="form-label">Link URL (Optional)</label>
                            <input type="text" class="form-control" id="link_url" name="link_url" placeholder="e.g., /category/faucets or https://example.com">
                            <small class="text-muted">Deep link or URL to open when the banner is tapped in the app.</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="display_order" name="display_order" value="<?= $nextOrder ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                                    <label class="form-check-label" for="is_active">Active (Visible)</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Banner
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
    const imageInput = document.getElementById('image');
    const videoInput = document.getElementById('video');

    function toggleFields() {
        if (typeImage.checked) {
            imageGroup.style.display = 'block';
            imageInput.required = true;
            videoGroup.style.display = 'none';
            videoInput.required = false;
            videoInput.value = ''; // clear value
        } else {
            imageGroup.style.display = 'none';
            imageInput.required = false;
            imageInput.value = ''; // clear value
            videoGroup.style.display = 'block';
            videoInput.required = true;
        }
    }

    typeImage.addEventListener('change', toggleFields);
    typeVideo.addEventListener('change', toggleFields);
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
