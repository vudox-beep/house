<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add verification_image column
    $sql = "SHOW COLUMNS FROM properties LIKE 'verification_image'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN verification_image VARCHAR(255) DEFAULT NULL AFTER status");
        echo "Added 'verification_image' column to properties table.<br>";
    } else {
        echo "'verification_image' column already exists.<br>";
    }

    // Add is_verified column
    $sql = "SHOW COLUMNS FROM properties LIKE 'is_verified'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        // We already have 'status', but 'is_verified' can track if the admin approved the photo
        // status='available' might only happen AFTER verification
        // Let's add it for explicit tracking
        $pdo->exec("ALTER TABLE properties ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER verification_image");
        echo "Added 'is_verified' column to properties table.<br>";
    } else {
        echo "'is_verified' column already exists.<br>";
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>