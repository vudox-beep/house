<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header (which includes sidebar and session check)
include 'includes/header.php';

// Fetch Dealer Stats
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $dealer_id = $_SESSION['user_id'];

    // Total Properties
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE dealer_id = :id");
    $stmt->execute([':id' => $dealer_id]);
    $total_properties = $stmt->fetchColumn();

    // Active Listings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE dealer_id = :id AND status = 'available'");
    $stmt->execute([':id' => $dealer_id]);
    $active_listings = $stmt->fetchColumn();

    // Total Views (Real)
    $stmt = $pdo->prepare("SELECT SUM(views) FROM properties WHERE dealer_id = :id");
    $stmt->execute([':id' => $dealer_id]);
    $total_views = $stmt->fetchColumn() ?: 0; 

    // Total Tenants
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rentals WHERE dealer_id = :id AND status = 'active'");
    $stmt->execute([':id' => $dealer_id]);
    $total_tenants = $stmt->fetchColumn();

    // Subscription Status
    $stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = :id");
    $stmt->execute([':id' => $dealer_id]);
    $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

    $sub_status = $subscription['subscription_status'] ?? 'inactive';
    $sub_expiry = $subscription['subscription_expiry'] ?? 'N/A';

    // Determine Plan Type (Basic vs Paid)
    $plan_type = 'Inactive';
    if ($sub_status === 'active') {
        // Check for successful payment
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = :id AND status = 'successful'");
        $stmt->execute([':id' => $dealer_id]);
        $has_paid = $stmt->fetchColumn() > 0;
        
        $plan_type = $has_paid ? 'Paid (Premium)' : 'Basic (Free Trial)';
        
        // Check if expired but status not updated
        if ($sub_expiry && strtotime($sub_expiry) < time()) {
             $sub_status = 'expired';
             $plan_type = 'Expired';
        }
    }

    // Recent Transactions
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :id ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([':id' => $dealer_id]);
    $recent_transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

    <!-- Subscription Banner -->
    <div class="subscription-banner shadow-sm">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Welcome back, <?php echo $_SESSION['user_name']; ?>!</h2>
                <p class="mb-0 opacity-75">
                    Plan: <strong><?php echo $plan_type; ?></strong> 
                    <?php if($sub_status === 'active'): ?>
                        <span class="badge bg-white text-primary ms-2"><?php echo ucfirst($sub_status); ?></span>
                    <?php else: ?>
                        <span class="badge bg-danger ms-2"><?php echo ucfirst($sub_status); ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <?php if($sub_status !== 'active' || $plan_type === 'Expired'): ?>
                <a href="subscribe.php" class="btn btn-light fw-bold text-primary">Upgrade / Renew</a>
            <?php else: ?>
                <div class="text-end">
                    <small class="d-block opacity-75">Valid until</small>
                    <span class="fw-bold fs-5"><?php echo ($sub_expiry && $sub_expiry !== 'N/A') ? date('M d, Y', strtotime($sub_expiry)) : 'Lifetime'; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon icon-brown">
                    <i class="bi bi-houses-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Total Properties</h6>
                    <h3><?php echo $total_properties; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon icon-orange">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Active Listings</h6>
                    <h3><?php echo $active_listings; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon icon-green">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Active Tenants</h6>
                    <h3><?php echo $total_tenants; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon icon-green">
                    <i class="bi bi-eye-fill"></i>
                </div>
                <div class="stats-info">
                    <h6>Total Views</h6>
                    <h3><?php echo number_format($total_views); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <?php if (count($recent_transactions) > 0): ?>
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
                            <th class="ps-4">Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_transactions as $txn): ?>
                            <tr>
                                <td class="ps-4 small fw-bold"><?php echo htmlspecialchars($txn['reference']); ?></td>
                                <td class="fw-bold"><?php echo htmlspecialchars($txn['currency'] . ' ' . number_format($txn['amount'], 2)); ?></td>
                                <td>
                                    <?php 
                                        $status = strtolower($txn['status']);
                                        $badge = match($status) {
                                            'successful', 'completed' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <span class="badge bg-<?php echo $badge; ?>-subtle text-<?php echo $badge; ?> border border-<?php echo $badge; ?>-subtle rounded-pill px-2">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Properties -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">My Recent Properties</h5>
            <a href="add_property.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Add New</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Property</th>
                            <th>Price</th>
                            <th>Views</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $propertyModel = new Property();
                        $recent_properties = $propertyModel->getByDealer($dealer_id);
                        // Limit to 5 for dashboard
                        $recent_properties = array_slice($recent_properties, 0, 5);
                        ?>
                        
                        <?php if (count($recent_properties) > 0): ?>
                            <?php foreach ($recent_properties as $prop): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-2" style="width: 50px; height: 50px;">
                                                <i class="bi bi-house text-muted fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($prop['title']); ?></div>
                                                <div class="small text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($prop['location']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-primary"><?php echo $prop['currency'] . ' ' . number_format($prop['price']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><i class="bi bi-eye-fill text-muted"></i> <?php echo number_format($prop['views'] ?? 0); ?></span></td>
                                    <td>
                                        <?php if($prop['status'] == 'available'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill"><?php echo ucfirst($prop['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($prop['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_property.php?id=<?php echo $prop['id']; ?>" class="btn btn-sm btn-light border me-1"><i class="bi bi-pencil"></i></a>
                                        <a href="../property_details.php?id=<?php echo $prop['id']; ?>" target="_blank" class="btn btn-sm btn-light border me-1"><i class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light border text-danger" onclick="deleteProperty(<?php echo $prop['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <h5 class="fw-bold text-muted">No Properties Listed Yet</h5>
                                    <a href="add_property.php" class="btn btn-primary mt-2">List Your First Property</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" action="delete_property.php" method="POST" style="display: none;">
    <input type="hidden" name="property_id" id="deletePropertyId">
</form>

<script>
function deleteProperty(id) {
    if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
        document.getElementById('deletePropertyId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>