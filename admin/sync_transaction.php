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

function buildEmailLayout(string $title, string $bodyContent): string {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f8f9fa; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
            .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
            .header h2 { color: #5D4037; margin: 0; }
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
                <h2>" . htmlspecialchars($title) . "</h2>
            </div>
            <div class='details'>" . $bodyContent . "</div>
            <div class='footer'>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</div>
        </div>
    </body>
    </html>";
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

    $stmtTransaction = $pdo->prepare("SELECT t.user_id, u.role
                                      FROM transactions t
                                      LEFT JOIN users u ON t.user_id = u.id
                                      WHERE t.reference = ?
                                      LIMIT 1");
    $stmtTransaction->execute([$reference]);
    $txn = $stmtTransaction->fetch(PDO::FETCH_ASSOC);

    $stmtPremium = $pdo->prepare("SELECT pc.user_id, u.name, u.email
                                  FROM premium_contacts pc
                                  JOIN users u ON pc.user_id = u.id
                                  WHERE pc.transaction_reference = ?
                                  LIMIT 1");
    $stmtPremium->execute([$reference]);
    $premium = $stmtPremium->fetch(PDO::FETCH_ASSOC);

    $stmtRent = $pdo->prepare("SELECT rp.id, rp.tenant_id, rp.month_year, rp.amount, rp.currency,
                                      tenant.name AS tenant_name, tenant.email AS tenant_email,
                                      dealer.name AS dealer_name, dealer.email AS dealer_email,
                                      p.title AS property_title
                               FROM rent_payments rp
                               JOIN rentals r ON rp.rental_id = r.id
                               JOIN users tenant ON rp.tenant_id = tenant.id
                               JOIN users dealer ON r.dealer_id = dealer.id
                               JOIN properties p ON r.property_id = p.id
                               WHERE rp.reference = ?
                               LIMIT 1");
    $stmtRent->execute([$reference]);
    $rentPayment = $stmtRent->fetch(PDO::FETCH_ASSOC);

    if ($status === 'successful') {
        try {
            require_once '../includes/SimpleMailer.php';
            $mailer = new SimpleMailer();

            // Delay auto-send slightly so the sync finishes first.
            sleep(2);

            if ($premium) {
                $stmtUpdatePremium = $pdo->prepare("UPDATE premium_contacts
                                                    SET status = 'active', lenco_reference = COALESCE(?, lenco_reference)
                                                    WHERE transaction_reference = ?");
                $stmtUpdatePremium->execute([$lenco_ref, $reference]);

                $subject = "Tenant Pro Payment Confirmation - " . SITE_NAME;
                $body = buildEmailLayout(
                    'Payment Received',
                    "<p>Hi " . htmlspecialchars($premium['name']) . ",</p>
                     <p>Your Tenant Pro payment was received successfully.</p>
                     <table>
                        <tr><th>Reference</th><td>" . htmlspecialchars($reference) . "</td></tr>
                        <tr><th>Amount</th><td>" . htmlspecialchars($data['currency'] ?? 'ZMW') . " " . number_format($amount, 2) . "</td></tr>
                        <tr><th>Status</th><td><span style='color:green;font-weight:bold;'>Paid</span></td></tr>
                     </table>
                     <p>You can now unlock landlord contact and WhatsApp access on the website.</p>"
                );
                $mailer->send($premium['email'], $subject, $body);
            } elseif ($rentPayment) {
                $stmtUpdateRent = $pdo->prepare("UPDATE rent_payments
                                                 SET status = 'approved', lenco_reference = COALESCE(?, lenco_reference)
                                                 WHERE id = ?");
                $stmtUpdateRent->execute([$lenco_ref, $rentPayment['id']]);

                $tenantSubject = "Rent Payment Confirmation - " . SITE_NAME;
                $tenantBody = buildEmailLayout(
                    'Rent Payment Received',
                    "<p>Hi " . htmlspecialchars($rentPayment['tenant_name']) . ",</p>
                     <p>Your rent payment was received successfully.</p>
                     <table>
                        <tr><th>Reference</th><td>" . htmlspecialchars($reference) . "</td></tr>
                        <tr><th>Property</th><td>" . htmlspecialchars($rentPayment['property_title']) . "</td></tr>
                        <tr><th>For Month</th><td>" . htmlspecialchars($rentPayment['month_year']) . "</td></tr>
                        <tr><th>Amount</th><td>" . htmlspecialchars($rentPayment['currency']) . " " . number_format((float) $rentPayment['amount'], 2) . "</td></tr>
                        <tr><th>Status</th><td><span style='color:green;font-weight:bold;'>Approved</span></td></tr>
                     </table>"
                );
                $mailer->send($rentPayment['tenant_email'], $tenantSubject, $tenantBody);

                if (!empty($rentPayment['dealer_email'])) {
                    $dealerSubject = "Tenant Lenco Payment Alert - " . SITE_NAME;
                    $dealerBody = buildEmailLayout(
                        'Tenant Payment Received',
                        "<p>Hi " . htmlspecialchars($rentPayment['dealer_name']) . ",</p>
                         <p>Your tenant has paid rent successfully through Lenco.</p>
                         <table>
                            <tr><th>Tenant</th><td>" . htmlspecialchars($rentPayment['tenant_name']) . "</td></tr>
                            <tr><th>Reference</th><td>" . htmlspecialchars($reference) . "</td></tr>
                            <tr><th>Property</th><td>" . htmlspecialchars($rentPayment['property_title']) . "</td></tr>
                            <tr><th>Amount</th><td>" . htmlspecialchars($rentPayment['currency']) . " " . number_format((float) $rentPayment['amount'], 2) . "</td></tr>
                         </table>"
                    );
                    $mailer->send($rentPayment['dealer_email'], $dealerSubject, $dealerBody);
                }
            } elseif ($txn && ($txn['role'] ?? '') === 'dealer') {
                $user = new User();
                $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
                $user->updateSubscription($txn['user_id'], 'active', $expiry);

                try {
                    $prop = new Property();
                    $prop->setFeaturedByDealer($txn['user_id'], 1);
                } catch (Exception $e) {}

                $stmtUser = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
                $stmtUser->execute([$txn['user_id']]);
                $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);

                if ($userData) {
                    $subject = "Subscription Payment Confirmation - " . SITE_NAME;
                    $invoiceLink = SITE_URL . "/dealer/invoice.php?ref=" . $reference;
                    $body = buildEmailLayout(
                        'Payment Received',
                        "<p>Hi " . htmlspecialchars($userData['name']) . ",</p>
                         <p>We have successfully received your payment for the dealer subscription.</p>
                         <table>
                            <tr><th>Reference</th><td>" . htmlspecialchars($reference) . "</td></tr>
                            <tr><th>Date</th><td>" . date('M d, Y H:i') . "</td></tr>
                            <tr><th>Amount</th><td>" . htmlspecialchars($data['currency'] ?? 'ZMW') . " " . number_format($amount, 2) . "</td></tr>
                            <tr><th>Status</th><td><span style='color:green; font-weight:bold;'>Paid</span></td></tr>
                         </table>
                         <center><a href='$invoiceLink' class='btn'>Download Invoice</a></center>"
                    );
                    $mailer->send($userData['email'], $subject, $body);
                }
            }
        } catch (Exception $e) {
            error_log("Failed to send sync email: " . $e->getMessage());
        }
    }

    header("Location: transactions.php?success=Transaction synced successfully&status=$status");

} catch (Exception $e) {
    header("Location: transactions.php?error=" . urlencode($e->getMessage()));
}
?>
