<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (!isset($_SESSION['admin_id'])) {
  header('Location: /Leilife/public/index.php');
  exit;
} 
?>

<style>
:root {
  --bg-light: #f5f1eb;
  --primary: #8b6f47;
  --secondary: #d2b48c;
  --accent: #a67c52;
  --text-dark: #3e2f1c;
  --white: #fff;
}

body {
  background: var(--bg-light);
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0 10px;
}

h2 {
  color: var(--primary);
  margin: 15px 0 25px;
  font-size: 24px;
}

/* Filters */
.report-filters {
  background: var(--white);
  padding: 12px 18px;
  border-radius: 10px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  margin-bottom: 20px;
}
.report-filters select, 
.report-filters input[type="date"], 
.report-filters button {
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
}
.report-filters button {
  background-color: var(--primary);
  color: var(--white);
  border: none;
  cursor: pointer;
}
.report-filters button:hover {
  background-color: var(--accent);
}

/* KPI cards - compact */
.report-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 25px;
}
.report-card {
  background: var(--white);
  border-radius: 10px;
  padding: 14px 16px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  text-align: left;
}
.report-card h4 {
  color: var(--text-dark);
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 6px;
}
.report-card p {
  font-size: 18px;
  color: var(--primary);
  font-weight: 700;
  margin: 0;
}

/* Charts grid */
.analytics-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 22px;
  margin-bottom: 30px;
}
.chart-box {
  background: var(--white);
  border-radius: 12px;
  padding: 18px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.chart-box h4 {
  color: var(--primary);
  margin-bottom: 12px;
  font-size: 15px;
}
canvas {
  width: 100%;
  height: 320px;
}

/* Export section */
.export-section {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-bottom: 30px;
}
.export-section button {
  padding: 8px 14px;
  border: none;
  border-radius: 6px;
  background-color: var(--primary);
  color: var(--white);
  font-weight: 600;
  cursor: pointer;
}
.export-section button:hover {
  background-color: var(--accent);
}
</style>

<h2>Reports & Analytics</h2>

<!-- Filters -->
<div class="report-filters">
  <div>
    <label><strong>Report Type:</strong></label>
    <select>
      <option>Daily</option>
      <option>Weekly</option>
      <option>Monthly</option>
    </select>
  </div>
<div>
  <label><strong>Date Range:</strong></label>
  <input type="date" id="fromDate"> -
  <input type="date" id="toDate">
</div>

  <div>
    <button>Generate</button>
  </div>
</div>

<!-- KPI Summary -->
<div class="report-cards">
<div class="report-card">
  <h4>Total Sales</h4>
  <p id="totalSales">Loading…</p>
</div>
  <div class="report-card">
  <h4>Total Orders</h4>
  <p id="totalOrders">Loading…</p>
</div>
<div class="report-card">
  <h4>Average Order Value</h4>
  <p id="avgOrderValue">Loading…</p>
</div>
<div class="report-card">
  <h4>Revenue Growth</h4>
  <p id="revenueGrowth">Loading…</p>
</div>
<div class="report-card">
  <h4>Top Product</h4>
  <p id="topProductName">Loading…</p>
  <small id="topProductDetails" style="color:#555;font-size:13px;"></small>
</div>
<div class="report-card">
  <h4>Top Customer</h4>
  <p id="topCustomerName">Loading…</p>
  <small id="topCustomerDetails" style="color:#555;font-size:13px;"></small>
</div>
</div>

<!-- Charts Section -->
<div class="analytics-charts">
  <div class="chart-box">
    <h4>Sales Trend (₱)</h4>
    <canvas id="salesTrend"></canvas>
  </div>
  <div class="chart-box">
    <h4>Revenue by Category</h4>
    <canvas id="revenueBreakdown"></canvas>
  </div>
  <div class="chart-box">
    <h4>Customer Growth</h4>
    <canvas id="userGrowth"></canvas>
  </div>
  <div class="chart-box">
    <h4>Customer Sentiment Summary</h4>
    <canvas id="sentimentChart"></canvas>
    <p id="pendingNotice" style="color:#777;font-size:14px;margin-top:8px;"></p>
    <p id="sentimentError" style="color:#c00;font-size:13px;margin-top:8px;display:none;"></p>
  </div>
</div>

<!-- Export Buttons -->
<div class="export-section">
  <button>Export PDF</button>
  <button>Export Excel</button>
  <button>Export CSV</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/* SAMPLE CHART DATA */
new Chart(document.getElementById('salesTrend'), {
  type: 'line',
  data: {
    labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
    datasets: [{
      label: 'Sales',
      data: [5000,6200,5800,7000,6500,7200,8000],
      borderColor: '#8b6f47',
      backgroundColor: 'rgba(139,111,71,0.2)',
      tension: 0.3,
      fill: true
    }]
  },
  options: { responsive: true }
});

new Chart(document.getElementById('revenueBreakdown'), {
  type: 'pie',
  data: {
    labels: ['Drinks','Snacks','Meals'],
    datasets: [{
      data: [55,25,20],
      backgroundColor: ['#a67c52','#d2b48c','#8b6f47']
    }]
  },
  options: { responsive: true }
});

new Chart(document.getElementById('userGrowth'), {
  type: 'bar',
  data: {
    labels: ['Week 1','Week 2','Week 3','Week 4'],
    datasets: [{
      label: 'New Customers',
      data: [120,160,180,220],
      backgroundColor: '#d2b48c'
    }]
  },
  options: { responsive: true }
});

/* KEEP YOUR SENTIMENT FETCH AS IS */
const sentimentEndpoint = '/Leilife/backend/admin/fetch_inbox_sentiment.php';
const canvas = document.getElementById('sentimentChart');
const pendingEl = document.getElementById('pendingNotice');
const errEl = document.getElementById('sentimentError');
if (canvas) {
  const ctx = canvas.getContext('2d');
  let sentimentChart = null;
  async function fetchSentiment() {
    try {
      const res = await fetch(sentimentEndpoint, { cache: 'no-store' });
      const data = await res.json();
      const stats = {
        POSITIVE: Number(data.POSITIVE ?? data.positive ?? 0),
        NEGATIVE: Number(data.NEGATIVE ?? data.negative ?? 0),
        NEUTRAL:  Number(data.NEUTRAL  ?? data.neutral  ?? 0),
        PENDING:  Number(data.PENDING  ?? data.pending  ?? 0)
      };
      const labels = ['Positive','Negative','Neutral','Pending'];
      const values = [stats.POSITIVE, stats.NEGATIVE, stats.NEUTRAL, stats.PENDING];
      const colors = ['#4caf50','#f44336','#2196f3','#9e9e9e'];
      if (!sentimentChart) {
        sentimentChart = new Chart(ctx, {
          type: 'bar',
          data: { labels, datasets: [{ data: values, backgroundColor: colors }] },
          options: { responsive:true, scales:{ y:{ beginAtZero:true } } }
        });
      } else {
        sentimentChart.data.datasets[0].data = values;
        sentimentChart.update();
      }
      pendingEl.textContent = stats.PENDING>0 ? `⚠️ ${stats.PENDING} pending` : '';
      errEl.style.display='none';
    } catch(e){ console.error(e); errEl.style.display='block'; errEl.textContent='Could not load sentiment data'; }
  }
  fetchSentiment();
  setInterval(fetchSentiment, 10000);
}
const formatPHP = (num) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 2 }).format(num);
};

let totalSalesValue = 0;
let totalOrdersValue = 0;

function updateAverageOrderValue() {
  const el = document.getElementById('avgOrderValue');
  if (!el) return;

  if (totalOrdersValue === 0) {
    el.textContent = '₱0.00';
  } else {
    const avg = totalSalesValue / totalOrdersValue;
    el.textContent = new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 2
    }).format(avg);
  }
}


async function fetchTotalSales(fromDate, toDate) {
  const url = new URL('/Leilife/backend/admin/get_total_sales.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Unknown response');

    totalSalesValue = Number(data.total_sales ?? 0);

    const el = document.getElementById('totalSales');
    if (el) el.textContent = formatPHP(totalSalesValue);

    updateAverageOrderValue(); // update AOV whenever total sales updates
  } catch (err) {
    console.error('Failed to fetch total sales:', err);
    const el = document.getElementById('totalSales');
    if (el) el.textContent = '—';
  }
}

async function fetchTotalOrders(fromDate, toDate) {
  const url = new URL('/Leilife/backend/admin/get_total_orders.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Unknown response');

    totalOrdersValue = Number(data.total_orders ?? 0);

    const el = document.getElementById('totalOrders');
    if (el) el.textContent = totalOrdersValue.toLocaleString();

    updateAverageOrderValue(); // update AOV whenever total orders updates
  } catch (err) {
    console.error('Failed to fetch total orders:', err);
    const el = document.getElementById('totalOrders');
    if (el) el.textContent = '—';
  }
}

async function fetchRevenueGrowth(fromDate, toDate) {
  const url = new URL('/Leilife/backend/admin/get_revenue_growth.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Unknown response');

    const el = document.getElementById('revenueGrowth');
    if (!el) return;

    const growth = Number(data.growth_percent ?? 0);
    const formatted =
      growth > 0 ? `+${growth.toFixed(2)}% 📈` :
      growth < 0 ? `${growth.toFixed(2)}% 📉` :
      '0.00%';

    el.textContent = formatted;
    el.style.color = growth > 0 ? '#4caf50' : (growth < 0 ? '#f44336' : '#3e2f1c');

  } catch (err) {
    console.error('Failed to fetch revenue growth:', err);
    const el = document.getElementById('revenueGrowth');
    if (el) el.textContent = '—';
  }
}
async function fetchTopProduct(fromDate, toDate) {
  const url = new URL('/Leilife/backend/admin/get_top_product.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Unknown');

    const nameEl = document.getElementById('topProductName');
    const detailsEl = document.getElementById('topProductDetails');
    const formatPHP = (n) =>
      new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
      }).format(n);

    nameEl.textContent = data.product_name;
    detailsEl.textContent = data.total_revenue > 0
      ? `${formatPHP(data.total_revenue)} • ${data.total_quantity} sold`
      : 'No sales data';

  } catch (err) {
    console.error('fetchTopProduct failed:', err);
    document.getElementById('topProductName').textContent = '—';
    document.getElementById('topProductDetails').textContent = '';
  }
}

async function fetchTopCustomer(fromDate, toDate) {
  const url = new URL('/Leilife/backend/admin/get_top_customer.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Unknown');

    const nameEl = document.getElementById('topCustomerName');
    const detailsEl = document.getElementById('topCustomerDetails');
    const formatPHP = (n) =>
      new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
      }).format(n);

    nameEl.textContent = data.customer_name;
    detailsEl.textContent = data.total_spent > 0
      ? `${formatPHP(data.total_spent)} • ${data.total_orders} orders`
      : 'No data found';

  } catch (err) {
    console.error('fetchTopCustomer failed:', err);
    document.getElementById('topCustomerName').textContent = '—';
    document.getElementById('topCustomerDetails').textContent = '';
  }
}



// 🔁 Automatically update total sales whenever date inputs change
document.addEventListener('DOMContentLoaded', () => {
  const fromInput = document.getElementById('fromDate');
  const toInput = document.getElementById('toDate');

  if (fromInput && !fromInput.value)
    fromInput.value = new Date().toISOString().slice(0, 10);
  if (toInput && !toInput.value)
    toInput.value = new Date().toISOString().slice(0, 10);

  // Initial load
  fetchTotalSales(fromInput?.value, toInput?.value);

  // Auto-update when date inputs change
  if (fromInput) {
    fromInput.addEventListener('change', () => {
      fetchTotalSales(fromInput.value, toInput?.value);
    });
  }

  if (toInput) {
    toInput.addEventListener('change', () => {
      fetchTotalSales(fromInput?.value, toInput.value);
    });
  }
   const load = () => {
    const from = fromInput?.value;
    const to = toInput?.value;
    fetchTotalSales(from, to);
    fetchTotalOrders(from, to);
    fetchRevenueGrowth(from, to);
    fetchTopProduct(from, to);
    fetchTopCustomer(from, to);
  };

  // initial load
  load();

  // auto-update when dates change
  if (fromInput) fromInput.addEventListener('change', load);
  if (toInput) toInput.addEventListener('change', load);
});

</script>
