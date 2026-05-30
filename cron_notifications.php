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

echo "Done processing rent notifications.\n";

// =========================================================================
// 2. NOTIFY DEALERS: New Tenant Requests
// =========================================================================
echo "\nStarting Tenant Requests Notification Script...\n";

try {
    // 1. Ensure the emails_sent column exists
    $stmt = $conn->query("SHOW COLUMNS FROM tenant_requests LIKE 'emails_sent'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE tenant_requests ADD COLUMN emails_sent TINYINT(1) DEFAULT 0");
        echo "Added 'emails_sent' column to tenant_requests table.\n";
    }

    // 2. Fetch new tenant requests that haven't been emailed yet
    $sql_requests = "SELECT r.*, u.name as tenant_name, u.email as tenant_email
                     FROM tenant_requests r 
                     JOIN users u ON r.user_id = u.id 
                     WHERE r.emails_sent = 0";
            
    $stmt_requests = $conn->query($sql_requests);
    $newRequests = $stmt_requests->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($newRequests)) {
        // 3. Fetch all dealers
        $stmt_dealers = $conn->query("SELECT email, name FROM users WHERE role = 'dealer'");
        $dealers = $stmt_dealers->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($dealers)) {
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
                        echo "Sent request notification to dealer: " . $dealer['email'] . "\n";
                    }
                    
                    // Sleep slightly to avoid overwhelming SMTP server
                    usleep(200000); 
                }

                // Mark as sent
                $updateStmt = $conn->prepare("UPDATE tenant_requests SET emails_sent = 1 WHERE id = :id");
                $updateStmt->execute([':id' => $request['id']]);
                echo "Marked request ID " . $request['id'] . " as successfully sent to all dealers.\n";
            }
        } else {
            echo "No dealers found to notify for tenant requests.\n";
        }
    } else {
        echo "No new tenant requests to notify.\n";
    }

    echo "Finished processing tenant requests.\n";

} catch (Exception $e) {
    echo "Error processing tenant requests: " . $e->getMessage() . "\n";
}

// =========================================================================
// 3. NOTIFY TENANTS: New Property Listings
// =========================================================================
echo "\nStarting New Property Notification Script...\n";

try {
    // 1. Ensure the emails_sent column exists on properties
    $stmt = $conn->query("SHOW COLUMNS FROM properties LIKE 'emails_sent'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE properties ADD COLUMN emails_sent TINYINT(1) DEFAULT 0");
        echo "Added 'emails_sent' column to properties table.\n";
    }

    // 2. Fetch new properties that haven't been emailed yet
    $sql_properties = "SELECT p.*, d.name as dealer_name
                       FROM properties p 
                       JOIN users d ON p.dealer_id = d.id 
                       WHERE p.emails_sent = 0 AND p.status = 'available'";
            
    $stmt_properties = $conn->query($sql_properties);
    $newProperties = $stmt_properties->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($newProperties)) {
        // 3. Fetch all tenants (users)
        $stmt_tenants = $conn->query("SELECT email, name FROM users WHERE role = 'user'");
        $tenants = $stmt_tenants->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($tenants)) {
            // 4. Send emails and update status
            foreach ($newProperties as $property) {
                $propertyType = !empty($property['property_type']) ? ucfirst($property['property_type']) : 'Property';
                $location = !empty($property['location']) ? $property['location'] : 'Any';
                $price = !empty($property['price']) ? number_format($property['price'], 2) : 'Negotiable';
                $currency = !empty($property['currency']) ? $property['currency'] : 'ZMW';
                $title = !empty($property['title']) ? $property['title'] : 'New Listing';
                $purpose = !empty($property['listing_purpose']) ? ucfirst($property['listing_purpose']) : 'Rent/Sale';

                $subject = "New " . $purpose . " Listing Alert - " . SITE_NAME;
                
                foreach ($tenants as $tenant) {
                    $emailContent = "Hello " . htmlspecialchars($tenant['name']) . ",<br><br>
                                     A new property for <b>" . strtolower($purpose) . "</b> has just been listed on <b>" . SITE_NAME . "</b>:<br><br>
                                     <b>Title:</b> " . htmlspecialchars($title) . "<br>
                                     <b>Type:</b> {$propertyType}<br>
                                     <b>Location:</b> " . htmlspecialchars($location) . "<br>
                                     <b>Price:</b> {$currency} {$price}<br>
                                     <b>Listed By:</b> " . htmlspecialchars($property['dealer_name']) . "<br><br>
                                     Log in or open the app to view more details, pictures, and contact the landlord.<br><br>
                                     <a href='" . SITE_URL . "/property_details.php?id=" . $property['id'] . "' style='display: inline-block; padding: 10px 20px; background-color: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px;'>View Property</a><br><br>
                                     Thank you,<br>
                                     The " . SITE_NAME . " Team";

                    if ($mailer->send($tenant['email'], $subject, $emailContent)) {
                        // Uncomment for debugging if needed: echo "Sent new listing notification to tenant: " . $tenant['email'] . "\n";
                    }
                    
                    // Sleep slightly to avoid overwhelming SMTP server
                    usleep(200000); 
                }

                // Mark as sent
                $updateStmt = $conn->prepare("UPDATE properties SET emails_sent = 1 WHERE id = :id");
                $updateStmt->execute([':id' => $property['id']]);
                echo "Marked property ID " . $property['id'] . " as successfully sent to all tenants.\n";
            }
        } else {
            echo "No tenants found to notify for new properties.\n";
        }
    } else {
        echo "No new properties to notify.\n";
    }

    echo "Finished processing new property listings.\n";

} catch (Exception $e) {
    echo "Error processing new properties: " . $e->getMessage() . "\n";
}

echo "\nAll cron tasks completed.\n";
?>
