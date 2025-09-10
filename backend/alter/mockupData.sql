-- CATEGORIES
-- INSERT INTO categories (main_category_id, main_category_name, category_name) VALUES
-- (1, 'Meal', 'rice_meal'),
-- (1, 'Meal', 'ala_carte'),
-- (1, 'Meal', 'all_day_breakfast'),
-- (1, 'Meal', 'pasta'),
-- (1, 'Meal', 'snacks'),
-- (1, 'Meal', 'sandwiches'),
-- (2, 'Drinks', 'milktea'),
-- (2, 'Drinks', 'premium_milktea'),
-- (2, 'Drinks', 'soda_series'),
-- (2, 'Drinks', 'iced_coffee'),
-- (2, 'Drinks', 'hot_coffee'),
-- (2, 'Drinks', 'frappuccino');
-- (2, 'Drinks', 'iced_non_coffee');


--PRODUCTS
-- Meals
-- INSERT INTO products (category_id, product_name, product_price, price_large, status, product_picture)
-- VALUES
-- -- -- rice_meal (category_id = 1)
-- (1, 'cheesy bacon & egg', 79, NULL, 'available', 'cheesy_bacon_&_egg.jpg'),
-- (1, 'cheesy chicken poppers', 89, NULL, 'available', 'cheesy_chicken_poppers.jpg'),
-- (1, 'sweet & sour chicken', 120, NULL, 'available', 'sweet_&_sour_chicken.jpg'),
-- (1, 'sweet & sour fish fillet', 120, NULL, 'available', 'sweet_&_sour_fish_fillet.jpg'),
-- (1, 'sweet & sour pork', 135, NULL, 'available', 'sweet_&_sour_pork.jpg'),
-- (1, 'sweet & sour shrimp', 145, NULL, 'available', 'sweet_&_sour_shrimp.jpg'),
-- (1, '3 pcs chicken wings', 99, NULL, 'available', '3_pcs_chicken_wings.jpg'),

-- -- -- ala_carte (category_id = 2)
-- (2, 'tempura solo', 99, NULL, 'available', 'tempura_solo.jpg'),
-- (2, 'tempura sharing', 189, NULL, 'available', 'tempura_sharing.jpg'),
-- (2, 'chicken wings solo', 179, NULL, 'available', 'chicken_wings_solo.jpg'),
-- (2, 'chicken wings sharing', 355, NULL, 'available', 'chicken_wings_sharing.jpg'),

-- -- -- all_day_breakfast (category_id = 3)
-- (3, 'hungarian', 120, NULL, 'available', 'hungarian.jpg'),
-- (3, 'bangus', 120, NULL, 'available', 'bangus.jpg'),
-- (3, 'tapa', 125, NULL, 'available', 'tapa.jpg'),
-- (3, 'tocino', 120, NULL, 'available', 'tocino.jpg'),
-- (3, 'shanghai', 110, NULL, 'available', 'shanghai.jpg'),
-- (3, 'Fried chicken', 120, NULL, 'available', 'fried_chicken.jpg'),
-- (3, 'Spam', 120, NULL, 'available', 'spam.jpg'),
-- (3, 'pork chop', 120, NULL, 'available', 'porkchop.jpg'),

-- -- -- pasta (category_id = 4)
-- (4, 'carbonara', 89, NULL, 'available', 'carbonara.jpg'),
-- (4, 'spaghetti', 79, NULL, 'available', 'spaghetti.jpg'),
-- (4, 'mac & cheese', 89, NULL, 'available', 'mac_&_cheese.jpg'),
-- (4, 'macaroni', 89, NULL, 'available', 'macaroni.jpg'),

-- -- -- snacks (category_id = 5) 
-- (5, 'nachos', 75, NULL, 'available', 'nachos.jpg'),
-- (5, 'fries', 49, NULL, 'available', 'fries.jpg'),
-- (5, 'chicken poppers', 59, NULL, 'available', 'chicken_poppers.jpg'),
-- (5, 'chic n pops sharing', 235, NULL, 'available', 'chic_n_pops_sharing.jpg'),
-- (5, 'nuggets', 59, NULL, 'available', 'nuggets.jpg'),

-- -- -- sandwiches (category_id = 6)
-- (6, 'ham & cheese', 135, NULL, 'available', 'ham_&_cheese.jpg'),
-- (6, 'chicken', 140, NULL, 'available', 'chicken.jpg'),
-- (6, 'bacon & cheese', 135, NULL, 'available', 'bacon_&_cheese.jpg');


-- -- Drinks
-- INSERT INTO products (category_id, product_name, product_price, price_large, status, product_picture)
-- VALUES
-- -- MILKTEA (category_id = 7)
-- (7, 'okinawa', 39, 49, 'available', 'milktea_okinawa.jpg'),
-- (7, 'wintermelon', 39, 49, 'available', 'milktea_wintermelon.jpg'),
-- (7, 'cookies&cream', 39, 49, 'available', 'milktea_cookies_cream.jpg'),
-- (7, 'dark choco', 39, 49, 'available', 'milktea_dark_choco.jpg'),
-- (7, 'chocolate', 39, 49, 'available', 'milktea_chocolate.jpg'),
-- (7, 'strawberry', 39, 49, 'available', 'milktea_strawberry.jpg'),
-- (7, 'taro', 39, 49, 'available', 'milktea_taro.jpg'),
-- (7, 'matcha', 39, 49, 'available', 'milktea_matcha.jpg'),
-- (7, 'red velvet', 39, 49, 'available', 'milktea_red_velvet.jpg'),
-- (7, 'mango', 39, 49, 'available', 'milktea_mango.jpg'),
-- (7, 'cheesecake', 39, 49, 'available', 'milktea_cheesecake.jpg'),

-- -- -- Premium MILKTEA (category_id = 8)
-- (8, 'matcha oreo', 59, 69, 'available', 'premium_matcha_oreo.jpg'),
-- (8, 'oreo cheesecake', 59, 69, 'available', 'premium_oreo_cheesecake.jpg'),
-- (8, 'taro oreo', 59, 69, 'available', 'premium_taro_oreo.jpg'),
-- (8, 'choco cheesecake', 59, 69, 'available', 'premium_choco_cheesecake.jpg'),
-- (8, 'red velvet cheesecake', 59, 69, 'available', 'premium_red_velvet_cheesecake.jpg'),

-- -- -- Soda series (category_id = 9)
-- (9, 'blueberry soda ade', 65, 85, 'available', 'soda_blueberry_ade.jpg'),
-- (9, 'strawberry soda ade', 65, 85, 'available', 'soda_strawberry_ade.jpg'),
-- (9, 'lychee soda ade', 65, 85, 'available', 'soda_lychee_ade.jpg'),
-- (9, 'lemon soda ade', 65, 85, 'available', 'soda_lemon_ade.jpg'),
-- (9, 'green apple soda ade', 65, 85, 'available', 'soda_green_apple_ade.jpg'),

-- -- -- Iced coffee (category_id = 10)
-- (10, 'americano', 69, 79, 'available', 'iced_americano.jpg'),
-- (10, 'spanish latte', 69, 79, 'available', 'iced_spanish_latte.jpg'),
-- (10, 'cafe latte', 69, 79, 'available', 'iced_cafe_latte.jpg'),
-- (10, 'caramel macchiato', 69, 79, 'available', 'iced_caramel_macchiato.jpg'),
-- (10, 'salted caramel', 69, 79, 'available', 'iced_salted_caramel.jpg'),
-- (10, 'matcha espresso', 69, 79, 'available', 'iced_matcha_espresso.jpg'),
-- (10, 'matcha strawberry', 69, 79, 'available', 'iced_matcha_strawberry.jpg'),
-- (10, 'mocha', 69, 79, 'available', 'iced_mocha.jpg'),
-- (10, 'white mocha', 69, 79, 'available', 'iced_white_mocha.jpg'),

-- -- -- Iced non-coffee (category_id = 13)
-- (13, 'matcha latte', 69, 79, 'available', 'noncoffee_matcha_latte.jpg'),
-- (13, 'strawberry & cream', 69, 79, 'available', 'noncoffee_strawberry_cream.jpg'),
-- (13, 'matcha strawberry', 79, 89, 'available', 'noncoffee_matcha_strawberry.jpg'),
-- (13, 'chocolate', 69, 79, 'available', 'noncoffee_chocolate.jpg'),

-- -- -- Hot coffee (category_id = 11)
-- (11, 'americano', 69, 79, 'available', 'hot_americano.jpg'),
-- (11, 'spanish latte', 69, 79, 'available', 'hot_spanish_latte.jpg'),
-- (11, 'cappuccino', 69, 79, 'available', 'hot_cappuccino.jpg'),
-- (11, 'white mocha', 69, 79, 'available', 'hot_white_mocha.jpg'),
-- (11, 'hot chocolate', 69, 79, 'available', 'hot_chocolate.jpg'),

-- -- -- Frappuccino (category_id = 12)
-- (12, 'javachips', 89, 99, 'available', 'frap_javachips.jpg'),
-- (12, 'caramel macchiato', 89, 99, 'available', 'frap_caramel_macchiato.jpg'),
-- (12, 'salted caramel', 89, 99, 'available', 'frap_salted_caramel.jpg'),
-- (12, 'spanish latte', 89, 99, 'available', 'frap_spanish_latte.jpg'),
-- (12, 'mocha', 89, 99, 'available', 'frap_mocha.jpg'),
-- (12, 'white mocha', 89, 99, 'available', 'frap_white_mocha.jpg'),
-- (12, 'french vanilla', 89, 99, 'available', 'frap_french_vanilla.jpg'),
-- (12, 'cheesecake', 89, 99, 'available', 'frap_cheesecake.jpg'),
-- (12, 'strawberry & cream', 89, 99, 'available', 'frap_strawberry_cream.jpg'),
-- (12, 'chocolate', 89, 99, 'available', 'frap_chocolate.jpg'),
-- (12, 'dark choco', 89, 99, 'available', 'frap_dark_choco.jpg'),
-- (12, 'cookies & cream', 89, 99, 'available', 'frap_cookies_cream.jpg'),
-- (12, 'matcha', 89, 99, 'available', 'frap_matcha.jpg');

--PRODUCT FLAVORS
-- insert into product_flavors (product_id,flavor_name) VALUES 
-- (7, 'buffalo'),
-- (7, 'sweet & spicy'),
-- (7, 'teriyaki'),
-- (7, 'cheese'),
-- (7, 'ala king'),
-- (7, 'spicy bbq'),
-- (7, 'barbeque'),
-- (7, 'salt & pepper'),
-- (11, 'buffalo'),
-- (11, 'sweet & spicy'),
-- (11, 'teriyaki'),
-- (11, 'cheese'),
-- (11, 'ala king'),
-- (11, 'spicy bbq'),
-- (11, 'barbeque'),
-- (11, 'salt & pepper');



