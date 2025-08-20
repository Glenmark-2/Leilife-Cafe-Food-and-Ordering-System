<?php
$page = $_GET['page'] ?? 'home';

include '../components/header.php';

$allowed_pages = ['home', 'menu', 'orders','signUp'];
if(in_array($page, $allowed_pages)){
    include "../pages/$page.php";
} else {
    echo "<h1>Page not found</h1>";
}

include '../components/footer.php';


