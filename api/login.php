<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password required']);
    exit;
}

$userModel = new User();
$loggedInUser = $userModel->login($email, $password);

if ($loggedInUser === "unverified") {
    echo json_encode(['status' => 'error', 'message' => 'Please verify your email address before logging in.', 'code' => 'email_unverified']);
    exit;
} elseif ($loggedInUser === "banned") {
    echo json_encode(['status' => 'error', 'message' => 'Your account has been banned.', 'code' => 'banned']);
    exit;
} elseif ($loggedInUser) {
    // Check subscription and identity verification
    $sub_status = 'inactive';
    $identity_verified = $loggedInUser['identity_verified'] ?? 0;
    
    if ($loggedInUser['role'] === 'dealer') {
        $pdo = (new Database())->connect();
        $stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = ?");
        $stmt->execute([$loggedInUser['id']]);
        $dealerData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($dealerData) {
            $sub_status = $dealerData['subscription_status'];
            $expiry = $dealerData['subscription_expiry'];
            if ($sub_status === 'active' && strtotime($expiry) < time()) {
                $sub_status = 'expired';
                // update in DB
                $upd = $pdo->prepare("UPDATE dealers SET subscription_status = 'expired' WHERE user_id = ?");
                $upd->execute([$loggedInUser['id']]);
            }
        }
        
        // Let the app decide to lock them out based on status, but we return it here
        if ($sub_status !== 'active') {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Your subscription has expired or is inactive. Please pay to access your account.',
                'code' => 'subscription_inactive',
                'user' => [
                    'id' => $loggedInUser['id'],
                    'name' => $loggedInUser['name'],
                    'email' => $loggedInUser['email'],
                    'role' => $loggedInUser['role'],
                    'subscription_status' => $sub_status
                ]
            ]);
            exit;
        }
    }
    
    // Remove sensitive data
    unset($loggedInUser['password']);
    unset($loggedInUser['verification_token']);
    
    $loggedInUser['subscription_status'] = $sub_status;
    $loggedInUser['identity_verified'] = $identity_verified;
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => $loggedInUser
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
}
