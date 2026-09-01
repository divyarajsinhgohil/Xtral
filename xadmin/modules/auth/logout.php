<?php
/**
 * Logout
 * WhatsApp CRM & Automation Admin Panel
 */

require_once dirname(__DIR__, 2) . '/config/db.php';

// Log the logout event
if (isLoggedIn()) {
    logInfo("Admin logout: " . getAdminUsername());
}

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header('Location: ' . BASE_URL . '/modules/auth/login.php?logged_out=1');
exit;
