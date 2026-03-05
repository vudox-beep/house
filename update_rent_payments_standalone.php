<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Try empty first
$dbname = 'rent';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS months_paid INT DEFAULT 1;";
    $conn->exec($sql);
    echo "Column 'months_paid' added successfully.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    // Try with '.' password just in case
    if ($pass === '') {
        try {
            $pass = '.';
            $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $sql = "ALTER TABLE rent_payments ADD COLUMN IF NOT EXISTS months_paid INT DEFAULT 1;";
            $conn->exec($sql);
            echo "Column 'months_paid' added successfully (with password).\n";
        } catch (PDOException $e2) {
             echo "Connection failed with password too: " . $e2->getMessage() . "\n";
        }
    }
}
?>