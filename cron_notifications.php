<?php
require_once 'config/config.php';
require_once 'models/Property.php';
require_once 'models/User.php';
require_once 'includes/SimpleMailer.php';

echo "Starting Rent Notification Script...\n";

$db = new Database();
$conn = $db->connect();
$mailer = new SimpleMailer();

// Get current date details
$current_day = date('j'); // Day of the month (1-31)
$current_month_year = date('F Y'); // e.g., "March 2026"
$next_month_year = date('F Y', strtotime('+1 month'));

// 1. NOTIFY TENANTS: Payment Due Soon (e.g., sent on 25th of previous month)
// OR Payment Due Today (e.g., sent on 1st of current month)

$sql_active_rentals = "SELECT r.*, t.name as tenant_name, t.email as tenant_email, 
                              d.name as dealer_name, d.email as dealer_email, 
                              p.title as property_title, p.location
                       FROM rentals r
                       JOIN users t ON r.tenant_id = t.id
                       JOIN users d ON r.dealer_id = d.id
                       JOIN properties p ON r.property_id = p.id
                       WHERE r.status = 'active'";

$stmt = $conn->prepare($sql_active_rentals);
$stmt->execute();
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($rentals as $rental) {
    // Check if payment already exists for this month
    $sql_check_pay = "SELECT id, status FROM rent_payments 
                      WHERE rental_id = :rid AND month_year = :my";
    $stmt_check = $conn->prepare($sql_check_pay);
    
    // We are checking for the UPCOMING month if it's late in the current month (e.g., 25th)
    // Or CURRENT month if it's early (e.g., 1st-5th)
    
    // LOGIC:
    // If today is 25th-31st: Remind for NEXT month
    // If today is 1st-5th: Remind for CURRENT month if not paid
    
    $target_month = ($current_day >= 25) ? $next_month_year : $current_month_year;
    
    $stmt_check->execute([':rid' => $rental['id'], ':my' => $target_month]);
    $payment = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    $should_notify_tenant = false;
    $should_notify_dealer = false;
    $subject = "";
    $message = "";

    // Case 1: Upcoming Due Date (e.g., sent on 28th)
    if ($current_day >= 25 && !$payment) {
        $should_notify_tenant = true;
        $subject = "Upcoming Rent Due: " . $target_month . " - " . SITE_NAME;
        $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                    This is a reminder that your rent for <b>" . $target_month . "</b> is due soon.<br>
                    Property: " . $rental['property_title'] . "<br>
                    Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                    Please login to your dashboard to view payment details and upload your proof of payment.<br>
                    <a href='" . SITE_URL . "/login.php'>Login Here</a>";
    }
    
// Case 2a: Due Today (1st of the month)
if ($current_day == 1 && !$payment) {
    $should_notify_tenant = true;
    $subject = "Rent Payment Due Today: " . $target_month . " - " . SITE_NAME;
    $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                Your rent for <b>" . $target_month . "</b> is due today.<br>
                Property: " . $rental['property_title'] . "<br>
                Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                Please login to your dashboard to upload proof of payment.<br>
                <a href='" . SITE_URL . "/login.php'>Login Here</a>";
}

// Case 2b: Gentle Reminder (3rd of the month)
if ($current_day == 3 && !$payment) {
    $should_notify_tenant = true;
    $subject = "Friendly Reminder: Rent for " . $target_month . " - " . SITE_NAME;
    $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                This is a friendly reminder to submit your rent payment for <b>" . $target_month . "</b>.<br>
                Property: " . $rental['property_title'] . "<br>
                Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                <a href='" . SITE_URL . "/login.php'>Login Here</a>";
}

// Case 2c: Overdue (5th of the month)
if ($current_day == 5 && !$payment) {
    $should_notify_tenant = true;
    $should_notify_dealer = true; // Alert dealer that tenant hasn't paid by the 5th
    
    $subject = "Rent Payment Overdue: " . $target_month . " - " . SITE_NAME;
    $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                We haven't received your rent payment for <b>" . $target_month . "</b> yet.<br>
                Property: " . $rental['property_title'] . "<br>
                Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                Please make the payment immediately to avoid penalties.<br>
                <a href='" . SITE_URL . "/login.php'>Login Here</a>";
                
    // Dealer Message
    $dealer_subject = "Tenant Payment Overdue: " . $rental['tenant_name'];
    $dealer_message = "Hello " . $rental['dealer_name'] . ",<br><br>
                       Your tenant <b>" . $rental['tenant_name'] . "</b> has not yet submitted payment for <b>" . $target_month . "</b>.<br>
                       Property: " . $rental['property_title'] . "<br><br>
                       You may want to follow up with them.";
}

    // Send Emails
    if ($should_notify_tenant) {
        echo "Sending email to Tenant: " . $rental['tenant_email'] . "\n";
        $mailer->send($rental['tenant_email'], $subject, $message);
    }
    
    if ($should_notify_dealer) {
        echo "Sending email to Dealer: " . $rental['dealer_email'] . "\n";
        $mailer->send($rental['dealer_email'], $dealer_subject, $dealer_message);
    }
}

echo "Done.\n";
?>
