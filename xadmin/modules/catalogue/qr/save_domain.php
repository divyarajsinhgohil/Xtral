<?php
/**
 * Save QR Live Domain Setting
 */
require_once dirname(__DIR__, 3) . '/config/db.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain = trim($_POST['live_site_url'] ?? '');
    $mode = $_POST['qr_domain_mode'] ?? 'live'; // 'live' or 'local'

    if (!empty($domain)) {
        if (!preg_match('#^https?://#i', $domain)) {
            $domain = 'https://' . $domain;
        }
        $domain = rtrim($domain, '/');
        
        // Save to settings table
        $existing = fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'live_site_url'");
        if ($existing !== false) {
            execute("UPDATE settings SET setting_value = ? WHERE setting_key = 'live_site_url'", [$domain]);
        } else {
            execute("INSERT INTO settings (setting_key, setting_value) VALUES ('live_site_url', ?)", [$domain]);
        }
    }

    $_SESSION['qr_domain_mode'] = ($mode === 'local') ? 'local' : 'live';
    $_SESSION['success'] = "QR domain settings updated. Using " . ($_SESSION['qr_domain_mode'] === 'live' ? "Live Domain ({$domain})" : "Localhost Domain") . " for QR codes.";
}

header('Location: list.php');
exit;
