-- Update Properties with Authentic Zambian Locations
-- HomeLink Real Estate Platform - Zambia Edition

USE homelink;

-- Update existing properties with Zambian locations
UPDATE properties SET 
    city = 'Lusaka',
    address = 'Kabulonga Road, Kabulonga',
    state = 'Lusaka Province'
WHERE property_id = 1;

UPDATE properties SET 
    city = 'Lusaka', 
    address = 'Woodlands Extension, Woodlands',
    state = 'Lusaka Province'
WHERE property_id = 2;

UPDATE properties SET 
    city = 'Lusaka',
    address = 'Rhodes Park, Great East Road',
    state = 'Lusaka Province'
WHERE property_id = 3;

UPDATE properties SET 
    city = 'Livingstone',
    address = 'Maramba Drive, Mosi-oa-Tunya',
    state = 'Southern Province'
WHERE property_id = 4;

UPDATE properties SET 
    city = 'Livingstone',
    address = 'Zambezi View Estate, Libuyu',
    state = 'Southern Province'
WHERE property_id = 5;

UPDATE properties SET 
    city = 'Kitwe',
    address = 'Parklands, Kitwe Central',
    state = 'Copperbelt Province'
WHERE property_id = 6;

UPDATE properties SET 
    city = 'Ndola',
    address = 'Kansenshi, Ndola Central',
    state = 'Copperbelt Province'
WHERE property_id = 7;

UPDATE properties SET 
    city = 'Kitwe',
    address = 'Riverside, Kitwe',
    state = 'Copperbelt Province'
WHERE property_id = 8;

UPDATE properties SET 
    city = 'Chingola',
    address = 'Chikola Township, Chingola',
    state = 'Copperbelt Province'
WHERE property_id = 9;

UPDATE properties SET 
    city = 'Mufulira',
    address = 'Kankoyo, Mufulira',
    state = 'Copperbelt Province'
WHERE property_id = 10;

-- Update property titles to reflect Zambian context
UPDATE properties SET title = 'Modern 2BR Apartment in Kabulonga' WHERE property_id = 1;
UPDATE properties SET title = 'Luxury 3BR House in Woodlands' WHERE property_id = 2;
UPDATE properties SET title = 'Cozy Studio in Rhodes Park' WHERE property_id = 3;
UPDATE properties SET title = 'Spacious Family Home in Livingstone' WHERE property_id = 4;
UPDATE properties SET title = 'Suburban Dream House in Livingstone' WHERE property_id = 5;
UPDATE properties SET title = 'Modern Townhouse in Kitwe' WHERE property_id = 6;
UPDATE properties SET title = 'Beachfront Condo near Victoria Falls' WHERE property_id = 7;
UPDATE properties SET title = 'Copperbelt Townhouse in Kitwe' WHERE property_id = 8;
UPDATE properties SET title = 'Investment Duplex in Chingola' WHERE property_id = 9;
UPDATE properties SET title = 'Mining Town House in Mufulira' WHERE property_id = 10;

-- Update descriptions to be more Zambia-specific
UPDATE properties SET description = 'Stunning modern apartment in the prestigious Kabulonga area. Features high-end finishes, secure complex, and close to international schools. Perfect for diplomats and professionals.' WHERE property_id = 1;

UPDATE properties SET description = 'Beautiful 3-bedroom family home in the sought-after Woodlands area. Large garden, modern kitchen, and excellent security. Close to Arcades Shopping Centre.' WHERE property_id = 2;

UPDATE properties SET description = 'Efficient and stylish studio perfect for young professionals. Recently renovated with modern appliances and great location on Great East Road.' WHERE property_id = 3;

UPDATE properties SET description = 'Spacious family home with beautiful garden in Livingstone. Close to Victoria Falls and tourism attractions. Perfect for families who love nature.' WHERE property_id = 4;

UPDATE properties SET description = 'Charming home in Livingstone with excellent views. Features modern finishes, updated bathrooms, and lovely garden. Near Zambezi River.' WHERE property_id = 5;

UPDATE properties SET description = 'Contemporary townhouse in Kitwe''s Parklands area. Modern amenities, secure complex, and great for Copperbelt professionals.' WHERE property_id = 6;

UPDATE properties SET description = 'Beautiful condominium near Victoria Falls with resort-style amenities. Perfect for holiday rentals or permanent living in Livingstone.' WHERE property_id = 7;

UPDATE properties SET description = 'Modern townhouse in Kitwe with great city views. Energy efficient and perfect for Copperbelt mining professionals and families.' WHERE property_id = 8;

UPDATE properties SET description = 'Great investment opportunity in Chingola. Two units on one lot in growing mining community. Excellent rental potential.' WHERE property_id = 9;

UPDATE properties SET description = 'Perfect family home in Mufulira mining town. Well-maintained property with good security and access to mining facilities.' WHERE property_id = 10;

-- Update prices to reflect Zambian market (in ZMW)
UPDATE properties SET price = 8500.00 WHERE property_id = 1;  -- 2BR apartment rent
UPDATE properties SET price = 2500000.00 WHERE property_id = 2; -- 3BR house sale
UPDATE properties SET price = 3500.00 WHERE property_id = 3;   -- Studio rent
UPDATE properties SET price = 1800000.00 WHERE property_id = 4; -- Family home sale
UPDATE properties SET price = 1500000.00 WHERE property_id = 5; -- Suburban house sale
UPDATE properties SET price = 1200000.00 WHERE property_id = 6; -- Townhouse sale
UPDATE properties SET price = 6500.00 WHERE property_id = 7;   -- Condo rent
UPDATE properties SET price = 950000.00 WHERE property_id = 8;  -- Townhouse sale
UPDATE properties SET price = 800000.00 WHERE property_id = 9;  -- Duplex sale
UPDATE properties SET price = 750000.00 WHERE property_id = 10; -- House sale
