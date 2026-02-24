<?php
require_once 'config/config.php';
require_once 'models/User.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please refresh.";
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $token = $_POST['token'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            $user = new User();
            if ($user->resetPassword($token, $password)) {
                $success = "Password reset successfully. You can now <a href='login.php' class='fw-bold text-success'>login</a>.";
            } else {
                $error = "Invalid or expired token.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

    <div class="auth-split-wrapper">
        <!-- Header -->
        <header class="auth-header">
            <a href="index.php" class="auth-logo">
                <i class="bi bi-house-heart-fill fs-3"></i> <?php echo SITE_NAME; ?>
            </a>
            <a href="login.php" class="text-decoration-none text-muted fw-medium small d-none d-md-block">Back to Login</a>
        </header>

        <div class="row g-0 h-100">
            <!-- Left Side -->
            <div class="col-lg-6 d-none d-lg-block">
                <div class="auth-left h-100 d-flex flex-column justify-content-center">
                    <h1>Secure your <br><span class="text-highlight">New Password.</span></h1>
                    <p class="lead text-muted mb-5">Create a strong password to keep your account safe.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="col-lg-6">
                <div class="auth-right h-100 d-flex align-items-center justify-content-center">
                    <div class="auth-form-container w-100" style="max-width: 400px;">
                        <h3 class="mb-2 fw-bold">Reset Password</h3>
                        <p class="text-muted mb-4">Enter your new password below.</p>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php else: ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" name="confirm_password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-auth-yellow w-100 mb-4">Update Password</button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
