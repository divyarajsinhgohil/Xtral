<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Home Screen Banners";
$activePage = 'catalogue_banners';

// Fetch all banners
$banners = fetchAll("SELECT * FROM catalogue_banners ORDER BY display_order ASC, id DESC");

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-images me-2"></i><?= $pageTitle ?></h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Banner
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <table id="bannersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="80">Preview</th>
                        <th>Title</th>
                        <th>Link URL</th>
                        <th width="80">Order</th>
                        <th width="80">Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($banners as $banner): ?>
                    <tr>
                        <td>
                            <?php if (($banner['banner_type'] ?? 'image') === 'video' && !empty($banner['video_url'])): ?>
                                <div style="width: 60px; height: 40px; border-radius: 4px; background: #eef2f3; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;" title="Video Banner">
                                    <i class="fas fa-video text-primary"></i>
                                </div>
                            <?php elseif (!empty($banner['image_url'])): ?>
                                <img src="<?= BASE_URL ?>/uploads/catalogue/banners/<?= $banner['image_url'] ?>" 
                                     alt="Banner" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                            <?php else: ?>
                                <span class="text-muted"><i class="fas fa-image"></i></span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($banner['title']) ?></td>
                        <td>
                            <?php if (!empty($banner['link_url'])): ?>
                                <small class="text-muted"><?= htmlspecialchars(substr($banner['link_url'], 0, 50)) ?><?= strlen($banner['link_url']) > 50 ? '...' : '' ?></small>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center"><?= $banner['display_order'] ?></td>
                        <td>
                            <?php if ($banner['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit.php?id=<?= $banner['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                    onclick="confirmDelete(<?= $banner['id'] ?>, '<?= htmlspecialchars(addslashes($banner['title'])) ?>')" 
                                    title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete banner: <strong id="deleteName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="delete.php" id="deleteForm">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#bannersTable').DataTable({
        pageLength: 25,
        order: [[3, 'asc']],
        columnDefs: [
            { orderable: false, targets: [0, 5] }
        ]
    });
});

function confirmDelete(id, name) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
