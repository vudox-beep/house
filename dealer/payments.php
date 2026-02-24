<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dealer') {
    header("Location: ../login.php");
    exit;
}

$dealer_id = $_SESSION['user_id'];

// Fetch All Transactions
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = :id ORDER BY created_at DESC");
    $stmt->execute([':id' => $dealer_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Payment History</h4>
        <a href="subscribe.php" class="btn btn-primary"><i class="bi bi-credit-card"></i> Make Payment</a>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-4">Reference</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $txn): ?>
                                <tr>
                                    <td class="ps-4 small fw-bold text-primary"><?php echo htmlspecialchars($txn['reference']); ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($txn['currency'] . ' ' . number_format($txn['amount'], 2)); ?></td>
                                    <td class="text-capitalize small"><?php echo htmlspecialchars($txn['payment_method'] ?? 'card'); ?></td>
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
                                    <td class="text-muted small"><?php echo date('M d, Y H:i', strtotime($txn['created_at'])); ?></td>
                                    <td class="small text-muted text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($txn['message']); ?>">
                                        <?php echo htmlspecialchars($txn['message']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-3 opacity-50"></i>
                                        <h5 class="fw-bold">No Payments Found</h5>
                                        <p class="text-muted">You haven't made any subscription payments yet.</p>
                                        <a href="subscribe.php" class="btn btn-primary mt-2">Subscribe Now</a>
                                    </div>
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