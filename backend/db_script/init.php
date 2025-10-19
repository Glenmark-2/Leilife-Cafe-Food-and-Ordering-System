<?php
require_once "db.php";
require_once "AppData.php";

$appData = new AppData($pdo);

// preload common stuff
$appData->loadCategories();
$appData->loadProducts();
