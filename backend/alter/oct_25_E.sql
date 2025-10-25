ALTER TABLE addresses 
ADD COLUMN region_name VARCHAR(100) NOT NULL DEFAULT 'NCR (National Capital Region)' AFTER region,
ADD COLUMN province_name VARCHAR(100) NOT NULL DEFAULT 'Metro Manila' AFTER province,
ADD COLUMN city_name VARCHAR(100) NOT NULL DEFAULT 'Caloocan City' AFTER city;