<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data)) {
    $data = $_POST;
}

$property_id = $data['property_id'] ?? '';
$dealer_id = $data['dealer_id'] ?? '';
$user_id = $data['user_id'] ?? null;
$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$message = trim($data['message'] ?? '');

if (empty($property_id) || empty($dealer_id) || empty($name) || empty($email) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'property_id, dealer_id, name, email and message are required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare("INSERT INTO inquiries (property_id, dealer_id, user_id, name, email, phone, message) VALUES (:property_id, :dealer_id, :user_id, :name, :email, :phone, :message)");
    $ok = $stmt->execute([
        ':property_id' => $property_id,
        ':dealer_id' => $dealer_id,
        ':user_id' => $user_id,
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message
    ]);

    if (!$ok) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to send inquiry']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Inquiry sent successfully',
        'inquiry_id' => (int)$conn->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
