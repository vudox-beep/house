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

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle Subscription Update
    $success_msg = '';
    $error_msg = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_subscription') {
        $user_id = $_POST['user_id'];
        $status = $_POST['subscription_status'];
        $expiry = $_POST['subscription_expiry'];
        
        // If expiry is empty, set to NULL (lifetime) or handle accordingly. 
        // Ideally, if active, it should have an expiry or be far in future.
        if (empty($expiry)) $expiry = null;

        $userObj = new User();
        if ($userObj->updateSubscription($user_id, $status, $expiry)) {
            $success_msg = "Subscription updated successfully.";
        } else {
            $error_msg = "Failed to update subscription.";
        }
    }

    // Filter by role
    $role_filter = $_GET['role'] ?? '';
    $query = "SELECT u.*, d.subscription_status, d.subscription_expiry 
              FROM users u 
              LEFT JOIN dealers d ON u.id = d.user_id 
              WHERE 1=1";
    $params = [];

    if ($role_filter) {
        $query .= " AND u.role = :role";
        $params[':role'] = $role_filter;
    }

    $query .= " ORDER BY u.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin Panel</title>
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
            <h4 class="mb-0 fw-bold text-dark">User Management</h4>
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

        <!-- Messages -->
        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="col-form-label fw-bold">Filter by Role:</label>
                    </div>
                    <div class="col-auto">
                        <select class="form-select" name="role" onchange="this.form.submit()">
                            <option value="">All Users</option>
                            <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>Users</option>
                            <option value="dealer" <?php echo $role_filter == 'dealer' ? 'selected' : ''; ?>>Dealers</option>
                            <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admins</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>Role</th>
                                <th>Subscription</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle p-2 me-2 text-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($user['name']); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($user['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($user['role'] == 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php elseif($user['role'] == 'dealer'): ?>
                                                <span class="badge bg-primary">Dealer</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">User</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($user['role'] === 'dealer'): ?>
                                                <?php 
                                                    $subStatus = $user['subscription_status'] ?? 'inactive';
                                                    $subExpiry = $user['subscription_expiry'];
                                                    $isExpired = $subExpiry && strtotime($subExpiry) < time();
                                                    
                                                    if ($subStatus === 'active' && !$isExpired) {
                                                        echo '<span class="badge bg-success">Active</span>';
                                                        if ($subExpiry) echo '<div class="small text-muted mt-1">Exp: '.date('M d, Y', strtotime($subExpiry)).'</div>';
                                                    } else {
                                                        echo '<span class="badge bg-secondary">Inactive</span>';
                                                        if ($isExpired) echo '<div class="small text-danger mt-1">Expired</div>';
                                                    }
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($user['is_verified']): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Verified</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Unverified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if($user['role'] === 'dealer'): ?>
                                                <button class="btn btn-sm btn-outline-primary border me-1" 
                                                        onclick="manageSubscription(<?php echo $user['id']; ?>, '<?php echo $user['subscription_status'] ?? 'inactive'; ?>', '<?php echo $user['subscription_expiry'] ? date('Y-m-d', strtotime($user['subscription_expiry'])) : ''; ?>')"
                                                        title="Manage Subscription">
                                                    <i class="bi bi-credit-card"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-light border me-1" title="View Details"><i class="bi bi-eye"></i></button>
                                            <?php if($user['role'] !== 'admin'): ?>
                                                <button class="btn btn-sm btn-light border text-danger" title="Delete User"><i class="bi bi-trash"></i></button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <h5 class="fw-bold text-muted">No Users Found</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Modal -->
    <div class="modal fade" id="subscriptionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_subscription">
                        <input type="hidden" name="user_id" id="sub_user_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="subscription_status" id="sub_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Expiry Date</label>
                            <input type="date" class="form-control" name="subscription_expiry" id="sub_expiry">
                            <div class="form-text">Leave blank for lifetime or no expiry.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function manageSubscription(id, status, expiry) {
        document.getElementById('sub_user_id').value = id;
        document.getElementById('sub_status').value = status;
        document.getElementById('sub_expiry').value = expiry;
        new bootstrap.Modal(document.getElementById('subscriptionModal')).show();
    }
    </script>
</body>
</html>