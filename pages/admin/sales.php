
  <!-- Main Content -->
  <div class="main-content">
    <h1>Sales</h1>
    <div class="cards">
      <div class="card">
        <h3>Total Sales</h3>
        <p>₱10,000</p>
        <small>10% than last month</small>
      </div>
      <div class="card">
        <h3>Total Orders</h3>
        <p>366</p>
        <small>10% than last month</small> 
      </div>
      <div class="card">
        <h3>Top Selling</h3>
        <p>Chicken Manok</p>
        <small>100 Total Orders</small>
      </div>
    </div>

    <div class="charts">
      <div class="left-column">
        <div class="card chart-card">
          <canvas id="salesChart"></canvas>
        </div>

        <div class="heatmap-card">
          <h3>Sales Heatmap</h3>
          <div class="heatmap">
            <?php
            // Dummy daily sales values
            $sales = [5, 20, 50, 75, 120, 0, 30, 60, 10, 90, 150, 200, 40, 70, 15, 5, 100, 30, 60, 80, 110];
            
            foreach ($sales as $value) {
              if ($value <= 25) {
                $level = 1;
              } elseif ($value <= 75) {
                $level = 2;
              } elseif ($value <= 125) {
                $level = 3;
              } else {
                $level = 4;
              }
              echo "<div class='day'><div class='level-$level'></div></div>";
            }
            ?>
          </div>
        </div>
      </div>

    <?php
// Example data: You can replace this with your DB query results
$transactions = [
  ["name" => "Lucy Chen", "date" => "July 1, 2025", "status" => "Pending", "amount" => 300],
  ["name" => "Lucy Chen", "date" => "July 1, 2025", "status" => "Completed", "amount" => 1565],
  ["name" => "Lucy Chen", "date" => "July 1, 2025", "status" => "Completed", "amount" => 676],
  ["name" => "Lucy Chen", "date" => "July 1, 2025", "status" => "Completed", "amount" => 300],
  ["name" => "Lucy Chen", "date" => "July 1, 2025", "status" => "Completed", "amount" => 300],
];
?>

<div class="transactions">
  <div class="transactions-header">
    <h3>Transactions</h3>
    <div class="filter-buttons">
      <button class="active">Newest</button>
      <button>Oldest</button>
    </div>
  </div>

  <ul class="transaction-list">
    <?php foreach ($transactions as $tx): ?>
      <li>
        <div class="left">
          <div class="avatar"></div>
          <div class="details">
            <p class="name"><?= htmlspecialchars($tx["name"]) ?></p>
            <p class="date"><?= htmlspecialchars($tx["date"]) ?></p>
          </div>
        </div>
        <div class="right">
          <span class="tag <?= strtolower($tx["status"]) ?>">
            <?= htmlspecialchars($tx["status"]) ?>
          </span>
          <span class="amount">+ ₱<?= number_format($tx["amount"], 0) ?></span>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug'],
        datasets: [{
          label: 'Monthly Sales (₱)',
          data: [1000, 1500, 2000, 3000, 2500, 3200, 4000, 5000],
          borderColor: '#4CAF50',
          backgroundColor: 'rgba(76, 175, 80, 0.2)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false
      }
    });
  </script>

