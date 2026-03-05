<div class="admin-sidebar shadow">
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
            <a href="transactions.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
                <i class="bi bi-wallet2"></i> Transactions
            </a>
        </li>
        <li class="sidebar-item">
            <a href="users.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
                <i class="bi bi-people-fill"></i> Users
            </a>
        </li>
        <li class="sidebar-item">
            <a href="reports.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up-arrow"></i> Reports
            </a>
        </li>
        <li class="sidebar-item">
            <a href="logs.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'logs.php' ? 'active' : ''; ?>">
                <i class="bi bi-journal-text"></i> Activity Logs
            </a>
        </li>
        <li class="sidebar-item">
            <a href="settings.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </li>
        <li class="sidebar-item">
            <a href="properties.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?>">
                <i class="bi bi-houses-fill"></i> Properties
            </a>
        </li>
        <li class="sidebar-item mt-5">
            <a href="../logout.php" class="sidebar-link text-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</div>