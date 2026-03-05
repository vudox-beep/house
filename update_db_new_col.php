<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add id_verification_status column
    $sql = "SHOW COLUMNS FROM users LIKE 'id_verification_status'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        // Create column with default 'unverified'
        // For existing dealers, we can default them to 'verified' if you want, 
        // OR 'unverified' to force them to upload ID.
        // Let's default to 'unverified' for safety, but you can run an update later.
        
        $pdo->exec("ALTER TABLE users ADD COLUMN id_verification_status ENUM('unverified', 'pending', 'verified', 'rejected') DEFAULT 'unverified' AFTER verification_doc");
        echo "Added 'id_verification_status' column to users table.<br>";
        
        // OPTIONAL: Migrating existing 'is_verified=1' users to 'verified' status
        // so they don't get blocked suddenly.
        $pdo->exec("UPDATE users SET id_verification_status = 'verified' WHERE is_verified = 1 AND role = 'dealer'");
        echo "Migrated existing verified dealers to new status.<br>";
        
    } else {
        echo "'id_verification_status' column already exists.<br>";
    }

    echo "<br><a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>