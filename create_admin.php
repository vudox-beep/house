<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Admin details
    $name = 'System Admin';
    $email = 'admin@luxestay.com';
    $password = 'Admin@123'; // Temporary password
    $role = 'admin';
    $status = 'active';

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if admin exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    // Check columns
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if ($stmt->fetch()) {
        // Update existing admin
        $sql = "UPDATE users SET password = :password, role = :role";
        $params = [
            ':password' => $password_hash,
            ':role' => $role,
            ':email' => $email
        ];

        if (in_array('status', $columns)) {
            $sql .= ", status = :status";
            $params[':status'] = $status;
        }

        $sql .= " WHERE email = :email";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "Admin user updated.<br>";
    } else {
        // Create new admin
        $fields = ['name', 'email', 'password', 'role', 'created_at'];
        $values = [':name', ':email', ':password', ':role', 'NOW()'];
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password' => $password_hash,
            ':role' => $role
        ];

        if (in_array('status', $columns)) {
            $fields[] = 'status';
            $values[] = ':status';
            $params[':status'] = $status;
        }

        if (in_array('email_verified', $columns)) {
            $fields[] = 'email_verified';
            $values[] = '1';
        }

        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "Admin user created.<br>";
    }

    echo "<h3>Admin Credentials:</h3>";
    echo "Email: <strong>$email</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "<br><a href='login.php'>Login Here</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
