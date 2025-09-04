<?php
// public/index.php

// 1) Determine requested page
$page = $_GET['page'] ?? 'home';

// 2) Load route map + middleware
$routes = include __DIR__ . '/../backend/config/routes.php';
require_once __DIR__ . '/../backend/middleware/auth.php';

// 3) Bootstrap data layer (so pages can use $appData without including init again)
require_once __DIR__ . '/../backend/db_script/init.php';

// 4) Resolve target file; fall back to 404 if unknown
$target = $routes[$page] ?? $routes['404'];

// 5) Enforce access control
requireLogin($page);

// 6) Render layout + page
include __DIR__ . '/../components/header.php';
include $target;
include __DIR__ . '/../components/footer.php';
