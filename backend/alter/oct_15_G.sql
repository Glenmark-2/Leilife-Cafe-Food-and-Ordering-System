CREATE TABLE refunds (
  refund_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  transaction_id INT NULL,
  paymongo_refund_id VARCHAR(100) NULL,
  amount DECIMAL(10,2) NOT NULL,
  reason VARCHAR(255) DEFAULT 'User requested refund',
  status ENUM('pending','processing','succeeded','failed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(order_id),
  FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id)
);


ALTER TABLE orders
ADD COLUMN payment_id VARCHAR(100) NULL AFTER payment_status;


ALTER TABLE order_items
MODIFY status ENUM('pending','preparing','finished','cancelled') NOT NULL DEFAULT 'pending';


ALTER TABLE orders 
MODIFY payment_status ENUM('unpaid','paid','failed','refunded') 
NOT NULL DEFAULT 'unpaid';
