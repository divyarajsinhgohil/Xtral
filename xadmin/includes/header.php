<!DOCTYPE html>
<?php
// Role helpers for nav visibility.
// Panel trimmed 2026-07-04 to Catalogue + Configuration + Admin Users; all
// other modules (CRM, orders, dispatch, complaints, marketing, messaging …)
// were permanently deleted — files and DB tables. Pre-drop dump:
// backups/xtral_full_backup_before_module_drop_2026-07-04.sql
$_navRole = getAdminRole();
$_isSuperAdmin = $_navRole === 'super_admin';
$_canCatalogue = in_array($_navRole, ['super_admin', 'sales_team']);
// Company logo (Configuration → Company Info) with static fallback
$_adminLogo = getSetting('company_logo');
$_adminLogoUrl = $_adminLogo
    ? BASE_URL . '/uploads/settings/' . rawurlencode($_adminLogo)
    : BASE_URL . '/uploads/catalogue/products/logo_full.png';
// Flash message from session
$_flashError = $_SESSION['flash_error'] ?? null;
$_flashSuccess = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'X-Tral Admin' ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/xtral_favicon_32.png">
    <link rel="icon" type="image/png" sizes="256x256" href="<?= BASE_URL ?>/assets/images/xtral_favicon_256.png">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= filemtime(dirname(__DIR__) . '/assets/css/custom.css') ?>" rel="stylesheet">

    <!-- Base path for JS (AJAX/image URLs) — works at any deploy location -->
    <script>window.XADMIN_BASE = '<?= BASE_URL ?>';</script>

    <?= $additionalCSS ?? '' ?>
</head>

<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient-primary">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/modules/dashboard/index.php">
                <img src="<?= $_adminLogoUrl ?>" alt="X-Tral" class="admin-brand-logo" width="180" height="48">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item me-2">
                        <span class="navbar-text text-white d-flex align-items-center gap-2">
                            <i class="fas fa-user-circle"></i>
                            <span><?= htmlspecialchars(getAdminName()) ?></span>
                            <span class="badge bg-white text-dark fw-normal" style="font-size:0.7rem;">
                                <?= htmlspecialchars(getRoleLabel($_navRole)) ?>
                            </span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/modules/auth/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="grid-container">
        <!-- Sidebar -->
        <div class="sidebar bg-dark" id="sidebar">
            <div class="sidebar-sticky">
                <ul class="nav flex-column accordion" id="sidebarAccordion">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>"
                            href="<?= BASE_URL ?>/modules/dashboard/index.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>

                                        <!-- Catalogue Section -->
                    <?php if ($_canCatalogue): ?>
                        <li class="nav-item">
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#catalogueMenu"
                                aria-expanded="<?= in_array($activePage, ['catalogue_categories', 'catalogue_sub_categories', 'catalogue_series', 'catalogue_products', 'catalogue_tools', 'catalogue_colors', 'catalogue_banners', 'catalogue_features', 'whatsapp_catalogues', 'catalogue_qr']) ? 'true' : 'false' ?>">
                                <span><i class="fas fa-book-open me-2"></i>Catalogue</span>
                                <i class="fas fa-chevron-down small"></i>
                            </a>
                            <div class="collapse <?= in_array($activePage, ['catalogue_categories', 'catalogue_sub_categories', 'catalogue_series', 'catalogue_products', 'catalogue_tools', 'catalogue_colors', 'catalogue_banners', 'catalogue_features', 'whatsapp_catalogues', 'catalogue_qr']) ? 'show' : '' ?>"
                                id="catalogueMenu" data-bs-parent="#sidebarAccordion">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_tools' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/tools/index.php">
                                            <i class="fas fa-wrench me-2"></i>Import / Export
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_colors' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/colors/list.php">
                                            <i class="fas fa-palette me-2"></i>Color Library
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_features' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/features/list.php">
                                            <i class="fas fa-star text-warning me-2"></i>Feature Icons
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_categories' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/categories/list.php">
                                            <i class="fas fa-list me-2"></i>Main Categories
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_sub_categories' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/sub_categories/list.php">
                                            <i class="fas fa-stream me-2"></i>Sub Categories
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_series' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/series/list.php">
                                            <i class="fas fa-layer-group me-2"></i>Series
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_products' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/products/list.php">
                                            <i class="fas fa-box-open me-2"></i>Products
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_qr' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/qr/list.php">
                                            <i class="fas fa-qrcode me-2"></i>Product QR Codes
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'catalogue_banners' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/catalogue/banners/list.php">
                                            <i class="fas fa-images me-2"></i>Home Screen Banners
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'whatsapp_catalogues' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/settings/whatsapp_catalogues.php">
                                            <i class="fas fa-file-pdf me-2"></i>Website Catalogues
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                    <?php endif; ?>

                                        <!-- Configuration Section (super_admin only) -->
                    <?php if ($_isSuperAdmin): ?>
                        <li class="nav-item">
                            <?php $configActive = ['company_info', 'backup']; ?>
                            <a class="nav-link collapsed d-flex justify-content-between align-items-center"
                                data-bs-toggle="collapse" data-bs-target="#configMenu"
                                aria-expanded="<?= in_array($activePage, $configActive) ? 'true' : 'false' ?>">
                                <span><i class="fas fa-cog me-2"></i>Configuration</span>
                                <i class="fas fa-chevron-down small"></i>
                            </a>
                            <div class="collapse <?= in_array($activePage, $configActive) ? 'show' : '' ?>" id="configMenu"
                                data-bs-parent="#sidebarAccordion">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'company_info' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/settings/company_info.php">
                                            <i class="fas fa-id-card me-2"></i>Company Info
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?= $activePage === 'backup' ? 'active' : '' ?>"
                                            href="<?= BASE_URL ?>/modules/settings/backup.php">
                                            <i class="fas fa-database me-2"></i>Backup & Restore
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        <!-- User Management (super_admin only) -->
                        <li class="nav-item">
                            <a class="nav-link <?= $activePage === 'admin_users' ? 'active' : '' ?>"
                                href="<?= BASE_URL ?>/modules/users/list.php">
                                <i class="fas fa-user-shield me-2"></i>Admin Users
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($_flashError): ?>
                <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_flashError) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($_flashSuccess): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_flashSuccess) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
