-- 1. Add new column `barangay` after `street_address`
ALTER TABLE addresses
ADD COLUMN barangay VARCHAR(100) NULL AFTER street_address;

-- 2. Drop `postal_code` column
ALTER TABLE addresses
DROP COLUMN postal_code;
