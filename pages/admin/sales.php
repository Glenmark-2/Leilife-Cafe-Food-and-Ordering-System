<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: /Leilife/public/index.php?page=home');
  exit;
}

$appData = new AppData($pdo);

$currentAdmin =  $appData->getCurrentAdmin();
$isMainAdmin = $currentAdmin['isMainAdmin'];

if (!$isMainAdmin) {
  header('Location: /Leilife/public/index.php?page=home');
  exit;
}

// Generate a new download token for this page load
if (empty($_SESSION['download_token'])) {
  $_SESSION['download_token'] = bin2hex(random_bytes(16));
}
$downloadToken = $_SESSION['download_token'];


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



<div class="container1">
  <div id="first-row">
    <div class="top-left">
      <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h2>Sales Management</h2>
    </div>

    <div class="export-buttons">
      <button onclick="exportFile('csv')">Export CSV</button>
      <button onclick="exportFile('excel')">Export Excel</button>
      <button onclick="exportFile('pdf')">Export PDF</button>
    </div>
  </div>



  <div class="filters">
    <div class="filter-group">
      <label>Status:</label>
      <select id="statusFilter">
        <option value="All" <?= $status === 'All' ? 'selected' : '' ?>>All</option>
        <option value="picked_up" <?= $status === 'picked_up' ? 'selected' : '' ?>>Picked up</option>
        <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
        <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>

    </div>

    <div class="filter-group" id="date-range">
      <label>Date Range:</label>
      <input type="date" id="fromDate" value="<?= htmlspecialchars($fromDate) ?>"><span id="dash">-</span>
      <input type="date" id="toDate" value="<?= htmlspecialchars($toDate) ?>">
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


  <!-- === Table Container === -->
  <div id="table-container">
    <div class="table-wrapper">
      <table class="staff-table">
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="ordersTableBody"></tbody>
      </table>
    </div>
  </div>




  <!-- ===== Order Details Modal ===== -->
  <div id="detailsModal" class="modal" style="text-transform: capitalize;">
    <div class="modal-card">
      <div class="modal-header">
        <h2>Order Details</h2>
        <button class="modal-close" onclick="closeModal()">✖</button>
      </div>

      <div class="modal-info">
        <p><strong>Order No:</strong> <span id="modalOrderNumber"></span></p>
        <p><strong>Customer:</strong> <span id="modalCustomer"></span></p>
        <p><strong>Date:</strong> <span id="modalDate"></span></p>
        <p><strong>Payment:</strong> <span id="modalPayment"></span></p>
        <p><strong>Status:</strong> <span id="modalStatus"></span></p>
        <p><strong>Total:</strong> <span id="modalTotal"></span></p>
      </div>

      <div class="modal-body">
        <h4>Items Ordered:</h4>
        <ul id="modalItemsList"></ul>
      </div>

      <div class="modal-buttons">
        <button id="closeMessageBtn" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>


  <script>
    const downloadToken = '<?= $downloadToken ?>'; // single-use token for export
    const orders = <?= json_encode($orders) ?>;

    function exportFile(type) {
      const status = document.getElementById('statusFilter').value;

      if (status.toLowerCase() === 'cancelled') {
        alert('Cancelled orders cannot be included in the sales report.');
        return;
      }

      const fromDate = document.getElementById('fromDate').value;
      const toDate = document.getElementById('toDate').value;
      const payment = document.getElementById('paymentFilter').value;

      let page = '';
      if (type === 'pdf') page = 'sales-report-pdf';
      else if (type === 'excel') page = 'sales-report-excel';
      else if (type === 'csv') page = 'sales-report-csv';
      else return alert('Invalid export type.');

      const params = [];
      params.push('page=' + encodeURIComponent(page));
      params.push('download=1');
      params.push('token=' + encodeURIComponent(downloadToken));

      if (fromDate) params.push(`fromDate=${encodeURIComponent(fromDate)}`);
      if (toDate) params.push(`toDate=${encodeURIComponent(toDate)}`);
      if (status && status.toLowerCase() !== 'all') params.push(`status=${encodeURIComponent(status)}`);
      if (payment && payment.toLowerCase() !== 'all') params.push(`payment=${encodeURIComponent(payment)}`);

      const url = '/Leilife/public/admin.php?' + params.join('&');
      window.open(url, '_blank');
    }


    // TABLE RENDER
    const tbody = document.getElementById("ordersTableBody");

    function renderTable(filtered = orders) {
      tbody.innerHTML = "";
      filtered.forEach(order => {
        // const driver = order.driver_name || 'Undefined';
        const payment = order.payment_method || 'Undefined';
        const date = order.date ? order.date.slice(0, 10) : 'Undefined';
        const total = parseFloat(order.total || 0).toFixed(2);
        const customer = order.customer_name || 'Undefined';
        const orderNumber = order.order_number || 'Undefined';

        const row = document.createElement("tr");
        row.innerHTML = `
  <td data-label="Order ID">#${orderNumber}</td>
  <td data-label="Customer" style="text-transform: capitalize;">${customer}</td>
  <td data-label="Total">₱${total}</td>
  <td data-label="Status" style="text-transform: capitalize;">${order.status || 'Undefined'}</td>
  <td data-label="Payment" style="text-transform: capitalize;">${payment}</td>
  <td data-label="Date">${new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric'
  })}</td>

  <td data-label="Actions" style="display:flex; justify-content:center;" class="actions">
    <button class="view-btn" onclick="viewDetails('${orderNumber}')">View</button>
  </td>
`;
        tbody.appendChild(row);

      });
    }

    renderTable();

    // VIEW DETAILS MODAL
    function viewDetails(orderNumber) {
      const order = orders.find(o => o.order_number === orderNumber);
      if (!order) return;

      document.getElementById("modalOrderNumber").textContent = order.order_number || "Undefined";
      document.getElementById("modalCustomer").textContent = order.customer_name || "Undefined";
      document.getElementById("modalPayment").textContent = order.payment_method || "Undefined";
      document.getElementById("modalStatus").textContent = order.status || "Undefined";
      document.getElementById("modalDate").textContent = order.date ? order.date.slice(0, 10) : "Undefined";
      document.getElementById("modalTotal").textContent = `₱${parseFloat(order.total || 0).toFixed(2)}`;

      const itemsList = document.getElementById("modalItemsList");
      const items = Array.isArray(order.items) ? order.items : [];
      itemsList.innerHTML = items.length ?
        items.map(i => `<li>${i.product_name || "Undefined"} × ${i.quantity || 1}</li>`).join("") :
        "<li>No items found</li>";

      document.getElementById("detailsModal").style.display = "flex";
    }


    function closeModal() {
      document.getElementById("detailsModal").style.display = "none";
    }

    window.addEventListener("click", function(e) {
      const modal = document.getElementById("detailsModal");
      if (e.target === modal) {
        modal.style.display = "none";
      }
    });


    // FILTER CHANGE HANDLING — RELOAD WITH GET PARAMS
    document.querySelectorAll('#statusFilter, #paymentFilter, #fromDate, #toDate')
      .forEach(el => el.addEventListener('change', () => {
        const params = new URLSearchParams(window.location.search);
        params.set('status', document.getElementById('statusFilter').value);
        params.set('payment', document.getElementById('paymentFilter').value);
        params.set('fromDate', document.getElementById('fromDate').value);
        params.set('toDate', document.getElementById('toDate').value);
        window.location.search = params.toString(); // refresh page with filters
      }));



    const dash = document.getElementById('dash');

    function updateDash() {
      if (window.innerWidth <= 530) {
        dash.style.display = 'none'; // remove dash
      } else {
        dash.style.display = 'inline'; // show dash
      }
    }

    // Run on load
    updateDash();

    // Run on window resize
    window.addEventListener('resize', updateDash);
  </script>