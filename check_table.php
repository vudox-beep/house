<?php
require_once 'config/db.php';

try {
    $database = new Database();
    $conn = $database->connect();
    
    $stmt = $conn->query("DESCRIBE saved_properties");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Table 'saved_properties' structure:\n";
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>