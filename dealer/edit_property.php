<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

// Include Header
include 'includes/header.php';

// Check Subscription
$userModel = new User();
$dealerProfile = $userModel->getDealerProfile($_SESSION['user_id']);
$canEdit = false;

if ($dealerProfile && $dealerProfile['subscription_status'] === 'active') {
    if (!empty($dealerProfile['subscription_expiry'])) {
        $expiryDate = new DateTime($dealerProfile['subscription_expiry']);
        $now = new DateTime();
        if ($expiryDate > $now) {
            $canEdit = true;
        }
    } else {
        $canEdit = true;
    }
}

if (!$canEdit) {
    echo "
    <div class='row justify-content-center'>
        <div class='col-md-8'>
            <div class='alert alert-warning text-center p-5 rounded-3 shadow-sm'>
                <i class='bi bi-lock-fill display-1 text-warning mb-3'></i>
                <h3 class='fw-bold'>Subscription Required</h3>
                <p class='lead'>Your subscription is inactive or expired. You cannot edit properties.</p>
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

if (!isset($_GET['id'])) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$propertyModel = new Property();
$property = $propertyModel->getById($_GET['id']);

if (!$property || $property['dealer_id'] != $_SESSION['user_id']) {
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'id' => $_GET['id'],
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
        'status' => $_POST['status'],
        'amenities' => htmlspecialchars($_POST['amenities']),
        'video_url' => htmlspecialchars($_POST['video_url'])
    ];

    if ($propertyModel->update($data)) {
        $success = "Property updated successfully!";
        // Refresh data
        $property = $propertyModel->getById($_GET['id']);
    } else {
        $error = "Failed to update property.";
    }
}
?>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow border-0 rounded-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Edit Property</h4>
                    <a href="properties.php" class="btn btn-sm btn-light border">Back to List</a>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Property Title *</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($property['title']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-bold">Price *</label>
                                <div class="input-group">
                                    <select class="form-select" id="currency" name="currency" style="max-width: 100px;">
                                        <option value="ZMW" <?php echo $property['currency'] == 'ZMW' ? 'selected' : ''; ?>>ZMW</option>
                                        <option value="USD" <?php echo $property['currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
                                        <option value="EUR" <?php echo $property['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                    </select>
                                    <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $property['price']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="property_type" class="form-label fw-bold">Property Type *</label>
                                <select class="form-select" id="property_type" name="property_type" required>
                                    <option value="house" <?php echo $property['property_type'] == 'house' ? 'selected' : ''; ?>>House</option>
                                    <option value="apartment" <?php echo $property['property_type'] == 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                    <option value="flat" <?php echo $property['property_type'] == 'flat' ? 'selected' : ''; ?>>Flat</option>
                                    <option value="boarding_house" <?php echo $property['property_type'] == 'boarding_house' ? 'selected' : ''; ?>>Boarding House</option>
                                    <option value="land" <?php echo $property['property_type'] == 'land' ? 'selected' : ''; ?>>Land</option>
                                    <option value="commercial" <?php echo $property['property_type'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label for="listing_purpose" class="form-label fw-bold">Purpose *</label>
                                <select class="form-select" id="listing_purpose" name="listing_purpose" required>
                                    <option value="rent" <?php echo ($property['listing_purpose'] ?? 'rent') == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                                    <option value="sale" <?php echo ($property['listing_purpose'] ?? '') == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-bold">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($property['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="bedrooms" class="form-label fw-bold">Bedrooms</label>
                                <input type="number" class="form-control" id="bedrooms" name="bedrooms" value="<?php echo $property['bedrooms']; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="bathrooms" class="form-label fw-bold">Bathrooms</label>
                                <input type="number" class="form-control" id="bathrooms" name="bathrooms" value="<?php echo $property['bathrooms']; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="rooms" class="form-label fw-bold">Total Rooms</label>
                                <input type="number" class="form-control" id="rooms" name="rooms" value="<?php echo $property['rooms']; ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="size_sqm" class="form-label fw-bold">Size (sqm)</label>
                                <input type="number" step="0.01" class="form-control" id="size_sqm" name="size_sqm" value="<?php echo $property['size_sqm']; ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-bold">City *</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($property['city']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="country" class="form-label fw-bold">Country *</label>
                                <select class="form-select" id="country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="Zambia" <?php echo $property['country'] == 'Zambia' ? 'selected' : ''; ?>>Zambia</option>
                                    <option value="South Africa" <?php echo $property['country'] == 'South Africa' ? 'selected' : ''; ?>>South Africa</option>
                                    <option value="Nigeria" <?php echo $property['country'] == 'Nigeria' ? 'selected' : ''; ?>>Nigeria</option>
                                    <option value="Kenya" <?php echo $property['country'] == 'Kenya' ? 'selected' : ''; ?>>Kenya</option>
                                    <option value="United Kingdom" <?php echo $property['country'] == 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                                    <option value="United States" <?php echo $property['country'] == 'United States' ? 'selected' : ''; ?>>United States</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label fw-bold">Address / Location *</label>
                            <input type="text" class="form-control mb-2" id="location" name="location" value="<?php echo htmlspecialchars($property['location']); ?>" required>
                            <small class="text-muted d-block mb-2">Click on the map to update location.</small>
                            <div id="map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd;"></div>
                            <input type="hidden" id="latitude" name="latitude" value="<?php echo $property['latitude']; ?>">
                            <input type="hidden" id="longitude" name="longitude" value="<?php echo $property['longitude']; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="available" <?php echo $property['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="rented" <?php echo $property['status'] == 'rented' ? 'selected' : ''; ?>>Rented / Unavailable</option>
                                <option value="sold" <?php echo $property['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="amenities" class="form-label fw-bold">Amenities</label>
                            <input type="text" class="form-control" id="amenities" name="amenities" value="<?php echo htmlspecialchars($property['amenities']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="video_url" class="form-label fw-bold">Video Tour URL</label>
                            <input type="url" class="form-control" id="video_url" name="video_url" value="<?php echo htmlspecialchars($property['video_url']); ?>">
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="upload_images.php?id=<?php echo $property['id']; ?>" class="btn btn-outline-secondary"><i class="bi bi-images"></i> Manage Images</a>
                            <button type="submit" class="btn btn-primary px-4">Update Property</button>
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
        // Use existing lat/long or default to Lusaka
        const existingLat = <?php echo !empty($property['latitude']) ? $property['latitude'] : -15.3875; ?>;
        const existingLng = <?php echo !empty($property['longitude']) ? $property['longitude'] : 28.3228; ?>;
        const location = { lat: existingLat, lng: existingLng };
        
        map = new google.maps.Map(document.getElementById("map"), {
            zoom: 12,
            center: location,
        });

        // Place initial marker
        marker = new google.maps.Marker({
            position: location,
            map: map,
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
