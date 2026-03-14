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
        
        // Get user email
        $stmtUser = $pdo->prepare("SELECT email, name FROM users WHERE id = ?");
        $stmtUser->execute([$userId]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            require_once '../includes/SimpleMailer.php';
            $mailer = new SimpleMailer();
            $subject = "Identity Verified - You can now post properties! - " . SITE_NAME;
            $body = "Hello " . htmlspecialchars($user['name']) . ",<br><br>
                     Congratulations! Your identity verification has been <b>approved</b>.<br>
                     You now have full access to add and manage properties on " . SITE_NAME . ".<br><br>
                     <a href='" . SITE_URL . "/dealer/add_property.php' style='background:#28a745;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Post a Property Now</a>";
            $mailer->send($user['email'], $subject, $body);
        }
        
        $message = "Dealer verified successfully and notified via email!";
    } elseif ($action === 'reject') {
        // Set identity_verified = 2 (Rejected) BUT keep the doc so we can see history
        $stmt = $pdo->prepare("UPDATE users SET identity_verified = 2 WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "Dealer rejected. Status updated to Rejected.";
    } elseif ($action === 'revoke') {
        // Revoke verification (Set identity_verified = 2 so they are blocked and see rejection message)
        $stmt = $pdo->prepare("UPDATE users SET identity_verified = 2 WHERE id = ?");
        $stmt->execute([$userId]);
        $message = "Verification revoked. Dealer is now blocked and marked as rejected.";
    }
}

// Fetch Pending Dealers (Unverified AND has document)
// We want to see ANYONE who uploaded a document but isn't approved yet.
// Even if they are verified by email, if they uploaded a doc, we want to see it?
// No, if identity_verified=0, they are pending.
$stmt = $pdo->query("SELECT * FROM users WHERE role IN ('dealer', 'Dealer') AND identity_verified = 0 AND verification_doc IS NOT NULL");
$pendingDealers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Verified Dealers History (New)
// We want ALL verified users regardless of role, just in case
// Or just check identity_verified = 1
$stmt = $pdo->query("SELECT * FROM users WHERE identity_verified = 1 ORDER BY id DESC LIMIT 50");
$verifiedDealers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Rejected Dealers (identity_verified = 2)
$stmt = $pdo->query("SELECT * FROM users WHERE identity_verified = 2 ORDER BY id DESC LIMIT 50");
$rejectedDealers = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                            <th>Verification Photo</th>
                            <th>Phases</th>
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
                                            <a href="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" target="_blank" title="Click to View Full Size">
                                                <img src="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 80px; max-width: 120px; object-fit: cover;" 
                                                     alt="Verification Photo">
                                            </a>
                                            <div class="small text-muted mt-1">Person + Property</div>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Phase 1: Email</span>
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Phase 2: Identity</span>
                                        </div>
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

    <!-- Rejected Dealers History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-danger text-white">
            <h6 class="m-0 fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Rejected Dealers History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Verification Photo</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($rejectedDealers) > 0): ?>
                            <?php foreach ($rejectedDealers as $dealer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dealer['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['phone'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($dealer['verification_doc'])): ?>
                                            <a href="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" target="_blank" title="Click to View Full Size">
                                                <img src="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 80px; max-width: 120px; object-fit: cover;" 
                                                     alt="Verification Photo">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-danger">Rejected</span></td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Re-approve this dealer?');">
                                            <input type="hidden" name="user_id" value="<?php echo $dealer['id']; ?>">
                                            <button type="submit" name="action" value="verify" class="btn btn-success btn-sm">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No rejected dealers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Verified Dealers History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 fw-bold"><i class="bi bi-check-circle-fill me-2"></i>Verified Dealers History</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Verification Photo</th>
                            <th>Status</th>
                            <th>Phases</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($verifiedDealers) > 0): ?>
                            <?php foreach ($verifiedDealers as $dealer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($dealer['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['email'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($dealer['phone'] ?? ''); ?></td>
                                    <td class="text-center">
                                        <?php if (!empty($dealer['verification_doc'])): ?>
                                            <a href="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" target="_blank" title="Click to View Full Size">
                                                <img src="../<?php echo htmlspecialchars($dealer['verification_doc']); ?>" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 80px; max-width: 120px; object-fit: cover;" 
                                                     alt="Verification Photo">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No file</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-success">Verified</span></td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if(empty($dealer['verification_token'])): ?>
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Phase 1: Email Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-circle me-1"></i> Phase 1: Email Pending</span>
                                            <?php endif; ?>
                                            
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Phase 2: Identity Verified</span>
                                        </div>
                                    </td>
                                    <td>
                                        <form method="POST" onsubmit="return confirm('Are you sure you want to REVOKE verification for this dealer? They will be blocked from adding properties.');">
                                            <input type="hidden" name="user_id" value="<?php echo $dealer['id']; ?>">
                                            <button type="submit" name="action" value="revoke" class="btn btn-warning btn-sm">
                                                <i class="bi bi-x-circle"></i> Revoke
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No verified dealers found.</td>
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
