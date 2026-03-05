<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/ActivityLogger.php';

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

    $logger = new ActivityLogger();

    // Handle User Actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $userObj = new User();
        
        if ($_POST['action'] === 'verify_user') {
            $user_id = $_POST['user_id'];
            $status = $_POST['verification_status']; // 1 or 0
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET is_verified = :status WHERE id = :id");
                if ($stmt->execute([':status' => $status, ':id' => $user_id])) {
                    $success_msg = "User verification status updated.";
                    $logger->log($_SESSION['user_id'], 'admin', 'verify_user', "Updated verification for user ID: $user_id to $status");
                } else {
                    $error_msg = "Failed to update verification status.";
                }
            } catch (PDOException $e) {
                $error_msg = "Database Error: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'update_subscription') {
            $user_id = $_POST['user_id'];
            $status = $_POST['subscription_status'];
            $expiry = $_POST['subscription_expiry'];
            if (empty($expiry)) $expiry = null;

            if ($userObj->updateSubscription($user_id, $status, $expiry)) {
                $success_msg = "Subscription updated successfully.";
                $logger->log($_SESSION['user_id'], 'admin', 'update_subscription', "Updated subscription for user ID: $user_id. Status: $status, Expiry: $expiry");
            } else {
                $error_msg = "Failed to update subscription.";
            }
        } elseif ($_POST['action'] === 'delete_user') {
            $user_id = $_POST['user_id'];
            // Prevent deleting self
            if ($user_id == $_SESSION['user_id']) {
                $error_msg = "You cannot delete your own account.";
            } else {
                if ($userObj->delete($user_id)) {
                    $success_msg = "User deleted successfully.";
                    $logger->log($_SESSION['user_id'], 'admin', 'delete_user', "Deleted user ID: $user_id");
                } else {
                    $error_msg = "Failed to delete user.";
                }
            }
        } elseif ($_POST['action'] === 'toggle_ban') {
            $user_id = $_POST['user_id'];
            $status = $_POST['ban_status']; // 1 for ban, 0 for unban
            
            if ($user_id == $_SESSION['user_id']) {
                $error_msg = "You cannot ban your own account.";
            } else {
                if ($userObj->toggleBan($user_id, $status)) {
                    $success_msg = "User status updated successfully.";
                    $action_type = $status == 1 ? 'ban_user' : 'unban_user';
                    $logger->log($_SESSION['user_id'], 'admin', $action_type, "Changed ban status for user ID: $user_id to $status");
                } else {
                    $error_msg = "Failed to update user status.";
                }
            }
        }
    }

    // Filter by role
    $role_filter = $_GET['role'] ?? '';
    $query = "SELECT u.*, 
                     d.subscription_status, 
                     d.subscription_expiry,
                     d.company_name,
                     d.office_address,
                     d.bio
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
                                            <?php if(!empty($user['is_banned'])): ?>
                                                <span class="badge bg-danger">Banned</span>
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
                                                
                                                <?php if(!$user['is_verified']): ?>
                                                    <button class="btn btn-sm btn-outline-success border me-1" 
                                                            onclick="verifyUser(<?php echo $user['id']; ?>, 1)"
                                                            title="Approve / Verify Dealer">
                                                        <i class="bi bi-patch-check"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary border me-1" 
                                                            onclick="verifyUser(<?php echo $user['id']; ?>, 0)"
                                                            title="Revoke Verification">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <button class="btn btn-sm btn-light border me-1" 
                                                    onclick='viewUser(<?php echo json_encode($user); ?>)'
                                                    title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            
                                            <?php if($user['role'] !== 'admin'): ?>
                                                <?php if(empty($user['is_banned'])): ?>
                                                    <button class="btn btn-sm btn-outline-warning border me-1" 
                                                            onclick="confirmBan(<?php echo $user['id']; ?>, 'Ban this user?', 1)"
                                                            title="Ban User">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-success border me-1" 
                                                            onclick="confirmBan(<?php echo $user['id']; ?>, 'Unban this user?', 0)"
                                                            title="Unban User">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <button class="btn btn-sm btn-outline-danger border" 
                                                        onclick="confirmDelete(<?php echo $user['id']; ?>)"
                                                        title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
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
                    <input type="hidden" name="action" value="update_subscription">
                    <input type="hidden" name="user_id" id="sub_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Subscription</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
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

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 100px; height: 100px;">
                                <i class="bi bi-person fs-1 text-muted" id="detail_icon"></i>
                                <img src="" id="detail_img" class="rounded-circle w-100 h-100 object-fit-cover d-none">
                            </div>
                            <h5 id="detail_name" class="fw-bold mb-1"></h5>
                            <span id="detail_role" class="badge bg-secondary"></span>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Account Information</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="small text-muted d-block">Email</label>
                                    <span id="detail_email" class="fw-medium"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted d-block">Phone</label>
                                    <span id="detail_phone" class="fw-medium"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted d-block">Status</label>
                                    <span id="detail_status"></span>
                                </div>
                                <div class="col-sm-6">
                                    <label class="small text-muted d-block">Joined Date</label>
                                    <span id="detail_joined" class="fw-medium"></span>
                                </div>
                            </div>

                            <!-- Dealer Specific Info -->
                            <div id="dealer_info_section" class="d-none mt-4">
                                <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Dealer Profile</h6>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="small text-muted d-block">Company Name</label>
                                        <span id="detail_company" class="fw-medium"></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="small text-muted d-block">Office Address</label>
                                        <span id="detail_address" class="fw-medium"></span>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted d-block">Subscription</label>
                                        <span id="detail_subscription"></span>
                                    </div>
                                    <div class="col-12">
                                        <label class="small text-muted d-block">Bio</label>
                                        <p id="detail_bio" class="text-muted small bg-light p-2 rounded mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_user">
        <input type="hidden" name="user_id" id="delete_user_id">
    </form>

    <!-- Ban Confirmation Form -->
    <form id="banForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_ban">
        <input type="hidden" name="user_id" id="ban_user_id">
        <input type="hidden" name="ban_status" id="ban_status">
    </form>

    <!-- Verification Form -->
    <form id="verifyForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="verify_user">
        <input type="hidden" name="user_id" id="verify_user_id">
        <input type="hidden" name="verification_status" id="verify_status">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function verifyUser(userId, status) {
        const action = status === 1 ? 'Verify' : 'Unverify';
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            document.getElementById('verify_user_id').value = userId;
            document.getElementById('verify_status').value = status;
            document.getElementById('verifyForm').submit();
        }
    }

    function viewUser(user) {
        // Basic Info
        document.getElementById('detail_name').innerText = user.name;
        document.getElementById('detail_email').innerText = user.email;
        document.getElementById('detail_phone').innerText = user.phone || 'N/A';
        document.getElementById('detail_joined').innerText = new Date(user.created_at).toLocaleDateString();
        
        // Role Badge
        const roleBadge = document.getElementById('detail_role');
        roleBadge.className = 'badge';
        if (user.role === 'admin') roleBadge.classList.add('bg-danger');
        else if (user.role === 'dealer') roleBadge.classList.add('bg-primary');
        else roleBadge.classList.add('bg-secondary');
        roleBadge.innerText = user.role.charAt(0).toUpperCase() + user.role.slice(1);

        // Status
        const statusSpan = document.getElementById('detail_status');
        let statusHtml = '';
        if (user.is_verified) statusHtml += '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill me-1">Verified</span>';
        else statusHtml += '<span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill me-1">Unverified</span>';
        
        if (user.is_banned == 1) statusHtml += '<span class="badge bg-danger">Banned</span>';
        else statusHtml += '<span class="badge bg-success">Active</span>';
        statusSpan.innerHTML = statusHtml;

        // Profile Image
        const icon = document.getElementById('detail_icon');
        const img = document.getElementById('detail_img');
        if (user.profile_image) {
            img.src = '../' + user.profile_image;
            img.classList.remove('d-none');
            icon.classList.add('d-none');
        } else {
            img.classList.add('d-none');
            icon.classList.remove('d-none');
        }

        // Dealer Specifics
        const dealerSection = document.getElementById('dealer_info_section');
        if (user.role === 'dealer') {
            dealerSection.classList.remove('d-none');
            document.getElementById('detail_company').innerText = user.company_name || 'N/A';
            document.getElementById('detail_address').innerText = user.office_address || 'N/A';
            document.getElementById('detail_bio').innerText = user.bio || 'No bio available.';
            
            let subText = '';
            if (user.subscription_status === 'active') {
                subText = '<span class="text-success fw-bold">Active</span>';
                if (user.subscription_expiry) subText += ' (Expires: ' + new Date(user.subscription_expiry).toLocaleDateString() + ')';
            } else {
                subText = '<span class="text-secondary">Inactive</span>';
            }
            document.getElementById('detail_subscription').innerHTML = subText;
        } else {
            dealerSection.classList.add('d-none');
        }

        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
    }

    function manageSubscription(id, status, expiry) {
        document.getElementById('sub_user_id').value = id;
        document.getElementById('sub_status').value = status;
        document.getElementById('sub_expiry').value = expiry;
        new bootstrap.Modal(document.getElementById('subscriptionModal')).show();
    }

    function confirmDelete(userId) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone and will remove all their data including properties and leads.')) {
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('deleteForm').submit();
        }
    }

    function confirmBan(userId, message, status) {
        if (confirm(message)) {
            document.getElementById('ban_user_id').value = userId;
            document.getElementById('ban_status').value = status;
            document.getElementById('banForm').submit();
        }
    }
    </script>
</body>
</html>