<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data)) $data = $_POST;
if (empty($data)) $data = $_GET;

$user_id = $data['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? '';

if (empty($user_id)) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (!empty($authorization)) {
        $token = str_replace('Bearer ', '', $authorization);
        $tokenParts = explode('.', $token);
        if (count($tokenParts) === 3) {
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if (isset($payload['id'])) {
                $user_id = $payload['id'];
            }
        }
    }
}

if (empty($user_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User ID required',
        'is_locked' => true
    ]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmtUser = $conn->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            'status' => 'error',
            'message' => 'User not found',
            'is_locked' => true
        ]);
        exit;
    }

    if ($user['role'] !== 'dealer') {
        echo json_encode([
            'status' => 'success',
            'is_dealer' => false,
            'name' => $user['name'],
            'account_type' => 'non_dealer',
            'paid_type' => 'none',
            'subscription_status' => 'none',
            'subscription_expiry' => null,
            'is_locked' => false,
            'lock_message' => ''
        ]);
        exit;
    }

    $stmtDealer = $conn->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = ?");
    $stmtDealer->execute([$user_id]);
    $dealer = $stmtDealer->fetch(PDO::FETCH_ASSOC);

    $subscription_status = 'inactive';
    $subscription_expiry = null;

    if ($dealer) {
        $subscription_status = strtolower(trim((string)($dealer['subscription_status'] ?? 'inactive')));
        $subscription_expiry = $dealer['subscription_expiry'];

        if ($subscription_status !== 'active' && !empty($subscription_expiry) && $subscription_expiry !== '0000-00-00 00:00:00' && strtotime($subscription_expiry) > time()) {
            $subscription_status = 'active';
            $stmtUpd = $conn->prepare("UPDATE dealers SET subscription_status = 'active' WHERE user_id = ?");
            $stmtUpd->execute([$user_id]);
        } elseif ($subscription_status === 'active' && !empty($subscription_expiry) && $subscription_expiry !== '0000-00-00 00:00:00' && strtotime($subscription_expiry) < time()) {
            $subscription_status = 'expired';
            $stmtUpd = $conn->prepare("UPDATE dealers SET subscription_status = 'expired' WHERE user_id = ?");
            $stmtUpd->execute([$user_id]);
        }
    }

    $is_locked = ($subscription_status !== 'active');
    $paid_type = $subscription_status === 'active' ? 'paid' : 'unpaid';
    $account_type = $subscription_status === 'active' ? 'Dealer Pro' : ($subscription_status === 'expired' ? 'Expired Dealer' : 'Free Trial');
    $lock_message = '';

    if ($subscription_status === 'expired') {
        $lock_message = 'Your subscription has expired. Please renew to continue.';
    } elseif ($is_locked) {
        $lock_message = 'Your subscription is inactive. Please make payment to continue.';
    }

    $days_remaining = 0;
    if (!empty($subscription_expiry) && $subscription_expiry !== '0000-00-00 00:00:00') {
        $seconds = strtotime($subscription_expiry) - time();
        $days_remaining = $seconds > 0 ? (int)floor($seconds / 86400) : 0;
    }

    echo json_encode([
        'status' => 'success',
        'is_dealer' => true,
        'user_id' => (int)$user_id,
        'name' => $user['name'],
        'account_type' => $account_type,
        'paid_type' => $paid_type,
        'subscription_status' => $subscription_status,
        'subscription_expiry' => $subscription_expiry,
        'days_remaining' => $days_remaining,
        'is_locked' => $is_locked,
        'lock_message' => $lock_message
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
