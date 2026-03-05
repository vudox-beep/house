<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add identity_verified column
    // 0 = Unverified (Blocked from Add Property)
    // 1 = Verified (Allowed)
    // 2 = Pending (Submitted but waiting)
    
    $sql = "SHOW COLUMNS FROM users LIKE 'identity_verified'";
    $stmt = $pdo->query($sql);
    
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN identity_verified TINYINT(1) DEFAULT 0 AFTER is_verified");
        echo "Added 'identity_verified' column.<br>";
        
        // IMPORTANT: Set existing dealers to 1 (Verified) so they don't get blocked
        // New dealers will default to 0
        $pdo->exec("UPDATE users SET identity_verified = 1 WHERE role = 'dealer'");
        echo "Updated existing dealers to be identity_verified = 1.<br>";
        
    } else {
        echo "'identity_verified' column already exists.<br>";
    }

    echo "<br><a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>