<?php
require_once "db.php";
require_once "appData.php";

$appData = new AppData($pdo);

// preload common stuff
$appData->loadCategories();
$appData->loadProducts();
