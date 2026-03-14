<?php
// Suppress warnings for CLI execution
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);

require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- Database Check ---\n";
    
    // Check Columns
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Columns in 'users' table:\n";
    if (in_array('identity_verified', $columns)) {
        echo "[OK] identity_verified exists.\n";
    } else {
        echo "[MISSING] identity_verified does NOT exist.\n";
    }

    if (in_array('verification_doc', $columns)) {
        echo "[OK] verification_doc exists.\n";
    } else {
        echo "[MISSING] verification_doc does NOT exist.\n";
    }

    // Check Rejected Users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE identity_verified = 2");
    $rejectedCount = $stmt->fetchColumn();
    echo "Rejected Users (identity_verified = 2): $rejectedCount\n";

} catch (PDOException $e) {
    echo "DB Error: " . $e->getMessage() . "\n";
}
?>