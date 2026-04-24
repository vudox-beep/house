<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';
require_once '../includes/LencoAPI.php';
require_once '../includes/SimpleMailer.php';

// Include Header
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
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

function formatRentPaymentMethod(?string $method): string {
    $normalized = strtolower((string) $method);
    return match ($normalized) {
        'lenco' => 'Lenco',
        'mobile_money' => 'Mobile Money',
        'bank_transfer' => 'Bank Transfer',
        default => ucwords(str_replace('_', ' ', $normalized ?: 'bank_transfer'))
    };
}

function buildRentPaymentEmail(string $recipientName, string $heading, string $intro, array $details, string $ctaPath): string {
    $rows = '';
    foreach ($details as $label => $value) {
        $rows .= '<tr><th style="padding:10px;border-bottom:1px solid #eee;text-align:left;">' . htmlspecialchars($label) . '</th><td style="padding:10px;border-bottom:1px solid #eee;">' . htmlspecialchars($value) . '</td></tr>';
    }

    $ctaUrl = rtrim(SITE_URL, '/') . '/' . ltrim($ctaPath, '/');

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; background: #f7f7f7; color: #333; margin: 0; padding: 20px; }
            .card { max-width: 640px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
            .btn { display: inline-block; margin-top: 18px; padding: 12px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 6px; }
            table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        </style>
    </head>
    <body>
        <div class='card'>
            <h2 style='margin-top:0;'>" . htmlspecialchars($heading) . "</h2>
            <p>Hello " . htmlspecialchars($recipientName) . ",</p>
            <p>" . $intro . "</p>
            <table>" . $rows . "</table>
            <a href='" . htmlspecialchars($ctaUrl) . "' class='btn'>Open Dashboard</a>
        </div>
    </body>
    </html>";
}

function logTenantLencoTransaction(PDO $conn, array $payment): void {
    $tableExists = $conn->query("SHOW TABLES LIKE 'transactions'")->rowCount() > 0;
    if (!$tableExists) {
        return;
    }

    $existing = $conn->prepare("SELECT id FROM transactions WHERE reference = :reference LIMIT 1");
    $existing->execute([':reference' => $payment['reference']]);
    $existingId = $existing->fetchColumn();

    $message = "Tenant rent payment via Lenco for {$payment['month_year']} at {$payment['property_title']} (Dealer: {$payment['dealer_name']})";
    $params = [
        ':user_id' => $payment['tenant_id'],
        ':reference' => $payment['reference'],
        ':lenco_reference' => $payment['lenco_reference'],
        ':amount' => $payment['amount'],
        ':currency' => $payment['currency'],
        ':status' => 'successful',
        ':message' => $message,
        ':payment_method' => 'lenco'
    ];

    if ($existingId) {
        $sql = "UPDATE transactions
                SET user_id = :user_id, lenco_reference = :lenco_reference, amount = :amount, currency = :currency,
                    status = :status, message = :message, payment_method = :payment_method, updated_at = NOW()
                WHERE id = :id";
        $params[':id'] = $existingId;
    } else {
        $sql = "INSERT INTO transactions (user_id, reference, lenco_reference, amount, currency, status, message, payment_method)
                VALUES (:user_id, :reference, :lenco_reference, :amount, :currency, :status, :message, :payment_method)";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
}

function sendTenantLencoPaymentEmails(array $payment): void {
    $mailer = new SimpleMailer();

    $tenantBody = buildRentPaymentEmail(
        $payment['tenant_name'] ?: 'Tenant',
        'Lenco Rent Payment Confirmed',
        'Your rent payment was received successfully and recorded automatically.',
        [
            'Reference' => $payment['reference'],
            'Lenco Ref' => $payment['lenco_reference'] ?: '-',
            'Property' => $payment['property_title'],
            'For Month' => $payment['month_year'],
            'Amount' => $payment['currency'] . ' ' . number_format((float) $payment['amount'], 2),
            'Status' => 'Approved'
        ],
        'tenant/payments.php'
    );
    $mailer->send($payment['tenant_email'], 'Rent Payment Confirmation - ' . SITE_NAME, $tenantBody);

    if (!empty($payment['dealer_email'])) {
        $dealerBody = buildRentPaymentEmail(
            $payment['dealer_name'] ?: 'Dealer',
            'Tenant Lenco Payment Received',
            htmlspecialchars($payment['tenant_name']) . ' has paid rent successfully using Lenco.',
            [
                'Tenant' => $payment['tenant_name'],
                'Reference' => $payment['reference'],
                'Property' => $payment['property_title'],
                'For Month' => $payment['month_year'],
                'Amount' => $payment['currency'] . ' ' . number_format((float) $payment['amount'], 2),
                'Status' => 'Approved'
            ],
            'dealer/tenant_payments.php'
        );
        $mailer->send($payment['dealer_email'], 'Tenant Lenco Payment Alert - ' . SITE_NAME, $dealerBody);
    }
}

function fetchRentPaymentContext(PDO $conn, int $paymentId, int $tenantId): ?array {
    $sql = "SELECT rp.*, p.title AS property_title,
                   tenant.name AS tenant_name, tenant.email AS tenant_email,
                   dealer.name AS dealer_name, dealer.email AS dealer_email
            FROM rent_payments rp
            JOIN rentals r ON rp.rental_id = r.id
            JOIN properties p ON r.property_id = p.id
            JOIN users tenant ON rp.tenant_id = tenant.id
            JOIN users dealer ON r.dealer_id = dealer.id
            WHERE rp.id = :payment_id AND rp.tenant_id = :tenant_id
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':payment_id' => $paymentId,
        ':tenant_id' => $tenantId
    ]);

    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    return $payment ?: null;
}

ensureRentPaymentLencoSchema($conn);

$error = '';
$success = '';

// Check if tenant has an active rental
$sql_rental = "SELECT id, rent_amount, currency FROM rentals WHERE tenant_id = :tenant_id AND status = 'active' LIMIT 1";
$stmt_rental = $conn->prepare($sql_rental);
$stmt_rental->execute([':tenant_id' => $user_id]);
$active_rental = $stmt_rental->fetch(PDO::FETCH_ASSOC);

$stmtTenant = $conn->prepare("SELECT name, email, phone FROM users WHERE id = :tenant_id LIMIT 1");
$stmtTenant->execute([':tenant_id' => $user_id]);
$tenant_profile = $stmtTenant->fetch(PDO::FETCH_ASSOC) ?: ['name' => '', 'email' => '', 'phone' => ''];

// Verify a pending Lenco payment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_lenco_payment'])) {
    $payment_id = (int) ($_POST['payment_id'] ?? 0);
    $payment = fetchRentPaymentContext($conn, $payment_id, (int) $user_id);

    if (!$payment || strtolower((string) ($payment['payment_method'] ?? '')) !== 'lenco') {
        $error = "Invalid Lenco payment selected.";
    } elseif (empty($payment['reference'])) {
        $error = "This payment does not have a Lenco reference yet.";
    } else {
        $lenco = new LencoAPI();
        $verification = $lenco->verifyTransaction($payment['reference']);

        if (isset($verification['status']) && $verification['status'] === true) {
            $verifiedData = $verification['data'] ?? [];
            $paymentStatus = strtolower($verifiedData['status'] ?? 'pending');

            if ($paymentStatus === 'successful') {
                $lencoReference = $verifiedData['lencoReference'] ?? $verifiedData['id'] ?? null;
                $notes = trim((string) ($payment['dealer_notes'] ?? ''));
                $autoNote = 'Approved automatically after successful Lenco payment.';
                $mergedNotes = $notes === '' ? $autoNote : $notes . ' | ' . $autoNote;

                $stmtApprove = $conn->prepare("UPDATE rent_payments
                                               SET status = 'approved',
                                                   lenco_reference = :lenco_reference,
                                                   dealer_notes = :dealer_notes
                                               WHERE id = :payment_id");
                $stmtApprove->execute([
                    ':lenco_reference' => $lencoReference,
                    ':dealer_notes' => $mergedNotes,
                    ':payment_id' => $payment_id
                ]);

                $payment = fetchRentPaymentContext($conn, $payment_id, (int) $user_id);
                if ($payment) {
                    logTenantLencoTransaction($conn, $payment);
                    sendTenantLencoPaymentEmails($payment);
                }

                $success = "Lenco payment confirmed and recorded successfully.";
            } elseif (in_array($paymentStatus, ['failed', 'cancelled'], true)) {
                $error = "Lenco reports this payment as " . ucfirst($paymentStatus) . ".";
            } else {
                $success = "Lenco payment is still " . $paymentStatus . ". Please verify again after approving the phone prompt.";
            }
        } else {
            $error = $verification['message'] ?? 'Unable to verify the Lenco payment right now.';
        }
    }
}

// Handle Payment Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_payment'])) {
    if (!$active_rental) {
        $error = "No active rental found.";
    } else {
        $month = $_POST['month'];
        $year = $_POST['year'];
        $amount = $_POST['amount'];
        $payment_method = $_POST['payment_method']; // 'cash', 'bank_transfer', 'mobile_money', 'lenco'
        $months_paid = isset($_POST['months_paid']) ? (int)$_POST['months_paid'] : 1;
        $month_year = $month . ' ' . $year;
        
        // Handle File Upload (Optional if Cash)
        if ($payment_method == 'lenco') {
            $phone = trim($_POST['lenco_phone'] ?? ($tenant_profile['phone'] ?? ''));
            $operator = strtolower(trim($_POST['lenco_operator'] ?? 'mtn'));
            $country = strtolower(trim($_POST['lenco_country'] ?? 'zm'));

            if ($phone === '') {
                $error = "Phone number is required for Lenco payments.";
            } else {
                $lenco = new LencoAPI();
                $response = $lenco->initiateMobileMoney($amount, $active_rental['currency'], $phone, $operator, $country);

                if (isset($response['status']) && $response['status'] === true) {
                    $reference = $response['data']['reference'] ?? $response['data']['id'] ?? ('RENT-' . uniqid() . '-' . time());
                    $stmt_insert = $conn->prepare("INSERT INTO rent_payments
                        (rental_id, tenant_id, month_year, amount, currency, proof_file, payment_method, status, months_paid, reference)
                        VALUES (:rid, :tid, :my, :amt, :curr, NULL, 'lenco', 'pending', :months, :reference)");
                    $stmt_insert->execute([
                        ':rid' => $active_rental['id'],
                        ':tid' => $user_id,
                        ':my' => $month_year,
                        ':amt' => $amount,
                        ':curr' => $active_rental['currency'],
                        ':months' => $months_paid,
                        ':reference' => $reference
                    ]);

                    $success = "Lenco payment request sent. Approve the prompt on your phone, then click Verify in your payment history.";
                } else {
                    $error = $response['message'] ?? 'Failed to start the Lenco payment.';
                }
            }
        } elseif ($payment_method == 'cash') {
             // Insert into DB without file
            $sql_insert = "INSERT INTO rent_payments (rental_id, tenant_id, month_year, amount, currency, proof_file, payment_method, status, months_paid) 
                           VALUES (:rid, :tid, :my, :amt, :curr, NULL, :method, 'pending', :months)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->execute([
                ':rid' => $active_rental['id'],
                ':tid' => $user_id,
                ':my' => $month_year,
                ':amt' => $amount,
                ':curr' => $active_rental['currency'],
                ':method' => $payment_method,
                ':months' => $months_paid
            ]);
            $success = "Cash payment recorded! Waiting for landlord approval.";
        } elseif (isset($_FILES['proof']) && $_FILES['proof']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $_FILES['proof']['name'];
            $filetype = $_FILES['proof']['type'];
            $filesize = $_FILES['proof']['size'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                $error = "Invalid file type. Only JPG, PNG, and PDF allowed.";
            } elseif ($filesize > 5 * 1024 * 1024) {
                $error = "File too large. Max 5MB.";
            } else {
                // Upload
                $new_filename = uniqid() . '.' . $ext;
                $upload_dir = '../uploads/payments/';
                if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
                
                if (move_uploaded_file($_FILES['proof']['tmp_name'], $upload_dir . $new_filename)) {
                    // Insert into DB
                    $sql_insert = "INSERT INTO rent_payments (rental_id, tenant_id, month_year, amount, currency, proof_file, payment_method, status, months_paid) 
                                   VALUES (:rid, :tid, :my, :amt, :curr, :proof, :method, 'pending', :months)";
                    $stmt_insert = $conn->prepare($sql_insert);
                    $stmt_insert->execute([
                        ':rid' => $active_rental['id'],
                        ':tid' => $user_id,
                        ':my' => $month_year,
                        ':amt' => $amount,
                        ':curr' => $active_rental['currency'],
                        ':proof' => 'uploads/payments/' . $new_filename,
                        ':method' => $payment_method,
                        ':months' => $months_paid
                    ]);
                    
                    $success = "Payment proof uploaded successfully! Waiting for landlord approval.";
                } else {
                    $error = "Failed to upload file.";
                }
            }
        } else {
            // If method is cash, maybe allow no file? 
            // But usually tenants should upload a photo of the receipt.
            $error = "Please upload a photo of the receipt or proof of payment.";
        }
    }
}

// Fetch Payment History
$sql_history = "SELECT * FROM rent_payments WHERE tenant_id = :tenant_id ORDER BY created_at DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->execute([':tenant_id' => $user_id]);
$payments = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Rent Payments</h4>
            <p class="text-muted small mb-0">Track and upload your monthly rent payments.</p>
        </div>
        <?php if($active_rental): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload me-2"></i> Upload Payment
            </button>
        <?php endif; ?>
    </div>

    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Payments Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">For Month</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Proof</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                            <th>Date Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($payments) > 0): ?>
                            <?php foreach ($payments as $pay): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($pay['month_year']); ?></td>
                                    <td class="text-primary fw-bold">
                                        <?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?>
                                        <?php if(isset($pay['months_paid']) && $pay['months_paid'] > 1): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis ms-1 rounded-pill" style="font-size: 0.65rem;"><?php echo $pay['months_paid']; ?> Mos</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                            <?php echo ucwords(str_replace('_', ' ', $pay['payment_method'] ?? 'bank_transfer')); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            $badge = match($pay['status']) {
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'warning'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo $badge; ?>-subtle text-<?php echo $badge; ?> border border-<?php echo $badge; ?>-subtle rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                            <?php echo $pay['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($pay['proof_file']): ?>
                                            <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border">
                                                <i class="bi bi-file-earmark-text text-danger"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted fst-italic">
                                        <?php echo $pay['dealer_notes'] ? htmlspecialchars($pay['dealer_notes']) : '-'; ?>
                                    </td>
                                    <td>
                                        <?php if (($pay['payment_method'] ?? '') === 'lenco' && $pay['status'] === 'pending'): ?>
                                            <form method="POST" class="mb-0">
                                                <input type="hidden" name="payment_id" value="<?php echo (int) $pay['id']; ?>">
                                                <button type="submit" name="verify_lenco_payment" class="btn btn-sm btn-primary">
                                                    Verify
                                                </button>
                                            </form>
                                        <?php elseif (($pay['payment_method'] ?? '') === 'lenco' && !empty($pay['reference'])): ?>
                                            <span class="small text-muted"><?php echo htmlspecialchars($pay['reference']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-wallet2 fs-1 opacity-25"></i>
                                    </div>
                                    <h5 class="fw-bold text-muted">No Payment History</h5>
                                    <p class="text-muted small">Upload your first rent payment proof to get started.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Upload Rent Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info border-0 d-flex align-items-center mb-3">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <small>Rent Amount: <strong><?php echo $active_rental ? $active_rental['currency'] . ' ' . number_format($active_rental['rent_amount']) : 'N/A'; ?></strong></small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold">Month</label>
                            <select class="form-select" name="month" required>
                                <?php 
                                $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                                $currentMonth = date('F');
                                foreach($months as $m) {
                                    $selected = ($m === $currentMonth) ? 'selected' : '';
                                    echo "<option value='$m' $selected>$m</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold">Year</label>
                            <select class="form-select" name="year" required>
                                <?php 
                                $currentYear = date('Y');
                                // Allow paying for next year too
                                for($i = $currentYear; $i <= $currentYear + 1; $i++) {
                                    $selected = ($i == $currentYear) ? 'selected' : '';
                                    echo "<option value='$i' $selected>$i</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Number of Months Paid</label>
                        <select class="form-select" name="months_paid" id="monthsPaid" onchange="updateTotalAmount()">
                            <option value="1">1 Month</option>
                            <option value="2">2 Months</option>
                            <option value="3">3 Months</option>
                            <option value="4">4 Months</option>
                            <option value="5">5 Months</option>
                            <option value="6">6 Months</option>
                            <option value="12">1 Year</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Total Amount Paid</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="amountInput" value="<?php echo $active_rental ? $active_rental['rent_amount'] : ''; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Payment Method</label>
                        <select class="form-select" name="payment_method" id="paymentMethod" onchange="toggleProofUpload()" required>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="lenco">Lenco</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3" id="lencoFields" style="display:none;">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lenco Phone</label>
                            <input type="text" class="form-control" name="lenco_phone" id="lencoPhone" value="<?php echo htmlspecialchars($tenant_profile['phone'] ?? ''); ?>" placeholder="e.g. 097xxxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Network</label>
                            <select class="form-select" name="lenco_operator" id="lencoOperator">
                                <option value="mtn">MTN</option>
                                <option value="airtel">Airtel</option>
                                <option value="zamtel">Zamtel</option>
                            </select>
                        </div>
                        <input type="hidden" name="lenco_country" value="zm">
                        <div class="col-12">
                            <div class="form-text small">Lenco sends a mobile money prompt to this number. No proof upload is needed after a successful verification.</div>
                        </div>
                    </div>

                    <div class="mb-3" id="proofUploadField">
                        <label class="form-label small fw-bold">Proof of Payment (Screenshot/Receipt)</label>
                        <input type="file" class="form-control" name="proof" id="proofFile" accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text small">Max 5MB. Make sure details are visible.</div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="upload_payment" class="btn btn-primary px-4">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleProofUpload() {
    const method = document.getElementById('paymentMethod').value;
    const proofField = document.getElementById('proofUploadField');
    const proofInput = document.getElementById('proofFile');
    const lencoFields = document.getElementById('lencoFields');
    const lencoPhone = document.getElementById('lencoPhone');
    const lencoOperator = document.getElementById('lencoOperator');
    
    if (method === 'cash') {
        proofField.style.display = 'none';
        proofInput.removeAttribute('required');
        lencoFields.style.display = 'none';
        lencoPhone.removeAttribute('required');
        lencoOperator.removeAttribute('required');
    } else if (method === 'lenco') {
        proofField.style.display = 'none';
        proofInput.removeAttribute('required');
        lencoFields.style.display = 'flex';
        lencoPhone.setAttribute('required', 'required');
        lencoOperator.setAttribute('required', 'required');
    } else {
        proofField.style.display = 'block';
        proofInput.setAttribute('required', 'required');
        lencoFields.style.display = 'none';
        lencoPhone.removeAttribute('required');
        lencoOperator.removeAttribute('required');
    }
}

function updateTotalAmount() {
    const months = parseInt(document.getElementById('monthsPaid').value) || 1;
    const rentAmount = <?php echo $active_rental ? $active_rental['rent_amount'] : 0; ?>;
    const total = rentAmount * months;
    
    document.getElementById('amountInput').value = total.toFixed(2);
}

toggleProofUpload();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
