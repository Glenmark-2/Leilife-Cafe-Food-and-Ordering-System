ALTER TABLE orders
ADD COLUMN delivery_method ENUM('pickup', 'home') NOT NULL;
ALTER TABLE orders MODIFY COLUMN status ENUM('pending','preparing','ready_for_delivery','delivered','cancelled','picked_up') NOT NULL;