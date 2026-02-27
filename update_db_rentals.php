<?php
require_once 'config/config.php';
$db = new Database();
$conn = $db->connect();
try {
    $conn->exec("ALTER TABLE rentals ADD COLUMN payment_reference VARCHAR(20) DEFAULT NULL");
    echo "Column added successfully";
} catch(Exception $e) {
    echo $e->getMessage();
}
?>