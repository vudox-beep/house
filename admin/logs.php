<?php
require_once '../config/config.php';
require_once '../models/User.php';
require_once '../includes/ActivityLogger.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$logger = new ActivityLogger();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filter by User
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if ($user_id) {
    $total_logs = $logger->getTotalLogsByUser($user_id);
    $logs = $logger->getLogsByUser($user_id, $limit, $offset);
} else {
    $total_logs = $logger->getTotalLogs();
    $logs = $logger->getLogs($limit, $offset);
}

$total_pages = ceil($total_logs / $limit);

include 'includes/header.php';
?>

<div class="admin-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-0 fw-bold">Activity Logs</h2>
                    <?php if ($user_id): ?>
                        <div class="text-muted small mt-1">
                            Filtering for User ID: <span class="fw-bold text-dark">#<?php echo $user_id; ?></span>
                            <a href="logs.php" class="text-decoration-none ms-2 badge bg-secondary-subtle text-secondary">Clear Filter</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="text-muted small">
                    Showing <?php echo count($logs); ?> of <?php echo $total_logs; ?> records
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th class="text-end pe-4">Date & Time</th>
                                    <th class="text-end pe-4"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($logs) > 0): ?>
                                    <?php foreach($logs as $log): ?>
                                        <tr>
                                            <td class="ps-4 text-muted small">#<?php echo $log['id']; ?></td>
                                            <td>
                                                <?php if($log['user_id']): ?>
                                                    <div class="fw-medium text-dark"><?php echo htmlspecialchars($log['user_name']); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($log['user_email']); ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted fst-italic">System / Guest</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($log['user_role']): ?>
                                                    <span class="badge bg-light text-dark border text-capitalize">
                                                        <?php echo htmlspecialchars($log['user_role']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-primary">
                                                    <?php echo htmlspecialchars($log['action']); ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted" style="max-width: 300px;">
                                                <?php echo htmlspecialchars($log['description'] ?? '-'); ?>
                                            </td>
                                            <td class="font-monospace small text-muted">
                                                <?php echo htmlspecialchars($log['ip_address']); ?>
                                            </td>
                                            <td class="text-end pe-4 text-muted small">
                                                <div><?php echo date('M d, Y', strtotime($log['created_at'])); ?></div>
                                                <div><?php echo date('H:i:s', strtotime($log['created_at'])); ?></div>
                                            </td>
                                            <td class="text-end pe-4">
                                                <?php if($log['user_id']): ?>
                                                    <a href="logs.php?user_id=<?php echo $log['user_id']; ?>" class="btn btn-sm btn-light border" title="View User History">
                                                        <i class="bi bi-clock-history"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-1 d-block mb-2 opacity-50"></i>
                                            No activity logs found.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="card-footer bg-white border-top-0 py-3">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>