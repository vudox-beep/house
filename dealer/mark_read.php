<?php
require_once '../config/config.php';

// Session Check
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notification_id'])) {
    $notifId = $_POST['notification_id'];
    $userId = $_SESSION['user_id'];

    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert into notification_reads table
        // Use INSERT IGNORE to handle duplicate clicks gracefully
        $stmt = $pdo->prepare("INSERT IGNORE INTO notification_reads (notification_id, user_id) VALUES (?, ?)");
        $stmt->execute([$notifId, $userId]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid Request']);
}
?>