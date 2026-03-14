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

            // Send Email with Invoice
            try {
                require_once '../includes/SimpleMailer.php';
                $mailer = new SimpleMailer();
                
                // Get User Details
                $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
                $stmtUser->execute([$txn['user_id']]);
                $userData = $stmtUser->fetch();

                if ($userData) {
                    $subject = "Subscription Payment Confirmation - " . SITE_NAME;
                    $invoiceLink = SITE_URL . "/dealer/invoice.php?ref=" . $reference;
                    
                    $body = "
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: 'Arial', sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
                            .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
                            .header h2 { color: #5D4037; }
                            .details { margin: 20px 0; }
                            .details table { width: 100%; border-collapse: collapse; }
                            .details th, .details td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
                            .btn { display: inline-block; padding: 12px 24px; background-color: #fbbf24; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
                            .footer { margin-top: 20px; text-align: center; font-size: 12px; color: #999; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h2>Payment Received</h2>
                                <p>Thank you for your subscription!</p>
                            </div>
                            <div class='details'>
                                <p>Hi " . htmlspecialchars($userData['name']) . ",</p>
                                <p>We have successfully received your payment for the dealer subscription.</p>
                                <table>
                                    <tr><th>Reference</th><td>" . htmlspecialchars($reference) . "</td></tr>
                                    <tr><th>Date</th><td>" . date('M d, Y H:i') . "</td></tr>
                                    <tr><th>Amount</th><td>" . htmlspecialchars($data['currency'] ?? 'ZMW') . " " . number_format($amount, 2) . "</td></tr>
                                    <tr><th>Status</th><td><span style='color:green; font-weight:bold;'>Paid</span></td></tr>
                                </table>
                                <center>
                                    <a href='$invoiceLink' class='btn'>Download Invoice</a>
                                </center>
                            </div>
                            <div class='footer'>
                                &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.
                            </div>
                        </div>
                    </body>
                    </html>";

                    $mailer->send($userData['email'], $subject, $body);
                }
            } catch (Exception $e) {
                // Log email error but don't stop process
                error_log("Failed to send subscription email: " . $e->getMessage());
            }
        }
    }

    header("Location: transactions.php?success=Transaction synced successfully&status=$status");

} catch (Exception $e) {
    header("Location: transactions.php?error=" . urlencode($e->getMessage()));
}
?>