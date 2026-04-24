<?php
/**
 * Dealer Payment API
 * Specifically designed for Flutter/Mobile consumption
 * 
 * Actions:
 * - initiate: Start a mobile money subscription payment
 * - verify: Check status of a pending payment
 * - get_status: Get current subscription status of the dealer
 * - history: Get transaction history for the dealer
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Referral.php';
require_once '../models/Property.php';
require_once '../includes/LencoAPI.php';

// Capture request data
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Fallback to $_REQUEST if JSON is empty
if (empty($data)) {
    $data = $_REQUEST;
}

$action = $data['action'] ?? '';
$user_id = $data['user_id'] ?? '';

// Basic Validation
if (empty($user_id)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'User ID is required'
    ]);
    exit;
}

$userModel = new User();
$user = $userModel->getUserById($user_id);

if (!$user) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'User not found'
    ]);
    exit;
}

if ($user['role'] !== 'dealer') {
    echo json_encode([
        'status' => 'error', 
        'message' => 'User is not a dealer'
    ]);
    exit;
}

$lenco = new LencoAPI();

switch ($action) {
    case 'initiate':
        // --- INITIATE PAYMENT ---
        $phone = $data['phone'] ?? $user['phone'] ?? '';
        $operator = $data['operator'] ?? 'mtn'; // mtn, airtel, zamtel
        $country = $data['country'] ?? 'zm';
        
        if (empty($phone)) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Phone number is required for payment initiation'
            ]);
            exit;
        }

        // Normalize phone: Remove spaces, dashes, plus signs exactly like the website
        $phone = preg_replace('/\D/', '', $phone);

        // Add phone validation matching the website's logic
        $isValidPhone = false;
        $countryLower = strtolower($country);
        if ($countryLower === 'zm') {
            if (strlen($phone) === 10 && (strpos($phone, '09') === 0 || strpos($phone, '07') === 0)) {
                $isValidPhone = true;
            } elseif (strlen($phone) === 9 && (strpos($phone, '9') === 0 || strpos($phone, '7') === 0)) {
                $isValidPhone = true;
            } elseif (strlen($phone) > 10 && strpos($phone, '260') === 0) {
                // If they provided the country code, it's valid too
                $isValidPhone = true;
            }
        } elseif ($countryLower === 'mw') {
            if (strlen($phone) >= 9 && strlen($phone) <= 10) {
                $isValidPhone = true;
            } elseif (strlen($phone) > 10 && strpos($phone, '265') === 0) {
                $isValidPhone = true;
            }
        } else {
            if (strlen($phone) >= 8 && strlen($phone) <= 15) {
                $isValidPhone = true;
            }
        }

        if (!$isValidPhone) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Invalid phone number format for ' . strtoupper($country) . '.'
            ]);
            exit;
        }

        $amount = SUBSCRIPTION_FEE;
        $currency = CURRENCY;
        
        $response = $lenco->initiateMobileMoney($amount, $currency, $phone, $operator, $country);
        
        if (isset($response['status']) && $response['status'] === true) {
            // Lenco sometimes returns reference in different paths
            $reference = $response['data']['reference'] ?? $response['data']['id'] ?? ('SUB-' . uniqid() . '-' . time());
            
            // Log transaction as pending
            try {
                $pdo = (new Database())->connect();
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, reference, amount, currency, status, payment_method) VALUES (?, ?, ?, ?, 'pending', 'mobile-money')");
                $stmt->execute([$user_id, $reference, $amount, $currency]);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment initiated. Check your phone for the USSD/Push prompt.',
                    'reference' => $reference,
                    'lenco_data' => $response['data']
                ]);
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Failed to save transaction: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => $response['message'] ?? 'Lenco API failed to initiate payment',
                'details' => $response
            ]);
        }
        break;

    case 'verify':
        // --- VERIFY PAYMENT ---
        $reference = $data['reference'] ?? '';
        
        if (empty($reference)) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Transaction reference is required for verification'
            ]);
            exit;
        }
        
        $result = $lenco->verifyTransaction($reference);
        
        if (isset($result['status']) && $result['status'] === true) {
            $resData = $result['data'];
            $status = strtolower($resData['status']); // 'successful', 'failed', 'pending'
            
            // Update transaction in DB
            try {
                $pdo = (new Database())->connect();
                $stmt = $pdo->prepare("UPDATE transactions SET status = ?, updated_at = NOW() WHERE reference = ?");
                $stmt->execute([$status, $reference]);
                
                if ($status === 'successful') {
                    // 1. Update Dealer Subscription Status
                    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                    $userModel->updateSubscription($user_id, 'active', $expiry);
                    
                    // 2. Process Referral Commission
                    try {
                        $referralModel = new Referral();
                        $amount_paid = (float)($resData['amount'] ?? SUBSCRIPTION_FEE);
                        $referralModel->processSubscriptionCommission((int)$user_id, $amount_paid);
                    } catch (Exception $e) {
                        // Log referral error but don't fail the payment response
                    }
                    
                    // 3. Auto-feature dealer's properties
                    try {
                        $propertyModel = new Property();
                        $propertyModel->setFeaturedByDealer($user_id, 1);
                    } catch (Exception $e) {}

                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Payment successful! Subscription activated for 30 days.',
                        'payment_status' => 'successful',
                        'expiry' => $expiry
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'pending',
                        'message' => 'Payment is still ' . $status,
                        'payment_status' => $status
                    ]);
                }
            } catch (PDOException $e) {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'Database error during verification: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Lenco could not verify this transaction'
            ]);
        }
        break;

    case 'get_status':
        // --- CHECK SUBSCRIPTION STATUS ---
        $profile = $userModel->getDealerProfile($user_id);
        
        if ($profile) {
            $status = $profile['subscription_status'] ?? 'none';
            $expiry = $profile['subscription_expiry'] ?? null;
            $is_active = ($status === 'active' && (!empty($expiry) && strtotime($expiry) > time()));
            
            echo json_encode([
                'status' => 'success',
                'subscription_status' => $status,
                'subscription_expiry' => $expiry,
                'is_active' => $is_active,
                'currency' => CURRENCY,
                'subscription_fee' => SUBSCRIPTION_FEE
            ]);
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Dealer profile not found'
            ]);
        }
        break;

    case 'history':
        // --- GET TRANSACTION HISTORY ---
        try {
            $pdo = (new Database())->connect();
            $stmt = $pdo->prepare("SELECT reference, amount, currency, status, payment_method, created_at 
                                   FROM transactions 
                                   WHERE user_id = ? 
                                   ORDER BY created_at DESC");
            $stmt->execute([$user_id]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'status' => 'success',
                'history' => $history
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Failed to fetch history: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode([
            'status' => 'error', 
            'message' => 'Invalid action. Supported: initiate, verify, get_status, history'
        ]);
        break;
}
