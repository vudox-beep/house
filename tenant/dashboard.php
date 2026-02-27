<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Fetch Tenant Data
$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

// Check for Active Rental
$sql = "SELECT r.*, p.title, p.location, pi.image_path as image
        FROM rentals r
        JOIN properties p ON r.property_id = p.id
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_main = 1
        WHERE r.tenant_id = :tenant_id AND r.status = 'active'
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute([':tenant_id' => $user_id]);
$active_rental = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch Last Payment
$sql_last_pay = "SELECT amount, created_at FROM rent_payments WHERE tenant_id = :tenant_id ORDER BY created_at DESC LIMIT 1";
$stmt_last_pay = $conn->prepare($sql_last_pay);
$stmt_last_pay->execute([':tenant_id' => $user_id]);
$last_payment = $stmt_last_pay->fetch(PDO::FETCH_ASSOC);

// Recent Payments
$sql_payments = "SELECT * FROM rent_payments WHERE tenant_id = :tenant_id ORDER BY created_at DESC LIMIT 5";
$stmt_payments = $conn->prepare($sql_payments);
$stmt_payments->execute([':tenant_id' => $user_id]);
$recent_payments = $stmt_payments->fetchAll(PDO::FETCH_ASSOC);

// Calculate Next Due Date (Simple: 1st of next month)
$next_due_date = date('M 01, Y', strtotime('+1 month'));
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h4>
            <p class="text-muted small mb-0">Manage your rental and payments.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon icon-brown">
                    <i class="bi bi-house-door-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Current Rental</h6>
                    <?php if($active_rental): ?>
                        <h5 class="text-truncate mb-0" style="max-width: 150px;"><?php echo htmlspecialchars($active_rental['title']); ?></h5>
                        <small class="text-muted"><?php echo htmlspecialchars($active_rental['location']); ?></small>
                    <?php else: ?>
                        <h3>None</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon icon-orange">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Next Due Date</h6>
                    <h3><?php echo $active_rental ? $next_due_date : '--'; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-icon icon-green">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div class="stats-info">
                    <h6>Last Payment</h6>
                    <?php if($last_payment): ?>
                        <h3><?php echo $active_rental['currency'] . ' ' . number_format($last_payment['amount']); ?></h3>
                        <small class="text-muted"><?php echo date('M d', strtotime($last_payment['created_at'])); ?></small>
                    <?php else: ?>
                        <h3>--</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(!$active_rental): ?>
    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-info-circle-fill fs-4 me-3 text-info"></i>
        <div>
            <h6 class="fw-bold mb-1">No Active Rental Found</h6>
            <p class="mb-0 small">You are not currently linked to any property. Ask your landlord/dealer to add you to their property using your email: <strong><?php echo $_SESSION['user_email'] ?? 'your email'; ?></strong></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Payments Placeholder -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Recent Payments</h5>
            <a href="payments.php" class="btn btn-sm btn-outline-primary">View History</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Month</th>
                            <th>Amount</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Date Uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_payments) > 0): ?>
                            <?php foreach ($recent_payments as $pay): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?php echo htmlspecialchars($pay['month_year']); ?></td>
                                    <td class="text-primary fw-bold"><?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?></td>
                                    <td>
                                        <?php if($pay['proof_file']): ?>
                                            <a href="../<?php echo $pay['proof_file']; ?>" target="_blank" class="btn btn-sm btn-light border">
                                                <i class="bi bi-file-earmark-text text-danger"></i>
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
                                        <span class="badge bg-<?php echo $badge; ?>-subtle text-<?php echo $badge; ?> border border-<?php echo $badge; ?>-subtle rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                            <?php echo $pay['status']; ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($pay['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-receipt fs-1 opacity-25"></i>
                                    </div>
                                    <h5 class="fw-bold text-muted">No Payment Records</h5>
                                    <p class="text-muted small">Your payment history will appear here once you start paying rent.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>