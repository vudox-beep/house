<?php
require_once 'config/db.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    // 1. Create Rentals Table (Links Tenant <-> Property <-> Dealer)
    $sql_rentals = "CREATE TABLE IF NOT EXISTS rentals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT NOT NULL,
        dealer_id INT NOT NULL,
        tenant_id INT NOT NULL,
        status ENUM('active', 'ended', 'pending') DEFAULT 'active',
        start_date DATE NOT NULL,
        end_date DATE NULL,
        rent_amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'ZMW',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
        FOREIGN KEY (dealer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $conn->exec($sql_rentals);
    echo "Rentals table created successfully.<br>";
    
    // 2. Create Rent Payments Table (For monthly uploads)
    $sql_payments = "CREATE TABLE IF NOT EXISTS rent_payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rental_id INT NOT NULL,
        tenant_id INT NOT NULL,
        month_year VARCHAR(20) NOT NULL, -- e.g., 'March 2026'
        amount DECIMAL(10, 2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'ZMW',
        proof_file VARCHAR(255) NULL, -- Path to screenshot
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        dealer_notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rental_id) REFERENCES rentals(id) ON DELETE CASCADE,
        FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    
    $conn->exec($sql_payments);
    echo "Rent Payments table created successfully.<br>";
    
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>