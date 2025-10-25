-- FIRST PUSH
ALTER TABLE addresses 
ADD COLUMN region_name VARCHAR(100) NOT NULL DEFAULT 'NCR (National Capital Region)' AFTER region,
ADD COLUMN province_name VARCHAR(100) NOT NULL DEFAULT 'Metro Manila' AFTER province,
ADD COLUMN city_name VARCHAR(100) NOT NULL DEFAULT 'Caloocan City' AFTER city;

-- SECOND
CREATE TABLE `payment_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `gcash_name` VARCHAR(100) DEFAULT NULL,
  `gcash_number` VARCHAR(15) DEFAULT NULL,
  `gcash_email` VARCHAR(100) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payment_settings` 
(`gcash_name`, `gcash_number`, `gcash_email`)
VALUES ("Ellie Imnida", 09123456789, "leilife.test@gmail.com");

CREATE TABLE `payment_methods` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `method` VARCHAR(50) NOT NULL,
  `status` ENUM('enabled', 'disabled') NOT NULL DEFAULT 'enabled',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `payment_methods` (method, status)
VALUES
('Cash', 'enabled'),
('GCash', 'disabled');
