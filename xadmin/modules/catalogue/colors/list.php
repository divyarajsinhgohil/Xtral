<?php
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

// Filter Logic
$search = $_GET['search'] ?? '';

$sql = "SELECT * FROM catalogue_colors";
$where = [];
$params = [];

if ($search) {
    $where[] = "name LIKE ?";
    $params[] = "%$search%";
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY name ASC";
$colors = fetchAll($sql, $params);

$pageTitle = "Color Library";
$activePage = "catalogue_colors";

include dirname(__DIR__, 3) . '/includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-palette me-2"></i>Color Library</h2>
            <p class="text-muted">Manage colors and textures for product variants.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Add New Color
            </a>
        </div>
    </div>

    <!-- Search/Filter -->
    <div class="card shadow mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <input type="text" name="search" class="form-control" placeholder="Search colors..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-secondary">Search</button>
                    <?php if($search): ?>
                        <a href="list.php" class="btn btn-outline-secondary">Reset</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Preview</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($colors)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No colors found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($colors as $c): ?>
                                <tr>
                                    <td>
                                        <div class="border rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden; background: #f8f9fa;">
                                            <?php if ($c['type'] === 'solid'): ?>
                                                <div style="width: 100%; height: 100%; background-color: <?= htmlspecialchars($c['hex_code']) ?>;"></div>
                                            <?php elseif ($c['type'] === 'texture' && $c['texture_image']): ?>
                                                <img src="<?= BASE_URL ?>/uploads/catalogue/colors/<?= htmlspecialchars($c['texture_image']) ?>" alt="Texture" style="width: 100%; height: 100%; object-fit: cover;">
                                            <?php else: ?>
                                                <i class="fas fa-image text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($c['name']) ?></td>
                                    <td>
                                        <?php if($c['type'] === 'solid'): ?>
                                            <span class="badge bg-secondary">Solid Color</span>
                                        <?php else: ?>
                                            <span class="badge bg-info">Texture</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($c['type'] === 'solid'): ?>
                                            <code><?= htmlspecialchars($c['hex_code']) ?></code>
                                        <?php else: ?>
                                            <small class="text-muted">Image File</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($c['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
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
</div>

<?php include dirname(__DIR__, 3) . '/includes/footer.php'; ?>
