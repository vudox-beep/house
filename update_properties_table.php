<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn = $database->connect();

    $queries = [
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS capacity INT DEFAULT NULL;",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS people_per_room INT DEFAULT NULL;",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS event_type VARCHAR(255) DEFAULT NULL;",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS catering_available TINYINT(1) DEFAULT 0;",
        "ALTER TABLE properties ADD COLUMN IF NOT EXISTS equipment_available TINYINT(1) DEFAULT 0;"
    ];

    foreach ($queries as $query) {
        $conn->exec($query);
    }
    echo "Columns added successfully.";
} catch (PDOException $e) {
    echo "Error adding columns: " . $e->getMessage();
}
?>