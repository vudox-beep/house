<?php
require_once 'config/db.php';
try {
    $db = new Database();
    $conn = $db->connect();
    
    $sql = "CREATE TABLE IF NOT EXISTS leads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        dealer_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
        FOREIGN KEY (dealer_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql);
    echo "Table 'leads' created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>