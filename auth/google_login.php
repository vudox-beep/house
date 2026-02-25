<?php
require_once '../config/config.php';

$login_url = 'https://accounts.google.com/o/oauth2/v2/auth?client_id=' . GOOGLE_CLIENT_ID . '&redirect_uri=' . urlencode(GOOGLE_REDIRECT_URL) . '&response_type=code&scope=' . urlencode('email profile') . '&access_type=online&prompt=consent';

header('Location: ' . $login_url);
exit;
?>
