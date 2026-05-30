<?php
require_once 'config/config.php';
require_once 'includes/SimpleMailer.php';

echo "Starting Tenant Requests Notification Script...\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Ensure the emails_sent column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tenant_requests LIKE 'emails_sent'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tenant_requests ADD COLUMN emails_sent TINYINT(1) DEFAULT 0");
        echo "Added 'emails_sent' column to tenant_requests table.\n";
    }

    // 2. Fetch new tenant requests that haven't been emailed yet
    $sql = "SELECT r.*, u.name as tenant_name, u.email as tenant_email
            FROM tenant_requests r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.emails_sent = 0";
            
    $stmt = $pdo->query($sql);
    $newRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($newRequests)) {
        echo "No new tenant requests to notify.\n";
        exit;
    }

    // 3. Fetch all dealers
    $stmt = $pdo->query("SELECT email, name FROM users WHERE role = 'dealer'");
    $dealers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dealers)) {
        echo "No dealers found to notify.\n";
        exit;
    }

    $mailer = new SimpleMailer();

    // 4. Send emails and update status
    foreach ($newRequests as $request) {
        $propertyType = !empty($request['property_type']) ? $request['property_type'] : 'Any';
        $location = !empty($request['location']) ? $request['location'] : 'Any';
        $budget = !empty($request['budget']) ? number_format($request['budget'], 2) : 'Not specified';
        $messageBody = nl2br(htmlspecialchars($request['message']));

        $subject = "New Tenant Request Alert - " . SITE_NAME;
        
        foreach ($dealers as $dealer) {
            $emailContent = "Hello " . htmlspecialchars($dealer['name']) . ",<br><br>
                             A new tenant request has been posted on <b>" . SITE_NAME . "</b>:<br><br>
                             <b>Tenant:</b> " . htmlspecialchars($request['tenant_name']) . "<br>
                             <b>Looking for:</b> {$propertyType}<br>
                             <b>Location:</b> {$location}<br>
                             <b>Budget:</b> ZMW {$budget}<br><br>
                             <b>Message:</b><br>
                             <blockquote style='border-left: 3px solid #0d6efd; margin: 10px 0; padding: 10px; background-color: #f8f9fa;'>
                                {$messageBody}
                             </blockquote><br><br>
                             Please log in to your dealer dashboard to respond to this request and connect with the tenant.<br><br>
                             <a href='" . SITE_URL . "/login.php' style='display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px;'>Login to Dashboard</a><br><br>
                             Thank you,<br>
                             The " . SITE_NAME . " Team";

            if ($mailer->send($dealer['email'], $subject, $emailContent)) {
                echo "Sent notification to dealer: " . $dealer['email'] . " for request ID " . $request['id'] . "\n";
            } else {
                echo "Failed to send notification to dealer: " . $dealer['email'] . "\n";
            }
            
            // Sleep slightly to avoid overwhelming SMTP server
            usleep(200000); 
        }

        // Mark as sent
        $updateStmt = $pdo->prepare("UPDATE tenant_requests SET emails_sent = 1 WHERE id = :id");
        $updateStmt->execute([':id' => $request['id']]);
        echo "Marked request ID " . $request['id'] . " as successfully sent to all dealers.\n";
    }

    echo "Finished processing tenant requests.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>