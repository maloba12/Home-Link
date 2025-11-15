-- Create missing tables for HomeLink system

-- Favorites table (for buyers to save properties)
CREATE TABLE IF NOT EXISTS favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bookings table (for property viewing requests)
CREATE TABLE IF NOT EXISTS bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    viewing_date DATE NULL,
    viewing_time TIME NULL,
    message TEXT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(property_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_favorites_user ON favorites(user_id);
CREATE INDEX idx_favorites_property ON favorites(property_id);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_property ON bookings(property_id);
CREATE INDEX idx_bookings_status ON bookings(status);

-- Insert sample data (optional)
-- Uncomment if you want sample favorites and bookings

-- INSERT INTO favorites (user_id, property_id) 
-- SELECT u.user_id, p.property_id 
-- FROM users u, properties p 
-- WHERE u.role = 'buyer' AND p.status = 'approved' 
-- LIMIT 3;

-- INSERT INTO bookings (user_id, property_id, viewing_date, viewing_time, message, status)
-- SELECT u.user_id, p.property_id, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', 'I would like to view this property', 'pending'
-- FROM users u, properties p
-- WHERE u.role = 'buyer' AND p.status = 'approved'
-- LIMIT 2;
