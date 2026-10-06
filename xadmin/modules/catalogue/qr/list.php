<?php
/**
 * Dynamic QR Codes Management List
 * Permanent Parent Links for Printed Catalogues
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();
require_once dirname(__DIR__, 3) . '/includes/catalogue_product_hierarchy.php';
$productHierarchy = catalogueProductHierarchySql(getDBConnection());
$productCategorySql = $productHierarchy['category'];
$productSubCategorySql = $productHierarchy['sub_category'];

$activePage = 'catalogue_qr';
$pageTitle = 'Product QR Codes (Dynamic Catalogue Links)';

// Search & Filter parameters
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? 'all';
$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$seriesId = !empty($_GET['series_id']) ? (int)$_GET['series_id'] : null;

// Domain mode: Default to 'live' for print catalogue generation
$domainMode = $_SESSION['qr_domain_mode'] ?? 'live';
$frontBase = getFrontBaseUrl($domainMode === 'live');
$liveDomainSetting = getFrontBaseUrl(true);
$localDomain = getFrontBaseUrl(false);

// Build query
$where = ["1=1"];
$params = [];

if ($search !== '') {
    $term = "%{$search}%";
    $cleanSearch = preg_replace('/[^a-zA-Z0-9]/', '', $search);
    $cleanTerm = '%' . ($cleanSearch !== '' ? $cleanSearch : $search) . '%';
    $where[] = "(q.code LIKE ? OR q.label LIKE ? OR p.name LIKE ? OR p.code LIKE ? OR REPLACE(p.code, '-', '') LIKE ? OR EXISTS (SELECT 1 FROM catalogue_product_variants v WHERE v.product_id = p.id AND (v.code LIKE ? OR REPLACE(v.code, '-', '') LIKE ?)))";
    $params = array_merge($params, [$term, $term, $term, $term, $cleanTerm, $term, $cleanTerm]);
}

if ($statusFilter === 'linked') {
    $where[] = "q.product_id IS NOT NULL";
} elseif ($statusFilter === 'unlinked') {
    $where[] = "q.product_id IS NULL";
}

if ($seriesId) {
    $where[] = "p.series_id = ?";
    $params[] = $seriesId;
} elseif ($categoryId) {
    $where[] = "{$productCategorySql} = ?";
    $params[] = $categoryId;
}

$whereSql = implode(" AND ", $where);

// Summary Stats
$stats = [
    'total' => fetchOne("SELECT COUNT(*) as c FROM catalogue_qr_codes")['c'] ?? 0,
    'linked' => fetchOne("SELECT COUNT(*) as c FROM catalogue_qr_codes WHERE product_id IS NOT NULL")['c'] ?? 0,
    'scans' => fetchOne("SELECT SUM(scan_count) as c FROM catalogue_qr_codes")['c'] ?? 0,
    'missing' => fetchOne("SELECT COUNT(*) as c FROM catalogue_products WHERE is_active = 1 AND id NOT IN (SELECT product_id FROM catalogue_qr_codes WHERE product_id IS NOT NULL)")['c'] ?? 0
];

// Fetch QR Records
$sql = "SELECT q.*, 
               p.id as prod_id, p.name as prod_name, p.code as prod_code, p.is_active as prod_active,
               s.name as series_name, c.name as category_name,
               (SELECT image_url FROM catalogue_product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as prod_image
        FROM catalogue_qr_codes q
        LEFT JOIN catalogue_products p ON q.product_id = p.id
        LEFT JOIN catalogue_series s ON p.series_id = s.id
        LEFT JOIN catalogue_categories c ON {$productCategorySql} = c.id
        WHERE {$whereSql}
        ORDER BY q.id DESC";

$qrList = fetchAll($sql, $params);

// Fetch categories and series for filters and modals
$categories = fetchAll("SELECT id, name FROM catalogue_categories WHERE is_active = 1 ORDER BY name ASC");
$allSeries = fetchAll("SELECT id, name, category_id FROM catalogue_series WHERE is_active = 1 ORDER BY name ASC");
$allProducts = fetchAll("SELECT p.id, p.name, p.code, s.name as series_name, c.name as category_name 
                        FROM catalogue_products p 
                        LEFT JOIN catalogue_series s ON p.series_id = s.id 
                        JOIN catalogue_categories c ON {$productCategorySql} = c.id 
                        WHERE p.is_active = 1 
                        ORDER BY c.name ASC, s.name ASC, p.name ASC");

// Additional CSS & JS
$additionalCSS = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.qr-preview-box {
    width: 65px;
    height: 65px;
    background: #fff;
    padding: 3px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s;
}
.qr-preview-box:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.qr-preview-box canvas, .qr-preview-box img {
    max-width: 100% !important;
    max-height: 100% !important;
}
.code-badge {
    font-family: monospace;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.permanent-link-pill {
    font-size: 0.8rem;
    color: #495057;
    background: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 3px 8px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    max-width: 100%;
}
.domain-badge-bar {
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    padding: 10px 16px;
}
#bulkActionBar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: #fff;
    padding: 12px 24px;
    border-radius: 50px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.3);
    z-index: 1050;
    display: none;
    align-items: center;
    gap: 16px;
    animation: slideUp 0.25s ease-out;
}
@keyframes slideUp {
    from { bottom: -60px; opacity: 0; }
    to { bottom: 24px; opacity: 1; }
}
</style>';

$additionalJS = '<script src="' . BASE_URL . '/assets/js/qrcode.min.js"></script>
<script src="' . BASE_URL . '/assets/js/qrcode-generator.min.js"></script>
<script src="' . BASE_URL . '/assets/js/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['info'])): ?>
        <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($_SESSION['info']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['info']); ?>
    <?php endif; ?>

    <!-- Header & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="mb-1"><i class="fas fa-qrcode text-primary me-2"></i>Permanent Product QR Codes</h2>
            <p class="text-muted mb-0 small">
                Dynamic redirection system: QR codes stay permanent on paper catalogue, while you can update the destination product anytime!
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <!-- Category/Series Wise Generator Trigger -->
            <button type="button" class="btn btn-warning text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                <i class="fas fa-magic me-1"></i> Generate QR Codes (Category/Series)
            </button>
            <!-- Download ZIP Trigger -->
            <button type="button" class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#zipModal">
                <i class="fas fa-file-archive me-1"></i> Download as ZIP (PNG / JPG)
            </button>
            <a href="print_sheet.php" target="_blank" class="btn btn-outline-dark shadow-sm">
                <i class="fas fa-print me-1"></i> Printable Sheet
            </a>
            <a href="create.php" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus me-1"></i> New QR Code
            </a>
        </div>
    </div>

    <!-- Live Website Domain Switcher Banner -->
    <div class="domain-badge-bar mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary text-uppercase px-2 py-1"><i class="fas fa-globe me-1"></i>QR Target Domain</span>
            <span class="fw-bold text-dark"><?= htmlspecialchars($frontBase) ?></span>
            <?php if ($domainMode === 'live'): ?>
                <span class="badge bg-success bg-opacity-75 text-white">Live Production (Print Ready)</span>
            <?php else: ?>
                <span class="badge bg-secondary text-white">Local Testing</span>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="save_domain.php" method="POST" class="d-inline-flex align-items-center gap-2">
                <input type="hidden" name="live_site_url" value="<?= htmlspecialchars($liveDomainSetting) ?>">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="submit" name="qr_domain_mode" value="live" class="btn btn-sm <?= $domainMode === 'live' ? 'btn-primary' : 'btn-outline-primary' ?>" title="Use Live domain for printed catalogues">
                        <i class="fas fa-satellite-dish me-1"></i> Live Domain
                    </button>
                    <button type="submit" name="qr_domain_mode" value="local" class="btn btn-sm <?= $domainMode === 'local' ? 'btn-dark' : 'btn-outline-dark' ?>" title="Use Localhost for local testing">
                        <i class="fas fa-laptop-code me-1"></i> Localhost
                    </button>
                </div>
            </form>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editDomainModal" title="Edit Live Domain Address">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3">
                        <i class="fas fa-qrcode fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total QR Codes</div>
                        <h4 class="mb-0 fw-bold"><?= number_format($stats['total']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3">
                        <i class="fas fa-link fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Linked Products</div>
                        <h4 class="mb-0 fw-bold"><?= number_format($stats['linked']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3">
                        <i class="fas fa-eye fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Catalogue Scans</div>
                        <h4 class="mb-0 fw-bold"><?= number_format($stats['scans']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle <?= $stats['missing'] > 0 ? 'bg-warning bg-opacity-10 text-warning' : 'bg-secondary bg-opacity-10 text-secondary' ?> p-3 me-3">
                        <i class="fas fa-box-open fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Products Without QR</div>
                        <h4 class="mb-0 fw-bold"><?= number_format($stats['missing']) ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search QR code (e.g. XT-1001), product name or code..." 
                               value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category_id" id="filter_category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="series_id" id="filter_series_id" class="form-select form-select-sm">
                        <option value="">All Series</option>
                        <?php foreach ($allSeries as $ser): ?>
                            <?php if (!$categoryId || $ser['category_id'] == $categoryId): ?>
                                <option value="<?= $ser['id'] ?>" <?= $seriesId == $ser['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($ser['name']) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>All Status</option>
                        <option value="linked" <?= $statusFilter === 'linked' ? 'selected' : '' ?>>Linked to Product</option>
                        <option value="unlinked" <?= $statusFilter === 'unlinked' ? 'selected' : '' ?>>Unlinked / Custom</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark flex-grow-1">Filter</button>
                    <?php if ($search !== '' || $categoryId || $seriesId || $statusFilter !== 'all'): ?>
                        <a href="list.php" class="btn btn-sm btn-outline-secondary" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Table Form (Supports Bulk Select & Delete) -->
    <form action="delete_bulk.php" method="POST" id="bulkForm">
        <div class="card border-0 shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="qrTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th width="80" class="text-center">QR Code</th>
                                <th width="240">Permanent Parent Link</th>
                                <th>Target Destination (Child Link)</th>
                                <th width="90" class="text-center">Scans</th>
                                <th width="80" class="text-center">Status</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($qrList)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-qrcode fa-3x mb-3 text-secondary opacity-50"></i>
                                        <h5>No QR codes found</h5>
                                        <p class="small mb-3">You can click below to automatically generate permanent QR codes for your products.</p>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generateModal">
                                            <i class="fas fa-magic me-1"></i> Generate QR Codes
                                        </button>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($qrList as $qr): ?>
                                    <?php 
                                        $permUrl = $frontBase . '/qr/' . rawurlencode($qr['code']);
                                        $cleanLabel = $qr['label'] ?: $qr['prod_name'] ?: $qr['code'];
                                        $cleanProdCode = $qr['prod_code'] ?? '';
                                    ?>
                                    <tr data-qr-id="<?= $qr['id'] ?>" 
                                        data-qr-code="<?= htmlspecialchars($qr['code']) ?>"
                                        data-qr-url="<?= htmlspecialchars($permUrl) ?>"
                                        data-qr-label="<?= htmlspecialchars($cleanLabel) ?>"
                                        data-prod-code="<?= htmlspecialchars($cleanProdCode) ?>"
                                        data-prod-name="<?= htmlspecialchars($qr['prod_name'] ?? '') ?>"
                                        data-category-name="<?= htmlspecialchars($qr['category_name'] ?? '') ?>"
                                        data-series-name="<?= htmlspecialchars($qr['series_name'] ?? '') ?>">
                                        <!-- Checkbox -->
                                        <td class="text-center">
                                            <input class="form-check-input qr-row-check" type="checkbox" name="qr_ids[]" value="<?= $qr['id'] ?>">
                                        </td>

                                        <!-- QR Image Thumbnail -->
                                        <td class="text-center">
                                            <div class="qr-preview-box mx-auto" 
                                                 data-qr-code="<?= htmlspecialchars($qr['code']) ?>"
                                                 data-qr-url="<?= htmlspecialchars($permUrl) ?>"
                                                 data-qr-label="<?= htmlspecialchars($cleanLabel) ?>"
                                                 data-prod-code="<?= htmlspecialchars($cleanProdCode) ?>"
                                                 title="Click to view full size & download PNG/JPG">
                                                <div id="qr_thumb_<?= $qr['id'] ?>"></div>
                                            </div>
                                        </td>

                                        <!-- Permanent Code & URL -->
                                        <td>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-dark code-badge"><?= htmlspecialchars($qr['code']) ?></span>
                                                <?php if (!empty($qr['label'])): ?>
                                                    <small class="text-muted fw-semibold"><?= htmlspecialchars($qr['label']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="permanent-link-pill" title="<?= htmlspecialchars($permUrl) ?>">
                                                <i class="fas fa-print text-primary" title="Print this URL on catalogue"></i>
                                                <span class="text-truncate" style="max-width: 170px;"><?= htmlspecialchars($permUrl) ?></span>
                                                <button type="button" class="btn btn-link btn-sm p-0 text-secondary copy-btn" 
                                                        data-clipboard="<?= htmlspecialchars($permUrl) ?>" 
                                                        title="Copy permanent link">
                                                    <i class="far fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Target Product / Child Link -->
                                        <td>
                                            <?php if (!empty($qr['prod_id'])): ?>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($qr['prod_image'])): ?>
                                                        <img src="<?= BASE_URL ?>/uploads/catalogue/products/<?= htmlspecialchars($qr['prod_image']) ?>" 
                                                             class="rounded me-2 border" style="width: 42px; height: 42px; object-fit: cover;" alt="">
                                                    <?php else: ?>
                                                        <div class="rounded me-2 border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 42px; height: 42px;">
                                                            <i class="fas fa-image"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold text-dark">
                                                            <?= htmlspecialchars($qr['prod_name']) ?>
                                                            <?php if (!empty($qr['prod_code']) && $qr['prod_code'] !== '—'): ?>
                                                                <span class="badge bg-light text-dark border ms-1"><?= htmlspecialchars($qr['prod_code']) ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            <?= htmlspecialchars($qr['category_name'] ?? '') ?> 
                                                            <?= !empty($qr['series_name']) ? '· ' . htmlspecialchars($qr['series_name']) : '' ?>
                                                        </small>
                                                    </div>

                                                </div>
                                            <?php elseif (!empty($qr['target_url'])): ?>
                                                <div class="text-primary">
                                                    <i class="fas fa-external-link-alt me-1"></i>
                                                    <a href="<?= htmlspecialchars($qr['target_url']) ?>" target="_blank" class="fw-bold text-decoration-none">
                                                        <?= htmlspecialchars($qr['target_url']) ?>
                                                    </a>
                                                </div>
                                                <small class="text-muted">Custom External Link</small>
                                            <?php else: ?>
                                                <span class="text-danger fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Not linked to any product
                                                </span>

                                            <?php endif; ?>
                                        </td>

                                        <!-- Scans -->
                                        <td class="text-center">
                                            <span class="badge bg-light text-dark border px-2 py-1 fs-6">
                                                <?= number_format($qr['scan_count']) ?>
                                            </span>
                                        </td>

                                        <!-- Status -->
                                        <td class="text-center">
                                            <?php if ($qr['is_active']): ?>
                                                <span class="badge bg-success bg-opacity-75">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Disabled</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Actions -->
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary view-qr-modal-btn"
                                                        data-qr-code="<?= htmlspecialchars($qr['code']) ?>"
                                                        data-qr-url="<?= htmlspecialchars($permUrl) ?>"
                                                        data-qr-label="<?= htmlspecialchars($cleanLabel) ?>"
                                                        data-prod-code="<?= htmlspecialchars($cleanProdCode) ?>"
                                                        title="View & Download PNG / JPG">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                <a href="edit.php?id=<?= $qr['id'] ?>" class="btn btn-outline-secondary" title="Edit QR Settings">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger single-delete-btn" 
                                                        data-qr-id="<?= $qr['id'] ?>" 
                                                        data-qr-code="<?= htmlspecialchars($qr['code']) ?>"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Floating Bulk Action Bar (Appears when 1+ checkboxes are selected) -->
        <div id="bulkActionBar" class="shadow-lg">
            <span class="fw-bold"><span id="selectedCountBadge">0</span> selected</span>
            <div class="vr bg-secondary opacity-50"></div>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3" onclick="openBulkZipModal()">
                <i class="fas fa-file-archive me-1"></i> Download Selected (ZIP)
            </button>
            <button type="button" class="btn btn-danger btn-sm rounded-pill px-3" onclick="confirmBulkDelete()">
                <i class="fas fa-trash me-1"></i> Delete Selected
            </button>
            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-2" onclick="clearAllSelections()" title="Deselect all">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </form>
</div>

<!-- ==================== MODALS ==================== -->

<!-- Modal 1: Category / Series Wise QR Code Generator -->
<div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="generate_bulk.php" method="POST" class="modal-content shadow border-0">
            <div class="modal-header bg-warning bg-opacity-25">
                <h5 class="modal-title fw-bold"><i class="fas fa-magic text-warning me-2"></i>Generate QR Codes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">
                    Automatically create permanent QR codes for products. Select whether to generate for all products, a specific category, or a series.
                </p>

                <!-- Scope Selection -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Generation Scope</label>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="scope" id="scope_all" value="all" checked onchange="toggleScopeInputs()">
                        <label class="form-check-label fw-semibold" for="scope_all">
                            All Catalogue Products (only those missing QR codes)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="scope" id="scope_cat" value="category" onchange="toggleScopeInputs()">
                        <label class="form-check-label fw-semibold" for="scope_cat">
                            Category Wise
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="scope" id="scope_series" value="series" onchange="toggleScopeInputs()">
                        <label class="form-check-label fw-semibold" for="scope_series">
                            Series Wise
                        </label>
                    </div>
                </div>

                <!-- Category Dropdown -->
                <div class="mb-3" id="scopeCategoryGroup" style="display:none;">
                    <label class="form-label fw-bold">Select Category</label>
                    <select name="category_id" id="gen_category_id" class="form-select">
                        <option value="">-- Choose Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Series Dropdown -->
                <div class="mb-3" id="scopeSeriesGroup" style="display:none;">
                    <label class="form-label fw-bold">Select Series</label>
                    <select name="series_id" id="gen_series_id" class="form-select">
                        <option value="">-- Choose Series --</option>
                        <?php foreach ($allSeries as $ser): ?>
                            <option value="<?= $ser['id'] ?>" data-cat="<?= $ser['category_id'] ?>">
                                <?= htmlspecialchars($ser['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Custom Code Prefix -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Code Prefix</label>
                    <input type="text" name="prefix" class="form-control font-monospace" value="XT-" placeholder="e.g. XT-">
                    <div class="form-text text-muted">Codes will be generated sequentially: <code>XT-1001</code>, <code>XT-1002</code>, etc.</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold">
                    <i class="fas fa-check me-1"></i> Start Generation
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Download ZIP (PNG & JPG Format Options) -->
<div class="modal fade" id="zipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-archive me-2"></i>Download QR Codes as ZIP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Which QR codes to include?</label>
                    <div class="form-check mb-2" id="zipSelectedOption" style="display: none;">
                        <input class="form-check-input" type="radio" name="zip_scope" id="zip_scope_selected" value="selected" checked>
                        <label class="form-check-label fw-semibold" for="zip_scope_selected">
                            Selected QR codes only (<span id="zipSelectedCount">0</span> items)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="zip_scope" id="zip_scope_visible" value="visible">
                        <label class="form-check-label fw-semibold" for="zip_scope_visible">
                            All currently filtered QR codes (<?= count($qrList) ?> items)
                        </label>
                    </div>
                </div>

                <!-- Format Selection: PNG, JPG or SVG -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Image Format</label>
                    <div class="d-flex flex-wrap gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="zip_format" id="zip_format_png" value="png" checked>
                            <label class="form-check-label fw-semibold" for="zip_format_png">
                                <i class="far fa-file-image text-primary me-1"></i> PNG (Transparent Background — No White Box)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="zip_format" id="zip_format_jpg" value="jpg">
                            <label class="form-check-label fw-semibold" for="zip_format_jpg">
                                <i class="fas fa-file-image text-danger me-1"></i> JPG (White Background)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="zip_format" id="zip_format_svg" value="svg">
                            <label class="form-check-label fw-semibold" for="zip_format_svg">
                                <i class="fas fa-bezier-curve text-success me-1"></i> SVG (Transparent Vector Cutout)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Text / Caption Option: Only QR or With Text -->
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="form-label fw-bold mb-1"><i class="fas fa-font text-primary me-1"></i> QR Code Text / Caption</label>
                    <div class="d-flex flex-wrap gap-4 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="zip_include_text" id="zip_text_no" value="no" checked>
                            <label class="form-check-label fw-semibold" for="zip_text_no">
                                <i class="fas fa-qrcode text-success me-1"></i> <strong>Only QR Code</strong> (Without any text / Pure QR) — Recommended
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="zip_include_text" id="zip_text_yes" value="yes">
                            <label class="form-check-label fw-semibold" for="zip_text_yes">
                                <i class="fas fa-signature text-secondary me-1"></i> With Text (Product Name &amp; Code at bottom)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Folder Structure: Category & Series wise -->
                <div class="mb-3 p-3 bg-light rounded border">
                    <label class="form-label fw-bold mb-1"><i class="fas fa-folder-tree text-warning me-1"></i> Folder Structure in ZIP</label>
                    <div class="form-check mt-1">
                        <input class="form-check-input" type="checkbox" id="zip_organize_folders" checked>
                        <label class="form-check-label fw-semibold" for="zip_organize_folders">
                            Organize inside Category &amp; Series folders
                            <small class="d-block text-muted">Example: <code>Sanitaryware/Amaze Series/AMAZE - 9 (XT-1001).png</code></small>
                        </label>
                    </div>
                </div>

                <!-- File Naming Style: Product Name primarily -->
                <div class="mb-3">
                    <label class="form-label fw-bold">File Naming Format</label>
                    <select id="zip_naming_style" class="form-select">
                        <option value="name_code" selected>Product Name + Code (e.g. AMAZE - 9 (XT-1001).png) — Recommended</option>
                        <option value="name_only">Product Name Only (e.g. AMAZE - 9.png)</option>
                        <option value="code_name">Code + Product Name (e.g. XT-1001 - AMAZE - 9.png)</option>
                    </select>
                </div>

                <!-- Resolution Selection -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Print Resolution</label>
                    <select id="zip_resolution" class="form-select">
                        <option value="1200" selected>Print-Ready High-Res (1200 x 1200 px / 300 DPI) — Recommended for Catalogues</option>
                        <option value="600">Standard Resolution (600 x 600 px)</option>
                    </select>
                </div>

                <!-- Progress Bar (shown while zipping) -->
                <div id="zipProgressWrapper" style="display: none;" class="mt-4">
                    <label class="small text-muted fw-bold d-flex justify-content-between">
                        <span id="zipStatusText">Generating QR images...</span>
                        <span id="zipPercentText">0%</span>
                    </label>
                    <div class="progress" style="height: 12px;">
                        <div id="zipProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 0%"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" id="zipCancelBtn">Cancel</button>
                <button type="button" class="btn btn-success btn-sm fw-bold px-4" id="startZipBtn" onclick="runZipGeneration()">
                    <i class="fas fa-download me-1"></i> Start ZIP Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal 3: Single QR Code View & Download (JPG & PNG options) -->
<div class="modal fade" id="qrViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="qrModalTitle">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="p-3 bg-white d-inline-block border rounded shadow-sm mb-3" style="max-width: 280px;">
                    <div id="modal_qr_canvas"></div>
                    <div class="mt-2 text-center">
                        <div id="modal_qr_caption" class="fw-bold text-dark fs-6"></div>
                        <span id="modal_qr_code" class="badge bg-secondary mt-1"></span>
                    </div>
                </div>

                <div class="mb-3 text-start">
                    <small class="text-muted d-block mb-1 fw-bold">Permanent URL on Catalogue:</small>
                    <code id="modal_qr_url" class="p-2 bg-light rounded text-break d-block" style="font-size: 0.8rem;"></code>
                </div>

                <!-- Text Option Toggle for Single Download -->
                <div class="d-flex align-items-center justify-content-between p-2 mb-3 bg-light border rounded">
                    <div class="text-start">
                        <span class="d-block fw-bold small text-dark"><i class="fas fa-font text-primary me-1"></i> Text below QR code:</span>
                        <small class="text-success fw-semibold" id="modalTextHelper">Only QR Code (Without any text)</small>
                    </div>
                    <div class="form-check form-switch m-0 fs-5">
                        <input class="form-check-input" type="checkbox" id="modalIncludeTextSwitch" onchange="toggleModalTextSwitch(this)">
                    </div>
                </div>

                <!-- Download Options in both PNG and JPG -->
                <div class="card bg-light border-0 p-3 mb-2 text-start">
                    <label class="fw-bold small mb-2"><i class="fas fa-download me-1"></i>Download Single Image (Product Name as filename):</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 text-truncate" onclick="downloadModalQr(1200, 'png')">
                                <i class="far fa-file-image me-1"></i> <strong>PNG</strong> Transparent (1200px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 text-truncate" onclick="downloadModalQr(1200, 'jpg')">
                                <i class="fas fa-file-image me-1"></i> <strong>JPG</strong> White BG (1200px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-truncate" onclick="downloadModalQr(300, 'png')">
                                PNG Transparent (300px)
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100 text-truncate" onclick="downloadModalQr(300, 'jpg')">
                                JPG White BG (300px)
                            </button>
                        </div>
                        <div class="col-12 mt-1">
                            <button type="button" class="btn btn-outline-success btn-sm w-100" onclick="downloadModalQr(0, 'svg')">
                                <i class="fas fa-bezier-curve me-1"></i> <strong>SVG Vector</strong> (Transparent Cutout for CorelDraw / InDesign)
                            </button>
                        </div>
                    </div>
                </div>
                <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">
                    PNG and SVG are exported with transparent background so you can place them seamlessly on colored or wooden catalogue pages.
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Modal 4: Edit Live Website Domain Setting -->
<div class="modal fade" id="editDomainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="save_domain.php" method="POST" class="modal-content shadow border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fas fa-globe text-primary me-2"></i>Configure Live Website Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">
                    This domain is encoded into your printed QR codes. When printed in your catalogue, customers' phones will scan this exact domain address.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Live Website Address <span class="text-danger">*</span></label>
                    <input type="text" name="live_site_url" class="form-control" value="<?= htmlspecialchars($liveDomainSetting) ?>" required placeholder="https://x-tral.com">
                    <div class="form-text text-muted">Example: <code>https://x-tral.com</code></div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save me-1"></i> Save Live Domain</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Single Delete Confirmation (Clean UI, No Native Browser Dialog) -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="fas fa-trash-alt fa-3x opacity-75"></i>
                </div>
                <h5 class="fw-bold mb-1">Delete QR Code?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to delete <strong id="delete_modal_code_display" class="text-dark"></strong>?
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" id="confirmSingleDeleteBtn">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Bulk Delete Confirmation (Clean UI, No Native Browser Dialog) -->
<div class="modal fade" id="bulkDeleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow border-0">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3">
                    <i class="fas fa-trash-alt fa-3x opacity-75"></i>
                </div>
                <h5 class="fw-bold mb-1">Delete Selected?</h5>
                <p class="text-muted small mb-3">
                    Are you sure you want to delete <strong id="bulk_delete_count_text" class="text-danger"></strong> selected QR code(s)?
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-light btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger btn-sm px-3 fw-bold" onclick="document.getElementById('bulkForm').submit();">
                        Delete All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Single Delete Form (Hidden) -->
<form id="singleDeleteForm" action="delete.php" method="POST" style="display:none;">
    <input type="hidden" name="id" id="single_delete_id">
</form>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // 1. Render thumbnail QR codes in table
    document.querySelectorAll(".qr-preview-box").forEach(box => {
        const url = box.getAttribute("data-qr-url");
        const container = box.firstElementChild;
        if (url && container) {
            new QRCode(container, {
                text: url,
                width: 60,
                height: 60,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });

    // 2. Copy permanent link button
    document.querySelectorAll(".copy-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            const text = this.getAttribute("data-clipboard");
            navigator.clipboard.writeText(text).then(() => {
                const icon = this.querySelector("i");
                icon.className = "fas fa-check text-success";
                setTimeout(() => { icon.className = "far fa-copy"; }, 1800);
            });
        });
    });

    // 3. Select All & Checkbox management
    const selectAllCheckbox = document.getElementById("selectAll");
    const rowCheckboxes = document.querySelectorAll(".qr-row-check");
    const bulkBar = document.getElementById("bulkActionBar");
    const selectedBadge = document.getElementById("selectedCountBadge");

    function updateBulkBar() {
        const checkedCount = document.querySelectorAll(".qr-row-check:checked").length;
        selectedBadge.textContent = checkedCount;
        if (checkedCount > 0) {
            bulkBar.style.display = "flex";
            document.getElementById("zipSelectedOption").style.display = "block";
            document.getElementById("zip_scope_selected").checked = true;
            document.getElementById("zipSelectedCount").textContent = checkedCount;
        } else {
            bulkBar.style.display = "none";
            document.getElementById("zipSelectedOption").style.display = "none";
            document.getElementById("zip_scope_visible").checked = true;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", function () {
            rowCheckboxes.forEach(cb => { cb.checked = selectAllCheckbox.checked; });
            updateBulkBar();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener("change", function () {
            if (!this.checked) selectAllCheckbox.checked = false;
            updateBulkBar();
        });
    });

    window.clearAllSelections = function () {
        if (selectAllCheckbox) selectAllCheckbox.checked = false;
        rowCheckboxes.forEach(cb => { cb.checked = false; });
        updateBulkBar();
    };

    // 4. Bulk Delete Confirmation (Clean in-page modal, no browser alert popup)
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteConfirmModal'));
    window.confirmBulkDelete = function () {
        const checked = document.querySelectorAll(".qr-row-check:checked");
        if (checked.length === 0) return;
        document.getElementById("bulk_delete_count_text").textContent = checked.length;
        bulkDeleteModal.show();
    };

    // 5. Single Delete (Clean in-page modal, no browser alert popup)
    const singleDeleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    document.querySelectorAll(".single-delete-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-qr-id");
            const code = this.getAttribute("data-qr-code");
            document.getElementById("single_delete_id").value = id;
            document.getElementById("delete_modal_code_display").textContent = code;
            singleDeleteModal.show();
        });
    });

    document.getElementById("confirmSingleDeleteBtn").addEventListener("click", function () {
        document.getElementById("singleDeleteForm").submit();
    });

    // 6. Scope inputs toggle in Generation Modal
    window.toggleScopeInputs = function () {
        const scope = document.querySelector('input[name="scope"]:checked').value;
        const catGroup = document.getElementById('scopeCategoryGroup');
        const seriesGroup = document.getElementById('scopeSeriesGroup');

        if (scope === 'all') {
            catGroup.style.display = 'none';
            seriesGroup.style.display = 'none';
        } else if (scope === 'category') {
            catGroup.style.display = 'block';
            seriesGroup.style.display = 'none';
        } else if (scope === 'series') {
            catGroup.style.display = 'block';
            seriesGroup.style.display = 'block';
        }
    };

    // Filter series dropdown when category is chosen in generation modal
    const genCatSelect = document.getElementById('gen_category_id');
    const genSeriesSelect = document.getElementById('gen_series_id');
    if (genCatSelect && genSeriesSelect) {
        genCatSelect.addEventListener('change', function () {
            const catId = this.value;
            Array.from(genSeriesSelect.options).forEach(opt => {
                if (!opt.value) return;
                const optCat = opt.getAttribute('data-cat');
                opt.style.display = (!catId || optCat === catId) ? '' : 'none';
            });
            genSeriesSelect.value = '';
        });
    }

    // 7. Single QR View Modal
    const qrViewModal = new bootstrap.Modal(document.getElementById('qrViewModal'));
    let currentModalData = {};

    function openQrModal(code, url, label, prodCode, prodName) {
        currentModalData = { code, url, label, prodCode, prodName };
        const displayTitle = prodName || label || code;
        document.getElementById('qrModalTitle').textContent = `QR: ${displayTitle}`;
        document.getElementById('modal_qr_code').textContent = code;
        document.getElementById('modal_qr_caption').textContent = displayTitle;
        document.getElementById('modal_qr_url').textContent = url;

        // Reset text switch to "Only QR (No Text)" by default
        const textSwitch = document.getElementById('modalIncludeTextSwitch');
        if (textSwitch) {
            textSwitch.checked = false;
            toggleModalTextSwitch(textSwitch);
        }

        const container = document.getElementById('modal_qr_canvas');
        container.innerHTML = '';
        new QRCode(container, {
            text: url,
            width: 240,
            height: 240,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        qrViewModal.show();
    }

    window.toggleModalTextSwitch = function(el) {
        const helper = document.getElementById('modalTextHelper');
        if (helper) {
            helper.textContent = el.checked ? 'With Product Name & Code text' : 'Only QR Code (Without any text)';
            helper.className = el.checked ? 'text-primary fw-semibold small' : 'text-success fw-semibold small';
        }
    };

    document.querySelectorAll('.qr-preview-box, .view-qr-modal-btn').forEach(el => {
        el.addEventListener('click', function () {
            const tr = this.closest('tr');
            const code = this.getAttribute('data-qr-code') || tr.getAttribute('data-qr-code');
            const url = this.getAttribute('data-qr-url') || tr.getAttribute('data-qr-url');
            const label = this.getAttribute('data-qr-label') || tr.getAttribute('data-qr-label');
            const prodCode = this.getAttribute('data-prod-code') || tr.getAttribute('data-prod-code');
            const prodName = tr ? tr.getAttribute('data-prod-name') : '';
            openQrModal(code, url, label, prodCode, prodName);
        });
    });

    // Helper: Clean path/filename parts to be 100% Windows and ZIP standard compliant
    function sanitizeZipPathPart(str) {
        if (!str) return '';
        let clean = str
            .replace(/["'\\/?:*|<>]/g, '')     // Illegal Windows / zip filename chars
            .replace(/[\x00-\x1F\x7F]/g, '')    // Control characters
            .trim()
            .replace(/[. ]+$/, '');             // Strip trailing periods and spaces (illegal in Windows)

        // Expand "CON." to "CONCEALED" (CON is reserved in Windows; CON. in plumbing = Concealed)
        clean = clean.replace(/^CON\.\s*/i, 'CONCEALED ');

        // Protect against any Windows reserved DOS device names (CON, PRN, AUX, NUL, COM1-9, LPT1-9)
        if (/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(\.|$|\s)/i.test(clean)) {
            clean = '_' + clean;
        }
        return clean;
    }

    // Helper: Filename builder with Product Name primarily
    function getQrFileName(prodName, code, style = 'name_code', ext = 'png') {
        const cleanName = sanitizeZipPathPart(prodName);
        const cleanCode = sanitizeZipPathPart(code) || 'QR';

        let baseName = '';
        if (style === 'name_only') {
            baseName = cleanName || cleanCode;
        } else if (style === 'code_name') {
            baseName = `${cleanCode}${cleanName ? ' - ' + cleanName : ''}`;
        } else {
            // default: name_code (Product Name primarily, then code)
            baseName = `${cleanName ? cleanName + ' (' + cleanCode + ')' : cleanCode}`;
        }
        return `${baseName}.${ext}`;
    }

    // Helper: Render canvas with transparent PNG support and optional caption text
    function renderCustomCanvas(url, code, prodName, prodCode, size, format, includeText, callback) {
        if (typeof includeText === 'function') {
            callback = includeText;
            includeText = false;
        }
        if (typeof qrcode !== 'function') {
            callback(null);
            return;
        }

        const qr = qrcode(0, 'H');
        qr.addData(url);
        qr.make();
        const count = qr.getModuleCount();
        const margin = 2; // quiet zone
        const totalModules = count + margin * 2;
        const cellSize = Math.floor(size / totalModules);
        const qrActualSize = cellSize * totalModules;

        const padding = Math.round(size * 0.04);
        const captionHeight = includeText ? Math.round(size * 0.16) : 0;

        const finalCanvas = document.createElement("canvas");
        finalCanvas.width = qrActualSize + (padding * 2);
        finalCanvas.height = qrActualSize + (padding * 2) + captionHeight;
        const ctx = finalCanvas.getContext("2d");

        // 1. Background (JPG = white, PNG = 100% transparent cutout)
        if (format === 'jpg') {
            ctx.fillStyle = "#ffffff";
            ctx.fillRect(0, 0, finalCanvas.width, finalCanvas.height);
        } else {
            ctx.clearRect(0, 0, finalCanvas.width, finalCanvas.height);
        }

        // 2. Draw QR code modules (dark only)
        ctx.fillStyle = "#000000";
        for (let r = 0; r < count; r++) {
            for (let c = 0; c < count; c++) {
                if (qr.isDark(r, c)) {
                    ctx.fillRect(
                        padding + (c + margin) * cellSize,
                        padding + (r + margin) * cellSize,
                        cellSize,
                        cellSize
                    );
                }
            }
        }

        // 3. Draw text ONLY IF includeText is true
        if (includeText) {
            const displayName = (prodName && prodName.trim()) ? prodName.trim() : code;
            ctx.fillStyle = "#111827";
            ctx.font = `bold ${Math.round(size * 0.046)}px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`;
            ctx.textAlign = "center";
            ctx.fillText(displayName.slice(0, 36), finalCanvas.width / 2, padding + qrActualSize + Math.round(size * 0.065));

            let subText = code;
            if (prodCode && prodCode !== '—') subText += ` • ${prodCode}`;
            ctx.fillStyle = "#4b5563";
            ctx.font = `500 ${Math.round(size * 0.034)}px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif`;
            ctx.fillText(subText, finalCanvas.width / 2, padding + qrActualSize + Math.round(size * 0.12));
        }

        callback(finalCanvas);
    }

    // SVG Vector generator helper (pure vector, lossless, transparent cutout, optional text)
    function generateSvgString(url, code, prodName, prodCode, isTransparent = true, includeText = false) {
        if (typeof qrcode !== 'function') return null;
        const qr = qrcode(0, 'H');
        qr.addData(url);
        qr.make();
        const count = qr.getModuleCount();
        const margin = 2;
        const cellSize = 10;
        const size = (count + margin * 2) * cellSize;
        const extraBottom = includeText ? 70 : 0;
        const totalHeight = size + extraBottom;

        let svg = `<?xml version="1.0" encoding="UTF-8"?>\n<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${totalHeight}" width="${size}" height="${totalHeight}">`;
        if (!isTransparent) {
            svg += `\n<rect width="100%" height="100%" fill="#ffffff"/>`;
        }

        for (let r = 0; r < count; r++) {
            for (let c = 0; c < count; c++) {
                if (qr.isDark(r, c)) {
                    const x = (c + margin) * cellSize;
                    const y = (r + margin) * cellSize;
                    svg += `\n<rect x="${x}" y="${y}" width="${cellSize}" height="${cellSize}" fill="#000000"/>`;
                }
            }
        }

        if (includeText) {
            const displayName = (prodName && prodName.trim()) ? prodName.trim() : code;
            const safeName = displayName.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').slice(0, 36);
            svg += `\n<text x="${size / 2}" y="${size + 30}" font-family="sans-serif" font-size="20" font-weight="bold" fill="#111827" text-anchor="middle">${safeName}</text>`;

            let subText = code;
            if (prodCode && prodCode !== '—') subText += ` • ${prodCode}`;
            const safeSub = subText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            svg += `\n<text x="${size / 2}" y="${size + 54}" font-family="sans-serif" font-size="14" font-weight="500" fill="#4b5563" text-anchor="middle">${safeSub}</text>`;
        }

        svg += `\n</svg>`;
        return svg;
    }

    // 8. Download Single Modal QR (PNG with transparent background, JPG or SVG, optional text)
    window.downloadModalQr = function (size, format) {
        if (!currentModalData.url) return;
        const includeText = document.getElementById('modalIncludeTextSwitch') ? document.getElementById('modalIncludeTextSwitch').checked : false;

        const fileName = getQrFileName(currentModalData.prodName || currentModalData.label, currentModalData.code, 'name_code', format);

        if (format === 'svg') {
            const svgContent = generateSvgString(currentModalData.url, currentModalData.code, currentModalData.prodName || currentModalData.label, currentModalData.prodCode, true, includeText);
            if (!svgContent) return;
            const blob = new Blob([svgContent], { type: "image/svg+xml;charset=utf-8" });
            const a = document.createElement("a");
            a.download = fileName;
            a.href = URL.createObjectURL(blob);
            a.click();
            setTimeout(() => URL.revokeObjectURL(a.href), 3000);
            return;
        }

        renderCustomCanvas(currentModalData.url, currentModalData.code, currentModalData.prodName || currentModalData.label, currentModalData.prodCode, size, format, includeText, function (finalCanvas) {
            if (!finalCanvas) return;
            const mimeType = format === 'jpg' ? 'image/jpeg' : 'image/png';
            const a = document.createElement("a");
            a.download = fileName;
            a.href = finalCanvas.toDataURL(mimeType, 0.95);
            a.click();
        });
    };



    // 10. ZIP Modal and Bulk ZIP generation (Category/Series Folder Wise, Product Name First, Transparent PNG, Option for with/without text)
    const zipModal = new bootstrap.Modal(document.getElementById('zipModal'));
    window.openBulkZipModal = function () {
        zipModal.show();
    };

    window.runZipGeneration = async function () {
        const scope = document.querySelector('input[name="zip_scope"]:checked').value;
        const format = document.querySelector('input[name="zip_format"]:checked').value;
        const resolution = parseInt(document.getElementById('zip_resolution').value, 10) || 1200;
        const organizeFolders = document.getElementById('zip_organize_folders') ? document.getElementById('zip_organize_folders').checked : true;
        const namingStyle = document.getElementById('zip_naming_style') ? document.getElementById('zip_naming_style').value : 'name_code';
        const includeTextRadio = document.querySelector('input[name="zip_include_text"]:checked');
        const includeText = includeTextRadio ? (includeTextRadio.value === 'yes') : false;

        let rowsToExport = [];
        if (scope === 'selected') {
            document.querySelectorAll(".qr-row-check:checked").forEach(cb => {
                const tr = cb.closest("tr");
                if (tr) rowsToExport.push(tr);
            });
        } else {
            rowsToExport = Array.from(document.querySelectorAll("#qrTable tbody tr[data-qr-code]"));
        }

        if (rowsToExport.length === 0) {
            alert("No QR codes to export.");
            return;
        }

        // Show progress bar
        const progressWrapper = document.getElementById("zipProgressWrapper");
        const progressBar = document.getElementById("zipProgressBar");
        const percentText = document.getElementById("zipPercentText");
        const statusText = document.getElementById("zipStatusText");
        const startBtn = document.getElementById("startZipBtn");

        progressWrapper.style.display = "block";
        startBtn.disabled = true;

        const zip = new JSZip();
        const mimeType = format === 'jpg' ? 'image/jpeg' : 'image/png';
        const ext = format === 'jpg' ? 'jpg' : (format === 'svg' ? 'svg' : 'png');

        let csvManifest = "Code,Product Name,Product Code,Category,Series,Folder Path,File Name,Permanent URL\n";

        // Map to ensure 100% unique filenames per folder so WinRAR/Windows never has collisions
        const usedPaths = new Set();
        function getUniqueFileName(folderKey, originalFileName) {
            const fullKey = (folderKey ? folderKey + '/' : '') + originalFileName.toLowerCase();
            if (!usedPaths.has(fullKey)) {
                usedPaths.add(fullKey);
                return originalFileName;
            }
            const dotIdx = originalFileName.lastIndexOf('.');
            const base = dotIdx !== -1 ? originalFileName.slice(0, dotIdx) : originalFileName;
            const extension = dotIdx !== -1 ? originalFileName.slice(dotIdx) : '';
            let counter = 2;
            while (true) {
                const candidate = `${base}_${counter}${extension}`;
                const candidateKey = (folderKey ? folderKey + '/' : '') + candidate.toLowerCase();
                if (!usedPaths.has(candidateKey)) {
                    usedPaths.add(candidateKey);
                    return candidate;
                }
                counter++;
            }
        }

        for (let i = 0; i < rowsToExport.length; i++) {
            const tr = rowsToExport[i];
            const code = tr.getAttribute("data-qr-code") || "";
            const url = tr.getAttribute("data-qr-url") || "";
            const prodCode = tr.getAttribute("data-prod-code") || "";
            const prodName = tr.getAttribute("data-prod-name") || "";
            const catName = tr.getAttribute("data-category-name") || "";
            const serName = tr.getAttribute("data-series-name") || "";

            const percent = Math.round(((i + 1) / rowsToExport.length) * 90);
            progressBar.style.width = percent + "%";
            percentText.textContent = percent + "%";
            statusText.textContent = `Rendering QR ${i + 1} of ${rowsToExport.length}: ${prodName || code}...`;

            // Clean folder structure inside ZIP using zip.folder() to register proper directory headers
            let targetZip = zip;
            let folderKey = "";
            let folderDisplay = "";
            if (organizeFolders) {
                const cleanCat = sanitizeZipPathPart(catName) || 'Uncategorized';
                const cleanSeries = sanitizeZipPathPart(serName) || 'General';
                targetZip = zip.folder(cleanCat).folder(cleanSeries);
                folderKey = `${cleanCat}/${cleanSeries}`;
                folderDisplay = `${cleanCat}/${cleanSeries}/`;
            }

            // Filename: Product Name primarily, sanitized & guaranteed unique
            const rawFileName = getQrFileName(prodName, code, namingStyle, ext);
            const fileName = getUniqueFileName(folderKey, rawFileName);

            if (format === 'svg') {
                const svgStr = generateSvgString(url, code, prodName, prodCode, true, includeText);
                if (svgStr) {
                    targetZip.file(fileName, svgStr);
                }
            } else {
                await new Promise((resolve) => {
                    renderCustomCanvas(url, code, prodName, prodCode, resolution, format, includeText, function (finalCanvas) {
                        if (finalCanvas) {
                            try {
                                const dataUrl = finalCanvas.toDataURL(mimeType, 0.95);
                                const parts = dataUrl.split(',');
                                if (parts.length > 1 && parts[1] && parts[1].length > 50) {
                                    targetZip.file(fileName, parts[1], { base64: true, binary: true });
                                } else {
                                    console.warn("Invalid canvas base64 data for QR " + code);
                                }
                            } catch (e) {
                                console.error("Error generating canvas for " + code, e);
                            }
                        }
                        resolve();
                    });
                });
            }

            csvManifest += `"${code}","${(prodName || '').replace(/"/g, '""')}","${prodCode}","${(catName || '').replace(/"/g, '""')}","${(serName || '').replace(/"/g, '""')}","${folderDisplay}","${fileName}","${url}"\n`;

            // Yield to browser event loop so UI updates and memory is freed
            if (i % 2 === 0) {
                await new Promise(r => setTimeout(r, 0));
            }
        }

        // Add CSV manifest to zip
        zip.file("catalogue_qr_manifest.csv", csvManifest);

        statusText.textContent = "Compressing ZIP archive (DEFLATE)...";
        progressBar.style.width = "95%";
        percentText.textContent = "95%";

        const content = await zip.generateAsync({
            type: "blob",
            compression: "DEFLATE",
            compressionOptions: { level: 6 },
            platform: "DOS"
        });

        progressBar.style.width = "100%";
        percentText.textContent = "100%";
        statusText.textContent = "Download starting...";

        const link = document.createElement("a");
        link.href = URL.createObjectURL(content);
        const timeStamp = new Date().toISOString().slice(0, 10);
        link.download = `XTRAL_Catalogue_QR_${format.toUpperCase()}_${timeStamp}.zip`;
        link.click();

        setTimeout(() => {
            progressWrapper.style.display = "none";
            startBtn.disabled = false;
            zipModal.hide();
        }, 1200);
    };
});
</script>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
