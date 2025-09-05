<?php
// Dummy data (replace with DB queries)
$totalSales = 10;
$totalOrders = 10;
$topSelling = ["name" => "Chicken Manok", "orders" => 100];
$transactions = [
    ["id" => "TXN001", "name" => "Lucy Chen", "date" => "2025-09-01", "amount" => 250, "status" => "Pending"],
    ["id" => "TXN002", "name" => "Mark Reyes", "date" => "2025-09-02", "amount" => 300, "status" => "Awaiting Confirmation"],
    ["id" => "TXN003", "name" => "Anna Cruz", "date" => "2025-09-03", "amount" => 150, "status" => "Delivered"],
    ["id" => "TXN004", "name" => "John Doe", "date" => "2025-09-04", "amount" => 420, "status" => "Cancelled"],
    ["id" => "TXN005", "name" => "Maria Santos", "date" => "2025-09-05", "amount" => 500, "status" => "Delivered"],
];
$salesData = [50, 70, 65, 90, 80, 100, 110, 95, 105, 120, 130, 140];
$heatmap = [
    "Mon" => [10, 20, 30, 40, 50, 60, 70],
    "Tue" => [15, 25, 35, 45, 55, 65, 75],
    "Wed" => [20, 30, 40, 50, 60, 70, 80],
    "Thu" => [25, 35, 45, 55, 65, 75, 85],
    "Fri" => [30, 40, 50, 60, 70, 80, 90],
    "Sat" => [35, 45, 55, 65, 75, 85, 95],
    "Sun" => [40, 50, 60, 70, 80, 90, 100],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="dashboard">
    <!-- Header -->
    <div class="header">
      <h2>Sales</h2>
      <select><option>Tony</option></select>
    </div>

    <!-- ONE BOX: Total Sales + Orders -->
    <div class="summary-box">
      <div class="summary-item">
        <h3><?= $totalSales ?></h3>
        <p>Total Sales</p>
      </div>
      <div class="summary-item">
        <h3><?= $totalOrders ?></h3>
        <p>Total Orders</p>
      </div>
    </div>

    <!-- Top Selling -->
    <!-- Top Selling -->
<div class="panel top-selling">
  <h4>Top Selling</h4>
  <div class="top-selling-item">
    <div class="thumb">
      <img src="" alt="Top Selling Item" />
    </div>
    <div class="info">
      <p><strong><?= $topSelling['name'] ?></strong><br><?= $topSelling['orders'] ?> Total Orders</p>
    </div>
  </div>
</div>


    <!-- Transactions -->
<!-- Transactions -->
<div class="panel transactions">
  <div class="transactions-header">
    <h4>Transactions</h4>
    <div class="filter-buttons">
      <button onclick="filterTransactions('newest')" class="active">Newest</button>
      <button onclick="filterTransactions('oldest')">Oldest</button>
    </div>
  </div>
  <div id="transaction-list">
    <?php foreach ($transactions as $index => $t): ?>
      <div class="item" data-index="<?= $index ?>" data-date="<?= $t['date'] ?>">
        <div class="avatar"><?= strtoupper($t['name'][0]) ?></div>
        <div class="details">
          <div class="main-line">
            <strong class="transaction-name"><?= $t['name'] ?></strong>
            <span class="transaction-id"><?= $t['id'] ?></span>
            <span class="transaction-date"><?= $t['date'] ?></span>
            <span class="transaction-amount">₱<?= number_format($t['amount'], 2) ?></span>
          </div>
          <div class="subtext">Deliver</div>
        </div>
        <span class="status <?= $t['status'] ?>"><?= $t['status'] ?></span>
        <!-- Ellipsis for mobile -->
        <span class="ellipsis" onclick="openTransactionModal(<?= $index ?>)">&#8942;</span>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<div class="panel heatmap"> <h4>Weekly Sales Heatmap</h4> <div class="heatmap-grid"> <div></div> <?php foreach (array_keys($heatmap['Mon']) as $i): ?> <div><?= $i+1 ?></div> <?php endforeach; ?> <?php foreach ($heatmap as $day => $values): ?> <div><?= $day ?></div> <?php foreach ($values as $v): $green = max(50, 255 - $v); ?> <div class="heatmap-cell" style="background: rgb(0,<?= $green ?>,0);"></div> <?php endforeach; ?> <?php endforeach; ?> </div> </div>
    <!-- Chart -->
    <div class="panel chart">
      <canvas id="salesChart"></canvas>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
          label: 'Monthly Sales',
          data: <?= json_encode($salesData) ?>,
          borderColor: 'green',
          borderWidth: 2,
          pointRadius: 3,
          fill: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true } }
      }
    });

    // Sorting
    function filterTransactions(order) {
      const list = document.getElementById("transaction-list");
      const items = Array.from(list.children);

      items.sort((a, b) => {
        const dateA = new Date(a.dataset.date);
        const dateB = new Date(b.dataset.date);
        return order === "oldest" ? dateA - dateB : dateB - dateA;
      });

      list.innerHTML = "";
      items.forEach(item => list.appendChild(item));

      document.querySelectorAll(".filter-buttons button").forEach(btn => btn.classList.remove("active"));
      document.querySelector(`.filter-buttons button[onclick="filterTransactions('${order}')"]`).classList.add("active");
    }

    // Default sort = newest
    filterTransactions("newest");
     function openTransactionModal(index) {
    // Assuming the modal component is a PHP file that you include dynamically
    const modalUrl = `transaction_modal.php?index=${index}`;
    fetch(modalUrl)
      .then(response => response.text())
      .then(html => {
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = html;
        document.body.appendChild(modalContainer);
      })
      .catch(err => console.error(err));
  }

  </script>
</body>
</html>
