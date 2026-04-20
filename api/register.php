<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/SimpleMailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';
$phone = $data['phone'] ?? '';
$role = $data['role'] ?? 'user'; // 'user' or 'dealer'

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Name, email, and password are required']);
    exit;
}

$userModel = new User();

if ($userModel->emailExists($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
    exit;
}

$token = bin2hex(random_bytes(32));
$expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

$userData = [
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'role' => $role,
    'phone' => $phone,
    'whatsapp_number' => $data['whatsapp_number'] ?? '',
    'verification_token' => $token,
    'token_expiry' => $expiry,
    'subscription_status' => (ENABLE_FREE_TRIAL && $role === 'dealer') ? 'active' : 'inactive',
    'subscription_expiry' => (ENABLE_FREE_TRIAL && $role === 'dealer') ? date('Y-m-d H:i:s', strtotime('+' . FREE_TRIAL_DURATION . ' days')) : null
];

if ($userModel->register($userData)) {
    // Send verification email
    $mailer = new SimpleMailer();
    $verifyLink = SITE_URL . "/verify_email.php?token=" . $token;
    $subject = "Verify Your Account - " . SITE_NAME;
    
    $body = "
    <h2>Welcome to " . SITE_NAME . ", $name!</h2>
    <p>Please verify your email address by clicking the link below:</p>
    <a href='$verifyLink' style='display:inline-block;background:#fbbf24;color:#000;padding:10px 20px;text-decoration:none;border-radius:5px;'>Verify Email Address</a>
    <p>Or copy this link: $verifyLink</p>
    ";
    
    $mailer->send($email, $subject, $body);

    echo json_encode([
        'status' => 'success',
        'message' => 'Registration successful. Please check your email to verify your account.'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Registration failed. Please try again.']);
}
