ALTER TABLE favorites
ADD UNIQUE KEY unique_fav (user_id, product_id);
