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

  <div class="summary">
    <div class="box pending"><span>My Deliveries</span><h2 id="pendingCount">--</h2></div>
    <div class="box preparing"><span>Ready to Deliver</span><h2 id="readyCount">--</h2></div>
    <div class="box delivered"><span>Delivered</span><h2 id="deliveredCount">--</h2></div>
    <div class="box cancelled"><span>Cancelled</span><h2 id="cancelledCount">--</h2></div>
  </div>

  <div class="actions" style="display:flex;flex-direction:column;gap:10px;">
    <a class="contact-btn" href="/Leilife/public/driver.php?page=available">📦 View Available Deliveries</a>
    <a class="complete-btn" href="/Leilife/public/driver.php?page=driver">🚗 View My Deliveries</a>
  </div>
</div>

<!-- Bottom Navigation -->
<nav class="bottom-nav">
  <a href="/Leilife/public/driver.php?page=available">📦 Available</a>
  <a href="/Leilife/public/driver.php?page=driver">🚗 My Deliveries</a>
  <a href="/Leilife/public/driver.php?page=dashboard" class="active">🏠 Dashboard</a>
</nav>

<script>
  window.currentDriverPage = 'dashboard';
</script>
