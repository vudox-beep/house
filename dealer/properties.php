<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Subscription
$userModel = new User();
$dealerProfile = $userModel->getDealerProfile($_SESSION['user_id']);

$canAddProperty = false;
if ($dealerProfile && $dealerProfile['subscription_status'] === 'active') {
    if (!empty($dealerProfile['subscription_expiry'])) {
        $expiryDate = new DateTime($dealerProfile['subscription_expiry']);
        $now = new DateTime();
        if ($expiryDate > $now) {
            $canAddProperty = true;
        }
    } else {
        $canAddProperty = true;
    }
}

$dealer_id = $_SESSION['user_id'];
$propertyModel = new Property();
$properties = $propertyModel->getByDealer($dealer_id);
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">My Properties</h4>
        <?php if ($canAddProperty): ?>
            <a href="add_property.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add New Property</a>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="Subscription Expired"><i class="bi bi-lock-fill"></i> Add New Property</button>
        <?php endif; ?>
    </div>

    <!-- Properties Table -->
    <?php if (!$canAddProperty): ?>
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
            <div>
                <strong>Subscription Expired</strong><br>
                You can view your properties, but you cannot edit or add new ones. <a href="subscribe.php" class="fw-bold text-dark">Renew Now</a>
            </div>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Property</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($properties) > 0): ?>
                            <?php foreach ($properties as $prop): ?>
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
                                    <td><?php echo ucfirst($prop['property_type']); ?></td>
                                    <td class="fw-bold text-primary"><?php echo $prop['currency'] . ' ' . number_format($prop['price']); ?></td>
                                    <td>
                                        <?php if($prop['status'] == 'available'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill"><?php echo ucfirst($prop['status']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($prop['created_at'])); ?></td>
                                    <td>
                                        <a href="edit_property.php?id=<?php echo $prop['id']; ?>" class="btn btn-sm btn-light border me-1" title="Edit"><i class="bi bi-pencil"></i></a>
                                        <a href="property_history.php?id=<?php echo $prop['id']; ?>" class="btn btn-sm btn-light border me-1" title="History"><i class="bi bi-clock-history"></i></a>
                                        <a href="../property_details.php?id=<?php echo $prop['id']; ?>" target="_blank" class="btn btn-sm btn-light border me-1" title="View"><i class="bi bi-eye"></i></a>
                                        <button class="btn btn-sm btn-light border text-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h5 class="fw-bold text-muted">No Properties Found</h5>
                                    <p class="text-muted">Start by adding your first property listing.</p>
                                    <a href="add_property.php" class="btn btn-primary mt-2">Add Property</a>
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