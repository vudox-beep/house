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
            <span class="me-3 fw-bold text-muted d-none d-md-block"><?php echo $_SESSION['user_name']; ?></span>
            <img src="../assets/images/user-placeholder.png" class="rounded-circle" width="40" height="40" alt="Admin">
        </div>
    </div>

<script>
    document.getElementById('adminSidebarToggle').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('active');
    });
</script>