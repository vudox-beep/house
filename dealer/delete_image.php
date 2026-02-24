<?php
require_once '../config/config.php';
require_once '../models/Property.php';
require_once '../models/User.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dealer') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['prop_id'])) {
    $image_id = $_GET['id'];
    $property_id = $_GET['prop_id'];

    $propertyModel = new Property();
    $property = $propertyModel->getById($property_id);

    // Verify ownership
    if ($property && $property['dealer_id'] == $_SESSION['user_id']) {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        
        // Get image path to delete file
        $stmt = $pdo->prepare("SELECT image_path FROM property_images WHERE id = ?");
        $stmt->execute([$image_id]);
        $image = $stmt->fetchColumn();

        if ($image) {
            // Delete from DB
            $delStmt = $pdo->prepare("DELETE FROM property_images WHERE id = ?");
            if ($delStmt->execute([$image_id])) {
                // Delete file
                $filePath = '../' . $image;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }
    
    header("Location: upload_images.php?id=" . $property_id);
    exit();
} else {
    header("Location: properties.php");
    exit();
}
?>