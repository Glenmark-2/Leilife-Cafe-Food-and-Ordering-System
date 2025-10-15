<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

// Redirect if admin not logged in
if (!isset($_SESSION['admin_id'])) {
  header('Location: /leilife/public/index.php?page=home');
  exit;
}

// Generate a new download token for this page load
if (empty($_SESSION['download_token'])) {
  $_SESSION['download_token'] = bin2hex(random_bytes(16));
}
$downloadToken = $_SESSION['download_token'];

$appData = new AppData($pdo);

// Optional welcome message
$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
  $showWelcome = true;
  unset($_SESSION['show_welcome']);
}

// Get filters from GET params (so they persist after reload)
$status = $_GET['status'] ?? 'All';
$driver = $_GET['driver'] ?? 'All';
$payment = $_GET['payment'] ?? 'All';
$fromDate = $_GET['fromDate'] ?? '';
$toDate = $_GET['toDate'] ?? '';

// Fetch orders based on filters
$orders = $appData->getOrdersByFilters(null, $status, $payment, $fromDate ?: null, $toDate ?: null);
?>

<h2>Sales Management</h2>

<!-- Filters -->
<div class="filters">
  <div class="filter-group">
    <label>Status:</label>
    <select id="statusFilter">
      <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All</option>
      <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
      <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
      <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
    </select>
  </div>

  <div class="filter-group">
    <label>Date Range:</label>
    <input type="date" id="fromDate" value="<?= htmlspecialchars($fromDate) ?>"> -
    <input type="date" id="toDate" value="<?= htmlspecialchars($toDate) ?>">
  </div>

  <div class="filter-group">
    <label>Driver:</label>
    <select id="driverFilter">
      <option value="All" <?= $driver === 'All' ? 'selected' : '' ?>>All</option>
      <?php
      $drivers = array_unique(array_map(fn($o) => $o['driver_name'] ?? 'Undefined', $orders));
      foreach ($drivers as $d) {
        $val = htmlspecialchars($d ?: 'Undefined');
        $selected = ($val === $driver) ? 'selected' : '';
        echo "<option value=\"$val\" $selected>$val</option>";
      }
      ?>
    </select>
  </div>

  <div class="filter-group">
    <label>Payment:</label>
    <select id="paymentFilter">
      <option value="All" <?= $payment === 'All' ? 'selected' : '' ?>>All</option>
      <?php
      // Fetch all distinct payment methods directly from database
      $stmt = $pdo->query("SELECT DISTINCT payment_method FROM orders WHERE payment_method IS NOT NULL AND payment_method <> ''");
      $allPayments = $stmt->fetchAll(PDO::FETCH_COLUMN);

      foreach ($allPayments as $p) {
        $val = htmlspecialchars($p);
        $selected = ($val === $payment) ? 'selected' : '';
        echo "<option value=\"$val\" $selected>$val</option>";
      }
      ?>

    </select>
  </div>
</div>

<!-- Orders Table -->
<table>
  <thead>
    <tr>
      <th>Order ID</th>
      <th>Customer</th>
      <th>Driver</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="ordersTableBody"></tbody>
</table>

<!-- Pagination -->
<div class="pagination">
  <button id="prevPage" disabled>Previous</button>
  <span id="pageInfo">Page 1</span>
  <button id="nextPage">Next</button>
</div>

<div class="export-buttons">
  <button onclick="exportFile('csv')">Export CSV</button>
  <button onclick="exportFile('excel')">Export Excel</button>
  <button onclick="exportFile('pdf')">Export PDF</button>
</div>

<!-- Modal -->
<div class="modal" id="detailsModal" style="display:none;">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h3>Order Details</h3>
    <div id="orderDetails"></div>
  </div>
</div>

<script>
  const downloadToken = '<?= $downloadToken ?>'; // single-use token for export
  const orders = <?= json_encode($orders) ?>;

  function exportFile(type) {
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const status = document.getElementById('statusFilter').value;
    const payment = document.getElementById('paymentFilter').value;

    let page = '';
    if (type === 'pdf') page = 'sales-report-pdf';
    else if (type === 'excel') page = 'sales-report-excel';
    else if (type === 'csv') page = 'sales-report-csv';
    else return alert('Invalid type');

    const params = [];
    params.push('page=' + encodeURIComponent(page));
    params.push('download=1');
    params.push('token=' + encodeURIComponent(downloadToken));

    if (fromDate) params.push(`fromDate=${encodeURIComponent(fromDate)}`);
    if (toDate) params.push(`toDate=${encodeURIComponent(toDate)}`);
    if (status && status.toLowerCase() !== 'all') params.push(`status=${encodeURIComponent(status)}`);
    if (payment && payment.toLowerCase() !== 'all') params.push(`payment=${encodeURIComponent(payment)}`);

    const url = '/leilife/public/admin.php?' + params.join('&');
    console.log("Export URL:", url);
    window.open(url, '_blank');
  }

  // TABLE RENDER
  const tbody = document.getElementById("ordersTableBody");

  function renderTable(filtered = orders) {
    tbody.innerHTML = "";
    filtered.forEach(order => {
      const driver = order.driver_name || 'Undefined';
      const payment = order.payment_method || 'Undefined';
      const date = order.date ? order.date.slice(0, 10) : 'Undefined';
      const total = parseFloat(order.total || 0).toFixed(2);
      const customer = order.customer_name || 'Undefined';
      const orderNumber = order.order_number || 'Undefined';

      const row = document.createElement("tr");
      row.innerHTML = `
            <td>#${orderNumber}</td>
            <td>${customer}</td>
            <td>${driver}</td>
            <td>₱${total}</td>
            <td>${order.status || 'Undefined'}</td>
            <td>${payment}</td>
            <td>${date}</td>
            <td class="actions"><button class="view-btn" onclick="viewDetails('${orderNumber}')">View</button></td>
        `;
      tbody.appendChild(row);
    });
  }

  renderTable();

  // VIEW DETAILS MODAL
  function viewDetails(orderNumber) {
    const order = orders.find(o => o.order_number === orderNumber);
    if (!order) return;
    const modal = document.getElementById("detailsModal");
    const details = document.getElementById("orderDetails");

    const driver = order.driver_name || 'Undefined';
    const payment = order.payment_method || 'Undefined';
    const date = order.date ? order.date.slice(0, 10) : 'Undefined';
    const customer = order.customer_name || 'Undefined';
    const items = Array.isArray(order.items) ? order.items : [];

    details.innerHTML = `
      <p><strong>Customer:</strong> ${customer}</p>
      <p><strong>Driver:</strong> ${driver}</p>
      <p><strong>Total:</strong> ₱${parseFloat(order.total || 0).toFixed(2)}</p>
      <p><strong>Status:</strong> ${order.status || 'Undefined'}</p>
      <p><strong>Payment:</strong> ${payment}</p>
      <p><strong>Date:</strong> ${date}</p>
      <p><strong>Items:</strong></p>
      <ul>${items.map(i => `<li>${i.product_name || 'Undefined'} × ${i.quantity || 1}</li>`).join('')}</ul>
    `;
    modal.style.display = "flex";
  }
});

nextBtn.addEventListener("click", () => {
  if (currentPage < Math.ceil(filteredOrders.length / rowsPerPage)) {
    currentPage++;
    renderTable(currentPage);
  }
});

function viewDetails(orderNumber) {
  const order = orders.find(o => o.order_number === orderNumber);
  if (!order) return;
  const modal = document.getElementById("detailsModal");
  const details = document.getElementById("orderDetails");

  const driver = order.driver_name || 'Undefined';
  const payment = order.payment_method || 'Undefined';
  const date = order.date ? order.date.slice(0,10) : 'Undefined';
  const customer = order.customer_name || 'Undefined';
  const items = Array.isArray(order.items) ? order.items : [];

  details.innerHTML = `
    <p><strong>Customer:</strong> ${customer}</p>
    <p><strong>Driver:</strong> ${driver}</p>
    <p><strong>Total:</strong> ₱${parseFloat(order.total || 0).toFixed(2)}</p>
    <p><strong>Status:</strong> ${order.status || 'Undefined'}</p>
    <p><strong>Payment:</strong> ${payment}</p>
    <p><strong>Date:</strong> ${date}</p>
    <p><strong>Items:</strong></p>
    <ul>${items.map(i => `<li>${i.product_name || 'Undefined'} × ${i.quantity || 1}</li>`).join('')}</ul>
  `;
  modal.style.display = "flex";
}

  // FILTER CHANGE HANDLING — RELOAD WITH GET PARAMS
  document.querySelectorAll('#statusFilter, #driverFilter, #paymentFilter, #fromDate, #toDate')
    .forEach(el => el.addEventListener('change', () => {
      const params = new URLSearchParams(window.location.search);
      params.set('status', document.getElementById('statusFilter').value);
      params.set('driver', document.getElementById('driverFilter').value);
      params.set('payment', document.getElementById('paymentFilter').value);
      params.set('fromDate', document.getElementById('fromDate').value);
      params.set('toDate', document.getElementById('toDate').value);
      window.location.search = params.toString(); // refresh page with filters
    }));
</script>
