<?php
require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 1. Add payment_method to rent_payments
    $sql1 = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'bank_transfer', 'mobile_money') DEFAULT 'bank_transfer'";
    $conn->exec($sql1);
    echo "payment_method added to rent_payments.<br>";
    
    // 2. Add bank_details to users (for dealers)
    $sql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_details TEXT NULL";
    $conn->exec($sql2);
    echo "bank_details added to users table.<br>";
    
} catch(PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>