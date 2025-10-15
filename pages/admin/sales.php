<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);
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

<!-- Pagination -->
<div class="pagination">
  <button id="prevPage" disabled>Previous</button>
  <span id="pageInfo">Page 1</span>
  <button id="nextPage">Next</button>
</div>

<div class="export-buttons">
  <button onclick="alert('Exported to CSV')">Export CSV</button>
  <button onclick="alert('Exported to Excel')">Export Excel</button>
  <button onclick="exportPDF()">Export PDF</button>
  <button onclick="exportFile('excel')">Export Excel</button>
  <button onclick="exportFile('pdf')">Export PDF</button>

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
function exportPDF() {

function exportFile(type) {
  const fromDate = document.getElementById('fromDate').value;
  const toDate = document.getElementById('toDate').value;
  const status = document.getElementById('statusFilter').value;
  const payment = document.getElementById('paymentFilter').value;

  let url = '';
  if (type === 'pdf') url = '/leilife/pages/admin/sales-report-pdf.php';
  else if (type === 'excel') url = '/leilife/pages/admin/sales-report-excel.php';
  else if (type === 'csv') url = '/leilife/pages/admin/sales-report-csv.php';

  const params = [];
  if (type === 'excel' || type === 'csv') params.push('download=1');
  if (fromDate) params.push(`fromDate=${encodeURIComponent(fromDate)}`);
  if (toDate) params.push(`toDate=${encodeURIComponent(toDate)}`);
  if (status && status !== 'All') params.push(`status=${encodeURIComponent(status)}`);
  if (payment && payment !== 'All') params.push(`payment=${encodeURIComponent(payment)}`);

  if (params.length > 0) url += '?' + params.join('&');
  if (status && status.toLowerCase() !== 'all') params.push(`status=${encodeURIComponent(status)}`);
  if (payment && payment.toLowerCase() !== 'all') params.push(`payment=${encodeURIComponent(payment)}`);

  if (params.length) url += '?' + params.join('&');
  window.open(url, '_blank');
}

const orders = <?= json_encode($orders) ?>;

const tbody = document.getElementById("ordersTableBody");
const pageInfo = document.getElementById("pageInfo");
const prevBtn = document.getElementById("prevPage");
const nextBtn = document.getElementById("nextPage");

let currentPage = 1;
const rowsPerPage = 10;
let filteredOrders = [...orders];

function renderTable(page = 1) {
  // Fully clear tbody
  while (tbody.firstChild) tbody.removeChild(tbody.firstChild);

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

  const start = (page - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  const paginated = filteredOrders.slice(start, end);

  paginated.forEach(order => {
    const row = tbody.insertRow(); // create proper <tr>

    const driver = order.driver_name || 'Undefined';
    const payment = order.payment_method || 'Undefined';
    const date = order.date ? order.date.slice(0, 10) : 'Undefined';
    const total = parseFloat(order.total || 0).toFixed(2);
    const customer = order.customer_name || 'Undefined';
    const orderNumber = order.order_number || 'Undefined';
    const status = order.status || 'Undefined';

    const cells = [
      `#${orderNumber}`,
      customer,
      driver,
      `₱${total}`,
      status,
      payment,
      date,
      `<button class="view-btn" onclick="viewDetails('${orderNumber}')">View</button>`
    ];

    cells.forEach((content, i) => {
      const cell = row.insertCell(i);
      cell.innerHTML = content;
      if (i === 7) cell.classList.add('actions');
    });
  });

  pageInfo.textContent = `Page ${currentPage} of ${Math.ceil(filteredOrders.length / rowsPerPage)}`;
  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === Math.ceil(filteredOrders.length / rowsPerPage);
}

prevBtn.addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--;
    renderTable(currentPage);
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

  filteredOrders = orders.filter(o => {
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

  currentPage = 1;
  renderTable(currentPage);
}

renderTable();
</script>
