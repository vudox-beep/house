<?php
require_once __DIR__ . '/../config/db.php';

class Lead {
    private $conn;
    private $table = 'leads';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET property_id = :property_id,
                      dealer_id = :dealer_id,
                      name = :name,
                      email = :email,
                      phone = :phone,
                      message = :message,
                      created_at = NOW()';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $data['property_id']);
        $stmt->bindParam(':dealer_id', $data['dealer_id']);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':message', $data['message']);
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getByDealer($dealer_id) {
        $query = 'SELECT l.*, p.title as property_title 
                  FROM ' . $this->table . ' l
                  LEFT JOIN properties p ON l.property_id = p.id
                  WHERE l.dealer_id = :dealer_id
                  ORDER BY l.created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dealer_id', $dealer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
