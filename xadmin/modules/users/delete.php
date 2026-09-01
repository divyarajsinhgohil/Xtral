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

// Cannot delete yourself
if ($id === getAdminId()) {
    jsonResponse(false, null, 'You cannot delete your own account.');
}

$user = fetchOne("SELECT id, name, username FROM admin_users WHERE id = ?", [$id]);
if (!$user) {
    jsonResponse(false, null, 'User not found.');
}

// Never delete the last remaining user — the panel would be locked out
$totalUsers = (int)(fetchOne("SELECT COUNT(*) AS c FROM admin_users")['c'] ?? 0);
if ($totalUsers <= 1) {
    jsonResponse(false, null, 'Cannot delete the last admin user.');
}

execute("DELETE FROM admin_users WHERE id = ?", [$id]);

logInfo("Admin user deleted: id={$id} ({$user['username']}) by " . getAdminUsername());

jsonResponse(true, null, "User '{$user['name']}' deleted permanently.");
