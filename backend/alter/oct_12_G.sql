CREATE TABLE driver_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  driver_id INT NOT NULL,
  order_id INT NOT NULL,
  status ENUM('claimed','delivered','cancelled') DEFAULT 'claimed',
  claimed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  delivered_at TIMESTAMP NULL,
  UNIQUE KEY unique_order (order_id)
);
