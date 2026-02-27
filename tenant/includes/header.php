<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not user (tenant)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dealer.css"> <!-- Reuse Dealer CSS for consistent look -->
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="dealer-main">
    <div class="dealer-topbar">
        <div class="d-flex align-items-center">
            <button class="btn btn-link d-md-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-3"></i>
            </button>
            <h4 class="mb-0 fw-bold">Dashboard</h4>
        </div>
        <div class="d-flex align-items-center">
            
            <span class="me-3 fw-bold text-muted d-none d-md-block"><?php echo $_SESSION['user_name']; ?></span>
            <?php 
            // Fallback if session var not set (e.g. older session)
            $profileImg = $_SESSION['profile_image'] ?? null;
            if (empty($profileImg) || !file_exists(__DIR__ . '/../../' . $profileImg)) {
                // Use UI Avatars if no image or file missing
                $headerProfile = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=random&size=128';
            } else {
                $headerProfile = '../' . $profileImg;
            }
            ?>
            <img src="<?php echo $headerProfile; ?>" class="rounded-circle" width="40" height="40" alt="Tenant" style="object-fit: cover;">
        </div>
    </div>

<script>
    const sidebar = document.querySelector('.dealer-sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');

    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent immediate closing
        sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (sidebar.classList.contains('active') && 
            !sidebar.contains(e.target) && 
            !toggleBtn.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    });
</script>