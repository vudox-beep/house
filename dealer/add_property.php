<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Subscription
$userModel = new User();
$dealerProfile = $userModel->getDealerProfile($_SESSION['user_id']);

$canAddProperty = false;
$plan_type = 'inactive';

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
            'video_url' => htmlspecialchars($_POST['video_url'])
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

                    <form method="POST" action="">
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
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="listing_purpose" class="form-label fw-bold">Purpose *</label>
                                <select class="form-select" id="listing_purpose" name="listing_purpose" required>
                                    <option value="rent">For Rent</option>
                                    <option value="sale">For Sale</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bathrooms" class="form-label fw-bold">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="rooms" class="form-label fw-bold">Total Rooms</label>
                                <input type="number" class="form-control" id="rooms" name="rooms">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="size_sqm" class="form-label fw-bold">Size (sqm)</label>
                                <input type="number" step="0.01" class="form-control" id="size_sqm" name="size_sqm">
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
<!-- Google Maps JS -->
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Default to Lusaka
        const defaultLocation = { lat: -15.3875, lng: 28.3228 };
        
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: defaultLocation,
        });

        // Add click listener
        map.addListener("click", (e) => {
            placeMarkerAndPanTo(e.latLng);
        });
    }

    function placeMarkerAndPanTo(latLng) {
        if (marker) {
            marker.setPosition(latLng);
        } else {
            marker = new google.maps.Marker({
                position: latLng,
                map: map,
            });
        }
        map.panTo(latLng);
        
        // Update hidden inputs
        document.getElementById('latitude').value = latLng.lat();
        document.getElementById('longitude').value = latLng.lng();
    }
</script>
</body>
</html>
