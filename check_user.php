<?php
require_once 'config/config.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied. Please login as Admin.");
}

$email = isset($_GET['email']) ? $_GET['email'] : 'chisalavudo'; // Default or from URL

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Debug User: $email</h1>";
    
    // Check Columns first
    echo "<h3>Table Structure (users)</h3>";
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    foreach($columns as $col) {
        if (in_array($col['Field'], ['is_verified', 'identity_verified', 'verification_doc', 'role'])) {
            echo "<strong>{$col['Field']}</strong>: {$col['Type']} (Default: {$col['Default']})\n";
        }
    }
    echo "</pre>";

    // Check User Data
    echo "<h3>User Data</h3>";
    // Using LIKE to find partial match if needed
    $stmt = $pdo->prepare("SELECT id, name, email, role, is_verified, identity_verified, verification_doc FROM users WHERE email LIKE ?");
    $stmt->execute(["%$email%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>is_verified (Email)</th><th>identity_verified (Doc)</th><th>verification_doc path</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$u['id']}</td>";
            echo "<td>{$u['name']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "<td>{$u['is_verified']}</td>";
            echo "<td>{$u['identity_verified']}</td>";
            echo "<td>" . ($u['verification_doc'] ? $u['verification_doc'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>User not found!</p>";
    }
    
    echo "<br><a href='admin/verify_dealers.php'>Back to Admin</a>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>