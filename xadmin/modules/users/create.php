<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
requireRole(['super_admin']);

$pageTitle = 'Add Admin User';
$activePage = 'admin_users';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email    = sanitize($_POST['email'] ?? '') ?: null;
    $mobile   = sanitize($_POST['mobile'] ?? '') ?: null;
    $password = $_POST['password'] ?? '';
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
    } elseif (empty($password)) {
        $error = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $existing = fetchOne("SELECT id FROM admin_users WHERE username = ?", [$username]);
        if ($existing) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            insert("INSERT INTO admin_users (name, username, email, mobile, role, assigned_cities, password_hash, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)",
                [$name, $username, $email, $mobile, $role, $assignedCities, $hash]);

            logInfo("Admin user created: {$username} (role: {$role}) by " . getAdminUsername());
            $_SESSION['flash_success'] = "User '{$name}' created successfully.";
            header('Location: ' . BASE_URL . '/modules/users/list.php');
            exit;
        }
    }
}

include dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-user-plus me-2"></i>Add Admin User</h1>
            <p class="text-muted mb-0">Create a new admin panel user — every user has full Admin access</p>
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
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                        <small class="text-muted">Letters, numbers, underscore only. Used to log in.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input type="text" class="form-control" name="mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>">
                    </div>
                </div>

                <hr>
                <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.75rem;letter-spacing:.05em;">Password</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" minlength="6" required>
                        <small class="text-muted">Minimum 6 characters.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password2" minlength="6" required>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>/modules/users/list.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include dirname(__DIR__, 2) . '/includes/footer.php';
?>
