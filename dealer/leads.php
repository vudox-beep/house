<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/Lead.php';

// Include Header
include 'includes/header.php';

// Helper for ID encoding (should be moved to utils or included)
if (!function_exists('encode_id')) {
    function encode_id($id) {
        return rtrim(strtr(base64_encode($id), '+/', '-_'), '=');
    }
}

$dealer_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->connect();

// Fetch Dealer Data (for subscription)
$stmt_user = $conn->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = :id");
$stmt_user->execute([':id' => $dealer_id]);
$dealer_data = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Check Subscription
$is_subscribed = false;
if ($dealer_data && $dealer_data['subscription_status'] === 'active' && strtotime($dealer_data['subscription_expiry']) > time()) {
    $is_subscribed = true;
}

$leadModel = new Lead();
$leads = $is_subscribed ? $leadModel->getByDealer($dealer_id) : [];
?>

<div class="container-fluid py-4">
    <?php if(!$is_subscribed): ?>
        <div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
            <div class="text-center">
                <div class="mb-4 text-warning">
                    <i class="bi bi-lock-fill" style="font-size: 4rem;"></i>
                </div>
                <h2 class="fw-bold mb-3">Access Restricted</h2>
                <p class="text-muted mb-4 fs-5" style="max-width: 500px; margin: 0 auto;">
                    Your dealer subscription is currently <strong>inactive</strong>. <br>
                    Please renew your subscription to view your leads and inquiries.
                </p>
                <a href="subscribe.php" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow-sm">
                    <i class="bi bi-credit-card-2-front me-2"></i> Renew Subscription
                </a>
            </div>
        </div>
    <?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Leads & Inquiries</h4>
            <p class="text-muted small mb-0">Manage messages from interested clients.</p>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">Client Details</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Property Interest</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Message</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Date</th>
                            <th class="py-3 text-muted small text-uppercase fw-bold">Status</th>
                            <th class="pe-4 py-3 text-end text-muted small text-uppercase fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($leads) > 0): ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                                <?php echo strtoupper(substr($lead['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['name']); ?></div>
                                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($lead['phone']); ?></div>
                                                <div class="small text-muted"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($lead['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($lead['property_title'])): ?>
                                            <a href="../property_details.php?id=<?php echo encode_id($lead['property_id']); ?>" target="_blank" class="text-decoration-none fw-semibold text-primary">
                                                <i class="bi bi-box-arrow-up-right small me-1"></i> <?php echo htmlspecialchars($lead['property_title']); ?>
                                            </a>
                                            <div class="small text-muted">Ref: #<?php echo str_pad($lead['property_id'], 6, '0', STR_PAD_LEFT); ?></div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">Property Deleted</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-wrap text-muted" style="max-width: 300px; font-size: 0.9rem;">
                                            <?php echo nl2br(htmlspecialchars($lead['message'])); ?>
                                        </div>
                                    </td>
                                    <td class="text-muted small">
                                        <div><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></div>
                                        <div class="text-xs text-secondary"><?php echo date('h:i A', strtotime($lead['created_at'])); ?></div>
                                    </td>
                                    <td>
                                        <?php if(isset($lead['status']) && $lead['status'] == 'read'): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="btn-group">
                                            <?php if(!empty($lead['phone'])): ?>
                                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>" target="_blank" class="btn btn-sm btn-outline-success" title="WhatsApp">
                                                    <i class="bi bi-whatsapp"></i>
                                                </a>
                                                <a href="tel:<?php echo $lead['phone']; ?>" class="btn btn-sm btn-outline-secondary" title="Call">
                                                    <i class="bi bi-telephone"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="mailto:<?php echo $lead['email']; ?>" class="btn btn-sm btn-outline-primary" title="Email">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-inbox fs-1 opacity-25"></i>
                                    </div>
                                    <h5 class="fw-bold text-muted">No Inquiries Yet</h5>
                                    <p class="text-muted small">Messages from interested buyers/tenants will appear here.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>