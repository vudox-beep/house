<?php
require_once 'config/config.php';
require_once 'models/Property.php';

$propertyModel = new Property();

// Get Search Parameters
$filters = [
    'location' => $_GET['location'] ?? '',
    'city' => $_GET['city'] ?? '',
    'country' => $_GET['country'] ?? '',
    'property_type' => $_GET['property_type'] ?? '',
    'listing_purpose' => $_GET['listing_purpose'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'bedrooms' => $_GET['bedrooms'] ?? '',
    'featured' => $_GET['featured'] ?? '',
    'latitude' => $_GET['latitude'] ?? '',
    'longitude' => $_GET['longitude'] ?? ''
];

$properties = $propertyModel->search($filters);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Listings - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/listings.css">
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

    <div class="container py-5">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar sticky-top" style="top: 100px;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0">Filters</h5>
                        <a href="listings.php" class="text-decoration-none small text-muted">Clear All</a>
                    </div>
                    
                    <form action="listings.php" method="GET">
                        <!-- Location Search (Generic) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Search Location</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="location" id="locationSearch" placeholder="City, Country, or Area..." value="<?php echo htmlspecialchars($filters['location']); ?>">
                                <button class="btn btn-outline-secondary" type="button" id="btnNearMe" title="Use my location">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </button>
                            </div>
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                        </div>

                        <!-- Specific City Filter -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">City</label>
                            <input type="text" class="form-control" name="city" placeholder="e.g. Lusaka" value="<?php echo htmlspecialchars($filters['city']); ?>">
                        </div>

                        <!-- Country Filter -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Country</label>
                            <select class="form-select" name="country">
                                <option value="">All Countries</option>
                                <option value="Zambia" <?php echo ($filters['country'] == 'Zambia') ? 'selected' : ''; ?>>Zambia</option>
                                <option value="South Africa" <?php echo ($filters['country'] == 'South Africa') ? 'selected' : ''; ?>>South Africa</option>
                                <option value="Nigeria" <?php echo ($filters['country'] == 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                                <option value="Kenya" <?php echo ($filters['country'] == 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                                <option value="United Kingdom" <?php echo ($filters['country'] == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                <option value="United States" <?php echo ($filters['country'] == 'United States') ? 'selected' : ''; ?>>United States</option>
                            </select>
                        </div>

                        <!-- Listing Purpose -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Purpose</label>
                            <select class="form-select" name="listing_purpose">
                                <option value="">Any Purpose</option>
                                <option value="rent" <?php echo ($filters['listing_purpose'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                                <option value="sale" <?php echo ($filters['listing_purpose'] == 'sale') ? 'selected' : ''; ?>>For Sale</option>
                                <option value="booking" <?php echo ($filters['listing_purpose'] == 'booking') ? 'selected' : ''; ?>>For Booking</option>
                                <option value="service" <?php echo ($filters['listing_purpose'] == 'service') ? 'selected' : ''; ?>>Service</option>
                            </select>
                        </div>

                        <!-- Property Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Category</label>
                            <select class="form-select" name="property_type">
                                <option value="">All Categories</option>
                                <?php 
                                $types = ['apartment', 'house', 'villa', 'cottage', 'studio', 'flat', 'boarding_house', 'manor', 'wedding_venue', 'restaurant', 'lodge', 'commercial'];
                                foreach($types as $type): 
                                ?>
                                    <option value="<?php echo $type; ?>" <?php echo ($filters['property_type'] == $type) ? 'selected' : ''; ?>>
                                        <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase">Price Range</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Bedrooms -->
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-uppercase">Bedrooms</label>
                            <select class="form-select" name="bedrooms">
                                <option value="">Any</option>
                                <option value="1" <?php echo ($filters['bedrooms'] == '1') ? 'selected' : ''; ?>>1+</option>
                                <option value="2" <?php echo ($filters['bedrooms'] == '2') ? 'selected' : ''; ?>>2+</option>
                                <option value="3" <?php echo ($filters['bedrooms'] == '3') ? 'selected' : ''; ?>>3+</option>
                                <option value="4" <?php echo ($filters['bedrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </form>
                </div>
            </div>

            <!-- Results Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0 fw-bold"><?php echo count($properties); ?> Properties Found</h4>
                    <div class="dropdown">
                        <button class="btn btn-white border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Sort by: Newest
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Newest First</a></li>
                            <li><a class="dropdown-item" href="#">Price: Low to High</a></li>
                            <li><a class="dropdown-item" href="#">Price: High to Low</a></li>
                        </ul>
                    </div>
                </div>

                <?php if(count($properties) > 0): ?>
                    <div class="row g-4">
                        <?php foreach($properties as $property): ?>
                            <?php 
                                $main_image = 'https://placehold.co/600x400?text=No+Image';
                                $images = $propertyModel->getImages($property['id']);
                                if(count($images) > 0) $main_image = $images[0]['image_path'];
                            ?>
                            <div class="col-md-6">
                                <div class="card listing-card h-100 border-0 shadow-sm">
                                    <div class="listing-img-wrapper">
                                        <a href="property_details.php?id=<?php echo $property['id']; ?>">
                                            <img src="<?php echo $main_image; ?>" class="listing-img" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                        </a>
                                        <span class="badge bg-white text-dark listing-badge position-absolute top-0 end-0 m-3 fw-bold">
                                            <?php echo ucfirst(str_replace('_', ' ', $property['property_type'])); ?> · 
                                            <?php 
                                                $purpose = $property['listing_purpose'] ?? 'rent';
                                                if ($purpose == 'booking') echo 'Booking';
                                                elseif ($purpose == 'service') echo 'Service';
                                                elseif ($purpose == 'sale') echo 'Sale';
                                                else echo 'Rent';
                                            ?>
                                        </span>
                                        <?php if($property['is_featured']): ?>
                                            <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-3 fw-bold">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-price fs-5">
                                                <?php 
                                                    if(in_array($property['property_type'], ['wedding_venue', 'commercial', 'studio'])) {
                                                        echo 'Booking Price: ' . $property['currency'] . ' ' . number_format($property['price']);
                                                    } elseif ($property['property_type'] == 'restaurant') {
                                                        echo 'Service Price: ' . $property['currency'] . ' ' . number_format($property['price']);
                                                    } else {
                                                        echo $property['currency'] . ' ' . number_format($property['price']);
                                                    }
                                                ?>
                                            </span>
                                            <small class="text-muted">
                                                <?php 
                                                    if (!in_array($property['property_type'], ['wedding_venue', 'restaurant', 'commercial', 'studio'])) {
                                                        if (($property['listing_purpose'] ?? 'rent') == 'rent') {
                                                            if ($property['property_type'] == 'boarding_house') {
                                                                echo '/ person';
                                                            } elseif ($property['property_type'] == 'lodge') {
                                                                echo '/ night';
                                                            } else {
                                                                echo '/ month';
                                                            }
                                                        }
                                                    }
                                                ?>
                                            </small>
                                        </div>
                                        <h5 class="card-title mb-1">
                                            <a href="property_details.php?id=<?php echo $property['id']; ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($property['title']); ?>
                                            </a>
                                        </h5>
                                        <p class="text-muted small mb-3"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                                        
                                        <div class="listing-features d-flex gap-3 pt-3 border-top">
                                            <span class="small text-muted"><i class="bi bi-people-fill"></i> <?php echo $property['bedrooms']; ?> Beds</span>
                                            <span class="small text-muted"><i class="bi bi-droplet-fill"></i> <?php echo $property['bathrooms']; ?> Baths</span>
                                            <span class="small text-muted"><i class="bi bi-aspect-ratio-fill"></i> <?php echo $property['size_sqm']; ?> m²</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                        <div class="mb-3"><i class="bi bi-search fs-1 text-muted opacity-50"></i></div>
                        <h4 class="text-muted">No properties found</h4>
                        <p class="text-muted mb-4">Try adjusting your filters to find what you're looking for.</p>
                        <a href="listings.php" class="btn btn-filter text-white">Clear Filters</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3 mt-5">
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

    <!-- Google Maps JS -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('btnNearMe').addEventListener('click', function() {
            if (navigator.geolocation) {
                // Show loading state
                this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        
                        // Use Google Maps Geocoding to get city name (optional but good for UX)
                        // For now, just submit with lat/long
                        
                        // You might want to update the location input to say "Current Location"
                        document.getElementById('locationSearch').value = "My Location";

                        // Try to get country via reverse geocoding if Google Maps API is available
                        if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                            const geocoder = new google.maps.Geocoder();
                            const latlng = { lat: parseFloat(lat), lng: parseFloat(lng) };
                            
                            geocoder.geocode({ location: latlng }, (results, status) => {
                                if (status === "OK") {
                                    if (results[0]) {
                                        // Find country component
                                        for (let i = 0; i < results[0].address_components.length; i++) {
                                            const component = results[0].address_components[i];
                                            if (component.types.includes("country")) {
                                                const countryName = component.long_name;
                                                // Auto-select country in dropdown if it matches
                                                const countrySelect = document.querySelector('select[name="country"]');
                                                if(countrySelect) {
                                                    for(let j=0; j < countrySelect.options.length; j++) {
                                                        if(countrySelect.options[j].value === countryName) {
                                                            countrySelect.selectedIndex = j;
                                                            break;
                                                        }
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                }
                                // Submit after attempting to set country
                                this.closest('form').submit();
                            });
                        } else {
                            // Submit immediately if no Google Maps API
                            this.closest('form').submit();
                        }
                    },
                    (error) => {
                        console.error("Error getting location:", error);
                        this.innerHTML = '<i class="bi bi-geo-alt-fill text-danger"></i>';
                        alert("Could not get your location. Please check your browser settings.");
                    }
                );
            } else {
                alert("Geolocation is not supported by this browser.");
            }
        });
    </script>
</body>
</html>
