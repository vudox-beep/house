<div class="dealer-sidebar shadow d-flex flex-column">
    <div class="px-3 pt-3 pb-2">
        <a href="../index.php" class="sidebar-brand m-0 p-0 text-decoration-none d-block mb-3">
            <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
        </a>
    </div>
    
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
    try {
        // Check if leads table exists and has status column first to avoid errors
        // For simplicity in this fix, we wrap in try-catch
        $stmtLead = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE dealer_id = ? AND status = 'new'");
        $stmtLead->execute([$dealer_id]);
        $newLeads = $stmtLead->fetchColumn();
    } catch (Exception $e) {
        $newLeads = 0; // Fallback
    }
    ?>

    <div class="flex-grow-1 overflow-y-auto">
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
                    <span><i class="bi bi-envelope-open-fill"></i> Inquiries</span>
                    <?php if($newLeads > 0): ?>
                        <span class="badge bg-danger rounded-pill small"><?php echo $newLeads; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="messages.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : ''; ?>">
                    <i class="bi bi-chat-dots-fill"></i> Live Chat
                </a>
            </li>
            <li class="sidebar-item">
                <a href="tenants.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'tenants.php' ? 'active' : ''; ?>">
                    <i class="bi bi-people-fill"></i> Manage Tenants
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
                <a href="referrals.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'referrals.php' ? 'active' : ''; ?>">
                    <i class="bi bi-megaphone-fill"></i> Referrals
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <i class="bi bi-person-circle"></i> Profile
                </a>
            </li>
        </ul>
    </div>

    <div class="mt-auto p-3 border-top border-white border-opacity-10">
        <a href="../logout.php" class="sidebar-link text-danger bg-transparent ps-2">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>
