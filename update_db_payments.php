<?php
require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 1. Add or update payment_method on rent_payments
    $sql1 = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS payment_method ENUM('cash', 'bank_transfer', 'mobile_money', 'lenco') DEFAULT 'bank_transfer'";
    $conn->exec($sql1);
    echo "payment_method added to rent_payments.<br>";

    $sql1b = "ALTER TABLE rent_payments MODIFY COLUMN payment_method ENUM('cash', 'bank_transfer', 'mobile_money', 'lenco') DEFAULT 'bank_transfer'";
    $conn->exec($sql1b);
    echo "payment_method updated for Lenco support.<br>";

    $sql1c = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS reference VARCHAR(255) DEFAULT NULL AFTER months_paid";
    $conn->exec($sql1c);
    echo "reference added to rent_payments.<br>";

    $sql1d = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS lenco_reference VARCHAR(255) DEFAULT NULL AFTER reference";
    $conn->exec($sql1d);
    echo "lenco_reference added to rent_payments.<br>";
    
    // 2. Add bank_details to users (for dealers)
    $sql2 = "ALTER TABLE users ADD COLUMN IF NOT EXISTS bank_details TEXT NULL";
    $conn->exec($sql2);
    echo "bank_details added to users table.<br>";
    
} catch(PDOException $e) {
    echo "Error updating tables: " . $e->getMessage();
}
?>
