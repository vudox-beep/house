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
            <a href="properties.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?>">
                <i class="bi bi-houses-fill"></i> My Properties
            </a>
        </li>
        <li class="sidebar-item">
            <a href="leads.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active' : ''; ?>">
                <i class="bi bi-chat-left-text-fill"></i> Leads
            </a>
        </li>
        <li class="sidebar-item">
            <a href="subscribe.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'subscribe.php' ? 'active' : ''; ?>">
                <i class="bi bi-credit-card-2-front-fill"></i> Subscription
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