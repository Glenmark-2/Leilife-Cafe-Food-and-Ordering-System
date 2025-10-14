<?php
// pages/driver/driver.php
if (session_status() === PHP_SESSION_NONE) session_start();
$rider = ["name" => $_SESSION['driver_name'] ?? "Driver", "shift" => "9:00 AM - 5:00 PM"];
?>
<div class="app-header">
  <h1>My Deliveries</h1>
  <div class="profile">👤 <?= htmlspecialchars($rider["name"]) ?></div>
</div>

<div class="container">
  <div class="rider-card">
    <h2><?= htmlspecialchars($rider["name"]) ?></h2>
    <p>Shift: <?= htmlspecialchars($rider["shift"]) ?></p>
  </div>

  <div id="orderList">
    <p>Loading your assigned orders...</p>
  </div>
</div>

<!-- Modal -->
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



<!-- Scripts -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="/Leilife/Scripts/driver/driver.js"></script>
<script>
  window.currentDriverPage = 'driver';
</script>
