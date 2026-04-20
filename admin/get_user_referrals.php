<?php
require_once '../config/config.php';
require_once '../models/Referral.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$userId = (int)($_GET['user_id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit();
}

try {
    // Clear any previous output buffers to ensure only JSON is returned
    while (ob_get_level()) ob_end_clean();
    
    $referralModel = new Referral();
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get stats
    $stats = $referralModel->getDealerReferralStats($userId);
    
    // Get list of referred users
    $stmt = $db->prepare("
        SELECT u.id, u.name, u.email, u.created_at, u.referral_registered_at,
               d.subscription_status
        FROM users u
        LEFT JOIN dealers d ON u.id = d.user_id
        WHERE u.referred_by_user_id = :id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute([':id' => $userId]);
    $referrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'stats' => $stats,
            'referrals' => $referrals
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
