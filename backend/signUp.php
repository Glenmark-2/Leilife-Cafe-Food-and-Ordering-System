<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\RegistrationController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new RegistrationController();
$controller->handleSignup();
