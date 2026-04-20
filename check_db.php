<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['HTTPS'] = 'off';

require_once 'config/config.php';

echo "Database: " . DB_NAME . "\n";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected successfully\n";

    $stmt = $pdo->query("DESCRIBE dealers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Dealers columns: " . implode(', ', $columns) . "\n";

    $stmt = $pdo->query("DESCRIBE referral_rewards");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Field'] === 'reward_type') {
            echo "Referral rewards type: " . $row['Type'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
