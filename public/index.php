<?php
$page = $_GET['page'] ?? 'home';

include '../components/header.php';

$allowed_pages = ['home', 'menu', 'orders','signUp','login','solo-product','user-profile','order-tracking','forgot-password','checkout-page','cart'];
if(in_array($page, $allowed_pages)){
    include "../pages/$page.php";
} else {
    echo "<h1>Page not found</h1>";
    echo "<h1>Error 404</h1>";
}

include '../components/footer.php';


