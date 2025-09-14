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
    'verify'          => __DIR__ . '/../verify.php',                // <--- new (maps to backend/verify.php)
    'verify_success'  => __DIR__ . '/../../pages/verify_success.php',
    'verify_notice'   => __DIR__ . '/../../components/verify_notice.php',
    'verify_failed'   => __DIR__ . '/../../pages/verify_failed.php',
    'verify_expired'  => __DIR__ . '/../../pages/verify_expired.php',
    'resend_verification' => __DIR__ . '/../resend_verification.php', // ✅ add this
    'change_password' => __DIR__ . '/../../components/change_password.php',

    // Optional 404 page; router will fall back to it
    '404'             => __DIR__ . '/../../pages/404.php',

    // test page for viewing component
    'test_page' => __DIR__ . '/../../pages/test_page.php',

];
