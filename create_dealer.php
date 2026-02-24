<?php
require_once 'config/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Dealer details
    $name = 'Test Dealer';
    $email = 'dealer@luxestay.com';
    $password = 'Dealer@123';
    $role = 'dealer';
    $status = 'active';
    $phone = '0970000000';
    $whatsapp = '0970000000';

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check if dealer exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    // Check columns
    $columns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if ($stmt->fetch()) {
        // Update existing dealer
        $sql = "UPDATE users SET password = :password, role = :role WHERE email = :email";
        $params = [
            ':password' => $password_hash,
            ':role' => $role,
            ':email' => $email
        ];
        
        if (in_array('status', $columns)) {
            $sql = "UPDATE users SET password = :password, role = :role, status = :status WHERE email = :email";
            $params[':status'] = $status;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo "Dealer account updated.<br>";
        
        // Get user ID
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user_id = $stmt->fetchColumn();

    } else {
        // Create new dealer
        $fields = ['name', 'email', 'password', 'role', 'phone', 'whatsapp_number', 'created_at'];
        $values = [':name', ':email', ':password', ':role', ':phone', ':whatsapp', 'NOW()'];
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':password' => $password_hash,
            ':role' => $role,
            ':phone' => $phone,
            ':whatsapp' => $whatsapp
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
        
        if (in_array('is_verified', $columns)) {
            $fields[] = 'is_verified';
            $values[] = '1';
        }

        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $user_id = $pdo->lastInsertId();
        echo "Dealer account created.<br>";
    }

    // Ensure dealers table entry
    $stmt = $pdo->prepare("SELECT user_id FROM dealers WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    
    if (!$stmt->fetch()) {
        $pdo->prepare("INSERT INTO dealers (user_id, company_name, subscription_status) VALUES (:user_id, :company, 'active')")
            ->execute([':user_id' => $user_id, ':company' => 'LuxeStay Realty']);
        echo "Dealer profile created.<br>";
    }

    echo "<h3>Dealer Credentials:</h3>";
    echo "Email: <strong>$email</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "<br><a href='login.php'>Login Here</a>";

} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
