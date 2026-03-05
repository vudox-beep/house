<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Try empty first
$dbname = 'rent';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("DESCRIBE properties");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " | " . $col['Type'] . "\n";
    }
} catch (PDOException $e) {
    echo "Connection failed with empty password: " . $e->getMessage() . "\n";
    // Try with '.'
    try {
        $pass = '.';
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->query("DESCRIBE properties");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo $col['Field'] . " | " . $col['Type'] . "\n";
        }
    } catch (PDOException $e2) {
        echo "Connection failed with '.' password: " . $e2->getMessage() . "\n";
    }
}
?>