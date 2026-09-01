<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

$pageTitle = "Catalogue Tools";
$activePage = 'catalogue_tools';

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-md-12">
            <h2><i class="fas fa-tools me-2"></i><?= $pageTitle ?></h2>
            <p class="text-muted">Manage your product catalogue via bulk operations.</p>
        </div>
    </div>

    <div class="row">
        <!-- Import Tool -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="custom-card-title mb-0"><i class="fas fa-file-import me-2"></i>Bulk Import</h5>
                </div>
                <div class="card-body">
                    <p>Add new products or update existing ones using a CSV file.</p>
                    <ul class="small">
                        <li><strong>Step 1:</strong> Create Categories, SubCategories & Series in admin (with images)</li>
                        <li><strong>Step 2:</strong> Export Structure template (from Export page)</li>
                        <li><strong>Step 3:</strong> Fill product data in Excel & import</li>
                        <li>Supports Dual Zone Pricing & image import via path or URL</li>
                    </ul>
                    <a href="import.php" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Go to Import
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Tool -->
        <div class="col-md-6 mb-4">
            <div class="card h-100 shadow-sm border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="custom-card-title mb-0"><i class="fas fa-file-export me-2"></i>Export & Templates</h5>
                </div>
                <div class="card-body">
                    <p>Download structure templates or export product data for editing.</p>
                    <ul class="small">
                        <li><strong>Export Structure:</strong> Get hierarchy names pre-filled for creating import CSV</li>
                        <li><strong>Export Products:</strong> Download products for price updates or backup</li>
                        <li>Filter by Main Category</li>
                    </ul>
                    <a href="export.php" class="btn btn-success">
                        <i class="fas fa-download me-2"></i>Go to Export
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
