<?php
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
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
                    <li class="nav-item"><a class="nav-link" href="pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link active" href="contact.php">Contact</a></li>
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
            <h1 class="fw-bold display-4">Contact Us</h1>
            <p class="lead">We'd love to hear from you. Get in touch today!</p>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row g-5">
            <div class="col-lg-5">
                <h3 class="fw-bold mb-4">Get in Touch</h3>
                <p class="text-muted mb-4">
                    Have a question about a property, need help listing your home, or just want to say hello? 
                    Our team is here to assist you.
                </p>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Our Location</h6>
                        <p class="text-muted mb-0">Lusaka, Zambia</p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-envelope-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Email Us</h6>
                        <p class="text-muted mb-0"><a href="mailto:chisalaluckyk5@gmail.com" class="text-decoration-none text-muted">chisalaluckyk5@gmail.com</a></p>
                    </div>
                </div>

                <div class="d-flex align-items-start mb-4">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-telephone-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1">Call Us</h6>
                        <p class="text-muted mb-0"><a href="tel:0772125121" class="text-decoration-none text-muted">0772125121</a></p>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-5">
                    <a href="#" class="btn btn-outline-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="btn btn-outline-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="btn btn-outline-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="btn btn-outline-primary rounded-circle p-2" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm p-4">
                    <h4 class="fw-bold mb-4">Send us a Message</h4>
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-bold small">Your Name</label>
                                <input type="text" class="form-control" id="name" placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold small">Your Email</label>
                                <input type="email" class="form-control" id="email" placeholder="john@example.com" required>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label fw-bold small">Subject</label>
                                <input type="text" class="form-control" id="subject" placeholder="Inquiry about..." required>
                            </div>
                            <div class="col-12">
                                <label for="message" class="form-label fw-bold small">Message</label>
                                <textarea class="form-control" id="message" rows="5" placeholder="How can we help you?" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section (Optional) -->
    <div class="container-fluid p-0 mt-5">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3846.5074212570386!2d28.2833!3d-15.4167!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1940f367e9140407%3A0x644265538054045!2sLusaka%2C%20Zambia!5e0!3m2!1sen!2szm!4v1625123456789!5m2!1sen!2szm" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-primary mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted small">The leading real estate platform in Africa & Europe. Connecting tenants with trusted dealers seamlessly.</p>
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
