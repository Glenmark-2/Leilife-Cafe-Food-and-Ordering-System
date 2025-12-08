-- Add payment-related fields to orders
ALTER TABLE orders 
ADD COLUMN payment_method ENUM('cash','gcash') DEFAULT 'cash' AFTER total,
ADD COLUMN payment_status ENUM('unpaid','paid','failed') DEFAULT 'unpaid' AFTER payment_method,
ADD COLUMN payment_ref VARCHAR(100) NULL AFTER payment_status;
