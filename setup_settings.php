<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create table
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value VARCHAR(255) NOT NULL
    )";
    $pdo->exec($sql);
    echo "Table 'settings' created or already exists.<br>";

    // Insert default values
    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    
    // Default: Free Trial Enabled (1), Duration (7 days)
    $stmt->execute(['enable_free_trial', '1']);
    $stmt->execute(['free_trial_duration', '7']);
    
    echo "Default settings inserted.<br>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
