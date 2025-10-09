<?php
session_start();

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

$appData = new AppData($pdo);

// Optional welcome message
$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}

// Fetch orders from database with default filters (All)
$orders = $appData->getOrdersByFilters(null, 'All', 'All', null, null);
?>

<h2>Sales Management</h2>

<!-- Filters -->
<div class="filters">
  <div class="filter-group">
    <label>Status:</label>
    <select id="statusFilter">
      <option value="All">All</option>
      <option value="Pending">Pending</option>
      <option value="Delivered">Delivered</option>
      <option value="Cancelled">Cancelled</option>
    </select>
  </div>

  <div class="filter-group">
    <label>Date Range:</label>
    <input type="date" id="fromDate"> - <input type="date" id="toDate">
  </div>

  <div class="filter-group">
    <label>Driver:</label>
    <select id="driverFilter">
      <option value="All">All</option>
      <?php
      // Get unique drivers from orders
      $drivers = array_unique(array_map(fn($o) => $o['driver_name'] ?? 'Undefined', $orders));
      foreach ($drivers as $d) {
          $val = htmlspecialchars($d ?: 'Undefined');
          echo "<option value=\"$val\">$val</option>";
      }
      ?>
    </select>
  </div>

  <div class="filter-group">
    <label>Payment:</label>
    <select id="paymentFilter">
      <option value="All">All</option>
      <?php
      // Get unique payment methods
      $payments = array_unique(array_map(fn($o) => $o['payment_method'] ?? 'Undefined', $orders));
      foreach ($payments as $p) {
          $val = htmlspecialchars($p ?: 'Undefined');
          echo "<option value=\"$val\">$val</option>";
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

<div class="export-buttons">
  <button onclick="alert('Exported to CSV')">Export CSV</button>
  <button onclick="alert('Exported to Excel')">Export Excel</button>
  <button onclick="alert('Exported to PDF')">Export PDF</button>
</div>

<!-- Modal -->
<div class="modal" id="detailsModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h3>Order Details</h3>
    <div id="orderDetails"></div>
  </div>
</div>

<script>
  // Pass PHP orders to JS
  const orders = <?= json_encode($orders) ?>;

  const tbody = document.getElementById("ordersTableBody");

  function renderTable(filtered = orders) {
    tbody.innerHTML = "";
    filtered.forEach(order => {
      const driver = order.driver_name || 'Undefined';
      const payment = order.payment_method || 'Undefined';
      const date = order.date ? order.date.slice(0,10) : 'Undefined';
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
        <td class="actions">
          <button class="view-btn" onclick="viewDetails('${orderNumber}')">View</button>
        </td>`;
      tbody.appendChild(row);
    });
  }

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

  function closeModal() {
    document.getElementById("detailsModal").style.display = "none";
  }

  // Filters
  document.querySelectorAll('#statusFilter, #driverFilter, #paymentFilter, #fromDate, #toDate')
    .forEach(el => el.addEventListener('change', filterOrders));

  function filterOrders() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const driver = document.getElementById('driverFilter').value.toLowerCase();
    const payment = document.getElementById('paymentFilter').value.toLowerCase();
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;

    const filtered = orders.filter(o => {
      const orderDate = o.date ? o.date.slice(0,10) : '';
      const orderStatus = (o.status || 'Undefined').toLowerCase();
      const orderDriver = (o.driver_name || 'Undefined').toLowerCase();
      const orderPayment = (o.payment_method || 'Undefined').toLowerCase();

      const matchStatus = (status === "all" || orderStatus === status);
      const matchDriver = (driver === "all" || orderDriver === driver);
      const matchPayment = (payment === "all" || orderPayment === payment);
      const matchDate = (!from || orderDate >= from) && (!to || orderDate <= to);

      return matchStatus && matchDriver && matchPayment && matchDate;
    });

    renderTable(filtered);
  }

  renderTable();
</script>
