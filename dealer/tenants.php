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
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $error = "Your subscription has expired. Please renew to manage your tenants.";
    }
} else {
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

// Include Custom CSS
echo '<link rel="stylesheet" href="../assets/css/dealer_tenants.css?v=' . time() . '">';

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

<!-- Main Content Area -->
<div class="tenant-manager-wrapper">
    
    <!-- Custom Topbar -->
    <header class="tm-topbar">
        <div class="tm-topbar-left">
            <button class="btn btn-link d-md-none text-dark p-0 me-3" id="tmSidebarToggle">
                <i class="bi bi-list fs-2"></i>
            </button>
            <h1 class="h4 mb-0 fw-bold text-dark">Tenant Management</h1>
        </div>
        <div class="tm-topbar-right">
            <button type="button" class="btn btn-primary-modern shadow-sm" <?php echo $is_subscribed ? 'data-bs-toggle="modal" data-bs-target="#addTenantModal"' : 'disabled'; ?>>
                <i class="bi bi-plus-lg me-2"></i>
                <span>Add Tenant</span>
            </button>
        </div>
    </header>

    <div class="tm-content container-fluid p-4">
        
        <?php if(!$is_subscribed): ?>
            <!-- Subscription Alert -->
            <div class="empty-state-container text-center py-5">
                <div class="mb-4 text-warning">
                    <i class="bi bi-lock-fill" style="font-size: 4rem;"></i>
                </div>
                <h2 class="fw-bold mb-3">Access Restricted</h2>
                <p class="text-muted mb-4 fs-5" style="max-width: 500px; margin: 0 auto;">
                    Your dealer subscription is currently <strong>inactive</strong>. <br>
                    Please renew your subscription to manage tenants and view payments.
                </p>
                <a href="subscribe.php" class="btn btn-primary-modern btn-lg px-5">
                    <i class="bi bi-credit-card-2-front me-2"></i> Renew Subscription
                </a>
            </div>
        <?php else: ?>
            
            <!-- Notifications -->
            <?php if($error): ?>
                <div class="alert alert-danger shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row g-4">
                
                <!-- Main Content Column -->
                <div class="col-12">
                    
                    <!-- Bank Info (Collapsible or Small Section) -->
                    <div class="tm-card mb-4">
                        <div class="tm-card-header" data-bs-toggle="collapse" data-bs-target="#bankDetailsCollapse" style="cursor: pointer;">
                            <h6 class="mb-0 fw-bold">Payment Instructions <i class="bi bi-chevron-down ms-2 small"></i></h6>
                            <i class="bi bi-wallet2 text-muted"></i>
                        </div>
                        <div class="collapse show" id="bankDetailsCollapse">
                            <div class="tm-card-body">
                                <p class="small text-muted mb-2">Details shown to tenants for deposits:</p>
                                <form method="POST">
                                    <div class="mb-3">
                                        <textarea class="form-control-modern" name="bank_details" rows="2" placeholder="Enter bank details..."><?php echo htmlspecialchars($dealer_data['bank_details'] ?? ''); ?></textarea>
                                    </div>
                                    <button type="submit" name="update_bank_details" class="btn btn-outline-modern btn-sm">Update Info</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Approvals -->
                    <?php if(count($pending_payments) > 0): ?>
                    <div class="tm-card mb-4 border-warning-left">
                        <div class="tm-card-header bg-warning-subtle">
                            <h6 class="mb-0 fw-bold text-warning-emphasis">Pending Approvals</h6>
                            <span class="badge bg-warning text-dark rounded-pill"><?php echo count($pending_payments); ?></span>
                        </div>
                        <div class="tm-card-body p-0">
                            <!-- Mobile View (List) -->
                            <div class="list-group list-group-flush d-lg-none">
                                <?php foreach($pending_payments as $pay): ?>
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($pay['tenant_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($pay['month_year']); ?></div>
                                            <div class="small text-muted" style="font-size: 0.75rem;"><i class="bi bi-calendar3 me-1"></i><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark"><?php echo number_format($pay['amount']); ?></div>
                                            <?php if($pay['proof_file']): ?>
                                            <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="small text-primary text-decoration-none"><i class="bi bi-file-earmark-text"></i> Proof</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2 mt-2">
                                        <form method="POST" class="flex-grow-1">
                                            <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success w-100"><i class="bi bi-check-lg"></i> Approve</button>
                                        </form>
                                        <form method="POST" class="flex-grow-1">
                                            <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger w-100"><i class="bi bi-x-lg"></i> Reject</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Desktop View (Table) -->
                            <div class="table-responsive d-none d-lg-block">
                                <table class="table table-hover mb-0 align-middle small">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-3 border-0">Tenant</th>
                                            <th class="border-0">Date</th>
                                            <th class="text-end border-0">Amt</th>
                                            <th class="text-end pe-3 border-0">Act</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($pending_payments as $pay): ?>
                                        <tr>
                                            <td class="ps-3 fw-medium text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($pay['tenant_name']); ?>">
                                                <?php echo htmlspecialchars($pay['tenant_name']); ?>
                                            </td>
                                            <td class="text-muted"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                            <td class="text-end fw-bold"><?php echo number_format($pay['amount']); ?></td>
                                            <td class="text-end pe-3">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <form method="POST">
                                                        <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success p-1 lh-1" title="Approve"><i class="bi bi-check-lg"></i></button>
                                                    </form>
                                                    <form method="POST">
                                                        <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-outline-danger p-1 lh-1" title="Reject"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                    <?php if($pay['proof_file']): ?>
                                                    <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border p-1 lh-1" title="Proof"><i class="bi bi-file-text"></i></a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Recent History -->
                    <div class="tm-card mb-4">
                        <div class="tm-card-header">
                            <h6 class="mb-0 fw-bold">Recent Activity</h6>
                        </div>
                        <div class="tm-card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle small">
                                    <thead class="bg-light text-muted">
                                        <tr>
                                            <th class="ps-3 border-0">Tenant</th>
                                            <th class="border-0">Date</th>
                                            <th class="text-end pe-3 border-0">Amt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($history_payments) > 0): ?>
                                            <?php foreach($history_payments as $pay): ?>
                                                <tr>
                                                    <td class="ps-3 fw-medium text-truncate" style="max-width: 200px;">
                                                        <?php echo htmlspecialchars($pay['tenant_name']); ?>
                                                    </td>
                                                    <td class="text-muted"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                                    <td class="text-end pe-3 fw-medium"><?php echo number_format($pay['amount']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center py-3 text-muted">No recent history</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Active Tenants List -->
                    <div class="tm-card">
                        <div class="tm-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <h6 class="mb-0 fw-bold">Active Tenants List</h6>
                            <div class="search-box">
                                <i class="bi bi-search text-muted"></i>
                                <input type="text" class="form-control form-control-sm border-0 bg-light" placeholder="Search tenants...">
                            </div>
                        </div>
                        
                        <div class="tm-card-body p-0">
                            <!-- Desktop Table / Mobile Cards Container -->
                            <div class="responsive-table-wrapper">
                                <table class="table align-middle mb-0 tm-table">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4">Tenant</th>
                                            <th>Property</th>
                                            <th>Rent</th>
                                            <th>Reference ID</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(count($tenants) > 0): ?>
                                            <?php foreach($tenants as $t): ?>
                                                <tr>
                                                    <td class="ps-4">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="avatar-circle-sm">
                                                                <?php echo strtoupper(substr($t['tenant_name'], 0, 1)); ?>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['tenant_name']); ?></div>
                                                                <div class="small text-muted"><?php echo htmlspecialchars($t['tenant_email']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center text-secondary">
                                                            <i class="bi bi-house-door me-2 text-muted"></i>
                                                            <span class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($t['property_title']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold text-dark"><?php echo number_format($t['rent_amount']); ?></div>
                                                        <div class="small text-muted">per month</div>
                                                    </td>
                                                    <td>
                                                        <?php if(!empty($t['payment_reference'])): ?>
                                                            <span class="badge bg-light text-dark border font-monospace"><?php echo chunk_split($t['payment_reference'], 4, ' '); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $status = $t['status'];
                                                            $badgeClass = 'bg-secondary-subtle text-secondary';
                                                            if($status == 'active') $badgeClass = 'bg-success-subtle text-success';
                                                            if($status == 'pending') $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                                                        ?>
                                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3"><?php echo ucfirst($status); ?></span>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <div class="dropdown">
                                                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots-vertical"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                                <li><h6 class="dropdown-header text-uppercase small fw-bold">Manage</h6></li>
                                                                <li>
                                                                    <button class="dropdown-item py-2" type="button" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $t['tenant_id']; ?>">
                                                                        <i class="bi bi-clock-history me-2 text-muted"></i> Payment History
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <button class="dropdown-item py-2" type="button" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $t['tenant_id']; ?>">
                                                                        <i class="bi bi-info-circle me-2 text-muted"></i> View Details
                                                                    </button>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li><a class="dropdown-item py-2 text-danger" href="#"><i class="bi bi-trash3 me-2"></i> End Tenancy</a></li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <div class="text-muted opacity-50 mb-2"><i class="bi bi-people fs-1"></i></div>
                                                    <p class="text-muted">No active tenants found.</p>
                                                    <button class="btn btn-sm btn-primary-modern" data-bs-toggle="modal" data-bs-target="#addTenantModal">Add Your First Tenant</button>
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

<!-- Closing dealer-main -->
</div>

<!-- Add Tenant Modal -->
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
                        <select class="form-select form-control-modern" name="property_id" required>
                            <option value="">Choose property...</option>
                            <?php foreach($properties as $prop): ?>
                                <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['title']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Tenant Email</label>
                        <input type="email" class="form-control-modern" name="email" placeholder="tenant@example.com" required>
                        <div class="form-text small">If the user exists, they will be linked. Otherwise, a new account will be created.</div>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold">Rent Amount</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 bg-light">K</span>
                                <input type="number" class="form-control border-start-0 ps-0 form-control-modern" name="rent_amount" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small fw-bold">Start Date</label>
                            <input type="date" class="form-control-modern" name="start_date" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">End Date (Optional)</label>
                        <input type="date" class="form-control-modern" name="end_date">
                    </div>
                    <div class="d-grid">
                        <button type="submit" name="add_tenant" class="btn btn-primary-modern py-2">Add Tenant</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modals for History & Details -->
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
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3 small text-muted">Date</th>
                                <th class="px-4 py-3 small text-muted">Month</th>
                                <th class="px-4 py-3 small text-muted">Amount</th>
                                <th class="px-4 py-3 small text-muted">Status</th>
                                <th class="px-4 py-3 small text-muted text-end">Proof</th>
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
                                        <span class="badge bg-success-subtle text-success">Approved</span>
                                    <?php elseif($hist['status'] == 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
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
                                    No payment history found.
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
                    <div class="avatar-initials mx-auto mb-3 fs-3" style="width: 64px; height: 64px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #475569;">
                        <?php echo strtoupper(substr($t['tenant_name'], 0, 1)); ?>
                    </div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($t['tenant_name']); ?></h5>
                    <p class="text-muted mb-0"><?php echo htmlspecialchars($t['tenant_email']); ?></p>
                </div>
                
                <div class="list-group list-group-flush border rounded-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="small fw-bold text-muted">PROPERTY</span>
                        <span class="fw-medium text-end"><?php echo htmlspecialchars($t['property_title']); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="small fw-bold text-muted">RENT</span>
                        <span class="fw-medium text-primary"><?php echo $t['currency'] . ' ' . number_format($t['rent_amount']); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <span class="small fw-bold text-muted">START DATE</span>
                        <span class="fw-medium"><?php echo date('M d, Y', strtotime($t['start_date'])); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3 bg-light">
                        <span class="small fw-bold text-muted">REF ID</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="font-monospace fw-bold"><?php echo chunk_split($t['payment_reference'] ?? 'N/A', 4, ' '); ?></span>
                            <button class="btn btn-link btn-sm p-0 text-muted" onclick="navigator.clipboard.writeText('<?php echo $t['payment_reference']; ?>')"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const customToggle = document.getElementById('tmSidebarToggle');
        const sidebar = document.querySelector('.dealer-sidebar');
        
        if (customToggle && sidebar) {
            customToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('active');
            });
        }
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar && sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== customToggle) {
                sidebar.classList.remove('active');
            }
        });
    });
</script>
</body>
</html>