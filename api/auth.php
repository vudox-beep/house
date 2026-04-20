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

$action = $data['action'] ?? '';

if ($action === 'login') {
    // --- LOGIN LOGIC ---
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
        // NEW: Check if the user is logging into the correct app!
        $expected_role = $data['role'] ?? null;
        if ($expected_role && $loggedInUser['role'] !== $expected_role) {
            echo json_encode([ 
                "status" => "error", 
                "message" => "Account mismatch. You cannot log into the " . ucfirst($expected_role) . " app with a " . ucfirst($loggedInUser['role']) . " account.", 
                "code" => "wrong_role" 
            ]); 
            exit; 
        }

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

} elseif ($action === 'register') {
    // --- REGISTER LOGIC ---
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
        
        if ($mailer->send($email, $subject, $body)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful. Please check your email to verify your account.'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful, but we could not send the verification email at this time. Please contact support.'
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Registration failed. Please try again.']);
    }

} else {
    // --- INVALID ACTION ---
    echo json_encode(['status' => 'error', 'message' => 'Invalid action. Must provide "action": "login" or "action": "register".']);
}
