<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add is_verified column to users table if not exists
    $sql = "SHOW COLUMNS FROM users LIKE 'is_verified'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER role");
        echo "Added 'is_verified' column to users table.<br>";
    } else {
        echo "'is_verified' column already exists.<br>";
    }
    
    // Auto-verify existing ADMINs
    $pdo->exec("UPDATE users SET is_verified = 1 WHERE role = 'admin'");
    echo "Verified all admins.<br>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>