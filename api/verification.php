<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) $data = $_POST;
if (!$data) $data = $_GET;

$action = $data['action'] ?? '';
$user_id = $data['user_id'] ?? '';

if (empty($action)) {
    echo json_encode(['status' => 'error', 'message' => 'Action is required']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ==========================================
    // ACTION: CHECK IF USER IS VERIFIED (EMAIL)
    // ==========================================
    if ($action === 'check_email_verification') {
        if (empty($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT is_verified FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'status' => 'success',
                'is_verified' => (bool)$user['is_verified']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'User not found']);
        }
    }

    // ==========================================
    // ACTION: CHECK IF DEALER IS IDENTITY VERIFIED
    // ==========================================
    elseif ($action === 'check_dealer_verification') {
        if (empty($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }

        $stmt = $pdo->prepare("SELECT identity_verified FROM users WHERE id = ? AND role = 'dealer'");
        $stmt->execute([$user_id]);
        $dealer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dealer) {
            echo json_encode([
                'status' => 'success',
                'identity_verified' => (bool)$dealer['identity_verified']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Dealer not found or user is not a dealer']);
        }
    }

    // ==========================================
    // ACTION: UPLOAD DEALER VERIFICATION DOCUMENTS
    // ==========================================
    elseif ($action === 'upload_verification') {
        // Support both JSON user_id and $_POST user_id for multipart requests
        $user_id = $data['user_id'] ?? $_POST['user_id'] ?? '';

        if (empty($user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'User ID is required']);
            exit;
        }

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $error_code = $_FILES['document']['error'] ?? 'NO_FILE';
            echo json_encode(['status' => 'error', 'message' => 'Please upload a valid document. Error code: ' . $error_code]);
            exit;
        }

        // Correct directory based on website structure
        $uploadDir = '../../assets/images/dealer_docs/';  
        if (!is_dir($uploadDir)) {  
            mkdir($uploadDir, 0777, true);  
        }  

        $fileExtension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));  
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];  

        if (!in_array($fileExtension, $allowedExtensions)) {  
            echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.']);  
            exit;  
        }  

        $fileName = 'verify_' . $user_id . '_' . time() . '.' . $fileExtension;  
        $targetPath = $uploadDir . $fileName;  

        if (move_uploaded_file($_FILES['document']['tmp_name'], $targetPath)) {  
            // Path saved in database starting from assets/
            $dbPath = 'assets/images/dealer_docs/' . $fileName;  
             
            // Save to verification_doc (expected by admin) and verification_document (app compatibility)
            try {  
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_doc VARCHAR(255) NULL");  
                $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_document VARCHAR(255) NULL");  
            } catch(PDOException $e) {}  

            $stmt = $pdo->prepare("UPDATE users SET verification_doc = ?, verification_document = ?, identity_verified = 0 WHERE id = ? AND role = 'dealer'");  
             
            if ($stmt->execute([$dbPath, $dbPath, $user_id])) {  
                $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://houseforrent.site'; 
                echo json_encode([  
                    'status' => 'success',  
                    'message' => 'Verification document uploaded successfully. Please wait for admin approval.',  
                    'document_url' => $siteUrl . '/' . $dbPath  
                ]);  
            } else {  
                echo json_encode(['status' => 'error', 'message' => 'Failed to update user record']);  
            }  
        } else {  
            echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file on server']);  
        }  
        exit; 
    }
    
    else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
