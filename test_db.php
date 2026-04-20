<?php
require_once 'config/config.php';
$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
$stmt = $pdo->query('SHOW COLUMNS FROM users');
$cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) {
    echo $c['Field'] . "\n";
}
$stmt2 = $pdo->query('SHOW COLUMNS FROM dealers');
$cols2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);
echo "--- DEALERS ---\n";
foreach($cols2 as $c) {
    echo $c['Field'] . "\n";
}
