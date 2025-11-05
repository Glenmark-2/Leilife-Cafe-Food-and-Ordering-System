ALTER TABLE orders 
MODIFY payment_status 
ENUM('unpaid', 'paid', 'pending_refund', 'partially_refunded', 'refunded', 'failed') 
NOT NULL DEFAULT 'unpaid';