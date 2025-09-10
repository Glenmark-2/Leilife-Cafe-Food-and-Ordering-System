ALTER TABLE users
ADD COLUMN google_id VARCHAR(50) UNIQUE NULL AFTER email,
ADD COLUMN profile_picture VARCHAR(255) NULL AFTER google_id,
ADD COLUMN google_email_verified TINYINT(1) DEFAULT 1 AFTER profile_picture;
