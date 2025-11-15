-- Add Sample Properties with Images
-- HomeLink Real Estate Platform

USE homelink;

-- Insert sample properties
INSERT INTO properties (seller_id, title, description, address, city, state, price, type, property_type, bedrooms, bathrooms, sqft, status) VALUES
-- Luxury Apartments
(2, 'Modern Downtown Apartment', 'Stunning modern apartment in the heart of downtown with panoramic city views. Features high-end finishes, open concept living, and premium amenities.', '123 Main Street', 'Nairobi', 'Kenya', 45000.00, 'rent', 'apartment', 2, 2, 1200, 'approved'),
(2, 'Luxury Penthouse Suite', 'Exclusive penthouse with rooftop terrace, private elevator, and breathtaking views. Perfect for sophisticated urban living.', '456 Elite Tower', 'Nairobi', 'Kenya', 85000.00, 'rent', 'apartment', 3, 3, 2500, 'approved'),
(2, 'Cozy Studio Apartment', 'Efficient and stylish studio perfect for young professionals. Recently renovated with modern appliances and great location.', '789 University Ave', 'Nairobi', 'Kenya', 25000.00, 'rent', 'studio', 1, 1, 600, 'approved'),

-- Family Houses
(3, 'Spacious Family Home', 'Beautiful 4-bedroom family home in quiet neighborhood. Large backyard, modern kitchen, and perfect for growing families.', '101 Family Lane', 'Mombasa', 'Kenya', 12000000.00, 'sale', 'house', 4, 3, 2800, 'approved'),
(3, 'Suburban Dream House', 'Charming suburban home with excellent schools nearby. Features hardwood floors, updated bathrooms, and lovely garden.', '202 Suburban Dr', 'Mombasa', 'Kenya', 8500000.00, 'sale', 'house', 3, 2, 2200, 'approved'),
(3, 'Modern Townhouse', 'Contemporary townhouse with smart home features. Energy efficient, low maintenance, and great for modern families.', '303 Tech Park', 'Nairobi', 'Kenya', 6500000.00, 'sale', 'townhouse', 3, 2, 1800, 'approved'),

-- Vacation/Investment Properties
(2, 'Beachfront Condo', 'Stunning beachfront condominium with ocean views. Resort-style amenities including pool, gym, and private beach access.', '505 Coastal Road', 'Mombasa', 'Kenya', 75000.00, 'rent', 'condo', 2, 2, 1500, 'approved'),
(3, 'Mountain View House', 'Serene mountain retreat with breathtaking views. Perfect getaway from city life with modern amenities.', '606 Mountain View', 'Nakuru', 'Kenya', 55000.00, 'rent', 'house', 3, 2, 2000, 'approved'),
(2, 'Investment Duplex', 'Great investment opportunity with rental income potential. Two units on one lot in growing neighborhood.', '707 Investment St', 'Kisumu', 'Kenya', 4500000.00, 'sale', 'house', 4, 2, 2400, 'approved'),
(3, 'Garden Apartment', 'Peaceful garden-level apartment with private patio access. Quiet community with excellent security and parking.', '808 Garden Complex', 'Nairobi', 'Kenya', 35000.00, 'rent', 'apartment', 2, 1, 900, 'approved');

-- Add images for each property
INSERT INTO images (property_id, image_url, is_primary) VALUES
-- Property 1 (Modern Downtown Apartment)
(1, 'assets/images/image 1.jpeg', TRUE),

-- Property 2 (Luxury Penthouse Suite)
(2, 'assets/images/image 2.jpeg', TRUE),

-- Property 3 (Cozy Studio Apartment)
(3, 'assets/images/image 3.jpeg', TRUE),

-- Property 4 (Spacious Family Home)
(4, 'assets/images/image 4.jpeg', TRUE),

-- Property 5 (Suburban Dream House)
(5, 'assets/images/image 5.jpeg', TRUE),

-- Property 6 (Modern Townhouse)
(6, 'assets/images/image 6.jpeg', TRUE),

-- Property 7 (Beachfront Condo)
(7, 'assets/images/image 7.jpeg', TRUE),

-- Property 8 (Mountain View House)
(8, 'assets/images/image 8.jpeg', TRUE),

-- Property 9 (Investment Duplex)
(9, 'assets/images/image 9.jpeg', TRUE),

-- Property 10 (Garden Apartment)
(10, 'assets/images/image 10.jpeg', TRUE);

-- Add additional images for variety (some properties have multiple images)
INSERT INTO images (property_id, image_url, is_primary) VALUES
(1, 'assets/images/image 2.jpeg', FALSE),
(4, 'assets/images/image 1.jpeg', FALSE),
(7, 'assets/images/image 3.jpeg', FALSE),
(2, 'assets/images/image 5.jpeg', FALSE),
(5, 'assets/images/image 8.jpeg', FALSE);
