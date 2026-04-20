<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/SimpleMailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Please use POST or GET']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data)) $data = $_POST;
if (empty($data)) $data = $_GET;

$token_user_id = '';
$headers = function_exists('getallheaders') ? getallheaders() : [];
$authorization = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
if (!empty($authorization)) {
    $token = str_replace('Bearer ', '', $authorization);
    $tokenParts = explode('.', $token);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode($tokenParts[1]), true);
        if (isset($payload['id'])) {
            $token_user_id = $payload['id'];
        }
    }
}

$action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === '') {
    if (!empty($data['email']) || !empty($data['property_id']) || !empty($data['rent_amount']) || !empty($data['start_date'])) {
        $action = 'add_tenant';
    } elseif (!empty($data['payment_id']) && !empty($data['status'])) {
        $action = 'verify_payment';
    } else {
        $action = 'get_tenants';
    }
}

try {
if ($action === 'get_tenants') {
    $dealer_id = $data['dealer_id'] ?? $_POST['dealer_id'] ?? $_GET['dealer_id'] ?? $data['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? $token_user_id ?? '';
    if (empty($dealer_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Dealer ID is required']);
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    try {
        // Fetch Existing Tenants
        $sql_tenants = "SELECT r.*, u.name as tenant_name, u.email as tenant_email, u.phone as tenant_phone, p.title as property_title 
                        FROM rentals r 
                        JOIN users u ON r.tenant_id = u.id 
                        JOIN properties p ON r.property_id = p.id 
                        WHERE r.dealer_id = :did 
                        ORDER BY r.created_at DESC";
        $stmt_tenants = $conn->prepare($sql_tenants);
        $stmt_tenants->execute([':did' => $dealer_id]);
        $tenants = $stmt_tenants->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching tenants: ' . $e->getMessage()]);
        exit;
    }

    // Calculate next due date for each tenant
    foreach ($tenants as &$t) {
        try {
            $sql_last_paid = "SELECT month_year, amount FROM rent_payments 
                              WHERE rental_id = :rid AND status = 'approved' 
                              ORDER BY id DESC LIMIT 1";
            $stmt_last = $conn->prepare($sql_last_paid);
            $stmt_last->execute([':rid' => $t['id']]);
            $last_paid = $stmt_last->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $last_paid = null;
        }

        $start_str = !empty($t['start_date']) ? $t['start_date'] : 'now';
        try {
            $start_date = new DateTime($start_str);
        } catch (Exception $e) {
            $start_date = new DateTime('now');
        }
        $start_day = (int)$start_date->format('d');

        if ($last_paid) {
            $last_paid_date = DateTime::createFromFormat('!F Y', $last_paid['month_year']);
            if ($last_paid_date) {
                $rent_amount = (isset($t['rent_amount']) && $t['rent_amount'] > 0) ? $t['rent_amount'] : 1;
                $months_paid = max(1, round($last_paid['amount'] / $rent_amount));
                
                $next_due = clone $last_paid_date;
                $next_due->modify('+' . $months_paid . ' month');
                
                $days_in_month = (int)$next_due->format('t');
                $target_day = min($start_day, $days_in_month);
                $next_due->setDate((int)$next_due->format('Y'), (int)$next_due->format('m'), $target_day);
                
                $t['next_due_date'] = $next_due->format('M d, Y');
            } else {
                 $t['next_due_date'] = date('M d, Y', strtotime('+1 month'));
            }
        } else {
            $t['next_due_date'] = $start_date->format('M d, Y');
        }
    }
    unset($t);

    try {
        // Fetch Recent Activity (Payments History)
        $sql_history = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title, r.payment_reference 
                         FROM rent_payments rp
                         JOIN rentals r ON rp.rental_id = r.id
                         JOIN users u ON rp.tenant_id = u.id
                         JOIN properties p ON r.property_id = p.id
                         WHERE r.dealer_id = :did AND rp.status != 'pending'
                         ORDER BY rp.created_at DESC LIMIT 10";
        $stmt_history = $conn->prepare($sql_history);
        $stmt_history->execute([':did' => $dealer_id]);
        $history_payments = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $history_payments = [];
    }

    try {
        // Fetch Pending Payments (Waiting for Dealer Approval)
        $sql_pending = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title, r.payment_reference 
                         FROM rent_payments rp
                         JOIN rentals r ON rp.rental_id = r.id
                         JOIN users u ON rp.tenant_id = u.id
                         JOIN properties p ON r.property_id = p.id
                         WHERE r.dealer_id = :did AND rp.status = 'pending'
                         ORDER BY rp.created_at DESC";
        $stmt_pending = $conn->prepare($sql_pending);
        $stmt_pending->execute([':did' => $dealer_id]);
        $pending_payments = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $pending_payments = [];
    }

    // Fetch Total Views and Active Listings for Dealer
    $total_views = 0;
    $active_listings = 0;
    try {
        // Fetch total views
        $stmt_views = $conn->prepare("SELECT SUM(views) as total_views FROM properties WHERE dealer_id = :did");
        $stmt_views->execute([':did' => $dealer_id]);
        $view_result = $stmt_views->fetch(PDO::FETCH_ASSOC);
        $total_views = $view_result['total_views'] ?? 0;

        // Fetch active listings count
        $stmt_listings = $conn->prepare("SELECT COUNT(*) as active_listings FROM properties WHERE dealer_id = :did AND status = 'available'");
        $stmt_listings->execute([':did' => $dealer_id]);
        $listings_result = $stmt_listings->fetch(PDO::FETCH_ASSOC);
        $active_listings = $listings_result['active_listings'] ?? 0;

    } catch(PDOException $e) {
        // If columns don't exist yet, we just return 0
    }

    // Ensure proof_of_payment has full URL for History
    foreach ($history_payments as &$payment) {
        if (!empty($payment['receipt_image']) && strpos($payment['receipt_image'], 'http') !== 0) {
            $payment['receipt_image'] = rtrim(SITE_URL, '/') . '/' . ltrim($payment['receipt_image'], '/');
        }
        $payment['proof_of_payment'] = $payment['receipt_image'] ?? null;
    }
    unset($payment);

    // Ensure proof_of_payment has full URL for Pending
    foreach ($pending_payments as &$payment) {
        if (!empty($payment['receipt_image']) && strpos($payment['receipt_image'], 'http') !== 0) {
            $payment['receipt_image'] = rtrim(SITE_URL, '/') . '/' . ltrim($payment['receipt_image'], '/');
        }
        $payment['proof_of_payment'] = $payment['receipt_image'] ?? null;
    }
    unset($payment);

    $total_tenants = count($tenants);
    $active_tenants = 0;
    foreach ($tenants as $tenant_item) {
        if (isset($tenant_item['status']) && strtolower((string)$tenant_item['status']) === 'active') {
            $active_tenants++;
        }
    }
    $history_count = count($history_payments);
    $pending_count = count($pending_payments);

    echo json_encode([
        'status' => 'success',
        'tenants' => $tenants,
        'recent_activity' => $history_payments,
        'pending_payments' => $pending_payments,
        'total_tenants' => $total_tenants,
        'active_tenants' => $active_tenants,
        'history_count' => $history_count,
        'pending_count' => $pending_count,
        'total_views' => $total_views,
        'active_listings' => $active_listings
    ]);
    exit;
}

// Handle Approve/Reject Payment
if ($action === 'verify_payment') {
    $dealer_id = $data['dealer_id'] ?? $_POST['dealer_id'] ?? $data['user_id'] ?? $_POST['user_id'] ?? $token_user_id ?? '';
    $payment_id = $data['payment_id'] ?? $_POST['payment_id'] ?? '';
    $verify_status = $data['status'] ?? $_POST['status'] ?? ''; // 'approved' or 'rejected'

    if (empty($dealer_id) || empty($payment_id) || !in_array($verify_status, ['approved', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields or invalid status']);
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    // Verify payment belongs to this dealer's tenant
    $sql_verify = "SELECT rp.id, rp.receipt_image FROM rent_payments rp 
                   JOIN rentals r ON rp.rental_id = r.id 
                   WHERE rp.id = :pid AND r.dealer_id = :did";
    $stmt_verify = $conn->prepare($sql_verify);
    $stmt_verify->execute([':pid' => $payment_id, ':did' => $dealer_id]);
    $payment_record = $stmt_verify->fetch(PDO::FETCH_ASSOC);
    
    if ($payment_record) {
        $sql_update = "UPDATE rent_payments SET status = :status WHERE id = :pid";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update->execute([':status' => $verify_status, ':pid' => $payment_id])) {
            
            $proof_url = null;
            if (!empty($payment_record['receipt_image'])) {
                $proof_url = rtrim(SITE_URL, '/') . '/' . ltrim($payment_record['receipt_image'], '/');
            }

            echo json_encode([
                'status' => 'success', 
                'message' => "Payment " . $verify_status . " successfully.",
                'proof_of_payment' => $proof_url
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update payment status.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid payment record or unauthorized.']);
    }
    exit;
}

// Default action: add_tenant
$dealer_id = $data['dealer_id'] ?? $_POST['dealer_id'] ?? $_REQUEST['dealer_id'] ?? $data['user_id'] ?? $_REQUEST['user_id'] ?? $token_user_id ?? '';
$email = trim($data['email'] ?? $_POST['email'] ?? $_REQUEST['email'] ?? '');
$property_id = $data['property_id'] ?? $_POST['property_id'] ?? $_REQUEST['property_id'] ?? '';
$rent_amount = $data['rent_amount'] ?? $_POST['rent_amount'] ?? $_REQUEST['rent_amount'] ?? '';
$start_date = $data['start_date'] ?? $_POST['start_date'] ?? $_REQUEST['start_date'] ?? '';
$end_date = !empty($data['end_date']) ? $data['end_date'] : (!empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : null);
$room_number = !empty($data['room_number']) ? trim($data['room_number']) : (!empty($_REQUEST['room_number']) ? trim($_REQUEST['room_number']) : null);
$initial_payment_months = isset($data['initial_payment_months']) ? (int)$data['initial_payment_months'] : (isset($_REQUEST['initial_payment_months']) ? (int)$_REQUEST['initial_payment_months'] : 0);

if (empty($dealer_id) || empty($email) || empty($property_id) || empty($rent_amount) || empty($start_date)) {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Missing required fields',
        'debug' => [
            'dealer_id' => $dealer_id,
            'email' => $email,
            'property_id' => $property_id,
            'rent_amount' => $rent_amount,
            'start_date' => $start_date,
            'raw_post' => $_POST,
            'raw_get' => $_GET,
            'raw_input' => file_get_contents("php://input")
        ]
    ]);
    exit;
}

$db = new Database();
$conn = $db->connect();

// 1. Check Dealer Subscription & Verification Status
$stmt_user = $conn->prepare("SELECT u.identity_verified, d.subscription_status, d.subscription_expiry 
                             FROM users u 
                             LEFT JOIN dealers d ON u.id = d.user_id 
                             WHERE u.id = :id AND u.role = 'dealer'");
$stmt_user->execute([':id' => $dealer_id]);
$dealer_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$dealer_data) {
    echo json_encode(['status' => 'error', 'message' => 'Dealer not found or Invalid ID. ID passed: ' . htmlspecialchars($dealer_id)]);
    exit;
}

if ($dealer_data['identity_verified'] != 1) {
    echo json_encode(['status' => 'error', 'message' => 'Dealer identity must be verified before adding tenants.']);
    exit;
}

if ($dealer_data['subscription_status'] !== 'active' || strtotime($dealer_data['subscription_expiry']) <= time()) {
    echo json_encode(['status' => 'error', 'message' => 'Your subscription has expired. Please renew to manage tenants.']);
    exit;
}

// 2. Check if user exists with this email
$sql_check = "SELECT id, role FROM users WHERE email = :email";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->execute([':email' => $email]);
$tenant = $stmt_check->fetch(PDO::FETCH_ASSOC);

$tenant_id = null;

if ($tenant) {
    if ($tenant['role'] == 'dealer') {
        echo json_encode(['status' => 'error', 'message' => 'This email belongs to a Dealer account. Please use a Tenant account email.']);
        exit;
    } else {
        $tenant_id = $tenant['id'];
    }
} else {
    // User does not exist - Create a temporary/placeholder account
    $temp_password = bin2hex(random_bytes(4)); // 8 char temp password
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    $name = explode('@', $email)[0]; // Default name from email
    
    $sql_create = "INSERT INTO users (name, email, password, role, is_verified) VALUES (:name, :email, :pass, 'user', 1)";
    $stmt_create = $conn->prepare($sql_create);
    if ($stmt_create->execute([
        ':name' => $name,
        ':email' => $email,
        ':pass' => $hashed_password
    ])) {
        $tenant_id = $conn->lastInsertId();
        
        // Send Invite Email with credentials
        $mailer = new SimpleMailer();
        $subject = "You've been added as a Tenant - " . SITE_NAME;
        $body = "Hello,<br><br>You have been added as a tenant on " . SITE_NAME . ".<br>
                 Your login details:<br>
                 Email: $email<br>
                 Password: $temp_password<br><br>
                 Please login at " . SITE_URL . "/login.php and change your password immediately.";
        $mailer->send($email, $subject, $body);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create new tenant account.']);
        exit;
    }
}

if ($tenant_id) {
    // 3. Generate 16-digit Payment Reference ID
    $payment_reference = '';
    for ($i = 0; $i < 16; $i++) {
        $payment_reference .= mt_rand(0, 9);
    }

    // 4. Create Rental Record
    try {
        $sql_rental = "INSERT INTO rentals (property_id, dealer_id, tenant_id, start_date, end_date, rent_amount, status, payment_reference, room_number) 
                       VALUES (:pid, :did, :tid, :start, :end, :amount, 'active', :ref, :room)";
        $stmt_rental = $conn->prepare($sql_rental);
        
        $stmt_rental->execute([
            ':pid' => $property_id,
            ':did' => $dealer_id,
            ':tid' => $tenant_id,
            ':start' => $start_date,
            ':end' => $end_date,
            ':amount' => $rent_amount,
            ':ref' => $payment_reference,
            ':room' => $room_number
        ]);
        
        $rental_id = $conn->lastInsertId();

        // 5. Handle Initial Payment (if provided)
        if ($initial_payment_months > 0) {
            $total_paid = $rent_amount * $initial_payment_months;
            $month_year = date('F Y', strtotime($start_date));
            
            $sql_pay = "INSERT INTO rent_payments (rental_id, tenant_id, month_year, amount, currency, status, payment_method, months_paid, dealer_notes) 
                        VALUES (:rid, :tid, :my, :amt, 'ZMW', 'approved', 'cash', :months, 'Initial payment recorded by dealer')";
            $stmt_pay = $conn->prepare($sql_pay);
            $stmt_pay->execute([
                ':rid' => $rental_id,
                ':tid' => $tenant_id,
                ':my' => $month_year,
                ':amt' => $total_paid,
                ':months' => $initial_payment_months
            ]);
        }

        // 6. Send Reference ID via email
        $mailer = new SimpleMailer();
        $subject = "Welcome! Your Payment Reference ID - " . SITE_NAME;
        $body = "Hello,<br><br>You have been successfully added as a tenant.<br>
                 <b>Important:</b> Please use the following Reference ID for all bank deposits:<br>
                 <h2 style='color: #2c3e50; letter-spacing: 2px;'>$payment_reference</h2>
                 <br>
                 Login to your dashboard to view details.";
        $mailer->send($email, $subject, $body);

        echo json_encode([
            'status' => 'success',
            'message' => 'Tenant added successfully! Reference ID generated and email sent.',
            'payment_reference' => $payment_reference,
            'tenant_id' => $tenant_id,
            'rental_id' => $rental_id
        ]);
    } catch (PDOException $e) {
        // Catch Foreign Key constraints or any DB errors
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    }
}
} catch (Exception $e) {
    // Return 200 OK so Flutter can read the JSON error message instead of crashing with a 500 status code
    echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
}
