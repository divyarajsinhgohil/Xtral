<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Get all categories for filter
$allCategories = fetchAll("SELECT id, name FROM catalogue_categories ORDER BY name ASC");

// Filter Logic
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : '';
$status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : '';

$sql = "SELECT s.*, c.name as category_name 
        FROM catalogue_sub_categories s
        JOIN catalogue_categories c ON s.category_id = c.id";

$where = [];
$params = [];

if ($categoryId !== '') {
    $where[] = "s.category_id = ?";
    $params[] = $categoryId;
}
if ($status !== '') {
    $where[] = "s.is_active = ?";
    $params[] = $status;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.name ASC, s.display_order ASC, s.name ASC";
$sub_categories = fetchAll($sql, $params);

$activePage = 'catalogue_sub_categories';
$pageTitle = 'Catalogue Sub Categories';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-tags me-2"></i>Sub Categories</h2>
            <p class="text-muted">Manage Level 2 categories (e.g., Diverters under Bath Fittings)</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add Sub Category
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error']; unset($_SESSION['error']); ?>
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
                <!-- Category Filter -->
                <div class="col-auto">
                    <select name="category_id" class="form-select form-select-sm" style="min-width: 200px;" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId === $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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

    <div class="card shadow">
        <div class="card-body">
            <form action="delete_bulk.php" method="POST" id="bulkDeleteForm">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="subCategoriesTable">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th width="50">Order</th>
                            <th width="80">Image</th>
                            <th>Sub Category Name</th>
                            <th>Parent Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sub_categories as $item): ?>
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="sub_category_ids[]" value="<?= $item['id'] ?>">
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $item['display_order'] ?></span>
                            </td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="../../../uploads/catalogue/sub_categories/<?= htmlspecialchars($item['image_url']) ?>" 
                                         class="img-thumbnail" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted border rounded" 
                                         style="width: 50px; height: 50px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= htmlspecialchars($item['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="edit.php?id=<?= $item['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars($item['name']) ?>')"
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
            </form>
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
                <p>Are you sure you want to delete <strong id="deleteName"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. All linked series and products will be affected.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="delete.php" method="POST" class="d-inline">
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
    var table = $('#subCategoriesTable').DataTable({
        order: [[4, 'asc'], [1, 'asc']], // Adjusted to account for new checkbox column
        pageLength: 25,
        language: {
            search: "Search sub categories:"
        },
        columnDefs: [
            { orderable: false, targets: [0, 2, 6] } // 0=Checkbox, 2=Image, 6=Actions
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-flex align-items-center'l<'#bulkDeleteContainer.ms-3'>><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // ── Bulk Delete Logic ─────────────────────────────────────────
    $('#bulkDeleteContainer').html(
        '<button type="button" id="btnBulkDelete" class="btn btn-danger btn-sm" disabled>' +
        '<i class="fas fa-trash-alt me-1"></i> Delete Selected' +
        '</button>'
    );

    function updateBulkDeleteBtn() {
        var checkedCount = $('.row-checkbox:checked').length;
        $('#btnBulkDelete').prop('disabled', checkedCount === 0);
    }

    // Select All Checkbox
    $('#selectAll').on('change', function() {
        var isChecked = $(this).prop('checked');
        $('.row-checkbox').prop('checked', isChecked);
        updateBulkDeleteBtn();
    });

    // Individual Row Checkbox
    $('#subCategoriesTable tbody').on('change', '.row-checkbox', function() {
        if (!$(this).prop('checked')) {
            $('#selectAll').prop('checked', false);
        } else {
            if ($('.row-checkbox:checked').length === $('.row-checkbox').length) {
                $('#selectAll').prop('checked', true);
            }
        }
        updateBulkDeleteBtn();
    });

    // Handle Bulk Delete Submit
    $('#btnBulkDelete').on('click', function() {
        if (confirm("Are you sure you want to delete all selected sub-categories?\nThis will permanently delete all underlying series, products, variants, and gallery images!")) {
            $('#bulkDeleteForm').submit();
        }
    });
});

function confirmDelete(id, name) {
    $('#deleteName').text(name);
    $('#deleteId').val(id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Filter state persistence (restore after edit) ────────────────
(function() {
    var STORAGE_KEY = 'catalogue_sub_categories_filters';

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
