<?php
require_once '../config/config.php';
require_once '../models/Property.php';

// Check if user is logged in and is a dealer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'dealer') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $property_id = $_POST['property_id'];
    $dealer_id = $_SESSION['user_id'];

    $propertyModel = new Property();
    
    // Verify ownership and delete
    if ($propertyModel->delete($property_id, $dealer_id)) {
        // Redirect with success message
        header("Location: properties.php?success=" . urlencode("Property deleted successfully."));
    } else {
        // Redirect with error message
        header("Location: properties.php?error=" . urlencode("Failed to delete property or you do not have permission."));
    }
} else {
    // Invalid request
    header("Location: properties.php");
}
exit;
?>