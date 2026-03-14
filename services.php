<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - <?php echo SITE_NAME; ?></title>
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
                    <li class="nav-item"><a class="nav-link active" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="pricing.php">Pricing</a></li>
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
            <h1 class="fw-bold display-4">Our Services</h1>
            <p class="lead">Comprehensive Real Estate Solutions for Everyone</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        
        <div class="row g-4 mb-5">
            <!-- Service 1: Property Listing -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-primary">
                        <i class="bi bi-houses-fill display-4"></i>
                    </div>
                    <h4 class="fw-bold">Property Listings</h4>
                    <p class="text-muted">
                        Showcase your property to thousands of potential tenants and buyers. 
                        We offer high-quality listings with photo galleries, video tours, and detailed descriptions.
                    </p>
                </div>
            </div>

            <!-- Service 2: Tenant Screening -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-success">
                        <i class="bi bi-person-check-fill display-4"></i>
                    </div>
                    <h4 class="fw-bold">Tenant Screening</h4>
                    <p class="text-muted">
                        Find reliable tenants with our rigorous screening process. 
                        We verify identities and ensure you connect with trustworthy individuals.
                    </p>
                </div>
            </div>

            <!-- Service 3: Property Management -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-warning">
                        <i class="bi bi-briefcase-fill display-4"></i>
                    </div>
                    <h4 class="fw-bold">Property Management</h4>
                    <p class="text-muted">
                        Let us handle the day-to-day operations. From rent collection to maintenance requests, 
                        our tools make managing your real estate portfolio effortless.
                    </p>
                </div>
            </div>

            <!-- Service 4: Listing Categories -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-danger">
                        <i class="bi bi-tags-fill display-4"></i>
                    </div>
                    <h4 class="fw-bold">Diverse Categories</h4>
                    <p class="text-muted">
                        We offer a wide range of property categories to suit every need, including Apartments, Houses, Boarding Houses, Land, Villas, and Commercial spaces.
                    </p>
                </div>
            </div>

            <!-- Service 5: Marketing & Promotion -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-info">
                        <i class="bi bi-megaphone-fill display-4"></i>
                    </div>
                    <h4 class="fw-bold">Premium Marketing</h4>
                    <p class="text-muted">
                        Boost your property's visibility with our featured listings and social media promotion packages to rent or sell faster.
                    </p>
                </div>
            </div>

            <!-- Service 6: 24/7 Support -->
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-up text-center p-4">
                    <div class="mb-3 text-secondary">
                        <i class="bi bi-headset display-4"></i>
                    </div>
                    <h4 class="fw-bold">24/7 Support</h4>
                    <p class="text-muted">
                        Our dedicated support team is always available to assist you with any inquiries, technical issues, or property concerns.
                    </p>
                </div>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="bg-white rounded-3 p-5 shadow-sm text-center">
            <h2 class="fw-bold mb-3">Ready to get started?</h2>
            <p class="text-muted mb-4">Join our platform today and experience the difference.</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="register.php" class="btn btn-primary btn-lg px-4">Register as Dealer</a>
                <a href="contact.php" class="btn btn-outline-primary btn-lg px-4">Contact Sales</a>
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
