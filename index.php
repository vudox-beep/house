<?php
require_once 'config/config.php';
require_once 'models/Property.php';

$propertyModel = new Property();
$properties = $propertyModel->getAll();

// Handle Search
if ($_SERVER['REQUEST_METHOD'] == 'GET' && (isset($_GET['location']) || isset($_GET['property_type']))) {
    $filters = [
        'location' => $_GET['location'] ?? '',
        'property_type' => $_GET['property_type'] ?? '',
        'min_price' => $_GET['min_price'] ?? '',
        'max_price' => $_GET['max_price'] ?? ''
    ];
    $properties = $propertyModel->search($filters);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Your Dream Home - <?php echo SITE_NAME; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house-heart-fill"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link active text-dark fw-semibold" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="#">About</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="#">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_role'] == 'dealer'): ?>
                            <li class="nav-item"><a class="nav-link text-muted" href="dealer/dashboard.php">Dashboard</a></li>
                        <?php elseif($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-muted" href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li class="nav-item dropdown ms-2">
                            <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm">
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-3"><a class="nav-link fw-semibold text-dark" href="login.php">Login</a></li>
                        <li class="nav-item ms-2"><a class="btn btn-primary text-dark fw-bold" href="register.php">Post a Property</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-luxe">
        <div class="hero-content">
            <h1 class="hero-title">Your Private Oasis Awaits</h1>
            <p class="hero-subtitle">Discover hand-picked premium estates for your next unforgettable escape.</p>
            
            <div class="search-bar-rounded">
                <form action="listings.php" method="GET" class="d-flex w-100 flex-column flex-md-row">
                    <div class="search-input-group">
                        <i class="bi bi-geo-alt"></i>
                        <input type="text" class="search-input" name="location" placeholder="City or Country">
                    </div>
                    <div class="search-input-group">
                        <i class="bi bi-tag"></i>
                        <select class="search-input bg-transparent" name="property_type" style="cursor:pointer;">
                            <option value="">Any Type</option>
                            <option value="apartment">Apartment</option>
                            <option value="house">House</option>
                            <option value="villa">Villa</option>
                        </select>
                    </div>
                    <div class="search-input-group">
                        <i class="bi bi-wallet2"></i>
                        <input type="number" class="search-input" name="max_price" placeholder="Max Budget">
                    </div>
                    <button type="submit" class="btn-search-rounded">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Category Navigation -->
    <section class="category-section">
        <div class="container">
            <div class="d-flex justify-content-center gap-4 flex-wrap">
                <a href="index.php?property_type=apartment" class="text-decoration-none">
                    <div class="category-icon-item <?php echo ($_GET['property_type'] ?? '') == 'apartment' ? 'active' : ''; ?>">
                        <i class="bi bi-building category-icon-img"></i>
                        <span class="category-name">Apartments</span>
                    </div>
                </a>
                <a href="index.php?property_type=villa" class="text-decoration-none">
                    <div class="category-icon-item <?php echo ($_GET['property_type'] ?? '') == 'villa' ? 'active' : ''; ?>">
                        <i class="bi bi-house-door category-icon-img"></i>
                        <span class="category-name">Villas</span>
                    </div>
                </a>
                <a href="index.php?property_type=cottage" class="text-decoration-none">
                    <div class="category-icon-item <?php echo ($_GET['property_type'] ?? '') == 'cottage' ? 'active' : ''; ?>">
                        <i class="bi bi-tree category-icon-img"></i>
                        <span class="category-name">Cottages</span>
                    </div>
                </a>
                <a href="index.php?property_type=studio" class="text-decoration-none">
                    <div class="category-icon-item <?php echo ($_GET['property_type'] ?? '') == 'studio' ? 'active' : ''; ?>">
                        <i class="bi bi-easel category-icon-img"></i>
                        <span class="category-name">Studios</span>
                    </div>
                </a>
                <a href="index.php?property_type=manor" class="text-decoration-none">
                    <div class="category-icon-item <?php echo ($_GET['property_type'] ?? '') == 'manor' ? 'active' : ''; ?>">
                        <i class="bi bi-bank category-icon-img"></i>
                        <span class="category-name">Manors</span>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Listings -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h3 class="section-title">Featured Listings</h3>
                    <p class="section-subtitle mb-0">Hand-picked homes with exceptional reviews and amenities.</p>
                </div>
                <a href="index.php?featured=1" class="text-warning fw-bold text-decoration-none">View all <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="row g-4">
                <?php 
                // Fetch featured properties
                $featuredProps = $propertyModel->getFeatured(3); 
                if(count($featuredProps) > 0):
                    foreach($featuredProps as $property):
                        $main_image = 'https://placehold.co/600x400?text=No+Image';
                        $images = $propertyModel->getImages($property['id']);
                        if(count($images) > 0) $main_image = $images[0]['image_path'];
                ?>
                <div class="col-md-4">
                    <div class="featured-card">
                        <div class="featured-img-wrapper">
                            <a href="property_details.php?id=<?php echo $property['id']; ?>">
                                <img src="<?php echo $main_image; ?>" class="featured-img" alt="<?php echo htmlspecialchars($property['title']); ?>">
                            </a>
                            <span class="position-absolute top-0 start-0 badge bg-white text-dark m-3 shadow-sm">
                                <?php echo (($property['listing_purpose'] ?? 'rent') === 'sale') ? 'Selling' : 'Rent'; ?>
                            </span>
                            <span class="position-absolute top-0 end-0 badge bg-success-subtle text-success m-3 shadow-sm">
                                <i class="bi bi-check-circle-fill"></i> Verified
                            </span>
                            <span class="featured-badge"><i class="bi bi-star-fill text-warning"></i> 4.9</span>
                            <span class="featured-price-badge">
                                <?php echo $property['currency'] . ' ' . number_format($property['price']); ?>
                                <?php echo (($property['listing_purpose'] ?? 'rent') === 'rent') ? ' / mo' : ''; ?>
                            </span>
                        </div>
                        <div class="featured-body">
                            <div class="featured-title"><?php echo htmlspecialchars($property['title']); ?></div>
                            <div class="featured-location"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></div>
                            <div class="featured-amenities">
                                <span><i class="bi bi-people"></i> <?php echo $property['bedrooms']; ?> Guests</span>
                                <span><i class="bi bi-layout-sidebar"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                <span><i class="bi bi-droplet"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                    <div class="col-12 text-center text-muted">No featured properties available yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="mb-1">Latest Listings</h2>
                    <p class="text-muted mb-0">Freshly added properties for you</p>
                </div>
                <a href="listings.php" class="text-decoration-none fw-bold">View All <i class="bi bi-arrow-right"></i></a>
            </div>
            
            <?php if(count($properties) > 0): ?>
                <div class="row g-4">
                    <?php foreach($properties as $property): ?>
                        <?php 
                            $main_image = 'https://placehold.co/600x400?text=No+Image';
                            $images = $propertyModel->getImages($property['id']);
                            if(count($images) > 0) {
                                foreach($images as $img) {
                                    if($img['is_main']) {
                                        $main_image = $img['image_path'];
                                        break;
                                    }
                                    $main_image = $images[0]['image_path'];
                                }
                            }
                        ?>
                        <div class="col-md-6 col-lg-4 d-flex">
                            <div class="card property-card border-0 shadow-sm w-100">
                                <div class="position-relative">
                                    <a href="property_details.php?id=<?php echo $property['id']; ?>">
                                        <img src="<?php echo $main_image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                    </a>
                                    <span class="position-absolute top-0 end-0 badge bg-white text-dark m-3 shadow-sm">
                                        <?php echo ucfirst($property['property_type']); ?> · <?php echo ($property['listing_purpose'] ?? 'rent') == 'sale' ? 'Sale' : 'Rent'; ?>
                                    </span>
                                    <span class="position-absolute bottom-0 start-0 badge bg-primary m-3 shadow-sm">
                                        <?php echo $property['currency'] . ' ' . number_format($property['price']); ?>
                                        <?php echo ($property['listing_purpose'] ?? 'rent') == 'rent' ? '/ mo' : ''; ?>
                                    </span>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted"><i class="bi bi-clock"></i> <?php echo date('M d, Y', strtotime($property['created_at'])); ?></small>
                                        <small class="text-success fw-bold"><i class="bi bi-check-circle-fill"></i> Verified</small>
                                    </div>
                                    <h5 class="card-title mb-2">
                                        <a href="property_details.php?id=<?php echo $property['id']; ?>" class="text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted small mb-3">
                                        <i class="bi bi-geo-alt-fill text-primary"></i> <?php echo htmlspecialchars($property['location']); ?>
                                    </p>
                                    <div class="property-features border-top pt-3">
                                        <span><i class="bi bi-people-fill"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                        <span><i class="bi bi-droplet-fill"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                                        <span><i class="bi bi-aspect-ratio-fill"></i> <?php echo $property['size_sqm']; ?> m²</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                    <img src="assets/images/no-results.svg" style="max-width: 150px; opacity: 0.5;" alt="No Results">
                    <h4 class="text-muted mt-3">No properties found</h4>
                    <p class="text-muted">Try adjusting your search criteria</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5 bg-primary text-center">
        <div class="container">
            <h2 class="mb-3 fw-bold text-dark">Are you a Property Owner?</h2>
            <p class="lead mb-4 text-dark">Join thousands of dealers and agents growing their business with us.</p>
            <a href="register.php" class="btn btn-light btn-lg px-5 fw-bold text-primary shadow-sm">Get Started Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5 class="text-primary mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted small">The leading real estate platform in Africa & Europe. Connecting tenants with trusted dealers seamlessly.</p>
                    <div class="mb-3">
                        <p class="small text-white mb-1"><i class="bi bi-envelope-fill text-primary me-2"></i> chisalaluckyk5@gmail.com</p>
                    </div>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-white hover-warning"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white hover-warning"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white hover-warning"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white hover-warning"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white mb-3">Quick Links</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="index.php" class="text-decoration-none text-white hover-warning">Home</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">About Us</a></li>
                        <li class="mb-2"><a href="listings.php" class="text-decoration-none text-white hover-warning">Properties</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">Contact</a></li>
                    </ul>
                </div>
                <div class="col-md-2">
                    <h6 class="text-white mb-3">Support</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">Terms of Service</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-white hover-warning">Privacy Policy</a></li>
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
                <div>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</div>
                <div>Builder: <span class="text-white">Lucky Chisala</span></div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
