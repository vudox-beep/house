<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to database.<br>";

    // Helper function to check if column exists
    function columnExists($pdo, $table, $column) {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM $table LIKE :column");
        $stmt->execute([':column' => $column]);
        return $stmt->fetch() !== false;
    }

    // Add verification_token
    if (!columnExists($pdo, 'users', 'verification_token')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL");
        echo "Added column: verification_token<br>";
    } else {
        echo "Column exists: verification_token<br>";
    }

    // Add token_expiry
    if (!columnExists($pdo, 'users', 'token_expiry')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN token_expiry TIMESTAMP NULL");
        echo "Added column: token_expiry<br>";
    } else {
        echo "Column exists: token_expiry<br>";
    }

    // Add google_id
    if (!columnExists($pdo, 'users', 'google_id')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) NULL");
        echo "Added column: google_id<br>";
    } else {
        echo "Column exists: google_id<br>";
    }
    
    // Add is_verified (if not already there, some logic assumes it)
    if (!columnExists($pdo, 'users', 'is_verified')) {
        $pdo->exec("ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0");
        echo "Added column: is_verified<br>";
    } else {
        echo "Column exists: is_verified<br>";
    }

    // Modify password to be nullable
    $pdo->exec("ALTER TABLE users MODIFY COLUMN password VARCHAR(255) NULL");
    echo "Modified column: password (made nullable)<br>";

    echo "Database schema check/update complete. <a href='register.php'>Go to Register</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
