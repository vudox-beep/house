<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in and redirecting them away to prevent redirect loops
if (isset($_SESSION['user_id'])) {
    $redirect = $_GET['redirect'] ?? '';
    if (!empty($redirect)) {
        // Attempt to decode base64 redirect
        $decoded_redirect = base64_decode($redirect, true);
        // Ensure it decoded to something meaningful (like containing '.php') before trusting it as base64
        if ($decoded_redirect !== false && strpos($decoded_redirect, '.php') !== false) {
            $redirect = $decoded_redirect;
        } else {
            $redirect = urldecode($redirect);
        }
        
        // Basic sanitization but allow ? and = for query params
        $redirect = preg_replace('/[^a-zA-Z0-9_\-\.\?\=\&]/', '', $redirect);
        
        // Prevent redirect loops back to login
        if (strpos($redirect, 'login.php') !== false) {
            $redirect = 'index.php';
        }
        
        // Final sanity check for overly long or malformed redirects
        if (strlen($redirect) > 500) {
            $redirect = 'index.php';
        }
        
        header("Location: " . $redirect);
    } else {
        if ($_SESSION['user_role'] == 'dealer') {
            header("Location: dealer/dashboard.php");
        } elseif ($_SESSION['user_role'] == 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: tenant/dashboard.php");
        }
    }
    exit;
}

require_once 'config/config.php';
require_once 'models/User.php';
require_once 'includes/ActivityLogger.php';

$error = '';

// Session-based Math CAPTCHA
if (!isset($_SESSION['login_captcha_num1'])) {
    $_SESSION['login_captcha_num1'] = rand(1, 9);
    $_SESSION['login_captcha_num2'] = rand(1, 9);
    $_SESSION['login_captcha_answer'] = $_SESSION['login_captcha_num1'] + $_SESSION['login_captcha_num2'];
}

// Session-based timing
if (!isset($_SESSION['login_start_time'])) {
    $_SESSION['login_start_time'] = time();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Session expired. Please refresh.";
    } else {
        // Honeypots
        if (!empty($_POST['company_name_optional']) || !empty($_POST['secondary_email'])) {
            $error = "Invalid submission detected.";
        }
        // Math CAPTCHA
        elseif (!isset($_POST['math_captcha']) || (int)$_POST['math_captcha'] !== $_SESSION['login_captcha_answer']) {
            $error = "Incorrect math answer. Are you a bot?";
        }
        // Submission speed check (min 2s)
        elseif (time() - $_SESSION['login_start_time'] < 2) {
            $error = "You are submitting too fast. Please try again.";
        }
        
        // Brute Force Protection (Simple Rate Limiting)
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();
        }

        // Reset attempts if 15 minutes passed
        if (time() - $_SESSION['last_attempt_time'] > 900) {
            $_SESSION['login_attempts'] = 0;
            $_SESSION['last_attempt_time'] = time();
        }

        if ($_SESSION['login_attempts'] >= 5) {
            $error = "Too many failed login attempts. Please try again in 15 minutes.";
        } else {
            $user = new User();
            $logger = new ActivityLogger();
            $email = sanitize_input($_POST['email']);
            $password = $_POST['password'];

            $loggedInUser = $user->login($email, $password);

            if ($loggedInUser === "unverified") {
                $error = "Please verify your email address before logging in.";
            } elseif ($loggedInUser === "banned") {
                $error = "Your account has been banned. Please contact support.";
            } elseif ($loggedInUser) {
                // Reset attempts on success
                $_SESSION['login_attempts'] = 0;
                
                $_SESSION['user_id'] = $loggedInUser['id'];
                $_SESSION['user_role'] = $loggedInUser['role'];
                $_SESSION['user_name'] = $loggedInUser['name'];
                $_SESSION['profile_image'] = $loggedInUser['profile_image']; // Set profile image in session

                // Log Login
                $logger->log($loggedInUser['id'], $loggedInUser['role'], 'login', 'User logged in successfully');

                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);

                // Handle custom redirect if passed
                $redirect = $_GET['redirect'] ?? '';
                if (!empty($redirect)) {
                    // Attempt to decode base64 redirect
                    $decoded_redirect = base64_decode($redirect, true);
                    if ($decoded_redirect !== false && strpos($decoded_redirect, '.php') !== false) {
                        $redirect = $decoded_redirect;
                    } else {
                        $redirect = urldecode($redirect);
                    }
                    
                    // Basic sanitization to ensure it's a local path
                    $redirect = preg_replace('/[^a-zA-Z0-9_\-\.\?\=\&]/', '', $redirect);
                    
                    if (strpos($redirect, 'login.php') === false && strlen($redirect) < 500) {
                        header("Location: " . $redirect);
                        exit;
                    }
                }

                if ($loggedInUser['role'] == 'dealer') {
                    header("Location: dealer/dashboard.php");
                } elseif ($loggedInUser['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif ($loggedInUser['role'] == 'user') {
                    // Redirect Tenants/Users to Tenant Dashboard
                    header("Location: tenant/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $error = "Invalid credentials. Please try again.";
            }
        }
    }

    // Regenerate CAPTCHA & Timer for next attempt after a POST
    $_SESSION['login_captcha_num1'] = rand(1, 9);
    $_SESSION['login_captcha_num2'] = rand(1, 9);
    $_SESSION['login_captcha_answer'] = $_SESSION['login_captcha_num1'] + $_SESSION['login_captcha_num2'];
    $_SESSION['login_start_time'] = time();
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
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php"><i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <li class="nav-item"><a class="nav-link active" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="auth-split-wrapper">
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
                        
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                <div><?php echo htmlspecialchars($_GET['error']); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_GET['registered'])): ?>
                            <div class="alert alert-success">Registration successful! Please login.</div>
                        <?php endif; ?>

                        <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . htmlspecialchars($_GET['redirect']) : ''; ?>">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <!-- Honeypots -->
                            <input type="text" name="company_name_optional" value="" style="display:none !important;" tabindex="-1" autocomplete="off">
                            <div style="position: absolute; left: -9999px;" aria-hidden="true">
                                <input type="text" name="secondary_email" tabindex="-1" autocomplete="off">
                            </div>
                            
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

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Security Check: What is <?php echo $_SESSION['login_captcha_num1']; ?> + <?php echo $_SESSION['login_captcha_num2']; ?>?</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
                                    <input type="number" class="form-control border-start-0 ps-0" name="math_captcha" placeholder="Answer" required>
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
                                <div class="col-12">
                                    <a href="auth/google_login.php?action=login" class="social-btn text-decoration-none">
                                        <i class="bi bi-google text-danger"></i> Google
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
