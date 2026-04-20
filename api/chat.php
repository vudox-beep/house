<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

$db = new Database();
$conn = $db->connect();

if ($action === 'send') {
    $receiver_id = $_POST['receiver_id'] ?? null;
    $property_id = $_POST['property_id'] ?? null;
    $message = $_POST['message'] ?? '';

    if (!$receiver_id || empty(trim($message))) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, property_id, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $receiver_id, $property_id ?: null, trim($message)])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit;
}

if ($action === 'fetch') {
    $contact_id = $_GET['contact_id'] ?? null;
    if (!$contact_id) {
        echo json_encode(['success' => false, 'error' => 'Missing contact ID']);
        exit;
    }

    // Mark as read
    $stmtRead = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmtRead->execute([$contact_id, $user_id]);

    $stmt = $conn->prepare("
        SELECT m.*, u.name as sender_name, u.profile_image 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $contact_id, $contact_id, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

if ($action === 'contacts') {
    // Fetch all users the current user has chatted with
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            CASE 
                WHEN sender_id = ? THEN receiver_id 
                ELSE sender_id 
            END as contact_id
        FROM messages 
        WHERE sender_id = ? OR receiver_id = ?
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    $contactIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // If an explicit target user is requested via URL but not in history yet, add them
    $target_user = $_GET['target_user'] ?? null;
    if ($target_user && !in_array($target_user, $contactIds) && $target_user != $user_id) {
        $contactIds[] = $target_user;
    }

    if (empty($contactIds)) {
        echo json_encode(['success' => true, 'contacts' => []]);
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($contactIds), '?'));
    $stmtUsers = $conn->prepare("
        SELECT u.id, u.name, u.profile_image, u.role,
        (SELECT message FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
         ORDER BY created_at DESC LIMIT 1) as last_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
        FROM users u 
        WHERE u.id IN ($placeholders)
        ORDER BY last_time DESC
    ");
    
    $params = [$user_id, $user_id, $user_id, $user_id, $user_id];
    $params = array_merge($params, $contactIds);
    $stmtUsers->execute($params);
    $contacts = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'contacts' => $contacts]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
