<?php
// backend/config/routes.php
return [
    'home'            => __DIR__ . '/../../pages/home.php',
    'menu'            => __DIR__ . '/../../pages/menu.php',
    'orders'          => __DIR__ . '/../../pages/orders.php',
    'signUp'          => __DIR__ . '/../../pages/signUp.php',
    'login'           => __DIR__ . '/../../pages/login.php',
    'solo-product'    => __DIR__ . '/../../pages/solo-product.php',
    'user-profile'    => __DIR__ . '/../../pages/user-profile.php',
    'order-tracking'  => __DIR__ . '/../../pages/order-tracking.php',
    'forgot-password' => __DIR__ . '/../../pages/forgot-password.php',
    'checkout-page'   => __DIR__ . '/../../pages/checkout-page.php',
    'cart'            => __DIR__ . '/../../pages/cart.php',
    // Optional 404 page; router will fall back to it
    '404'             => __DIR__ . '/../../pages/404.php',
];
