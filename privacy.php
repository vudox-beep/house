<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="terms.php">Terms of Service</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <h1 class="mb-4 fw-bold">Privacy Policy</h1>
                <p class="text-muted mb-5">Last Updated: <?php echo date('F d, Y'); ?></p>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">1. Information We Collect</h3>
                    <p>We collect information you provide directly to us, such as when you create an account, update your profile, post a listing, or communicate with other users. This may include your name, email address, phone number, and payment information.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">2. How We Use Your Information</h3>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services.</li>
                        <li>Process transactions and send related information.</li>
                        <li>Verify your identity and prevent fraud.</li>
                        <li>Send you technical notices, updates, security alerts, and support messages.</li>
                    </ul>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">3. Fake Listings & Fraud Prevention</h3>
                    <p class="text-danger fw-bold">Strict Policy Against Fake Listings:</p>
                    <p>We have a zero-tolerance policy for fake listings. Any user found posting false information, misleading photos, or non-existent properties will be immediately banned.</p>
                    <p><strong>Consequences:</strong></p>
                    <ul>
                        <li>Immediate account suspension or permanent ban.</li>
                        <li>Reporting of fraudulent activities to relevant local authorities.</li>
                        <li>Forfeiture of any subscription fees paid.</li>
                    </ul>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">4. Data Security</h3>
                    <p>We implement reasonable security measures to help protect your personal information. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>
                </div>

                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h3 class="h5 fw-bold mb-3">5. Changes to This Policy</h3>
                    <p>We may change this Privacy Policy from time to time. If we make changes, we will notify you by revising the date at the top of the policy and, in some cases, provide you with additional notice.</p>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i> By using our services, you agree to the collection and use of information in accordance with this policy.
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>