<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\CartController;

$controller = new CartController();
$controller->getCartCount();
