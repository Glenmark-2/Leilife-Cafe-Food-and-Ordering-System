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

  <div class="actions">
    <a class="action-btn available" href="/Leilife/public/driver.php?page=available">📦 View Available Deliveries</a>
    <a class="action-btn deliveries" href="/Leilife/public/driver.php?page=driver">🚗 View My Deliveries</a>
  </div>
</div>



<script>
  window.currentDriverPage = 'dashboard';
</script>
