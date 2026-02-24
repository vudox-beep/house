<?php
require_once 'config/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $property_id = $_POST['property_id'] ?? null;
    $reason = $_POST['reason'] ?? '';
    $details = $_POST['details'] ?? '';
    $user_id = $_SESSION['user_id'] ?? null; // Null if guest

    if (!$property_id || !$reason) {
        // Redirect back with error
        header("Location: property_details.php?id=$property_id&error=Please fill in all fields");
        exit;
    }

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("INSERT INTO property_reports (property_id, user_id, reason, details) VALUES (:pid, :uid, :reason, :details)");
        $stmt->execute([
            ':pid' => $property_id,
            ':uid' => $user_id,
            ':reason' => $reason,
            ':details' => $details
        ]);

        // Redirect with success
        header("Location: property_details.php?id=$property_id&success=Report submitted successfully. We will review it shortly.");
        exit;

    } catch (PDOException $e) {
        // Log error and redirect
        error_log("Report Error: " . $e->getMessage());
        header("Location: property_details.php?id=$property_id&error=Something went wrong. Please try again.");
        exit;
    }

} else {
    header("Location: index.php");
    exit;
}
?>
