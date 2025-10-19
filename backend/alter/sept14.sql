SET GLOBAL time_zone = '+00:00';
SET time_zone = '+00:00';
------------------------------------------------------------
-- Addresses
ALTER TABLE addresses
ADD CONSTRAINT fk_addresses_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

-- Cart
ALTER TABLE cart
ADD CONSTRAINT fk_cart_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

-- Favorites
ALTER TABLE favorites
ADD CONSTRAINT fk_favorites_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

-- Feedback
ALTER TABLE feedback
ADD CONSTRAINT fk_feedback_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

-- Orders
ALTER TABLE orders
ADD CONSTRAINT fk_orders_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

-- Password Resets
ALTER TABLE password_resets
ADD CONSTRAINT fk_password_resets_user
FOREIGN KEY (user_id) REFERENCES users(user_id)
ON DELETE CASCADE;

----------------------------------------------------------------

-- Addresses: drop old conflicting FK
ALTER TABLE addresses
DROP FOREIGN KEY addresses_ibfk_1;

-- Cart: drop old conflicting FK if exists
ALTER TABLE cart
DROP FOREIGN KEY cart_ibfk_1;

-- Favorites: drop old conflicting FK if exists
ALTER TABLE favorites
DROP FOREIGN KEY favorites_ibfk_1;

-- Feedback: drop old conflicting FK if exists
ALTER TABLE feedback
DROP FOREIGN KEY feedback_ibfk_2;

-- Orders: drop old conflicting FK if exists
ALTER TABLE orders
DROP FOREIGN KEY orders_ibfk_1;

-- Password Resets: drop old conflicting FK if exists
ALTER TABLE password_resets
DROP FOREIGN KEY fk_pr_user;
