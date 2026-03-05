<?php
require_once 'config/db.php';
$db = new Database();
$conn = $db->connect();
$stmt = $conn->query("DESCRIBE properties");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " | " . $col['Type'] . "\n";
}
?>