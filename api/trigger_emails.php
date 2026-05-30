<?php
require_once '../config/config.php';

// Trigger the cron notification script in the background so the app/website doesn't hang
$script_path = realpath(__DIR__ . '/../cron_notifications.php');
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    pclose(popen("start /B php " . escapeshellarg($script_path), "r"));
} else {
    exec("php " . escapeshellarg($script_path) . " > /dev/null 2>&1 &");
}

echo json_encode(['status' => 'success', 'message' => 'Email notifications triggered in background.']);
?>