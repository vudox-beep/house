<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/Property.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;
if (!$data) $data = $_GET;

$action = $data['action'] ?? 'get_all';

$propertyModel = new Property();

if ($action === 'get_all') {
    // --- GET ALL PROPERTIES ---
    $properties = $propertyModel->getAll();
    $result = [];

    foreach ($properties as $prop) {
        $images = $propertyModel->getImages($prop['id']);
        $prop_images = [];
        $main_image = null;

        foreach ($images as $img) {
            $fullUrl = rtrim(SITE_URL, '/') . '/' . $img['image_path'];
            $prop_images[] = [
                'id' => $img['id'],
                'url' => $fullUrl,
                'is_main' => $img['is_main']
            ];
            if ($img['is_main']) {
                $main_image = $fullUrl;
            }
        }

        // Fallback if no main image
        if (!$main_image && count($prop_images) > 0) {
            $main_image = $prop_images[0]['url'];
        }

        $prop['images'] = $prop_images;
        $prop['main_image'] = $main_image;
        
        // Ensure verification doc url is full if it exists
        if (!empty($prop['verification_image'])) {
            $prop['verification_image'] = rtrim(SITE_URL, '/') . '/' . $prop['verification_image'];
        }

        // Ensure video url is full if it exists
        if (!empty($prop['video_url']) && strpos($prop['video_url'], 'http') !== 0) {
            $prop['video_url'] = rtrim(SITE_URL, '/') . '/' . ltrim($prop['video_url'], '/');
        }

        $result[] = $prop;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $result
    ]);

} elseif ($action === 'get_single') {
    // --- GET SINGLE PROPERTY ---
    $id = $data['property_id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['status' => 'error', 'message' => 'Property ID required']);
        exit;
    }

    $prop = $propertyModel->getById($id);
    
    if (!$prop) {
        echo json_encode(['status' => 'error', 'message' => 'Property not found']);
        exit;
    }

    // Increment views safely
    $propertyModel->incrementViews($id);

    $images = $propertyModel->getImages($id);
    $prop_images = [];
    $main_image = null;

    foreach ($images as $img) {
        $fullUrl = rtrim(SITE_URL, '/') . '/' . $img['image_path'];
        $prop_images[] = [
            'id' => $img['id'],
            'url' => $fullUrl,
            'is_main' => $img['is_main']
        ];
        if ($img['is_main']) {
            $main_image = $fullUrl;
        }
    }

    if (!$main_image && count($prop_images) > 0) {
        $main_image = $prop_images[0]['url'];
    }

    $prop['images'] = $prop_images;
    $prop['main_image'] = $main_image;
    
    // Document URL mapping
    if (!empty($prop['verification_image'])) {
        $prop['verification_image'] = rtrim(SITE_URL, '/') . '/' . ltrim($prop['verification_image'], '/');
    }

    // Video URL mapping
    if (!empty($prop['video_url']) && strpos($prop['video_url'], 'http') !== 0) {
        $prop['video_url'] = rtrim(SITE_URL, '/') . '/' . ltrim($prop['video_url'], '/');
    }

    echo json_encode([
        'status' => 'success',
        'data' => $prop
    ]);

} elseif ($action === 'get_property_images') {
    // --- GET IMAGES FOR A SPECIFIC PROPERTY ---
    $property_id = $data['property_id'] ?? '';
    
    if (empty($property_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Property ID required']);
        exit;
    }

    // Verify property exists
    $prop = $propertyModel->getById($property_id);
    if (!$prop) {
        echo json_encode(['status' => 'error', 'message' => 'Property not found']);
        exit;
    }

    // Fetch images
    $images = $propertyModel->getImages($property_id);
    $prop_images = [];
    $main_image = null;

    foreach ($images as $img) {
        $fullUrl = rtrim(SITE_URL, '/') . '/' . ltrim($img['image_path'], '/');
        $prop_images[] = [
            'id' => $img['id'],
            'url' => $fullUrl,
            'is_main' => $img['is_main']
        ];
        if ($img['is_main']) {
            $main_image = $fullUrl;
        }
    }

    if (!$main_image && count($prop_images) > 0) {
        $main_image = $prop_images[0]['url'];
    }

    // Include video if present
    $video_url = null;
    if (!empty($prop['video_url'])) {
        $video_url = strpos($prop['video_url'], 'http') === 0
            ? $prop['video_url']
            : rtrim(SITE_URL, '/') . '/' . ltrim($prop['video_url'], '/');
    }

    echo json_encode([
        'status' => 'success',
        'property_id' => $property_id,
        'main_image' => $main_image,
        'images' => $prop_images,
        'video_url' => $video_url
    ]);

} elseif ($action === 'get_dealer_properties') {
    // --- GET PROPERTIES FOR A SPECIFIC DEALER ---
    $dealer_id = $data['dealer_id'] ?? '';
    
    if (empty($dealer_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Dealer ID required']);
        exit;
    }

    // Get dealer profile info
    $pdo = (new Database())->connect();
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.phone, u.whatsapp_number, u.profile_image, 
               u.identity_verified, d.company_name, d.address 
        FROM users u 
        LEFT JOIN dealers d ON u.id = d.user_id 
        WHERE u.id = ? AND u.role = 'dealer'
    ");
    $stmt->execute([$dealer_id]);
    $dealer_info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dealer_info) {
        echo json_encode(['status' => 'error', 'message' => 'Dealer not found']);
        exit;
    }
    
    if (!empty($dealer_info['profile_image'])) {
        $dealer_info['profile_image'] = rtrim(SITE_URL, '/') . '/' . $dealer_info['profile_image'];
    }

    // Get dealer's properties
    $stmtProps = $pdo->prepare("SELECT * FROM properties WHERE dealer_id = ? ORDER BY created_at DESC");
    $stmtProps->execute([$dealer_id]);
    $properties = $stmtProps->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($properties as $prop) {
        $images = $propertyModel->getImages($prop['id']);
        $prop_images = [];
        $main_image = null;

        foreach ($images as $img) {
            $fullUrl = rtrim(SITE_URL, '/') . '/' . $img['image_path'];
            $prop_images[] = [
                'id' => $img['id'],
                'url' => $fullUrl,
                'is_main' => $img['is_main']
            ];
            if ($img['is_main']) {
                $main_image = $fullUrl;
            }
        }

        if (!$main_image && count($prop_images) > 0) {
            $main_image = $prop_images[0]['url'];
        }

        $prop['images'] = $prop_images;
        $prop['main_image'] = $main_image;
        
        // Video URL mapping
        if (!empty($prop['video_url']) && strpos($prop['video_url'], 'http') !== 0) {
            $prop['video_url'] = rtrim(SITE_URL, '/') . '/' . ltrim($prop['video_url'], '/');
        }

        $result[] = $prop;
    }

    echo json_encode([
        'status' => 'success',
        'dealer' => $dealer_info,
        'properties' => $result
    ]);

} elseif ($action === 'create_property') {
    // --- CREATE PROPERTY ---
    $dealer_id = $data['dealer_id'] ?? '';
    
    if (empty($dealer_id) || empty($data['title']) || empty($data['price']) || empty($data['location'])) {
        echo json_encode(['status' => 'error', 'message' => 'Dealer ID, Title, Price, and Location are required.']);
        exit;
    }

    // Determine auto-feature status based on subscription
    $is_featured = 0;
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $checkPaid = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status = 'successful'");
        $checkPaid->execute([$dealer_id]);
        if ($checkPaid->fetchColumn() > 0) {
            $is_featured = 1;
        }
    } catch (Exception $e) {}

    $propData = [
        'dealer_id' => $dealer_id,
        'title' => htmlspecialchars($data['title']),
        'description' => htmlspecialchars($data['description'] ?? ''),
        'price' => floatval($data['price']),
        'currency' => $data['currency'] ?? 'ZMW',
        'property_type' => $data['property_type'] ?? 'house',
        'listing_purpose' => $data['listing_purpose'] ?? 'rent',
        'location' => htmlspecialchars($data['location']),
        'city' => htmlspecialchars($data['city'] ?? ''),
        'country' => htmlspecialchars($data['country'] ?? ''),
        'latitude' => !empty($data['latitude']) ? floatval($data['latitude']) : null,
        'longitude' => !empty($data['longitude']) ? floatval($data['longitude']) : null,
        'status' => 'available',
        'is_featured' => $is_featured,
        'amenities' => htmlspecialchars($data['amenities'] ?? ''),
        'video_url' => htmlspecialchars($data['video_url'] ?? ''),
        'verification_image' => null,
        'is_verified' => 0
    ];

    // Dynamic fields based on property type
    $type = $propData['property_type'];

    if ($type === 'house' || $type === 'apartment') {
        $propData['bedrooms'] = isset($data['bedrooms']) ? intval($data['bedrooms']) : null;
        $propData['bathrooms'] = isset($data['bathrooms']) ? intval($data['bathrooms']) : null;
    } elseif ($type === 'boarding_house') {
        $propData['capacity'] = !empty($data['capacity']) ? intval($data['capacity']) : null;
        $propData['people_per_room'] = !empty($data['people_per_room']) ? intval($data['people_per_room']) : null;
    } elseif ($type === 'event_space') {
        $propData['capacity'] = !empty($data['capacity']) ? intval($data['capacity']) : null;
        $propData['event_type'] = !empty($data['event_type']) ? htmlspecialchars($data['event_type']) : null;
        $propData['catering_available'] = !empty($data['catering_available']) ? 1 : 0;
        $propData['equipment_available'] = !empty($data['equipment_available']) ? 1 : 0;
    } elseif ($type === 'office' || $type === 'shop' || $type === 'warehouse') {
        $propData['size_sqm'] = isset($data['size_sqm']) ? floatval($data['size_sqm']) : null;
        $propData['rooms'] = isset($data['rooms']) ? intval($data['rooms']) : null;
    }

    $property_id = $propertyModel->create($propData);

    if ($property_id) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Property created successfully.',
            'property_id' => $property_id
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create property.']);
    }

} elseif ($action === 'upload_property_images') {
    // --- UPLOAD PROPERTY IMAGES & VIDEOS ---
    $property_id = $_POST['property_id'] ?? '';
    
    if (empty($property_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Property ID is required']);
        exit;
    }

    if (!isset($_FILES['images']) || empty($_FILES['images']['name'][0])) {
        echo json_encode(['status' => 'error', 'message' => 'No files provided']);
        exit;
    }

    $uploadDir = '../assets/images/properties/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [];
    // Added mp4 and mov for video support
    $allowedTypes = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov'];

    $is_main = 1; // Make first image main

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['images']['name'][$key]);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($fileExt, $allowedTypes)) {
                $newName = uniqid('prop_') . '_' . time() . '.' . $fileExt;
                $targetPath = $uploadDir . $newName;
                
                if (move_uploaded_file($tmp_name, $targetPath)) {
                    $dbPath = 'assets/images/properties/' . $newName;
                    
                    // If it's a video, update the video_url column instead of adding to property_images table
                    if ($fileExt === 'mp4' || $fileExt === 'mov') {
                        $pdo = (new Database())->connect();
                        $stmt = $pdo->prepare("UPDATE properties SET video_url = ? WHERE id = ?");
                        $stmt->execute([$dbPath, $property_id]);
                    } else {
                        // Standard Image
                        $propertyModel->addImage($property_id, $dbPath, $is_main);
                        $is_main = 0; // Only first image is main
                    }
                    
                    $fullUrl = rtrim(SITE_URL, '/') . '/' . $dbPath;
                    $uploadedFiles[] = $fullUrl;
                }
            }
        }
    }

    if (count($uploadedFiles) > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => count($uploadedFiles) . ' files uploaded successfully',
            'urls' => $uploadedFiles
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload any valid files']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}
