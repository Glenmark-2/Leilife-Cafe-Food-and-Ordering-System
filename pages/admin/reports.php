
<style>
:root {
  --bg-light: #f5f1eb;
  --primary: #8b6f47;
  --secondary: #d2b48c;
  --accent: #a67c52;
  --text-dark: #3e2f1c;
  --white: #fff;
}

/* Title */
h2 {
  color: var(--primary);
  margin-bottom: 25px;
}

/* Report Filters */
.report-filters {
  background: var(--white);
  padding: 15px 20px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  gap: 15px;
  justify-content: space-between;
  flex-wrap: wrap;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  margin-bottom: 25px;
}

.report-filters select, .report-filters input[type="date"] {
  padding: 8px 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}

/* Cards */
.report-cards {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 30px;
}

.report-card {
  flex: 1 1 calc(25% - 20px);
  background: var(--white);
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
  min-width: 220px;
  text-align: center;
}

.report-card h4 {
  color: var(--primary);
  margin-bottom: 10px;
}

.report-card p {
  font-size: 22px;
  color: var(--text-dark);
  font-weight: 600;
}

/* Charts */
.analytics-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.chart-box {
  background: var(--white);
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.chart-box h4 {
  color: var(--primary);
  margin-bottom: 15px;
}

canvas {
  max-width: 100%;
  height: 300px;
}

/* Export Buttons */
.export-section {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.export-section button {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  background-color: var(--primary);
  color: var(--white);
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.export-section button:hover {
  background-color: var(--accent);
}

@media (max-width: 768px) {
  .report-cards {
    flex-direction: column;
  }
}
</style>

<h2>Reports & Analytics</h2>

<!-- Filters -->
<div class="report-filters">
  <div>
    <label for="reportType"><strong>Report Type:</strong></label>
    <select id="reportType">
      <option value="Daily">Daily</option>
      <option value="Weekly">Weekly</option>
      <option value="Monthly">Monthly</option>
    </select>
  </div>
  <div>
    <label for="fromDate"><strong>Date Range:</strong></label>
    <input type="date" id="fromDate"> - <input type="date" id="toDate">
  </div>
  <div>
    <button onclick="generateReport()">Generate Report</button>
  </div>
</div>

<!-- Summary Cards -->
<div class="report-cards">
  <div class="report-card">
    <h4>Total Sales</h4>
    <p>₱45,320</p>
  </div>
  <div class="report-card">
    <h4>Revenue Growth</h4>
    <p>+12%</p>
  </div>
  <div class="report-card">
    <h4>Top Product</h4>
    <p>Iced Latte</p>
  </div>
  <div class="report-card">
    <h4>Active Users</h4>
    <p>1,240</p>
  </div>
</div>

<!-- Charts Section -->
<div class="analytics-charts">
  <div class="chart-box">
    <h4>Sales Trend (₱)</h4>
    <canvas id="salesTrend"></canvas>
  </div>
  <div class="chart-box">
    <h4>Revenue Breakdown by Category</h4>
    <canvas id="revenueBreakdown"></canvas>
  </div>
  <div class="chart-box">
    <h4>User Growth</h4>
    <canvas id="userGrowth"></canvas>
  </div>
  <div class="chart-box">
    <h4>Driver Performance</h4>
    <canvas id="driverPerformance"></canvas>
  </div>
</div>

<!-- Export -->
<div class="export-section">
  <button onclick="alert('Report exported as PDF')">Export PDF</button>
  <button onclick="alert('Report exported as Excel')">Export Excel</button>
  <button onclick="alert('Report exported as CSV')">Export CSV</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function generateReport() {
  alert("Report generated based on filters!");
}

// Chart.js Configuration
const ctxSales = document.getElementById('salesTrend');
new Chart(ctxSales, {
  type: 'line',
  data: {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [{
      label: 'Sales',
      data: [5000, 6200, 5800, 7000, 6500, 7200, 8000],
      borderColor: '#8b6f47',
      backgroundColor: 'rgba(139,111,71,0.2)',
      tension: 0.3,
      fill: true
    }]
  },
  options: { responsive: true }
});

const ctxRevenue = document.getElementById('revenueBreakdown');
new Chart(ctxRevenue, {
  type: 'pie',
  data: {
    labels: ['Drinks', 'Snacks', 'Meals'],
    datasets: [{
      data: [55, 25, 20],
      backgroundColor: ['#a67c52', '#d2b48c', '#8b6f47']
    }]
  },
  options: { responsive: true }
});

const ctxUsers = document.getElementById('userGrowth');
new Chart(ctxUsers, {
  type: 'bar',
  data: {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
      label: 'New Users',
      data: [120, 160, 180, 220],
      backgroundColor: '#d2b48c'
    }]
  },
  options: { responsive: true }
});

const ctxDrivers = document.getElementById('driverPerformance');
new Chart(ctxDrivers, {
  type: 'bar',
  data: {
    labels: ['Driver 1', 'Driver 2', 'Driver 3'],
    datasets: [{
      label: 'Orders Delivered',
      data: [45, 38, 50],
      backgroundColor: '#8b6f47'
    }]
  },
  options: { responsive: true }
});
</script>
