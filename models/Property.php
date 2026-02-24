<?php
require_once __DIR__ . '/../config/db.php';

class Property {
    private $conn;
    private $table = 'properties';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Get all properties (Only from Active Dealers)
    public function getAll() {
        $query = 'SELECT p.*, u.name as dealer_name, u.whatsapp_number, u.phone 
                  FROM ' . $this->table . ' p
                  LEFT JOIN users u ON p.dealer_id = u.id
                  LEFT JOIN dealers d ON u.id = d.user_id
                  WHERE d.subscription_status = "active" AND (d.subscription_expiry IS NULL OR d.subscription_expiry > NOW())
                  ORDER BY p.created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get properties by dealer
    public function getByDealer($dealer_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE dealer_id = :dealer_id ORDER BY created_at DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':dealer_id', $dealer_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Create Property
    public function create($data) {
        $query = 'INSERT INTO ' . $this->table . ' 
                  SET dealer_id = :dealer_id,
                      title = :title,
                      description = :description,
                      price = :price,
                      currency = :currency,
                      bedrooms = :bedrooms,
                      bathrooms = :bathrooms,
                      rooms = :rooms,
                      size_sqm = :size_sqm,
                      property_type = :property_type,
                      listing_purpose = :listing_purpose,
                      location = :location,
                      city = :city,
                      country = :country,
                      latitude = :latitude,
                      longitude = :longitude,
                      status = :status,
                      is_featured = :is_featured,
                      amenities = :amenities,
                      video_url = :video_url';

        $stmt = $this->conn->prepare($query);

        // Bind data
        $stmt->bindParam(':dealer_id', $data['dealer_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':bedrooms', $data['bedrooms']);
        $stmt->bindParam(':bathrooms', $data['bathrooms']);
        $stmt->bindParam(':rooms', $data['rooms']);
        $stmt->bindParam(':size_sqm', $data['size_sqm']);
        $stmt->bindParam(':property_type', $data['property_type']);
        $stmt->bindParam(':listing_purpose', $data['listing_purpose']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':country', $data['country']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':is_featured', $data['is_featured']);
        $stmt->bindParam(':amenities', $data['amenities']);
        $stmt->bindParam(':video_url', $data['video_url']);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Update Property
    public function update($data) {
        $query = 'UPDATE ' . $this->table . ' 
                  SET title = :title,
                      description = :description,
                      price = :price,
                      currency = :currency,
                      bedrooms = :bedrooms,
                      bathrooms = :bathrooms,
                      rooms = :rooms,
                      size_sqm = :size_sqm,
                      property_type = :property_type,
                      listing_purpose = :listing_purpose,
                      location = :location,
                      city = :city,
                      country = :country,
                      latitude = :latitude,
                      longitude = :longitude,
                      status = :status,
                      amenities = :amenities,
                      video_url = :video_url
                  WHERE id = :id AND dealer_id = :dealer_id';

        $stmt = $this->conn->prepare($query);

        // Bind data
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':dealer_id', $data['dealer_id']);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':currency', $data['currency']);
        $stmt->bindParam(':bedrooms', $data['bedrooms']);
        $stmt->bindParam(':bathrooms', $data['bathrooms']);
        $stmt->bindParam(':rooms', $data['rooms']);
        $stmt->bindParam(':size_sqm', $data['size_sqm']);
        $stmt->bindParam(':property_type', $data['property_type']);
        $stmt->bindParam(':listing_purpose', $data['listing_purpose']);
        $stmt->bindParam(':location', $data['location']);
        $stmt->bindParam(':city', $data['city']);
        $stmt->bindParam(':country', $data['country']);
        $stmt->bindParam(':latitude', $data['latitude']);
        $stmt->bindParam(':longitude', $data['longitude']);
        $stmt->bindParam(':status', $data['status']);
        $stmt->bindParam(':amenities', $data['amenities']);
        $stmt->bindParam(':video_url', $data['video_url']);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete Property
    public function delete($id, $dealer_id) {
        $query = 'DELETE FROM ' . $this->table . ' WHERE id = :id AND dealer_id = :dealer_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':dealer_id', $dealer_id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Get Single Property
    public function getById($id) {
        $query = 'SELECT p.*, u.name as dealer_name, u.whatsapp_number, u.phone, u.email as dealer_email
                  FROM ' . $this->table . ' p
                  LEFT JOIN users u ON p.dealer_id = u.id
                  WHERE p.id = :id LIMIT 1';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Search Properties
    public function search($filters) {
        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            // Haversine formula to find properties within 50km radius
            $lat = $filters['latitude'];
            $lng = $filters['longitude'];
            $radius = 50; // km

            $query = "SELECT p.*, (
                        6371 * acos(
                            cos(radians(:lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians(:lng)) +
                            sin(radians(:lat)) * sin(radians(latitude))
                        )
                    ) AS distance 
                    FROM " . $this->table . " p
                    LEFT JOIN dealers d ON p.dealer_id = d.user_id
                    WHERE d.subscription_status = 'active' AND (d.subscription_expiry IS NULL OR d.subscription_expiry > NOW())
                    HAVING distance < :radius";
        } else {
            $query = 'SELECT p.* FROM ' . $this->table . ' p 
                      LEFT JOIN dealers d ON p.dealer_id = d.user_id
                      WHERE d.subscription_status = "active" AND (d.subscription_expiry IS NULL OR d.subscription_expiry > NOW())';
        }
        
        if (!empty($filters['location']) && empty($filters['latitude'])) {
            $query .= ' AND (location LIKE :location OR city LIKE :location OR country LIKE :location)';
        }
        if (!empty($filters['city'])) {
            $query .= ' AND city LIKE :city';
        }
        if (!empty($filters['country'])) {
            $query .= ' AND country = :country';
        }
        if (!empty($filters['property_type'])) {
            $query .= ' AND property_type = :property_type';
        }
        if (!empty($filters['max_price'])) {
            $query .= ' AND price <= :max_price';
        }
        if (!empty($filters['min_price'])) {
            $query .= ' AND price >= :min_price';
        }
        if (!empty($filters['bedrooms'])) {
            $query .= ' AND bedrooms >= :bedrooms';
        }
        if (!empty($filters['featured'])) {
            $query .= ' AND is_featured = 1';
        }

        $query .= ' ORDER BY created_at DESC';

        $stmt = $this->conn->prepare($query);

        if (!empty($filters['latitude']) && !empty($filters['longitude'])) {
            $stmt->bindParam(':lat', $filters['latitude']);
            $stmt->bindParam(':lng', $filters['longitude']);
            $stmt->bindParam(':radius', $radius);
        }

        if (!empty($filters['location']) && empty($filters['latitude'])) {
            $location = '%' . $filters['location'] . '%';
            $stmt->bindParam(':location', $location);
        }
        if (!empty($filters['city'])) {
            $city = '%' . $filters['city'] . '%';
            $stmt->bindParam(':city', $city);
        }
        if (!empty($filters['country'])) {
            $stmt->bindParam(':country', $filters['country']);
        }
        if (!empty($filters['property_type'])) {
            $stmt->bindParam(':property_type', $filters['property_type']);
        }
        if (!empty($filters['max_price'])) {
            $stmt->bindParam(':max_price', $filters['max_price']);
        }
        if (!empty($filters['min_price'])) {
            $stmt->bindParam(':min_price', $filters['min_price']);
        }
        if (!empty($filters['bedrooms'])) {
            $stmt->bindParam(':bedrooms', $filters['bedrooms']);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get Featured Properties
    public function getFeatured($limit = 3) {
        $query = 'SELECT p.* FROM ' . $this->table . ' p
                  LEFT JOIN dealers d ON p.dealer_id = d.user_id
                  WHERE p.is_featured = 1 AND p.status = "available" 
                  AND d.subscription_status = "active" AND (d.subscription_expiry IS NULL OR d.subscription_expiry > NOW())
                  ORDER BY RAND() LIMIT :limit';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add Image
    public function addImage($property_id, $image_path, $is_main = 0) {
        $query = 'INSERT INTO property_images (property_id, image_path, is_main) VALUES (:property_id, :image_path, :is_main)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':is_main', $is_main);
        return $stmt->execute();
    }

    // Get Images
    public function getImages($property_id) {
        $query = 'SELECT * FROM property_images WHERE property_id = :property_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add Tenancy History
    public function addHistory($data) {
        $query = 'INSERT INTO tenancy_history (property_id, tenant_name, start_date, end_date, condition_start, condition_end) 
                  VALUES (:property_id, :tenant_name, :start_date, :end_date, :condition_start, :condition_end)';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $data['property_id']);
        $stmt->bindParam(':tenant_name', $data['tenant_name']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        $stmt->bindParam(':condition_start', $data['condition_start']);
        $stmt->bindParam(':condition_end', $data['condition_end']);
        return $stmt->execute();
    }

    // Update Featured Status for Dealer's Properties
    public function setFeaturedByDealer($dealer_id, $status = 1) {
        $query = 'UPDATE ' . $this->table . ' SET is_featured = :status WHERE dealer_id = :dealer_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':dealer_id', $dealer_id);
        return $stmt->execute();
    }

    // Get Tenancy History
    public function getHistory($property_id) {
        $query = 'SELECT * FROM tenancy_history WHERE property_id = :property_id ORDER BY end_date DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':property_id', $property_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
