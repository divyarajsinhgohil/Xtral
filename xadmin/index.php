<?php
/**
 * Entry point - redirect to login or dashboard
 */
require_once __DIR__ . '/config/db.php';
initSession();
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
} else {
    header('Location: ' . BASE_URL . '/modules/auth/login.php');
}
exit;
