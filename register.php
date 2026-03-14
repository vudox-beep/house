<?php
require_once 'config/config.php';
require_once 'models/User.php';
require_once 'includes/SimpleMailer.php';
require_once 'includes/ActivityLogger.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $logger = new ActivityLogger();
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $whatsapp_number = $_POST['whatsapp_number'] ?? '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($user->emailExists($email)) {
        $error = "Email already registered.";
    } else {
        // Generate Verification Token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+3 minutes'));

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'phone' => $phone,
            'whatsapp_number' => $whatsapp_number,
            'verification_token' => $token,
            'token_expiry' => $expiry,
            // Free Trial Logic
            'subscription_status' => (ENABLE_FREE_TRIAL && $role === 'dealer') ? 'active' : 'inactive',
            'subscription_expiry' => (ENABLE_FREE_TRIAL && $role === 'dealer') ? date('Y-m-d H:i:s', strtotime('+' . FREE_TRIAL_DURATION . ' days')) : null
        ];

        if ($user->register($data)) {
            // Log Registration
            // Since we don't have the user ID easily from register() (it returns bool), we'll query for it or just log with null user_id
            // Ideally User::register should return the ID. For now, we'll fetch it by email.
            $conn = (new Database())->connect();
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $newUser = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($newUser) {
                $logger->log($newUser['id'], $role, 'register', "New user registered: $email ($role)");
            }

            // Send Email
            $mailer = new SimpleMailer();
            $verifyLink = SITE_URL . "/verify_email.php?token=" . $token;
            
            $subject = "Verify Your Account - " . SITE_NAME;
            
            // Email Template
            $body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: 'Arial', sans-serif; background-color: #f8f9fa; margin: 0; padding: 0; }
                    .email-container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e9ecef; }
                    .header { background-color: #fbbf24; padding: 30px; text-align: center; }
                    .content { padding: 40px 30px; color: #333333; line-height: 1.6; }
                    .button { display: inline-block; background-color: #fbbf24; color: #000000; font-weight: bold; padding: 16px 36px; text-decoration: none; border-radius: 6px; margin-top: 20px; font-size: 16px; border: 1px solid #e0a800; }
                    .footer { background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #6c757d; border-top: 1px solid #e9ecef; }
                    .link-copy { margin-top: 30px; font-size: 12px; color: #999; word-break: break-all; border-top: 1px solid #eee; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='header'>
                        <h1 style='margin:0; font-size: 24px; color: #000; font-weight: 800; letter-spacing: -0.5px;'>" . SITE_NAME . "</h1>
                    </div>
                    <div class='content'>
                        <h2 style='margin-top:0; color: #1a1a1a;'>Welcome, $name!</h2>
                        <p style='font-size: 16px;'>Thanks for signing up for <strong>" . SITE_NAME . "</strong>. We're excited to help you find your dream property.</p>
                        <p style='font-size: 16px;'>Please verify your email address to activate your account. This link will expire in 3 minutes.</p>
                        <div style='text-align: center; margin: 35px 0;'>
                            <a href='$verifyLink' class='button'>Verify Email Address</a>
                        </div>
                        <p style='color: #666; font-size: 14px;'>If the button above doesn't work, verify using the link below:</p>
                        
                        <div class='link-copy'>
                            $verifyLink
                        </div>
                    </div>
                    <div class='footer'>
                        &copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.<br>
                        If you didn't create an account, you can safely ignore this email.
                    </div>
                </div>
            </body>
            </html>";

            if ($mailer->send($email, $subject, $body)) {
                $success = "Registration successful! Please check your email (including spam folder) to verify your account.";
            } else {
                $success = "Registration successful, but failed to send email.";
            }
        } else {
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - <?php echo SITE_NAME; ?></title>
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
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link active" href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="auth-split-wrapper">
        <div class="row g-0 h-100">
            
            <!-- Left Side: Marketing -->
            <div class="col-lg-6">
                <div class="auth-left h-100">
                    <h1>Find your dream home <br><span class="text-highlight">faster than ever.</span></h1>
                    <p class="lead text-muted mb-5">Join thousands of happy renters who found their perfect living space through <?php echo SITE_NAME; ?>.</p>
                    
                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-heart-fill"></i>
                            </div>
                            <div class="feature-text">
                                <h6>Save your favorite listings</h6>
                                <p>Keep track of the homes you love and compare them easily.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                            <div class="feature-text">
                                <h6>Get early access</h6>
                                <p>Be the first to see new listings that match your criteria.</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="feature-text">
                                <h6>Verified landlords</h6>
                                <p>Every listing is vetted to ensure your safety and security.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form -->
            <div class="col-lg-6">
                <div class="auth-right h-100">
                    <div class="auth-form-container">
                        <h3 class="mb-2">Create your account</h3>
                        
                        <?php if(ENABLE_FREE_TRIAL): ?>
                            <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                                <i class="bi bi-gift-fill fs-4 me-3"></i>
                                <div>
                                    <strong>Special Offer!</strong><br>
                                    Sign up as a Dealer today and get <span class="fw-bold text-decoration-underline">1 Month Free Trial</span>!
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-4">Join our community today.</p>
                        <?php endif; ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if(isset($_GET['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2"></i>
                                <div><?php echo htmlspecialchars($_GET['error']); ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="name" placeholder="John Doe" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0" name="email" placeholder="john@example.com" required>
                                </div>
                            </div>
                            
                            <!-- Additional fields for functionality, though not in simple mockup -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Phone</label>
                                    <input type="text" class="form-control" name="phone" placeholder="097..." required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Account Type</label>
                                    <select class="form-select" name="role">
                                        <option value="user">Tenant</option>
                                        <option value="dealer">Dealer</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Password</label>
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

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label small text-muted" for="terms">
                                    I agree to the <a href="terms.php" class="text-warning text-decoration-none">Terms of Service</a> and <a href="privacy.php" class="text-warning text-decoration-none">Privacy Policy</a>.
                                </label>
                            </div>

                            <button type="submit" class="btn btn-auth-yellow mb-4">Create Account</button>

                            <div class="text-center position-relative mb-4">
                                <hr class="text-muted opacity-25">
                                <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">Or sign up with</span>
                            </div>

                            <div class="row g-2">
                                <div class="col-12">
                                    <a href="auth/google_login.php?action=signup" class="social-btn text-decoration-none">
                                        <i class="bi bi-google text-danger"></i> Google
                                    </a>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <p class="small text-muted">Already have an account? <a href="login.php" class="fw-bold text-dark text-decoration-none">Log in</a></p>
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
