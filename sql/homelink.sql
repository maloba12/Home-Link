-- HomeLink Database Schema
-- Smart Housing and Property Connection Platform

CREATE DATABASE IF NOT EXISTS homelink;
USE homelink;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
    full_name VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Properties Table
CREATE TABLE properties (
    property_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    zip_code VARCHAR(20),
    price DECIMAL(10, 2) NOT NULL,
    type ENUM('rent', 'sale') NOT NULL,
    property_type ENUM('apartment', 'house', 'condo', 'townhouse', 'studio') NOT NULL,
    bedrooms INT DEFAULT 0,
    bathrooms DECIMAL(2, 1) DEFAULT 0,
    sqft INT,
    status ENUM('pending', 'approved', 'rejected', 'sold', 'rented') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Images Table
CREATE TABLE images (
    image_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Amenities Table
CREATE TABLE amenities (
    amenity_id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    amenity VARCHAR(100) NOT NULL,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Favorites Table
CREATE TABLE favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id)
);

-- Bookings Table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_date DATETIME NOT NULL,
    message TEXT,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
);

-- Searches Table
CREATE TABLE searches (
    search_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    search_query TEXT,
    filters JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Insert Admin User (password: admin123)
INSERT INTO users (username, email, password, role, full_name) VALUES 
('admin', 'admin@homelink.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin User');

-- Insert Sample Users
INSERT INTO users (username, email, password, role, full_name, phone) VALUES 
('chandabuyer', 'chanda@example.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'buyer', 'Chanda Mwila', '+260-97-123-4567'),
('lindiweseller', 'lindiwe@example.co.zm', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seller', 'Lindiwe Banda', '+260-96-765-4321');

-- Insert Sample Properties
INSERT INTO properties (seller_id, title, description, address, city, state, zip_code, price, type, property_type, bedrooms, bathrooms, sqft, status) VALUES 
(3, 'Modern 2BR Apartment in Kabulonga', 'Spacious 2-bedroom apartment with modern amenities near shopping and restaurants.', '12 Alick Nkhata Rd, Kabulonga', 'Lusaka', 'Lusaka Province', '10101', 18000, 'rent', 'apartment', 2, 2, 110, 'approved'),
(3, 'Luxury 3BR House in Woodlands', 'Stunning 3-bedroom house with a beautiful garden. Perfect for families.', '25 Lake Rd, Woodlands', 'Lusaka', 'Lusaka Province', '10102', 3200000, 'sale', 'house', 3, 2.5, 240, 'approved'),
(3, 'Cozy Studio in Rhodes Park', 'Contemporary studio apartment with all amenities. Great for professionals.', '7 Addis Ababa Dr, Rhodes Park', 'Lusaka', 'Lusaka Province', '10103', 9000, 'rent', 'studio', 1, 1, 45, 'approved');

-- Insert Amenities
INSERT INTO amenities (property_id, amenity) VALUES 
(1, 'parking'),
(1, 'gym'),
(1, 'pool'),
(2, 'parking'),
(2, 'garden'),
(2, 'fireplace'),
(3, 'parking'),
(3, 'gym'),
(3, 'concierge');

-- Insert Sample Images
INSERT INTO images (property_id, image_url, is_primary) VALUES 
(1, 'assets/images/image 1.jpeg', TRUE),
(1, 'assets/images/image 2.jpeg', FALSE),
(2, 'assets/images/image 3.jpeg', TRUE),
(2, 'assets/images/image 4.jpeg', FALSE),
(3, 'assets/images/image 5.jpeg', TRUE);