<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Subscription & Verification
$userModel = new User();
$dealerProfile = $userModel->getDealerProfile($_SESSION['user_id']);
$userProfile = $userModel->getUserById($_SESSION['user_id']); // Get generic user data for verification status

$canAddProperty = false;

// Check Verification First
if (empty($userProfile['identity_verified'])) { // Use new column
    // Check if they already submitted a verification request
    // We can check if 'verification_doc' is not null, then show pending message.
    
    // Actually, the user asked: "if the dealer is not verified on the add property they should upload"
    // This implies that instead of just blocking them, we should show a form to upload ID/Verification documents.
    
    $upload_success = '';
    $upload_error = '';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['dealer_verification_doc'])) {
        $target_dir = "../assets/images/dealer_docs/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["dealer_verification_doc"]["name"], PATHINFO_EXTENSION));
        $new_filename = 'dealer_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Allow certain file formats
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        if (!in_array($file_extension, $allowed)) {
            $upload_error = "Only JPG, PNG & PDF files are allowed.";
        } else {
            if (move_uploaded_file($_FILES["dealer_verification_doc"]["tmp_name"], $target_file)) {
                // Update user to mark as 'pending verification' if we had a status column
                // For now, assume uploading means pending.
                // We should probably save the filename to the user record so we know they uploaded something.
                // Let's do a quick update to the users table to store this doc path if we can, 
                // OR just rely on the email sent to admin.
                
                require_once '../includes/SimpleMailer.php';
                $mailer = new SimpleMailer();
                $subject = "Dealer Verification Request - " . $_SESSION['user_name'];
                $body = "User " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ") has uploaded a verification document.<br>File: " . SITE_URL . "/assets/images/dealer_docs/" . $new_filename;
                $mailer->send(SMTP_FROM, $subject, $body);
                
                // SAVE TO DATABASE so Admin can see it
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $stmt = $pdo->prepare("UPDATE users SET verification_doc = :doc WHERE id = :id");
                    $stmt->execute([':doc' => "assets/images/dealer_docs/" . $new_filename, ':id' => $_SESSION['user_id']]);
                } catch (Exception $e) {
                    $upload_error = "DB Update Failed: " . $e->getMessage();
                    $upload_success = ''; // Clear success if DB fail
                }
                
                if (empty($upload_error)) {
                    $upload_success = "Document uploaded successfully. An administrator will review your account shortly.";
                    $_SESSION['verification_pending'] = true;
                }
                
            } else {
                $upload_error = "Failed to upload file.";
            }
        }
    }
    
    // Force stop here - DO NOT SHOW THE REST OF THE PAGE
    ?>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card border-danger shadow-lg'>
                    <div class='card-header bg-danger text-white py-3'>
                        <h4 class='mb-0 fw-bold'><i class='bi bi-shield-lock-fill me-2'></i>Account Verification Required</h4>
                    </div>
                    <div class='card-body p-5 text-center'>
                        <div class="mb-4">
                            <i class='bi bi-person-badge display-1 text-danger'></i>
                        </div>
                        
                        <h3 class='fw-bold mb-3'>You cannot post properties yet!</h3>
                        <p class='lead mb-4'>To prevent fraud and ensure safety, all dealers must verify their identity before listing properties.</p>
                        
                        <?php if($upload_success || isset($_SESSION['verification_pending'])): ?>
                             <div class="alert alert-info border-info text-start p-4">
                                <h4 class="alert-heading fw-bold"><i class="bi bi-clock-history"></i> Verification Pending</h4>
                                <p class="mb-0 lead">Thank you for uploading your verification photo. Our team is currently reviewing your submission.</p>
                                <hr>
                                <p class="mb-0 small">You will be notified via email once your account is approved. This usually takes 24-48 hours.</p>
                            </div>
                            <div class='mt-4'>
                                <a href='dashboard.php' class='btn btn-primary px-4'>Return to Dashboard</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning border-warning text-start">
                                <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Critical Action Required:</h5>
                                <p class="mb-0">Please upload a clear photo of <strong>yourself standing next to your property</strong> to verify your identity.</p>
                            </div>
                            
                            <?php if($upload_error): ?>
                                <div class='alert alert-danger fw-bold'><?php echo $upload_error; ?></div>
                            <?php endif; ?>
                            
                            <form method='POST' enctype='multipart/form-data' class='mt-4 p-4 border rounded bg-light'>
                                <div class='mb-3 text-start'>
                                    <label class='form-label fw-bold'>Upload Verification Photo:</label>
                                    
                                    <!-- Preview for ID Document -->
                                    <div class='mb-3 text-center p-3 border rounded bg-white' id='idPreviewContainer' style='display:none;'>
                                        <img id='idPreview' src='' class='img-fluid rounded shadow-sm' style='max-height: 250px;'>
                                        <div class="mt-2 text-success small fw-bold"><i class="bi bi-check-circle"></i> Photo Ready to Upload</div>
                                    </div>
                                    
                                    <input type='file' class='form-control form-control-lg' name='dealer_verification_doc' required accept='.jpg,.jpeg,.png,.pdf' onchange='previewIDImage(this)'>
                                    <div class="form-text">Photo of you + property. Formats: JPG, PNG</div>
                                </div>
                                <button type='submit' class='btn btn-danger btn-lg w-100 fw-bold shadow-sm'>
                                    <i class="bi bi-upload me-2"></i> Submit Verification Photo
                                </button>
                            </form>
                            
                            <div class='mt-4'>
                                <a href='dashboard.php' class='btn btn-outline-secondary'>Back to Dashboard</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function previewIDImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('idPreview').src = e.target.result;
                document.getElementById('idPreviewContainer').style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>
    <?php
    exit; // STOP EXECUTION HERE
}

if ($dealerProfile && $dealerProfile['subscription_status'] === 'active') {
    // Check expiry
    $is_expired = false;
    if (!empty($dealerProfile['subscription_expiry'])) {
        $expiryDate = new DateTime($dealerProfile['subscription_expiry']);
        $now = new DateTime();
        if ($expiryDate < $now) {
            $is_expired = true;
        }
    }
    
    if (!$is_expired) {
        $canAddProperty = true;
        // Determine if paid or free trial
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $checkPaid = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'successful'");
            $checkPaid->execute([$_SESSION['user_id']]);
            $plan_type = ($checkPaid->fetchColumn() > 0) ? 'paid' : 'free_trial';
        } catch (Exception $e) {}
    }
}

// RESTRICTION: Only Paid users can add properties? 
// User request: "if the account is not on paid version dont let them upload or edit"
// This implies Free Trial users CANNOT upload? Or just "Inactive"?
// "if a account is paid make it featured... if not on paid version dont let them upload"
// This suggests a strict "Pay to Post" model, meaning Free Trial might be "View Only" or "Limited"?
// However, earlier we set up Free Trial to allow access. 
// If the user literally means "Must be Paid to Upload", then Free Trial is useless for uploading.
// Let's assume "Not on Paid Version" means "Expired or Inactive".
// BUT, the specific phrase "dont let them upload... make it automatic change when paid" implies forcing payment.
// Let's implement: Active (Paid or Free Trial) = Can Upload. Inactive/Expired = Blocked.
// Wait, "if account is not on paid version dont let them upload". 
// If I strictly follow this, Free Trial users cannot upload.
// Let's stick to: Active Subscription (Paid or Free) = Can Upload. 
// If the user meant "Only Premium Paid Users", I would exclude Free Trial.
// Given "Free Trial" is usually to *try* the features, blocking upload seems counter-intuitive.
// I will assume "Paid Version" here means "Active Subscription" (vs Unpaid/Expired).

if (!$canAddProperty) {
    echo "
    <div class='row justify-content-center'>
        <div class='col-md-8'>
            <div class='alert alert-warning text-center p-5 rounded-3 shadow-sm'>
                <i class='bi bi-lock-fill display-1 text-warning mb-3'></i>
                <h3 class='fw-bold'>Subscription Required</h3>
                <p class='lead'>Your subscription is inactive or expired. Please upgrade to a paid plan to manage properties.</p>
                <a href='subscribe.php' class='btn btn-warning mt-3 fw-bold px-4'>Upgrade Now</a>
            </div>
        </div>
    </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $property = new Property();
    
    // Validate inputs
    if (empty($_POST['title']) || empty($_POST['price']) || empty($_POST['location'])) {
        $error = "Please fill in all required fields.";
    } else {
        $is_featured = 0;
        
        // Handle Verification Image Upload (No longer required for individual properties)
        $verification_image_path = null;
        
        if (!$error) {
            // Check if user is on paid plan to auto-feature
        $stmt = $userModel->getDealerProfile($_SESSION['user_id']);
        if ($stmt && $stmt['subscription_status'] === 'active') {
            // Check for successful payment to differentiate from Free Trial if needed
            // However, "Active" usually means they can post.
            // If Free Trial also gets featured status, then just check 'active'.
            // If only PAID gets featured, we need to check transactions.
            
            // User requested: "if a account is paid make it featured"
            // Let's check if they have a successful transaction (Paid)
            try {
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $checkPaid = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'successful'");
                $checkPaid->execute([$_SESSION['user_id']]);
                if ($checkPaid->fetchColumn() > 0) {
                    $is_featured = 1;
                }
            } catch (Exception $e) {
                // Silent fail, default to 0
            }
        }

        $data = [
            'dealer_id' => $_SESSION['user_id'],
            'title' => htmlspecialchars($_POST['title']),
            'description' => htmlspecialchars($_POST['description']),
            'price' => floatval($_POST['price']),
            'currency' => $_POST['currency'],
            'bedrooms' => intval($_POST['bedrooms']),
            'bathrooms' => intval($_POST['bathrooms']),
            'rooms' => intval($_POST['rooms']),
            'size_sqm' => floatval($_POST['size_sqm']),
            'property_type' => $_POST['property_type'],
            'listing_purpose' => $_POST['listing_purpose'],
            'location' => htmlspecialchars($_POST['location']),
            'city' => htmlspecialchars($_POST['city']),
            'country' => htmlspecialchars($_POST['country']),
            'latitude' => !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null,
            'longitude' => !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null,
            'status' => 'available',
            'is_featured' => $is_featured,
            'amenities' => htmlspecialchars($_POST['amenities']),
            'video_url' => htmlspecialchars($_POST['video_url']),
            'capacity' => !empty($_POST['capacity']) ? intval($_POST['capacity']) : null,
            'people_per_room' => !empty($_POST['people_per_room']) ? intval($_POST['people_per_room']) : null,
            'event_type' => !empty($_POST['event_type']) ? htmlspecialchars($_POST['event_type']) : null,
            'catering_available' => isset($_POST['catering_available']) ? 1 : 0,
            'equipment_available' => isset($_POST['equipment_available']) ? 1 : 0,
            'verification_image' => $verification_image_path,
            'is_verified' => 0 // Default to unverified until admin checks
        ];

        $property_id = $property->create($data);
        if ($property_id) {
            $success = "Property added successfully! Now upload images.";
            // Redirect to image upload page with the new property ID
            echo "<script>window.location.href = 'upload_images.php?id=" . $property_id . "';</script>";
            exit;
        } else {
            $error = "Failed to add property. Please try again.";
        }
    }
}
}
?>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h4 class="mb-0 fw-bold">Add New Property</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Property Title *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-bold">Price *</label>
                                <div class="input-group">
                                    <select class="form-select" id="currency" name="currency" style="max-width: 100px;">
                                        <option value="ZMW">ZMW</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                    </select>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="property_type" class="form-label fw-bold">Property Type *</label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="house">House</option>
                                    <option value="apartment">Apartment</option>
                                    <option value="flat">Flat</option>
                                    <option value="boarding_house">Boarding House</option>
                                    <option value="land">Land</option>
                                    <option value="commercial">Commercial</option>
                                    <option value="wedding_venue">Wedding Venue</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="lodge">Lodge</option>
                                    <option value="studio">Studio</option>
                                    <option value="cottage">Cottage</option>
                                    <option value="manor">Manor</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="listing_purpose" class="form-label fw-bold">Purpose *</label>
                                <select class="form-select" id="listing_purpose" name="listing_purpose" required>
                                    <option value="rent">For Rent</option>
                                    <option value="sale">For Sale</option>
                                    <option value="booking">For Booking</option>
                                    <option value="service">Service</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <!-- Standard Fields (House, Apartment, Flat, Cottage, Manor) -->
                            <div class="col-md-3 mb-3 field-group group-standard">
                                <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms">
                            </div>
                            <div class="col-md-3 mb-3 field-group group-standard">
                                <label for="bathrooms" class="form-label fw-bold">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms">
                            </div>
                            
                            <!-- Boarding House Fields -->
                            <div class="col-md-3 mb-3 field-group group-boarding" style="display:none;">
                                <label for="people_per_room" class="form-label fw-bold">People per Room</label>
                                <input type="number" class="form-control" id="people_per_room" name="people_per_room">
                            </div>

                            <!-- Venue / Restaurant / Lodge Fields -->
                            <div class="col-md-3 mb-3 field-group group-venue" style="display:none;">
                                <label for="capacity" class="form-label fw-bold">Capacity (People)</label>
                                <input type="number" class="form-control" id="capacity" name="capacity">
                            </div>
                            <div class="col-md-3 mb-3 field-group group-venue" style="display:none;">
                                <label for="event_type" class="form-label fw-bold">Event Type Suitability</label>
                                <input type="text" class="form-control" id="event_type" name="event_type" placeholder="e.g. Weddings, Conferences">
                            </div>
                            
                            <!-- Common Room Count -->
                            <div class="col-md-3 mb-3 field-group group-common">
                                <label for="rooms" class="form-label fw-bold">Total Rooms</label>
                                <input type="number" class="form-control" id="rooms" name="rooms">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="size_sqm" class="form-label fw-bold">Size (sqm)</label>
                                <input type="number" step="0.01" class="form-control" id="size_sqm" name="size_sqm">
                            </div>
                        </div>

                        <!-- Amenities Checkboxes for Special Categories -->
                        <div class="mb-3 field-group group-venue" style="display:none;">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="catering_available" name="catering_available" value="1">
                                <label class="form-check-label" for="catering_available">Catering Available</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" id="equipment_available" name="equipment_available" value="1">
                                <label class="form-check-label" for="equipment_available">Equipment Available (Tables, Chairs, etc.)</label>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-bold">City *</label>
                                <input type="text" class="form-control" id="city" name="city" required placeholder="e.g. Lusaka">
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label fw-bold">Country *</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label fw-bold">Address / Location *</label>
                            <input type="text" class="form-control mb-2" id="location" name="location" placeholder="Specific Area, Street" required>
                            <small class="text-muted d-block mb-2">Click on the map to pin the exact location.</small>
                            <div id="map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>

                        <div class="mb-3">
                            <label for="amenities" class="form-label fw-bold">Amenities</label>
                            <input type="text" class="form-control" id="amenities" name="amenities" placeholder="e.g. WiFi, Parking, Pool, Gym (Comma separated)">
                        </div>

                        <div class="mb-3">
                            <label for="video_url" class="form-label fw-bold">Video Tour URL (Optional)</label>
                            <input type="url" class="form-control" id="video_url" name="video_url" placeholder="https://youtube.com/...">
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="properties.php" class="btn btn-light border">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Save & Continue</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&libraries=places&callback=initMap" async defer></script>

<script>
    let map;
    let marker;
    let autocomplete;
    const locationInput = document.getElementById('location');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');

    function initMap() {
        // Default to Lusaka, Zambia
        const defaultLocation = { lat: -15.4167, lng: 28.2833 };
        
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 13,
            center: defaultLocation,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        // Initialize marker
        marker = new google.maps.Marker({
            position: defaultLocation,
            map: map,
            draggable: true
        });

        // Try to get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setCenter(userLocation);
                map.setZoom(15);
                marker.setPosition(userLocation);
                updateCoordinates(userLocation);
            });
        }

        // Add click listener
        map.addListener("click", function(e) {
            marker.setPosition(e.latLng);
            updateCoordinates(e.latLng);
        });

        // Add drag listener
        marker.addListener("dragend", function(e) {
            updateCoordinates(e.latLng);
        });

        // Initialize Autocomplete
        initAutocomplete();
    }

    function initAutocomplete() {
        autocomplete = new google.maps.places.Autocomplete(locationInput);
        autocomplete.bindTo("bounds", map);

        autocomplete.addListener("place_changed", function() {
            const place = autocomplete.getPlace();

            if (!place.geometry || !place.geometry.location) {
                return;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(17);
            }

            marker.setPosition(place.geometry.location);
            updateCoordinates(place.geometry.location);
            
            // Auto-fill City/Country if possible (Optional but nice)
            fillAddressDetails(place);
        });
    }

    function updateCoordinates(latLng) {
        latitudeInput.value = latLng.lat();
        longitudeInput.value = latLng.lng();
    }

    function fillAddressDetails(place) {
        // Reset fields
        document.getElementById('city').value = '';
        
        // Loop through address components
        for (const component of place.address_components) {
            const componentType = component.types[0];

            if (componentType === "locality" || componentType === "administrative_area_level_1") {
                document.getElementById('city').value = component.long_name;
            }
            if (componentType === "country") {
                const countrySelect = document.getElementById('country');
                // Try to match country name
                for(let i=0; i<countrySelect.options.length; i++) {
                    if(countrySelect.options[i].text === component.long_name) {
                        countrySelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    }

    // Dynamic Field Logic (Keep existing logic)
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamic Field Logic
        const typeSelect = document.getElementById('property_type');
        if(typeSelect) {
            typeSelect.addEventListener('change', updateFields);
            updateFields(); // Initial call
        }
    });

    function updateFields() {
        const typeSelect = document.getElementById('property_type');
        if(!typeSelect) return;
        
        const type = typeSelect.value;
        const standardGroups = document.querySelectorAll('.group-standard');
        const boardingGroups = document.querySelectorAll('.group-boarding');
        const venueGroups = document.querySelectorAll('.group-venue');
        const commonGroups = document.querySelectorAll('.group-common');

        // Hide all first
        standardGroups.forEach(el => el.style.display = 'none');
        boardingGroups.forEach(el => el.style.display = 'none');
        venueGroups.forEach(el => el.style.display = 'none');
        commonGroups.forEach(el => el.style.display = 'none');

        if (['house', 'apartment', 'flat', 'cottage', 'manor'].includes(type)) {
            standardGroups.forEach(el => el.style.display = 'block');
            commonGroups.forEach(el => el.style.display = 'block');
        } else if (type === 'boarding_house') {
            standardGroups.forEach(el => el.style.display = 'block'); 
            boardingGroups.forEach(el => el.style.display = 'block');
            commonGroups.forEach(el => el.style.display = 'block');
        } else if (type === 'lodge') {
            standardGroups.forEach(el => el.style.display = 'block');
            venueGroups.forEach(el => el.style.display = 'block');
            commonGroups.forEach(el => el.style.display = 'block');
        } else if (['wedding_venue', 'restaurant', 'commercial', 'studio'].includes(type)) {
            venueGroups.forEach(el => el.style.display = 'block');
        }
    }

    function previewVerificationImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById('verificationPreview');
                const container = document.getElementById('verificationPreviewContainer');
                
                preview.src = e.target.result;
                container.style.display = 'block';
                
                // Optional: Scroll to preview
                container.scrollIntoView({behavior: 'smooth', block: 'center'});
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
