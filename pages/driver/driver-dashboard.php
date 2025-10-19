<?php
// pages/driver/driver-dashboard.php
if (session_status() === PHP_SESSION_NONE) session_start();
$rider = ["name" => $_SESSION['driver_name'] ?? "Driver", "shift" => "9:00 AM - 5:00 PM"];
?>
<div class="app-header">
  <h1>Driver Dashboard</h1>
  <div class="profile">👤 <?= htmlspecialchars($rider["name"]) ?></div>
</div>

<div class="container">
  <div class="rider-card">
    <h2>Welcome, <?= htmlspecialchars($rider["name"]) ?></h2>
    <p>Shift: <?= htmlspecialchars($rider["shift"]) ?></p>
  </div>

 <div class="summary" role="region" aria-label="Delivery summary">
        <div class="box">
          <small>Delivered</small>
          <h2 id="statAssigned">0</h2>
        </div>
        <div class="box">
          <small>Today Earnings</small>
          <h2 id="statEarnings">₱0.00</h2>
        </div>
      </div>
</div>



<script>
  window.currentDriverPage = 'dashboard';
</script>
