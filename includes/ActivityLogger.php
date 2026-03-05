<?php
require_once __DIR__ . '/../config/db.php';

class ActivityLogger {
    private $conn;
    private $table = 'activity_logs';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            user_role VARCHAR(50) NULL,
            action VARCHAR(255) NOT NULL,
            description TEXT NULL,
            ip_address VARCHAR(45) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        try {
            $this->conn->exec($sql);
        } catch(PDOException $e) {
            // Log error silently or handle
        }
    }

    public function log($user_id, $role, $action, $description = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (user_id, user_role, action, description, ip_address) 
                  VALUES (:user_id, :role, :action, :description, :ip)";
        
        $stmt = $this->conn->prepare($query);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        // Clean data
        $action = htmlspecialchars(strip_tags($action));
        $description = htmlspecialchars(strip_tags($description));
        
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip', $ip);
        
        return $stmt->execute();
    }

    public function getLogs($limit = 50, $offset = 0) {
        $query = "SELECT l.*, u.name as user_name, u.email as user_email 
                  FROM " . $this->table . " l
                  LEFT JOIN users u ON l.user_id = u.id
                  ORDER BY l.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLogsByUser($user_id, $limit = 50, $offset = 0) {
        $query = "SELECT l.*, u.name as user_name, u.email as user_email 
                  FROM " . $this->table . " l
                  LEFT JOIN users u ON l.user_id = u.id
                  WHERE l.user_id = :user_id
                  ORDER BY l.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotalLogsByUser($user_id) {
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
    
    public function getTotalLogs() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }
}
?>