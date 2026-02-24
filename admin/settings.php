<?php
require_once '../config/config.php';
require_once '../models/User.php';

// Auth Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success_msg = '';
$error_msg = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle Form Submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $enable_free_trial = isset($_POST['enable_free_trial']) ? '1' : '0';
        $free_trial_duration = $_POST['free_trial_duration'] ?? '30';

        // Update settings
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (:key, :val) ON DUPLICATE KEY UPDATE setting_value = :val");
        
        $stmt->execute([':key' => 'enable_free_trial', ':val' => $enable_free_trial]);
        $stmt->execute([':key' => 'free_trial_duration', ':val' => $free_trial_duration]);

        $success_msg = "Settings updated successfully.";
    }

    // Fetch current settings
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $current_enable = isset($settings['enable_free_trial']) ? (bool)$settings['enable_free_trial'] : true;
    $current_duration = isset($settings['free_trial_duration']) ? (int)$settings['free_trial_duration'] : 30;

} catch (PDOException $e) {
    $error_msg = "DB Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="admin-main">
        <!-- Topbar -->
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h4 class="mb-0 fw-bold text-dark">System Settings</h4>
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

        <?php if ($success_msg): ?>
            <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if ($error_msg): ?>
            <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Settings Form -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Subscription Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_free_trial" name="enable_free_trial" <?php echo $current_enable ? 'checked' : ''; ?>>
                        <label class="form-check-label fw-bold" for="enable_free_trial">Enable Free Trial for New Dealers</label>
                        <div class="form-text">If enabled, new dealers will automatically get a free subscription period upon registration.</div>
                    </div>

                    <div class="mb-4">
                        <label for="free_trial_duration" class="form-label fw-bold">Free Trial Duration (Days)</label>
                        <input type="number" class="form-control" id="free_trial_duration" name="free_trial_duration" value="<?php echo $current_duration; ?>" min="1" required>
                        <div class="form-text">How many days the free trial should last (e.g., 7 for one week, 30 for one month).</div>
                    </div>

                    <hr>

                    <button type="submit" class="btn btn-primary px-4">Save Settings</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
