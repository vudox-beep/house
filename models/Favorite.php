<?php
require_once __DIR__ . '/../config/db.php';

class Favorite {
    private $conn;
    private $table = 'saved_properties';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Toggle Favorite (Add/Remove)
    public function toggle($user_id, $property_id) {
        if ($this->isSaved($user_id, $property_id)) {
            return $this->remove($user_id, $property_id);
        } else {
            return $this->add($user_id, $property_id);
        }
    }

    // Add Favorite
    public function add($user_id, $property_id) {
        $query = 'INSERT INTO ' . $this->table . ' (user_id, property_id) VALUES (:user_id, :property_id)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':property_id', $property_id);
        
        if ($stmt->execute()) {
            return 'added';
        }
        return false;
    }

    // Remove Favorite
    public function remove($user_id, $property_id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE user_id = :user_id AND property_id = :property_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':property_id', $property_id);
        
        if ($stmt->execute()) {
            return 'removed';
        }
        return false;
    }

    // Check if Saved
    public function isSaved($user_id, $property_id) {
        $query = 'SELECT id FROM ' . $this->table . ' WHERE user_id = :user_id AND property_id = :property_id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Get User Favorites
    public function getUserFavorites($user_id) {
        $query = 'SELECT p.*, f.created_at as saved_at 
                  FROM ' . $this->table . ' f
                  JOIN properties p ON f.property_id = p.id
                  WHERE f.user_id = :user_id
                  ORDER BY f.created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>