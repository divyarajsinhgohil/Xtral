<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$productCategorySql = $productHierarchy['category'];
$productSubCategorySql = $productHierarchy['sub_category'];

// Get filters data
$priceLabel1 = getPriceLabel1();
$priceLabel2 = getPriceLabel2();
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
        (SELECT MIN(price_zone2) FROM catalogue_product_variants WHERE product_id = p.id) as min_price_zone2,
        (SELECT MAX(price_zone2) FROM catalogue_product_variants WHERE product_id = p.id) as max_price_zone2,
        (SELECT GROUP_CONCAT(code SEPARATOR ', ') FROM catalogue_product_variants WHERE product_id = p.id) as variant_codes,
        (SELECT GROUP_CONCAT(attribute_value SEPARATOR ', ') FROM catalogue_product_variants WHERE product_id = p.id) as variant_values
        FROM catalogue_products p
        LEFT JOIN catalogue_series s ON p.series_id = s.id
        JOIN catalogue_categories c ON {$productCategorySql} = c.id
        LEFT JOIN catalogue_sub_categories sc ON {$productSubCategorySql} = sc.id";

$where = [];
$params = [];

if ($seriesId !== '') {
    $where[] = "p.series_id = ?";
    $params[] = $seriesId;
}
if ($categoryId !== '') {
    $where[] = "{$productCategorySql} = ?";
    $params[] = $categoryId;
}
if ($subCategoryId !== '') {
    $where[] = "{$productSubCategorySql} = ?";
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

if ($seriesId !== '') {
    $sql .= " ORDER BY p.display_order ASC, p.id ASC";
} else {
    $sql .= " ORDER BY p.id ASC";
}
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

// Find currently active series object for reorder mode display
$selectedSeriesObj = null;
if ($seriesId !== '') {
    foreach ($allSeries as $s) {
        if ((int)$s['id'] === (int)$seriesId) {
            $selectedSeriesObj = $s;
            break;
        }
    }
}

$activePage = 'catalogue_products';
$pageTitle = 'Catalogue Products';

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
            <h2><i class="fas fa-box me-2"></i>Products</h2>
            <p class="text-muted">Manage product details and gallery</p>
        </div>
        <div class="col-md-4 text-end">
            <button type="button" class="btn btn-outline-secondary me-2" data-bs-toggle="modal" data-bs-target="#priceTitlesModal">
                <i class="fas fa-tags me-1"></i>Default Price Titles
            </button>
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
    <!-- Filter Bar End -->

    <?php if ($selectedSeriesObj): ?>
        <div class="alert alert-primary d-flex flex-wrap align-items-center justify-content-between py-2 px-3 mb-3 shadow-sm border-0" id="reorderBanner" style="background: linear-gradient(135deg, #e8f0fe, #f8fafc); border-left: 4px solid #0d6efd !important;">
            <div class="d-flex align-items-center me-3 my-1">
                <i class="fas fa-arrows-alt-v fs-5 text-primary me-2"></i>
                <div>
                    <strong class="text-primary">Series Reorder Mode ("<?= htmlspecialchars($selectedSeriesObj['name']) ?>"):</strong>
                    <span class="text-dark ms-1">Move products to change index: drag using cursor handle <i class="fas fa-grip-vertical text-muted mx-1"></i>, use <strong>Up / Down</strong> buttons, press <strong>Alt+Up / Alt+Down</strong> keys, or click index badge directly to set order.</span>
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
            <span><strong>Tip:</strong> Select a <strong>Series</strong> in the filter above to easily drag and drop or use Up / Down buttons to adjust product display order for that series.</span>
        </div>
    <?php endif; ?>

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
                            <th width="115" class="text-center"><?= $seriesId !== '' ? '<i class="fas fa-arrows-alt-v text-primary me-1"></i>Order' : 'Order' ?></th>
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
                        <tr data-product-id="<?= $item['id'] ?>">
                            <td class="text-center">
                                <input class="form-check-input row-checkbox" type="checkbox" name="product_ids[]" value="<?= $item['id'] ?>">
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <?php if ($seriesId !== ''): ?>
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
                                    <span class="badge bg-secondary order-badge <?= $seriesId !== '' ? 'order-badge-interactive' : '' ?>"
                                          title="<?= $seriesId !== '' ? 'Click to set order index directly' : '' ?>"><?= $item['display_order'] ?></span>
                                </div>
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
                                <?php if (!empty($item['code'])): ?>
                                    <small class="text-muted d-block">Code: <strong><?= htmlspecialchars(dashCode($item['code'])) ?></strong></small>
                                <?php endif; ?>
                                <?php if ($item['variant_type'] === 'none'): ?>
                                    <?php if (empty($item['code'])): ?>
                                        <small class="text-muted">Code: —</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">Type: <?= ucfirst($item['variant_type']) ?> Variants</small><br>
                                    <?php if (!empty($item['variant_codes'])): ?>
                                        <small class="text-muted" style="font-size: 0.8em;">
                                            Variants: <?= htmlspecialchars(implode(', ', array_map('dashCode', explode(', ', $item['variant_codes'])))) ?>
                                        </small>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php
                                    $searchTokens = [];
                                    if (!empty($item['code'])) {
                                        $searchTokens[] = $item['code'];
                                        $searchTokens[] = dashCode($item['code']);
                                        $searchTokens[] = preg_replace('/[^a-zA-Z0-9]/', '', $item['code']);
                                    }
                                    if (!empty($item['variant_codes'])) {
                                        foreach (explode(', ', $item['variant_codes']) as $vc) {
                                            $searchTokens[] = $vc;
                                            $searchTokens[] = dashCode($vc);
                                            $searchTokens[] = preg_replace('/[^a-zA-Z0-9]/', '', $vc);
                                        }
                                    }
                                    if (!empty($item['variant_values'])) {
                                        $searchTokens[] = $item['variant_values'];
                                    }
                                    $searchTokensStr = implode(' ', array_unique(array_filter($searchTokens)));
                                ?>
                                <span class="d-none search-tokens"><?= htmlspecialchars($searchTokensStr) ?></span>
                            </td>
                            <td>
                                <?php if (empty($item['series_name']) || ($item['series_name'] === $item['category_name'] && empty($item['sub_category_name']))): ?>
                                    <strong class="text-dark"><?= htmlspecialchars($item['category_name'] ?? '') ?></strong><br>
                                    <span class="badge bg-light text-secondary border" style="font-size: 0.72rem;"><i class="fas fa-layer-group me-1 text-primary"></i>Direct in Main Category</span>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($item['series_name'] ?? '') ?></strong><br>
                                    <small class="text-muted">
                                        <?= htmlspecialchars($item['category_name'] ?? '') ?>
                                        <?= !empty($item['sub_category_name']) ? ' &rsaquo; ' . htmlspecialchars($item['sub_category_name']) : '' ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $itemLabel1 = !empty($item['price_label_1']) ? $item['price_label_1'] : $priceLabel1;
                                    $itemLabel2 = !empty($item['price_label_2']) ? $item['price_label_2'] : $priceLabel2;
                                ?>
                                <?php if ($item['variant_type'] === 'none'): ?>
                                    <div><small class="text-muted"><?= htmlspecialchars($itemLabel1) ?>:</small> ₹<?= number_format((float)($item['price'] ?? 0), 2) ?></div>
                                    <?php if (!empty($item['price_zone2']) && (float)$item['price_zone2'] > 0): ?>
                                        <div><small class="text-muted"><?= htmlspecialchars($itemLabel2) ?>:</small> ₹<?= number_format((float)$item['price_zone2'], 2) ?></div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div>
                                        <small class="text-muted"><?= htmlspecialchars($itemLabel1) ?>:</small><br>
                                        ₹<?= number_format((float)($item['min_price'] ?? 0), 2) ?> - ₹<?= number_format((float)($item['max_price'] ?? 0), 2) ?>
                                    </div>
                                    <?php if (!empty($item['max_price_zone2']) && (float)$item['max_price_zone2'] > 0): ?>
                                        <div class="mt-1">
                                            <small class="text-muted"><?= htmlspecialchars($itemLabel2) ?>:</small><br>
                                            ₹<?= number_format((float)($item['min_price_zone2'] ?? 0), 2) ?> - ₹<?= number_format((float)$item['max_price_zone2'], 2) ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <a href="../qr/list.php?search=<?= urlencode($item['code'] ?: $item['name']) ?>" 
                                       class="btn btn-outline-dark" 
                                       title="View Catalogue QR Code">
                                        <i class="fas fa-qrcode"></i>
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

<!-- Price Titles Modal -->
<div class="modal fade" id="priceTitlesModal" tabindex="-1" aria-labelledby="priceTitlesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="save_price_labels.php" method="POST">
                <input type="hidden" name="redirect" value="list.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="priceTitlesModalLabel"><i class="fas fa-tags me-2"></i>Global Default Price Titles</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">Set the site-wide default price titles (Default: <strong>Zone 1</strong> & <strong>Zone 2</strong>). Any individual product can also have its own unique custom price titles set in that product's Edit page.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price 1 Title</label>
                        <input type="text" name="price_label_1" class="form-control" value="<?= htmlspecialchars($priceLabel1) ?>" required placeholder="e.g., Zone 1 or MRP">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Price 2 Title</label>
                        <input type="text" name="price_label_2" class="form-control" value="<?= htmlspecialchars($priceLabel2) ?>" required placeholder="e.g., Zone 2 or Offer Price">
                        <small class="text-muted">If a product has no Price 2 entered, only Price 1 will be displayed on the website.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Price Titles</button>
                </div>
            </form>
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
    var urlParams = new URLSearchParams(window.location.search);
    var initialSearch = urlParams.get('search') || '';
    var isSeriesFiltered = <?= $seriesId !== '' ? 'true' : 'false' ?>;

    // ── DataTable ────────────────────────────────────────────────
    var table = $('#productsTable').DataTable({
        search: { search: initialSearch },
        order: [], // Preserve database/DOM order in reorder mode
        pageLength: isSeriesFiltered ? -1 : 25,
        lengthMenu: isSeriesFiltered 
            ? [[-1, 25, 50, 100], ["All (Reorder)", 25, 50, 100]] 
            : [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        language: { search: "Search products:" },
        columnDefs: [
            { orderable: false, targets: isSeriesFiltered ? [0, 1, 2, 7] : [0, 2, 7] }
        ],
        dom: "<'row'<'col-sm-12 col-md-6 d-flex align-items-center'l<'#bulkDeleteContainer.ms-3'>><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    });

    // ── Button disabled states (top/bottom) ───────────────────────
    function updateButtonsState() {
        var $rows = $('#productsTable tbody tr[data-product-id]');
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
        saveProductOrder();
    }

    // ── Up / Down Button Handlers ─────────────────────────────────
    $('#productsTable tbody').on('click', '.btn-move-up', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-product-id]');
        var $prev = $row.prev('tr[data-product-id]');
        if ($prev.length) {
            $prev.before($row);
            highlightAndSave($row);
        }
    });

    $('#productsTable tbody').on('click', '.btn-move-down', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-product-id]');
        var $next = $row.next('tr[data-product-id]');
        if ($next.length) {
            $next.after($row);
            highlightAndSave($row);
        }
    });

    // ── Click Order Badge to Set Index Directly ───────────────────
    $('#productsTable tbody').on('click', '.order-badge-interactive', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $row = $(this).closest('tr[data-product-id]');
        var currentIdx = parseInt($(this).text().trim(), 10) || 1;
        var totalRows = $('#productsTable tbody tr[data-product-id]').length;
        var target = prompt('Move this product to index position (1 to ' + totalRows + '):', currentIdx);
        if (target === null) return;
        target = parseInt(target, 10);
        if (isNaN(target) || target < 1 || target > totalRows || target === currentIdx) return;

        var $allRows = $('#productsTable tbody tr[data-product-id]');
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
    $('#productsTable tbody').on('click', 'tr[data-product-id]', function(e) {
        if ($(e.target).closest('a, button, input, select, .drag-handle').length) return;
        $('#productsTable tbody tr').removeClass('row-reorder-selected');
        $(this).addClass('row-reorder-selected');
    });

    $(document).on('keydown', function(e) {
        if (!isSeriesFiltered) return;
        if ($(e.target).is('input, textarea, select')) return;
        var $selected = $('#productsTable tbody tr.row-reorder-selected');
        if (!$selected.length) return;

        if (e.key === 'ArrowUp' || (e.altKey && e.key === 'ArrowUp')) {
            e.preventDefault();
            var $prev = $selected.prev('tr[data-product-id]');
            if ($prev.length) {
                $prev.before($selected);
                highlightAndSave($selected);
            }
        } else if (e.key === 'ArrowDown' || (e.altKey && e.key === 'ArrowDown')) {
            e.preventDefault();
            var $next = $selected.next('tr[data-product-id]');
            if ($next.length) {
                $next.after($selected);
                highlightAndSave($selected);
            }
        }
    });

    // ── Drag & Drop Product Reordering ────────────────────────────
    if (isSeriesFiltered && typeof Sortable !== 'undefined') {
        var tbodyEl = document.querySelector('#productsTable tbody');
        if (tbodyEl) {
            Sortable.create(tbodyEl, {
                handle: '.drag-handle',
                animation: 180,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onStart: function() {
                    if (table.search()) {
                        alert("Note: Search filter is active. Clear search for full series reordering.");
                    }
                },
                onEnd: function() {
                    updateButtonsState();
                    saveProductOrder();
                }
            });
        }
    }

    updateButtonsState();

    function saveProductOrder() {
        if (table.search()) {
            alert("Please clear the search filter before reordering products to preserve all items.");
            return;
        }

        var productIds = [];
        $('#productsTable tbody tr[data-product-id]').each(function(idx) {
            var pid = $(this).attr('data-product-id');
            if (pid) {
                productIds.push(pid);
                $(this).find('.order-badge').text(idx + 1);
            }
        });

        if (productIds.length === 0) return;

        var $spinner = $('#reorderSpinner');
        var $toast = $('#reorderToast');

        if ($spinner.length) $spinner.show();
        if ($toast.length) $toast.hide();

        fetch('reorder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ product_ids: productIds })
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if ($spinner.length) $spinner.hide();
            if (res.success) {
                if ($toast.length) {
                    $toast.stop(true, true).fadeIn(150).delay(2500).fadeOut(600);
                }
            } else {
                alert(res.message || 'Error updating product order');
            }
        })
        .catch(function(err) {
            if ($spinner.length) $spinner.hide();
            console.error('Reorder error:', err);
            alert('Network error while saving product order');
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
