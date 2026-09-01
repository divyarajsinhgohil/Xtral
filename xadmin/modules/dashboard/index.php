<?php
/**
 * Dashboard — X-Tral Admin
 * Trimmed to the shipping modules: Catalogue, Configuration, Admin Users.
 * Orders / dispatch / complaints / marketing cards removed — those modules
 * (and their DB tables) were decommissioned on 2026-07-04. Restore from
 * backups/xtral_full_backup_before_module_drop_2026-07-04.sql if ever needed.
 */

require_once dirname(__DIR__, 2) . '/config/db.php';
requireLogin();

$pageTitle  = 'Dashboard';
$activePage = 'dashboard';

// ── Catalogue counters ──────────────────────────────────────────────────
$totalProducts   = (int)(fetchOne("SELECT COUNT(*) AS c FROM catalogue_products")['c'] ?? 0);
$activeProducts  = (int)(fetchOne("SELECT COUNT(*) AS c FROM catalogue_products WHERE is_active = 1")['c'] ?? 0);
$totalCategories = (int)(fetchOne("SELECT COUNT(*) AS c FROM catalogue_categories")['c'] ?? 0);
$totalSeries     = (int)(fetchOne("SELECT COUNT(*) AS c FROM catalogue_series")['c'] ?? 0);
$totalBanners    = (int)(fetchOne("SELECT COUNT(*) AS c FROM catalogue_banners")['c'] ?? 0);
$totalAdmins     = (int)(fetchOne("SELECT COUNT(*) AS c FROM admin_users")['c'] ?? 0);

// ── Recently updated products ───────────────────────────────────────────
$recentProducts = fetchAll(
    "SELECT id, name, is_active, updated_at
     FROM catalogue_products
     ORDER BY updated_at DESC LIMIT 6"
);

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container-fluid p-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h1>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars(getAdminUsername()) ?>!</p>
        </div>
        <span class="badge bg-secondary p-2">
            <i class="fas fa-clock me-1"></i><?= date('l, F j, Y - g:i A') ?>
        </span>
    </div>

    <!-- Catalogue Counters -->
    <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size:.75rem;letter-spacing:.05em;">Catalogue Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <a href="<?= BASE_URL ?>/modules/catalogue/products/list.php" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size:.7rem;">Products</h6>
                            <h2 class="mb-0 text-dark"><?= number_format($totalProducts) ?></h2>
                            <small class="text-muted"><?= number_format($activeProducts) ?> active</small>
                        </div>
                        <div class="stat-icon bg-primary"><i class="fas fa-box-open"></i></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="<?= BASE_URL ?>/modules/catalogue/categories/list.php" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size:.7rem;">Main Categories</h6>
                            <h2 class="mb-0 text-dark"><?= number_format($totalCategories) ?></h2>
                            <small class="text-muted"><?= number_format($totalSeries) ?> series</small>
                        </div>
                        <div class="stat-icon bg-info"><i class="fas fa-list"></i></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="<?= BASE_URL ?>/modules/catalogue/banners/list.php" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size:.7rem;">Home Banners</h6>
                            <h2 class="mb-0 text-dark"><?= number_format($totalBanners) ?></h2>
                            <small class="text-muted">home screen banners</small>
                        </div>
                        <div class="stat-icon bg-warning"><i class="fas fa-images"></i></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php if ($_isSuperAdmin): ?>
        <div class="col-md-6 col-lg-3">
            <a href="<?= BASE_URL ?>/modules/users/list.php" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-2" style="font-size:.7rem;">Admin Users</h6>
                            <h2 class="mb-0 text-dark"><?= number_format($totalAdmins) ?></h2>
                            <small class="text-muted">panel accounts</small>
                        </div>
                        <div class="stat-icon bg-danger"><i class="fas fa-user-shield"></i></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <?php
    // Quick actions for the remaining modules.
    $actions = [];
    if ($_canCatalogue) {
        $actions[] = [BASE_URL . '/modules/catalogue/products/list.php', 'fa-box-open', 'Products', 'primary'];
        $actions[] = [BASE_URL . '/modules/catalogue/tools/index.php', 'fa-wrench', 'Import / Export', 'info'];
    }
    if ($_isSuperAdmin) {
        $actions[] = [BASE_URL . '/modules/settings/company_info.php', 'fa-id-card', 'Company Info', 'success'];
        $actions[] = [BASE_URL . '/modules/settings/backup.php', 'fa-database', 'Backup & Restore', 'warning'];
    }
    $btnCol = count($actions) > 0 ? (int)floor(12 / count($actions)) : 12;
    ?>
    <?php if (!empty($actions)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0"><i class="fas fa-bolt me-2 text-warning"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($actions as [$href, $icon, $label, $color]): ?>
                <div class="col-md-<?= $btnCol ?>">
                    <a href="<?= $href ?>" class="btn btn-outline-<?= $color ?> w-100">
                        <i class="fas <?= $icon ?> d-block fs-2 mb-2"></i><?= $label ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recently Updated Products -->
    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-box-open me-2 text-primary"></i>Recently Updated Products</h5>
                    <a href="<?= BASE_URL ?>/modules/catalogue/products/list.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentProducts)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fs-1 mb-3 d-block"></i>
                            <p>No products yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentProducts as $p): ?>
                                <a href="<?= BASE_URL ?>/modules/catalogue/products/edit.php?id=<?= (int)$p['id'] ?>" class="list-group-item list-group-item-action px-0 text-decoration-none">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($p['name']) ?></h6>
                                            <small class="text-muted">Updated <?= date('d M Y, g:i A', strtotime($p['updated_at'])) ?></small>
                                        </div>
                                        <span class="badge bg-<?= $p['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/includes/footer.php'; ?>
