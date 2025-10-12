<?php

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$messages = $appData->loadMessagesToday($archived);
$topProducts = $appData->topProducts();
$orderCounts = $appData->getTodayOrdersByStatus();
$totalActiveAdmin = $appData->activeAdmin();
$totalActiveDriver = $appData->activeDriver();

?>
<!-- ===============================
     MODERN DASHBOARD STYLING
     =============================== -->
<style>
:root {
  --bg: #f9fafb;
  --card: #ffffff;
  --border: #e5e7eb;
  --text: #111827;
  --muted: #6b7280;
  --accent: #4f46e5;
  --accent-light: #eef2ff;
  --shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
  --radius: 12px;
  --transition: all 0.25s ease;
  --pending: #facc15;
  --preparing: #fb923c;
  --ready: #38bdf8;
  --delivered: #10b981;
  --cancelled: #ef4444;
  font-family: "Poppins", "Inter", sans-serif;
}

/* Base Styles */
body {
  margin: 0;
  padding: 0;
  background: var(--bg);
  color: var(--text);
  font-size: 15px;
  line-height: 1.5;
}

/* Container */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 24px 20px;
}

/* ----------------------------
   HEADER BAR
---------------------------- */
#first-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 28px;
  flex-wrap: wrap;
  gap: 12px;
}

#first-row h2 {
  font-size: 28px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
}

#first-row h2::before {
  content: "☰";
  font-size: 22px;
  color: var(--accent);
  cursor: pointer;
}

/* Filter Dropdown */
#salesFilterForm select {
  padding: 8px 14px;
  border-radius: 20px;
  border: 1px solid var(--border);
  background: var(--card);
  box-shadow: var(--shadow);
  cursor: pointer;
  color: var(--text);
  transition: var(--transition);
}

#salesFilterForm select:hover {
  background: var(--accent-light);
}

/* ----------------------------
   ORDER STATUS CARDS
---------------------------- */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat {
  background: var(--card);
  box-shadow: var(--shadow);
  border-radius: var(--radius);
  padding: 18px;
  display: flex;
  flex-direction: column;
  transition: var(--transition);
  cursor: pointer;
  border-left: 6px solid transparent;
}

.stat:hover {
  transform: translateY(-4px);
}

.stat p {
  font-size: 14px;
  color: var(--muted);
  margin: 0 0 6px;
}

.stat h3 {
  font-size: 22px;
  font-weight: 700;
  margin: 0;
}

.stat--pending { border-color: var(--pending); }
.stat--preparing { border-color: var(--preparing); }
.stat--ready { border-color: var(--ready); }
.stat--delivered { border-color: var(--delivered); }
.stat--cancelled { border-color: var(--cancelled); }
.stat--admin { border-color: var(--accent); }
.stat--driver { border-color: var(--accent-light); }

/* ----------------------------
   RECENT ORDERS
---------------------------- */
#third-row {
  background: var(--card);
  box-shadow: var(--shadow);
  border-radius: var(--radius);
  padding: 22px;
  margin-bottom: 24px;
}

.recent-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 14px;
}

.recent-top p {
  font-size: 17px;
  font-weight: 600;
  margin: 0;
}

.sort-dropdown {
  display: flex;
  align-items: center;
  gap: 6px;
}

.sort-dropdown select {
  border: 1px solid var(--border);
  background: var(--accent-light);
  padding: 6px 10px;
  border-radius: 10px;
  font-size: 14px;
  cursor: pointer;
  transition: var(--transition);
}

.sort-dropdown select:hover {
  background: var(--accent);
  color: white;
}

/* Table Body (Flex rows) */
#table-body .table-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 14px;
  border-bottom: 1px solid var(--border);
  transition: var(--transition);
}

#table-body .table-row:hover {
  background: #f9fafb;
}

/* Status Buttons */
.status-btn {
  border: none;
  border-radius: 8px;
  padding: 6px 14px;
  cursor: pointer;
  font-weight: 600;
  text-transform: capitalize;
  transition: all 0.25s ease;
  color: #333;
}

.status-btn:hover {
  opacity: 0.9;
}

.status-menu {
  position: absolute;
  top: 110%;
  left: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  padding: 6px 0;
  min-width: 150px;
  z-index: 100;
  transform: scale(1);
  opacity: 1;
  transform-origin: top;
  transition: all 0.2s ease;
}

.status-menu.hidden {
  opacity: 0;
  transform: scale(0.95);
  pointer-events: none;
}

.status-option {
  padding: 8px 14px;
  cursor: pointer;
  transition: background 0.2s;
}

.status-option:hover {
  background: #f0f0f0;
}


/* ----------------------------
   RECENT MESSAGES
---------------------------- */
#table-container {
  background: var(--card);
  box-shadow: var(--shadow);
  border-radius: var(--radius);
  padding: 22px;
}

#table-container p {
  font-weight: 700;
  margin-bottom: 12px;
  font-size: 16px;
}

.staff-table {
  width: 100%;
  border-collapse: collapse;
}

.staff-table th, .staff-table td {
  padding: 10px 12px;
  text-align: left;
  border-bottom: 1px solid var(--border);
}

.staff-table th {
  background: var(--accent-light);
  font-weight: 600;
  color: var(--text);
}

.staff-table tr:hover {
  background: #f9fafb;
}

.staff-table .unread {
  background: #eef2ff;
  font-weight: 600;
}

.editBtn {
  background: var(--accent);
  color: #fff;
  border: none;
  border-radius: 20px;
  padding: 6px 14px;
  cursor: pointer;
  transition: var(--transition);
}

.editBtn:hover {
  background: #4338ca;
}
/* ==== BASE RESET ==== */
#table {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  overflow: hidden;
  font-family: 'Poppins', sans-serif;
}

#table-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f7f8fa;
  padding: 14px 24px;
  border-bottom: 1px solid #e6e6e6;
  font-weight: 600;
  color: #333;
  font-size: 14px;
}

#table-body {
  display: flex;
  flex-direction: column;
}

/* ==== TABLE ROWS ==== */
.table-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 24px;
  transition: all 0.2s ease-in-out;
  background: #fff;
}

.table-row:nth-child(odd) {
  background: #fafafa;
}

.table-row:hover {
  background: #f0f4ff;
  transform: translateY(-2px);
}

/* ==== TEXT CELLS ==== */
.table-row p {
  margin: 0;
  color: #444;
  font-size: 14px;
  font-weight: 500;
}

/* ==== STATUS BUTTON ==== */
.status-btn {
  padding: 6px 12px;
  border: none;
  border-radius: 8px;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.1s ease;
}

.status-btn[data-status="pending"] {
  background: #fff3cd;
  color: #856404;
}

.status-btn[data-status="preparing"] {
  background: #d1ecf1;
  color: #0c5460;
}

.status-btn[data-status="ready_for_delivery"] {
  background: #d4edda;
  color: #155724;
}

.status-btn:hover {
  transform: scale(1.05);
}

/* ==== STATUS MENU ==== */
.status-menu {
  position: absolute;
  top: 120%;
  left: 0;
  width: 150px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 6px 16px rgba(0,0,0,0.08);
  z-index: 10;
  overflow: hidden;
}

.status-option {
  padding: 10px 14px;
  display: block;
  font-size: 14px;
  color: #333;
  cursor: pointer;
  transition: background 0.15s;
}

.status-option:hover {
  background: #f2f4f8;
}

/* ==== EXPANDABLE ROW (ORDER ITEMS) ==== */
.expandable-row {
  background: #f9fafc;
  padding: 16px 36px;
  animation: fadeIn 0.25s ease-in-out;
  border-top: 1px solid #eee;
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-4px);}
  to {opacity: 1; transform: translateY(0);}
}

/* ==== ITEM TABLE INSIDE EXPAND ==== */
.expandable-row table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 6px;
  font-size: 13px;
}

.expandable-row th, 
.expandable-row td {
  padding: 10px 12px;
  text-align: left;
}

.expandable-row thead {
  background: #f2f4f7;
  color: #333;
  text-transform: uppercase;
  font-size: 12px;
  font-weight: 600;
}

.expandable-row tbody tr {
  border-bottom: 1px solid #eee;
}

.expandable-row select {
  padding: 5px 10px;
  border-radius: 6px;
  border: 1px solid #ccc;
  font-size: 13px;
}

/* ==== RESPONSIVE DESIGN ==== */
@media (max-width: 768px) {
  #table-title, .table-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .table-row p, .status-btn {
    width: 100%;
    margin-bottom: 6px;
  }
}


/* ----------------------------
   MODALS
---------------------------- */
.modal {
  display: none;
  position: fixed;
  z-index: 2000;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(17, 24, 39, 0.4);
  backdrop-filter: blur(4px);
  justify-content: center;
  align-items: center;
}

.modal.show { display: flex; }

.modal-content {
  background: var(--card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  padding: 24px;
  width: 90%;
  max-width: 420px;
  position: relative;
  animation: slideUp 0.3s ease;
}

.close-btn, .close {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 22px;
  cursor: pointer;
  color: var(--muted);
  transition: var(--transition);
}

.close-btn:hover, .close:hover {
  color: var(--accent);
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

/* ----------------------------
   RESPONSIVE DESIGN
---------------------------- */
@media (max-width: 768px) {
  #first-row h2 {
    font-size: 22px;
  }

  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }

  #third-row, #table-container {
    padding: 16px;
  }

  .staff-table th, .staff-table td {
    font-size: 13px;
  }
}

@media (max-width: 480px) {
  .stats-row {
    grid-template-columns: 1fr;
  }

  #salesFilterForm select {
    padding: 6px 10px;
  }

  .editBtn {
    padding: 4px 10px;
  }
}
</style>

</style>

<div class="container">
  <div id="first-row">
      <h2>Dashboard</h2>
      <!-- you can keep the sales filter at header if desired -->
      <form method="GET" id="salesFilterForm" style="display:flex; align-items:center;">
          <select id="sales-period" name="period" onchange="document.getElementById('salesFilterForm').submit()">
              <option value="today" <?= (!isset($_GET['period']) || $_GET['period'] === 'today') ? 'selected' : '' ?>>Today</option>
              <option value="week" <?= (isset($_GET['period']) && $_GET['period'] === 'week') ? 'selected' : '' ?>>This Week</option>
              <option value="month" <?= (isset($_GET['period']) && $_GET['period'] === 'month') ? 'selected' : '' ?>>This Month</option>
              <option value="year" <?= (isset($_GET['period']) && $_GET['period'] === 'year') ? 'selected' : '' ?>>This Year</option>
          </select>
      </form>
  </div>


  <!-- stats row -->
  <div class="stats-row" style="margin-bottom:14px;">
    <div class="stat stat--pending"><div><p>Pending</p><h3 id="pending-count"><?= $orderCounts['pending'] ?></h3></div></div>
    <div class="stat stat--preparing"><div><p>Preparing</p><h3 id="preparing-count"><?= $orderCounts['preparing'] ?></h3></div></div>
    <div class="stat stat--ready"><div><p>Ready to Deliver</p><h3 id="ready-count"><?= $orderCounts['ready_for_delivery'] ?></h3></div></div>
    <div class="stat stat--delivered"><div><p>Delivered</p><h3 id="delivered-count"><?= $orderCounts['delivered'] ?></h3></div></div>
    <div class="stat stat--cancelled"><div><p>Cancelled</p><h3 id="cancelled-count"><?= $orderCounts['cancelled'] ?></h3></div></div>
    <div class="stat stat--admin"><div><p>Active Admins</p><h3><?= $totalActiveAdmin?></h3></div></div>
    <div class="stat stat--driver"><div><p>Active Drivers</p><h3><?= $totalActiveDriver?></h3></div></div>
  </div>

  <!-- Recent orders -->
  <div id="third-row" class="recent">
    <div class="recent-top">
      <p><strong>Recent Orders</strong></p>
      <div class="sort-dropdown" style="margin-left:auto;">
        <label for="sort">Sort by:</label>
        <select id="sort" style="margin-left:6px;">
            <option value="order_date">Order Date</option>
            <option value="status">Status</option>
            <option value="total">Total</option>
        </select>
      </div>
    </div>

    <div id="table">
      <div id="table-title">
          <p style="width: 20%;">Order #</p>
          <p style="width: 25%;">Customer</p>
          <p style="width: 20%;">Amount</p>
          <p style="width: 10%;">Item</p>
          <p style="width: 20%;">Status</p>
      </div>

      <div id="table-body">
        <!-- rows are injected by JS (loadOrders) -->
      </div>
    </div>
  </div>

  <!-- Inbox -->
  <div id="table-container">
    <p style="font-weight:700; margin-bottom:8px;">Recent Messages</p>
    <table class="staff-table" aria-live="polite">
      <thead>
        <tr>
          <th>Name</th><th>Email</th><th>Subject</th><th>Type</th><th>Date</th><th style="text-align:center;">Actions</th>
        </tr>
      </thead>
      <tbody id="inboxTableBody">
        <?php if ($messages && count($messages) > 0): ?>
            <?php foreach ($messages as $msg): ?>
                <tr id="row-<?= $msg['sender_id'] ?>" class="<?= $msg['status'] == 0 ? 'unread' : '' ?>">
                    <td><?= htmlspecialchars($msg['name'] ?? 'Guest') ?></td>
                    <td><?= htmlspecialchars($msg['email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($msg['subject'] ?? '(No Subject)') ?></td>
                    <td><?= ucfirst(htmlspecialchars($msg['type'])) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
                    <td class="actions">
                        <button type="button" class="editBtn" data-message="<?= htmlspecialchars($msg['message']) ?>" data-id="<?= $msg['sender_id'] ?>">View</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" style="text-align:center; padding:18px;">No messages found</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Message modal -->
  <div id="messageModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="modal-content" role="document">
          <span class="close-btn" aria-label="Close">&times;</span>
          <h2 style="margin-top:0;">Message</h2>
          <p id="modalMessage" style="white-space:pre-wrap; color:var(--muted);"></p>
      </div>
  </div>

  <?php if ($showWelcome): ?>
  <div id="welcomeModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="modal-content">
          <span class="close" onclick="closeWelcome()" aria-hidden="true">&times;</span>
          <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
          <p style="color:var(--muted)">You're now logged in.</p>
          <button onclick="closeWelcome()" style="margin-top:12px; padding:8px 12px; border-radius:10px; border:none; background:var(--accent); color:white; cursor:pointer;">Continue</button>
      </div>
  </div>
  <?php endif; ?>
</div>


<!-- External JS base url -->
<script>
    const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/Leilife/", "/") ?>/";
</script>

<!-- ============================
     CLEANED & ORGANIZED JS
     (keeps all function names / ids unchanged)
     ============================ -->
<script>
/* -------------------------------
   ROW SECTIONS (Mobile-friendly)
--------------------------------*/

let currentView = 'active'; // "active" or "completed"

document.addEventListener("DOMContentLoaded", () => {
    // Welcome modal
    if (window.showWelcome) {
        const welcomeModal = document.getElementById('welcomeModal');
        if (welcomeModal) {
            welcomeModal.style.display = 'flex';
            window.closeWelcome = function () { welcomeModal.style.display = 'none'; };
        }
    }

    // Inbox modal
    initInboxModal();

    // Order view toggle buttons (optional)
    const btnActive = document.getElementById('btnActiveOrders');
    const btnCompleted = document.getElementById('btnCompletedOrders');

    if (btnActive) {
        btnActive.addEventListener('click', () => {
            currentView = 'active';
            btnActive.classList.add('active');
            if (btnCompleted) btnCompleted.classList.remove('active');
            const sortEl = document.getElementById('sort');
            loadOrders(sortEl ? sortEl.value : 'order_date');
        });
    }

    if (btnCompleted) {
        btnCompleted.addEventListener('click', () => {
            currentView = 'completed';
            btnCompleted.classList.add('active');
            if (btnActive) btnActive.classList.remove('active');
            loadOrders('order_date');
        });
    }

    // Sorting
    const sortEl = document.getElementById('sort');
    if (sortEl) {
        sortEl.addEventListener('change', e => {
            const selected = e.target.value;
            if (selected === 'completed') {
                currentView = 'completed';
                if (btnCompleted) { btnCompleted.classList.add('active'); }
                if (btnActive) { btnActive.classList.remove('active'); }
                loadOrders('order_date');
            } else {
                currentView = 'active';
                if (btnActive) { btnActive.classList.add('active'); }
                if (btnCompleted) { btnCompleted.classList.remove('active'); }
                loadOrders(selected);
            }
        });
    }

    // Load orders initially
    loadOrders(sortEl ? sortEl.value : 'order_date');

    // Close status menus when clicking outside
    document.addEventListener('click', e => {
        if (!e.target.classList.contains('status-btn')) {
            document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
        }
    });

    // Auto refresh counts and orders every 60s (optional)
    setInterval(() => {
        const s = document.getElementById('sort');
        loadOrders(s ? s.value : 'order_date');
    }, 60000);
});

// --------------------
// Inbox modal logic
// --------------------
function initInboxModal() {
    const modal = document.getElementById('messageModal');
    if (!modal) {
        // still attach edit buttons defensively
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                fetch(BASE_URL + "backend/admin/archive_message.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `mark_read=1&id=${id}`
                }).catch(() => {});
            });
        });
        return;
    }

    const modalMsg = document.getElementById('modalMessage');
    const closeBtn = modal.querySelector('.close-btn');
    if (closeBtn) closeBtn.onclick = () => (modal.style.display = "none");
    window.onclick = (e) => { if (e.target == modal) modal.style.display = "none"; };

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const msg = btn.dataset.message;
            const id = btn.dataset.id;
            if (modalMsg) modalMsg.textContent = msg;
            modal.style.display = "flex";

            fetch(BASE_URL + "backend/admin/archive_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `mark_read=1&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) document.querySelector(`#row-${data.id}`)?.classList.remove("unread");
            })
            .catch(err => console.error('archive_message error', err));
        });
    });
}

// ==================================================
// LOAD ORDERS
// ==================================================
async function loadOrders(sortBy = 'order_date') {
    const tableBody = document.getElementById('table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    try {
        const res = await fetch(`/Leilife/backend/admin/get_orders.php?view=${encodeURIComponent(currentView)}&sort=${encodeURIComponent(sortBy)}`);
        const data = await res.json();
        if (!data.success) {
            console.warn('get_orders returned success=false', data.error);
            tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
            updateCounts(0,0,0);
            return;
        }

        const orders = data.orders || [];
        if (orders.length === 0) {
            tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No ${currentView === 'active' ? 'active' : 'completed'} orders found.</p>`;
            updateCounts(0,0,0);
            return;
        }

        let pending = 0, preparing = 0, ready = 0;

        orders.forEach(order => {
            if (order.status === 'pending') pending++;
            if (order.status === 'preparing') preparing++;
            if (order.status === 'ready_for_delivery') ready++;

            // Main order row
            const row = document.createElement('div');
            row.classList.add('table-row');
            row.style.display = "flex";
            row.style.alignItems = "center";
            row.style.justifyContent = "space-between";
            row.style.padding = "10px 20px";
            row.style.borderBottom = "1px solid #ddd";
            row.style.cursor = "pointer";
            row.dataset.orderId = order.order_id;

            row.innerHTML = `
                <p style="width:20%;">${escapeHtml(order.order_number)}</p>
                <p style="width:25%;">${escapeHtml(order.customer_name || 'Unknown User')}</p>
                <p style="width:20%;">₱${parseFloat(order.total || 0).toFixed(2)}</p>
                <p style="width:10%; text-align:left;">${order.items_count}</p>
                <div style="width:20%; position:relative;">
                    <button class="status-btn" data-id="${order.order_id}" data-status="${order.status}">
                        ${formatStatus(order.status)}
                    </button>
                    <div class="status-menu hidden">
                        ${createStatusOptions(order.status)}
                    </div>
                </div>
            `;

            tableBody.appendChild(row);

            // Expandable item row
            const expandRow = document.createElement('div');
            expandRow.classList.add('expandable-row');
            expandRow.style.display = "none";
            expandRow.style.background = "#fafafa";
            expandRow.style.padding = "10px 30px";
            expandRow.style.borderBottom = "1px solid #ddd";
            expandRow.dataset.orderId = order.order_id;
            tableBody.appendChild(expandRow);

            row.addEventListener('click', async e => {
                if (e.target.classList.contains('status-btn') || e.target.classList.contains('status-option')) return;

                const wasVisible = expandRow.style.display === 'block';
                document.querySelectorAll('.expandable-row').forEach(el => el.style.display = 'none');
                if (wasVisible) { expandRow.style.display = 'none'; return; }

                expandRow.innerHTML = `<p style="color:#888;">Loading items...</p>`;
                expandRow.style.display = 'block';

                try {
                    const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`);
                    const itemData = await itemsRes.json();
                    if (itemData.success && Array.isArray(itemData.items)) {
                        expandRow.innerHTML = renderItemTable(itemData.items);
                        attachItemDelegatedListener(expandRow, order.order_id);
                    } else {
                        expandRow.innerHTML = `<p style="color:#888;">No items found.</p>`;
                    }
                } catch (err) {
                    expandRow.innerHTML = `<p style="color:red;">Error loading items</p>`;
                    console.error(err);
                }
            });
        });

        updateCounts(pending, preparing, ready);
        attachStatusListeners();

    } catch (err) {
        console.error("Error loading orders:", err);
        tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
        updateCounts(0,0,0);
    }
}

// ==================================================
// ITEM TABLE
// ==================================================
function renderItemTable(items) {
  return `
  <table class="item-table">
      <thead>
          <tr>
              <th>Item</th>
              <th>Qty</th>
              <th>Status</th>
          </tr>
      </thead>
      <tbody>
      ${items.map(i => {
          const st = (i.status || 'pending').toLowerCase();
          return `
          <tr data-item-id="${i.order_item_id}">
              <td>${escapeHtml(i.product_name)}</td>
              <td class="text-center">${i.quantity}</td>
              <td class="text-center">
                  <select class="item-status" data-prev="${st}">
                      <option value="pending" ${st === 'pending' ? 'selected' : ''}>Pending</option>
                      <option value="preparing" ${st === 'preparing' ? 'selected' : ''}>Preparing</option>
                      <option value="finished" ${st === 'finished' ? 'selected' : ''}>Finished</option>
                  </select>
                  <span class="status-feedback"></span>
              </td>
          </tr>`;
      }).join('')}
      </tbody>
  </table>`;
}


// ==================================================
// ITEM STATUS LISTENER
// ==================================================
function attachItemDelegatedListener(container, orderId) {
    if (container._itemListener) container.removeEventListener('change', container._itemListener);

    const listener = async function (e) {
        if (!e.target.matches('.item-status')) return;
        const select = e.target;
        const tr = select.closest('tr');
        const itemId = tr?.dataset?.itemId;
        if (!itemId) return;

        const newStatus = select.value;
        const prev = select.dataset.prev;
        const feedback = tr.querySelector('.status-feedback');

        select.disabled = true;
        if (feedback) feedback.textContent = '⏳';

        try {
            const res = await fetch('/Leilife/backend/admin/update_order_item_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_item_id: itemId, status: newStatus })
            });
            const result = await res.json();
            if (result.success) {
                select.dataset.prev = newStatus;
                if (feedback) { feedback.textContent = '✅'; setTimeout(() => feedback.textContent = '', 700); }
            } else {
                if (feedback) { feedback.textContent = '❌'; setTimeout(() => feedback.textContent = '', 900); }
                if (prev) select.value = prev;
            }
        } catch (err) {
            if (feedback) { feedback.textContent = '⚠️'; setTimeout(() => feedback.textContent = '', 900); }
            if (prev) select.value = prev;
            console.error(err);
        } finally {
            select.disabled = false;
        }
    };

    container._itemListener = listener;
    container.addEventListener('change', listener);
}

// ==================================================
// ORDER STATUS
// ==================================================
function createStatusOptions(current) {
    const statuses = ['pending', 'preparing', 'ready_for_delivery', 'delivered', 'cancelled'];
    return statuses.map(st => `<div class="status-option ${st === current ? 'active' : ''}" data-status="${st}">${formatStatus(st)}</div>`).join('');
}

function attachStatusListeners() {
  // Close all menus when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.status-btn') && !e.target.closest('.status-menu')) {
      document.querySelectorAll('.status-menu').forEach(menu => menu.classList.add('hidden'));
    }
  });

  // Status button click
  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.onclick = null;
    btn.addEventListener('click', e => {
      e.stopPropagation();

      const menu = btn.nextElementSibling;
      document.querySelectorAll('.status-menu').forEach(m => {
        if (m !== menu) m.classList.add('hidden');
      });

      if (menu) menu.classList.toggle('hidden');
    });
  });

  // Option click
  document.querySelectorAll('.status-option').forEach(option => {
    option.onclick = null;
    option.addEventListener('click', async e => {
      e.stopPropagation();

      const menu = option.closest('.status-menu');
      const btn = menu?.previousElementSibling;
      const orderId = btn?.dataset.id;
      const newStatus = option.dataset.status;

      if (!orderId) return;

      // Update UI instantly
      btn.textContent = formatStatus(newStatus);
      btn.dataset.status = newStatus;
      updateStatusButtonColor(btn, newStatus);
      menu.classList.add('hidden');

      // Sync expandable item dropdowns
      const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
      if (expandRow) {
        expandRow.querySelectorAll('.item-status').forEach(select => {
          select.value = mapOrderToItemStatus(newStatus);
          select.dataset.prev = select.value;
        });
      }

      // Send to backend
      try {
        const res = await fetch('/Leilife/backend/admin/update_order_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ order_id: orderId, status: newStatus })
        });
        const data = await res.json();
        if (!data.success) alert('Failed to update status');
      } catch (err) {
        console.error('Status update error:', err);
      }
    });
  });
}

// Helper function: change button color based on status
function updateStatusButtonColor(btn, status) {
  const colors = {
    pending: '#e0e0e0',
    preparing: '#f7d774',
    ready_for_delivery: '#7bc47f',
    delivered: '#5cb85c',
    cancelled: '#f26c6c'
  };
  const textColors = {
    pending: '#555',
    preparing: '#8a6d00',
    ready_for_delivery: '#0c5d18',
    delivered: '#fff',
    cancelled: '#fff'
  };
  btn.style.background = colors[status] || '#eee';
  btn.style.color = textColors[status] || '#333';
}




// ==================================================
// HELPERS
// ==================================================
function mapOrderToItemStatus(orderStatus) {
    switch (orderStatus) {
        case 'pending': return 'pending';
        case 'preparing': return 'preparing';
        case 'ready_for_delivery': return 'finished';
        case 'delivered': return 'finished';
        case 'cancelled': return 'pending';
        default: return 'pending';
    }
}

function updateCounts(pending, preparing, ready) {
    const p = document.getElementById('pending-count');
    const pr = document.getElementById('preparing-count');
    const r = document.getElementById('ready-count');
    if (p) p.innerText = pending;
    if (pr) pr.innerText = preparing;
    if (r) r.innerText = ready;
}

function formatStatus(status) {
    return status ? status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '';
}

function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
