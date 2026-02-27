<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';
require_once '../includes/SimpleMailer.php';

// Include Header
include 'includes/header.php';

$dealer_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

$error = '';
$success = '';

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
            $success = "Payment " . $status . " successfully.";
        } else {
            $error = "Failed to update payment status.";
        }
    } else {
        $error = "Invalid payment record.";
    }
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
        // 3. Create Rental Record
        $sql_rental = "INSERT INTO rentals (property_id, dealer_id, tenant_id, start_date, end_date, rent_amount, status) 
                       VALUES (:pid, :did, :tid, :start, :end, :amount, 'active')";
        $stmt_rental = $conn->prepare($sql_rental);
        if ($stmt_rental->execute([
            ':pid' => $property_id,
            ':did' => $dealer_id,
            ':tid' => $tenant_id,
            ':start' => $start_date,
            ':end' => $end_date,
            ':amount' => $rent_amount
        ])) {
            $success = "Tenant added successfully! They can now access their dashboard.";
        } else {
            $error = "Failed to add tenant to property.";
        }
    }
}

// Update Bank Details
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_bank_details'])) {
    $bank_details = trim($_POST['bank_details']);
    $sql_update_bank = "UPDATE users SET bank_details = :bank WHERE id = :id";
    $stmt_update_bank = $conn->prepare($sql_update_bank);
    if ($stmt_update_bank->execute([':bank' => $bank_details, ':id' => $dealer_id])) {
        $success = "Bank details updated successfully.";
    } else {
        $error = "Failed to update bank details.";
    }
}

// Fetch Dealer's Properties (for dropdown)
$sql_props = "SELECT id, title FROM properties WHERE dealer_id = :did AND status != 'rented'"; 
$stmt_props = $conn->prepare($sql_props);
$stmt_props->execute([':did' => $dealer_id]);
$properties = $stmt_props->fetchAll(PDO::FETCH_ASSOC);

// Fetch Existing Tenants
$sql_tenants = "SELECT r.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title 
                FROM rentals r 
                JOIN users u ON r.tenant_id = u.id 
                JOIN properties p ON r.property_id = p.id 
                WHERE r.dealer_id = :did 
                ORDER BY r.created_at DESC";
$stmt_tenants = $conn->prepare($sql_tenants);
$stmt_tenants->execute([':did' => $dealer_id]);
$tenants = $stmt_tenants->fetchAll(PDO::FETCH_ASSOC);

// Fetch Pending Payments
$sql_payments = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title 
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
$sql_history = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title 
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

?>

<div class="dealer-main">
    <div class="dealer-topbar">
        <div class="d-flex align-items-center">
             <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <h4 class="mb-0 fw-bold">Manage Tenants & Payments</h4>
        </div>
    </div>

    <div class="container-fluid p-4">
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Desktop Layout Optimization -->
        <div class="row g-4">
            <!-- Left Column: Add Tenant & Pending Payments -->
            <div class="col-lg-4 col-md-5">

                <!-- Payment Settings (Moved here) -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-primary">Deposit Instructions</h5>
                        <i class="bi bi-wallet2 fs-5 text-primary opacity-50"></i>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Bank Account / Mobile Money Info</label>
                                <textarea class="form-control bg-light" name="bank_details" rows="3" placeholder="e.g. Bank Name: ABC Bank&#10;Account No: 123456789&#10;Mobile Money: 097xxxxxxx (Name)"><?php echo htmlspecialchars($dealer_data['bank_details'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="update_bank_details" class="btn btn-outline-primary btn-sm">Save Payment Details</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Pending Payments Card -->
                <?php if(count($pending_payments) > 0): ?>
                <div class="card border-0 shadow-sm rounded-3 mb-4 border-warning border-start border-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-warning"><i class="bi bi-clock-history me-2"></i> Pending Payments</h5>
                        <span class="badge bg-warning text-dark rounded-pill"><?php echo count($pending_payments); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach($pending_payments as $pay): ?>
                            <div class="list-group-item p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($pay['tenant_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($pay['property_title']); ?></small>
                                    </div>
                                    <span class="fw-bold text-primary"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="small text-muted">
                                        For: <strong><?php echo htmlspecialchars($pay['month_year']); ?></strong>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <?php if($pay['proof_file']): ?>
                                            <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border" title="View Proof">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <form method="POST" class="d-inline-flex gap-1">
                                            <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" title="Reject">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Add Tenant Form -->
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Add New Tenant</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Select Property</label>
                                <select name="property_id" class="form-select" required>
                                    <option value="">-- Choose Property --</option>
                                    <?php foreach($properties as $prop): ?>
                                        <option value="<?php echo $prop['id']; ?>"><?php echo htmlspecialchars($prop['title']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Tenant Email</label>
                                <input type="email" name="email" class="form-control" placeholder="tenant@example.com" required>
                                <div class="form-text small">If they are not registered, an account will be created for them.</div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Rent Amount (ZMW)</label>
                                    <input type="number" name="rent_amount" class="form-control" placeholder="2500" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Lease End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>

                            <div class="d-grid">
                                <button type="submit" name="add_tenant" class="btn btn-primary">Add Tenant</button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Payment History -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Recent History</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if(count($history_payments) > 0): ?>
                                <?php foreach($history_payments as $pay): ?>
                                <div class="list-group-item p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <div class="fw-bold text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($pay['tenant_name']); ?></div>
                                            <small class="text-muted text-truncate d-block" style="max-width: 150px;"><?php echo htmlspecialchars($pay['property_title']); ?></small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></div>
                                            <?php 
                                                $badge = match($pay['status']) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>-subtle text-<?php echo $badge; ?> rounded-pill" style="font-size: 0.7rem;">
                                                <?php echo ucfirst($pay['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($pay['month_year']); ?>
                                        </div>
                                        <?php if($pay['proof_file']): ?>
                                            <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="text-decoration-none small">
                                                <i class="bi bi-file-earmark-text"></i> Proof
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-muted small">No history yet.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Tenants List -->
            <div class="col-lg-8 col-md-7">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">My Tenants</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-center">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Tenant Details</th>
                                        <th>Property</th>
                                        <th>Rent Amount</th>
                                        <th>Lease Info</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(count($tenants) > 0): ?>
                                        <?php foreach($tenants as $t): ?>
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="bg-primary-subtle text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <span class="fw-bold"><?php echo strtoupper(substr($t['tenant_name'], 0, 1)); ?></span>
                                                        </div>
                                                        <div class="text-start">
                                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($t['tenant_name']); ?></div>
                                                            <div class="small text-muted"><?php echo htmlspecialchars($t['tenant_email']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                        <i class="bi bi-house-door me-1 text-muted"></i>
                                                        <?php echo htmlspecialchars($t['property_title']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-dark"><?php echo $t['currency'] . ' ' . number_format($t['rent_amount']); ?></div>
                                                    <div class="small text-muted">Monthly</div>
                                                </td>
                                                <td>
                                                    <div class="small text-muted">Started: <?php echo date('M d, Y', strtotime($t['start_date'])); ?></div>
                                                    <?php if($t['end_date']): ?>
                                                        <div class="small text-muted">Ends: <?php echo date('M d, Y', strtotime($t['end_date'])); ?></div>
                                                        <?php 
                                                            $days_remaining = (strtotime($t['end_date']) - time()) / (60 * 60 * 24);
                                                            if ($days_remaining > 0 && $days_remaining < 30) {
                                                                echo '<div class="badge bg-warning text-dark mt-1">Expiring Soon</div>';
                                                            } elseif ($days_remaining < 0) {
                                                                echo '<div class="badge bg-danger mt-1">Expired</div>';
                                                            }
                                                        ?>
                                                    <?php else: ?>
                                                        <div class="small text-muted fst-italic">Ongoing</div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                        $statusClass = match($t['status']) {
                                                            'active' => 'success',
                                                            'ended' => 'secondary',
                                                            'pending' => 'warning',
                                                            default => 'primary'
                                                        };
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusClass; ?>-subtle text-<?php echo $statusClass; ?> border border-<?php echo $statusClass; ?>-subtle rounded-pill">
                                                        <?php echo ucfirst($t['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                            <li><a class="dropdown-item" href="#"><i class="bi bi-pencil me-2"></i> Edit Lease</a></li>
                                                            <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-x-circle me-2"></i> End Tenancy</a></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted mb-3">
                                                    <i class="bi bi-people fs-1 opacity-25"></i>
                                                </div>
                                                <h5 class="fw-bold text-muted">No Tenants Yet</h5>
                                                <p class="text-muted small">Add your first tenant using the form on the left.</p>
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
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>