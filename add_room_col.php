<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add room_number column to rentals table
    $sql = "SHOW COLUMNS FROM rentals LIKE 'room_number'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE rentals ADD COLUMN room_number VARCHAR(50) DEFAULT NULL AFTER property_id");
        echo "Added 'room_number' column to rentals table.<br>";
    } else {
        echo "'room_number' column already exists.<br>";
    }

    echo "<br><a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>