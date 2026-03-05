<?php
require_once '../config/config.php';
require_once '../models/Favorite.php';

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['property_id'])) {
    $user_id = $_SESSION['user_id'];
    $property_id = $_POST['property_id'];

    $favoriteModel = new Favorite();
    $result = $favoriteModel->toggle($user_id, $property_id);

    if ($result) {
        echo json_encode(['success' => true, 'action' => $result]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>