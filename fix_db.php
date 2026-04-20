<?php
require_once 'config/config.php';

try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Attempt to add the missing column
    $pdo->exec("ALTER TABLE rent_payments ADD COLUMN proof_of_payment VARCHAR(255) NULL AFTER payment_method");
    echo "Successfully added 'proof_of_payment' column!";
} catch(PDOException $e) {
    if(strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column 'proof_of_payment' already exists.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
