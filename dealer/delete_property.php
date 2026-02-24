<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../models/Property.php';

check_dealer();

if (isset($_GET['id'])) {
    $propertyModel = new Property();
    $property_id = $_GET['id'];
    $dealer_id = $_SESSION['user_id'];

    if ($propertyModel->delete($property_id, $dealer_id)) {
        header("Location: dashboard.php?deleted=true");
    } else {
        header("Location: dashboard.php?error=failed_delete");
    }
} else {
    header("Location: dashboard.php");
}
exit;
?>
