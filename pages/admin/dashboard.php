<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: /leilife/public/index.php');
  exit;
}
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
<style>
:root{
--bg:#f4f6f9;
--card:#ffffff;
--muted:#6b7280;
--accent:#007bff;
--danger:#e85959;
--success:#2aa05b;
--surface-border:#e6e9ee;
--pending: #fbeda8;
--preparing: #bde7f5;
--ready: #7ac37e;
--delivered: #10b981;
--cancelled: #ef4444;
}

/* Base Styles */
body {
background: var(--bg);
font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
color:#111827;
margin:0;
padding:0;
}
#first-row { display: flex; align-items: center; gap: 10px;padding-bottom: 10px;margin-top: 10px;} 
#first-row h2 { margin: 0; font-size: 1.5rem; color: #1a353c; font-weight: 600; } /* Hamburger button container */ 
.hamburger { display: flex; flex-direction: column; justify-content: center; gap: 4px; width: 28px; height: 24px; background: none; border: none; cursor: pointer; padding: 0; } /* The three bars */ 
.hamburger span { display: block; height: 3px; width: 100%; background-color: #1205ff; border-radius: 3px; transition: all 0.3s ease; } /* Hide on desktop */ 
@media (min-width: 768px) { .hamburger { display: none; } }

/* Stats Cards */
.stats-row {
display: grid;
grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
gap: 14px;
margin-bottom: 24px;
}
.surface{
  margin-right: 30px;
}
.stat {
background: var(--card);
border-radius: 10px;
padding: 18px;
display: flex;
flex-direction: column;
transition: transform 0.2s;
cursor: pointer;
border-left: 5px solid transparent;
}

.stat:hover { transform: translateY(-4px); }

.stat p { font-size: 14px; color: var(--muted); margin: 0 0 6px; }
.stat h3 { font-size: 22px; font-weight: 700; margin: 0; }

/* Status Border Colors */
.stat--pending { border-color: var(--pending); }
.stat--preparing { border-color: var(--preparing); }
.stat--ready { border-color: var(--ready); }
.stat--delivered { border-color: var(--delivered); }
.stat--cancelled { border-color: var(--cancelled); }
.stat--admin { border-color: var(--accent); }

/* Search */
#orderSearch {
padding:8px 10px;
border-radius:8px;
border:1px solid var(--surface-border);
width:100%;
max-width:360px;
}

/* Recent Orders Table */
.recent {
background: var(--card);
border-radius:12px;
padding:14px;
box-shadow:0 6px 18px rgba(11,22,39,0.03);
border:1px solid var(--surface-border);
margin-bottom:16px;
}

.recent-top { display:flex; gap:12px; align-items:center; margin-bottom:12px; flex-wrap:wrap; }
.recent-top p { font-weight:700; margin:0; }

.sort-dropdown { margin-left:auto; display:flex; align-items:center; gap:8px; }

/* Table */
#table { width:100%; border-radius:8px; overflow:hidden; border:1px solid var(--surface-border); background:transparent; }
#table-title, .table-row { display:flex; align-items:center; padding:10px 12px; gap:8px; font-size:14px; }
#table-title { background:#fbfdff; font-weight:700; border-bottom:1px solid var(--surface-border); color:#111827; }
.table-row { background:var(--card); border-bottom:1px solid #f1f3f5; transition:background .12s ease; cursor:pointer; }
.table-row:hover { background:#fbfdff; }

/* Column widths */
.col-order { width:100%; min-width:200px; }
.col-customer { width:100%; min-width:110px; }
.col-amount { width:100%; min-width:90px; }
.col-items { width:100%; min-width:50px; text-align:left; }
.col-status { width:100%; min-width:120px; position:relative; }
.col-payment { width:100%; min-width:100px; }
.col-method { width:100%; min-width:90px; }
.col-receipt { width:100%; min-width:90px; display:flex; justify-content:center; }
.col-receipt button {
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 14px;
    cursor: pointer;
    transition: background 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .col-receipt button img {
    width: 16px;
    height: 16px;
  }

  .col-receipt button:hover {
    background: #4338ca;
  }
/* Table body */
#table-body { max-height:580px; overflow:auto; min-height:420px; background:transparent; }

/* Status menu */
.status-wrapper { position: relative; display: inline-block; }
.status-btn { border:none; background:none; cursor:pointer; padding:4px 6px; }
.status-btn .badge { padding:5px 10px; border-radius:8px; color:#fff; font-weight:500; font-size:13px; }
.badge.s-pending { background:#ffb74d; }
.badge.s-preparing { background:#42a5f5; }
.badge.s-ready_for_delivery { background:#66bb6a; }
.badge.s-delivered { background:#26a69a; }
.badge.s-picked_up { background:#8d6e63; }
.badge.s-cancelled { background:#ef5350; }

.status-menu {
position:absolute;
top:calc(100% + 8px);
left:0;
min-width:180px;
background:#fff;
border-radius:8px;
border:1px solid rgba(0,0,0,0.06);
box-shadow:0 8px 20px rgba(11,22,39,0.06);
display:none;
z-index:1200;
flex-direction:column;
overflow:hidden;
}
.status-menu.visible { display:flex; }
.status-option { padding:10px 12px; cursor:pointer; font-size:14px; transition:background .12s; }
.status-option:hover { background:#f6f8fb; color:var(--accent); }

/* Expandable row */
.expandable-row { background:#f9fafc; padding:16px 36px; border-top:1px solid #eee; animation:fadeIn 0.25s ease-in-out; display:none; }
.expandable-row.show { display:block; }
@keyframes fadeIn { from{opacity:0;transform:translateY(-4px);} to{opacity:1;transform:translateY(0);} }

/* Expandable item table */
.expandable-row table { width:100%; border-collapse:collapse; margin-top:6px; font-size:13px; }
.expandable-row th, .expandable-row td { padding:10px 12px; text-align:left; }
.expandable-row thead { background:#f2f4f7; color:#333; text-transform:uppercase; font-size:12px; font-weight:600; }
.expandable-row tbody tr { border-bottom:1px solid #eee; }
.expandable-row select { padding:5px 10px; border-radius:6px; border:1px solid #ccc; font-size:13px; }

/* Pagination */
.pagination-bar { display:flex; justify-content:space-between; align-items:center; padding:10px 0; gap:8px; margin-top:12px; }
.pagination-left { color:var(--muted); font-size:13px; }
.pagination-controls { display:flex; gap:6px; align-items:center; }
.pg-btn { border:1px solid var(--surface-border); background:var(--card); padding:8px 10px; border-radius:8px; cursor:pointer; min-width:44px; text-align:center; }
.pg-btn.disabled { opacity:0.5; cursor:not-allowed; }
.pg-btn.active { background:var(--accent); color:#fff; border-color:var(--accent); }

/* Modals */
.modal { display:none; position:fixed; z-index:2000; top:0; left:0; width:100%; height:100%; background:rgba(17,24,39,0.4); backdrop-filter:blur(4px); justify-content:center; align-items:center; }
.modal.show { display:flex; }
.modal-content { background:var(--card); border-radius:var(--radius); box-shadow:var(--shadow); padding:24px; width:90%; max-width:420px; position:relative; animation:slideUp 0.3s ease; }
.close-btn, .close { position:absolute; top:12px; right:16px; font-size:22px; cursor:pointer; color:var(--muted); transition:var(--transition); }
.close-btn:hover, .close:hover { color:var(--accent); }

/* ===== Responsive Adjustments ===== */
/* ===== Responsive Adjustments ===== */
 

  /* Sort dropdown fits screen */
  .sort-dropdown { 
    width: 100%; 
    justify-content: space-between; 
  }


html, body {
  width: 100%;
  max-width: 100%;
  overflow-x: hidden; /* Prevents horizontal scroll */
  box-sizing: border-box;
}

/* Ensure all containers scale with screen width */
*,
*::before,
*::after {
  box-sizing: inherit;
  max-width: 100%;
}
.staff-table {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.staff-table th {
  background: #fbfdff;
  color: var(--text);
  font-weight: 600;
}
.staff-table tr:hover {
  background: #fafafa;
}
.table-wrapper {
  max-height: 70vh;         /* Controls vertical scroll area height */
  overflow-y: auto;          /* Enables vertical scrolling */
  overflow-x: hidden;        /* Prevents horizontal scrolling */
  border: 1px solid var(--border);
  border-radius: 8px;
}

@media screen and (max-width: 720px) {
  .table-wrapper {
    overflow-x: auto;       /* Allow horizontal scroll for mobile */
  }
  #table-title{
    display:none
  }
  .surface {
    margin-right:40px;
  }
  #table-body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 14px;
    text-align: left; /* 👈 Force left alignment globally */
  }

  .table-row {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* 👈 Align all children to left */
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .table-row:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.08);
  }

  /* Header Section */
  .col-order {
    font-weight: 700;
    font-size: 15px;
    color: var(--text);
    margin-bottom: 4px;
  }

  .col-order::before {
    content: "Order #: ";
    color: var(--muted);
    font-weight: 500;
  }

  .col-customer {
    font-size: 15px;
    color: var(--text);
    font-weight: 600;
    margin-bottom: 12px;
  }

  .col-customer::before {
    content: "Customer: ";
    color: var(--muted);
    font-weight: 500;
  }

  /* Info Section */
  .col-amount,
  .col-items,
  .col-payment,
  .col-method {
    font-size: 14px;
    color: var(--text);
    margin: 2px 0;
  }

  .col-amount::before,
  .col-items::before,
  .col-payment::before,
  .col-method::before {
    color: var(--muted);
    font-weight: 500;
    margin-right: 4px;
  }

  .col-amount::before { content: "Amount:"; }
  .col-items::before { content: "Items:"; }
  .col-payment::before { content: "Payment:"; }
  .col-method::before { content: "Method:"; }

  /* Status Section */
  .col-status {
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .col-status::before {
    content: "Status:";
    color: var(--muted);
    font-weight: 500;
  }

  .status-btn {
    border: none;
    background: none;
    padding: 0;
  }

  .badge {
    font-size: 13px;
    font-weight: 500;
    padding: 5px 10px;
    border-radius: 8px;
  }

  .s-preparing { background: #e0ecff; color: #2457d5; }

  /* Footer / Download button */
  .col-receipt {
    margin-top: 12px;
    width: 100%;
    display: flex;
    justify-content: flex-end; /* 👈 align button to right edge */
  }
 .staff-table {
  width: 700px;  
    border: none;
    background: transparent;
  }

  .staff-table thead {
    display: none; /* Hide table headers on small screens */
  }

  .staff-table tbody {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 14px;
  }

  .staff-table tr {
    display: flex;
    flex-direction: column;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
  }

  .staff-table tr:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
  }

  .staff-table td {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 6px 0;
    border: none !important;
    text-align: left;
    font-size: 14px;
    color: var(--text);
  }

  /* Label each field using before pseudo-elements */
  .staff-table td:nth-child(1)::before { content: "Name: "; color: var(--muted); font-weight: 500; }
  .staff-table td:nth-child(2)::before { content: "Email: "; color: var(--muted); font-weight: 500; }
  .staff-table td:nth-child(3)::before { content: "Subject: "; color: var(--muted); font-weight: 500; }
  .staff-table td:nth-child(4)::before { content: "Type: "; color: var(--muted); font-weight: 500; }
  .staff-table td:nth-child(5)::before { content: "Date: "; color: var(--muted); font-weight: 500; }
  .staff-table td:nth-child(6)::before { content: "Actions: "; color: var(--muted); font-weight: 500; }

  /* Actions button styling */
  .staff-table td.actions {
    
    margin-top: 8px;
  }

  .staff-table td.actions button {
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.2s ease;
  }

  .staff-table td.actions button:hover {
    background: #4338ca;
  }

  /* Unread highlight */
  .staff-table tr.unread {
    border: 2px solid var(--accent);
  }
  
}


  /* ===== Hide table header ===== */


  /* ===== Expandable row inside card ===== */
  .expandable-row {
    background: #f9fafc;
    padding: 14px 18px;
    border-radius: 10px;
    border: 1px solid #eee;
    margin-top: 6px;
  }

  /* ===== Text adjustments ===== */
  body {
    font-size: 14px;
    line-height: 1.5;
  }

  /* Make sure modals stay usable */
  .modal-content {
    width: 95%;
    max-width: 360px;
    padding: 20px;
  }

  /* Status menu alignment fix */
  .status-menu {
    right: 0;
    left: auto;
    min-width: 160px;
  }

@media screen and (min-width: 721px) {
  .staff-table {

    width: 100%;
    border-collapse: collapse;
    background: var(--card);
    border: 1px solid var(--surface-border);
    border-radius: 8px;
    overflow: hidden;
  }

  /* Table headers */
  .staff-table thead tr {
    background: #f3f4f6;
    border-bottom: 1px solid var(--surface-border);
  }

  .staff-table th {
    padding: 12px 14px;
    text-align: left;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.03em;
  }

  /* Table body cells */
  .staff-table td {
    padding: 14px;
    font-size: 14px;
    color: var(--text);
    border-bottom: 1px solid var(--surface-border);
    vertical-align: middle;
  }

  /* Alternate row background */
  .staff-table tr:nth-child(even) {
    background: #fafbfc;
  }

  /* Hover row effect */
  .staff-table tr:hover {
    background: #f5f7fa;
    transition: background 0.2s ease-in-out;
  }

  /* Actions column */
  .staff-table .actions {
    text-align: center;
  }

  .staff-table .actions button {
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 6px 12px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    transition: background 0.2s ease;
  }

  .staff-table .actions button:hover {
    background: var(--accent-light);
  }

  /* Unread row highlight */
  .staff-table tr.unread {
    background: #eef2ff;
    font-weight: 600;
  }

  /* Table container title */
  #table-container > p {
    font-size: 16px;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 10px;
  }
}

</style>
<div class="surface">
<div id="first-row">
  <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <h2>Dashboard</h2>
</div>

  <div class="stats-row" style="margin-bottom:14px;">
    <div class="stat stat--pending">
      <div>
        <p>Pending</p>
        <h3 id="pending-count"><?= $orderCounts['pending'] ?></h3>
      </div>
    </div>
    <div class="stat stat--preparing">
      <div>
        <p>Preparing</p>
        <h3 id="preparing-count"><?= $orderCounts['preparing'] ?></h3>
      </div>
    </div>
    <div class="stat stat--ready">
      <div>
        <p>Ready to Deliver</p>
        <h3 id="ready-count"><?= $orderCounts['ready_for_delivery'] ?></h3>
      </div>
    </div>
    <div class="stat stat--delivered">
      <div>
        <p>Delivered</p>
        <h3 id="delivered-count"><?= $orderCounts['delivered'] ?></h3>
      </div>
    </div>
    <div class="stat stat--cancelled">
      <div>
        <p>Cancelled</p>
        <h3 id="cancelled-count"><?= $orderCounts['cancelled'] ?></h3>
      </div>
    </div>
    <div class="stat stat--admin">
      <div>
        <p>Active Admins</p>
        <h3><?= $totalActiveAdmin ?></h3>
      </div>
    </div>
    <div class="stat stat--driver">
      <div>
        <p>Active Drivers</p>
        <h3><?= $totalActiveDriver ?></h3>
      </div>
    </div>
  </div>

  <div style="margin-bottom:12px; display:flex; align-items:center; gap:8px;">
    <input type="text" id="orderSearch" placeholder="Enter order number" style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; flex:1;">
  </div>

  <!-- Recent orders -->
  <div id="third-row" class="recent" aria-live="polite"> <div class="recent-top"> <p><strong>Recent Orders</strong></p> <div class="sort-dropdown" style="margin-left:auto;"> <label for="sort">Sort by:</label> <select id="sort" style="margin-left:6px;"> <option value="order_date">Order Date</option> <option value="status">Status</option> <option value="total">Total</option> <option value="pickup">Pick up</option> <option value="home_delivery">Home Delivery</option> </select> </div> </div> <div id="table" role="region" aria-label="Recent Orders Table"> <div id="table-title" aria-hidden="true"> <p class="col-order">Order #</p> <p class="col-customer">Customer</p> <p class="col-amount">Amount</p> <p class="col-items">Item</p> <p class="col-status">Status</p> <p class="col-payment">Payment Status</p> <p class="col-method">Method</p> <p class="col-receipt">Download Receipt</p> </div> <div id="table-body"> <!-- rows injected by JS --> </div> </div> <!-- pagination -->
   <div id="table-container" style="margin-top:12px;">
  <p style="font-weight:700; margin-bottom:8px;">Recent Messages</p>

  <div class="table-wrapper">
    <table class="staff-table" aria-live="polite">
      <thead>
        <tr style="background:#fbfdff;">
          <th style="padding:10px; text-align:left;">Name</th>
          <th style="padding:10px; text-align:left;">Email</th>
          <th style="padding:10px; text-align:left;">Subject</th>
          <th style="padding:10px; text-align:left;">Type</th>
          <th style="padding:10px; text-align:left;">Date</th>
          <th style="text-align:center; padding:10px;">Actions</th>
        </tr>
      </thead>
      <tbody id="inboxTableBody">
        <?php if ($messages && count($messages) > 0): ?>
          <?php foreach ($messages as $msg): ?>
            <tr id="row-<?= $msg['sender_id'] ?>" class="<?= $msg['status'] == 0 ? 'unread' : '' ?>">
              <td style="padding:10px;"><?= htmlspecialchars($msg['name'] ?? 'Guest') ?></td>
              <td style="padding:10px;"><?= htmlspecialchars($msg['email'] ?? '-') ?></td>
              <td style="padding:10px;"><?= htmlspecialchars($msg['subject'] ?? '(No Subject)') ?></td>
              <td style="padding:10px;"><?= ucfirst(htmlspecialchars($msg['type'])) ?></td>
              <td style="padding:10px;"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
              <td class="actions" style="padding:10px; text-align:center;">
                <button type="button" class="editBtn"
                        data-message="<?= htmlspecialchars($msg['message']) ?>"
                        data-id="<?= $msg['sender_id'] ?>">View</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:18px;">No messages found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

  <div id="messageModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none; position:fixed; inset:0; justify-content:center; align-items:center; z-index:60;">
    <div class="modal-content" role="document" style="background:var(--card); padding:18px; border-radius:10px; width:90%; max-width:600px; border:1px solid var(--surface-border);">
      <span class="close-btn" aria-label="Close" style="float:right; cursor:pointer; font-size:22px;">&times;</span>
      <h2 style="margin-top:0;">Message</h2>
      <p id="modalMessage" style="white-space:pre-wrap; color:var(--muted);"></p>
    </div>
  </div>

  <?php if ($showWelcome): ?>
    <div id="welcomeModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none; position:fixed; inset:0; justify-content:center; align-items:center; z-index:60;">
      <div class="modal-content" style="background:var(--card); padding:18px; border-radius:10px; width:90%; max-width:420px; border:1px solid var(--surface-border);">
        <span class="close" onclick="closeWelcome()" aria-hidden="true" style="float:right; cursor:pointer;">&times;</span>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
        <p style="color:var(--muted)">You're now logged in.</p>
        <button onclick="closeWelcome()" style="margin-top:12px; padding:8px 12px; border-radius:10px; border:none; background:var(--accent); color:white; cursor:pointer;">Continue</button>
      </div>
    </div>
  <?php endif; ?>
</div>

<div id="notif-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
  <div class="notif-content" style="background:white; padding:20px; border-radius:12px; text-align:center; max-width:300px;">
  </div>
</div>
</div>

<script>
const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://$_SERVER[HTTP_HOST]/Leilife/', '/') ?>/";
let currentPage = 1;
const rowsPerPage = 10;
let allOrders = [];
let currentView = 'active';

document.addEventListener('DOMContentLoaded', () => {
  // keep your welcome modal logic unchanged
  if (<?= $showWelcome ? 'true' : 'false' ?>) {
    const welcomeModal = document.getElementById('welcomeModal');
    if (welcomeModal) { welcomeModal.style.display = 'flex'; window.closeWelcome = () => welcomeModal.style.display = 'none'; }
  }

  initInboxModal();

  const sortEl = document.getElementById('sort');
  const searchEl = document.getElementById('orderSearch');

  if (sortEl) {
    sortEl.addEventListener('change', () => {
      currentPage = 1;
      loadOrders(sortEl.value, searchEl ? searchEl.value.trim() : '');
    });
  }

  if (searchEl) {
    let t;
    searchEl.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        currentPage = 1;
        loadOrders(sortEl ? sortEl.value : 'order_date', searchEl.value.trim());
      }, 300);
    });
  }

  loadOrders(sortEl ? sortEl.value : 'order_date', searchEl ? searchEl.value.trim() : '');

  // close status menus when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.status-btn') && !e.target.closest('.status-menu')) {
      document.querySelectorAll('.status-menu.visible').forEach(m => m.classList.remove('visible'));
    }
  });

  // periodic refresh if you want
  // setInterval(() => {
  //   const s = document.getElementById('sort');
  //   loadOrders(s ? s.value : 'order_date', document.getElementById('orderSearch')?.value || '');
  // }, 60000);
});

/* -------------------------
   Robust loadOrders() (keeps your original structure)
   ------------------------- */
async function loadOrders(sortBy = 'order_date', searchTerm = '') {
  const tableBody = document.getElementById('table-body');
  if (!tableBody) return;
  tableBody.innerHTML = '';

  try {
    const res = await fetch(`/Leilife/backend/admin/get_orders.php?view=${encodeURIComponent(currentView)}&sort=${encodeURIComponent(sortBy)}&order_number=${encodeURIComponent(searchTerm)}`, {cache: 'no-store'});
    const data = await res.json();

    if (!data.success) {
      tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
      updateCounts(0,0,0);
      return;
    }

    const orders = data.orders || [];
    if (orders.length === 0) {
      tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No ${currentView==='active'?'active':'completed'} orders found.</p>`;
      updateCounts(0,0,0);
      return;
    }

    let pending = 0, preparing = 0, ready = 0;

    // Build rows exactly like your original code but with consistent classes
    orders.forEach(order => {
      if (order.status === 'pending') pending++;
      if (order.status === 'preparing') preparing++;
      if (order.status === 'ready_for_delivery') ready++;

      const row = document.createElement('div');
      row.classList.add('table-row');
      row.dataset.orderId = order.order_id;

     row.innerHTML = `
  <p class="col-order">${escapeHtml(order.order_number)}</p>
  <p class="col-customer">${escapeHtml(order.customer_name || 'Unknown User')}</p>
  <p class="col-amount">₱${parseFloat(order.total || 0).toFixed(2)}</p>
  <p class="col-items">${order.items_count}</p>
  <div class="col-status">
    <button class="status-btn" data-id="${order.order_id}" data-status="${escapeAttr(order.status)}">
      <span class="badge s-${escapeCss(order.status)}">${formatStatus(order.status)}</span>
    </button>
    <div class="status-menu" aria-hidden="true">
      ${createStatusOptions(order.status, order.delivery_method)}
    </div>
  </div>
  <p class="col-payment">${escapeHtml(order.payment_status)}</p>
  <p class="col-method">${escapeHtml(order.delivery_method)}</p>
  <div class="col-receipt">
    <button class="dlBtn">
      <img src="/leilife/public/assests/downloads.png" alt="Download" style="width:20px;">
    </button>
  </div>
`;

      tableBody.appendChild(row);

      // download button handler
      const dlBtn = row.querySelector('.dlBtn');
      if (dlBtn) dlBtn.addEventListener('click', (e) => { e.stopPropagation(); downloadReceipt(order.order_number, order.user_id); });

      // expandable row element (inserted after row)
      const expandRow = document.createElement('div');
      expandRow.classList.add('expandable-row');
      expandRow.dataset.orderId = order.order_id;
      expandRow.style.display = 'none';
      tableBody.appendChild(expandRow);

      // click handler for expansion (careful with event targets)
      row.addEventListener('click', async (e) => {
        // ignore clicks inside status button/menu
        if (e.target.closest('.status-btn') || e.target.closest('.status-menu') || e.target.closest('.status-option') || e.target.closest('.dlBtn')) return;

        const wasVisible = expandRow.classList.contains('show');
        // hide all expandables
        document.querySelectorAll('.expandable-row.show').forEach(r => { r.classList.remove('show'); r.style.display = 'none'; });
        if (wasVisible) {
          expandRow.classList.remove('show'); expandRow.style.display = 'none';
          return;
        }

        expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">Loading items...</p>`;
        expandRow.classList.add('show'); expandRow.style.display = 'block';

        // robust fetch and parse
        try {
          const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`, {cache: 'no-store'});
          // Try parse JSON safely
          let itemData;
          try { itemData = await itemsRes.json(); } catch (jsonErr) {
            console.error('get_order_items returned non-JSON:', jsonErr);
            const txt = await itemsRes.text();
            console.warn('Raw response for get_order_items:', txt.substring(0,1000));
            expandRow.innerHTML = `<p style="color:red; padding:10px;">Error loading items (invalid response).</p>`;
            return;
          }

          if (!itemData || !itemData.success || !Array.isArray(itemData.items)) {
            console.warn('get_order_items structure unexpected:', itemData);
            expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">No items found.</p>`;
            return;
          }

          expandRow.innerHTML = renderItemTable(itemData.items);
          attachItemDelegatedListener(expandRow, order.order_id);
        } catch (err) {
          console.error('Error fetching order items:', err);
          expandRow.innerHTML = `<p style="color:red; padding:10px;">Error loading items.</p>`;
        }
      });
    }); // end foreach orders

    // update counts & global store
    updateCounts(pending, preparing, ready);
    allOrders = orders;

    // attach status handlers after DOM exists
    attachStatusListeners();

  } catch (err) {
    console.error('Error loading orders:', err);
    tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
    updateCounts(0,0,0);
  }
}

/* -------------------------
   renderOrders() and pagination code preserved below if you use it elsewhere
   (kept intact from your previous version — not required if you don't call it)
   ------------------------- */

//   function renderOrders() { 
//   const tableBody = document.getElementById('table-body'); 
//   tableBody.innerHTML = ''; 
//   const total = allOrders.length; 
//   const totalPages = Math.max(1, Math.ceil(total / rowsPerPage)); 
//   if (currentPage > totalPages) currentPage = totalPages; 
//   const start = (currentPage - 1) * rowsPerPage; 
//   const slice = allOrders.slice(start, start + rowsPerPage); 
  
//   // create rows
//   slice.forEach(order => { 
//     const row = document.createElement('div'); 
//     row.classList.add('table-row'); 
//     row.dataset.orderId = order.order_id; 
    
//     // build status badge + button
//     const statusBadge = `<span class="badge s-${escapeCss(order.status)}">${formatStatus(order.status)}</span>`; 
//     row.innerHTML = `
//       <div class="col-order"><p>${escapeHtml(order.order_number)}</p></div> 
//       <div class="col-customer"><p>${escapeHtml(order.customer_name || 'Unknown User')}</p></div> 
//       <div class="col-amount"><p id="order-total-${order.order_id}">₱${parseFloat(order.total || 0).toFixed(2)}</p></div> 
//       <div class="col-items"><p style="text-align:left;">${order.items_count ?? 0}</p></div> 
//       <div class="col-status">
//         <div style="display:flex; gap:8px; align-items:center;"> 
//           <button class="status-btn" data-id="${escapeAttr(order.order_id)}" data-status="${escapeAttr(order.status)}">${statusBadge}</button> 
//         </div> 
//         <div class="status-menu" aria-hidden="true"> 
//           ${createStatusOptions(order.status, order.delivery_method)} 
//         </div> 
//       </div> 
//       <div class="col-payment"><p>${escapeHtml(order.payment_status)}</p></div> 
//       <div class="col-method"><p>${escapeHtml(order.delivery_method)}</p></div> 
//       <div class="col-receipt"> 
//         <button class="dlBtn" aria-label="Download Receipt" style="background:transparent; border:0; padding:4px; cursor:pointer;"> 
//           <img src="/leilife/public/assests/downloads.png" alt="Download" style="width:20px;"> 
//         </button> 
//       </div>
//     `; 

//     tableBody.appendChild(row); 

//     // downloadable receipt
//     const dlBtn = row.querySelector('.dlBtn'); 
//     if (dlBtn) { 
//       dlBtn.addEventListener('click', (e) => { 
//         e.stopPropagation(); 
//         downloadReceipt(order.order_number, order.user_id); 
//       }); 
//     } 

//     // expandable area for items
//     const expandRow = document.createElement('div'); 
//     expandRow.classList.add('expandable-row'); 
//     expandRow.dataset.orderId = order.order_id; 
//     expandRow.style.display = 'none'; 
//     tableBody.appendChild(expandRow); 

//     // click to expand row (except when clicking status button or status menu)
//     row.addEventListener('click', async (e) => { 
//       if (e.target.closest('.status-btn') || e.target.closest('.status-menu') || e.target.closest('.status-option')) return; 
//       const wasVisible = expandRow.style.display === 'block'; 
//       document.querySelectorAll('.expandable-row').forEach(r => r.style.display = 'none'); 
//       if (wasVisible) { 
//         expandRow.style.display = 'none'; 
//         return; 
//       } 
//       expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">Loading items...</p>`; 
//       expandRow.style.display = 'block'; 
//       try { 
//         const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`); 
//         const itemData = await itemsRes.json(); 
//         if (itemData.success && Array.isArray(itemData.items)) { 
//           expandRow.innerHTML = renderItemTable(itemData.items); 
//           attachItemDelegatedListener(expandRow, order.order_id); 
//         } else { 
//           expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">No items found.</p>`; 
//         } 
//       } catch (err) { 
//         expandRow.innerHTML = `<p style="color:${'red'}; padding:10px;">Error loading items</p>`; 
//         console.error(err); 
//       } 
//     }); 
//   }); 
  
//   attachStatusListeners(); 
// } 



/* -------------------------
   Status menu generation & listeners
   ------------------------- */
function createStatusOptions(current, deliveryMethod) {
  let statuses = [];
  const dm = (deliveryMethod || '').toLowerCase();
  if (dm.includes('pickup')) statuses = ['pending','preparing','picked_up','cancelled'];
  else statuses = ['pending','preparing','ready_for_delivery','delivered','cancelled'];
  return statuses.map(s => `<div class="status-option" data-status="${s}">${formatStatus(s)}</div>`).join('');
}

function attachStatusListeners() {
  // Remove duplicate listeners and rebind menu toggle
  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.replaceWith(btn.cloneNode(true));
  });

  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const menu = btn.parentElement.querySelector('.status-menu');
      if (!menu) return;
      document.querySelectorAll('.status-menu.visible').forEach(m => {
        if (m !== menu) m.classList.remove('visible');
      });
      menu.classList.toggle('visible');
    });
  });

  // Remove duplicate listeners on options
  document.querySelectorAll('.status-option').forEach(opt => {
    opt.replaceWith(opt.cloneNode(true));
  });

  document.querySelectorAll('.status-option').forEach(opt => {
    opt.addEventListener('click', async (e) => {
      e.stopPropagation();
      const menu = opt.closest('.status-menu');
      const btn = menu ? menu.parentElement.querySelector('.status-btn') : null;
      const orderId = btn ? btn.dataset.id : null;
      const newStatus = opt.dataset.status;
      if (!orderId || !newStatus) return;

      const confirmAndDo = async () => {
        // Immediate visual update
        btn.innerHTML = `<span class="badge s-${escapeCss(newStatus)}">${formatStatus(newStatus)}</span>`;
        btn.dataset.status = newStatus;
        menu.classList.remove('visible');

        try {
          const res = await fetch('/Leilife/backend/admin/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: newStatus })
          });
          const data = await res.json();

          if (data.success) {
            console.log('Status updated:', orderId, '→', newStatus);

            // Update expandable items if visible
            const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
            if (expandRow) {
              expandRow.querySelectorAll('.item-status').forEach(sel => {
                sel.value = mapOrderToItemStatus(newStatus);
                sel.dataset.prev = sel.value;
              });
            }

            // Reattach listeners to keep menus working
            attachStatusListeners();

          } else {
            alert('Failed to update status on server.');
            loadOrders(document.getElementById('sort')?.value || 'order_date', document.getElementById('orderSearch')?.value || '');
          }

        } catch (err) {
          console.error('Status update error:', err);
          alert('Network error while updating. Please try again.');
        }
      };

      if (newStatus === 'cancelled') {
        showConfirmModal("Are you sure you want to cancel this order?", confirmAndDo);
      } else {
        confirmAndDo();
      }
    });
  });

  // Close any open menu when clicking outside
  document.addEventListener('click', () => {
    document.querySelectorAll('.status-menu.visible').forEach(m => m.classList.remove('visible'));
  });
}


/* -------------------------
   ITEM table render & item-status handling (kept near-original)
   ------------------------- */
function renderItemTable(items) {
  return `
    <table class="item-table" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left; padding:8px;">Item</th>
          <th style="text-align:center; padding:8px;">Qty</th>
          <th style="text-align:center; padding:8px;">Status</th>
        </tr>
      </thead>
      <tbody>
        ${items.map(i => {
          const st = (i.status || 'pending').toLowerCase();
          return `
            <tr data-item-id="${i.order_item_id}" class="status-${escapeCss(st)}">
              <td style="padding:8px;">${escapeHtml(i.product_name)}</td>
              <td style="padding:8px; text-align:center;">${i.quantity}</td>
              <td style="padding:8px; text-align:center;">
                <select class="item-status" data-prev="${st}">
                  <option value="pending" ${st==='pending'?'selected':''}>Pending</option>
                  <option value="preparing" ${st==='preparing'?'selected':''}>Preparing</option>
                  <option value="finished" ${st==='finished'?'selected':''}>Finished</option>
                  <option value="cancelled" ${st==='cancelled'?'selected':''}>Cancelled</option>
                </select>
                <span class="status-feedback"></span>
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
}

function attachItemDelegatedListener(container, orderId) {
  if (!container) return;
  if (container._itemListener) container.removeEventListener('change', container._itemListener);

  const listener = async (e) => {
    if (!e.target.matches('.item-status')) return;
    const select = e.target;
    const tr = select.closest('tr');
    const itemId = tr?.dataset?.itemId;
    if (!itemId) {
      console.warn('No itemId found in row:', tr);
      return;
    }

    const prev = select.dataset.prev;
    const newStatus = select.value;
    const feedback = tr.querySelector('.status-feedback');
    select.disabled = true;
    if (feedback) feedback.textContent = '⏳ Updating...';

    try {
      const res = await fetch('/Leilife/backend/admin/update_order_item_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_item_id: itemId, status: newStatus })
      });

      let resultText = await res.text();
      console.log('Raw response:', resultText);

      let result;
      try {
        result = JSON.parse(resultText);
      } catch {
        console.error('Invalid JSON response:', resultText);
        result = { success: false };
      }

      if (result.success) {
        console.log('✅ Item status updated successfully:', itemId, newStatus);

        // Update this row visually
        select.dataset.prev = newStatus;
        tr.className = `status-${escapeCss(newStatus)}`;

        // Update badge style instantly
        select.closest('td').style.background = 'var(--card)';
        if (feedback) {
          feedback.textContent = '✅';
          setTimeout(() => feedback.textContent = '', 800);
        }

        // Update main order badge (even without result.order_status)
        const effOrderId = orderId || tr.closest('.expandable-row')?.dataset?.orderId;
        const orderStatusBtn = document.querySelector(`.status-btn[data-id="${effOrderId}"]`);
        if (orderStatusBtn) {
          orderStatusBtn.innerHTML = `<span class="badge s-${escapeCss(newStatus)}">${formatStatus(newStatus)}</span>`;
          orderStatusBtn.dataset.status = newStatus;
        }

      } else {
        console.warn('❌ Backend returned failure:', result);
        select.value = prev;
        if (feedback) {
          feedback.textContent = '❌ Failed';
          setTimeout(() => feedback.textContent = '', 900);
        }
      }
    } catch (err) {
      console.error('⚠️ Network or script error:', err);
      select.value = prev;
      if (feedback) {
        feedback.textContent = '⚠️';
        setTimeout(() => feedback.textContent = '', 900);
      }
    } finally {
      select.disabled = false;
    }
  };

  container._itemListener = listener;
  container.addEventListener('change', listener);
}



/* -------------------------
   Helpers (kept simple)
   ------------------------- */
function downloadReceipt(order_number, user_id) {
  if (!order_number || !user_id) return;
  const url = `/Leilife/public/admin.php?page=pos-receipt&download=1&order_number=${encodeURIComponent(order_number)}&user_id=${encodeURIComponent(user_id)}`;
  window.open(url, '_blank');
}
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
  if (!status) return '';
  return String(status).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
function escapeHtml(str) {
  if (str == null) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function escapeAttr(v){ return String(v).replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
function escapeCss(v){ return String(v||'').replace(/[^a-z0-9_-]/gi,''); }

/* Confirm modal and Inbox preserved (assumes your HTML exists) */
function showConfirmModal(message, onConfirm) {
  let modal = document.getElementById("notif-modal");
  if (!modal) { onConfirm(); return; }
  const content = modal.querySelector(".notif-content");
  const originalHTML = content.innerHTML;
  content.innerHTML = `
    <p style="margin-bottom:12px;">${escapeHtml(message)}</p>
    <div style="display:flex; gap:10px; justify-content:center;">
      <button id="confirm-yes" style="padding:8px 12px; border-radius:8px; background:var(--accent); color:#fff; border:none;">Yes</button>
      <button id="confirm-no" style="padding:8px 12px; border-radius:8px; border:1px solid var(--surface-border); background:var(--card);">Cancel</button>
    </div>
  `;
  modal.style.display = 'flex';
  document.getElementById('confirm-yes').onclick = () => { modal.style.display='none'; content.innerHTML = originalHTML; onConfirm(); };
  document.getElementById('confirm-no').onclick = () => { modal.style.display='none'; content.innerHTML = originalHTML; };
}
function initInboxModal() {
  const modal = document.getElementById('messageModal');
  const modalMsg = document.getElementById('modalMessage');
  const closeBtn = modal?.querySelector('.close-btn');
  if (closeBtn) closeBtn.onclick = () => modal.style.display = 'none';
  window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

  // attach existing editBtn logic if present
  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      const msg = btn.dataset.message;
      const id = btn.dataset.id;
      if (modalMsg) modalMsg.textContent = msg;
      modal.style.display = 'flex';

      fetch(BASE_URL + "backend/admin/archive_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `mark_read=1&id=${id}`
      }).then(res => res.json()).then(data => {
        if (data.success) document.querySelector(`#row-${data.id}`)?.classList.remove("unread");
      }).catch(err => console.error('archive_message error', err));
    });
  });
}

</script>
