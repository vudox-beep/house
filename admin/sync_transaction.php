<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Property.php';
require_once '../includes/LencoAPI.php';

// Auth Check (Admin only)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$reference = $_GET['reference'] ?? null;

if (!$reference) {
    header("Location: transactions.php?error=No reference provided");
    exit;
}

try {
    $lenco = new LencoAPI();
    $result = $lenco->verifyTransaction($reference);
    
    $status = 'failed';
    $message = "Unable to verify transaction via API.";
    $lenco_ref = null;
    $amount = 0;
    
    if (isset($result['status']) && $result['status'] === true) {
        $data = $result['data'];
        $status = strtolower($data['status']); // successful, pending, failed
        $message = "Synced from Lenco: " . ucfirst($status);
        $lenco_ref = $data['lencoReference'] ?? $data['id'] ?? null;
        $amount = $data['amount'] ?? 0;
    } else {
        $message = "Lenco API Error: " . ($result['message'] ?? 'Unknown');
    }

    // Update DB
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("UPDATE transactions SET status = ?, message = ?, lenco_reference = ?, updated_at = NOW() WHERE reference = ?");
    $stmt->execute([$status, $message, $lenco_ref, $reference]);
    
    // Also update subscription if successful
    if ($status === 'successful') {
        // Get user_id from transaction
        $stmt = $pdo->prepare("SELECT user_id FROM transactions WHERE reference = ?");
        $stmt->execute([$reference]);
        $txn = $stmt->fetch();
        
        if ($txn) {
            $user = new User();
            $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
            $user->updateSubscription($txn['user_id'], 'active', $expiry);
            
            // Feature properties
            try {
                $prop = new Property();
                $prop->setFeaturedByDealer($txn['user_id'], 1);
            } catch (Exception $e) {}
        }
    }

    header("Location: transactions.php?success=Transaction synced successfully&status=$status");

} catch (Exception $e) {
    header("Location: transactions.php?error=" . urlencode($e->getMessage()));
}
?>