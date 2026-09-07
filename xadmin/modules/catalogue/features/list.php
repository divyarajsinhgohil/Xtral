<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Feature Icons Library";
$activePage = "catalogue_features";

// Handle Delete
if (isset($_GET['delete_id'])) {
    $deleteId = (int) $_GET['delete_id'];
    try {
        $feature = fetchOne("SELECT icon_url FROM catalogue_features WHERE id = ?", [$deleteId]);
        if ($feature) {
            if ($feature['icon_url']) {
                deleteUploadedFile('catalogue/features/' . $feature['icon_url']);
            }
            execute("DELETE FROM catalogue_features WHERE id = ?", [$deleteId]);
            $_SESSION['success'] = "Feature deleted successfully.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Cannot delete feature. It may be assigned to products.";
    }
    header("Location: list.php");
    exit;
}

$features = fetchAll("SELECT * FROM catalogue_features ORDER BY display_order ASC, name ASC");

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-star text-warning me-2"></i><?= $pageTitle ?></h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Feature
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="featuresTable">
                    <thead class="table-light">
                        <tr>
                            <th width="80">Icon</th>
                            <th>Feature Name</th>
                            <th>Display Order</th>
                            <th>Status</th>
                            <th width="150" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($features as $f): ?>
                            <tr>
                                <td>
                                    <?php if ($f['icon_url']): ?>
                                        <img src="<?= BASE_URL ?>/uploads/catalogue/features/<?= htmlspecialchars($f['icon_url']) ?>"
                                            class="img-thumbnail" style="width: 50px; height: 50px; object-fit: contain;">
                                    <?php else: ?>
                                        <div class="bg-light border text-center pt-2" style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($f['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($f['display_order']) ?></td>
                                <td>
                                    <?php if ($f['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="edit.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-info text-white"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete"
                                        onclick="confirmDeleteFeature(<?= $f['id'] ?>, <?= htmlspecialchars(json_encode($f['name']), ENT_QUOTES) ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($features)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No features found. <a href="create.php">Create one now</a>.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFeatureModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete feature <strong id="deleteFeatureName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmDeleteFeatureBtn" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i>Delete</a>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
    function confirmDeleteFeature(id, name) {
        document.getElementById('deleteFeatureName').textContent = name;
        document.getElementById('confirmDeleteFeatureBtn').href = 'list.php?delete_id=' + id;
        new bootstrap.Modal(document.getElementById('deleteFeatureModal')).show();
    }

    $(document).ready(function () {
        if ($('#featuresTable tbody tr').length > 1) {
            $('#featuresTable').DataTable({
                "pageLength": 25,
                "ordering": true,
                "columnDefs": [
                    { "orderable": false, "targets": [0, 4] } // Disable sorting on Icon and Actions
                ]
            });
        }
    });
</script>