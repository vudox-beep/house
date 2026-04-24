<?php
require_once 'config/config.php';
require_once 'models/Property.php';
require_once 'models/User.php';
require_once 'includes/SimpleMailer.php';

echo "Starting Rent Notification Script...\n";

$db = new Database();
$conn = $db->connect();
$mailer = new SimpleMailer();

function calculateNextRentDue(array $rental, PDO $conn): array {
    $sql = "SELECT month_year, amount, months_paid
            FROM rent_payments
            WHERE rental_id = :rental_id AND status = 'approved'
            ORDER BY id DESC
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':rental_id' => $rental['id']]);
    $lastPaid = $stmt->fetch(PDO::FETCH_ASSOC);

    $startDate = new DateTime($rental['start_date']);
    $startDay = (int) $startDate->format('d');

    if ($lastPaid) {
        $lastPaidDate = DateTime::createFromFormat('!F Y', $lastPaid['month_year']);
        if ($lastPaidDate) {
            $rentAmount = (float) ($rental['rent_amount'] ?? 0);
            $monthsPaid = (int) ($lastPaid['months_paid'] ?? 1);
            if ($monthsPaid < 1 && $rentAmount > 0) {
                $monthsPaid = max(1, (int) round(((float) $lastPaid['amount']) / $rentAmount));
            }

            $nextDue = clone $lastPaidDate;
            $nextDue->modify('+' . max(1, $monthsPaid) . ' month');

            $daysInMonth = (int) $nextDue->format('t');
            $targetDay = min($startDay, $daysInMonth);
            $nextDue->setDate((int) $nextDue->format('Y'), (int) $nextDue->format('m'), $targetDay);

            return [
                'month_year' => $nextDue->format('F Y'),
                'due_date' => $nextDue
            ];
        }
    }

    return [
        'month_year' => $startDate->format('F Y'),
        'due_date' => $startDate
    ];
}

// Get current date details
$current_day = date('j'); // Day of the month (1-31)
$current_month_year = date('F Y'); // e.g., "March 2026"
$next_month_year = date('F Y', strtotime('+1 month'));
$today = new DateTime('today');

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
    $dueData = calculateNextRentDue($rental, $conn);
    $target_month = $dueData['month_year'];
    $days_until_due = (int) $today->diff($dueData['due_date'])->format('%r%a');

    // Check if payment already exists for this month
    $sql_check_pay = "SELECT id, status FROM rent_payments 
                      WHERE rental_id = :rid AND month_year = :my";
    $stmt_check = $conn->prepare($sql_check_pay);
    
    $stmt_check->execute([':rid' => $rental['id'], ':my' => $target_month]);
    $payment = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    $should_notify_tenant = false;
    $should_notify_dealer = false;
    $subject = "";
    $message = "";

    // Case 1: Exact 5-day reminder before the next due date
    if ($days_until_due === 5 && !$payment) {
        $should_notify_tenant = true;
        $subject = "Upcoming Rent Due: " . $target_month . " - " . SITE_NAME;
        $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                    This is a reminder that your rent for <b>" . $target_month . "</b> is due in 5 days.<br>
                    Property: " . $rental['property_title'] . "<br>
                    Due Date: " . $dueData['due_date']->format('M d, Y') . "<br>
                    Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                    Please login to your dashboard to view payment details and upload your proof of payment.<br>
                    <a href='" . SITE_URL . "/login.php'>Login Here</a>";
    } elseif ($current_day >= 25 && !$payment) {
        // Keep the fallback month-end reminder for tenants on monthly billing cycles.
        $fallbackMonth = $next_month_year;
        if ($fallbackMonth === $target_month) {
            $should_notify_tenant = true;
            $subject = "Upcoming Rent Due: " . $target_month . " - " . SITE_NAME;
            $message = "Hello " . $rental['tenant_name'] . ",<br><br>
                        This is a reminder that your rent for <b>" . $target_month . "</b> is due soon.<br>
                        Property: " . $rental['property_title'] . "<br>
                        Amount: " . $rental['currency'] . " " . number_format($rental['rent_amount']) . "<br><br>
                        Please login to your dashboard to view payment details and upload your proof of payment.<br>
                        <a href='" . SITE_URL . "/login.php'>Login Here</a>";
        }
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
