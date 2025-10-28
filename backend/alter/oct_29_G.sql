ALTER TABLE refunds 
ADD COLUMN order_item_id INT NULL AFTER order_id;


ALTER TABLE refunds 
ADD CONSTRAINT fk_refunds_order_items 
FOREIGN KEY (order_item_id) REFERENCES order_items(order_item_id)
ON DELETE SET NULL;


ALTER TABLE refunds
ADD COLUMN payment_id VARCHAR(100) NULL AFTER transaction_id;