<?php
require_once dirname(__DIR__, 2) . '/config/db.php';
requireRole(['super_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, null, 'Invalid request.');
}

$id = intval($_POST['id'] ?? 0);
if (!$id) {
    jsonResponse(false, null, 'Invalid user ID.');
}

// Cannot deactivate yourself
if ($id === getAdminId()) {
    jsonResponse(false, null, 'You cannot deactivate your own account.');
}

$user = fetchOne("SELECT id, name, is_active FROM admin_users WHERE id = ?", [$id]);
if (!$user) {
    jsonResponse(false, null, 'User not found.');
}

$newStatus = $user['is_active'] ? 0 : 1;
execute("UPDATE admin_users SET is_active = ? WHERE id = ?", [$newStatus, $id]);

$action = $newStatus ? 'activated' : 'deactivated';
logInfo("Admin user {$action}: id={$id} ({$user['name']}) by " . getAdminUsername());

jsonResponse(true, ['is_active' => $newStatus], "User {$action} successfully.");
