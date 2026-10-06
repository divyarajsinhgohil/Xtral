<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Get filters data
$allCategories = fetchAll("SELECT id, name FROM catalogue_categories ORDER BY name ASC");
$allSubCategories = fetchAll("SELECT id, name, category_id FROM catalogue_sub_categories ORDER BY name ASC");

// Filter Logic
$categoryId = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : '';
$subCategoryId = isset($_GET['sub_category_id']) && $_GET['sub_category_id'] !== '' ? (int)$_GET['sub_category_id'] : '';
$status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : '';

$sql = "SELECT s.*, c.name as category_name, sc.name as sub_category_name 
        FROM catalogue_series s
        JOIN catalogue_categories c ON s.category_id = c.id
        LEFT JOIN catalogue_sub_categories sc ON s.sub_category_id = sc.id";

$where = [];
$params = [];

if ($categoryId !== '') {
    $where[] = "s.category_id = ?";
    $params[] = $categoryId;
}
if ($subCategoryId !== '') {
    $where[] = "s.sub_category_id = ?";
    $params[] = $subCategoryId;
}
if ($status !== '') {
    $where[] = "s.is_active = ?";
    $params[] = $status;
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// Find currently active category object for reorder mode display
$selectedCategoryObj = null;
if ($categoryId !== '') {
    foreach ($allCategories as $c) {
        if ((int)$c['id'] === (int)$categoryId) {
            $selectedCategoryObj = $c;
            break;
        }
    }
}

if ($categoryId !== '') {
    $sql .= " ORDER BY s.display_order ASC, s.name ASC";
} else {
    $sql .= " ORDER BY c.name ASC, sc.name ASC, s.display_order ASC, s.name ASC";
}
$series = fetchAll($sql, $params);

$activePage = 'catalogue_series';
$pageTitle = 'Catalogue Series';

// Load Select2 & Sortable Styles
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
        flex: 1 1 0;       /* Sub-Category — takes remaining space */
        min-width: 180px;
    }
    .filter-bar-form .filter-item.filter-sm {
        flex: 0 0 120px;   /* Status — fixed small */
    }
    .filter-bar-form .filter-item .select2-container {
        width: 100% !important;
    }
    .filter-bar-form .filter-reset {
        flex: 0 0 auto;
    }
    /* ── Drag & Drop & Up/Down Reorder Styles ── */
    .drag-handle {
        cursor: grab;
        cursor: -webkit-grab;
        color: #6c757d;
        padding: 3px 5px;
        border-radius: 4px;
        transition: color 0.15s, background-color 0.15s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .drag-handle:hover {
        color: #0d6efd;
        background-color: #e9ecef;
    }
    .drag-handle:active {
        cursor: grabbing;
        cursor: -webkit-grabbing;
    }
    .order-btn-group {
        display: inline-flex;
        flex-direction: column;
        vertical-align: middle;
    }
    .order-btn-group .btn-move-up,
    .order-btn-group .btn-move-down {
        padding: 1px 4px !important;
        font-size: 0.65rem !important;
        line-height: 1 !important;
        height: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        color: #495057;
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }
    .order-btn-group .btn-move-up:hover:not(:disabled),
    .order-btn-group .btn-move-down:hover:not(:disabled) {
        background: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
    .order-btn-group .btn-move-up:disabled,
    .order-btn-group .btn-move-down:disabled {
        opacity: 0.3;
        cursor: not-allowed;
    }
    .order-badge-interactive {
        cursor: pointer;
        transition: transform 0.15s ease, background-color 0.15s;
        user-select: none;
    }
    .order-badge-interactive:hover {
        background-color: #0d6efd !important;
        transform: scale(1.1);
    }
    .row-highlight-move {
        animation: highlightRow 1.2s ease-out;
    }
    @keyframes highlightRow {
        0% { background-color: #cfe2ff !important; }
        50% { background-color: #e8f0fe !important; }
        100% { background-color: transparent; }
    }
    tr.row-reorder-selected {
        outline: 2px solid #0d6efd;
        outline-offset: -2px;
        background-color: #f0f7ff !important;
    }
    .sortable-ghost {
        opacity: 0.35;
        background-color: #cfe2ff !important;
    }
    .sortable-chosen {
        background-color: #fff3cd !important;
    }
</style>';

$additionalJS = '<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-8">
            <h2><i class="fas fa-layer-group me-2"></i>Product Series</h2>
            <p class="text-muted">Manage product series (e.g., Thermostatic, Premium Collection)</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Series
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
                        <?php foreach ($allSubCategories as $sub): ?>
                            <?php if ($categoryId === '' || $sub['category_id'] == $categoryId): ?>
                                <option value="<?= $sub['id'] ?>" <?= $subCategoryId === $sub['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sub['name']) ?>
                                </option>
                            <?php endif; ?>
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

                <!-- Reset Button -->
                <div class="filter-reset">
                    <a href="list.php" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>

            </form>
        </div>
    </div>
    <!-- Filter Bar End -->

    <?php if ($selectedCategoryObj): ?>
        <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between py-2 px-3 mb-3 shadow-sm border-0" id="reorderBanner" style="background: linear-gradient(135deg, #e8f0fe, #f8fafc); border-left: 4px solid #0d6efd !important;">
            <div class="d-flex align-items-center me-3 my-1">
                <i class="fas fa-arrows-alt-v fs-5 text-primary me-2"></i>
                <div>
                    <strong class="text-primary">Series Reorder Mode ("<?= htmlspecialchars($selectedCategoryObj['name']) ?>"):</strong>
                    <span class="text-dark ms-1">Move series to change index: drag using cursor handle <i class="fas fa-grip-vertical text-muted mx-1"></i>, use <strong>Up / Down</strong> buttons, press <strong>Alt+Up / Alt+Down</strong> keys, or click index badge directly to set order.</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 my-1">
                <span id="reorderSpinner" class="spinner-border spinner-border-sm text-primary" style="display:none;" role="status"></span>
                <span id="reorderToast" class="badge bg-success px-3 py-2 fs-6 shadow-sm" style="display:none;">
                    <i class="fas fa-check-circle me-1"></i> Order Saved!
                </span>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-light border d-flex align-items-center py-2 px-3 mb-3 small text-muted">
            <i class="fas fa-info-circle text-primary me-2"></i>
            <span><strong>Tip:</strong> Select a <strong>Category</strong> in the filter above to easily drag and drop or use Up / Down buttons to adjust series display order for that category.</span>
        </div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <form action="delete_bulk.php" method="POST" id="bulkDeleteForm">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="seriesTable">
                    <thead>
                        <tr>
                            <th width="40" class="text-center">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </th>
                            <th width="115" class="text-center"><?= $categoryId !== '' ? '<i class="fas fa-arrows-alt-v text-primary me-1"></i>Order' : 'Order' ?></th>
                            <th width="80">Image</th>
                            <th>Series Name</th>
                            <th>Category</th>
                            <th>Sub Category</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($series as $item): ?>
                        <tr data-series-id="<?= $item['id'] ?>">
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="series_ids[]" value="<?= $item['id'] ?>">
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <?php if ($categoryId !== ''): ?>
                                        <span class="drag-handle" title="Drag to reorder with cursor"><i class="fas fa-grip-vertical"></i></span>
                                        <div class="btn-group-vertical btn-group-sm order-btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-light border-0 p-0 px-1 btn-move-up" title="Move Up (Alt+Up)">
                                                <i class="fas fa-chevron-up text-secondary" style="font-size: 0.65rem;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border-0 p-0 px-1 btn-move-down" title="Move Down (Alt+Down)">
                                                <i class="fas fa-chevron-down text-secondary" style="font-size: 0.65rem;"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <span class="badge bg-secondary order-badge <?= $categoryId !== '' ? 'order-badge-interactive' : '' ?>"
                                          title="<?= $categoryId !== '' ? 'Click to set order index directly' : '' ?>"><?= $item['display_order'] ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($item['image_url'])): ?>
                                    <img src="../../../uploads/catalogue/series/<?= htmlspecialchars($item['image_url']) ?>" 
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
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($item['category_name']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($item['sub_category_name']): ?>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars($item['sub_category_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small"><em>Direct (3-Tier)</em></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                                <?php if ($item['is_new_arrival']): ?>
                                    <br><span class="badge bg-primary mt-1"><i class="fas fa-star me-1"></i>New Arrival</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../products/list.php?series_id=<?= $item['id'] ?>" 
                                       class="btn btn-outline-info" 
                                       title="Manage & Reorder Products in this Series">
                                        <i class="fas fa-boxes"></i>
                                    </a>
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
                    This will delete all products in this series!
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
// All sub-categories for dynamic filtering
const allSubCategories = <?= json_encode(array_values($allSubCategories)) ?>;

$(document).ready(function() {

    var isCategoryFiltered = <?= $categoryId !== '' ? 'true' : 'false' ?>;

    // ── DataTable ────────────────────────────────────────────────
    var table = $('#seriesTable').DataTable({
        order: isCategoryFiltered ? [] : [[3, 'asc'], [4, 'asc'], [1, 'asc']],
        pageLength: isCategoryFiltered ? -1 : 25,
        lengthMenu: isCategoryFiltered 
            ? [[-1, 25, 50, 100], ["All (Reorder)", 25, 50, 100]] 
            : [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: { search: "Search series:" },
        columnDefs: [
            { orderable: false, targets: isCategoryFiltered ? [0, 1, 2, 7] : [0, 2, 7] }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-flex align-items-center'l<'#bulkDeleteContainer.ms-3'>><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // ── Button disabled states (top/bottom) ───────────────────────
    function updateButtonsState() {
        var $rows = $('#seriesTable tbody tr[data-series-id]');
        var count = $rows.length;
        $rows.each(function(idx) {
            $(this).find('.btn-move-up').prop('disabled', idx === 0);
            $(this).find('.btn-move-down').prop('disabled', idx === count - 1);
        });
    }

    function highlightAndSave($row) {
        $row.removeClass('row-highlight-move');
        void $row[0].offsetWidth; // Force reflow
        $row.addClass('row-highlight-move');
        if ($row[0] && typeof $row[0].scrollIntoViewIfNeeded === 'function') {
            $row[0].scrollIntoViewIfNeeded(false);
        }
        updateButtonsState();
        saveSeriesOrder();
    }

    // ── Up / Down Button Handlers ─────────────────────────────────
    $('#seriesTable tbody').on('click', '.btn-move-up', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-series-id]');
        var $prev = $row.prev('tr[data-series-id]');
        if ($prev.length) {
            $prev.before($row);
            highlightAndSave($row);
        }
    });

    $('#seriesTable tbody').on('click', '.btn-move-down', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-series-id]');
        var $next = $row.next('tr[data-series-id]');
        if ($next.length) {
            $next.after($row);
            highlightAndSave($row);
        }
    });

    // ── Click Order Badge to Set Index Directly ───────────────────
    $('#seriesTable tbody').on('click', '.order-badge-interactive', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-series-id]');
        var currentIdx = parseInt($(this).text().trim(), 10) || 1;
        var totalRows = $('#seriesTable tbody tr[data-series-id]').length;
        var target = prompt('Move this series to index position (1 to ' + totalRows + '):', currentIdx);
        if (target === null) return;
        target = parseInt(target, 10);
        if (isNaN(target) || target < 1 || target > totalRows || target === currentIdx) return;

        var $allRows = $('#seriesTable tbody tr[data-series-id]');
        if (target === 1) {
            $allRows.first().before($row);
        } else if (target >= totalRows) {
            $allRows.last().after($row);
        } else {
            var targetRow = $allRows.get(target - 1);
            if (target < currentIdx) {
                $(targetRow).before($row);
            } else {
                $(targetRow).after($row);
            }
        }
        highlightAndSave($row);
    });

    // ── Row Selection & Keyboard Arrow Controls (Up / Down) ──────
    $('#seriesTable tbody').on('click', 'tr[data-series-id]', function(e) {
        if ($(e.target).closest('a, button, input, select, .drag-handle').length) return;
        $('#seriesTable tbody tr').removeClass('row-reorder-selected');
        $(this).addClass('row-reorder-selected');
    });

    $(document).on('keydown', function(e) {
        if (!isCategoryFiltered) return;
        if ($(e.target).is('input, textarea, select')) return;
        var $selected = $('#seriesTable tbody tr.row-reorder-selected');
        if (!$selected.length) return;

        if (e.key === 'ArrowUp' || (e.altKey && e.key === 'ArrowUp')) {
            e.preventDefault();
            var $prev = $selected.prev('tr[data-series-id]');
            if ($prev.length) {
                $prev.before($selected);
                highlightAndSave($selected);
            }
        } else if (e.key === 'ArrowDown' || (e.altKey && e.key === 'ArrowDown')) {
            e.preventDefault();
            var $next = $selected.next('tr[data-series-id]');
            if ($next.length) {
                $next.after($selected);
                highlightAndSave($selected);
            }
        }
    });

    // ── Drag & Drop Series Reordering ─────────────────────────────
    if (isCategoryFiltered && typeof Sortable !== 'undefined') {
        var tbodyEl = document.querySelector('#seriesTable tbody');
        if (tbodyEl) {
            Sortable.create(tbodyEl, {
                handle: '.drag-handle',
                animation: 180,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onStart: function() {
                    if (table.search()) {
                        alert("Note: Search filter is active. Clear search for full category reordering.");
                    }
                },
                onEnd: function() {
                    updateButtonsState();
                    saveSeriesOrder();
                }
            });
        }
    }

    updateButtonsState();

    function saveSeriesOrder() {
        if (table.search()) {
            alert("Please clear the search filter before reordering series to preserve all items.");
            return;
        }

        var seriesIds = [];
        $('#seriesTable tbody tr[data-series-id]').each(function(idx) {
            var sid = $(this).attr('data-series-id');
            if (sid) {
                seriesIds.push(sid);
                $(this).find('.order-badge').text(idx + 1);
            }
        });

        if (seriesIds.length === 0) return;

        var $spinner = $('#reorderSpinner');
        var $toast = $('#reorderToast');

        if ($spinner.length) $spinner.show();
        if ($toast.length) $toast.hide();

        fetch('reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ series_ids: seriesIds })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if ($spinner.length) $spinner.hide();
            if (res.success) {
                if ($toast.length) {
                    $toast.stop(true, true).fadeIn(150).delay(2500).fadeOut(600);
                }
            } else {
                alert(res.message || 'Error updating series order');
            }
        })
        .catch(function(err) {
            if ($spinner.length) $spinner.hide();
            console.error('Reorder error:', err);
            alert('Network error while saving series order');
        });
    }

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
    $('#seriesTable tbody').on('change', '.row-checkbox', function() {
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
        if (confirm("Are you sure you want to delete all selected series?\nThis will permanently delete all underlying products, variants, and gallery images!")) {
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

    // ── Auto-submit on change ─────────────────────────────────────
    $('.filter-select').on('change', function() {
        if ($(this).attr('id') === 'filter_category') {
            updateSubCategories($(this).val(), null, true);
        } else {
            $('#filterForm').submit();
        }
    });

    // ── Dynamic Sub-Category population ──────────────────────────
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

        $subSelect.trigger('change.select2');

        if (autoSubmit) {
            $('#filterForm').submit();
        }
    }

    // Restore selected sub-category value on load
    var initialSubCatId = <?= $subCategoryId !== '' ? $subCategoryId : 'null' ?>;
    if (initialSubCatId) {
        $('#filter_sub_category').val(initialSubCatId).trigger('change.select2');
    }
});

function confirmDelete(id, name) {
    $('#deleteName').text(name);
    $('#deleteId').val(id);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// ── Filter state persistence (restore after edit) ────────────────
(function() {
    var STORAGE_KEY = 'catalogue_series_filters';

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
