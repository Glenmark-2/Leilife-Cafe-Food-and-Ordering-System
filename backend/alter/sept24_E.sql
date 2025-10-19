CREATE TABLE admin_account (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,      
    email VARCHAR(100) NOT NULL UNIQUE,          
    password VARCHAR(255) NOT NULL,              
    full_name VARCHAR(100),                      
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,                   
    is_active BOOLEAN DEFAULT TRUE               
);

INSERT INTO admin_account (username, email, password, full_name, is_active)
VALUES (
    'mAdmin', 
    'mAdmin@gmail.com', 
    '$2y$10$ZYm54KdpYaYIh6wRmyXbQOz3s/j8fSAVXzk8.tQgXPdIiFxxFtMsS',  -- replace with your hashed password
    'Maumie',
    TRUE
);

ALTER TABLE staff_roles
ADD COLUMN is_archive TINYINT(1) DEFAULT 0;

ALTER TABLE products
ADD COLUMN is_archive TINYINT(1) DEFAULT 0;

ALTER TABLE staff_roles
ADD COLUMN is_admin TINYINT(1) DEFAULT 0;

-- main admin
INSERT INTO staff_roles (staff_name, staff_role, shift, status, staff_image,is_archive, is_admin)
VALUES (
    'Maumie', 
    'Admin', 
    'Day',  
    'Available',
    'Maumie.png',
    0,
    1
);

-- Step 1: Expand ENUM to include both old and new values
ALTER TABLE staff_roles
MODIFY COLUMN status ENUM('Available','Unavailable','Active','Inactive') NOT NULL DEFAULT 'Active';

-- Step 2: Convert old data to new equivalents
UPDATE staff_roles SET status = 'Active' WHERE status = 'Available';
UPDATE staff_roles SET status = 'Inactive' WHERE status = 'Unavailable';

-- Step 3: Shrink ENUM to only the new values
ALTER TABLE staff_roles
MODIFY COLUMN status ENUM('Active','Inactive') NOT NULL DEFAULT 'Active';
