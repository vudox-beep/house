<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .glass-header {
            background: rgba(251, 191, 36, 0.85); /* Yellow with opacity */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            color: #1f2937;
        }
    </style>
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
                    <li class="nav-item"><a class="nav-link active" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="services.php">Services</a></li>
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
    <header class="glass-header py-5 text-center">
        <div class="container">
            <h1 class="fw-bold display-4">About Us</h1>
            <p class="lead">Building Trust in Real Estate Across Africa</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="img-fluid rounded-3 shadow" alt="About Us">
            </div>
            <div class="col-md-6 mt-4 mt-md-0">
                <h2 class="fw-bold mb-3">Who We Are</h2>
                <p class="text-muted">
                    <strong><?php echo SITE_NAME; ?></strong> is a premier real estate platform dedicated to connecting tenants with trusted dealers and property owners. 
                    Our mission is to simplify the property search process, making it transparent, secure, and efficient for everyone involved.
                </p>
                <p class="text-muted">
                    Founded with a vision to bridge the gap in the housing market, we provide a robust marketplace for rentals, sales, and property management services.
                </p>
                <div class="mt-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3"><i class="bi bi-check-lg"></i></div>
                        <div><strong>Verified Listings</strong><br><small class="text-muted">Every property is vetted for authenticity.</small></div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3"><i class="bi bi-shield-lock"></i></div>
                        <div><strong>Secure Transactions</strong><br><small class="text-muted">Safe and transparent payment processes.</small></div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3"><i class="bi bi-people"></i></div>
                        <div><strong>Community Driven</strong><br><small class="text-muted">Supporting both tenants and landlords.</small></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-white p-4">
                    <h3 class="fw-bold mb-3">Platform Nature & Services</h3>
                    <p class="text-muted">
                        We want to be clear about our role. <strong><?php echo SITE_NAME; ?></strong> is a property listing and management platform. 
                        While we provide tools for property management—such as rent collection, tenant screening, and maintenance tracking—we are not the owners of the properties listed (unless explicitly stated). 
                        We act as a bridge, facilitating smooth interactions between property owners/dealers and tenants.
                    </p>
                    <p class="text-muted mb-0">
                        Our goal is to empower property owners with professional management tools while giving tenants a reliable place to find their next home.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center py-5">
            <h2 class="fw-bold">Ready to find your dream home?</h2>
            <p class="text-muted mb-4">Browse our exclusive listings today.</p>
            <a href="listings.php" class="btn btn-primary btn-lg px-5">Explore Properties</a>
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
                        <li class="mb-2"><a href="listings.php" class="text-decoration-none text-white hover-warning">Properties</a></li>
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
