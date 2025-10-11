<?php
// pages/driver/driver-available.php
if (session_status() === PHP_SESSION_NONE) session_start();
$rider = ["name" => $_SESSION['driver_name'] ?? "Driver"];
?>
<div class="app-header">
  <h1>Available Deliveries</h1>
  <div class="profile">👤 <?= htmlspecialchars($rider["name"]) ?></div>
</div>

<div class="container">
  <div id="availableOrderList">
    <p>Loading available orders...</p>
  </div>
</div>

<!-- Modal -->
<div class="modal" id="availableOrderModal" style="display:none;">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Order Details</h3>
      <span class="close-btn">&times;</span>
    </div>
    <div id="availableModalBody"></div>
  </div>
</div>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
  <a href="/Leilife/public/driver.php?page=available" class="active">📦 Available</a>
  <a href="/Leilife/public/driver.php?page=driver">🚗 My Deliveries</a>
  <a href="/Leilife/public/driver.php?page=dashboard">🏠 Dashboard</a>
</nav>

<!-- Scripts -->
<script src="/Leilife/Scripts/driver/available-orders.js"></script>
<script>
  window.currentDriverPage = 'available';
</script>
