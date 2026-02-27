<?php
require_once '../config/config.php';

// Detect if this is Login or Signup based on a query parameter or session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if an 'action' parameter is passed (e.g., google_login.php?action=login or ?action=signup)
// Default to 'login' if not set, but actually we need to know.
// Let's rely on the pages (login.php and register.php) passing this param.
$action = $_GET['action'] ?? 'login'; // Default to login if not specified
$_SESSION['auth_action'] = $action;

$login_url = 'https://accounts.google.com/o/oauth2/v2/auth?client_id=' . GOOGLE_CLIENT_ID . '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URL) . '&response_type=code&scope=' . urlencode('email profile') . '&access_type=online&prompt=consent';

header('Location: ' . $login_url);
exit;
?>
