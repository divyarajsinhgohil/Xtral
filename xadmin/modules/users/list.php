<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
requireRole(['super_admin']);

$pageTitle = 'Admin Users';
$activePage = 'admin_users';

$users = fetchAll("SELECT id, name, username, email, mobile, role, assigned_cities, is_active, last_login, created_at FROM admin_users ORDER BY role ASC, name ASC");

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user-shield me-2"></i>Admin Users</h1>
            <p class="text-muted mb-0">Manage who can access the admin panel and their permissions</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                        <tr class="<?= !$u['is_active'] ? 'table-secondary text-muted' : '' ?>">
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
                                <?php if ($u['email']): ?>
                                <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($u['username']) ?></code></td>
                            <td><?= htmlspecialchars($u['mobile'] ?? '—') ?></td>
                            <td>
                                <?php
                                $roleBadge = [
                                    'super_admin'        => 'danger',
                                    'sales_team'         => 'primary',
                                    'support_team'       => 'info',
                                    'inventory_manager'  => 'warning',
                                    'purchase_manager'   => 'info',
                                    'dispatch_manager'   => 'success',
                                    'mis_viewer'         => 'secondary',
                                ];
                                $badgeColor = $roleBadge[$u['role']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $badgeColor ?>"><?= getRoleLabel($u['role']) ?></span>
                            </td>
                            <td>
                                <?php if ($u['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= $u['last_login'] ? date('d M Y, h:i A', strtotime($u['last_login'])) : 'Never' ?>
                                </small>
                            </td>
                            <td class="text-end">
                                <a href="<?= BASE_URL ?>/modules/users/edit.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($u['id'] !== getAdminId()): ?>
                                <button class="btn btn-sm <?= $u['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?> toggle-btn me-1"
                                        data-id="<?= $u['id'] ?>"
                                        data-active="<?= $u['is_active'] ?>"
                                        title="<?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                    <i class="fas fa-<?= $u['is_active'] ? 'ban' : 'check' ?>"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn"
                                        data-id="<?= $u['id'] ?>"
                                        data-name="<?= htmlspecialchars($u['name']) ?>"
                                        title="Delete permanently">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No admin users found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = <<<'JS'
<script>
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const isActive = this.dataset.active === '1';
        const action = isActive ? 'deactivate' : 'activate';
        if (!confirm(`Are you sure you want to ${action} this user?`)) return;

        fetch(window.XADMIN_BASE + '/modules/users/toggle_active.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to update user.');
        });
    });
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const name = this.dataset.name;
        if (!confirm(`Delete user "${name}" permanently? This cannot be undone.`)) return;

        fetch(window.XADMIN_BASE + '/modules/users/delete.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Failed to delete user.');
        });
    });
});
</script>
JS;
include dirname(__DIR__, 2) . '/includes/footer.php';
?>
