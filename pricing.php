<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
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
                    <li class="nav-item"><a class="nav-link active" href="pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_role'] == 'dealer'): ?>
                            <li class="nav-item"><a class="nav-link" href="dealer/dashboard.php">Dashboard</a></li>
                        <?php elseif($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Panel</a></li>
                        <?php elseif($_SESSION['user_role'] == 'user'): ?>
                            <li class="nav-item"><a class="nav-link" href="tenant/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header Section -->
    <header class="bg-primary text-white py-5 text-center">
        <div class="container">
            <h1 class="fw-bold display-4">Simple, Transparent Pricing</h1>
            <p class="lead">Choose the plan that fits your business needs.</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        
        <div class="row row-cols-1 row-cols-md-2 mb-3 text-center justify-content-center">
            
            <!-- Basic Plan -->
            <div class="col">
                <div class="card mb-4 rounded-3 shadow-sm border-0 h-100">
                    <div class="card-header py-3 bg-white border-bottom-0">
                        <h4 class="my-0 fw-normal">Basic</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h1 class="card-title pricing-card-title">Free<small class="text-muted fw-light"></small></h1>
                        <ul class="list-unstyled mt-3 mb-4 flex-grow-1 text-start px-4">
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Browse Properties</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Contact Dealers</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Save Favorites</li>
                            <li class="mb-2 text-muted"><i class="bi bi-x-lg me-2"></i>No Property Listings</li>
                            <li class="mb-2 text-muted"><i class="bi bi-x-lg me-2"></i>No Analytics</li>
                        </ul>
                        <a href="register.php?plan=basic" class="w-100 btn btn-lg btn-outline-primary">Sign Up Free</a>
                    </div>
                </div>
            </div>
            
            <!-- Pro Dealer Plan -->
            <div class="col">
                <div class="card mb-4 rounded-3 shadow border-primary h-100 position-relative">
                    <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-warning text-dark px-3 py-2 shadow-sm">
                        RECOMMENDED
                    </span>
                    <div class="card-header py-3 bg-primary text-white border-bottom-0">
                        <h4 class="my-0 fw-normal">Dealer Pro</h4>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h1 class="card-title pricing-card-title">K20<small class="text-muted fw-light">/check</small></h1>
                        <ul class="list-unstyled mt-3 mb-4 flex-grow-1 text-start px-4">
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Unlimited Listings</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Unlimited Views</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Priority Support</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Advanced Analytics</li>
                            <li class="mb-2"><i class="bi bi-check-lg text-success me-2"></i>Featured Listings</li>
                        </ul>
                        <a href="register.php?plan=pro" class="w-100 btn btn-lg btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="text-center py-4">
            <h3 class="fw-bold mb-3">Frequently Asked Questions</h3>
            <div class="row justify-content-center text-start mt-4">
                <div class="col-md-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                    Can I change my plan later?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Yes, you can upgrade or downgrade your plan at any time from your dashboard settings. Changes will take effect at the start of the next billing cycle.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                    Is there a free trial?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    Absolutely! New dealers get a 1-month free trial on the Basic plan to explore our platform's features before committing.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 shadow-sm rounded">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    We accept all major credit cards, debit cards, and mobile money payments (Airtel/MTN Money).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-primary mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted small">The leading real estate platform in Africa. Connecting tenants with trusted dealers seamlessly.</p>
                    <div class="mb-3">
                        <p class="small text-white mb-1"><i class="bi bi-envelope-fill text-primary me-2"></i> chisalaluckyk5@gmail.com</p>
                        <p class="small text-white mb-1"><i class="bi bi-telephone-fill text-primary me-2"></i> 0772125121</p>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none text-white hover-warning">Home</a></li>
                        <li class="mb-2"><a href="about.php" class="text-decoration-none text-white hover-warning">About Us</a></li>
                        <li class="mb-2"><a href="services.php" class="text-decoration-none text-white hover-warning">Services</a></li>
                        <li class="mb-2"><a href="pricing.php" class="text-decoration-none text-white hover-warning">Pricing</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-decoration-none text-white hover-warning">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white mb-3">Support</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">Help Center</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-decoration-none text-white hover-warning">Terms of Service</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-decoration-none text-white hover-warning">Privacy Policy</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">FAQs</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 class="text-white mb-3">Newsletter</h6>
                    <p class="small text-muted">Subscribe to get the latest property news.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Enter your email">
                        <button class="btn btn-primary" type="button">Subscribe</button>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="d-flex justify-content-between align-items-center small text-muted">
                <div>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. <span class="fw-bold">Owned by <?php echo OWNER_NAME; ?>.</span></div>
                <div>Builder: <span class="text-white">Lucky Chisala</span></div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
