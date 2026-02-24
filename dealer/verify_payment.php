<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../models/User.php';
require_once '../includes/LencoAPI.php';

check_dealer();

// Include Header
include 'includes/header.php';

$reference = $_GET['reference'] ?? $_GET['tx_ref'] ?? null;

if (!$reference) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>No transaction reference found. <a href='subscribe.php'>Go Back</a></div></div>";
    exit;
}

$lenco = new LencoAPI();
$result = $lenco->verifyTransaction($reference);
$is_valid = false;
$message = "Payment verification failed.";
$status = 'failed';
$amount = 0;
$currency = 'ZMW';
$payment_method = 'unknown';
$lenco_ref = null;

if (isset($result['status']) && $result['status'] === true) {
    $data = $result['data'];
    $status = strtolower($data['status']);
    $amount = $data['amount'] ?? 0;
    $currency = $data['currency'] ?? 'ZMW';
    $payment_method = $data['type'] ?? $data['channel'] ?? 'unknown';
    $lenco_ref = $data['lencoReference'] ?? $data['id'] ?? null;
    
    if ($status === 'successful') {
        $is_valid = true;
    } elseif ($status === 'pending' || $status === 'submitted') {
        $message = "Payment is currently <strong>PENDING</strong>. <br>Please approve the prompt on your phone if using Mobile Money.";
    } else {
        $message = "Payment status: " . ucfirst($status);
    }
} else {
    $message = "Unable to verify transaction. " . ($result['message'] ?? '');
}

// Log Transaction
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if transaction exists
    $stmt = $pdo->prepare("SELECT id FROM transactions WHERE reference = ?");
    $stmt->execute([$reference]);
    
    if ($stmt->fetch()) {
        // Update
        $stmt = $pdo->prepare("UPDATE transactions SET status = ?, message = ?, updated_at = NOW() WHERE reference = ?");
        $stmt->execute([$status, $message, $reference]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, reference, lenco_reference, amount, currency, status, message, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $reference, $lenco_ref, $amount, $currency, $status, $message, $payment_method]);
    }
} catch (PDOException $e) {
    // Log error silently
}

if ($is_valid) {
    $user = new User();
    // Set expiry to 30 days from now
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    if ($user->updateSubscription($_SESSION['user_id'], 'active', $expiry)) {
        // Feature all existing properties for this user
        try {
            $prop = new Property();
            $prop->setFeaturedByDealer($_SESSION['user_id'], 1);
        } catch (Exception $e) {}

        // Success View
        echo "
        <div class='container mt-5 text-center'>
            <div class='card shadow-sm border-0 rounded-3 p-5'>
                <div class='mb-4 text-success'>
                    <i class='bi bi-check-circle-fill display-1'></i>
                </div>
                <h2 class='fw-bold text-success'>Payment Successful!</h2>
                <p class='lead text-muted mb-4'>Your subscription has been activated.</p>
                <p class='text-muted'>Reference: <strong>$reference</strong></p>
                <a href='dashboard.php' class='btn btn-primary btn-lg px-5 mt-3'>Go to Dashboard</a>
            </div>
        </div>";
    } else {
        echo "<div class='container mt-5'><div class='alert alert-warning'>Payment successful but failed to update subscription. Please contact support. Ref: $reference</div></div>";
    }
} else {
    // Failure/Pending View
    echo "
    <div class='container mt-5 text-center'>
        <div class='card shadow-sm border-0 rounded-3 p-5'>
            <div class='mb-4 text-warning'>
                <i class='bi bi-exclamation-circle-fill display-1'></i>
            </div>
            <h3 class='fw-bold'>Verification Status</h3>
            <p class='lead mb-4'>$message</p>
            <div class='d-flex justify-content-center gap-3'>
                <a href='verify_payment.php?reference=$reference' class='btn btn-outline-primary'>Refresh Status</a>
                <a href='subscribe.php' class='btn btn-light'>Try Again</a>
            </div>
        </div>
    </div>";
}
?>
