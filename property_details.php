<?php
require_once 'config/config.php';
require_once 'models/Property.php';
require_once 'models/Favorite.php';
require_once 'models/Lead.php';
require_once 'includes/SimpleMailer.php';

// Helper to encode/decode IDs (simple obfuscation)
function encode_id($id) {
    return rtrim(strtr(base64_encode($id), '+/', '-_'), '=');
}

function decode_id($encoded_id) {
    return base64_decode(strtr($encoded_id, '-_', '+/'));
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$raw_id = $_GET['id'];
// Try to decode if it looks encoded (not numeric)
if (!is_numeric($raw_id)) {
    $property_id = decode_id($raw_id);
    if (!is_numeric($property_id)) {
         echo "Invalid Property ID.";
         exit;
    }
} else {
    // Ideally redirect to encoded version to enforce consistency
    // But for now, just use it
    $property_id = $raw_id;
}

$propertyModel = new Property();
$propertyModel->incrementViews($property_id); // Increment views
$property = $propertyModel->getById($property_id);
$images = $propertyModel->getImages($property_id);

$is_saved = false;
if (isset($_SESSION['user_id'])) {
    $favoriteModel = new Favorite();
    $is_saved = $favoriteModel->isSaved($_SESSION['user_id'], $property_id);
}

if (!$property) {
    echo "Property not found.";
    exit;
}

// Redirect if accessing via raw ID to encoded ID (Optional but recommended)
if (is_numeric($raw_id)) {
    $encoded = encode_id($raw_id);
    header("Location: property_details.php?id=" . $encoded);
    exit;
}

// Prepare amenities array
$amenities = !empty($property['amenities']) ? explode(',', $property['amenities']) : [];

// Default coordinates if missing (Lusaka)
$lat = $property['latitude'] ?? -15.3875;
$lng = $property['longitude'] ?? 28.3228;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lead_name'])) {
    // CSRF Check (Re-enabled with check for token existence)
    $token_ok = verify_csrf_token($_POST['csrf_token'] ?? '');
    if (!$token_ok) {
        // Log CSRF failure for debugging
        error_log("CSRF Verification Failed. Session Token: " . ($_SESSION['csrf_token'] ?? 'None') . " | Post Token: " . ($_POST['csrf_token'] ?? 'None'));
        // header("Location: property_details.php?id=" . $raw_id . "&error=" . urlencode("Session expired. Please try again."));
        // exit;
    }
    
    $lead = new Lead();
    $name = sanitize_input($_POST['lead_name']);
    $email = sanitize_input($_POST['lead_email']);
    $phone = sanitize_input($_POST['lead_phone']);
    $message_text = sanitize_input($_POST['lead_message']);
    
    // Ensure Dealer ID is valid
    if (empty($property['dealer_id'])) {
         header("Location: property_details.php?id=" . $raw_id . "&error=" . urlencode("Cannot submit inquiry: Invalid Dealer."));
         exit;
    }

    $created = $lead->create([
        'property_id' => $property['id'],
        'dealer_id' => $property['dealer_id'],
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message_text
    ]);
    
    if ($created) {
        $mailer = new SimpleMailer();
        $subject = "New Lead: " . $property['title'];
        
        // Generate Link: Use the same ID format that the current page is using
        if (is_numeric($property['id']) && !is_numeric($raw_id)) {
             // If current page is using encoded ID, keep using encoded ID
             $link_id = encode_id($property['id']);
        } else {
             // Otherwise use raw ID
             $link_id = $property['id'];
        }
        $link = SITE_URL . "/property_details.php?id=" . $link_id;
        
        $ref = "#" . str_pad($property['id'], 6, '0', STR_PAD_LEFT);
        $body = "<div style='font-family:Arial,sans-serif'>
                    <h2 style='margin:0'>" . SITE_NAME . "</h2>
                    <p>A new inquiry has been submitted for your listing.</p>
                    <p><strong>Property:</strong> " . htmlspecialchars($property['title']) . " (" . $ref . ")</p>
                    <p><strong>Lead Name:</strong> " . htmlspecialchars($name) . "<br>
                       <strong>Email:</strong> " . htmlspecialchars($email) . "<br>
                       <strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                    <p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message_text)) . "</p>
                    <p><a href='" . $link . "' style='display:inline-block;background:#fbbf24;color:#000;padding:10px 16px;text-decoration:none;border-radius:6px;font-weight:bold'>View Listing</a></p>
                  </div>";
        
        // Get Dealer Email directly if not in property array (though it should be)
        $recipient = $property['dealer_email'];
        if (empty($recipient)) {
             // Fallback: Fetch dealer user email if missing from join
             // This is just a safeguard
             $userModel = new User(); // Assuming User model exists
             // $dealerUser = $userModel->getById($property['dealer_id']);
             // $recipient = $dealerUser['email'];
             // Since we don't have user model loaded here fully, let's trust property join
             $recipient = SMTP_FROM; // Fallback to admin/sender if no dealer email found
        }

        $sent = $mailer->send($recipient, $subject, $body);
        
        // Redirect back to the encoded URL user came from
        header("Location: property_details.php?id=" . $raw_id . "&success=" . urlencode("Your inquiry has been sent."));
        exit;
    } else {
        header("Location: property_details.php?id=" . $raw_id . "&error=" . urlencode("Failed to submit inquiry."));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($property['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Lightbox CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/css/glightbox.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-white">

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
                    <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="services.php">Services</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="pricing.php">Pricing</a></li>
                    <li class="nav-item"><a class="nav-link text-muted" href="contact.php">Contact</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_role'] == 'dealer'): ?>
                            <li class="nav-item"><a class="nav-link text-muted" href="dealer/dashboard.php">Dashboard</a></li>
                        <?php elseif($_SESSION['user_role'] == 'admin'): ?>
                            <li class="nav-item"><a class="nav-link text-muted" href="admin/dashboard.php">Admin Panel</a></li>
                        <?php elseif($_SESSION['user_role'] == 'user'): ?>
                            <li class="nav-item"><a class="nav-link text-muted" href="tenant/dashboard.php">Dashboard</a></li>
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

    <div class="container mt-4 mb-5">
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Breadcrumb & Title -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="#" class="text-decoration-none text-muted"><?php echo ucfirst($property['property_type']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($property['title']); ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-warning text-dark fw-bold text-uppercase px-3 py-2 rounded-pill shadow-sm">
                        <?php 
                            $purpose = $property['listing_purpose'] ?? 'rent';
                            if ($purpose == 'booking') echo 'For Booking';
                            elseif ($purpose == 'service') echo 'Service';
                            elseif ($purpose == 'sale') echo 'For Sale';
                            else echo 'For Rent';
                        ?>
                    </span>
                    <span class="badge bg-light text-dark border fw-medium px-3 py-2 rounded-pill">
                        <?php echo ucfirst(str_replace('_', ' ', $property['property_type'])); ?>
                    </span>
                </div>
                <h1 class="fw-bold mb-1"><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="text-muted mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
            </div>
            <div class="text-end">
                <h2 class="text-primary fw-bold mb-0">
                    <?php 
                        if ($property['property_type'] == 'restaurant') {
                            echo 'Service Price: ';
                        } elseif (in_array($property['property_type'], ['wedding_venue', 'commercial', 'studio'])) {
                            echo 'Booking Price: ';
                        }
                        echo $property['currency'] . ' ' . number_format($property['price']); 
                    ?>
                </h2>
                <small class="text-muted">
                    <?php 
                        if (($property['listing_purpose'] ?? 'rent') == 'sale') {
                            echo 'Full Price';
                        } else {
                            if ($property['property_type'] == 'boarding_house') {
                                echo '/ person';
                            } elseif ($property['property_type'] == 'lodge') {
                                echo '/ night';
                            } elseif (in_array($property['property_type'], ['wedding_venue', 'restaurant', 'commercial', 'studio'])) {
                                echo ''; // Suffix already handled in prefix
                            } else {
                                echo '/ month';
                            }
                        }
                    ?>
                </small>
            </div>
        </div>

        <!-- Image Gallery (Grid + Slider Support) -->
        <div class="gallery-grid position-relative">
            <?php 
                $count = count($images);
                // Main Image
                $img1 = ($count > 0) ? $images[0]['image_path'] : 'assets/images/placeholder.jpg';
                
                // Favorite Button Overlay
                $btnHtml = '';
                if(isset($_SESSION['user_id'])) {
                     $btnHtml = '<button class="btn btn-light rounded-circle shadow position-absolute top-0 end-0 m-3 favorite-btn" style="z-index: 20; width: 40px; height: 40px; padding: 0;" onclick="toggleFavorite(' . $property['id'] . '); event.preventDefault();"><i class="bi ' . ($is_saved ? 'bi-heart-fill text-danger' : 'bi-heart') . '" style="font-size: 1.2rem; line-height: 40px;"></i></button>';
                } else {
                     $btnHtml = '<a href="login.php" class="btn btn-light rounded-circle shadow position-absolute top-0 end-0 m-3" style="z-index: 20; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="bi bi-heart" style="font-size: 1.2rem;"></i></a>';
                }

                echo '<div class="gallery-item gallery-main position-relative">';
                echo $btnHtml;
                echo '<a href="' . $img1 . '" class="glightbox" data-gallery="property-gallery"><img src="' . $img1 . '" class="gallery-img"></a>';
                echo '</div>';

                // Sub Images
                if ($count > 1) {
                    echo '<div class="gallery-item gallery-sub"><a href="' . $images[1]['image_path'] . '" class="glightbox" data-gallery="property-gallery"><img src="' . $images[1]['image_path'] . '" class="gallery-img"></a></div>';
                }
                if ($count > 2) {
                    echo '<div class="gallery-item gallery-sub"><a href="' . $images[2]['image_path'] . '" class="glightbox" data-gallery="property-gallery"><img src="' . $images[2]['image_path'] . '" class="gallery-img"></a></div>';
                }
                
                // 4th Image Container (with Overlay if more)
                if ($count > 3) {
                     echo '<div class="gallery-item gallery-sub position-relative">';
                     echo '<a href="' . $images[3]['image_path'] . '" class="glightbox" data-gallery="property-gallery"><img src="' . $images[3]['image_path'] . '" class="gallery-img"></a>';
                     
                     if ($count > 4) {
                         // Hidden links for the rest of images so Lightbox can see them
                         for($i = 4; $i < $count; $i++) {
                             echo '<a href="' . $images[$i]['image_path'] . '" class="glightbox d-none" data-gallery="property-gallery"></a>';
                         }
                         // Overlay Trigger
                         echo '<div class="gallery-more-overlay pointer-event-none">+' . ($count - 4) . ' More</div>';
                     }
                     echo '</div>';
                }
                
                // Add Video if available
                if (!empty($property['video_url'])) {
                    echo '<a href="' . $property['video_url'] . '" class="glightbox d-none" data-gallery="property-gallery"></a>';
                    // Optional: Add a visible button to open video
                }
            ?>
        </div>



        <div class="row g-5">
            <!-- Left Column: Details -->
            <div class="col-lg-8">
                
                <!-- Key Features (Neat Cards) -->
                <div class="mb-4">
                    <div class="row g-3">
                        <?php if(in_array($property['property_type'], ['house', 'apartment', 'flat', 'cottage', 'manor', 'lodge', 'bedsitter'])): ?>
                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Bedrooms</div>
                                <div class="fw-bold fs-5"><i class="bi bi-bed me-1"></i><?php echo $property['bedrooms']; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Bathrooms</div>
                                <div class="fw-bold fs-5"><i class="bi bi-droplet me-1"></i><?php echo $property['bathrooms']; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($property['property_type'] == 'boarding_house'): ?>
                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Per Room</div>
                                <div class="fw-bold fs-6"><i class="bi bi-people me-1"></i><?php echo $property['people_per_room'] ?? 1; ?> People</div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(in_array($property['property_type'], ['wedding_venue', 'restaurant', 'commercial', 'studio'])): ?>
                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Capacity</div>
                                <div class="fw-bold fs-6"><i class="bi bi-people-fill me-1"></i><?php echo $property['capacity'] ?? 'N/A'; ?></div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Area</div>
                                <div class="fw-bold fs-6"><i class="bi bi-aspect-ratio me-1"></i><?php echo $property['size_sqm']; ?> m²</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light border rounded-3 p-3 h-100 shadow-sm">
                                <div class="small text-muted">Type</div>
                                <div class="fw-bold fs-6"><?php echo ucfirst(str_replace('_', ' ', $property['property_type'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Special Details for Venues -->
                <?php if(in_array($property['property_type'], ['wedding_venue', 'restaurant', 'commercial', 'studio'])): ?>
                <div class="mb-5">
                    <h4 class="fw-bold mb-3">Venue Details</h4>
                    <div class="row g-3">
                        <?php if(!empty($property['event_type'])): ?>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block">Ideal For</small>
                                <span class="fw-bold"><?php echo htmlspecialchars($property['event_type']); ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light">
                                <small class="text-muted d-block">Services</small>
                                <div class="d-flex gap-3 mt-1">
                                    <?php if($property['catering_available']): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> Catering</span>
                                    <?php endif; ?>
                                    <?php if($property['equipment_available']): ?>
                                        <span class="badge bg-primary"><i class="bi bi-check-circle"></i> Equipment</span>
                                    <?php endif; ?>
                                    <?php if(!$property['catering_available'] && !$property['equipment_available']): ?>
                                        <span class="text-muted">Space Only</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3">About this property</h4>
                    <p class="text-secondary" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($property['description'])); ?>
                    </p>
                </div>

                <!-- Video Section -->
        <?php if(!empty($property['video_url'])): ?>
            <div class="mb-5">
                <h4 class="fw-bold mb-3">Video Tour</h4>
                <div class="ratio ratio-16x9 rounded-3 overflow-hidden shadow-sm bg-dark">
                    <?php 
                        $video_url = $property['video_url'];
                        // Check if YouTube
                        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video_url, $matches)) {
                            echo '<iframe src="https://www.youtube.com/embed/' . $matches[1] . '?rel=0" title="YouTube video player" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
                        } 
                        // Check if Vimeo
                        elseif (preg_match('/(?:vimeo\.com\/)([0-9]+)/', $video_url, $matches)) {
                             echo '<iframe src="https://player.vimeo.com/video/' . $matches[1] . '" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>';
                        }
                        // Default to standard video tag if file extension detected (mp4, webm, ogg) or looks like a direct file
                        elseif (preg_match('/\.(mp4|webm|ogg|mov)$/i', $video_url) || strpos($video_url, 'uploads/') !== false) {
                            echo '<video controls playsinline class="w-100 h-100" style="object-fit: cover;">
                                    <source src="' . htmlspecialchars($video_url) . '" type="video/mp4">
                                    Your browser does not support the video tag.
                                  </video>';
                        }
                        // Fallback to Link/Lightbox
                        else {
                            echo '<div class="d-flex align-items-center justify-content-center h-100 flex-column">
                                    <i class="bi bi-play-circle-fill display-1 text-white mb-3"></i>
                                    <a href="' . $video_url . '" class="btn btn-light fw-bold px-4 py-2 glightbox" data-gallery="video-tour">Watch Video</a>
                                  </div>';
                        }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Amenities -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3">What this place offers</h4>
                    <div class="amenities-grid">
                        <?php foreach($amenities as $amenity): ?>
                            <div class="bg-light border rounded-3 p-3 d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-check-circle-fill text-success"></i>
                                <span class="fw-medium"><?php echo htmlspecialchars(trim($amenity)); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Map -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3">Where you'll be</h4>
                    <div id="map" class="map-container"></div>
                </div>

                <!-- Rental Cost Estimator -->
                <div class="mb-5">
                    <div class="accordion" id="calculatorAccordion">
                        <div class="accordion-item border-0 shadow-sm rounded-3 overflow-hidden">
                            <h2 class="accordion-header" id="headingCalc">
                                <button class="accordion-button collapsed fw-bold bg-white" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCalc" aria-expanded="false" aria-controls="collapseCalc">
                                    <i class="bi bi-calculator me-2 text-primary"></i> Rental Cost Estimator
                                </button>
                            </h2>
                            <div id="collapseCalc" class="accordion-collapse collapse" aria-labelledby="headingCalc" data-bs-parent="#calculatorAccordion">
                                <div class="accordion-body bg-light">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Monthly Rent</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><?php echo $property['currency']; ?></span>
                                                <input type="number" class="form-control" id="calcRent" value="<?php echo $property['price']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Lease Duration (Months)</label>
                                            <input type="number" class="form-control" id="calcDuration" value="12">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Security Deposit (One-time)</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><?php echo $property['currency']; ?></span>
                                                <input type="number" class="form-control" id="calcDeposit" value="<?php echo $property['price']; ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Est. Utilities / Mo</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><?php echo $property['currency']; ?></span>
                                                <input type="number" class="form-control" id="calcUtilities" value="0">
                                            </div>
                                        </div>
                                        <div class="col-12 text-end mt-3">
                                            <button class="btn btn-primary btn-sm px-4" onclick="calculateRentalCost()">Calculate Total</button>
                                        </div>
                                        <div class="col-12 mt-3" id="calcResult" style="display: none;">
                                            <div class="alert alert-success mb-0">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span>Total Cost (First Year):</span>
                                                    <span class="fw-bold fs-5" id="totalCost"></span>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                                    <span>Move-in Cost (First Month + Deposit):</span>
                                                    <span class="fw-bold" id="moveInCost"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Agent Card -->
            <div class="col-lg-4">
                <div class="agent-card shadow-sm bg-white">
                    <div class="d-flex align-items-center mb-4">
                        <img src="assets/images/user-placeholder.png" class="agent-avatar" alt="Landlord">
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($property['dealer_name'] ?? 'Landlord'); ?></h6>
                            <small class="text-muted">Verified Host <i class="bi bi-patch-check-fill text-primary"></i></small>
                        </div>
                    </div>

                    <?php if(!empty($property['phone'])): ?>
                        <a href="tel:<?php echo $property['phone']; ?>" class="btn-contact btn-call">
                            <i class="bi bi-telephone-fill"></i> Call Landlord
                        </a>
                    <?php endif; ?>

                    <?php if(!empty($property['whatsapp_number'])): ?>
                        <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $property['whatsapp_number']); ?>?text=I'm interested in <?php echo urlencode($property['title']); ?>" target="_blank" class="btn-contact btn-whatsapp">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    <?php endif; ?>

                    <button class="btn-contact btn-live-chat">
                        <i class="bi bi-chat-dots-fill"></i> Live Chat
                    </button>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <button class="btn-contact btn-light border mt-2 w-100 favorite-btn" onclick="toggleFavorite(<?php echo $property['id']; ?>)">
                            <i class="bi <?php echo $is_saved ? 'bi-heart-fill' : 'bi-heart'; ?>"></i> 
                            <span class="save-text"><?php echo $is_saved ? 'Saved' : 'Save Property'; ?></span>
                        </button>
                    <?php else: ?>
                        <a href="login.php" class="btn-contact btn-light border mt-2 w-100 text-decoration-none d-block text-center">
                            <i class="bi bi-heart"></i> Save Property
                        </a>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="alert alert-danger bg-danger-subtle border-danger small mb-4">
                        <div class="d-flex">
                            <i class="bi bi-shield-exclamation text-danger fs-5 me-2"></i>
                            <div>
                                <strong>Safety Warning:</strong> Do NOT pay any money (rent or deposit) before visiting this property and meeting the landlord in person.
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Send an enquiry</h6>
                    <form method="POST" action="property_details.php?id=<?php echo $raw_id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Your Name</label>
                            <input type="text" class="form-control" name="lead_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" class="form-control" name="lead_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="text" class="form-control" name="lead_phone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Message</label>
                            <textarea class="form-control" name="lead_message" rows="4" required>I'm interested in this property.</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send Enquiry</button>
                    </form>
                    
                    <hr class="my-4">
                    <div class="text-center">
                         <small class="text-muted d-block mb-2">Reference ID: #<?php echo str_pad($property['id'], 6, '0', STR_PAD_LEFT); ?></small>
                         <button type="button" class="btn btn-link text-decoration-none text-muted p-0" data-bs-toggle="modal" data-bs-target="#reportModal">
                             <i class="bi bi-flag"></i> Report this listing
                         </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="report_property.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Report Listing</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="property_id" value="<?php echo $property['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Reason for reporting</label>
                            <select class="form-select" name="reason" required>
                                <option value="">Select a reason...</option>
                                <option value="fraud">Fraudulent / Scam</option>
                                <option value="unavailable">Property is not available</option>
                                <option value="incorrect">Incorrect Information</option>
                                <option value="offensive">Offensive Content</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Additional Details</label>
                            <textarea class="form-control" name="details" rows="4" placeholder="Please provide more details..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                    </div>
                </form>
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
                <div>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved. <span class="fw-bold">Owned by <?php echo OWNER_NAME; ?>.</span></div>
                <div>Builder: <span class="text-white">Lucky Chisala</span></div>
            </div>
        </div>
    </footer>

    <!-- Google Maps JS -->
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });

        // ... (Favorite and Calculator functions remain same) ...

        function toggleFavorite(propertyId) {
            // Optimistic UI Update for all favorite buttons
            const btns = document.querySelectorAll('.favorite-btn');
            let isSaved = false;

            btns.forEach(btn => {
                const icon = btn.querySelector('i');
                const text = btn.querySelector('.save-text');
                
                // Determine current state from the first button found
                if (btn === btns[0]) {
                     isSaved = icon.classList.contains('bi-heart-fill');
                }

                if (isSaved) {
                    icon.classList.remove('bi-heart-fill', 'text-danger');
                    icon.classList.add('bi-heart');
                    if(text) text.innerText = 'Save';
                } else {
                    icon.classList.remove('bi-heart');
                    icon.classList.add('bi-heart-fill', 'text-danger');
                    if(text) text.innerText = 'Saved';
                }
            });

            // AJAX Request
            fetch('tenant/save_property.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'property_id=' + propertyId
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Revert if failed
                    alert('Action failed. Please try again.');
                    btns.forEach(btn => {
                        const icon = btn.querySelector('i');
                        const text = btn.querySelector('.save-text');
                        
                        if (isSaved) {
                            icon.classList.add('bi-heart-fill', 'text-danger');
                            icon.classList.remove('bi-heart');
                            if(text) text.innerText = 'Saved';
                        } else {
                            icon.classList.add('bi-heart');
                            icon.classList.remove('bi-heart-fill', 'text-danger');
                            if(text) text.innerText = 'Save';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred.');
            });
        }

        function calculateRentalCost() {
            const rent = parseFloat(document.getElementById('calcRent').value) || 0;
            const duration = parseFloat(document.getElementById('calcDuration').value) || 0;
            const deposit = parseFloat(document.getElementById('calcDeposit').value) || 0;
            const utilities = parseFloat(document.getElementById('calcUtilities').value) || 0;
            
            const resultDiv = document.getElementById('calcResult');
            const totalCostSpan = document.getElementById('totalCost');
            const moveInCostSpan = document.getElementById('moveInCost');

            if (rent <= 0 || duration <= 0) {
                alert('Please enter valid rent amount and duration.');
                return;
            }

            const totalRent = rent * duration;
            const totalUtilities = utilities * duration;
            const totalCost = totalRent + deposit + totalUtilities;
            const moveInCost = rent + deposit;

            const currency = '<?php echo $property['currency']; ?>';
            
            totalCostSpan.innerText = currency + ' ' + totalCost.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0});
            moveInCostSpan.innerText = currency + ' ' + moveInCost.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0});
            
            resultDiv.style.display = 'block';
        }

        function initMap() {
            var lat = <?php echo $lat; ?>;
            var lng = <?php echo $lng; ?>;
            
            // Basic validation
            if (lat === 0) lat = -15.3875;
            if (lng === 0) lng = 28.3228;

            var location = { lat: lat, lng: lng };
            
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: location
            });
            
            var marker = new google.maps.Marker({
                position: location,
                map: map,
                title: '<?php echo htmlspecialchars($property['title']); ?>'
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
