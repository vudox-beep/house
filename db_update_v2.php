<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Add profile_image, reset_token, reset_expires to users table
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('profile_image', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");
        echo "Added 'profile_image' column to users.<br>";
    }
    
    if (!in_array('reset_token', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) DEFAULT NULL");
        echo "Added 'reset_token' column to users.<br>";
    }

    if (!in_array('reset_expires', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN reset_expires DATETIME DEFAULT NULL");
        echo "Added 'reset_expires' column to users.<br>";
    }

    // 2. Create tenancy_history table
    $pdo->exec("CREATE TABLE IF NOT EXISTS tenancy_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        tenant_name VARCHAR(255) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        condition_start TEXT,
        condition_end TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    )");
    echo "Created/Verified 'tenancy_history' table.<br>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>