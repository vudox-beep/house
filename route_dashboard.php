<?php
require_once 'config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login");
    exit;
}

$role = $_SESSION['user_role'] ?? '';

switch ($role) {
    case 'admin':
        header("Location: admin/dashboard");
        break;
    case 'dealer':
        header("Location: dealer/dashboard");
        break;
    case 'user':
        // Tenant role is 'user' in DB usually, sometimes 'tenant'
        header("Location: tenant/dashboard");
        break;
    default:
        header("Location: login");
        break;
}
exit;
?>