CREATE TABLE inbox (
    sender_id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('mail', 'feedback') NOT NULL, 
    
    -- Mail fields
    email VARCHAR(255),
    name VARCHAR(255),
    subject VARCHAR(255),
    
    -- Feedback fields
    user_id INT,
    order_id INT,
    
    -- Common fields
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);