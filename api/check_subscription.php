<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;
if (!$data) $data = $_GET;

$user_id = $data['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID required', 'is_locked' => true]);
    exit;
}

$pdo = (new Database())->connect();

// Check user role and identity verification
$stmtUser = $pdo->prepare("SELECT role, identity_verified FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found', 'is_locked' => true]);
    exit;
}

if ($user['role'] !== 'dealer') {
    echo json_encode(['status' => 'success', 'message' => 'User is not a dealer', 'is_dealer' => false, 'is_locked' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = ?");
$stmt->execute([$user_id]);
$dealerData = $stmt->fetch(PDO::FETCH_ASSOC);

$sub_status = 'inactive';
$expiry = null;

if ($dealerData) {
    $sub_status = $dealerData['subscription_status'];
    $expiry = $dealerData['subscription_expiry'];
    
    // Check if expired
    if ($sub_status === 'active' && !empty($expiry) && strtotime($expiry) < time()) {
        $sub_status = 'expired';
        $upd = $pdo->prepare("UPDATE dealers SET subscription_status = 'expired' WHERE user_id = ?");
        $upd->execute([$user_id]);
    }
}

// Check identity verification state
$identity_status = 'unverified';
$identity_message = 'Please upload your ID to verify your account.';

if ($user['identity_verified'] == 1) {
    $identity_status = 'verified';
    $identity_message = 'Your identity is verified.';
} elseif ($user['identity_verified'] == 2) {
    $identity_status = 'rejected';
    $identity_message = 'Your identity verification was rejected. Please re-upload your document.';
} elseif ($user['identity_verified'] == 0 && !empty($user['verification_doc'])) {
    // If they have uploaded a doc but it's 0, it means it's pending admin review
    $identity_status = 'pending';
    $identity_message = 'Your identity document is pending admin approval.';
}

// Lock the app if they haven't paid OR if they are rejected/unverified
// (If pending, you might still want to lock them out from adding properties, but maybe let them see the dashboard)
$is_payment_locked = ($sub_status !== 'active');
$is_identity_locked = ($identity_status === 'unverified' || $identity_status === 'rejected');

// General lock (true if either payment is bad OR identity is not verified/pending)
$is_locked = ($is_payment_locked || $is_identity_locked);

$lock_message = '';
if ($is_payment_locked) {
    $lock_message = 'Your subscription has expired or is inactive. Please make a payment to continue using the app.';
} elseif ($is_identity_locked) {
    $lock_message = $identity_message;
}

echo json_encode([
    'status' => 'success',
    'is_dealer' => true,
    'plan_name' => 'Premium Dealer Plan',
    'subscription_status' => $sub_status,
    'subscription_expiry' => $expiry,
    'identity_status' => $identity_status,
    'identity_message' => $identity_message,
    'is_payment_locked' => $is_payment_locked,
    'is_identity_locked' => $is_identity_locked,
    'is_locked' => $is_locked,
    'lock_message' => $lock_message
]);
