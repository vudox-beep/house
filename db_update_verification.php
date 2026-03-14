<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking database columns...\n";

    // Check if identity_verified column exists in users table
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'identity_verified'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Adding 'identity_verified' column to users table...\n";
        // 0 = Pending/Unverified, 1 = Verified, 2 = Rejected
        $sql = "ALTER TABLE users ADD COLUMN identity_verified TINYINT(1) DEFAULT 0 AFTER is_verified";
        $pdo->exec($sql);
        echo "Column 'identity_verified' added successfully.\n";
    } else {
        echo "Column 'identity_verified' already exists.\n";
    }

    // Check if verification_doc column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'verification_doc'");
    $existsDoc = $stmt->fetch();

    if (!$existsDoc) {
        echo "Adding 'verification_doc' column to users table...\n";
        $sql = "ALTER TABLE users ADD COLUMN verification_doc VARCHAR(255) NULL AFTER identity_verified";
        $pdo->exec($sql);
        echo "Column 'verification_doc' added successfully.\n";
    } else {
        echo "Column 'verification_doc' already exists.\n";
    }
    
    // Sync existing data: If is_verified=1, set identity_verified=1 (Migration)
    echo "Syncing legacy verification status...\n";
    $sql = "UPDATE users SET identity_verified = 1 WHERE is_verified = 1 AND identity_verified = 0";
    $pdo->exec($sql);
    echo "Sync complete.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>