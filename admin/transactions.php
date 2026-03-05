<?php
require_once '../config/config.php';
require_once '../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Helper function to mask reference
function mask_reference($ref) {
    if (strlen($ref) <= 8) return $ref;
    return substr($ref, 0, 4) . '....' . substr($ref, -4);
}


// Fetch Transactions (Assuming a transactions table exists, otherwise use mock or empty)
$view = $_GET['view'] ?? 'local';
$lenco_transactions = [];
$lenco_error = null;

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if transactions table exists
    $tableExists = $pdo->query("SHOW TABLES LIKE 'transactions'")->rowCount() > 0;

    $transactions = [];
    if ($tableExists) {
        $stmt = $pdo->query("SELECT t.*, u.name as user_name, u.email as user_email 
                             FROM transactions t 
                             LEFT JOIN users u ON t.user_id = u.id 
                             ORDER BY t.created_at DESC");
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Fetch Lenco Data if requested
    if ($view === 'lenco') {
        require_once '../includes/LencoAPI.php';
        $lenco = new LencoAPI();
        $page = $_GET['page'] ?? 1;
        $response = $lenco->getCollections($page);
        
        if (isset($response['status']) && $response['status'] === true) {
            $lenco_transactions = $response['data'] ?? [];
        } else {
            $lenco_error = $response['message'] ?? "Failed to fetch data from Lenco.";
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
    <title>Transactions - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Content Wrapper -->
        <div class="container-fluid p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 fw-bold text-dark">Transactions</h4>
                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success py-1 px-3 mb-0 small"><i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger py-1 px-3 mb-0 small"><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                
                <div class="btn-group">
                    <a href="transactions.php?view=local" class="btn btn-outline-secondary <?php echo $view === 'local' ? 'active' : ''; ?>">System Logs</a>
                    <a href="transactions.php?view=lenco" class="btn btn-outline-secondary <?php echo $view === 'lenco' ? 'active' : ''; ?>">Lenco Live</a>
                </div>
            </div>

            <?php if ($view === 'lenco'): ?>
                <!-- Lenco Live Data -->
                <?php if ($lenco_error): ?>
                    <div class="alert alert-danger"><?php echo $lenco_error; ?></div>
                <?php endif; ?>
                
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
                         <h5 class="fw-bold mb-0">Lenco Collections (Live)</h5>
                         <span class="badge bg-primary">Page <?php echo htmlspecialchars($_GET['page'] ?? 1); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase">
                                    <tr>
                                        <th class="ps-4">Reference</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($lenco_transactions) > 0): ?>
                                        <?php foreach ($lenco_transactions as $txn): ?>
                                            <tr>
                                                <td class="ps-4 small fw-bold" title="<?php echo $txn['reference']; ?>">
                                                    <?php echo mask_reference($txn['reference'] ?? '-'); ?>
                                                </td>
                                                <td class="fw-bold"><?php echo ($txn['currency'] ?? 'ZMW') . ' ' . number_format($txn['amount'] ?? 0, 2); ?></td>
                                                <td>
                                                    <?php 
                                                        $status = strtolower($txn['status'] ?? '');
                                                        $badgeClass = match($status) {
                                                            'successful' => 'success',
                                                            'pending' => 'warning',
                                                            'failed' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>
                                                    <span class="badge bg-<?php echo $badgeClass; ?>-subtle text-<?php echo $badgeClass; ?> border border-<?php echo $badgeClass; ?>-subtle rounded-pill px-2">
                                                        <?php echo ucfirst($status); ?>
                                                    </span>
                                                </td>
                                                <td class="small">
                                                    <?php 
                                                        if (isset($txn['mobileMoneyDetails'])) {
                                                            echo $txn['mobileMoneyDetails']['phone'] ?? '-';
                                                            echo ' <span class="text-muted">(' . ($txn['mobileMoneyDetails']['operator'] ?? '-') . ')</span>';
                                                        } elseif (isset($txn['cardDetails'])) {
                                                            echo 'Card: **** ' . ($txn['cardDetails']['last4'] ?? '****');
                                                        } else {
                                                            echo '-';
                                                        }
                                                    ?>
                                                </td>
                                                <td class="small text-muted">
                                                    <?php echo isset($txn['createdAt']) ? date('M d, H:i', strtotime($txn['createdAt'])) : '-'; ?>
                                                </td>
                                                <td>
                                                    <a href="sync_transaction.php?reference=<?php echo $txn['reference']; ?>" class="btn btn-sm btn-light border" title="Sync to Local DB">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4 text-muted">No collections found.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0 py-3">
                        <?php $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>
                        <div class="d-flex justify-content-between">
                            <?php if($page > 1): ?>
                                <a href="transactions.php?view=lenco&page=<?php echo $page - 1; ?>" class="btn btn-sm btn-outline-secondary">Previous</a>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>
                            
                            <a href="transactions.php?view=lenco&page=<?php echo $page + 1; ?>" class="btn btn-sm btn-outline-secondary">Next</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Local Transactions Table -->
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">Ref ID</th>
                                    <th>User</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Message</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($transactions) > 0): ?>
                                    <?php foreach ($transactions as $txn): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-primary small" title="<?php echo $txn['reference']; ?>">
                                                <?php echo mask_reference($txn['reference']); ?>
                                            </td>
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
                                            <td class="text-capitalize small"><?php echo htmlspecialchars($txn['payment_method'] ?? 'card'); ?></td>
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
                                            <td class="text-muted small"><?php echo date('M d, H:i', strtotime($txn['created_at'])); ?></td>
                                            <td class="small text-muted text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($txn['message']); ?>">
                                                <?php echo htmlspecialchars($txn['message']); ?>
                                            </td>
                                            <td>
                                                <a href="sync_transaction.php?reference=<?php echo $txn['reference']; ?>" class="btn btn-sm btn-outline-primary" title="Sync from Lenco">
                                                    <i class="bi bi-arrow-repeat"></i> Sync
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-receipt fs-1 d-block mb-3 opacity-50"></i>
                                                <h5 class="fw-bold">No Transactions Found</h5>
                                            </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>