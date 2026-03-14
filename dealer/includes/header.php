<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not dealer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dealer') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../models/User.php';

// Fetch Subscription Status (Fresh from DB to catch Admin updates)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $subData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $subStatus = $subData['subscription_status'] ?? 'inactive';
    $subExpiry = $subData['subscription_expiry'] ?? null;
    
} catch (PDOException $e) {
    $subStatus = 'inactive';
    $subExpiry = null;
}

// Calculate Status
$daysLeft = 0;
$isNearExpiry = false;
$isExpired = false;

if ($subStatus === 'active') {
    if ($subExpiry) {
        $expiryTimestamp = strtotime($subExpiry);
        $currentTimestamp = time();
        
        if ($expiryTimestamp < $currentTimestamp) {
            $isExpired = true;
            $subStatus = 'expired'; // Override status for UI
        } else {
            $diff = $expiryTimestamp - $currentTimestamp;
            $daysLeft = ceil($diff / (60 * 60 * 24));
            if ($daysLeft <= 7) {
                $isNearExpiry = true;
            }
        }
    }
} else {
    // If status is explicitly inactive or expired in DB
    if ($subStatus === 'expired') $isExpired = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dealer.css">
</head>
<body>

<?php include __DIR__ . '/sidebar.php'; ?>

<div class="dealer-main">
    <div class="dealer-topbar shadow-sm bg-white d-flex justify-content-between align-items-center px-4 py-3 mb-4">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-dark d-md-none me-3 p-0" id="sidebarToggle">
                <i class="bi bi-list fs-2"></i>
            </button>
            <h4 class="mb-0 fw-bold text-dark">Dashboard</h4>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            
<?php
// ... (Previous session checks)

// Fetch Notifications (Admin Messages)
$adminNotifications = [];
try {
    // Re-use $pdo if available, or create new
    if (!isset($pdo)) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    // Check if table exists first to avoid fatal errors if SQL wasn't run
    $tableExists = $pdo->query("SHOW TABLES LIKE 'notifications'")->rowCount() > 0;
    
    if ($tableExists) {
        $stmtNotif = $pdo->prepare("SELECT n.* FROM notifications n 
                                   LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
                                   WHERE n.is_active = 1 
                                   AND (n.target_role = 'all' OR n.target_role = 'dealer') 
                                   AND nr.id IS NULL
                                   ORDER BY n.created_at DESC LIMIT 5");
        $stmtNotif->execute([$_SESSION['user_id']]);
        $adminNotifications = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    // Silently fail if table doesn't exist yet
    $adminNotifications = [];
}

// Calculate Total Alerts
$totalAlerts = count($adminNotifications);
if ($isNearExpiry || $isExpired) $totalAlerts++;
?>
<!-- ... (HTML Head) ... -->

        <!-- Notification Bell -->
        <div class="dropdown">
            <a href="#" class="text-decoration-none position-relative text-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell fs-4"></i>
                <?php if($totalAlerts > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $totalAlerts; ?>
                        <span class="visually-hidden">unread messages</span>
                    </span>
                <?php endif; ?>
            </a>
            
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2 p-0" style="width: 350px; max-height: 450px; overflow-y: auto;">
                <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Notifications</h6>
                    <span class="badge bg-primary rounded-pill"><?php echo $totalAlerts; ?> New</span>
                </div>
                
                <div class="list-group list-group-flush">
                    <!-- Subscription Status (Always Visible) -->
                    <div class="list-group-item border-0 bg-light">
                        <div class="d-flex align-items-center">
                            <?php if($subStatus === 'active' && !$isExpired): ?>
                                <div class="bg-success-subtle text-success rounded-circle p-2 me-3"><i class="bi bi-check-lg"></i></div>
                                <div>
                                    <div class="fw-bold text-success">Active Plan</div>
                                    <?php if($subExpiry): ?>
                                        <div class="small text-muted">Expires in <?php echo $daysLeft; ?> days</div>
                                    <?php else: ?>
                                        <div class="small text-muted">Lifetime Access</div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif($isExpired): ?>
                                <div class="bg-danger-subtle text-danger rounded-circle p-2 me-3"><i class="bi bi-x-lg"></i></div>
                                <div>
                                    <div class="fw-bold text-danger">Plan Expired</div>
                                    <div class="small text-muted">Please renew now.</div>
                                </div>
                            <?php else: ?>
                                <div class="bg-secondary-subtle text-secondary rounded-circle p-2 me-3"><i class="bi bi-pause-fill"></i></div>
                                <div>
                                    <div class="fw-bold text-secondary">Inactive</div>
                                    <div class="small text-muted">No active plan.</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Subscription Alerts -->
                    <?php if ($isExpired): ?>
                        <a href="subscribe.php" class="list-group-item list-group-item-action bg-danger-subtle border-0 mb-1">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <strong class="text-danger"><i class="bi bi-exclamation-circle-fill me-1"></i> Subscription Expired</strong>
                                <small class="text-muted">Now</small>
                            </div>
                            <p class="mb-1 small text-dark">Your subscription has expired. Please renew to restore visibility.</p>
                        </a>
                    <?php elseif ($isNearExpiry): ?>
                        <a href="subscribe.php" class="list-group-item list-group-item-action bg-warning-subtle border-0 mb-1">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <strong class="text-warning-emphasis"><i class="bi bi-clock-history me-1"></i> Expiring Soon</strong>
                                <small class="text-muted"><?php echo $daysLeft; ?> days left</small>
                            </div>
                            <p class="mb-1 small text-dark">Your plan expires in <?php echo $daysLeft; ?> days. Renew now to avoid interruption.</p>
                        </a>
                    <?php endif; ?>

                    <!-- Admin Messages -->
                    <?php if (!empty($adminNotifications)): ?>
                        <?php foreach($adminNotifications as $notif): ?>
                            <?php 
                                $bgClass = match($notif['type']) {
                                    'danger' => 'bg-danger-subtle',
                                    'warning' => 'bg-warning-subtle',
                                    'success' => 'bg-success-subtle',
                                    default => 'bg-light'
                                };
                                $icon = match($notif['type']) {
                                    'danger' => 'bi-exclamation-triangle-fill text-danger',
                                    'warning' => 'bi-exclamation-circle-fill text-warning',
                                    'success' => 'bi-check-circle-fill text-success',
                                    default => 'bi-info-circle-fill text-primary'
                                };
                            ?>
                            <div class="list-group-item list-group-item-action border-0 mb-1 <?php echo $bgClass; ?>">
                                <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                    <strong class="text-dark"><i class="bi <?php echo $icon; ?> me-1"></i> <?php echo htmlspecialchars($notif['title']); ?></strong>
                                    <button onclick="markAsRead(<?php echo $notif['id']; ?>, this)" class="btn btn-sm btn-link text-muted p-0 ms-2" title="Mark as read"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <p class="mb-1 small text-dark"><?php echo htmlspecialchars($notif['message']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" style="font-size: 0.75rem;"><?php echo date('M d', strtotime($notif['created_at'])); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if (empty($adminNotifications) && !$isNearExpiry && !$isExpired): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                            No new notifications
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

            <!-- Profile Dropdown -->
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <?php 
                    $profileImg = $_SESSION['profile_image'] ?? null;
                    if (empty($profileImg) || !file_exists(__DIR__ . '/../../' . $profileImg)) {
                        $headerProfile = 'https://ui-avatars.com/api/?name=' . urlencode($_SESSION['user_name']) . '&background=random&size=128';
                    } else {
                        $headerProfile = '../' . $profileImg;
                    }
                    ?>
                    <img src="<?php echo $headerProfile; ?>" class="rounded-circle me-2 border" width="35" height="35" alt="Dealer" style="object-fit: cover;">
                    <span class="fw-bold text-dark d-none d-md-block"><?php echo $_SESSION['user_name']; ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="subscribe.php"><i class="bi bi-credit-card me-2"></i> Subscription</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                </ul>
            </div>
            
        </div>
    </div>

<script>
    const sidebar = document.querySelector('.dealer-sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.dealer-main');

    if(toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('active') && 
                !sidebar.contains(e.target) && 
                !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });
    }

    // Mark as Read Functionality
    function markAsRead(notificationId, element) {
        fetch('mark_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'notification_id=' + notificationId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the notification item from the list
                element.closest('.list-group-item').remove();
                
                // Update the badge count
                const badge = document.querySelector('.badge.bg-danger');
                if(badge) {
                    let count = parseInt(badge.innerText);
                    count--;
                    if(count > 0) {
                        badge.innerText = count;
                        badge.innerHTML = count + ' <span class="visually-hidden">unread messages</span>';
                    } else {
                        badge.remove();
                    }
                }
                
                // Update the "New" badge inside dropdown
                const headerBadge = document.querySelector('.dropdown-menu .badge.bg-primary');
                if(headerBadge) {
                     let count = parseInt(headerBadge.innerText);
                     count--;
                     headerBadge.innerText = count + ' New';
                }
            }
        });
    }
</script>