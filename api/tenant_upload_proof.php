<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/config.php';
require_once '../models/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method. Must be POST']);
    exit;
}

$action = $_POST['action'] ?? 'upload_proof';

if ($action === 'get_history') {
    $tenant_id = $_POST['tenant_id'] ?? '';
    
    if (empty($tenant_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Tenant ID is required']);
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    // Fetch tenant's active rental details
    $sql_rental = "SELECT r.*, p.title as property_title, p.location, d.company_name as dealer_company
                   FROM rentals r
                   JOIN properties p ON r.property_id = p.id
                   JOIN dealers d ON r.dealer_id = d.user_id
                   WHERE r.tenant_id = :tid AND r.status = 'active'
                   ORDER BY r.created_at DESC LIMIT 1";
    $stmt_rental = $conn->prepare($sql_rental);
    $stmt_rental->execute([':tid' => $tenant_id]);
    $rental_info = $stmt_rental->fetch(PDO::FETCH_ASSOC);

    // Fetch payment history for this tenant
    $sql_history = "SELECT rp.*, p.title as property_title, r.payment_reference
                    FROM rent_payments rp
                    JOIN rentals r ON rp.rental_id = r.id
                    JOIN properties p ON r.property_id = p.id
                    WHERE rp.tenant_id = :tid 
                    ORDER BY rp.created_at DESC";
    $stmt_history = $conn->prepare($sql_history);
    $stmt_history->execute([':tid' => $tenant_id]);
    $history_payments = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

    foreach ($history_payments as &$payment) {
        if (!empty($payment['receipt_image']) && strpos($payment['receipt_image'], 'http') !== 0) {
            $payment['receipt_image'] = rtrim(SITE_URL, '/') . '/' . ltrim($payment['receipt_image'], '/');
        }
        // Also map it to proof_of_payment so your flutter app doesn't break
        $payment['proof_of_payment'] = $payment['receipt_image'] ?? null;
    }
    unset($payment);

    echo json_encode([
        'status' => 'success',
        'rental_info' => $rental_info,
        'payment_history' => $history_payments
    ]);
    exit;
}

// Default action: upload_proof
$tenant_id = $_POST['tenant_id'] ?? '';
$rental_id = $_POST['rental_id'] ?? '';
$month_year = $_POST['month_year'] ?? ''; // e.g. "April 2024"
$amount = $_POST['amount'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'bank_transfer'; // Optional: bank_transfer, mobile_money, cash

if (empty($tenant_id) || empty($rental_id) || empty($month_year) || empty($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'Tenant ID, Rental ID, Month/Year, and Amount are required']);
    exit;
}

if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Please upload a proof of payment image']);
    exit;
}

$uploadDir = '../assets/images/proofs/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$fileExtension = strtolower(pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only JPG, PNG, and PDF are allowed.']);
    exit;
}

$fileName = 'proof_' . $tenant_id . '_' . time() . '.' . $fileExtension;
$targetPath = $uploadDir . $fileName;

if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $targetPath)) {
    $dbPath = 'assets/images/proofs/' . $fileName;
    $fullUrl = rtrim(SITE_URL, '/') . '/' . $dbPath;
    
    $db = new Database();
    $conn = $db->connect();
    
    // Insert pending payment record
    $sql = "INSERT INTO rent_payments (rental_id, tenant_id, month_year, amount, currency, status, payment_method, receipt_image, months_paid) 
            VALUES (:rid, :tid, :my, :amt, 'ZMW', 'pending', :method, :proof, 1)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt->execute([
        ':rid' => $rental_id,
        ':tid' => $tenant_id,
        ':my' => $month_year,
        ':amt' => $amount,
        ':method' => $payment_method,
        ':proof' => $dbPath
    ])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Proof of payment uploaded successfully. Waiting for dealer approval.',
            'proof_url' => $fullUrl
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error. Could not save payment.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file on server']);
}
