<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Referral.php';
require_once '../includes/LencoAPI.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Must be POST']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$user_id = $data['user_id'] ?? '';
$action = $data['action'] ?? 'initiate'; // 'initiate' or 'verify'

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

$lenco = new LencoAPI();

if ($action === 'initiate') {
    // --- INITIATE PAYMENT (Mobile Money Example) ---
    $phone = $data['phone'] ?? '';
    $operator = $data['operator'] ?? 'mtn'; // mtn, airtel, zamtel
    $country = $data['country'] ?? 'zm';
    
    // First get the user to pull their info (email, name) for Lenco
    $pdo = (new Database())->connect();
    $stmtUser = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
        exit;
    }

    if (empty($phone)) {
        // Fallback to user profile phone if not provided in request
        $phone = $user['phone'] ?? '';
        if (empty($phone)) {
            echo json_encode(['status' => 'error', 'message' => 'Phone number is required for mobile money']);
            exit;
        }
    }
    
    $amount = SUBSCRIPTION_FEE;
    $currency = CURRENCY;
    
    // Use the getPaid method equivalent from backend by initiating a transaction
    // LencoAPI.php has initiateMobileMoney which creates a backend collection request.
    $response = $lenco->initiateMobileMoney($amount, $currency, $phone, $operator, $country);
    
    if (isset($response['status']) && $response['status'] === true) {
        // The reference is nested differently sometimes depending on Lenco API version.
        // Usually it's in data -> reference or data -> id
        $reference = $response['data']['reference'] ?? $response['data']['id'] ?? ('SUB-' . uniqid() . '-' . time());
        
        // Save pending transaction to DB
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, reference, amount, currency, status, payment_method) VALUES (?, ?, ?, ?, 'pending', 'mobile-money')");
            $stmt->execute([$user_id, $reference, $amount, $currency]);
        } catch (PDOException $e) {}
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Payment initiated. Please check your phone for the prompt.',
            'reference' => $reference,
            'lenco_response' => $response['data']
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $response['message'] ?? 'Failed to initiate payment'
        ]);
    }

} elseif ($action === 'verify') {
    // --- VERIFY PAYMENT ---
    $reference = $data['reference'] ?? '';
    
    if (empty($reference)) {
        echo json_encode(['status' => 'error', 'message' => 'Reference is required for verification']);
        exit;
    }
    
    $result = $lenco->verifyTransaction($reference);
    
    if (isset($result['status']) && $result['status'] === true) {
        $resData = $result['data'];
        $status = strtolower($resData['status']);
        $message = "Payment status: " . ucfirst($status);
        
        // Update DB
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $stmt = $pdo->prepare("UPDATE transactions SET status = ?, message = ?, updated_at = NOW() WHERE reference = ?");
            $stmt->execute([$status, $message, $reference]);
        } catch (PDOException $e) {}
        
        if ($status === 'successful') {
            // Update User Subscription
            $user = new User();
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            $user->updateSubscription($user_id, 'active', $expiry);
            
            // Referral Logic: Pay commission
            try {
                $referralModel = new Referral();
                $referralModel->processSubscriptionCommission((int)$user_id, $resData['amount'] ?? SUBSCRIPTION_FEE);
            } catch (Exception $e) {}
            
            // Feature all existing properties for this user
            try {
                require_once '../models/Property.php';
                $prop = new Property();
                $prop->setFeaturedByDealer($user_id, 1);
            } catch (Exception $e) {}
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Payment successful and subscription activated.',
                'payment_status' => 'successful'
            ]);
        } else {
            echo json_encode([
                'status' => 'pending',
                'message' => 'Payment is still ' . $status,
                'payment_status' => $status
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unable to verify transaction']);
    }
} elseif ($action === 'history') {
    // --- FETCH DEALER PAYMENT HISTORY ---
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->prepare("SELECT id, reference, amount, currency, status, payment_method, message, created_at, updated_at 
                               FROM transactions 
                               WHERE user_id = ? 
                               ORDER BY created_at DESC");
        $stmt->execute([$user_id]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'status' => 'success',
            'transactions' => $transactions
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch history: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
