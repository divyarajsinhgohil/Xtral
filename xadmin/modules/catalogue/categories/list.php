<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Filter Logic
$status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : '';

$sql = "SELECT * FROM catalogue_categories";
$params = [];
$where = [];

if ($status !== '') {
    $where[] = "is_active = ?";
    $params[] = $status;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY display_order ASC, name ASC";
$categories = fetchAll($sql, $params);

$activePage = 'catalogue_categories';
$pageTitle = 'Catalogue Categories';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-layer-group me-2"></i>Catalogue Categories</h2>
            <p class="text-muted">Manage product categories for the public catalogue</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Category
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="col-form-label small fw-bold">Filter By:</label>
                </div>
                <!-- Status Filter -->
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" <?= $status === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status === 0 && $status !== '' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <!-- Buttons -->
                <div class="col-auto">
                    <a href="list.php" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="categoriesTable">
                    <thead>
                        <tr>
                            <th width="50">Order</th>
                            <th width="80">Image</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>
                                <span class="badge bg-secondary"><?= $category['display_order'] ?></span>
                            </td>
                            <td>
                                <?php if (!empty($category['image_url'])): ?>
                                    <img src="../../../uploads/catalogue/categories/<?= htmlspecialchars($category['image_url']) ?>" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($category['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted border rounded" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($category['name']) ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars(mb_strimwidth($category['description'] ?? '', 0, 50, '...')) ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($category['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?= $category['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category <strong id="deleteCategoryName"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. All sub-categories, series, and products under this category will also be deleted.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete.php" method="POST" class="d-inline">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Delete Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>

<script>
$(document).ready(function() {
    $('#categoriesTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25,
        language: {
            search: "Search categories:"
        },
        columnDefs: [
            { orderable: false, targets: [1, 5] } // Disable sorting on image and actions
        ]
    });
});

function confirmDelete(id, name) {
    $('#deleteCategoryName').text(name);
    $('#deleteId').val(id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Filter state persistence (restore after edit) ────────────────
(function() {
    var STORAGE_KEY = 'catalogue_categories_filters';

    if (window.location.search === '' || window.location.search === '?') {
        var saved = sessionStorage.getItem(STORAGE_KEY);
        if (saved && saved !== '?' && saved !== '') {
            window.location.replace(window.location.pathname + saved);
        }
    } else {
        sessionStorage.setItem(STORAGE_KEY, window.location.search);
    }

    document.querySelectorAll('a[href^="edit.php"]').forEach(function(link) {
        link.addEventListener('click', function() {
            sessionStorage.setItem(STORAGE_KEY, window.location.search);
        });
    });

    var resetBtn = document.querySelector('a[href="list.php"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            sessionStorage.removeItem(STORAGE_KEY);
        });
    }
})();
</script>
