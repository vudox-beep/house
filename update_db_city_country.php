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

    // Add city
    if (!columnExists($pdo, 'properties', 'city')) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN city VARCHAR(100) NULL AFTER location");
        echo "Added column: city<br>";
    } else {
        echo "Column exists: city<br>";
    }

    // Add country
    if (!columnExists($pdo, 'properties', 'country')) {
        $pdo->exec("ALTER TABLE properties ADD COLUMN country VARCHAR(100) NULL AFTER city");
        echo "Added column: country<br>";
    } else {
        echo "Column exists: country<br>";
    }

    // Update existing records to populate city/country from location if possible (simple heuristic)
    // This is a basic split, assumes "City, Country" or just "City" format.
    // For now, we will just set a default if empty to avoid breaking filters.
    $pdo->exec("UPDATE properties SET city = 'Lusaka', country = 'Zambia' WHERE city IS NULL");

    echo "Database updated. <a href='listings.php'>View Listings</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
