<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../models/Property.php';

// Set timezone to Lusaka (UTC+2) for correct date display
date_default_timezone_set('Africa/Lusaka');

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

    // Initialize amounts
    $total_revenue = 0;
    $amounts_source = 'live'; // Track where amounts come from
    
    // Get ALL transactions from Lenco Live to compute accurate totals
    require_once '../includes/LencoAPI.php';
    $lenco = new LencoAPI();
    
    $all_lenco_transactions = [];
    $lenco_api_success = false;
    $lenco_page = 1;
    $max_pages = 50; // Safety limit to prevent infinite loops
    
    // Fetch all pages from Lenco to get complete transaction history
    while ($lenco_page <= $max_pages) {
        $response = $lenco->getCollections($lenco_page);
        
        if (isset($response['status']) && $response['status'] === true) {
            $lenco_api_success = true;
            $page_data = $response['data'] ?? [];
            
            if (empty($page_data)) {
                break; // No more data
            }
            
            $all_lenco_transactions = array_merge($all_lenco_transactions, $page_data);
            $lenco_page++;
        } else {
            // API call failed - if we got at least some data, use it
            if ($lenco_page > 1) {
                $lenco_api_success = true; // We got partial data
            }
            break;
        }
    }
    
    // Calculate Total Revenue from LIVE Lenco data (only successful/completed)
    if ($lenco_api_success && !empty($all_lenco_transactions)) {
        foreach ($all_lenco_transactions as $ltxn) {
            $txn_status = strtolower($ltxn['status'] ?? '');
            if ($txn_status === 'successful' || $txn_status === 'completed') {
                $total_revenue += floatval($ltxn['amount'] ?? 0);
            }
        }
    } else {
        // Fallback to local DB ONLY if Lenco API completely fails
        $amounts_source = 'local';
        if ($transactions_exist) {
            $stmt = $pdo->query("SELECT SUM(amount) FROM transactions WHERE status = 'successful' OR status = 'completed'");
            $total_revenue = $stmt->fetchColumn() ?: 0;
        }
    }
    
    // Get Recent Transactions (latest 5 from Lenco)
    $recent_lenco = array_slice($all_lenco_transactions, 0, 5);
    $lenco_transactions = [];

    foreach($recent_lenco as $ltxn) {
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

        // Get the date from whichever field Lenco uses
        $txn_date_raw = $ltxn['createdAt'] 
            ?? $ltxn['created_at'] 
            ?? $ltxn['date'] 
            ?? $ltxn['transactionDate'] 
            ?? $ltxn['updatedAt'] 
            ?? null;
        
        // If no date field found, extract timestamp from reference (e.g. SUB-1776697138090)
        if (!$txn_date_raw && $ref) {
            if (preg_match('/(\d{10,13})/', $ref, $matches)) {
                $ts = (int)$matches[1];
                // If milliseconds (13 digits), convert to seconds
                if ($ts > 9999999999) {
                    $ts = intval($ts / 1000);
                }
                $txn_date_raw = date('Y-m-d\TH:i:s.000\Z', $ts);
            }
        }
        
        // Final fallback
        if (!$txn_date_raw) {
            $txn_date_raw = date('Y-m-d H:i:s');
        }

        $lenco_transactions[] = [
            'reference' => $ref,
            'user_name' => $user_name,
            'user_email' => $user_contact,
            'currency' => $ltxn['currency'] ?? 'ZMW',
            'amount' => $ltxn['amount'] ?? 0,
            'status' => $ltxn['status'] ?? 'pending',
            'created_at' => $txn_date_raw
        ];
    }
    
    $recent_transactions = $lenco_transactions;
    $last_updated = date('H:i:s');

    // Get Actual Account Balance (Settlements)
    $account_balance = 0;
    $balance_source = 'live';
    
    // Try to fetch the actual wallet/settlement balance from Lenco
    $balance_response = $lenco->getBalance();
    
    if (isset($balance_response['status']) && $balance_response['status'] === true) {
        // Check standard Lenco account structure
        if (isset($balance_response['data']['availableBalance'])) {
             $account_balance = $balance_response['data']['availableBalance'];
        } elseif (isset($balance_response['data']['balance'])) {
             $account_balance = $balance_response['data']['balance'];
        } elseif (isset($balance_response['data']['currentBalance'])) {
             $account_balance = $balance_response['data']['currentBalance'];
        } else {
             // If balance API returns unexpected structure, use live revenue as balance
             $account_balance = $total_revenue;
             $balance_source = 'calculated';
        }
    } else {
         // If balance API fails entirely, use the live revenue total
         $account_balance = $total_revenue;
         $balance_source = 'calculated';
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
            <div class="d-flex align-items-center">
                <button class="btn btn-link d-md-none me-3 p-0 text-dark" id="adminSidebarToggle">
                    <i class="bi bi-list fs-3"></i>
                </button>
                <h4 class="mb-0 fw-bold text-dark">Dashboard Overview</h4>
            </div>
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
                         <?php if ($amounts_source === 'local'): ?>
                            <span class="badge bg-warning-subtle text-warning ms-1" title="Lenco API unavailable, showing local data">Local</span>
                         <?php else: ?>
                            <span class="badge bg-success-subtle text-success ms-1" title="Updated at <?php echo $last_updated; ?>">Live</span>
                         <?php endif; ?>
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
                                            <?php 
                                                $txn_date = new DateTime($txn['created_at'], new DateTimeZone('UTC'));
                                                $txn_date->setTimezone(new DateTimeZone('Africa/Lusaka'));
                                            ?>
                                            <div><?php echo $txn_date->format('M d, Y'); ?></div>
                                            <div class="text-xs opacity-75"><?php echo $txn_date->format('H:i A'); ?></div>
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
    <script>
        document.getElementById('adminSidebarToggle').addEventListener('click', function() {
            document.querySelector('.admin-sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>