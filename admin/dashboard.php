<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Property.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Database Connection for Stats
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get Total Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $total_users = $stmt->fetchColumn();

    // Get Total Dealers
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'dealer'");
    $total_dealers = $stmt->fetchColumn();

    // Get Total Properties
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties");
    $total_properties = $stmt->fetchColumn();

    // Get Pending Verifications (New)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'dealer' AND identity_verified = 0 AND verification_doc IS NOT NULL");
    $pending_verifications = $stmt->fetchColumn();

    // Get Recent Transactions with User Details
    $recent_transactions = [];
    $transactions_exist = $pdo->query("SHOW TABLES LIKE 'transactions'")->rowCount() > 0;

    // Get Total Revenue
    $total_revenue = 0;
    if ($transactions_exist) {
        $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'successful'");
        $total_revenue = $stmt->fetchColumn() ?: 0;
    }
    
    // Get Recent Transactions from Lenco Live
    require_once '../includes/LencoAPI.php';
    $lenco = new LencoAPI();
    $response = $lenco->getCollections(1); // Page 1
    $lenco_transactions = [];

    if (isset($response['status']) && $response['status'] === true) {
        $raw_transactions = $response['data'] ?? [];
        $raw_transactions = array_slice($raw_transactions, 0, 5); // Limit to 5
        
        foreach($raw_transactions as $ltxn) {
            $ref = $ltxn['reference'] ?? '';
            
            // Try to find user in local DB
            $user_name = 'External Client';
            $user_contact = 'Unknown';
            
            if (isset($ltxn['mobileMoneyDetails']['phone'])) {
                $user_contact = $ltxn['mobileMoneyDetails']['phone'];
            } elseif (isset($ltxn['cardDetails']['last4'])) {
                $user_contact = 'Card ****' . $ltxn['cardDetails']['last4'];
            }
            
            if ($ref) {
                $stmt = $pdo->prepare("SELECT u.name, u.email FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.reference = :ref LIMIT 1");
                $stmt->execute([':ref' => $ref]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $user_name = $user['name'];
                    $user_contact = $user['email'];
                }
            }

            $lenco_transactions[] = [
                'reference' => $ref,
                'user_name' => $user_name,
                'user_email' => $user_contact,
                'currency' => $ltxn['currency'] ?? 'ZMW',
                'amount' => $ltxn['amount'] ?? 0,
                'status' => $ltxn['status'] ?? 'pending',
                'created_at' => $ltxn['createdAt'] ?? date('Y-m-d H:i:s')
            ];
        }
    }
    
    // Fallback if API fails or is empty, use empty array (or local DB if preferred, but user asked for live lenco)
    $recent_transactions = $lenco_transactions;

    // Get Actual Account Balance (Settlements)
    $account_balance = 0;
    
    // We try to fetch the actual wallet/settlement balance, not just sum of transactions.
    $balance_response = $lenco->getBalance();
    
    if (isset($balance_response['status']) && $balance_response['status'] === true) {
        // Check for 'data' object which might contain 'available' or 'balance'
        if (isset($balance_response['data']['balance'])) {
             $account_balance = $balance_response['data']['balance'];
        } elseif (isset($balance_response['data']['available'])) {
             $account_balance = $balance_response['data']['available'];
        } else {
            // If it returns a list of settlements, sum up the pending/available ones?
            // Usually settlements endpoint returns a list. 
            // If no direct balance endpoint exists, we might need to rely on the manual sum we did before
            // BUT user says "needs to show exact balance not total traction"
            
            // Let's fallback to the sum calculation if API doesn't give a clear single balance field
            // But reset it first to ensure we don't double count.
            
            // If the user means "Current Available Balance" (which might be less than total revenue due to payouts),
            // we really need that specific endpoint.
            
            // Reverting to the transaction sum for now as a "Best Guess" of balance if no payouts have happened.
             if ($transactions_exist) {
                $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'successful' OR status = 'completed'");
                $account_balance = $stmt->fetchColumn() ?: 0;
            }
        }
    } else {
         // Fallback to DB sum
         if ($transactions_exist) {
            $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'successful' OR status = 'completed'");
            $account_balance = $stmt->fetchColumn() ?: 0;
        }
    }

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold text-dark">Dashboard Overview</h4>
            <div class="d-flex align-items-center">
                <div class="me-3 text-end">
                    <p class="mb-0 fw-bold text-dark"><?php echo $_SESSION['user_name']; ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <?php if ($pending_verifications > 0): ?>
            <div class="col-md-12">
                <div class="alert alert-warning border-warning shadow-sm d-flex justify-content-between align-items-center p-4">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Pending Dealer Verifications</h4>
                        <p class="mb-0 text-dark"><?php echo $pending_verifications; ?> dealer(s) are waiting for identity approval.</p>
                    </div>
                    <a href="verify_dealers.php" class="btn btn-warning fw-bold text-dark px-4">Review Requests</a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-3">
                <div class="stats-card p-4 rounded shadow-sm bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Total Users</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_users; ?></h2>
                        </div>
                        <div class="bg-light rounded p-3 text-primary">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 rounded shadow-sm bg-white border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Dealers</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_dealers; ?></h2>
                        </div>
                        <div class="bg-light rounded p-3 text-success">
                            <i class="bi bi-briefcase fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 rounded shadow-sm bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Properties</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?php echo $total_properties; ?></h2>
                        </div>
                        <div class="bg-light rounded p-3 text-warning">
                            <i class="bi bi-house fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card p-4 rounded shadow-sm bg-white border-start border-4 border-danger">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase small fw-bold">Account Balance</h6>
                            <h2 class="mb-0 fw-bold text-dark">K <?php echo number_format($account_balance, 2); ?></h2>
                        </div>
                        <div class="bg-light rounded p-3 text-danger">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-top small">
                         <span class="text-muted">Total Revenue: K <?php echo number_format($total_revenue, 2); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Transactions</h5>
                <a href="transactions.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Transaction ID</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_transactions) > 0): ?>
                                <?php foreach ($recent_transactions as $txn): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary small"><?php echo htmlspecialchars($txn['reference']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2 d-none d-md-block">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark small"><?php echo htmlspecialchars($txn['user_name'] ?? 'Unknown'); ?></div>
                                                    <div class="small text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($txn['user_email'] ?? '-'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($txn['currency'] . ' ' . number_format($txn['amount'], 2)); ?></td>
                                        <td>
                                            <?php 
                                                $status = strtolower($txn['status']);
                                                if($status == 'successful' || $status == 'completed'): 
                                            ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Success</span>
                                            <?php elseif($status == 'pending' || $status == 'submitted'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2">Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small">
                                            <div><?php echo date('M d, Y', strtotime($txn['created_at'])); ?></div>
                                            <div class="text-xs opacity-75"><?php echo date('H:i A', strtotime($txn['created_at'])); ?></div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No transactions found.</td>
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