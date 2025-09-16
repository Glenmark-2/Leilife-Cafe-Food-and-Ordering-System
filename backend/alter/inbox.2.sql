ALTER TABLE inbox
ADD COLUMN status TINYINT(1) DEFAULT 0 AFTER message,
ADD COLUMN is_archived TINYINT(1) DEFAULT 0 AFTER status;

-- status → 0 = unread, 1 = read
-- is_archived → 0 = active, 1 = archived