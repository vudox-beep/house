<?php
require_once 'config/config.php';
require_once 'includes/ActivityLogger.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    $logger = new ActivityLogger();
    $logger->log($_SESSION['user_id'], $_SESSION['user_role'] ?? 'unknown', 'logout', 'User logged out');
}

session_unset();
session_destroy();
header("Location: login.php");
exit;
?>
