<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\FavoriteController;

$controller = new FavoriteController();
$controller->handleToggle();
