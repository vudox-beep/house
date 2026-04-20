<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;

$action = $data['action'] ?? '';

if (empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Action is required']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ==========================================
    // ACTION: SEND INQUIRY TO DEALER
    // ==========================================
    if ($action === 'send_inquiry') {
        $property_id = $data['property_id'] ?? '';
        $dealer_id = $data['dealer_id'] ?? '';
        $user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $name = htmlspecialchars($data['name'] ?? '');
        $email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $phone = htmlspecialchars($data['phone'] ?? '');
        $message = htmlspecialchars($data['message'] ?? '');

        if (empty($property_id) || empty($dealer_id) || empty($name) || empty($email) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields (Property, Dealer, Name, Email, Message) are required']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO inquiries (property_id, dealer_id, user_id, name, email, phone, message) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$property_id, $dealer_id, $user_id, $name, $email, $phone, $message])) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Your inquiry has been sent to the dealer successfully.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send inquiry']);
        }
    }

    // ==========================================
    // ACTION: REPORT FAKE LISTING TO ADMIN
    // ==========================================
    elseif ($action === 'report_property') {
        $property_id = $data['property_id'] ?? '';
        $user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $reason = htmlspecialchars($data['reason'] ?? '');
        $details = htmlspecialchars($data['details'] ?? '');

        if (empty($property_id) || empty($reason)) {
            echo json_encode(['status' => 'error', 'message' => 'Property ID and Reason are required to file a report']);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO property_reports (property_id, user_id, reason, details) 
            VALUES (?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$property_id, $user_id, $reason, $details])) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Thank you. The listing has been reported to the admins for review.'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit report']);
        }
    } 
    
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
