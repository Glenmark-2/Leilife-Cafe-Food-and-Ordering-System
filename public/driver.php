<?php
// public/driver.php
session_start();

// 1) Determine requested page
$page = $_GET['page'] ?? 'driver';

// 2) Load route map + middleware
$routes = include __DIR__ . '/../backend/config/driver_routes.php';
require_once __DIR__ . '/../backend/middleware/driver_auth.php';

// 3) Bootstrap data layer
// require_once __DIR__ . '/../backend/db_script/init.php';

// 4) Resolve target file; fall back to 404 if unknown
$target = $routes[$page] ?? $routes['404'];

// 5) Enforce access control
requireDriverLogin($page);

// 6) Load style config for driver pages
$styleConfig = include __DIR__ . '/../backend/config/style_config_driver.php';
$cssFiles = $styleConfig[$page] ?? [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Driver Dashboard</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <?php foreach ($cssFiles as $css): ?>
    <link rel="stylesheet" href="/Leilife/<?php echo htmlspecialchars($css); ?>">
  <?php endforeach; ?>
</head>
<body>

<div id="driverPageContent">
  <?php include $target; ?>
</div>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
  <a href="/Leilife/public/driver.php?page=available" class="active">📦 Available</a>
  <a href="/Leilife/public/driver.php?page=driver">🚗 My Deliveries</a>
  <a href="/Leilife/public/driver.php?page=dashboard">🏠 Dashboard</a>
</nav>

<script>
  window.currentDriverPage = "<?php echo htmlspecialchars($page); ?>";
</script>
</body>
</html>
