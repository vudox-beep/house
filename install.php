<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    echo "Database created or already exists.<br>";
    
    // Select database
    $pdo->exec("USE " . DB_NAME);
    
    // Read SQL file
    $sql = file_get_contents('database.sql');
    
    // Execute SQL
    $pdo->exec($sql);
    echo "Tables created successfully.<br>";
    echo "Installation complete. <a href='index.php'>Go to Home</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
