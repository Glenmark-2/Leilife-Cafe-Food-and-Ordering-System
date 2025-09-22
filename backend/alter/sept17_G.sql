ALTER TABLE orders
ADD COLUMN payment_method ENUM('gcash','grabpay','card','cod') NULL,
ADD COLUMN paymongo_id VARCHAR(100) NULL,
ADD COLUMN payment_status ENUM('unpaid','paid','failed','refunded') DEFAULT 'unpaid';


ALTER TABLE order_items
ADD COLUMN size ENUM('medium','large') NULL,
ADD COLUMN flavor_ids VARCHAR(100) NULL;
