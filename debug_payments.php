<?php
require_once 'config/db.php';
$db = new Database();
$conn = $db->connect();
$stmt = $conn->query("SELECT * FROM rent_payments ORDER BY created_at DESC LIMIT 5");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($payments);
?>