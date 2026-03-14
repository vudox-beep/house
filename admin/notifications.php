<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Session Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Handle Create Notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $type = $_POST['type'];
    $target = $_POST['target_role'];

    if ($title && $message) {
        $stmt = $pdo->prepare("INSERT INTO notifications (title, message, type, target_role, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $message, $type, $target, $_SESSION['user_id']]);
        $success = "Notification sent successfully!";
    } else {
        $error = "Title and message are required.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: notifications.php");
    exit();
}

// Fetch Notifications
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Notifications - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-sidebar { min-height: 100vh; background: #2c3e50; color: white; width: 250px; position: fixed; }
        .sidebar-brand { padding: 20px; font-size: 1.5rem; font-weight: bold; color: #f1c40f; text-decoration: none; display: block; text-align: center; }
        .sidebar-nav { list-style: none; padding: 0; }
        .sidebar-link { display: block; padding: 15px 20px; color: rgba(255,255,255,0.8); text-decoration: none; transition: 0.3s; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid #f1c40f; }
        .sidebar-link i { margin-right: 10px; }
        .admin-main { margin-left: 250px; padding: 30px; background-color: #f8f9fa; min-height: 100vh; }
    </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="admin-main">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-bell-fill text-primary"></i> Notifications Manager</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg"></i> Send New Message
        </button>
    </div>

    <?php if(isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Message</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notifications as $notif): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo htmlspecialchars($notif['title']); ?></td>
                                <td><?php echo htmlspecialchars(substr($notif['message'], 0, 50)) . (strlen($notif['message'])>50 ? '...' : ''); ?></td>
                                <td>
                                    <?php 
                                    $badge = match($notif['type']) {
                                        'info' => 'bg-info',
                                        'warning' => 'bg-warning',
                                        'danger' => 'bg-danger',
                                        'success' => 'bg-success',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($notif['type']); ?></span>
                                </td>
                                <td><span class="badge bg-dark"><?php echo ucfirst($notif['target_role']); ?></span></td>
                                <td class="small text-muted"><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></td>
                                <td class="text-end pe-4">
                                    <a href="?delete=<?php echo $notif['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this notification?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($notifications)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No notifications sent yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Send New Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g., System Maintenance">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="3" required placeholder="Enter your message here..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="info">Info (Blue)</option>
                                <option value="success">Success (Green)</option>
                                <option value="warning">Warning (Yellow)</option>
                                <option value="danger">Danger (Red)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Target Audience</label>
                            <select name="target_role" class="form-select">
                                <option value="all">All Users</option>
                                <option value="dealer">Dealers Only</option>
                                <option value="user">Tenants Only</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
