<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add verification_doc column
    $sql = "SHOW COLUMNS FROM users LIKE 'verification_doc'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_doc VARCHAR(255) DEFAULT NULL AFTER is_verified");
        echo "Added 'verification_doc' column to users table.<br>";
    } else {
        echo "'verification_doc' column already exists.<br>";
    }

    echo "<br><a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>