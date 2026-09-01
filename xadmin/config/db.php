<?php
/**
 * Database Configuration & Helper Functions
 * WhatsApp CRM & Automation Admin Panel
 */

// Base URL for this project — auto-detected from where the xadmin folder
// sits under the web root, so the same code runs locally (/Xtral/xadmin)
// and on the live server (/xadmin) without editing.
$__docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
$__appRoot = str_replace('\\', '/', dirname(__DIR__));
if ($__docRoot !== '' && stripos($__appRoot, $__docRoot) === 0) {
    define('BASE_URL', rtrim(substr($__appRoot, strlen($__docRoot)), '/'));
} else {
    define('BASE_URL', '/xadmin'); // CLI / unusual setups — adjust if needed
}
unset($__docRoot, $__appRoot);

// Database Configuration — auto-switches between local XAMPP and the live
// Hostinger server, so the same file works in both places without editing.
$__isLocalDb = in_array($_SERVER['SERVER_NAME'] ?? 'localhost', ['localhost', '127.0.0.1'], true);
if ($__isLocalDb) {
    // Local development (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'xtral');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Live server (Hostinger)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u401719003_xtral');
    define('DB_USER', 'u401719003_xtral');
    define('DB_PASS', 'X-tralDatabase1');
}
unset($__isLocalDb);
define('DB_CHARSET', 'utf8mb4');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// File Upload Configuration
define('MAX_UPLOAD_SIZE', 10485760); // 10MB in bytes
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');

// Logging Configuration
define('LOG_PATH', dirname(__DIR__) . '/logs/');

// HMAC key used to sign plumber-settlement PDF download URLs (api/payout/*).
// Treat as a secret — do NOT expose anywhere. Rotating this invalidates any
// signed download links already sent out (via WhatsApp etc.).
define('PAYOUT_DOWNLOAD_SECRET', '899d73fc37d9d9da1cd4b47df7e81d9c494f1ea826ff105d4254354528d172df');

// SMTP — sends the public Contact form (xfront/contact.php) to this inbox.
// SMTP_USER must be able to authenticate at SMTP_HOST — for Gmail this is
// a 16-character App Password (Google Account -> Security -> App passwords),
// NOT the normal account password (Gmail blocks plain-password SMTP login).
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'xtralcare@gmail.com');
define('SMTP_PASS', 'fsdgfmqyzhvcumjy');                          // <-- fill in the Gmail App Password
define('SMTP_FROM_EMAIL', 'xtralcare@gmail.com');
define('SMTP_FROM_NAME', 'X-Tral Website');
define('SMTP_TO_EMAIL', 'xtralcare@gmail.com');

// Timezone — India
date_default_timezone_set('Asia/Kolkata');

// Error Reporting — shown on localhost only; hidden on the live server
// (errors still go to the logs/ folder either way).
error_reporting(E_ALL);
$__isLocal = in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);
ini_set('display_errors', $__isLocal ? 1 : 0);
unset($__isLocal);

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDBConnection()
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            // Pin MySQL session timezone to IST so TIMESTAMP columns + NOW() stay in sync
            // with PHP's Asia/Kolkata default. Otherwise live MySQL (often UTC) returns
            // TIMESTAMP values 5:30 hours off.
            $pdo->exec("SET time_zone = '+05:30'");
        } catch (PDOException $e) {
            logError('Database Connection Error: ' . $e->getMessage());
            // Only throw exception if explicitly requested (for new JSON APIs)
            // Otherwise use die() to maintain backward compatibility
            if (defined('API_JSON_RESPONSE')) {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
            die('Database connection failed. Please check your configuration.');
        }
    }

    return $pdo;
}

/**
 * Initialize Session with Security Settings
 */
function initSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        // Session security settings
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

        session_start();

        // Check session timeout
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
            session_unset();
            session_destroy();
            session_start();
        }

        $_SESSION['LAST_ACTIVITY'] = time();

        // Regenerate session ID periodically
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }
}

/**
 * Check if User is Logged In
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require Login - Redirect to Login Page if Not Authenticated
 */
function requireLogin()
{
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/modules/auth/login.php');
        exit;
    }
}

/**
 * Get Current Admin User ID
 * @return int|null
 */
function getAdminId()
{
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Get Current Admin User ID (Alias)
 * @return int|null
 */
function getCurrentUserId()
{
    return getAdminId();
}

/**
 * Get Current Admin Username
 * @return string|null
 */
function getAdminUsername()
{
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Get Current Admin Display Name
 * @return string
 */
function getAdminName()
{
    return $_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'Admin';
}

/**
 * Get Current Admin Role
 * @return string
 */
function getAdminRole()
{
    return $_SESSION['admin_role'] ?? 'super_admin';
}

/**
 * Check if current user has one of the given roles
 * @param array $roles
 * @return bool
 */
function hasRole(array $roles): bool
{
    return in_array(getAdminRole(), $roles, true);
}

/**
 * Require specific role(s) — redirect with error if not allowed
 * @param array $allowedRoles
 */
function requireRole(array $allowedRoles): void
{
    requireLogin();
    if (!hasRole($allowedRoles)) {
        $_SESSION['flash_error'] = 'You do not have permission to access that page.';
        header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
        exit;
    }
}

/**
 * Human-readable role label
 * @param string $role
 * @return string
 */
function getRoleLabel(string $role): string
{
    // Single-role panel: every user is a full Admin (other roles removed 2026-07-04)
    $labels = [
        'super_admin' => 'Admin',
    ];
    return $labels[$role] ?? ucfirst($role);
}

/**
 * Execute Query and Return Result
 * @param string $sql SQL query
 * @param array $params Parameters for prepared statement
 * @return PDOStatement
 */
function query($sql, $params = [])
{
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Fetch Single Row
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array|false
 */
function fetchOne($sql, $params = [])
{
    $stmt = query($sql, $params);
    return $stmt->fetch();
}

/**
 * Fetch All Rows
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return array
 */
function fetchAll($sql, $params = [])
{
    $stmt = query($sql, $params);
    return $stmt->fetchAll();
}

/**
 * Insert Record and Return Last Insert ID
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return string Last insert ID
 */
function insert($sql, $params = [])
{
    query($sql, $params);
    return getDBConnection()->lastInsertId();
}

/**
 * Get Last Insert ID (Direct)
 * @return string Last insert ID
 */
function lastInsertId()
{
    return getDBConnection()->lastInsertId();
}

/**
 * Update or Delete - Returns Affected Rows
 * @param string $sql SQL query
 * @param array $params Parameters
 * @return int Affected rows
 */
function execute($sql, $params = [])
{
    $stmt = query($sql, $params);
    return $stmt->rowCount();
}

/**
 * Get Setting Value
 * @param string $key Setting key
 * @return string|null
 */
function getSetting($key)
{
    $result = fetchOne("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    return $result ? $result['setting_value'] : null;
}

/**
 * Update Setting Value
 * @param string $key Setting key
 * @param string $value Setting value
 * @return bool
 */
function updateSetting($key, $value)
{
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = ?";
    execute($sql, [$key, $value, $value]);
    return true;
}

/**
 * Sanitize Input
 * @param string $data Input data
 * @return string
 */
function sanitize($data)
{
    return strip_tags(trim($data));
}

/**
 * Normalize a product/variant code to use dashes instead of spaces
 * (e.g. "TDS 60" -> "TDS-60"). Used both for display and to normalize
 * codes on save, so QR codes printed with the old space form still
 * resolve — the front end dash-normalizes both sides before matching.
 * @param string|null $code
 * @return string
 */
function dashCode(?string $code): string
{
    return $code ? preg_replace('/-+/', '-', preg_replace('/\s+/', '-', trim($code))) : '';
}

/**
 * Validate Mobile Number Format
 * @param string $mobile Mobile number
 * @return bool
 */
function isValidMobile($mobile)
{
    // Remove all non-digit characters
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    // Check if it's between 10-15 digits
    return preg_match('/^[0-9]{10,15}$/', $mobile);
}

/**
 * Format Mobile Number for WhatsApp API
 * @param string $mobile Mobile number
 * @return string Formatted mobile (with country code if missing)
 */
function formatMobile($mobile)
{
    // Remove all non-digit characters
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    // If doesn't start with country code, add default (91 for India)
    if (strlen($mobile) === 10) {
        $mobile = '91' . $mobile;
    }

    return $mobile;
}

/**
 * Log Error to File
 * @param string $message Error message
 * @param string $type Log type (error, info, warning)
 */
function logError($message, $type = 'error')
{
    $logFile = LOG_PATH . $type . '_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;

    // Create logs directory if it doesn't exist
    if (!is_dir(LOG_PATH)) {
        mkdir(LOG_PATH, 0755, true);
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

/**
 * Log Info to File
 * @param string $message Info message
 */
function logInfo($message)
{
    logError($message, 'info');
}

/**
 * Send JSON Response
 * @param bool $success Success status
 * @param mixed $data Response data
 * @param string $message Response message
 */
function jsonResponse($success, $data = null, $message = '')
{
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Handle File Upload
 * @param array $file $_FILES array element
 * @param string $destination Destination folder (relative to uploads/)
 * @param array $allowedTypes Allowed MIME types
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function handleFileUpload($file, $destination, $allowedTypes = [])
{
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error: ' . $file['error']];
    }

    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 10MB limit'];
    }

    // Check file type if specified
    if (!empty($allowedTypes)) {
        $isValid = false;

        // Try using finfo if available
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (in_array($mimeType, $allowedTypes)) {
                    $isValid = true;
                }
            }
        }

        // Fallback: check file extension if finfo is missing or invalid
        if (!$isValid) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extMap = [
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => ['png'],
                'image/gif' => ['gif'],
                'image/webp' => ['webp'],
                'video/mp4' => ['mp4'],
                'video/webm' => ['webm'],
                'video/ogg' => ['ogg'],
                'video/quicktime' => ['mov', 'qt']
            ];

            $allowedExts = [];
            foreach ($allowedTypes as $type) {
                if (isset($extMap[$type])) {
                    $allowedExts = array_merge($allowedExts, $extMap[$type]);
                }
            }

            if (!empty($allowedExts) && in_array($ext, $allowedExts)) {
                $isValid = true;
            }
        }

        if (!$isValid) {
            return ['success' => false, 'error' => 'Invalid file type'];
        }
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $destination . '/' . $filename;

    // Create destination directory if it doesn't exist
    $dir = UPLOAD_PATH . $destination;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

/**
 * Save base64-encoded image (data URI) to uploads and return filename
 * @param string $dataUri data:image/jpeg;base64,... or data:image/png;base64,...
 * @param string $destination Destination folder (relative to uploads/)
 * @param string $prefix Filename prefix
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function saveBase64Image($dataUri, $destination, $prefix = 'img')
{
    if (strpos($dataUri, 'data:image/') !== 0) {
        return ['success' => false, 'error' => 'Invalid image data'];
    }

    if (preg_match('/^data:(image\\/\\w+);base64,(.+)$/', $dataUri, $matches) !== 1) {
        return ['success' => false, 'error' => 'Malformed image data'];
    }

    $mime = $matches[1];
    $base64 = $matches[2];

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    if (!isset($allowed[$mime])) {
        return ['success' => false, 'error' => 'Invalid image type'];
    }
    $ext = $allowed[$mime];

    $data = base64_decode($base64);
    if ($data === false) {
        return ['success' => false, 'error' => 'Failed to decode image'];
    }
    if (strlen($data) > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'Image exceeds size limit'];
    }

    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $ext;
    $dir = UPLOAD_PATH . $destination;
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $path = $dir . '/' . $filename;
    if (file_put_contents($path, $data) === false) {
        return ['success' => false, 'error' => 'Failed to save image'];
    }

    return ['success' => true, 'filename' => $filename];
}

/**
 * Crop an image to a square (center) in-place.
 * @param string $fullPath Absolute filesystem path
 * @return bool
 */
function cropImageToSquare($fullPath)
{
    if (!file_exists($fullPath)) {
        return false;
    }
    $imageInfo = getimagesize($fullPath);
    if (!$imageInfo)
        return false;
    [$width, $height, $type] = $imageInfo;
    if ($width <= 0 || $height <= 0)
        return false;

    $size = min($width, $height);
    $srcX = (int) (($width - $size) / 2);
    $srcY = (int) (($height - $size) / 2);

    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($fullPath);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($fullPath);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($fullPath);
            break;
        default:
            return false;
    }
    if (!$src)
        return false;

    $dst = imagecreatetruecolor($size, $size);
    // Preserve transparency for PNG/GIF
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    if (!imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $size, $size, $size, $size)) {
        imagedestroy($src);
        imagedestroy($dst);
        return false;
    }

    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dst, $fullPath, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dst, $fullPath);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dst, $fullPath);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);
    return $result;
}

/**
 * Delete File from Uploads Directory
 * @param string $filepath Relative path from uploads/
 * @return bool
 */
function deleteUploadedFile($filepath)
{
    $fullPath = UPLOAD_PATH . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Clean Old Generated Images (older than 24 hours)
 */
function cleanOldGeneratedImages()
{
    $dir = UPLOAD_PATH . 'generated/';
    if (!is_dir($dir))
        return;

    $files = glob($dir . '*');
    $now = time();

    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= 86400) { // 24 hours
                unlink($file);
            }
        }
    }
}

/**
 * Get WhatsApp Template Name from config
 * Maps logical template keys to actual Facebook template names
 * @param string $key Logical key (e.g., 'complaint_registered')
 * @return string Actual template name from config/whatsapp_templates.php
 */
function getWhatsAppTemplateName($key)
{
    // Re-read the file every call. We used to cache it in a static, but on
    // shared hosting (Hostinger) different PHP-FPM workers held different
    // OPcache snapshots, so updates to the file were not picked up until
    // every worker recycled. Re-reading sidesteps that — file is tiny.
    $configFile = dirname(__DIR__) . '/config/whatsapp_templates.php';
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($configFile, false);
    }
    $templates = file_exists($configFile) ? (require $configFile) : [];
    return $templates[$key] ?? $key; // Falls back to key itself if not found
}

// Initialize session on every request
initSession();
