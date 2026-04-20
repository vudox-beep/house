<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Must be POST']);
    exit;
}

$user_id = $_POST['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
    exit;
}

if (!isset($_FILES['verification_image']) || $_FILES['verification_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No image uploaded or upload error occurred. Make sure to send file as multipart/form-data with key "verification_image".']);
    exit;
}

$uploadDir = '../assets/images/dealer_docs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$fileExtension = strtolower(pathinfo($_FILES['verification_image']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.']);
    exit;
}

$fileName = time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['verification_image']['tmp_name'], $targetPath)) {
    // Relative path to store in database
    $dbPath = 'assets/images/dealer_docs/' . $fileName;
    
    $pdo = (new Database())->connect();
    
    // Update the user's document and set identity_verified to 0 (pending)
    $stmt = $pdo->prepare("UPDATE users SET verification_doc = ?, identity_verified = 0 WHERE id = ?");
    
    if ($stmt->execute([$dbPath, $user_id])) {
        // Build the full URL to the image
        $fullUrl = rtrim(SITE_URL, '/') . '/' . $dbPath;

        echo json_encode([
            'status' => 'success',
            'message' => 'Identity document uploaded successfully. Waiting for admin approval.',
            'path' => $dbPath,
            'full_url' => $fullUrl
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update database record']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file on server']);
}
