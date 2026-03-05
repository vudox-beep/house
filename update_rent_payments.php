<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn = $database->connect();

    $sql = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS months_paid INT DEFAULT 1;";
    $conn->exec($sql);
    echo "Column 'months_paid' added successfully.";
} catch (PDOException $e) {
    echo "Error adding column: " . $e->getMessage();
}
?>