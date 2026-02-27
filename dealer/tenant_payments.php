<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$dealer_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

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
                                    <td colspan="8" class="text-center py-5">
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