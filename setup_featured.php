<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM properties LIKE 'is_featured'");
    if ($stmt->rowCount() == 0) {
        // Add column
        $pdo->exec("ALTER TABLE properties ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status");
        echo "Column 'is_featured' added to properties table.<br>";
    } else {
        echo "Column 'is_featured' already exists.<br>";
    }

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
