<?php
// Standalone DB update script to bypass config/cli issues

$configs = [
    [
        'host' => 'localhost',
        'user' => 'atphieleqa_house',
        'pass' => 'Octomass12.',
        'name' => 'atphieleqa_house'
    ],
    [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'house' // Guessing local DB name
    ],
    [
        'host' => 'localhost',
        'user' => 'root',
        'pass' => '',
        'name' => 'atphieleqa_house' // Another guess
    ]
];

$conn = null;

foreach ($configs as $conf) {
    try {
        echo "Trying to connect with user '{$conf['user']}' to db '{$conf['name']}'...\n";
        $dsn = "mysql:host={$conf['host']};dbname={$conf['name']}";
        $conn = new PDO($dsn, $conf['user'], $conf['pass']);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully!\n";
        break;
    } catch (PDOException $e) {
        echo "Failed: " . $e->getMessage() . "\n";
    }
}

if (!$conn) {
    die("Could not connect to any database configuration.\n");
}

try {
    // Check if column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM properties LIKE 'listing_purpose'");
    $stmt->execute();
    $exists = $stmt->fetch();
    
    if (!$exists) {
        $sql = "ALTER TABLE properties ADD COLUMN listing_purpose ENUM('rent', 'sale') NOT NULL DEFAULT 'rent' AFTER property_type";
        $conn->exec($sql);
        echo "Column 'listing_purpose' added successfully.\n";
    } else {
        echo "Column 'listing_purpose' already exists.\n";
    }
    
} catch(PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>