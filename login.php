<?php
require_once 'config/config.php';
require_once 'models/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please refresh.";
    } else {
        $user = new User();
        $email = sanitize_input($_POST['email']);
        $password = $_POST['password'];

        $loggedInUser = $user->login($email, $password);

        if ($loggedInUser === "unverified") {
            $error = "Please verify your email address before logging in.";
        } elseif ($loggedInUser === "banned") {
            $error = "Your account has been banned. Please contact support.";
        } elseif ($loggedInUser) {
            $_SESSION['user_id'] = $loggedInUser['id'];
            $_SESSION['user_role'] = $loggedInUser['role'];
            $_SESSION['user_name'] = $loggedInUser['name'];
            $_SESSION['profile_image'] = $loggedInUser['profile_image']; // Set profile image in session

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            if ($loggedInUser['role'] == 'dealer') {
                header("Location: dealer/dashboard.php");
            } elseif ($loggedInUser['role'] == 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid credentials. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

    <div class="auth-split-wrapper">
        <header class="auth-header">
            <a href="index.php" class="auth-logo">
                <i class="bi bi-house-heart-fill fs-3"></i> <?php echo SITE_NAME; ?>
            </a>
            <a href="index.php" class="text-decoration-none text-muted fw-medium small d-none d-md-block">Back to Home</a>
        </header>

        <div class="row g-0 h-100">
            
            <!-- Left Side: Marketing -->
            <div class="col-lg-6">
                <div class="auth-left h-100">
                    <h1>Welcome back to <br><span class="text-highlight"><?php echo SITE_NAME; ?></span></h1>
                    <p class="lead text-muted mb-5">Log in to manage your listings, save your favorite properties, and connect with trusted dealers.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-speedometer2"></i>
                            </div>
                            <div class="feature-text">
                                <h6>Manage your dashboard</h6>
                                <p>Track views, leads, and manage your property portfolio efficiently.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-chat-dots-fill"></i>
                            </div>
                            <div class="feature-text">
                                <h6>Connect with clients</h6>
                                <p>Respond to inquiries instantly via WhatsApp or direct call.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="col-lg-6">
                <div class="auth-right h-100">
                    <div class="auth-form-container">
                        <h3 class="mb-2">Log in to your account</h3>
                        <p class="text-muted mb-4">Welcome back! Please enter your details.</p>

                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_GET['registered'])): ?>
                            <div class="alert alert-success">Registration successful! Please login.</div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" name="email" placeholder="john@example.com" required>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label fw-bold small">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control border-start-0 ps-0" name="password" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                                </div>
                                <a href="forgot_password.php" class="small text-warning text-decoration-none fw-semibold">Forgot password?</a>
                            </div>

                            <button type="submit" class="btn btn-auth-yellow mb-4">Log In</button>

                            <div class="text-center position-relative mb-4">
                                <hr class="text-muted opacity-25">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">Or log in with</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="auth/google_login.php" class="social-btn text-decoration-none">
                                        <i class="bi bi-google"></i> Google
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="#" class="social-btn text-decoration-none">
                                        <i class="bi bi-apple"></i> Apple
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <p class="small text-muted">Don't have an account? <a href="register.php" class="fw-bold text-dark text-decoration-none">Sign up</a></p>
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
