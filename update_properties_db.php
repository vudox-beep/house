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

    // Add is_featured
    if (!columnExists($pdo, 'properties', 'is_featured')) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN is_featured TINYINT(1) DEFAULT 0");
        echo "Added column: is_featured<br>";
    } else {
        echo "Column exists: is_featured<br>";
    }

    // Randomly feature some properties if none are featured
    $stmt = $pdo->query("SELECT COUNT(*) FROM properties WHERE is_featured = 1");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("UPDATE properties SET is_featured = 1 ORDER BY RAND() LIMIT 3");
        echo "Randomly featured 3 properties for demo.<br>";
    }

    // Add latitude
    if (!columnExists($pdo, 'properties', 'latitude')) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN latitude DECIMAL(10, 8) NULL");
        echo "Added column: latitude<br>";
    } else {
        echo "Column exists: latitude<br>";
    }

    // Add longitude
    if (!columnExists($pdo, 'properties', 'longitude')) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN longitude DECIMAL(11, 8) NULL");
        echo "Added column: longitude<br>";
    } else {
        echo "Column exists: longitude<br>";
    }

    // Update demo data with lat/long if empty
    $pdo->exec("UPDATE properties SET latitude = -15.3875, longitude = 28.3228 WHERE latitude IS NULL"); // Default to Lusaka coordinates

    echo "Properties table updated successfully. <a href='index.php'>Go Home</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
