<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
requireRole(['super_admin']);

$pageTitle = 'Edit Admin User';
$activePage = 'admin_users';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit;
}

$user = fetchOne("SELECT * FROM admin_users WHERE id = ?", [$id]);
if (!$user) {
    $_SESSION['flash_error'] = 'User not found.';
    header('Location: ' . BASE_URL . '/modules/users/list.php');
    exit;
}

$error = '';
$isSelf = ($id === getAdminId());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email'] ?? '') ?: null;
    $mobile   = sanitize($_POST['mobile'] ?? '') ?: null;
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Single-role panel: every user is a full Admin (other roles removed 2026-07-04)
    $role = 'super_admin';
    $assignedCities = null;

    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($username)) {
        $error = 'Username is required.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username)) {
        $error = 'Username must be 3-50 characters, letters/numbers/underscore only.';
    } elseif (fetchOne("SELECT id FROM admin_users WHERE username = ? AND id != ?", [$username, $id])) {
        $error = 'Username already taken by another user.';
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (!empty($password) && $password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            execute("UPDATE admin_users SET name=?, username=?, email=?, mobile=?, role=?, assigned_cities=?, password_hash=? WHERE id=?",
                [$name, $username, $email, $mobile, $role, $assignedCities, $hash, $id]);
        } else {
            execute("UPDATE admin_users SET name=?, username=?, email=?, mobile=?, role=?, assigned_cities=? WHERE id=?",
                [$name, $username, $email, $mobile, $role, $assignedCities, $id]);
        }

        // Editing your own username — keep the session in sync so you stay logged in
        if ($isSelf) {
            $_SESSION['admin_username'] = $username;
            $_SESSION['admin_name'] = $name;
        }

        logInfo("Admin user updated: id={$id} by " . getAdminUsername());
        $_SESSION['flash_success'] = "User '{$name}' updated successfully.";
        header('Location: ' . BASE_URL . '/modules/users/list.php');
        exit;
    }
}

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user-edit me-2"></i>Edit User — <?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-muted mb-0">Update details or reset password</p>
        </div>
        <a href="<?= BASE_URL ?>/modules/users/list.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST">

                <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.05em;">Basic Info</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($_POST['username'] ?? $user['username']) ?>" required>
                        <small class="text-muted">Letters, numbers, underscore only. Used to log in.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? $user['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? $user['mobile'] ?? '') ?>">
                    </div>
                </div>

                <hr>
                <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.05em;">Reset Password <span class="text-muted fw-normal">(leave blank to keep current)</span></h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">New Password</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="password2" minlength="6">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/modules/users/list.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include dirname(__DIR__, 2) . '/includes/footer.php';
?>
