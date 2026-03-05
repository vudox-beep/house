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
        WHERE r.tenant_id = :tenant_id AND r.status = 'active'";

$stmt = $conn->prepare($sql);
$stmt->execute([':tenant_id' => $user_id]);
$active_rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Use the first rental for main stats if available
$active_rental = count($active_rentals) > 0 ? $active_rentals[0] : null;

// Calculate "Next Due Date" for Dashboard Summary (earliest due date among all properties)
$next_due_dates = [];
foreach ($active_rentals as $key => $r) {
    // Get last approved payment
    $sql_last_paid = "SELECT month_year, created_at, months_paid FROM rent_payments 
                      WHERE rental_id = :rid AND status = 'approved' 
                      ORDER BY id DESC LIMIT 1";
    $stmt_last = $conn->prepare($sql_last_paid);
    $stmt_last->execute([':rid' => $r['id']]);
    $last_paid = $stmt_last->fetch(PDO::FETCH_ASSOC);

        $start_date = new DateTime($r['start_date']);
        $start_day = (int)$start_date->format('d');

        if ($last_paid) {
            $last_paid_date = DateTime::createFromFormat('!F Y', $last_paid['month_year']);
            if ($last_paid_date) {
                // Determine months paid (default to 1 if column missing or 0)
                $months_paid = isset($last_paid['months_paid']) && $last_paid['months_paid'] > 0 ? (int)$last_paid['months_paid'] : 1;
                
                // Next due is +X months from the last paid month
                $next_due = clone $last_paid_date;
                $next_due->modify('+' . $months_paid . ' month');
                
                // Adjust day to match start date
                $days_in_month = (int)$next_due->format('t');
                $target_day = min($start_day, $days_in_month);
                $next_due->setDate((int)$next_due->format('Y'), (int)$next_due->format('m'), $target_day);
                
                $active_rentals[$key]['next_due_date'] = $next_due->format('M d, Y');
                $next_due_dates[] = $next_due->getTimestamp();
            } else {
                 $active_rentals[$key]['next_due_date'] = date('M d, Y', strtotime('+1 month'));
                 $next_due_dates[] = strtotime('+1 month');
            }
        } else {
            // No payments yet, due date is start date
            $active_rentals[$key]['next_due_date'] = $start_date->format('M d, Y');
            $next_due_dates[] = $start_date->getTimestamp();
        }
}

// Determine the earliest "Next Due Date" for the dashboard summary
$dashboard_due_date = '--';
$dashboard_status_color = 'muted';

if (!empty($next_due_dates)) {
    sort($next_due_dates);
    $earliest_timestamp = $next_due_dates[0];
    $dashboard_due_date = date('M d, Y', $earliest_timestamp);
    
    if ($earliest_timestamp < time()) {
        $dashboard_status_color = 'danger';
    } else {
        $dashboard_status_color = 'success';
    }
}

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
// $next_due_date = date('M 01, Y', strtotime('+1 month')); // Replaced by dynamic calculation above
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
                    <?php if(count($active_rentals) > 0): ?>
                        <div class="d-flex flex-column gap-2">
                        <?php foreach($active_rentals as $rental): ?>
                            <div>
                                <h5 class="text-truncate mb-0" style="max-width: 150px;"><?php echo htmlspecialchars($rental['title']); ?></h5>
                                <small class="text-muted"><?php echo htmlspecialchars($rental['location']); ?></small>
                            </div>
                        <?php endforeach; ?>
                        </div>
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
                    <?php if(count($active_rentals) > 0): ?>
                        <h3 class="text-<?php echo $dashboard_status_color; ?>"><?php echo $dashboard_due_date; ?></h3>
                        <?php if(count($active_rentals) > 1): ?>
                        <small class="text-muted">Earliest due date across properties</small>
                        <?php endif; ?>
                    <?php else: ?>
                        <h3>--</h3>
                    <?php endif; ?>
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
                        <h3><?php echo ($active_rentals[0]['currency'] ?? 'ZMW') . ' ' . number_format($last_payment['amount']); ?></h3>
                        <small class="text-muted"><?php echo date('M d', strtotime($last_payment['created_at'])); ?></small>
                    <?php else: ?>
                        <h3>--</h3>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if(count($active_rentals) > 0): ?>
    <!-- Reference ID Banner -->
    <?php foreach($active_rentals as $rental): ?>
    <div class="alert alert-light border shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center">
            <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                <i class="bi bi-bank2 fs-4"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Payment Reference ID (<?php echo htmlspecialchars($rental['title']); ?>)</h6>
                <p class="text-muted small mb-0">Use this 16-digit ID for all bank deposit narrations/references.</p>
            </div>
        </div>
        <div class="d-flex align-items-center bg-white border rounded-3 px-3 py-2">
            <span class="fs-5 fw-bold font-monospace text-primary me-3 tracking-wide">
                <?php echo chunk_split($rental['payment_reference'] ?? 'PENDING', 4, ' '); ?>
            </span>
            <button class="btn btn-link btn-sm p-0 text-muted" onclick="navigator.clipboard.writeText('<?php echo $rental['payment_reference'] ?? ''; ?>')" title="Copy ID">
                <i class="bi bi-copy fs-5"></i>
            </button>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
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
                                    <td class="text-primary fw-bold">
                                        <?php echo $pay['currency'] . ' ' . number_format($pay['amount']); ?>
                                        <?php if(isset($pay['months_paid']) && $pay['months_paid'] > 1): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis ms-1 rounded-pill" style="font-size: 0.65rem;"><?php echo $pay['months_paid']; ?> Mos</span>
                                        <?php endif; ?>
                                    </td>
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