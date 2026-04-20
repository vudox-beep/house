<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;
if (!$data) $data = $_GET;

$action = $data['action'] ?? '';
$user_id = $data['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($action === 'toggle') {
        // --- ADD OR REMOVE FROM FAVORITES ---
        $property_id = $data['property_id'] ?? '';
        
        if (empty($property_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Property ID is required']);
            exit;
        }

        // Check if it already exists
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        $exists = $stmt->fetch();

        if ($exists) {
            // Remove from favorites
            $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
            $del->execute([$user_id, $property_id]);
            echo json_encode(['status' => 'success', 'message' => 'Removed from favorites', 'is_favorite' => false]);
        } else {
            // Add to favorites
            $ins = $pdo->prepare("INSERT INTO favorites (user_id, property_id) VALUES (?, ?)");
            $ins->execute([$user_id, $property_id]);
            echo json_encode(['status' => 'success', 'message' => 'Added to favorites', 'is_favorite' => true]);
        }
    } 
    elseif ($action === 'get_all') {
        // --- GET ALL FAVORITE PROPERTIES FOR A USER ---
        $stmt = $pdo->prepare("
            SELECT p.*, f.created_at as favorited_at 
            FROM favorites f
            JOIN properties p ON f.property_id = p.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        
        // Fetch images for each property
        foreach ($properties as $prop) {
            $imgStmt = $pdo->prepare("SELECT image_path, is_main FROM property_images WHERE property_id = ?");
            $imgStmt->execute([$prop['id']]);
            $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $main_image = null;
            
            foreach ($images as $img) {
                if ($img['is_main'] == 1) {
                    $main_image = rtrim(SITE_URL, '/') . '/' . ltrim($img['image_path'], '/');
                    break;
                }
            }
            
            // Fallback to first image if no main image
            if (!$main_image && count($images) > 0) {
                $main_image = rtrim(SITE_URL, '/') . '/' . ltrim($images[0]['image_path'], '/');
            }

            $prop['main_image'] = $main_image;
            
            // Format video url if exists
            if (!empty($prop['video_url']) && strpos($prop['video_url'], 'http') !== 0) {
                $prop['video_url'] = rtrim(SITE_URL, '/') . '/' . ltrim($prop['video_url'], '/');
            }

            $result[] = $prop;
        }

        echo json_encode([
            'status' => 'success',
            'data' => $result
        ]);
    } 
    elseif ($action === 'check') {
        // --- CHECK IF A SPECIFIC PROPERTY IS FAVORITED ---
        $property_id = $data['property_id'] ?? '';
        
        if (empty($property_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Property ID is required']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->execute([$user_id, $property_id]);
        $exists = $stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'is_favorite' => $exists ? true : false
        ]);
    } 
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
