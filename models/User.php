<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $phone;
    public $whatsapp_number;
    public $profile_image;
    public $is_verified;
    public $is_banned;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Register User
    public function register($data) {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET name = :name, 
                      email = :email, 
                      password = :password, 
                      role = :role, 
                      phone = :phone, 
                      whatsapp_number = :whatsapp_number,
                      verification_token = :verification_token,
                      token_expiry = :token_expiry';

        $stmt = $this->conn->prepare($query);

        // Clean data
        $this->name = htmlspecialchars(strip_tags($data['name']));
        $this->email = htmlspecialchars(strip_tags($data['email']));
        // Password can be null for Google Login
        if (!empty($data['password'])) {
            $this->password = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            $this->password = null;
        }
        $this->role = htmlspecialchars(strip_tags($data['role']));
        $this->phone = htmlspecialchars(strip_tags($data['phone']));
        $this->whatsapp_number = htmlspecialchars(strip_tags($data['whatsapp_number']));

        // Bind data
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':whatsapp_number', $this->whatsapp_number);
        
        $token = $data['verification_token'] ?? null;
        $expiry = $data['token_expiry'] ?? null;
        
        $stmt->bindParam(':verification_token', $token);
        $stmt->bindParam(':token_expiry', $expiry);

        if($stmt->execute()) {
            $user_id = $this->conn->lastInsertId();
            
            // If dealer and Free Trial is enabled, create initial dealer record
            if ($this->role === 'dealer' && !empty($data['subscription_status'])) {
                $this->updateSubscription($user_id, $data['subscription_status'], $data['subscription_expiry']);
            }
            
            return true;
        }

        printf("Error: %s.\n", $stmt->error);
        return false;
    }

    // Login User
    public function login($email, $password) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if($user) {
            // Check if banned
            if ($user['is_banned'] == 1 && $user['role'] !== 'admin') {
                return "banned";
            }
            
            // Check verification (Skip for admin)
            if ($user['is_verified'] == 0 && $user['role'] !== 'admin') {
                return "unverified";
            }
            if (!empty($user['password']) && password_verify($password, $user['password'])) {
                return $user;
            }
        }

        return false;
    }

    // Verify Token
    public function verifyToken($token) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE verification_token = :token AND token_expiry > NOW() LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Update user to verified
            $updateQuery = 'UPDATE ' . $this->table . ' SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = :id';
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':id', $user['id']);
            if ($updateStmt->execute()) {
                return true;
            }
        }
        return false;
    }

    // Create or Update Google User
    public function loginWithGoogle($googleUser) {
        // Check if email exists
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $googleUser['email']);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update google_id if missing
            if (empty($user['google_id'])) {
                $update = 'UPDATE ' . $this->table . ' SET google_id = :gid, is_verified = 1 WHERE id = :id';
                $ustmt = $this->conn->prepare($update);
                $ustmt->bindParam(':gid', $googleUser['id']);
                $ustmt->bindParam(':id', $user['id']);
                $ustmt->execute();
            }
            return $user;
        } else {
            // Register new user
            $query = 'INSERT INTO ' . $this->table . ' SET name = :name, email = :email, google_id = :gid, is_verified = 1, role = "user"';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $googleUser['name']);
            $stmt->bindParam(':email', $googleUser['email']);
            $stmt->bindParam(':gid', $googleUser['id']);
            
            if ($stmt->execute()) {
                // Fetch newly created user
                return $this->getUserById($this->conn->lastInsertId());
            }
        }
        return false;
    }


    // Get User by ID
    public function getUserById($id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Check if email exists
    public function emailExists($email) {
        $query = 'SELECT id FROM ' . $this->table . ' WHERE email = :email LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Update Dealer Subscription
    public function updateSubscription($user_id, $status, $expiry) {
        // Check if dealer record exists, if not create one (though it should exist ideally)
        $query = 'INSERT INTO dealers (user_id, subscription_status, subscription_expiry) 
                  VALUES (:user_id, :status, :expiry)
                  ON DUPLICATE KEY UPDATE subscription_status = :status, subscription_expiry = :expiry';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':expiry', $expiry);
        
        return $stmt->execute();
    }

    // Update Dealer Profile
    public function updateDealerProfile($user_id, $data) {
        $query = 'INSERT INTO dealers (user_id, company_name, office_address, bio) 
                  VALUES (:user_id, :company_name, :office_address, :bio)
                  ON DUPLICATE KEY UPDATE company_name = :company_name, office_address = :office_address, bio = :bio';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':company_name', $data['company_name']);
        $stmt->bindParam(':office_address', $data['office_address']);
        $stmt->bindParam(':bio', $data['bio']);
        
        // Also update user table phone/whatsapp
        $userQuery = 'UPDATE users SET phone = :phone, whatsapp_number = :whatsapp_number WHERE id = :user_id';
        $userStmt = $this->conn->prepare($userQuery);
        $userStmt->bindParam(':user_id', $user_id);
        $userStmt->bindParam(':phone', $data['phone']);
        $userStmt->bindParam(':whatsapp_number', $data['whatsapp_number']);
        $userStmt->execute();

        return $stmt->execute();
    }

    // Get Dealer Profile
    public function getDealerProfile($user_id) {
        $query = 'SELECT u.*, d.company_name, d.office_address, d.bio, d.subscription_status, d.subscription_expiry
                  FROM users u
                  LEFT JOIN dealers d ON u.id = d.user_id
                  WHERE u.id = :user_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update Profile (General User)
    public function updateProfile($id, $name, $phone, $whatsapp, $profile_image = null) {
        $query = 'UPDATE ' . $this->table . ' SET name = :name, phone = :phone, whatsapp_number = :whatsapp';
        
        if ($profile_image) {
            $query .= ', profile_image = :profile_image';
        }
        
        $query .= ' WHERE id = :id';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':whatsapp', $whatsapp);
        if ($profile_image) {
            $stmt->bindParam(':profile_image', $profile_image);
        }
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    // Update Password
    public function updatePassword($id, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = 'UPDATE ' . $this->table . ' SET password = :password WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Set Reset Token
    public function setResetToken($email, $token, $expiry) {
        $query = 'UPDATE ' . $this->table . ' SET reset_token = :token, reset_expires = :expiry WHERE email = :email';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':email', $email);
        return $stmt->execute();
    }

    // Verify Reset Token
    public function verifyResetToken($token) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE reset_token = :token AND reset_expires > NOW() LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Reset Password via Token
    public function resetPassword($token, $password) {
        $user = $this->verifyResetToken($token);
        if ($user) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = 'UPDATE ' . $this->table . ' SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user['id']);
            return $stmt->execute();
        }
        return false;
    }

    // Delete User
    public function delete($id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Ban/Unban User
    public function toggleBan($id, $status) {
        $query = 'UPDATE ' . $this->table . ' SET is_banned = :status WHERE id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
