<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../includes/SimpleMailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_POST;
}

$action = trim($data['action'] ?? '');
if ($action === '') {
    echo json_encode(['status' => 'error', 'message' => 'Action is required']);
    exit;
}

$mailer = new SimpleMailer();

try {
    if ($action === 'registration_verify') {
        $email = trim($data['email'] ?? '');
        $name = trim($data['name'] ?? 'User');
        $verify_link = trim($data['verify_link'] ?? '');
        $token = trim($data['verification_token'] ?? '');

        if ($email === '' || ($verify_link === '' && $token === '')) {
            echo json_encode(['status' => 'error', 'message' => 'Email and verify_link or verification_token are required']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            exit;
        }

        if ($verify_link === '' && $token !== '') {
            $verify_link = rtrim(SITE_URL, '/') . '/verify_email.php?token=' . urlencode($token);
        }

        $subject = 'Verify Your Email - ' . SITE_NAME;
        $body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
            <h2 style='color: #2c3e50; text-align: center;'>Welcome to " . SITE_NAME . "!</h2>
            <p>Hello {$name},</p>
            <p>Please verify your email by clicking below:</p>
            <div style='text-align: center; margin: 25px 0;'>
                <a href='{$verify_link}' style='background-color: #007bff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Verify My Email</a>
            </div>
            <p style='word-break: break-all; color: #007bff;'>{$verify_link}</p>
        </div>";

        if ($mailer->send($email, $subject, $body)) {
            echo json_encode(['status' => 'success', 'message' => 'Verification email sent successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send verification email']);
        }
        exit;
    }

    if ($action === 'forgot_password') {
        $email = trim($data['email'] ?? '');
        $reset_code = trim($data['reset_code'] ?? '');
        if ($email === '' || $reset_code === '') {
            echo json_encode(['status' => 'error', 'message' => 'Email and reset_code are required']);
            exit;
        }

        $subject = 'Password Reset - ' . SITE_NAME;
        $body = "<p>Your reset code is: <b>{$reset_code}</b></p>";

        if ($mailer->send($email, $subject, $body)) {
            echo json_encode(['status' => 'success', 'message' => 'Reset email sent successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send reset email']);
        }
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
?>
