<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config/config.php';
require_once '../includes/SMSHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

if (empty($phone) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Phone number and message are required']);
    exit();
}

// Basic validation: ensure phone starts with + (recommended for international SMS)
if (strpos($phone, '+') !== 0) {
    // If not starting with +, and is 10 digits, assume Zambia (+260)
    if (strlen($phone) === 10 && (strpos($phone, '09') === 0 || strpos($phone, '07') === 0)) {
        $phone = '+260' . substr($phone, 1);
    }
}

$sms = new SMSHelper();
$result = $sms->sendSMS($phone, $message);

echo json_encode($result);
