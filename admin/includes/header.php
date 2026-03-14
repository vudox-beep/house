<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="admin-main">
    <div class="admin-topbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-md-none me-3" id="adminSidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <h4 class="mb-0 fw-bold">Admin Panel</h4>
        </div>
        <div class="d-flex align-items-center">
            
            <!-- Notification Manager Link -->
            <a href="notifications.php" class="text-secondary text-decoration-none me-4 position-relative">
                <i class="bi bi-bell-fill fs-4"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-primary border border-light rounded-circle" style="font-size: 0.5rem;">
                    <span class="visually-hidden">Manage</span>
                </span>
            </a>

            <!-- Admin Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <span class="me-2 fw-bold text-dark d-none d-md-block"><?php echo $_SESSION['user_name']; ?></span>
                    <img src="../assets/images/user-placeholder.png" class="rounded-circle border" width="40" height="40" alt="Admin">
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item" href="notifications.php"><i class="bi bi-megaphone me-2"></i> Send Notification</a></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i> Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

<script>
    document.getElementById('adminSidebarToggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
    });
</script>