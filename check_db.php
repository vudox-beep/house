<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Checking columns in 'users' table:\n";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array('identity_verified', $columns)) {
        echo "- 'identity_verified' exists.\n";
    } else {
        echo "- 'identity_verified' MISSING!\n";
    }

    if (in_array('verification_doc', $columns)) {
        echo "- 'verification_doc' exists.\n";
    } else {
        echo "- 'verification_doc' MISSING!\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>