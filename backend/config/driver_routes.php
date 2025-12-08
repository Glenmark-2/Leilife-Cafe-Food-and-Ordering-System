<?php
return [
  // Dashboard / Overview (optional)
  'dashboard' => __DIR__ . '/../../pages/driver/driver-dashboard.php',

  // My Deliveries (assigned to logged-in driver)
  'driver' => __DIR__ . '/../../pages/driver/driver.php',

  // Available Deliveries (unassigned orders)
  'available' => __DIR__ . '/../../pages/driver/driver-available.php',

  // 404 fallback
  '404' => __DIR__ . '/../../pages/driver/driver-404.php',
];
