<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

$error = '';
$success = '';

// Check if tenant has an active rental
$sql_rental = "SELECT id, rent_amount, currency FROM rentals WHERE tenant_id = :tenant_id AND status = 'active' LIMIT 1";
$stmt_rental = $conn->prepare($sql_rental);
$stmt_rental->execute([':tenant_id' => $user_id]);
$active_rental = $stmt_rental->fetch(PDO::FETCH_ASSOC);

// Handle Payment Upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_payment'])) {
    if (!$active_rental) {
        $error = "No active rental found.";
    } else {
        $month = $_POST['month'];
        $year = $_POST['year'];
        $amount = $_POST['amount'];
        $payment_method = $_POST['payment_method']; // 'cash', 'bank_transfer', 'mobile_money'
        $months_paid = isset($_POST['months_paid']) ? (int)$_POST['months_paid'] : 1;
        $month_year = $month . ' ' . $year;
        
        // Handle File Upload (Optional if Cash)
        if ($payment_method == 'cash') {
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
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
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
                            <option value="cash">Cash</option>
                        </select>
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
    
    if (method === 'cash') {
        proofField.style.display = 'none';
        proofInput.removeAttribute('required');
    } else {
        proofField.style.display = 'block';
        proofInput.setAttribute('required', 'required');
    }
}

function updateTotalAmount() {
    const months = parseInt(document.getElementById('monthsPaid').value) || 1;
    const rentAmount = <?php echo $active_rental ? $active_rental['rent_amount'] : 0; ?>;
    const total = rentAmount * months;
    
    document.getElementById('amountInput').value = total.toFixed(2);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>