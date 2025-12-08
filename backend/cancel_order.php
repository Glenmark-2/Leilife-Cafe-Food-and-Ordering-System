<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\OrderController;

$controller = new OrderController();
$controller->cancelOrder();
