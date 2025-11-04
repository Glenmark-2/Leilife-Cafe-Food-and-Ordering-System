CREATE TABLE delivery_options (
    delivery_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    option_name VARCHAR(100) NOT NULL, 
    status ENUM('enabled', 'disabled') NOT NULL DEFAULT 'enabled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO delivery_options (option_name, status) VALUES
('Pick-up', 'enabled'),
('Home Delivery', 'enabled');
