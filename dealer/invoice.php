<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Session Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("Access denied.");
}

$ref = $_GET['ref'] ?? null;
if (!$ref) {
    die("Invalid reference.");
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Transaction
    $stmt = $pdo->prepare("SELECT t.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                           FROM transactions t 
                           JOIN users u ON t.user_id = u.id 
                           WHERE t.reference = ? AND t.status = 'successful'");
    $stmt->execute([$ref]);
    $txn = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$txn) {
        die("Invoice not found or payment pending.");
    }

    // Authorization: Only Admin or Owner
    if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_id'] != $txn['user_id']) {
        die("Unauthorized access.");
    }

    // Generate PDF (Using simple HTML for now, forcing download via headers)
    $filename = "Invoice_" . $txn['reference'] . ".html"; // Using HTML as simple invoice for now

    header("Content-Type: text/html");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Invoice - <?php echo $txn['reference']; ?></title>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; padding: 40px; color: #333; max-width: 800px; margin: 0 auto; border: 1px solid #eee; }
            .header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
            .logo { font-size: 24px; font-weight: bold; color: #5D4037; }
            .invoice-title { font-size: 32px; color: #aaa; text-transform: uppercase; }
            .meta { display: flex; justify-content: space-between; margin-bottom: 40px; }
            .meta-box h4 { margin: 0 0 10px; font-size: 14px; color: #999; text-transform: uppercase; }
            .meta-box p { margin: 0; font-weight: bold; }
            .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .table th { background: #f9f9f9; padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            .table td { padding: 12px; border-bottom: 1px solid #eee; }
            .total { text-align: right; font-size: 20px; font-weight: bold; margin-top: 20px; }
            .status { color: green; font-weight: bold; border: 2px solid green; padding: 5px 10px; border-radius: 4px; display: inline-block; }
            .footer { margin-top: 50px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 20px; }
            @media print { body { border: none; } }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="logo"><?php echo SITE_NAME; ?></div>
            <div class="invoice-title">INVOICE</div>
        </div>

        <div class="meta">
            <div class="meta-box">
                <h4>Billed To</h4>
                <p><?php echo htmlspecialchars($txn['user_name']); ?></p>
                <p><?php echo htmlspecialchars($txn['user_email']); ?></p>
                <p><?php echo htmlspecialchars($txn['user_phone']); ?></p>
            </div>
            <div class="meta-box" style="text-align: right;">
                <h4>Invoice Details</h4>
                <p>Ref: #<?php echo $txn['reference']; ?></p>
                <p>Date: <?php echo date('M d, Y', strtotime($txn['created_at'])); ?></p>
                <p>Status: <span class="status">PAID</span></p>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Qty</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Dealer Subscription / Verification Check</td>
                    <td>1</td>
                    <td style="text-align: right;"><?php echo $txn['currency'] . ' ' . number_format($txn['amount'], 2); ?></td>
                </tr>
            </tbody>
        </table>

        <div class="total">
            Total: <?php echo $txn['currency'] . ' ' . number_format($txn['amount'], 2); ?>
        </div>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p><?php echo SITE_NAME; ?> | Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
        
        <script>
            window.print();
        </script>
    </body>
    </html>
    <?php

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
