<?php
// if (!isset($_SESSION['driver_id'])) {
//   header('Location: /leilife/pages/admin/login-driver-123.php');
//   exit;
//}

$rider = ["name" => "Tony Rider", "shift" => "9:00 AM - 5:00 PM"];
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Driver Dashboard</title>
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>
  <div class="app-header">
    <h1>Driver Dashboard</h1>
    <div class="profile">👤 <?= htmlspecialchars($rider["name"]) ?></div>
  </div>

  <div class="container">
    <div class="rider-card">
      <h2><?= htmlspecialchars($rider["name"]) ?></h2>
      <p>Shift: <?= htmlspecialchars($rider["shift"]) ?></p>
    </div>

    <div class="summary">
      <div class="box pending"><span>Pending</span><h2 id="pendingCount">0</h2></div>
      <div class="box preparing"><span>Preparing</span><h2 id="preparingCount">0</h2></div>
      <div class="box delivered"><span>Delivered</span><h2 id="deliveredCount">0</h2></div>
      <div class="box cancelled"><span>Cancelled</span><h2 id="cancelledCount">0</h2></div>
    </div>

    <div id="orderList">
      <p>Loading orders...</p>
    </div>
  </div>

  <div class="modal" id="orderModal" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Order Details</h3>
        <span class="close-btn">&times;</span>
      </div>
      <div id="modalBody"></div>
      <div id="mapContainer"></div>
      <div id="directionsPanel"></div>
    </div>
  </div>

</body>
<!-- Leaflet + Driver scripts -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="/Leilife/Scripts/driver/driver.js"></script>
</html>
