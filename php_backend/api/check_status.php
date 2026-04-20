<?php  
  header('Content-Type: application/json');  
  header('Access-Control-Allow-Origin: *');  
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');  
  header('Access-Control-Allow-Headers: Content-Type, Authorization');  
    
  // FIXED PATHS: These are relative to php_backend/api/check_status.php 
  require_once '../../config/db.php'; 
  require_once '../../includes/auth_check.php'; 
   
  // Use existing database connection from config if available, otherwise create one 
  try { 
      $db = new Database(); 
      $conn = $db->connect(); 
  } catch (Exception $e) { 
      echo json_encode(['status' => 'error', 'message' => 'Database connection failed']); 
      exit; 
  } 
    
  // Handle preflight OPTIONS request for CORS  
  if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {  
      http_response_code(200);  
      exit();  
  }  
    
  $data = json_decode(file_get_contents("php://input"), true);  
  if (empty($data)) $data = $_POST;  
  if (empty($data)) $data = $_GET;  
    
  $action = $data['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';  
  $user_id = $data['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? '';  
    
  // Fallback to token if user_id is not explicitly provided  
  if (empty($user_id)) {  
      $headers = function_exists('getallheaders') ? getallheaders() : []; 
      $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''); 
      if (!empty($authorization)) {  
          $token = str_replace('Bearer ', '', $authorization);  
          $tokenParts = explode('.', $token);  
          if (count($tokenParts) == 3) {  
              $payload = json_decode(base64_decode($tokenParts[1]), true);  
              if (isset($payload['id'])) {  
                  $user_id = $payload['id'];  
              }  
          }  
      }  
  }  
    
  if (empty($user_id)) {  
      echo json_encode(['status' => 'error', 'message' => 'User ID required', 'is_locked' => true]);  
      exit;  
  }  
    
  try {  
      // ==========================================   
      // ACTION: CHECK IF USER IS VERIFIED (EMAIL)   
      // ==========================================   
      if ($action === 'check_email_verification') {   
          $stmt = $conn->prepare("SELECT verification_token FROM users WHERE id = ?");   
          $stmt->execute([$user_id]);   
          $user = $stmt->fetch(PDO::FETCH_ASSOC);   
    
          if ($user) {   
              // In your DB, NULL verification_token means verified 
              $is_verified = empty($user['verification_token']); 
              echo json_encode([   
                  'status' => 'success',   
                  'is_verified' => $is_verified 
              ]);   
          } else {   
              echo json_encode(['status' => 'error', 'message' => 'User not found']);   
          }   
          exit;  
      }   
    
      // ==========================================   
      // ACTION: CHECK IF DEALER IS IDENTITY VERIFIED   
      // ==========================================   
      elseif ($action === 'check_dealer_verification') {   
          $stmt = $conn->prepare("SELECT identity_verified FROM users WHERE id = ? AND role = 'dealer'");   
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
          exit;  
      }   
     
      // ==========================================   
      // ACTION: UPLOAD DEALER VERIFICATION DOCUMENTS   
      // ==========================================   
      elseif ($action === 'upload_verification') {   
          $user_id = $data['user_id'] ?? $_POST['user_id'] ?? $_GET['user_id'] ?? '';   
     
          if (empty($user_id)) {   
              echo json_encode(['status' => 'error', 'message' => 'User ID is required']);   
              exit;   
          } 
   
          if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {  
              $err = $_FILES['document']['error'] ?? 'NO_FILE'; 
              echo json_encode(['status' => 'error', 'message' => "Please upload a valid document. Error code: $err"]);  
              exit;  
          }  
   
          // DYNAMIC ABSOLUTE PATH: Navigate out of php_backend/api to root, then to assets 
          $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/'); 
          // Pointing directly to the root assets folder 
          $uploadDir = $documentRoot . '/assets/images/dealer_docs/';  
           
          // Fallback if DOCUMENT_ROOT fails or isn't set up correctly 
          if (!is_dir($documentRoot . '/assets/')) { 
              $uploadDir = dirname(__FILE__) . '/../../../assets/images/dealer_docs/'; 
          } 
   
          if (!is_dir($uploadDir)) {  
              if (!mkdir($uploadDir, 0755, true)) { 
                  echo json_encode(['status' => 'error', 'message' => 'Failed to create upload directory.']); 
                  exit; 
              } 
          }  
   
          $fileExtension = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));  
          $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];  
   
          if (!in_array($fileExtension, $allowedExtensions)) {  
              echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.']);  
              exit;  
          }  
   
          $fileName = 'verify_' . $user_id . '_' . time() . '.' . $fileExtension;    
          $absoluteTargetPath = $uploadDir . $fileName;  
   
          if (move_uploaded_file($_FILES['document']['tmp_name'], $absoluteTargetPath)) {    
              chmod($absoluteTargetPath, 0644); // Ensure file is readable by the web server 
               
              // PATH FOR DATABASE: Starts from assets/ so the website can see it 
              $dbPath = 'assets/images/dealer_docs/' . $fileName;    
                  
              // Update BOTH columns just in case, and set status to pending (0) 
              $stmt = $conn->prepare("UPDATE users SET verification_doc = ?, identity_verified = 0 WHERE id = ? AND role = 'dealer'");    
                  
              if ($stmt->execute([$dbPath, $user_id])) {    
                  echo json_encode([    
                      'status' => 'success',    
                      'message' => 'Verification document uploaded successfully. Please wait for admin approval.',    
                      'document_url' => 'https://houseforrent.site/' . $dbPath    
                  ]);    
              } else {    
                  echo json_encode(['status' => 'error', 'message' => 'Failed to update database record']);    
              }    
          } else {    
              echo json_encode(['status' => 'error', 'message' => "Failed to save file. Ensure folder permissions are correct."]);    
          }    
          exit;   
      }  
     
      // Default: Return full dashboard status  
      $stmtUser = $conn->prepare("SELECT name, role, identity_verified, verification_doc FROM users WHERE id = ?");  
      $stmtUser->execute([$user_id]);  
      $user = $stmtUser->fetch(PDO::FETCH_ASSOC);  
     
      if (!$user) {  
          echo json_encode(['status' => 'error', 'message' => 'User not found', 'is_locked' => true]);  
          exit;  
      }  
     
      if ($user['role'] !== 'dealer') {  
          echo json_encode(['status' => 'success', 'message' => 'User is not a dealer', 'is_dealer' => false, 'is_locked' => false]);  
          exit;  
      }  
     
      $stmt = $conn->prepare("SELECT subscription_status, subscription_expiry FROM dealers WHERE user_id = ?");  
      $stmt->execute([$user_id]);  
      $dealerData = $stmt->fetch(PDO::FETCH_ASSOC);  
     
      $sub_status = 'inactive';  
      $expiry = null;  
     
      if ($dealerData) {  
          $sub_status = strtolower(trim((string)($dealerData['subscription_status'] ?? 'inactive')));  
          $expiry = $dealerData['subscription_expiry'];  
             
          if ($sub_status === 'active' && !empty($expiry) && strtotime($expiry) < time()) {  
              $sub_status = 'expired';  
              $upd = $conn->prepare("UPDATE dealers SET subscription_status = 'expired' WHERE user_id = ?");  
              $upd->execute([$user_id]);  
          }  
      }  
     
      $is_locked = ($sub_status !== 'active');  
      $lock_message = ''; 
      if ($sub_status === 'expired') { 
          $lock_message = 'Your subscription has expired. Please make a payment to continue using the app.'; 
      } elseif ($is_locked) { 
          $lock_message = 'Your subscription is inactive. Please make a payment to continue using the app.'; 
      } 
     
      // Advanced identity status  
      $identity_status = 'verified';  
      if ((int)$user['identity_verified'] === 0) {  
          if (!empty($user['verification_doc'])) {  
              $identity_status = 'pending';  
          } else {  
              $identity_status = 'unverified';  
          }  
      }  
     
      $active_tenants = 0; 
      try { 
          $stmtTenants = $conn->prepare("SELECT COUNT(*) as total FROM rentals WHERE dealer_id = ? AND status = 'active'"); 
          $stmtTenants->execute([$user_id]); 
          $tenantsData = $stmtTenants->fetch(PDO::FETCH_ASSOC); 
          $active_tenants = $tenantsData['total'] ?? 0; 
      } catch (PDOException $e) {} 
   
      $total_views = 0; 
      try { 
          $stmtViews = $conn->prepare("SELECT SUM(views) as total FROM properties WHERE dealer_id = ?"); 
          $stmtViews->execute([$user_id]); 
          $viewsData = $stmtViews->fetch(PDO::FETCH_ASSOC); 
          $total_views = $viewsData['total'] ?? 0; 
      } catch (PDOException $e) {} 
   
      $recent_payments = []; 
      try { 
          $stmtPayments = $conn->prepare(" 
              SELECT id, reference, amount, currency, status, payment_method, message as description, created_at, updated_at 
              FROM transactions 
              WHERE user_id = ? 
              ORDER BY created_at DESC 
              LIMIT 5 
          "); 
          $stmtPayments->execute([$user_id]); 
          $recent_payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC); 
          if (!$recent_payments) { 
              $recent_payments = []; 
          } 
      } catch (PDOException $e) { 
          $recent_payments = []; 
      } 
   
      echo json_encode([  
          'status' => 'success',  
          'is_dealer' => true,  
          'name' => $user['name'],  
          'subscription_status' => $sub_status,  
          'subscription_expiry' => $expiry,  
          'identity_verified' => $user['identity_verified'],  
          'verification_document' => $user['verification_doc'],  
          'identity_status' => $identity_status,  
          'active_tenants' => $active_tenants, 
          'total_views' => $total_views, 
          'recent_payments' => $recent_payments, 
          'is_locked' => $is_locked,  
          'lock_message' => $lock_message  
      ]);  
     
  } catch (PDOException $e) {   
      echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);   
  }  
  ?>