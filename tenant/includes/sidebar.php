<div class="dealer-sidebar shadow">
    <a href="../index.php" class="sidebar-brand">
        <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
    </a>
    
    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="sidebar-item">
            <a href="my_rental.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_rental.php' ? 'active' : ''; ?>">
                <i class="bi bi-house-door-fill"></i> My Rental
            </a>
        </li>
        <li class="sidebar-item">
            <a href="saved_properties.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'saved_properties.php' ? 'active' : ''; ?>">
                <i class="bi bi-heart-fill"></i> Saved Properties
            </a>
        </li>
        <li class="sidebar-item">
            <a href="payments.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <i class="bi bi-wallet2"></i> Rent Payments
            </a>
        </li>
        <li class="sidebar-item">
            <a href="profile.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                <i class="bi bi-person-circle"></i> Profile
            </a>
        </li>
        <li class="sidebar-item mt-5">
            <a href="../logout.php" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</div>