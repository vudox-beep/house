<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add 'type' column to property_images if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM property_images")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('type', $columns)) {
        $pdo->exec("ALTER TABLE property_images ADD COLUMN type ENUM('image', 'video') DEFAULT 'image'");
        echo "Added 'type' column to property_images.<br>";
    } else {
        echo "'type' column already exists in property_images.<br>";
    }

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>