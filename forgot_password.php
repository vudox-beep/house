<?php
require_once 'config/config.php';
require_once 'models/User.php';
require_once 'includes/SimpleSMTP.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please refresh.";
    } else {
        $email = sanitize_input($_POST['email']);
        $user = new User();

        if ($user->emailExists($email)) {
            // Generate Token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store Token in DB
            if ($user->setResetToken($email, $token, $expiry)) {
                // Send Email
                $resetLink = SITE_URL . "/reset_password.php?token=" . $token;
                
                // Fallback for localhost dev if SMTP fails or not configured
                $dev_link_msg = "";
                if (strpos(SITE_URL, 'localhost') !== false) {
                    $dev_link_msg = "<br><br><b>Dev Mode:</b> <a href='$resetLink'>Click here to reset (Localhost)</a>";
                }

                $subject = "Reset Your Password - " . SITE_NAME;
                $body = "Hi,<br><br>We received a request to reset your password. Click the link below to create a new password:<br><br><a href='$resetLink' style='background:#fbbf24; color:#1f2937; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Reset Password</a><br><br>This link expires in 1 hour.<br><br>If you didn't ask for this, please ignore this email.$dev_link_msg";

                $smtp = new SimpleSMTP(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS);
                if ($smtp->send($email, $subject, $body, SITE_NAME)) {
                    $success = "Password reset link sent to your email.";
                } else {
                    $error = "Failed to send email. " . ($dev_link_msg ? "Check below." : "Please try again later.");
                    if ($dev_link_msg) $success = $dev_link_msg; // Show link for dev
                }
            } else {
                $error = "Something went wrong. Please try again.";
            }
        } else {
            // Security: Don't reveal if email exists or not, but for UX we might say "If that email exists..."
            // For now, let's just say sent to avoid enumeration if desired, OR be helpful.
            // User requested "make sure it works", so being helpful is better for now.
            $error = "Email not found in our records.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                    <h1>Recover your <br><span class="text-highlight">Account Access.</span></h1>
                    <p class="lead text-muted mb-5">Don't worry, it happens to the best of us. We'll help you get back in.</p>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="col-lg-6">
                <div class="auth-right h-100 d-flex align-items-center justify-content-center">
                    <div class="auth-form-container w-100" style="max-width: 400px;">
                        <h3 class="mb-2 fw-bold">Forgot Password?</h3>
                        <p class="text-muted mb-4">Enter your email to receive a reset link.</p>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" name="email" placeholder="john@example.com" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-auth-yellow w-100 mb-4">Send Reset Link</button>

                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> Back to Login</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
