<?php
/**
 * Login Page
 * X-Tral Admin Panel
 */

require_once dirname(__DIR__, 2) . '/config/db.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
    exit;
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Fetch user from database
        $user = fetchOne("SELECT * FROM admin_users WHERE username = ?", [$username]);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Check account is active
            if (isset($user['is_active']) && !$user['is_active']) {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } else {
                // Successful login — store role & name in session
                $_SESSION['admin_id']       = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name']     = $user['name'] ?? $user['username'];
                $_SESSION['admin_role']     = $user['role'] ?? 'super_admin';

                // Update last login timestamp
                execute("UPDATE admin_users SET last_login = NOW() WHERE id = ?", [$user['id']]);

                logInfo("Admin login successful: {$username} (role: {$_SESSION['admin_role']})");

                // Redirect to dashboard
                header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
                exit;
            }
        } elseif (empty($error)) {
            $error = 'Invalid username or password';
            logError("Failed login attempt for username: {$username}");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - X-Tral Admin</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/xtral_favicon_32.png">
    <link rel="icon" type="image/png" sizes="256x256" href="<?= BASE_URL ?>/assets/images/xtral_favicon_256.png">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/assets/css/custom.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #2B676E 0%, #1C4449 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #2B676E 0%, #1C4449 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .login-header img.login-logo {
            width: min(320px, 100%);
            height: auto;
            margin-bottom: 15px;
            background: #fff;
            border-radius: 10px;
            padding: 10px 14px;
        }
        .login-header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }
        .login-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 8px;
        }
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            border-color: #2B676E;
            box-shadow: 0 0 0 0.2rem rgba(43, 103, 110, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #2B676E 0%, #1C4449 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            color: white;
            transition: transform 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(43, 103, 110, 0.4);
            color: white;
        }
        .alert {
            border-radius: 8px;
        }
        .input-group-text {
            background: #f8f9fa;
            border-right: none;
            border-radius: 8px 0 0 8px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 15px;
            bottom: 14px;
            color: #6c757d;
            z-index: 10;
        }
        .password-toggle:hover {
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <?php
                // Company logo (Configuration → Company Info) with static fallback
                $loginLogo = getSetting('company_logo');
                $loginLogoUrl = $loginLogo
                    ? BASE_URL . '/uploads/settings/' . rawurlencode($loginLogo)
                    : BASE_URL . '/uploads/catalogue/products/logo_full.png';
                ?>
                <img src="<?= $loginLogoUrl ?>" alt="X-Tral Logo" class="login-logo">
                <h1>X-Tral Admin</h1>
                <p>Sign in to manage your admin panel</p>
            </div>
            
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user me-2"></i>Username
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               placeholder="Enter your username"
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required 
                               autofocus>
                    </div>
                    
                    <div class="mb-4 position-relative">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>
                
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secured by SSL encryption
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <small class="text-white">
                <i class="fas fa-code me-1"></i>
                X-Tral Admin v1.0
            </small>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Password Toggle Script -->
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
