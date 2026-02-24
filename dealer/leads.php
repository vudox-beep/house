<?php
require_once '../config/config.php';
require_once '../models/Property.php';

// Include Header
include 'includes/header.php';

// Mock Leads Data (Since we don't have a leads table yet)
// In a real app, this would be: $leads = $leadModel->getByDealer($_SESSION['user_id']);
$leads = []; 
// Example structure:
// $leads[] = [
//     'id' => 1,
//     'name' => 'John Doe',
//     'email' => 'john@example.com',
//     'phone' => '+260971234567',
//     'property_title' => 'Luxury Villa in Kabulonga',
//     'message' => 'I am interested in this property. Is it still available?',
//     'created_at' => '2023-10-27 10:30:00',
//     'status' => 'new'
// ];
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Leads & Inquiries</h4>
    </div>

    <!-- Leads Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Client</th>
                            <th>Property Interest</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($leads) > 0): ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($lead['name']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($lead['phone']); ?></div>
                                    </td>
                                    <td>
                                        <a href="#" class="text-decoration-none fw-medium"><?php echo htmlspecialchars($lead['property_title']); ?></a>
                                    </td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($lead['message']); ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                                    <td>
                                        <?php if($lead['status'] == 'new'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">New</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Read</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" title="Contact via WhatsApp"><i class="bi bi-whatsapp"></i></button>
                                        <button class="btn btn-sm btn-light border" title="Mark as Read"><i class="bi bi-check2"></i></button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3">
                                        <i class="bi bi-chat-square-text fs-1 opacity-50"></i>
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

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>