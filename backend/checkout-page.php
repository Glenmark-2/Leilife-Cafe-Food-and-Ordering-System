<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\CheckoutController;

$controller = new CheckoutController();
$controller->handleRequest();
 