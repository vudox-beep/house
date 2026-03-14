<?php
require_once '../config/config.php';

// Allow any logged in admin
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("Access Denied: Admin only.");
}

echo "<h1>Database Debugger</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Check Columns
    echo "<h3>Table Structure (users)</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('verification_doc', $columns)) {
        echo "<p style='color:green'>✅ Column 'verification_doc' EXISTS.</p>";
    } else {
        echo "<p style='color:red'>❌ Column 'verification_doc' MISSING!</p>";
    }

    if (in_array('identity_verified', $columns)) {
        echo "<p style='color:green'>✅ Column 'identity_verified' EXISTS.</p>";
    } else {
        echo "<p style='color:red'>❌ Column 'identity_verified' MISSING!</p>";
    }

    // 2. Check Specific User
    $search = 'chisalavudo'; // Partial match
    echo "<h3>User Search: '$search'</h3>";
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, is_verified, identity_verified, verification_doc FROM users WHERE email LIKE ? OR name LIKE ?");
    $stmt->execute(["%$search%", "%$search%"]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Is Verified</th><th>Identity Verified</th><th>Doc Path</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>{$u['id']}</td>";
            echo "<td>{$u['name']}</td>";
            echo "<td>{$u['email']}</td>";
            echo "<td>{$u['role']}</td>";
            echo "<td>{$u['is_verified']}</td>";
            echo "<td>{$u['identity_verified']}</td>"; // Should be 0 or 1
            echo "<td>" . ($u['verification_doc'] ? $u['verification_doc'] : 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No user found matching '$search'.</p>";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>