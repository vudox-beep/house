<?php
require_once '../config/config.php';
require_once '../models/User.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get Total Users Over Time (Mocked for now as we might not have timestamps for daily tracking)
    $monthly_users = [
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        'data' => [10, 20, 30, 40, 50, 60] // Placeholder
    ];

    // Get Property Types Distribution
    $stmt = $pdo->query("SELECT property_type, COUNT(*) as count FROM properties GROUP BY property_type");
    $property_types = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $prop_labels = [];
    $prop_data = [];
    foreach ($property_types as $type) {
        $prop_labels[] = ucfirst($type['property_type']);
        $prop_data[] = $type['count'];
    }

    // Handle Report Action (Dismiss/Delete)
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $report_id = $_GET['id'];
        if ($_GET['action'] == 'dismiss') {
            $stmt = $pdo->prepare("UPDATE property_reports SET status = 'dismissed' WHERE id = ?");
            $stmt->execute([$report_id]);
        } elseif ($_GET['action'] == 'delete') {
            $stmt = $pdo->prepare("DELETE FROM property_reports WHERE id = ?");
            $stmt->execute([$report_id]);
        }
        header("Location: reports.php");
        exit;
    }

    // Fetch Recent Reports
    $stmt = $pdo->query("SELECT r.*, p.title as property_title, u.name as reporter_name 
                         FROM property_reports r 
                         LEFT JOIN properties p ON r.property_id = p.id 
                         LEFT JOIN users u ON r.user_id = u.id 
                         ORDER BY r.created_at DESC");
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold text-dark">Analytics & Reports</h4>
            <div class="d-flex align-items-center">
                <div class="me-3 text-end">
                    <p class="mb-0 fw-bold text-dark"><?php echo $_SESSION['user_name']; ?></p>
                    <small class="text-muted">Administrator</small>
                </div>
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <!-- User Growth Chart -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">User Growth</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Property Types Chart -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="fw-bold mb-0">Property Types</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="propertyTypesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Reports Table -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Property Reports</h5>
                <span class="badge bg-danger"><?php echo count($reports); ?> Reports</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Property</th>
                                <th>Reporter</th>
                                <th>Reason</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reports) > 0): ?>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <a href="../property_details.php?id=<?php echo $report['property_id']; ?>" target="_blank" class="fw-bold text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($report['property_title'] ?? 'Unknown Property'); ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($report['reporter_name'] ?? 'Guest'); ?>
                                        </td>
                                        <td><span class="badge bg-warning text-dark"><?php echo ucfirst($report['reason']); ?></span></td>
                                        <td class="text-muted small" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($report['details']); ?>">
                                            <?php echo htmlspecialchars($report['details']); ?>
                                        </td>
                                        <td>
                                            <?php if($report['status'] == 'pending'): ?>
                                                <span class="badge bg-danger">Pending</span>
                                            <?php elseif($report['status'] == 'dismissed'): ?>
                                                <span class="badge bg-secondary">Dismissed</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Reviewed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted small"><?php echo date('M d, Y', strtotime($report['created_at'])); ?></td>
                                        <td>
                                            <?php if($report['status'] != 'dismissed'): ?>
                                                <a href="reports.php?action=dismiss&id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-secondary me-1" title="Dismiss">
                                                    <i class="bi bi-x-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="reports.php?action=delete&id=<?php echo $report['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <h5 class="fw-bold text-muted">No Reports Found</h5>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Growth Chart
        const ctxUser = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(ctxUser, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthly_users['labels']); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode($monthly_users['data']); ?>,
                    borderColor: '#fbbf24',
                    backgroundColor: 'rgba(251, 191, 36, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                }
            }
        });

        // Property Types Chart
        const ctxProp = document.getElementById('propertyTypesChart').getContext('2d');
        new Chart(ctxProp, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($prop_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($prop_data); ?>,
                    backgroundColor: [
                        '#fbbf24', '#f59e0b', '#d97706', '#b45309', '#78350f', '#fffbeb'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>