<div class="dealer-sidebar shadow">
    <a href="../index.php" class="sidebar-brand">
        <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
    </a>
    
    <?php
    // Fetch counts for badges
    // We assume db connection is available via header inclusion or require it here
    if (!isset($pdo)) {
        require_once __DIR__ . '/../../config/db.php';
        $db = new Database();
        $pdo = $db->connect();
    }
    
    $dealer_id = $_SESSION['user_id'];
    
    // Count active properties
    $stmtProp = $pdo->prepare("SELECT COUNT(*) FROM properties WHERE dealer_id = ? AND status = 'active'");
    $stmtProp->execute([$dealer_id]);
    $activeProps = $stmtProp->fetchColumn();
    
    // Count new leads
    // Assuming leads table has a 'status' column where 'new' or 'unread' is the default
    // If status column doesn't exist, we might need to check if table exists first or add it
    // For now, let's try to count all leads if status doesn't exist, or just 'new' ones
    try {
        $stmtLead = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE dealer_id = ? AND status = 'new'");
        $stmtLead->execute([$dealer_id]);
        $newLeads = $stmtLead->fetchColumn();
    } catch (Exception $e) {
        $newLeads = 0; // Fallback if column missing
    }
    ?>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="dashboard.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="sidebar-item">
            <a href="properties.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                <span><i class="bi bi-houses-fill"></i> My Properties</span>
                <?php if($activeProps > 0): ?>
                    <span class="badge bg-primary rounded-pill small"><?php echo $activeProps; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="leads.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'leads.php' ? 'active' : ''; ?> d-flex justify-content-between align-items-center">
                <span><i class="bi bi-chat-left-text-fill"></i> Leads</span>
                <?php if($newLeads > 0): ?>
                    <span class="badge bg-danger rounded-pill small"><?php echo $newLeads; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="subscribe.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'subscribe.php' ? 'active' : ''; ?>">
                <i class="bi bi-credit-card-2-front-fill"></i> Subscription
            </a>
        </li>
        <li class="sidebar-item">
            <a href="payments.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                <i class="bi bi-receipt"></i> Payment History
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