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
h2 {
  color: var(--primary);
  margin: 10px 0 25px;
  font-size: 26px;
  font-weight: 700;
}

/* Filters */
.report-filters {
  background: var(--white);
  padding: 15px 20px;
  border-radius: 12px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.08);
  margin-bottom: 25px;
}
.report-filters label {
  font-weight: 600;
  margin-right: 5px;
}
.report-filters select,
.report-filters input[type="date"],
.report-filters button {
  padding: 8px 12px;
  border: 1px solid #ccc;
  border-radius: 8px;
  font-size: 14px;
}
.report-filters button {
  background-color: var(--primary);
  color: var(--white);
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: background-color 0.2s ease;
}
.report-filters button:hover {
  background-color: var(--accent);
}

/* KPI cards */
.report-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-bottom: 30px;
}
.report-card {
  background: var(--white);
  border-radius: 12px;
  padding: 18px 20px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.08);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.report-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}
.report-card h4 {
  color: var(--text-dark);
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 6px;
}
.report-card p {
  font-size: 20px;
  color: var(--primary);
  font-weight: 700;
  margin: 0;
}

/* Charts grid */
.analytics-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
  gap: 24px;
  margin-bottom: 35px;
}
.chart-box {
  background: var(--white);
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.1);
}
.chart-box h4 {
  color: var(--primary);
  margin-bottom: 12px;
  font-size: 15px;
  font-weight: 600;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
canvas {
  width: 100% !important;
  height: 320px !important;
}

/* Export section */
.export-section {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 30px;
}
.export-section button {
  padding: 9px 16px;
  border: none;
  border-radius: 8px;
  background-color: var(--primary);
  color: var(--white);
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s ease;
}
.export-section button:hover {
  background-color: var(--accent);
}

.btn-toggle {
  background-color: var(--secondary);
  color: white;
  border: none;
  border-radius: 6px;
  padding: 6px 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-size: 13px;
}
.btn-toggle:hover {
  background-color: var(--accent);
}

@media (max-width: 600px) {
  .report-filters {
    flex-direction: column;
    align-items: stretch;
  }
  .report-filters > div {
    width: 100%;
  }
  h2 {
    font-size: 22px;
  }
}
</style>

<h2>Reports & Analytics</h2>

<!-- Filters -->
<div class="report-filters">
  <div>
    <label>Report Type:</label>
    <select id="reportType">
      <option value="daily">Daily</option>
      <option value="weekly" selected>Weekly</option>
      <option value="monthly">Monthly</option>
    </select>
  </div>
  <div>
    <label>Date Range:</label>
    <input type="date" id="fromDate"> -
    <input type="date" id="toDate">
  </div>
  <button>Generate</button>
</div>

<!-- KPI Summary -->
<div class="report-cards">
  <div class="report-card"><h4>Total Sales</h4><p id="totalSales">Loading…</p></div>
  <div class="report-card"><h4>Total Orders</h4><p id="totalOrders">Loading…</p></div>
  <div class="report-card"><h4>Average Order Value</h4><p id="avgOrderValue">Loading…</p></div>
  <div class="report-card"><h4>Revenue Growth</h4><p id="revenueGrowth">Loading…</p></div>
  <div class="report-card"><h4>Top Product</h4><p id="topProductName">Loading…</p><small id="topProductDetails" style="color:#666;font-size:13px;"></small></div>
  <div class="report-card"><h4>Top Customer</h4><p id="topCustomerName">Loading…</p><small id="topCustomerDetails" style="color:#666;font-size:13px;"></small></div>
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
    <h4>Customer Growth <button id="toggleChartType" class="btn-toggle">Switch to Line View</button></h4>
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
/* Chart Global Styles */
Chart.defaults.font.family = 'Poppins';
Chart.defaults.color = '#3e2f1c';
Chart.defaults.plugins.legend.labels.boxWidth = 15;
Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(0,0,0,0.7)';

function createGradient(ctx, color) {
  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, color + 'CC');
  gradient.addColorStop(1, color + '00');
  return gradient;
}
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

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
let currentChartType = 'bar'; // start as bar by default
let userGrowthChart = null;

async function fetchUserGrowth(fromDate, toDate) {
  const reportType = document.getElementById('reportType');
  const type = reportType ? reportType.value : 'weekly';

  const url = new URL('/Leilife/backend/admin/get_user_growth.php', window.location.origin);
  url.searchParams.set('fromDate', fromDate);
  url.searchParams.set('toDate', toDate);
  url.searchParams.set('type', type);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Invalid response');

    const sorted = (data.data || []).sort((a, b) => new Date(a.label) - new Date(b.label));
    const labels = sorted.map(r => String(r.label));
    const values = sorted.map(r => parseInt(r.count, 10) || 0);

    // No data fallback
    if (!labels.length) {
      if (userGrowthChart) {
        userGrowthChart.destroy();
        userGrowthChart = null;
      }
      const parent = document.getElementById('userGrowth').parentElement;
      let notice = parent.querySelector('.no-data-note');
      if (!notice) {
        notice = document.createElement('div');
        notice.className = 'no-data-note';
        notice.style.color = '#666';
        notice.style.padding = '12px';
        notice.textContent = 'No user registrations found for the selected range.';
        parent.appendChild(notice);
      } else {
        notice.style.display = '';
      }
      return;
    } else {
      const parent = document.getElementById('userGrowth').parentElement;
      const notice = parent.querySelector('.no-data-note');
      if (notice) notice.style.display = 'none';
    }

    const ctx = document.getElementById('userGrowth').getContext('2d');
    const datasetLabel =
      type === 'daily' ? 'New Users (daily)' :
      type === 'monthly' ? 'New Users (monthly)' :
      'New Users (weekly)';

    const chartConfig = {
      type: currentChartType,
      data: {
        labels: labels,
        datasets: [{
          label: datasetLabel,
          data: values,
          backgroundColor: '#d2b48c',
          borderColor: '#d2b48c',
          fill: currentChartType === 'bar',
          tension: 0.3,
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        scales: {
          x: { ticks: { autoSkip: true, maxRotation: 0, minRotation: 0 } },
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              callback: v => Number.isInteger(v) ? v : ''
            }
          }
        },
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: ctx => `${ctx.formattedValue} users`
            }
          }
        }
      }
    };

    if (userGrowthChart) userGrowthChart.destroy();
    userGrowthChart = new Chart(ctx, chartConfig);

  } catch (err) {
    console.error('fetchUserGrowth failed:', err);
  }
}

let salesTrendChart = null;

async function fetchSalesTrend(fromDate, toDate) {
  const reportType = document.getElementById('reportType')?.value || 'weekly';
  const canvas = document.getElementById('salesTrend');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const url = new URL('/Leilife/backend/admin/get_sales_trend.php', window.location.origin);
  url.searchParams.set('type', reportType);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();

    if (!data.success || !Array.isArray(data.data)) throw new Error(data.error || 'Invalid response');

    // Sort by label (date or period)
    const sorted = data.data.sort((a, b) => new Date(a.label) - new Date(b.label));
    const labels = sorted.map(r => r.label);
    const values = sorted.map(r => parseFloat(r.total_sales || 0));

    // No data fallback
    if (!labels.length) {
      const parent = canvas.parentElement;
      parent.innerHTML = '<p style="color:#666;padding:15px;">No sales data found for the selected range.</p>';
      return;
    }

    // Destroy old chart if exists
    if (salesTrendChart) salesTrendChart.destroy();

    salesTrendChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Sales (₱)',
          data: values,
          borderColor: '#8b6f47',
          backgroundColor: 'rgba(139,111,71,0.25)',
          fill: true,
          tension: 0.3,
          borderWidth: 2,
          pointRadius: 4,
          pointBackgroundColor: '#8b6f47'
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: v => `₱${v.toLocaleString()}`
            }
          },
          x: { ticks: { autoSkip: true } }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: ctx => `₱${Number(ctx.raw || 0).toLocaleString()}`
            }
          },
          legend: { display: false }
        }
      }
    });

  } catch (err) {
    console.error('fetchSalesTrend failed:', err);
  }
}

async function fetchRevenueBreakdown(fromDate, toDate) {
  const canvas = document.getElementById('revenueBreakdown');
  if (!canvas) {
    console.warn('Revenue Breakdown canvas not found.');
    return;
  }

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    console.warn('Revenue Breakdown context not available.');
    return;
  }

  const url = new URL('/Leilife/backend/admin/get_revenue_breakdown.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate)   url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Invalid response');

    const categories = Object.keys(data.data);
    const totals = categories.map(cat => data.data[cat].revenue[0] || 0);

    if (!categories.length) {
      const parent = canvas.parentElement;
      parent.innerHTML = '<p style="color:#666;padding:15px;">No revenue data found for the selected range.</p>';
      return;
    }

    // Destroy old chart if any
    if (window.revenueChart) window.revenueChart.destroy();

    window.revenueChart = new Chart(ctx, {
      type: 'pie',
      data: {
        labels: categories,
        datasets: [{
          data: totals,
          backgroundColor: ['#a67c52','#d2b48c','#8b6f47','#c2a47c','#b58c65']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Revenue by Category',
            color: '#3e2f1c',
            font: { size: 14 }
          },
          legend: { position: 'bottom' },
          tooltip: {
            callbacks: {
              label: ctx => {
                const val = ctx.raw || 0;
                return `${ctx.label}: ₱${val.toLocaleString()}`;
              }
            }
          }
        }
      }
    });

  } catch (err) {
    console.error('fetchRevenueBreakdown failed:', err);
  }
}



document.addEventListener('DOMContentLoaded', () => {
  const fromInput = document.getElementById('fromDate');
  const toInput = document.getElementById('toDate');
  const reportType = document.getElementById('reportType');
  const generateBtn = document.querySelector('.report-filters button'); // your Generate button

  if (fromInput && !fromInput.value)
    fromInput.value = new Date().toISOString().slice(0, 10);
  if (toInput && !toInput.value)
    toInput.value = new Date().toISOString().slice(0, 10);

 const load = () => {
  const from = fromInput?.value;
  const to = toInput?.value;
  fetchTotalSales(from, to);
  fetchTotalOrders(from, to);
  fetchRevenueGrowth(from, to);
  fetchTopProduct(from, to);
  fetchTopCustomer(from, to);
  fetchUserGrowth(from, to);
  fetchRevenueBreakdown(from, to);
  fetchSalesTrend(from, to); // ✅ added
};


  // Initial load
  load();

  // Update when inputs or report type change
  if (fromInput) fromInput.addEventListener('change', load);
  if (toInput) toInput.addEventListener('change', load);
  if (reportType) reportType.addEventListener('change', load);
  if (generateBtn) generateBtn.addEventListener('click', load);
});
const toggleBtn = document.getElementById('toggleChartType');
  if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
      currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
      toggleBtn.textContent = currentChartType === 'bar'
        ? 'Switch to Line View'
        : 'Switch to Bar View';

      const fromDate = document.getElementById('fromDate')?.value || '';
      const toDate = document.getElementById('toDate')?.value || '';
      fetchUserGrowth(fromDate, toDate);
    });
  }

</script>
