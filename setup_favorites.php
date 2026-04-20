<?php
require_once 'config/config.php';
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'favorites'");
    if ($stmt->rowCount() == 0) {
        $stmt = $pdo->query("SHOW TABLES LIKE 'saved_properties'");
        if ($stmt->rowCount() == 0) {
            // Create favorites table if it doesn't exist
            $pdo->exec("CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                property_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY user_prop (user_id, property_id)
            )");
            echo "Created 'favorites' table.\n";
        } else {
            echo "Table 'saved_properties' exists.\n";
        }
    } else {
        echo "Table 'favorites' exists.\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
