<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

// Fetch Active Rentals (Modified to fetch ALL active rentals, not just one)
$sql = "SELECT r.*, p.title, p.location, p.price, 
        (SELECT image_path FROM property_images WHERE property_id = p.id ORDER BY is_main DESC, id ASC LIMIT 1) as image,
        u.name as dealer_name, u.email as dealer_email, u.phone as dealer_phone, u.bank_details
        FROM rentals r
        JOIN properties p ON r.property_id = p.id
        JOIN users u ON r.dealer_id = u.id
        WHERE r.tenant_id = :tenant_id AND r.status = 'active'";

$stmt = $conn->prepare($sql);
$stmt->execute([':tenant_id' => $user_id]);
$rentals = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Next Due Date for each rental
foreach ($rentals as &$r) {
    // Get the last approved payment month
    // Order by created_at as we don't have period_end column yet
    $sql_last_paid = "SELECT month_year, months_paid, created_at FROM rent_payments 
                      WHERE rental_id = :rid AND status = 'approved' 
                      ORDER BY id DESC LIMIT 1";
    $stmt_last = $conn->prepare($sql_last_paid);
    $stmt_last->execute([':rid' => $r['id']]);
    $last_paid = $stmt_last->fetch(PDO::FETCH_ASSOC);

    if ($last_paid && !empty($last_paid['month_year'])) {
        // Parse the last paid month (e.g., "March 2026")
        $last_paid_date = DateTime::createFromFormat('!F Y', $last_paid['month_year']);
        
        if ($last_paid_date) {
            // Determine months paid (default to 1 if column missing or 0)
            $months_paid = isset($last_paid['months_paid']) && $last_paid['months_paid'] > 0 ? (int)$last_paid['months_paid'] : 1;
            
            // Calculate Billing Start Date (The start of the period being paid for)
            // If next due is May 15, then billing start is May 15.
            // If the user last paid for March (1 month), coverage ended April 15 (approx).
            // So next due is April 15.
            
            // The logic: 
            // Last Paid Month: March
            // Months Paid: 1
            // Covered Until: April
            // Next Due: April
            
            // Wait, if I pay for March 2026 (1 month), it covers March.
            // So next due is April.
            
            // If the user is seeing "March 15" again, it means the calculation didn't advance enough.
            // Let's check:
            // Last Paid: March 2026.
            // Months Paid: 1.
            // Next Due = March + 1 month = April.
            
            // IF the user paid for "February" last time, then next due is March.
            // The user said "if i paid billing start on 15".
            // If billing starts March 15, and they paid 1 month, they are covered until April 15.
            // So next due date is April 15.
            
            // Ensure we are adding months correctly to the *paid* month.
            // If DB says `month_year` = "March 2026", that is the month they paid FOR.
            // So next payment starts AFTER that month.
            
            $next_due = clone $last_paid_date;
            $next_due->modify('+' . $months_paid . ' month');
            
            // Adjust day to match start date
            $start_date = new DateTime($r['start_date']);
            $start_day = (int)$start_date->format('d');
            $days_in_month = (int)$next_due->format('t');
            $target_day = min($start_day, $days_in_month);
            $next_due->setDate((int)$next_due->format('Y'), (int)$next_due->format('m'), $target_day);
            
            // Billing Start Date: The date the last payment was created/approved
            // This reflects "When did I last pay?"
            if (!empty($last_paid['created_at'])) {
                $r['billing_start_date'] = date('M d, Y', strtotime($last_paid['created_at']));
            } else {
                $r['billing_start_date'] = 'N/A';
            }
            
            $r['next_due_date'] = $next_due->format('M d, Y');
            
            // Calculate status
            $today = new DateTime();
            $today->setTime(0, 0, 0);
            
            $next_due_compare = clone $next_due;
            $next_due_compare->setTime(0, 0, 0);
            
             if ($next_due_compare < $today) {
                $r['payment_status'] = 'Overdue';
                $r['status_color'] = 'danger';
            } elseif ($next_due_compare == $today) {
                $r['payment_status'] = 'Due Today';
                $r['status_color'] = 'warning';
            } else {
                if ($next_due_compare->format('Y-m') == $today->format('Y-m')) {
                    $r['payment_status'] = 'Due This Month';
                    $r['status_color'] = 'warning';
                } else {
                    $r['payment_status'] = 'Paid';
                    $r['status_color'] = 'success';
                }
            }
        } else {
             // Fallback if date parsing fails
             $r['next_due_date'] = date('M d, Y', strtotime('+1 month'));
             if (!empty($last_paid['created_at'])) {
                 $r['billing_start_date'] = date('M d, Y', strtotime($last_paid['created_at']));
             } else {
                 $r['billing_start_date'] = 'N/A';
             }
             $r['payment_status'] = 'Due Soon';
             $r['status_color'] = 'warning';
        }
    } else {
        // No payments yet, due date is start date
        $start_date = new DateTime($r['start_date']);
        $r['next_due_date'] = $start_date->format('M d, Y');
        
        // Billing start date is the rental start date
        $r['billing_start_date'] = $start_date->format('M d, Y');
        
        // Check if start date is in past (Day Precise)
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        $start_date_compare = clone $start_date;
        $start_date_compare->setTime(0, 0, 0);
        
        if ($start_date_compare < $today) {
             $r['payment_status'] = 'Overdue';
             $r['status_color'] = 'danger';
        } elseif ($start_date_compare == $today) {
             $r['payment_status'] = 'Due Today';
             $r['status_color'] = 'warning';
        } else {
             $r['payment_status'] = 'Due Soon';
             $r['status_color'] = 'warning';
        }
    }
}
unset($r); // Break reference

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">My Rentals</h4>
            <p class="text-muted small mb-0">Details of your current properties.</p>
        </div>
    </div>

    <?php if (count($rentals) > 0): ?>
        <?php foreach($rentals as $rental): ?>
        <div class="card border-0 shadow-sm rounded-3 mb-5 overflow-hidden">
            <div class="card-header bg-light border-bottom py-3">
                <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-house-door-fill me-2"></i><?php echo htmlspecialchars($rental['title']); ?></h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <!-- Property Card -->
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="card border-0 bg-white h-100">
                            <?php 
                            $imageUrl = !empty($rental['image']) && file_exists('../' . $rental['image']) 
                                ? '../' . $rental['image'] 
                                : 'https://via.placeholder.com/600x400?text=No+Image';
                            ?>
                            <img src="<?php echo htmlspecialchars($imageUrl); ?>" class="card-img-top rounded-3 mb-3" alt="Property Image" style="height: 250px; object-fit: cover;">
                            
                            <h5 class="fw-bold mb-2"><?php echo htmlspecialchars($rental['title']); ?></h5>
                            <p class="text-muted mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($rental['location']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 mb-3">
                                <span class="text-muted small">Monthly Rent</span>
                                <span class="fw-bold fs-5 text-primary"><?php echo $rental['currency'] . ' ' . number_format($rental['rent_amount']); ?></span>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="text-muted d-block">Paid Day</small>
                                    <span class="fw-medium"><?php echo $rental['billing_start_date']; ?></span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Next Due Date</small>
                                    <span class="fw-bold text-<?php echo $rental['status_color'] ?? 'primary'; ?>"><?php echo $rental['next_due_date']; ?></span>
                                </div>
                            </div>
                            <div class="row g-2 mt-3">
                                    <div class="col-6">
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Active</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Payment Status</small>
                                    <span class="badge bg-<?php echo $rental['status_color'] ?? 'secondary'; ?>-subtle text-<?php echo $rental['status_color'] ?? 'secondary'; ?> border border-<?php echo $rental['status_color'] ?? 'secondary'; ?>-subtle rounded-pill"><?php echo $rental['payment_status'] ?? 'Pending'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Landlord Info -->
                    <div class="col-md-6">
                        <div class="card border bg-light h-100">
                            <div class="card-header bg-white border-bottom py-3">
                                <h6 class="fw-bold mb-0">Landlord / Dealer Info</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-primary-subtle text-primary rounded-circle p-3 me-3">
                                        <i class="bi bi-person-badge-fill fs-3"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($rental['dealer_name']); ?></h6>
                                        <span class="text-muted small">Property Manager</span>
                                    </div>
                                </div>

                                <ul class="list-group list-group-flush bg-transparent">
                                    <li class="list-group-item px-0 border-0 bg-transparent d-flex align-items-center">
                                        <i class="bi bi-envelope me-3 text-muted"></i>
                                        <a href="mailto:<?php echo htmlspecialchars($rental['dealer_email']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rental['dealer_email']); ?></a>
                                    </li>
                                    <li class="list-group-item px-0 border-0 bg-transparent d-flex align-items-center">
                                        <i class="bi bi-telephone me-3 text-muted"></i>
                                        <a href="tel:<?php echo htmlspecialchars($rental['dealer_phone']); ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($rental['dealer_phone']); ?></a>
                                    </li>
                                    <li class="list-group-item px-0 border-0 bg-transparent d-flex align-items-center">
                                        <i class="bi bi-whatsapp me-3 text-success"></i>
                                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $rental['dealer_phone']); ?>" target="_blank" class="text-decoration-none text-dark">Chat on WhatsApp</a>
                                    </li>
                                </ul>
                                
                                <hr class="my-4">
                                
                                <?php if(!empty($rental['bank_details'])): ?>
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-2">Deposit Details</h6>
                                    <div class="bg-white p-3 rounded-3 small border">
                                        <i class="bi bi-wallet2 me-2 text-primary"></i>
                                        <?php echo nl2br(htmlspecialchars($rental['bank_details'])); ?>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="alert alert-warning border-0 d-flex align-items-start mb-0" role="alert">
                                    <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                    <div class="small">
                                        Need to report an issue? Contact your landlord directly using the details above.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/6028/6028690.png" width="120" class="mb-3 opacity-50" alt="No House">
            <h4 class="fw-bold text-muted">No Active Rental</h4>
            <p class="text-muted">You are not currently assigned to any property.</p>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>