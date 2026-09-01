<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Get filters data
$allSeries = fetchAll("SELECT id, name, category_id, sub_category_id FROM catalogue_series ORDER BY name ASC");
$allCategories = fetchAll("SELECT id, name FROM catalogue_categories ORDER BY name ASC");
$allSubCategories = fetchAll("SELECT id, name, category_id FROM catalogue_sub_categories ORDER BY name ASC");

// Filter Logic
$seriesId      = isset($_GET['series_id'])      && $_GET['series_id']      !== '' ? (int)$_GET['series_id']      : '';
$categoryId    = isset($_GET['category_id'])    && $_GET['category_id']    !== '' ? (int)$_GET['category_id']    : '';
$subCategoryId = isset($_GET['sub_category_id']) && $_GET['sub_category_id'] !== '' ? (int)$_GET['sub_category_id'] : '';
$status        = isset($_GET['status'])         && $_GET['status']         !== '' ? (int)$_GET['status']         : '';
$productType   = isset($_GET['product_type'])  && $_GET['product_type']  !== '' ? $_GET['product_type']  : '';

$sql = "SELECT p.*, s.name as series_name, c.name as category_name, sc.name as sub_category_name,
        (SELECT image_url FROM catalogue_product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as image_url,
        (SELECT MIN(price) FROM catalogue_product_variants WHERE product_id = p.id) as min_price,
        (SELECT MAX(price) FROM catalogue_product_variants WHERE product_id = p.id) as max_price,
        (SELECT GROUP_CONCAT(code SEPARATOR ', ') FROM catalogue_product_variants WHERE product_id = p.id) as variant_codes,
        (SELECT GROUP_CONCAT(attribute_value SEPARATOR ', ') FROM catalogue_product_variants WHERE product_id = p.id) as variant_values
        FROM catalogue_products p
        JOIN catalogue_series s ON p.series_id = s.id
        JOIN catalogue_categories c ON s.category_id = c.id
        LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id";

$where = [];
$params = [];

if ($seriesId !== '') {
    $where[] = "p.series_id = ?";
    $params[] = $seriesId;
}
if ($categoryId !== '') {
    $where[] = "s.category_id = ?";
    $params[] = $categoryId;
}
if ($subCategoryId !== '') {
    $where[] = "sc.id = ?";
    $params[] = $subCategoryId;
}
if ($status !== '') {
    $where[] = "p.is_active = ?";
    $params[] = $status;
}
if ($productType !== '') {
    switch ($productType) {
        case 'simple':
            $where[] = "p.variant_type = 'none'";
            break;
        case 'color':
            $where[] = "p.variant_type = 'color'";
            break;
        case 'size':
            $where[] = "p.variant_type = 'size'";
            break;
        case 'new_arrival':
            $where[] = "p.is_new_arrival = 1";
            break;
    }
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY c.name ASC, s.name ASC, p.display_order ASC, p.name ASC";
$products = fetchAll($sql, $params);

// Filter sub-categories shown in dropdown: if a category is selected show only that category's sub-cats
$filteredSubCategories = $categoryId !== ''
    ? array_filter($allSubCategories, fn($s) => $s['category_id'] == $categoryId)
    : $allSubCategories;

// Filter series shown in dropdown based on selected category and/or sub-category
$filteredSeries = $allSeries;
if ($subCategoryId !== '') {
    $filteredSeries = array_filter($allSeries, fn($s) => $s['sub_category_id'] == $subCategoryId);
} elseif ($categoryId !== '') {
    $filteredSeries = array_filter($allSeries, fn($s) => $s['category_id'] == $categoryId);
}

$activePage = 'catalogue_products';
$pageTitle = 'Catalogue Products';

// Load Select2
$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<style>
    /* ── Select2 small size vertical-center fix ── */
    .select2-container--bootstrap-5 .select2-selection--single {
        height: 31px !important;
        display: flex !important;
        align-items: center !important;
        font-size: .875rem;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        line-height: normal !important;
        padding: 0 28px 0 10px !important;
        width: 100%;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
        height: 31px !important;
        top: 0 !important;
    }
    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__clear {
        margin-top: 0 !important;
        line-height: 1 !important;
    }
    /* ── Filter bar full-width flex layout ── */
    .filter-bar-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
    }
    .filter-bar-form .filter-label {
        font-size: .875rem;
        font-weight: 600;
        white-space: nowrap;
    }
    .filter-bar-form .filter-item {
        flex: 0 0 160px;   /* Category — fixed small */
    }
    .filter-bar-form .filter-item.filter-grow {
        flex: 1 1 0;       /* Sub-Category & Series — equal share of remaining space */
        min-width: 180px;
    }
    .filter-bar-form .filter-item.filter-sm {
        flex: 0 0 140px;   /* Status & Product Type — fixed small */
    }
    .filter-bar-form .filter-item .select2-container {
        width: 100% !important;
    }
    .filter-bar-form .filter-reset {
        flex: 0 0 auto;
    }
</style>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-box me-2"></i>Products</h2>
            <p class="text-muted">Manage product details and gallery</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Product
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
            <form method="GET" id="filterForm" class="filter-bar-form">

                <span class="filter-label">Filter By:</span>

                <!-- Category Filter -->
                <div class="filter-item">
                    <select name="category_id" id="filter_category" class="form-select form-select-sm filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($allCategories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId === $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sub-Category Filter -->
                <div class="filter-item filter-grow">
                    <select name="sub_category_id" id="filter_sub_category" class="form-select form-select-sm filter-select">
                        <option value="">All Sub-Categories</option>
                        <?php foreach ($filteredSubCategories as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= $subCategoryId === $sub['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Series Filter -->
                <div class="filter-item filter-grow">
                    <select name="series_id" id="filter_series" class="form-select form-select-sm filter-select">
                        <option value="">All Series</option>
                        <?php foreach ($filteredSeries as $ser): ?>
                            <option value="<?= $ser['id'] ?>" <?= $seriesId === $ser['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($ser['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="filter-item filter-sm">
                    <select name="status" id="filter_status" class="form-select form-select-sm filter-select">
                        <option value="">All Status</option>
                        <option value="1" <?= $status === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= $status === 0 && $status !== '' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <!-- Product Type Filter -->
                <div class="filter-item filter-sm">
                    <select name="product_type" id="filter_product_type" class="form-select form-select-sm filter-select">
                        <option value="">All Products</option>
                        <option value="simple" <?= $productType === 'simple' ? 'selected' : '' ?>>Simple</option>
                        <option value="color" <?= $productType === 'color' ? 'selected' : '' ?>>Color Variant</option>
                        <option value="size" <?= $productType === 'size' ? 'selected' : '' ?>>Size Variant</option>
                        <option value="new_arrival" <?= $productType === 'new_arrival' ? 'selected' : '' ?>>⭐ New Arrival</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="filter-reset">
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
                <table class="table table-hover align-middle" id="productsTable">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th width="50">Order</th>
                            <th width="80">Image</th>
                            <th>Product Name / Code</th>
                            <th>Series / Category</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $item): ?>
                        <tr>
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="product_ids[]" value="<?= $item['id'] ?>">
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $item['display_order'] ?></span>
                            </td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="../../../uploads/catalogue/products/<?= htmlspecialchars($item['image_url']) ?>" 
                                         class="img-thumbnail" 
                                         style="width: 60px; height: 60px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted border rounded" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                <?php if (!empty($item['is_new_arrival'])): ?>
                                    <span class="badge bg-warning text-dark ms-1" style="font-size: 0.7em;"><i class="fas fa-star me-1"></i>New</span>
                                <?php endif; ?>
                                <?php if (!empty($item['video_url'])): ?>
                                    <span class="badge bg-info text-dark ms-1" style="font-size: 0.7em;" title="Has Video"><i class="fas fa-video me-1"></i>Video</span>
                                <?php endif; ?><br>
                                <?php if ($item['variant_type'] === 'none'): ?>
                                    <small class="text-muted">Code: <?= htmlspecialchars(dashCode($item['code'])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Type: <?= ucfirst($item['variant_type']) ?> Variants</small><br>
                                    <small class="text-muted" style="font-size: 0.8em;">
                                        Codes: <?= htmlspecialchars(implode(', ', array_map('dashCode', explode(', ', $item['variant_codes'] ?? '')))) ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($item['series_name']) ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($item['category_name']) ?>
                                    <?= $item['sub_category_name'] ? ' &rsaquo; ' . htmlspecialchars($item['sub_category_name']) : '' ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($item['variant_type'] === 'none'): ?>
                                    <?= number_format($item['price'] ?? 0, 2) ?>
                                <?php else: ?>
                                    <small class="text-muted">Range:</small><br>
                                    <?= number_format($item['min_price'] ?? 0, 2) ?> - <?= number_format($item['max_price'] ?? 0, 2) ?>
                                <?php endif; ?>
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
                    This will delete product details and all gallery images.
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

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// All sub-categories and series data (for dynamic filtering without extra AJAX)
const allSubCategories = <?= json_encode(array_values($allSubCategories)) ?>;
const allSeries = <?= json_encode(array_values($allSeries)) ?>;

$(document).ready(function() {

    // ── DataTable ────────────────────────────────────────────────
    var table = $('#productsTable').DataTable({
        order: [[4, 'asc'], [1, 'asc'], [3, 'asc']], // Adjusted column indices due to new checkbox column
        pageLength: 25,
        language: { search: "Search products:" },
        columnDefs: [
            { orderable: false, targets: [0, 2, 7] } // 0=Checkbox, 2=Image, 7=Actions
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
    $('#productsTable tbody').on('change', '.row-checkbox', function() {
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
        if (confirm("Are you sure you want to delete all selected products?\nThis will delete product details and all gallery images.")) {
            $('#bulkDeleteForm').submit();
        }
    });

    // ── Select2 on all filter selects ────────────────────────────
    $('.filter-select').select2({
        theme: 'bootstrap-5',
        width: 'resolve',
        allowClear: true,
        placeholder: function() {
            return $(this).find('option[value=""]').text();
        }
    });

    // ── Auto-submit when any filter changes ──────────────────────
    $('.filter-select').on('change', function() {
        var id = $(this).attr('id');
        if (id === 'filter_category') {
            // When category changes: reload sub-categories + series, then submit
            var catId = $(this).val();
            updateSubCategories(catId, null, false);
            updateSeries(catId, null, true);
        } else if (id === 'filter_sub_category') {
            // When sub-category changes: reload series, then submit
            var subId = $(this).val();
            var catId = $('#filter_category').val();
            updateSeries(catId, subId, true);
        } else {
            $('#filterForm').submit();
        }
    });

    // ── Dynamic Sub-Category population on Category change ───────
    function updateSubCategories(categoryId, selectedSubId, autoSubmit) {
        var $subSelect = $('#filter_sub_category');
        $subSelect.empty().append('<option value="">All Sub-Categories</option>');

        var filtered = categoryId
            ? allSubCategories.filter(function(s) { return s.category_id == categoryId; })
            : allSubCategories;

        filtered.forEach(function(s) {
            var selected = (selectedSubId && s.id == selectedSubId) ? ' selected' : '';
            $subSelect.append('<option value="' + s.id + '"' + selected + '>' + s.name + '</option>');
        });

        $subSelect.trigger('change.select2'); // refresh Select2 display

        if (autoSubmit) {
            $('#filterForm').submit();
        }
    }

    // ── Dynamic Series population on Category / Sub-Category change ──
    function updateSeries(categoryId, subCategoryId, autoSubmit) {
        var $seriesSelect = $('#filter_series');
        $seriesSelect.empty().append('<option value="">All Series</option>');

        var filtered;
        if (subCategoryId) {
            // Sub-category selected → show only series under that sub-category
            filtered = allSeries.filter(function(s) { return s.sub_category_id == subCategoryId; });
        } else if (categoryId) {
            // Category selected (no sub-category) → show series under that category
            filtered = allSeries.filter(function(s) { return s.category_id == categoryId; });
        } else {
            // No filter → show all series
            filtered = allSeries;
        }

        filtered.forEach(function(s) {
            $seriesSelect.append('<option value="' + s.id + '">' + s.name + '</option>');
        });

        $seriesSelect.trigger('change.select2'); // refresh Select2 display

        if (autoSubmit) {
            $('#filterForm').submit();
        }
    }

    // On page load, restore Select2 display for pre-selected values (rendered server-side)
    var initialSubCatId = <?= $subCategoryId !== '' ? $subCategoryId : 'null' ?>;
    var initialSeriesId  = <?= $seriesId !== ''      ? $seriesId      : 'null' ?>;
    if (initialSubCatId) {
        $('#filter_sub_category').val(initialSubCatId).trigger('change.select2');
    }
    if (initialSeriesId) {
        $('#filter_series').val(initialSeriesId).trigger('change.select2');
    }
});

function confirmDelete(id, name) {
    $('#deleteName').text(name);
    $('#deleteId').val(id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Filter state persistence (restore after edit) ────────────────
(function() {
    var STORAGE_KEY = 'catalogue_products_filters';

    // On page load: if no filter params in URL, restore from sessionStorage
    if (window.location.search === '' || window.location.search === '?') {
        var saved = sessionStorage.getItem(STORAGE_KEY);
        if (saved && saved !== '?' && saved !== '') {
            window.location.replace(window.location.pathname + saved);
        }
    } else {
        // Save current filter state
        sessionStorage.setItem(STORAGE_KEY, window.location.search);
    }

    // Save on edit button click
    document.querySelectorAll('a[href^="edit.php"]').forEach(function(link) {
        link.addEventListener('click', function() {
            sessionStorage.setItem(STORAGE_KEY, window.location.search);
        });
    });

    // Clear on reset button click
    var resetBtn = document.querySelector('a[href="list.php"]');
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            sessionStorage.removeItem(STORAGE_KEY);
        });
    }
})();
</script>
