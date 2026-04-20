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

$selectedSection = $_GET['section'] ?? '';
$sectionMap = [
    'boarding_house_rent' => ['property_type' => 'boarding_house', 'listing_purpose' => 'rent'],
    'house_rent' => ['property_type' => 'house', 'listing_purpose' => 'rent'],
    'apartment_rent' => ['property_type' => 'apartment', 'listing_purpose' => 'rent'],
    'land_sale' => ['property_type' => 'land', 'listing_purpose' => 'sale'],
    'all_sale' => ['property_type' => '', 'listing_purpose' => 'sale'],
    'all_booking' => ['property_type' => '', 'listing_purpose' => 'booking'],
    'all_service' => ['property_type' => '', 'listing_purpose' => 'service']
];

if (!empty($selectedSection) && isset($sectionMap[$selectedSection])) {
    $filters['property_type'] = $sectionMap[$selectedSection]['property_type'];
    $filters['listing_purpose'] = $sectionMap[$selectedSection]['listing_purpose'];
}

$properties = $propertyModel->search($filters);

$purposeLabels = [
    'rent' => 'For Rent',
    'sale' => 'For Sale',
    'booking' => 'For Booking',
    'service' => 'Service'
];

$typeLabels = [
    'apartment' => 'Apartment',
    'house' => 'House',
    'villa' => 'Villa',
    'cottage' => 'Cottage',
    'studio' => 'Studio',
    'flat' => 'Flat',
    'boarding_house' => 'Boarding House',
    'manor' => 'Manor',
    'wedding_venue' => 'Wedding Venue',
    'restaurant' => 'Restaurant',
    'lodge' => 'Lodge',
    'commercial' => 'Commercial',
    'land' => 'Land'
];

$groupedProperties = [];
foreach ($properties as $property) {
    $purpose = $property['listing_purpose'] ?? 'rent';
    $type = $property['property_type'] ?? 'property';
    $groupKey = $purpose . '|' . $type;
    $groupTitle = ($purposeLabels[$purpose] ?? ucfirst(str_replace('_', ' ', $purpose))) . ' - ' . ($typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)));
    if (!isset($groupedProperties[$groupKey])) {
        $groupedProperties[$groupKey] = [
            'title' => $groupTitle,
            'items' => []
        ];
    }
    $groupedProperties[$groupKey]['items'][] = $property;
}
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
    <style>
        /* Custom tweaks for the top filter bar */
        .listing-card .card-body {
            padding: 1rem;
        }
        .listing-img-wrapper {
            height: 180px; /* Slightly smaller for 4-in-a-row */
        }
        .listing-card h5 {
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
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

    <div class="container py-4">
        
        <!-- Top Filter Section -->
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="filter-sidebar mb-4">
                    <form action="listings.php" method="GET">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0"><i class="bi bi-sliders me-2"></i>Filters</h5>
                            <a href="listings.php" class="text-decoration-none small text-muted">Clear All</a>
                        </div>
                        
                        <div class="row g-3">
                            <!-- Location -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Location</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" name="location" id="locationSearch" placeholder="City, Area..." value="<?php echo htmlspecialchars($filters['location']); ?>">
                                    <button class="btn btn-outline-secondary" type="button" id="btnNearMe" title="Use my location">
                                        <i class="bi bi-geo-alt-fill"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                            </div>

                            <!-- City -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">City</label>
                                <input type="text" class="form-control form-control-sm" name="city" placeholder="e.g. Lusaka" value="<?php echo htmlspecialchars($filters['city']); ?>">
                            </div>

                            <!-- Country -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">Country</label>
                                <select class="form-select form-select-sm" name="country">
                                    <option value="">All Countries</option>
                                    <option value="Zambia" <?php echo ($filters['country'] == 'Zambia') ? 'selected' : ''; ?>>Zambia</option>
                                    <option value="South Africa" <?php echo ($filters['country'] == 'South Africa') ? 'selected' : ''; ?>>South Africa</option>
                                    <option value="Nigeria" <?php echo ($filters['country'] == 'Nigeria') ? 'selected' : ''; ?>>Nigeria</option>
                                    <option value="Kenya" <?php echo ($filters['country'] == 'Kenya') ? 'selected' : ''; ?>>Kenya</option>
                                    <option value="United Kingdom" <?php echo ($filters['country'] == 'United Kingdom') ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="United States" <?php echo ($filters['country'] == 'United States') ? 'selected' : ''; ?>>United States</option>
                                </select>
                            </div>

                            <!-- Purpose -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">Purpose</label>
                                <select class="form-select form-select-sm" name="listing_purpose">
                                    <option value="">Any Purpose</option>
                                    <option value="rent" <?php echo ($filters['listing_purpose'] == 'rent') ? 'selected' : ''; ?>>For Rent</option>
                                    <option value="sale" <?php echo ($filters['listing_purpose'] == 'sale') ? 'selected' : ''; ?>>For Sale</option>
                                    <option value="booking" <?php echo ($filters['listing_purpose'] == 'booking') ? 'selected' : ''; ?>>For Booking</option>
                                    <option value="service" <?php echo ($filters['listing_purpose'] == 'service') ? 'selected' : ''; ?>>Service</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Section</label>
                                <select class="form-select form-select-sm" name="section">
                                    <option value="">All Sections</option>
                                    <option value="boarding_house_rent" <?php echo ($selectedSection == 'boarding_house_rent') ? 'selected' : ''; ?>>Boarding House Rent</option>
                                    <option value="house_rent" <?php echo ($selectedSection == 'house_rent') ? 'selected' : ''; ?>>House Rent</option>
                                    <option value="apartment_rent" <?php echo ($selectedSection == 'apartment_rent') ? 'selected' : ''; ?>>Apartment Rent</option>
                                    <option value="land_sale" <?php echo ($selectedSection == 'land_sale') ? 'selected' : ''; ?>>Land Sale</option>
                                    <option value="all_sale" <?php echo ($selectedSection == 'all_sale') ? 'selected' : ''; ?>>All Sale Listings</option>
                                    <option value="all_booking" <?php echo ($selectedSection == 'all_booking') ? 'selected' : ''; ?>>All Booking Listings</option>
                                    <option value="all_service" <?php echo ($selectedSection == 'all_service') ? 'selected' : ''; ?>>All Service Listings</option>
                                </select>
                            </div>

                            <!-- Category -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold small text-uppercase">Category</label>
                                <select class="form-select form-select-sm" name="property_type">
                                    <option value="">All Categories</option>
                                    <?php 
                                    $types = ['apartment', 'house', 'villa', 'cottage', 'studio', 'flat', 'boarding_house', 'land', 'manor', 'wedding_venue', 'restaurant', 'lodge', 'commercial'];
                                    foreach($types as $type): 
                                    ?>
                                        <option value="<?php echo $type; ?>" <?php echo ($filters['property_type'] == $type) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Row 2 -->
                            
                            <!-- Price Min -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">Min Price</label>
                                <input type="number" class="form-control form-control-sm" name="min_price" placeholder="Min" value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                            </div>
                            
                            <!-- Price Max -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">Max Price</label>
                                <input type="number" class="form-control form-control-sm" name="max_price" placeholder="Max" value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                            </div>

                            <!-- Bedrooms -->
                            <div class="col-md-2">
                                <label class="form-label fw-bold small text-uppercase">Bedrooms</label>
                                <select class="form-select form-select-sm" name="bedrooms">
                                    <option value="">Any</option>
                                    <option value="1" <?php echo ($filters['bedrooms'] == '1') ? 'selected' : ''; ?>>1+</option>
                                    <option value="2" <?php echo ($filters['bedrooms'] == '2') ? 'selected' : ''; ?>>2+</option>
                                    <option value="3" <?php echo ($filters['bedrooms'] == '3') ? 'selected' : ''; ?>>3+</option>
                                    <option value="4" <?php echo ($filters['bedrooms'] == '4') ? 'selected' : ''; ?>>4+</option>
                                </select>
                            </div>
                            
                            <!-- Search Button -->
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 btn-sm py-2 fw-bold">
                                    <i class="bi bi-search me-1"></i> Update Results
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 fw-bold"><?php echo count($properties); ?> Properties Found</h4>
            <div class="dropdown">
                <button class="btn btn-white border dropdown-toggle btn-sm" type="button" data-bs-toggle="dropdown">
                    Sort by: Newest
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Newest First</a></li>
                    <li><a class="dropdown-item" href="#">Price: Low to High</a></li>
                    <li><a class="dropdown-item" href="#">Price: High to Low</a></li>
                </ul>
            </div>
        </div>

        <!-- Property Grid -->
        <?php if(count($properties) > 0): ?>
            <?php foreach($groupedProperties as $group): ?>
                <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($group['title']); ?></h5>
                    <span class="badge bg-primary-subtle text-primary"><?php echo count($group['items']); ?> listings</span>
                </div>
                <div class="row g-3">
                    <?php foreach($group['items'] as $property): ?>
                        <?php 
                            $main_image = 'https://placehold.co/600x400?text=No+Image';
                            $images = $propertyModel->getImages($property['id']);
                            if(count($images) > 0) $main_image = $images[0]['image_path'];
                        ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card listing-card h-100 border-0 shadow-sm">
                                <div class="listing-img-wrapper position-relative">
                                    <a href="property_details.php?id=<?php echo $property['id']; ?>">
                                        <img src="<?php echo $main_image; ?>" class="listing-img" alt="<?php echo htmlspecialchars($property['title']); ?>">
                                    </a>
                                    <span class="badge bg-white text-dark listing-badge position-absolute top-0 end-0 m-2 fw-bold small" style="font-size: 0.7rem;">
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
                                        <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2 fw-bold small" style="font-size: 0.7rem; z-index: 1;">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-price fw-bold text-primary" style="font-size: 0.95rem;">
                                            <?php echo $property['currency'] . ' ' . number_format($property['price']); ?>
                                        </span>
                                    </div>
                                    <h6 class="card-title mb-1 text-truncate">
                                        <a href="property_details.php?id=<?php echo $property['id']; ?>" class="text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($property['title']); ?>
                                        </a>
                                    </h6>
                                    <p class="text-muted small mb-2 text-truncate"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
                                    
                                    <div class="listing-features d-flex justify-content-between pt-2 border-top align-items-center">
                                        <span class="small text-muted" style="font-size: 0.7rem;" title="Bedrooms">
                                            <i class="bi bi-people-fill text-warning"></i> <?php echo $property['bedrooms']; ?> <span class="d-none d-sm-inline">Beds</span>
                                        </span>
                                        <span class="small text-muted" style="font-size: 0.7rem;" title="Bathrooms">
                                            <i class="bi bi-droplet-fill text-warning"></i> <?php echo $property['bathrooms']; ?> <span class="d-none d-sm-inline">Baths</span>
                                        </span>
                                        <span class="small text-muted" style="font-size: 0.7rem;" title="Size">
                                            <i class="bi bi-aspect-ratio-fill text-warning"></i> <?php echo $property['size_sqm']; ?> m²
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 bg-white rounded-3 shadow-sm">
                <div class="mb-3"><i class="bi bi-search fs-1 text-muted opacity-50"></i></div>
                <h4 class="text-muted">No properties found</h4>
                <p class="text-muted mb-4">Try adjusting your filters to find what you're looking for.</p>
                <a href="listings.php" class="btn btn-filter text-white">Clear Filters</a>
            </div>
        <?php endif; ?>
        
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
                        <p class="small text-white mb-1"><i class="bi bi-telephone-fill text-primary me-2"></i> 0772125121</p>
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
                <div>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. <span class="fw-bold">Owned by <?php echo defined('OWNER_NAME') ? OWNER_NAME : 'Site Owner'; ?>.</span></div>
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
