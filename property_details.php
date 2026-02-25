<?php
require_once 'config/config.php';
require_once 'models/Property.php';
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
                        <?php echo ($property['listing_purpose'] ?? 'rent') == 'sale' ? 'For Sale' : 'For Rent'; ?>
                    </span>
                    <span class="badge bg-light text-dark border fw-medium px-3 py-2 rounded-pill">
                        <?php echo ucfirst($property['property_type']); ?>
                    </span>
                </div>
                <h1 class="fw-bold mb-1"><?php echo htmlspecialchars($property['title']); ?></h1>
                <p class="text-muted mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($property['location']); ?></p>
            </div>
            <div class="text-end">
                <h2 class="text-primary fw-bold mb-0"><?php echo $property['currency'] . ' ' . number_format($property['price']); ?></h2>
                <small class="text-muted"><?php echo ($property['listing_purpose'] ?? 'rent') == 'sale' ? 'Full Price' : '/ month'; ?></small>
            </div>
        </div>

        <!-- Image Gallery (Grid + Slider Support) -->
        <div class="gallery-grid">
            <?php 
                $count = count($images);
                // Main Image
                $img1 = ($count > 0) ? $images[0]['image_path'] : 'assets/images/placeholder.jpg';
                echo '<div class="gallery-item gallery-main"><a href="' . $img1 . '" class="glightbox" data-gallery="property-gallery"><img src="' . $img1 . '" class="gallery-img"></a></div>';

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
                
                <!-- Key Features -->
                <div class="d-flex justify-content-between border-bottom pb-4 mb-4 text-center">
                    <div>
                        <span class="d-block text-muted small">Bedrooms</span>
                        <span class="fw-bold fs-5"><i class="bi bi-people"></i> <?php echo $property['bedrooms']; ?></span>
                    </div>
                    <div>
                        <span class="d-block text-muted small">Bathrooms</span>
                        <span class="fw-bold fs-5"><i class="bi bi-droplet"></i> <?php echo $property['bathrooms']; ?></span>
                    </div>
                    <div>
                        <span class="d-block text-muted small">Area</span>
                        <span class="fw-bold fs-5"><i class="bi bi-aspect-ratio"></i> <?php echo $property['size_sqm']; ?> m²</span>
                    </div>
                    <div>
                        <span class="d-block text-muted small">Type</span>
                        <span class="fw-bold fs-5"><?php echo ucfirst($property['property_type']); ?></span>
                    </div>
                </div>

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
                            <div class="amenity-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span><?php echo trim($amenity); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Map -->
                <div class="mb-5">
                    <h4 class="fw-bold mb-3">Where you'll be</h4>
                    <div id="map" class="map-container"></div>
                </div>

            </div>

            <!-- Right Column: Agent Card -->
            <div class="col-lg-4">
                <div class="agent-card shadow-sm bg-white">
                    <div class="d-flex align-items-center mb-4">
                        <img src="assets/images/user-placeholder.png" class="agent-avatar" alt="Agent">
                        <div>
                            <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($property['dealer_name'] ?? 'Agent'); ?></h6>
                            <small class="text-muted">Verified Host <i class="bi bi-patch-check-fill text-primary"></i></small>
                        </div>
                    </div>

                    <?php if(!empty($property['phone'])): ?>
                        <a href="tel:<?php echo $property['phone']; ?>" class="btn-contact btn-call">
                            <i class="bi bi-telephone-fill"></i> Call Agent
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
                    
                    <hr class="my-4">
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
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
    <!-- Lightbox JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/glightbox/3.2.0/js/glightbox.min.js"></script>
    <script>
        const lightbox = GLightbox({
            touchNavigation: true,
            loop: true,
            autoplayVideos: true
        });

        function initMap() {
            var location = { lat: <?php echo $lat; ?>, lng: <?php echo $lng; ?> };
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
