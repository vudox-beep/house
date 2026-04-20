<?php
require_once '../config/config.php';
require_once '../includes/LencoAPI.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access denied");
}

header('Content-Type: application/json');

$lenco = new LencoAPI();

// Debug: Show raw responses from both endpoints
$accounts_response = $lenco->getAccounts();  // New: /accounts (plural)
$balance_response = $lenco->getBalance();     // Uses getAccounts() internally now

echo json_encode([
    'raw_accounts_response' => $accounts_response,
    'processed_balance_response' => $balance_response,
], JSON_PRETTY_PRINT);
