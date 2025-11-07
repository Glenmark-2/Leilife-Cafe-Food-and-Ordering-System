    <?php
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
    if (!isset($_SESSION['admin_id'])) {
      header('Location: /Leilife/public/index.php');
      exit;
    }

    require_once __DIR__ . '/../../backend/db_script/db.php';
    require_once __DIR__ . '/../../backend/db_script/appData.php';

    $appData = new AppData($pdo);

    $currentAdmin =  $appData->getCurrentAdmin();
    $isMainAdmin = $currentAdmin['isMainAdmin'];

    if (!$isMainAdmin) {
      header('Location: /leilife/public/index.php');
      exit;
    }




    ?>

 

    <body>
      <div class="surface">
        <div id="first-row"> 
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
          <span></span>
            <span></span>
          <span></span>
            </button>
           <h2>Reports & Analytics</h2>
                  </div>
          <div class="report-filters">
            <div>
              <label for="reportType">Report Type:</label>
              <select id="reportType">
                <option value="daily">Daily</option>
                <option value="weekly" selected>Weekly</option>
                <option value="monthly">Monthly</option>
              </select>
            </div>

            <div>
              <label for="fromDate">Date Range:</label>
              <input type="date" id="fromDate"> -
              <input type="date" id="toDate">
            </div>

            <div class="spacer"></div>

            <div style="display:flex;gap:10px;align-items:center">
              <button id="generateBtn">Generate</button>
            </div>
          </div>

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
              <small id="topProductDetails"></small>
            </div>
            <div class="report-card">
              <h4>Top Customer</h4>
              <p id="topCustomerName">Loading…</p>
              <small id="topCustomerDetails"></small>
            </div>
          </div>

          <!-- Unified Chart Section -->
          <div class="analytics-wrapper">
            <div class="chart-table">
              <div class="chart-selector">
                <button data-chart="salesTrend" class="active">Sales Trend</button>
                <button data-chart="revenueBreakdown">Revenue by Category</button>
                <button data-chart="userGrowth">Customer Growth</button>
              </div>

              <div class="chart-container">
                <canvas id="salesTrend"></canvas>
                <canvas id="revenueBreakdown"></canvas>
                <canvas id="userGrowth"></canvas>
              </div>
            </div>

            <!-- Sentiment Summary stays on the side -->
            <div class="sentiment-side">
              <h4>Customer Sentiment Summary</h4>
              <canvas id="sentimentChart"></canvas>
              <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
                <p id="pendingNotice" style="color:#777;font-size:14px;margin:0;"></p>
                <p id="sentimentError" style="color:#c00;font-size:13px;margin:0;display:none;"></p>
              </div>
            </div>
          </div>

          <div class="export-section">
            <button id="export-pdf">Export PDF</button>
            <button id="export-excel">Export Excel</button>
            <button id="export-csv">Export CSV</button>
          </div>

        </div>
      </div>
      <script>
        document.addEventListener("DOMContentLoaded", () => {
          const chartContainer = document.querySelector('.chart-container');
          const chartButtons = document.querySelectorAll('.chart-selector button');

          // Make sure a canvas with the given id exists inside .chart-container.
          function ensureCanvasExists(id) {
            let c = document.getElementById(id);
            if (c) return c;
            // Create and append a canvas with same id (preserves layout)
            c = document.createElement('canvas');
            c.id = id;
            // match styling rules (display will be controlled by showChart)
            c.style.width = '100%';
            c.style.height = '340px';
            chartContainer.appendChild(c);
            return c;
          }

          async function showChart(target) {
            const canvases = chartContainer.querySelectorAll('canvas');

            // Find the currently visible chart
            const currentCanvas = chartContainer.querySelector('canvas.active');
            const newCanvas = document.getElementById(target) || ensureCanvasExists(target);

            // If already showing the requested chart, skip
            if (currentCanvas === newCanvas) return;

            // Prepare transition
            canvases.forEach(cv => cv.classList.remove('active'));

            if (currentCanvas) {
              // Fade out the current chart smoothly
              currentCanvas.style.opacity = '0';
              currentCanvas.style.visibility = 'hidden';
            }

            // Wait a short delay before switching
            setTimeout(async () => {
              // Fade in new chart
              newCanvas.classList.add('active');
              newCanvas.style.opacity = '1';
              newCanvas.style.visibility = 'visible';

              // Update active button styling
              chartButtons.forEach(btn => btn.classList.remove('active'));
              const activeBtn = document.querySelector(`.chart-selector button[data-chart="${target}"]`);
              if (activeBtn) activeBtn.classList.add('active');

              const fromDate = document.getElementById('fromDate')?.value || '';
              const toDate = document.getElementById('toDate')?.value || '';

              try {
                if (target === 'salesTrend') {
                  await fetchSalesTrend(fromDate, toDate);
                } else if (target === 'revenueBreakdown') {
                  await fetchRevenueBreakdown(fromDate, toDate);
                } else if (target === 'userGrowth') {
                  await fetchUserGrowth(fromDate, toDate);
                }
              } catch (e) {
                console.error('Error while showing chart', target, e);
              }
            }, 200); // Small delay for smooth fade transition
          }

          // Attach listeners to selector buttons
          chartButtons.forEach(btn => {
            btn.addEventListener('click', (ev) => {
              const target = btn.getAttribute('data-chart');
              if (!target) return;
              showChart(target);
            });
          });

          // Pick default to show:
          // 1) If one of the buttons already has class "active", use it
          // 2) Else use the first button
          let defaultTarget = null;
          const preActive = document.querySelector('.chart-selector button.active');
          if (preActive) defaultTarget = preActive.dataset.chart;
          if (!defaultTarget && chartButtons.length) defaultTarget = chartButtons[0].dataset.chart;

          if (defaultTarget) {
            // show default chart (no await needed)
            showChart(defaultTarget);
          }
        });
      </script>











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
              const res = await fetch(sentimentEndpoint, {
                cache: 'no-store'
              });
              const data = await res.json();
              const stats = {
                POSITIVE: Number(data.POSITIVE ?? data.positive ?? 0),
                NEGATIVE: Number(data.NEGATIVE ?? data.negative ?? 0),
                NEUTRAL: Number(data.NEUTRAL ?? data.neutral ?? 0),
                PENDING: Number(data.PENDING ?? data.pending ?? 0)
              };
              const labels = ['Positive', 'Negative', 'Neutral', 'Pending'];
              const values = [stats.POSITIVE, stats.NEGATIVE, stats.NEUTRAL, stats.PENDING];
              const colors = ['#4caf50', '#f44336', '#2196f3', '#9e9e9e'];
              if (!sentimentChart) {
                sentimentChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                    labels,
                    datasets: [{
                      data: values,
                      backgroundColor: colors
                    }]
                  },
                  options: {
                    responsive: true,
                    scales: {
                      y: {
                        beginAtZero: true
                      }
                    }
                  }
                });
              } else {
                sentimentChart.data.datasets[0].data = values;
                sentimentChart.update();
              }
              pendingEl.textContent = stats.PENDING > 0 ? `⚠️ ${stats.PENDING} pending` : '';
              errEl.style.display = 'none';
            } catch (e) {
              console.error(e);
              errEl.style.display = 'block';
              errEl.textContent = 'Could not load sentiment data';
            }
          }
          fetchSentiment();
          setInterval(fetchSentiment, 10000);
        }
        const formatPHP = (num) => {
          return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
            maximumFractionDigits: 2
          }).format(num);
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
          if (toDate) url.searchParams.set('toDate', toDate);

          try {
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
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
          if (toDate) url.searchParams.set('toDate', toDate);

          try {
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
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
          if (toDate) url.searchParams.set('toDate', toDate);

          try {
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
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
          if (toDate) url.searchParams.set('toDate', toDate);

          try {
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
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
            detailsEl.textContent = data.total_revenue > 0 ?
              `${formatPHP(data.total_revenue)} • ${data.total_quantity} sold` :
              'No sales data';

          } catch (err) {
            console.error('fetchTopProduct failed:', err);
            document.getElementById('topProductName').textContent = '—';
            document.getElementById('topProductDetails').textContent = '';
          }
        }

        async function fetchTopCustomer(fromDate, toDate) {
          const url = new URL('/Leilife/backend/admin/get_top_customer.php', window.location.origin);
          if (fromDate) url.searchParams.set('fromDate', fromDate);
          if (toDate) url.searchParams.set('toDate', toDate);

          try {
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
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
            detailsEl.textContent = data.total_spent > 0 ?
              `${formatPHP(data.total_spent)} • ${data.total_orders} orders` :
              'No data found';

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
            const res = await fetch(url.toString(), {
              cache: 'no-store'
            });
            if (!res.ok) throw new Error('Network error ' + res.status);
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Invalid response');

            const sorted = (data.data || []).sort((a, b) => new Date(a.label) - new Date(b.label));
            const labels = sorted.map(r => String(r.label));
            const values = sorted.map(r => parseInt(r.count, 10) || 0);

            if (!labels.length) {
              if (userGrowthChart) {
                userGrowthChart.destroy();
                userGrowthChart = null;
              }

              let notice = canvas.nextElementSibling;
              if (!notice || !notice.classList.contains('no-data-note')) {
                notice = document.createElement('div');
                notice.className = 'no-data-note';
                notice.style.color = '#666';
                notice.style.padding = '12px';
                notice.textContent = 'No user registrations found for the selected range.';
                canvas.insertAdjacentElement('afterend', notice);
              }
              notice.style.display = 'block';
              return;
            } else {
              const notice = canvas.nextElementSibling;
              if (notice && notice.classList.contains('no-data-note')) notice.style.display = 'none';
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
                  x: {
                    ticks: {
                      autoSkip: true,
                      maxRotation: 0,
                      minRotation: 0
                    }
                  },
                  y: {
                    beginAtZero: true,
                    ticks: {
                      stepSize: 1,
                      callback: v => Number.isInteger(v) ? v : ''
                    }
                  }
                },
                plugins: {
                  legend: {
                    display: false
                  },
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
  if (!canvas) {
    console.warn('Sales Trend canvas not found.');
    return;
  }

  const ctx = canvas.getContext('2d');
  if (!ctx) {
    console.warn('Sales Trend context not available.');
    return;
  }

  // Build URL with parameters
  const url = new URL('/Leilife/backend/admin/get_sales_trend.php', window.location.origin);
  url.searchParams.set('type', reportType);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate) url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error(`Network error (${res.status})`);

    const data = await res.json();
    if (!data.success || !Array.isArray(data.data)) {
      throw new Error(data.error || 'Invalid data format');
    }

    // Sort and map the data
    const sorted = data.data.sort((a, b) => new Date(a.label) - new Date(b.label));
    const labels = sorted.map(r => r.label);
    const values = sorted.map(r => parseFloat(r.total_sales || 0));

    // Destroy any previous chart before redrawing
    if (salesTrendChart) {
      salesTrendChart.destroy();
      salesTrendChart = null;
    }

    // Handle "no data" case
    if (!labels.length || values.every(v => v === 0)) {
      const parent = canvas.parentElement;
      if (parent) {
        parent.querySelectorAll('.no-data-note').forEach(el => el.remove());
        const msg = document.createElement('div');
        msg.textContent = 'No sales data found for the selected range.';
        msg.className = 'no-data-note';
        msg.style.textAlign = 'center';
        msg.style.color = '#666';
        msg.style.padding = '20px';
        msg.style.fontSize = '14px';
        parent.appendChild(msg);
      }
      return;
    }

    // Remove "no data" message if previously shown
    const parent = canvas.parentElement;
    parent?.querySelectorAll('.no-data-note').forEach(el => el.remove());

    // Render chart normally
    salesTrendChart = new Chart(ctx, {
  type: 'line',
  data: { /* ... */ },
  options: {
    responsive: true,
    maintainAspectRatio: true, // ✅ Keep proportion (no stretch)
    aspectRatio: 2,            // ✅ Adjust height-to-width ratio (1.5–2 looks good)
    scales: {
      x: {
        ticks: {
          color: '#3e2f1c',
          autoSkip: true,
          maxRotation: 0,
          minRotation: 0
        },
        grid: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: {
          color: '#3e2f1c',
          callback: v => `₱${v.toLocaleString()}`
        },
        grid: { color: 'rgba(0,0,0,0.05)' }
      }
    },
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => `₱${Number(ctx.raw || 0).toLocaleString()}`
        }
      },
      title: {
        display: true,
        text: `Sales Trend (${fromDate || 'Start'} → ${toDate || 'Today'})`,
        color: '#3e2f1c',
        font: { size: 14, weight: 'bold' },
        padding: { top: 10, bottom: 10 }
      }
    },
    animation: { duration: 400 }
  }
});


  } catch (err) {
    console.error('fetchSalesTrend failed:', err);
    const parent = canvas.parentElement;
    if (parent) {
      parent.querySelectorAll('.no-data-note').forEach(el => el.remove());
      const msg = document.createElement('div');
      msg.textContent = 'Error fetching sales data. Please try again later.';
      msg.className = 'no-data-note';
      msg.style.textAlign = 'center';
      msg.style.color = '#b00';
      msg.style.padding = '20px';
      parent.appendChild(msg);
    }
  }
}


async function fetchRevenueBreakdown(fromDate, toDate) {
  let canvas = document.getElementById('revenueBreakdown');
  if (!canvas) {
    console.warn('Revenue Breakdown canvas not found, creating new one.');
    const chartContainer = document.querySelector('.chart-container');
    canvas = document.createElement('canvas');
    canvas.id = 'revenueBreakdown';
    chartContainer?.appendChild(canvas);
  }

  const ctx = canvas?.getContext('2d');
  if (!ctx) {
    console.warn('Revenue Breakdown context not available.');
    return;
  }

  const url = new URL('/Leilife/backend/admin/get_revenue_breakdown.php', window.location.origin);
  if (fromDate) url.searchParams.set('fromDate', fromDate);
  if (toDate) url.searchParams.set('toDate', toDate);

  try {
    const res = await fetch(url.toString(), { cache: 'no-store' });
    if (!res.ok) throw new Error('Network error ' + res.status);
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Invalid response');

    const categories = Object.keys(data.data);
    const totals = categories.map(cat => data.data[cat].revenue[0] || 0);

    // ✅ Clear old notice
    const note = canvas.nextElementSibling;
    if (note && note.classList.contains('no-data-note')) note.remove();

    if (!categories.length) {
      let msg = document.createElement('div');
      msg.textContent = 'No revenue data found for the selected range.';
      msg.className = 'no-data-note';
      msg.style.color = '#666';
      msg.style.padding = '15px';
      canvas.insertAdjacentElement('afterend', msg);
      return;
    }

    if (window.revenueChart) window.revenueChart.destroy();

    window.revenueChart = new Chart(ctx, {
  type: 'pie',
  data: {
    labels: categories,
    datasets: [{
      data: totals,
      backgroundColor: ['#a67c52', '#d2b48c', '#8b6f47', '#c2a47c', '#b58c65']
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: true, // ✅ keeps circle circular
    aspectRatio: 1.8,          // ✅ prevents tall or squashed pie
    plugins: {
      title: {
        display: true,
        text: 'Revenue by Category',
        color: '#3e2f1c',
        font: { size: 14, weight: 'bold' },
        padding: { top: 10, bottom: 10 }
      },
      legend: {
        position: 'bottom',
        labels: {
          boxWidth: 15,
          color: '#3e2f1c'
        }
      },
      tooltip: {
        callbacks: {
          label: ctx => `${ctx.label}: ₱${(ctx.raw || 0).toLocaleString()}`
        }
      }
    }
  }
});

  } catch (err) {
    console.error('fetchRevenueBreakdown failed:', err);
  }
}

window.addEventListener('resize', () => {
  if (salesTrendChart) salesTrendChart.resize();
});

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
            toggleBtn.textContent = currentChartType === 'bar' ?
              'Switch to Line View' :
              'Switch to Bar View';

            const fromDate = document.getElementById('fromDate')?.value || '';
            const toDate = document.getElementById('toDate')?.value || '';
            fetchUserGrowth(fromDate, toDate);
          });
        }
      </script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>

      <script>
        document.addEventListener('DOMContentLoaded', function() {

          // Capture KPI and chart data
          function collectDashboardData() {
            const kpis = Array.from(document.querySelectorAll('.kpi-card')).map(card => ({
              title: card.querySelector('.kpi-title')?.textContent.trim(),
              value: card.querySelector('.kpi-value')?.textContent.trim()
            }));

            const activeChart = document.querySelector('.chart-container canvas');
            return {
              kpis,
              activeChart
            };
          }


        });


        document.addEventListener("DOMContentLoaded", () => {
          const exportButtons = {
            pdf: document.getElementById("export-pdf"),
            excel: document.getElementById("export-excel"),
            csv: document.getElementById("export-csv")
          };

          function downloadReport(type) {
            const reportType = document.getElementById("reportType")?.value || "weekly";
            const fromDate = document.getElementById("fromDate")?.value || "";
            const toDate = document.getElementById("toDate")?.value || "";

            // ✅ Update this path to wherever your report files actually are
            const basePath = "/Leilife/pages/admin/";

            const endpoints = {
              pdf: basePath + "reports-pdf.php",
              excel: basePath + "report-excel.php",
              csv: basePath + "report-csv.php"
            };

            const url = new URL(endpoints[type], window.location.origin);
            url.searchParams.set("reportType", reportType);
            if (fromDate) url.searchParams.set("fromDate", fromDate);
            if (toDate) url.searchParams.set("toDate", toDate);

            // Trigger download
            const link = document.createElement("a");
            link.href = url.toString();
            link.download = `report_${reportType}_${type}.${type === "pdf" ? "pdf" : type}`;
            document.body.appendChild(link);
            link.click();
            link.remove();
          }

          exportButtons.pdf?.addEventListener("click", () => downloadReport("pdf"));
          exportButtons.excel?.addEventListener("click", () => downloadReport("excel"));
          exportButtons.csv?.addEventListener("click", () => downloadReport("csv"));
        });

      </script>