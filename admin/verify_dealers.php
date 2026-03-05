<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Check Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$message = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action === 'verify') {
        // Set both is_verified (legacy) and identity_verified (new)
        $stmt = $pdo->prepare("UPDATE users SET is_verified = 1, identity_verified = 1 WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "Dealer verified successfully!";
    } elseif ($action === 'reject') {
        // Just unverify or delete doc? Let's just reset doc for now
        $stmt = $pdo->prepare("UPDATE users SET verification_doc = NULL WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "Dealer rejected. Document removed.";
    }
}

// Fetch Pending Dealers (Unverified AND has document)
// Check identity_verified = 0
$stmt = $pdo->query("SELECT * FROM users WHERE role = 'dealer' AND identity_verified = 0 AND verification_doc IS NOT NULL");
$pendingDealers = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Dealer Verification Requests</h2>
        <a href="index.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 fw-bold text-primary">Pending Verifications</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Document</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($pendingDealers) > 0): ?>
                            <?php foreach ($pendingDealers as $dealer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dealer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['phone']); ?></td>
                                    <td class="text-center">
                                        <?php if ($dealer['verification_doc']): ?>
                                            <a href="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" target="_blank" class="btn btn-sm btn-info">
                                                <i class="bi bi-file-earmark-image"></i> View ID
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="user_id" value="<?php echo $dealer['id']; ?>">
                                            <button type="submit" name="action" value="verify" class="btn btn-success btn-sm" onclick="return confirm('Approve this dealer?');">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject this dealer?');">
                                                <i class="bi bi-x-lg"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No pending verification requests.</td>
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
