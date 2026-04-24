<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Verification Status First
$userModel = new User();
$userProfile = $userModel->getUserById($_SESSION['user_id']);

if ($userProfile['identity_verified'] != 1) {
    // Check if doc uploaded
    $upload_success = '';
    $upload_error = '';
    $is_pending = false;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dealer_verification_doc'])) {
        $target_dir = "../assets/images/dealer_docs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["dealer_verification_doc"]["name"], PATHINFO_EXTENSION));
        $new_filename = 'dealer_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($file_extension, $allowed)) {
            $upload_error = "Only JPG, PNG & PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["dealer_verification_doc"]["tmp_name"], $target_file)) {
                require_once '../includes/SimpleMailer.php';
                $mailer = new SimpleMailer();
                $subject = "Dealer Verification Request - " . $_SESSION['user_name'];
                $body = "User " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ") has uploaded a verification document.<br>File: " . SITE_URL . "/assets/images/dealer_docs/" . $new_filename;
                $mailer->send(SMTP_FROM, $subject, $body);
                
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $pdo->prepare("UPDATE users SET verification_doc = :doc, identity_verified = 0 WHERE id = :id");
                    $stmt->execute([':doc' => "assets/images/dealer_docs/" . $new_filename, ':id' => $_SESSION['user_id']]);
                    $userProfile['identity_verified'] = 0; 
                } catch (Exception $e) {}
                
                $upload_success = "Document uploaded successfully.";
                $is_pending = true;
            } else {
                $upload_error = "Failed to upload file.";
            }
        }
    }
    
    $is_rejected = ($userProfile['identity_verified'] == 2);
    if ($upload_success) {
        $is_rejected = false;
        $is_pending = true;
    } elseif ($userProfile['identity_verified'] == 0 && !empty($userProfile['verification_doc'])) {
        $is_pending = true;
    }

    echo "
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card border-danger shadow-lg'>
                    <div class='card-header bg-danger text-white py-3'>
                        <h4 class='mb-0 fw-bold'><i class='bi bi-shield-lock-fill me-2'></i>Account Verification Required</h4>
                    </div>
                    <div class='card-body p-5 text-center'>
                        <div class='mb-4'>
                            <i class='bi bi-person-badge display-1 text-danger'></i>
                        </div>
                        <h3 class='fw-bold mb-3'>Verification Required</h3>
                        <p class='lead mb-4'>You cannot manage tenant payments until your identity is verified.</p>
                        
                        " . ($is_rejected ? "
                            <div class='alert alert-danger border-danger text-start p-4 mb-4'>
                                <h4 class='alert-heading fw-bold'><i class='bi bi-x-circle-fill'></i> Verification Rejected</h4>
                                <p class='mb-0 lead'>Your previous verification attempt was rejected. Please upload a new photo.</p>
                            </div>
                        " : "") . "

                        " . ($is_pending ? "
                             <div class='alert alert-info border-info text-start p-4'>
                                <h4 class='alert-heading fw-bold'><i class='bi bi-clock-history'></i> Verification Pending</h4>
                                <p class='mb-0 lead'>Your verification photo is under review. Please wait for approval.</p>
                            </div>
                        " : "
                            " . (!$is_rejected ? "
                            <div class='alert alert-warning border-warning text-start'>
                                <h5 class='alert-heading fw-bold'><i class='bi bi-exclamation-triangle-fill'></i> Action Required:</h5>
                                <p class='mb-0'>Please upload a photo of <strong>yourself standing next to your property</strong> to proceed.</p>
                            </div>" : "") . "
                            
                            " . ($upload_error ? "<div class='alert alert-danger'>$upload_error</div>" : "") . "
                            
                            <form method='POST' enctype='multipart/form-data' class='mt-4 p-4 border rounded bg-light'>
                                <div class='mb-3 text-start'>
                                    <label class='form-label fw-bold'>Upload Verification Photo</label>
                                    <input type='file' class='form-control' name='dealer_verification_doc' required accept='.jpg,.jpeg,.png,.pdf'>
                                    <div class='form-text'>Photo of you + property. Formats: JPG, PNG</div>
                                </div>
                                <button type='submit' class='btn btn-danger w-100 fw-bold'>Submit Verification Photo</button>
                            </form>
                        ") . "
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}

$dealer_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

function ensureRentPaymentLencoSchema(PDO $conn): void {
    static $checked = false;
    if ($checked) {
        return;
    }

    $paymentMethodColumn = $conn->query("SHOW COLUMNS FROM rent_payments LIKE 'payment_method'")->fetch(PDO::FETCH_ASSOC);
    if ($paymentMethodColumn && strpos($paymentMethodColumn['Type'], "'lenco'") === false) {
        $conn->exec("ALTER TABLE rent_payments MODIFY COLUMN payment_method ENUM('cash','bank_transfer','mobile_money','lenco') DEFAULT 'bank_transfer'");
    }

    $columnsToEnsure = [
        'reference' => "ALTER TABLE rent_payments ADD COLUMN reference VARCHAR(255) DEFAULT NULL AFTER months_paid",
        'lenco_reference' => "ALTER TABLE rent_payments ADD COLUMN lenco_reference VARCHAR(255) DEFAULT NULL AFTER reference"
    ];

    foreach ($columnsToEnsure as $column => $sql) {
        $exists = $conn->query("SHOW COLUMNS FROM rent_payments LIKE " . $conn->quote($column))->fetch(PDO::FETCH_ASSOC);
        if (!$exists) {
            $conn->exec($sql);
        }
    }

    $checked = true;
}

ensureRentPaymentLencoSchema($conn);

$error = '';
$success = '';

// Handle Actions (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['payment_id'])) {
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
}

// Fetch Payments
$filter = $_GET['filter'] ?? 'all';
$sql_payments = "SELECT rp.*, u.name as tenant_name, u.email as tenant_email, p.title as property_title 
                 FROM rent_payments rp
                 JOIN rentals r ON rp.rental_id = r.id
                 JOIN users u ON rp.tenant_id = u.id
                 JOIN properties p ON r.property_id = p.id
                 WHERE r.dealer_id = :did";

if ($filter == 'pending') {
    $sql_payments .= " AND rp.status = 'pending'";
}

$sql_payments .= " ORDER BY rp.created_at DESC";

$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->execute([':did' => $dealer_id]);
$payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="dealer-main">
    <div class="dealer-topbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <h4 class="mb-0 fw-bold">Tenant Payments</h4>
        </div>
    </div>

    <div class="container-fluid p-4">
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Payment Records</h5>
                <div>
                    <a href="?filter=all" class="btn btn-sm <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline-secondary'; ?> me-2">All</a>
                    <a href="?filter=pending" class="btn btn-sm <?php echo $filter == 'pending' ? 'btn-warning' : 'btn-outline-secondary'; ?>">Pending Approval</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4 text-start">Tenant</th>
                                <th>Property</th>
                                <th>Month</th>
                                <th>Method</th>
                                <th>Amount</th>
                                <th>Proof</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($payments) > 0): ?>
                                <?php foreach($payments as $pay): ?>
                                    <tr>
                                        <td class="ps-4 text-start">
                                            <div class="fw-bold"><?php echo htmlspecialchars($pay['tenant_name']); ?></div>
                                            <div class="small text-muted"><?php echo htmlspecialchars($pay['tenant_email']); ?></div>
                                        </td>
                                        <td>
                                            <span class="d-inline-block text-truncate" style="max-width: 150px;">
                                                <?php echo htmlspecialchars($pay['property_title']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($pay['month_year']); ?></td>
                                        <td>
                                            <div class="small fw-semibold"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $pay['payment_method'] ?? 'bank_transfer'))); ?></div>
                                            <?php if (!empty($pay['reference'])): ?>
                                                <div class="small text-muted"><?php echo htmlspecialchars($pay['reference']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-primary fw-bold"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></td>
                                        <td>
                                            <?php if($pay['proof_file']): ?>
                                                <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border text-primary">
                                                    <i class="bi bi-file-earmark-text me-1"></i> View
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">No file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                $badge = match($pay['status']) {
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'warning'
                                                };
                                            ?>
                                            <span class="badge bg-<?php echo $badge; ?>-subtle text-<?php echo $badge; ?> border border-<?php echo $badge; ?>-subtle rounded-pill text-uppercase">
                                                <?php echo $pay['status']; ?>
                                            </span>
                                        </td>
                                        <td class="small text-muted"><?php echo date('M d', strtotime($pay['created_at'])); ?></td>
                                        <td>
                                            <?php if($pay['status'] == 'pending'): ?>
                                                <form method="POST" class="d-flex justify-content-center gap-2">
                                                    <input type="hidden" name="payment_id" value="<?php echo $pay['id']; ?>">
                                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                    <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger" title="Reject">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted mb-3">
                                            <i class="bi bi-cash-stack fs-1 opacity-25"></i>
                                        </div>
                                        <h5 class="fw-bold text-muted">No Payment Records</h5>
                                        <p class="text-muted small">Payments uploaded by your tenants will appear here.</p>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
