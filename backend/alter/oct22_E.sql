-- Add flavor_set_id column to products table
ALTER TABLE products
ADD COLUMN flavor_set_id INT DEFAULT NULL AFTER has_flavor;

-- Add flavor_set_id column to product_flavors table
ALTER TABLE product_flavors
ADD COLUMN flavor_set_id INT DEFAULT NULL AFTER status;

ALTER TABLE products ADD INDEX (flavor_set_id);
ALTER TABLE product_flavors ADD INDEX (flavor_set_id);

update product_flavors set flavor_set_id =1;
update products set flavor_set_id =1 where has_flavor =1;

SELECT * from products;
SELECT * from product_flavors;

SELECT * from carts;
SELECT * from cart_items;