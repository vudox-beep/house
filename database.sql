-- Database Schema for House for Rent System

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'dealer', 'admin') DEFAULT 'user',
    whatsapp_number VARCHAR(20),
    profile_image VARCHAR(255),
    verification_token VARCHAR(255) DEFAULT NULL,
    token_expiry TIMESTAMP NULL DEFAULT NULL,
    verification_doc VARCHAR(255) DEFAULT NULL,
    identity_verified TINYINT(1) DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE,
    is_banned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dealers (
    user_id INT PRIMARY KEY,
    company_name VARCHAR(100),
    office_address TEXT,
    bio TEXT,
    subscription_status ENUM('active', 'expired', 'none') DEFAULT 'none',
    subscription_expiry TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) DEFAULT 'ZMW',
    bedrooms INT,
    bathrooms INT,
    rooms INT,
    size_sqm DECIMAL(10, 2),
    property_type ENUM('house', 'apartment', 'flat', 'boarding_house') NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    status ENUM('available', 'rented') DEFAULT 'available',
    amenities TEXT, -- JSON or comma separated
    video_url VARCHAR(255),
    views INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_boosted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dealer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE property_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dealer_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL,
    payment_method VARCHAR(50), -- e.g., 'lenco'
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (dealer_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    user_id INT, -- Can be null if guest
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE saved_properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);
