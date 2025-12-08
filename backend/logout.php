<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\AuthController;

session_start();
$controller = new AuthController();
$controller->handleLogout();
