<?php
// backend/config/style_config.php
return [
    'home' => [
        '../CSS/pages/home.css',
        '../CSS/partials/card.css',
        '../CSS/components/reco-card.css',
        '../CSS/components/reco-carousel.css'
    ],
    'menu' => [
        '../CSS/pages/menu.css',
        '../CSS/partials/menu-card.css'
    ],
    'cart'            => ['../CSS/pages/cart.css'],
    'checkout-page'   => ['../CSS/pages/checkout-page.css'],
    'signUp'          => ['../CSS/pages/signUp.css'],
    'login'           => ['../CSS/pages/login.css'],
    'solo-product'    => ['../CSS/pages/solo-product.css'],
    'user-profile'    => ['../CSS/pages/user-profile.css','../CSS/admin/components/set-address-modal.css'],
    'order-tracking'  => ['../CSS/pages/order-tracking.css'],
    'forgot-password' => ['../CSS/pages/forgot-password.css'],
    'verify_success'  => ['../CSS/pages/verify_success.css'],
    'test_page'       => ['../CSS/components/verify_notice.css'],

    
     // optional: keep alias so previous links still pull the style
     'verify_notice' => ['../CSS/components/verify_notice.css'],


];
