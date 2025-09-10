-- ALTER TABLE products
-- DROP FOREIGN KEY products_ibfk_1;

-- DROP TABLE IF EXISTS categories;

-- CREATE TABLE categories (
--     main_category_id INT NOT NULL,
--     main_category_name VARCHAR(50) NOT NULL,
--     category_id INT NOT NULL AUTO_INCREMENT,
--     category_name VARCHAR(200) NOT NULL,
--     PRIMARY KEY (category_id)
-- );

-- product_price is also price_medium for drinks, while this new column allows null, since its for large drinks prices only
-- ALTER TABLE products
-- ADD COLUMN price_large DECIMAL(10,2) NULL;

-- ALTER TABLE products
-- MODIFY COLUMN price_large DECIMAL(10,2) NULL AFTER product_price;

-- select * from categories;
