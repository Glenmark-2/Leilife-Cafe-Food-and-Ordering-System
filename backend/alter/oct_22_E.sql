ALTER TABLE product_flavors
DROP FOREIGN KEY product_flavors_ibfk_1;

ALTER TABLE product_flavors
DROP COLUMN product_id;

delete from product_flavors  where flavor_id >= 9;

ALTER TABLE product_flavors
ADD COLUMN status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available';

ALTER TABLE products
ADD COLUMN has_size ENUM('1','0') NOT NULL DEFAULT '0';

ALTER TABLE products
ADD COLUMN has_flavor ENUM('1','0') NOT NULL DEFAULT '0';

-- set nyo ung products nyo na may flavors or size
UPDATE products
SET has_flavor = 1
WHERE product_id IN (7, 10, 11);

UPDATE products
SET has_size = 1
WHERE product_id BETWEEN 32 AND 83;

CREATE TABLE drink_size (
    size_id INT(11) NOT NULL AUTO_INCREMENT,
    size_name ENUM('medium', 'large') NOT NULL,
    status ENUM('available', 'unavailable') NOT NULL DEFAULT 'available',
    PRIMARY KEY (size_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO drink_size (size_name, status)
VALUES 
('medium', 'available'),
('large', 'available');