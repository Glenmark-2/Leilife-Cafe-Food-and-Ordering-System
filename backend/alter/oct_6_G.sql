ALTER TABLE order_items
ADD COLUMN status ENUM('pending', 'preparing', 'finished') DEFAULT 'pending';
-- done nobi