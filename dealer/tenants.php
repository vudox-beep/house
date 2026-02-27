<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';
require_once '../includes/SimpleMailer.php';

$dealer_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// Fetch Dealer Data (for bank details and subscription)
$stmt_user = $conn->prepare("SELECT u.bank_details, d.subscription_status, d.subscription_expiry 
                             FROM users u 
                             LEFT JOIN dealers d ON u.id = d.user_id 
                             WHERE u.id = :id");
$stmt_user->execute([':id' => $dealer_id]);
$dealer_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Check Subscription
$is_subscribed = false;
if ($dealer_data['subscription_status'] === 'active' && strtotime($dealer_data['subscription_expiry']) > time()) {
    $is_subscribed = true;
}

// Block Actions if Not Subscribed
if (!$is_subscribed) {
    // If they try to submit a form, block it
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $error = "Your subscription has expired. Please renew to manage your tenants.";
        // Prevent further processing (except for simple navigation)
        // We will just show the error and not run the logic below
    }
} else {
    // Only process POST requests if subscribed
    
    // Handle Payment Actions (Approve/Reject)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['payment_id'])) {
        $payment_id = $_POST['payment_id'];
        $action = $_POST['action']; // 'approve' or 'reject'
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        // Verify payment belongs to this dealer's tenant
        $sql_verify = "SELECT rp.id FROM rent_payments rp 
                       JOIN rentals r ON rp.rental_id = r.id 
                       WHERE rp.id = :pid AND r.dealer_id = :did";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->execute([':pid' => $payment_id, ':did' => $dealer_id]);
        
        if ($stmt_verify->fetch()) {
            // Update Status
            $sql_update = "UPDATE rent_payments SET status = :status WHERE id = :pid";
            $stmt_update = $conn->prepare($sql_update);
            if ($stmt_update->execute([':status' => $status, ':pid' => $payment_id])) {
                $_SESSION['success'] = "Payment " . $status . " successfully.";
            } else {
                $_SESSION['error'] = "Failed to update payment status.";
            }
        } else {
            $_SESSION['error'] = "Invalid payment record.";
        }
        header("Location: tenants.php");
        exit();
    }

    // Add Tenant Logic
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tenant'])) {
        $email = trim($_POST['email']);
        $property_id = $_POST['property_id'];
        $rent_amount = $_POST['rent_amount'];
        $start_date = $_POST['start_date'];
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        // 1. Check if user exists with this email
        $sql_check = "SELECT id, role FROM users WHERE email = :email";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->execute([':email' => $email]);
        $tenant = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        $tenant_id = null;
        
        if ($tenant) {
            // User exists
            if ($tenant['role'] == 'dealer') {
                $error = "This email belongs to a Dealer account. Please use a Tenant account email.";
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
                         Property: <b>Active Rental</b><br>
                         Your login details:<br>
                         Email: $email<br>
                         Password: $temp_password<br><br>
                         Please login at " . SITE_URL . "/login.php and change your password immediately.";
                $mailer->send($email, $subject, $body);
            } else {
                $error = "Failed to create new tenant account.";
            }
        }
        
        if ($tenant_id) {
            // Generate 16-digit Payment Reference ID (e.g., numeric string)
            $payment_reference = '';
            for ($i = 0; $i < 16; $i++) {
                $payment_reference .= mt_rand(0, 9);
            }

            // 3. Create Rental Record
            $sql_rental = "INSERT INTO rentals (property_id, dealer_id, tenant_id, start_date, end_date, rent_amount, status, payment_reference) 
                           VALUES (:pid, :did, :tid, :start, :end, :amount, 'active', :ref)";
            $stmt_rental = $conn->prepare($sql_rental);
            if ($stmt_rental->execute([
                ':pid' => $property_id,
                ':did' => $dealer_id,
                ':tid' => $tenant_id,
                ':start' => $start_date,
                ':end' => $end_date,
                ':amount' => $rent_amount,
                ':ref' => $payment_reference
            ])) {
                // Send Reference ID via email
                $mailer = new SimpleMailer();
                $subject = "Welcome! Your Payment Reference ID - " . SITE_NAME;
                $body = "Hello,<br><br>You have been successfully added as a tenant.<br>
                         <b>Important:</b> Please use the following Reference ID for all bank deposits:<br>
                         <h2 style='color: #2c3e50; letter-spacing: 2px;'>$payment_reference</h2>
                         <br>
                         Login to your dashboard to view details.";
                $mailer->send($email, $subject, $body);

                $success = "Tenant added successfully! Reference ID generated: <b>$payment_reference</b>";
            } else {
                $error = "Failed to add tenant to property.";
            }
        }
        
        if ($success) $_SESSION['success'] = $success;
        if ($error) $_SESSION['error'] = $error;
        header("Location: tenants.php");
        exit();
    }

    // Update Bank Details
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bank_details'])) {
        $bank_details = trim($_POST['bank_details']);
        $sql_update_bank = "UPDATE users SET bank_details = :bank WHERE id = :id";
        $stmt_update_bank = $conn->prepare($sql_update_bank);
        if ($stmt_update_bank->execute([':bank' => $bank_details, ':id' => $dealer_id])) {
            $_SESSION['success'] = "Bank details updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update bank details.";
        }
        header("Location: tenants.php");
        exit();
    }
}

// Include Header
include 'includes/header.php';

// Fetch Dealer's Properties (for dropdown)
$sql_props = "SELECT id, title FROM properties WHERE dealer_id = :did AND status != 'rented'"; 
$stmt_props = $conn->prepare($sql_props);
$stmt_props->execute([':did' => $dealer_id]);
$properties = $stmt_props->fetchAll(PDO::FETCH_ASSOC);

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

// Fetch Pending Payments
$sql_payments = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title, r.payment_reference 
                 FROM rent_payments rp
                 JOIN rentals r ON rp.rental_id = r.id
                 JOIN users u ON rp.tenant_id = u.id
                 JOIN properties p ON r.property_id = p.id
                 WHERE r.dealer_id = :did AND rp.status = 'pending'
                 ORDER BY rp.created_at DESC";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->execute([':did' => $dealer_id]);
$pending_payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

// Fetch Payment History (Approved/Rejected)
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

// Fetch Dealer Data (for bank details)
$stmt_user = $conn->prepare("SELECT bank_details FROM users WHERE id = :id");
$stmt_user->execute([':id' => $dealer_id]);
$dealer_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Fetch All Payment History (Grouped by Tenant)
$sql_all_history = "SELECT rp.*, u.id as tenant_id 
                    FROM rent_payments rp
                    JOIN rentals r ON rp.rental_id = r.id
                    JOIN users u ON rp.tenant_id = u.id
                    WHERE r.dealer_id = :did AND rp.status != 'pending'
                    ORDER BY rp.created_at DESC";
$stmt_all_history = $conn->prepare($sql_all_history);
$stmt_all_history->execute([':did' => $dealer_id]);
$all_history_raw = $stmt_all_history->fetchAll(PDO::FETCH_ASSOC);

// Group by Tenant ID
$tenant_history = [];
foreach ($all_history_raw as $h) {
    $tenant_history[$h['tenant_id']][] = $h;
}

?>

<div class="dealer-main bg-light min-vh-100">
    <style>
        .dealer-main {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .page-shell {
            width: 100%;
            max-width: 1600px; /* Increased max-width for better desktop usage */
            margin: 0 auto;
        }
        .card {
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        /* ... existing styles ... */
        
        /* DESKTOP TABLE STYLES (min-width: 992px) */
        @media (min-width: 992px) {
            .table-responsive {
                overflow: visible; /* Allow table to take full width without scrollbar constraints if space allows */
            }
            .table-custom, .table-compact {
                width: 100% !important;
                table-layout: auto;
            }
            .table-custom th, .table-custom td {
                white-space: nowrap; /* Prevent wrapping on desktop for cleaner look */
            }
            /* Allow Tenant Name to wrap if needed, or keep it consistent */
            .table-custom td:first-child {
                white-space: normal;
            }
        }

        /* MOBILE STYLES (max-width: 768px) */
        @media (max-width: 768px) {
            .table-custom thead {
                display: none; /* Hide headers on mobile if using card view */
            }
            .table-custom, .table-custom tbody, .table-custom tr, .table-custom td {
                display: block;
                width: 100%;
            }
            .table-custom tr {
                margin-bottom: 1rem;
                background: #fff;
                border: 1px solid #eee;
                border-radius: 8px;
                padding: 1rem;
            }
            .table-custom td {
                text-align: right;
                padding: 0.5rem 0;
                border-bottom: 1px solid #f8f9fa;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .table-custom td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.75rem;
                color: #6c757d;
            }
            .table-custom td:last-child {
                border-bottom: none;
            }
        }
        .card:hover {
            box-shadow: 0 4px 18px rgba(0,0,0,0.06);
        }
        .table-custom {
            border-collapse: separate;
            border-spacing: 0;
        }
        .table-custom thead th {
            background-color: #f8f9fa;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 1rem 1.5rem;
            border-bottom: 2px solid #e9ecef;
        }
        .table-custom tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f3f5;
            color: #495057;
            font-size: 0.9rem;
        }
        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }
        .table-custom tbody tr:hover {
            background-color: #f8f9fa;
        }
        .avatar-initials {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 2px 5px rgba(37, 117, 252, 0.2);
        }
        .status-badge {
            padding: 0.4em 0.8em;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 6px;
        }
        .status-badge.active { background-color: #d1e7dd; color: #0f5132; }
        .status-badge.pending { background-color: #fff3cd; color: #664d03; }
        .status-badge.ended { background-color: #e2e3e5; color: #41464b; }
        
        .action-btn {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: background-color 0.2s;
            color: #6c757d;
        }
        .action-btn:hover {
            background-color: #e9ecef;
            color: #212529;
        }
        .panel-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c757d;
            font-weight: 700;
        }
        .table-compact thead th {
            padding: 0.75rem 1rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: 0.04em;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        .table-compact tbody td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f1f3f5;
            font-size: 0.85rem;
        }
    </style>
    <div class="dealer-topbar bg-white border-bottom py-3 px-4 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                 <button class="btn btn-link d-md-none me-3 text-dark p-0" id="sidebarToggle">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <div>
                    <h4 class="mb-1 fw-bold text-dark">Tenant Management</h4>
                    <p class="text-muted small mb-0">Overview of your active rentals and payments</p>
                </div>
            </div>
            <div>
                 <button type="button" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm" <?php echo $is_subscribed ? 'data-bs-toggle="modal" data-bs-target="#addTenantModal"' : 'disabled'; ?>>
                    <i class="bi bi-plus-lg"></i> 
                    <span class="d-none d-md-inline">New Tenant</span>
                </button>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="page-shell">
        
        <?php if(!$is_subscribed): ?>
            <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
                <div class="text-center">
                    <div class="mb-4 text-warning">
                        <i class="bi bi-lock-fill" style="font-size: 4rem;"></i>
                    </div>
                    <h2 class="fw-bold mb-3">Access Restricted</h2>
                    <p class="text-muted mb-4 fs-5" style="max-width: 500px; margin: 0 auto;">
                        Your dealer subscription is currently <strong>inactive</strong>. <br>
                        Please renew your subscription to manage tenants and view payments.
                    </p>
                    <a href="subscribe.php" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-credit-card-2-front me-2"></i> Renew Subscription
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Main Content for Subscribed Users -->
            
            <?php if($error): ?>
                <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row g-4">
            <div class="col-xl-2 col-lg-3">
                
                <div class="card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="panel-title">Payment Info</div>
                            <span class="badge bg-light text-primary rounded-pill"><i class="bi bi-wallet2"></i></span>
                        </div>
                        <h5 class="fw-bold mb-3">Deposit Instructions</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <textarea class="form-control bg-light border-0 small" name="bank_details" rows="4" style="font-size: 0.9rem;" placeholder="Enter bank details here..."><?php echo htmlspecialchars($dealer_data['bank_details'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" name="update_bank_details" class="btn btn-outline-primary btn-sm w-100">Update Details</button>
                        </form>
                    </div>
                </div>

                <?php if(count($pending_payments) > 0): ?>
                <div class="card mb-4 border-warning border-start border-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                        <div class="panel-title text-warning">Pending Approvals</div>
                        <span class="badge bg-warning text-dark"><?php echo count($pending_payments); ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pending_payments as $pay): ?>
                                    <tr>
                                        <td class="fw-semibold">
                                            <?php echo htmlspecialchars($pay['tenant_name']); ?>
                                            <div class="small text-muted font-monospace mt-1">Ref: <?php echo htmlspecialchars($pay['payment_reference'] ?? 'N/A'); ?></div>
                                        </td>
                                        <td class="text-muted small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                        <td class="text-muted"><?php echo htmlspecialchars($pay['month_year']); ?></td>
                                        <td class="fw-semibold"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline-flex gap-1">
                                                <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                <?php if($pay['proof_file']): ?>
                                                    <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-light border btn-sm" title="Proof"><i class="bi bi-file-earmark-image"></i></a>
                                                <?php endif; ?>
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm" title="Approve"><i class="bi bi-check"></i></button>
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" title="Reject"><i class="bi bi-x"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="panel-title">Recent Payments</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-compact mb-0">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($history_payments) > 0): ?>
                                    <?php foreach($history_payments as $pay): ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?php echo htmlspecialchars($pay['tenant_name']); ?>
                                                <div class="small text-muted font-monospace mt-1">Ref: <?php echo htmlspecialchars($pay['payment_reference'] ?? 'N/A'); ?></div>
                                            </td>
                                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($pay['month_year']); ?></td>
                                            <td class="text-end fw-semibold"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No history yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="col-xl-10 col-lg-9">
                <div class="card">
                    <div class="card-header bg-white py-3 border-bottom">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="fw-bold mb-0">Active Tenants</h6>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control bg-light border-0" placeholder="Search tenants...">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom w-100 mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 25%;">Tenant</th>
                                    <th style="width: 15%;">Reference ID</th>
                                    <th style="width: 20%;">Property</th>
                                    <th style="width: 15%;">Rent</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 10%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($tenants) > 0): ?>
                                    <?php foreach($tenants as $t): ?>
                                        <tr>
                                            <td data-label="Tenant">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-initials me-3 flex-shrink-0">
                                                        <?php echo strtoupper(substr($t['tenant_name'], 0, 1)); ?>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['tenant_name']); ?></div>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($t['tenant_email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Reference ID">
                                                <?php if(!empty($t['payment_reference'])): ?>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-light text-dark border font-monospace"><?php echo chunk_split($t['payment_reference'], 4, ' '); ?></span>
                                                        <button class="btn btn-link btn-sm p-0 text-muted" onclick="navigator.clipboard.writeText('<?php echo $t['payment_reference']; ?>')" title="Copy"><i class="bi bi-copy"></i></button>
                                                    </div>
                                                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">For Bank Deposits</small>
                                                <?php else: ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Property">
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="bi bi-house-door me-2"></i>
                                                    <span class="text-truncate" style="max-width: 180px;"><?php echo htmlspecialchars($t['property_title']); ?></span>
                                                </div>
                                            </td>
                                            <td data-label="Rent">
                                                <div class="fw-bold text-dark"><?php echo $t['currency'] . ' ' . number_format($t['rent_amount']); ?></div>
                                                <small class="text-muted">per month</small>
                                            </td>
                                            <td data-label="Status">
                                                <div class="d-flex flex-column gap-1">
                                                    <div>
                                                        <?php 
                                                            $sClass = match($t['status']) {
                                                                'active' => 'active',
                                                                'pending' => 'pending',
                                                                default => 'ended'
                                                            };
                                                        ?>
                                                        <span class="status-badge <?php echo $sClass; ?>"><?php echo ucfirst($t['status']); ?></span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php if($t['end_date']): ?>
                                                            Ends: <?php echo date('M d, Y', strtotime($t['end_date'])); ?>
                                                        <?php else: ?>
                                                            Month-to-Month
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-end" data-label="Actions">
                                                <div class="dropdown">
                                                    <button class="action-btn ms-auto" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                        <li><h6 class="dropdown-header">Manage</h6></li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $t['tenant_id']; ?>">
                                                                <i class="bi bi-receipt me-2"></i> Payment History
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $t['tenant_id']; ?>">
                                                                <i class="bi bi-eye me-2"></i> See Details
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-person-x me-2"></i> End Tenancy</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="py-4">
                                                <div class="mb-3 text-muted opacity-25">
                                                    <i class="bi bi-people fs-1"></i>
                                                </div>
                                                <h5 class="fw-bold text-muted">No active tenants</h5>
                                                <p class="text-muted small mb-3">Get started by adding a new tenant to your property.</p>
                                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTenantModal">Add Tenant</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </div>
    
    <?php endif; ?>
    </div>
    </div>
    <!-- End Main Content -->
    
    <!-- Add Tenant Modal (Only rendered if subscribed) -->
    <?php if($is_subscribed): ?>
    <div class="modal fade" id="addTenantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Add New Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Select Property</label>
                            <select class="form-select" name="property_id" required>
                                <option value="">Choose property...</option>
                                <?php foreach($properties as $prop): ?>
                                    <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold">Tenant Email</label>
                            <input type="email" class="form-control" name="email" placeholder="tenant@example.com" required>
                            <div class="form-text small">If the user exists, they will be linked. Otherwise, a new account will be created.</div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">Rent Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 bg-light">K</span>
                                    <input type="number" class="form-control border-start-0 ps-0" name="rent_amount" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-muted small fw-bold">Start Date</label>
                                <input type="date" class="form-control" name="start_date" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">End Date (Optional)</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="add_tenant" class="btn btn-primary py-2 fw-bold">Add Tenant</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- History & Details Modals -->
    <?php foreach($tenants as $t): ?>
    <!-- History Modal -->
    <div class="modal fade" id="historyModal<?php echo $t['tenant_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom">
                    <div>
                        <h5 class="modal-title fw-bold">Payment History</h5>
                        <p class="mb-0 text-muted small">Tenant: <?php echo htmlspecialchars($t['tenant_name']); ?></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3 small text-muted text-uppercase">Date</th>
                                    <th class="px-4 py-3 small text-muted text-uppercase">Month</th>
                                    <th class="px-4 py-3 small text-muted text-uppercase">Amount</th>
                                    <th class="px-4 py-3 small text-muted text-uppercase">Status</th>
                                    <th class="px-4 py-3 small text-muted text-uppercase text-end">Proof</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $t_history = $tenant_history[$t['tenant_id']] ?? [];
                                if(count($t_history) > 0): 
                                    foreach($t_history as $hist):
                                ?>
                                <tr>
                                    <td class="px-4 py-3"><?php echo date('M d, Y', strtotime($hist['created_at'])); ?></td>
                                    <td class="px-4 py-3 fw-medium"><?php echo htmlspecialchars($hist['month_year']); ?></td>
                                    <td class="px-4 py-3 fw-bold"><?php echo number_format($hist['amount']); ?></td>
                                    <td class="px-4 py-3">
                                        <?php if($hist['status'] == 'approved'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Approved</span>
                                        <?php elseif($hist['status'] == 'rejected'): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <?php if($hist['proof_file']): ?>
                                            <a href="../<?php echo $hist['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-clock-history fs-1 d-block mb-2 opacity-25"></i>
                                        No payment history found for this tenant.
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal<?php echo $t['tenant_id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Tenant Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="avatar-initials mx-auto mb-3 fs-3" style="width: 64px; height: 64px;">
                            <?php echo strtoupper(substr($t['tenant_name'], 0, 1)); ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($t['tenant_name']); ?></h5>
                        <p class="text-muted mb-0"><?php echo htmlspecialchars($t['tenant_email']); ?></p>
                        <?php if(!empty($t['tenant_phone'])): ?>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($t['tenant_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="list-group list-group-flush border rounded-3">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small fw-bold">PROPERTY</span>
                            <span class="fw-medium text-end"><?php echo htmlspecialchars($t['property_title']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small fw-bold">RENT AMOUNT</span>
                            <span class="fw-bold text-primary"><?php echo $t['currency'] . ' ' . number_format($t['rent_amount']); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small fw-bold">START DATE</span>
                            <span><?php echo date('M d, Y', strtotime($t['start_date'])); ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="text-muted small fw-bold">END DATE</span>
                            <span><?php echo $t['end_date'] ? date('M d, Y', strtotime($t['end_date'])) : 'Month-to-Month'; ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                            <span class="text-muted small fw-bold">REFERENCE ID</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="font-monospace fw-bold"><?php echo chunk_split($t['payment_reference'] ?? 'N/A', 4, ' '); ?></span>
                                <?php if(!empty($t['payment_reference'])): ?>
                                <button class="btn btn-link btn-sm p-0 text-muted" onclick="navigator.clipboard.writeText('<?php echo $t['payment_reference']; ?>')"><i class="bi bi-copy"></i></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
