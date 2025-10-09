<?php
session_start();

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /Leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}




$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$messages = $appData->loadInbox($archived);
?>



<div id="first-row">
    <h2>Dashboard</h2>
</div>

<!-- Row 1: Total Sales + Top Products -->
<div class="dashboard-row">
    <div class="dashboard-box sales-box">
        <div class="sales-header">
            <h3>Total Sales</h3>
            <select id="sales-period">
                <option>Today</option>
                <option>This Week</option>
                <option>This Month</option>
                <option>This Year</option>
            </select>
        </div>
        <h1 style="font-size:40px; margin-top:10px;">₱ 25,340</h1>
    </div>

    <div class="dashboard-box top-products-box">
        <h3>Top 3 Selling Products</h3>
        <ol style="margin-top:10px; font-size:18px;">
            <li>Milk Tea - 120 sold</li>
            <li>Burger - 95 sold</li>
            <li>Fries - 80 sold</li>
        </ol>
    </div>
</div>

<!-- Row 2: Pending / Preparing / Ready + Admin + Driver -->
<div class="dashboard-row">
    <div class="dashboard-box pending-box">
        <p>Pending</p>
        <h3 id="pending-count">15</h3>
    </div>

    <div class="dashboard-box preparing-box">
        <p>Preparing</p>
        <h3 id="preparing-count">8</h3>
    </div>

    <div class="dashboard-box ready-box">
        <p>Ready to Deliver</p>
        <h3 id="ready-count">10</h3>
    </div>

    <div class="dashboard-box admin-box">
        <p>Active Admins</p>
        <h3>3</h3>
    </div>

    <div class="dashboard-box driver-box">
        <p>Active Drivers</p>
        <h3>5</h3>
    </div>
</div>

<!-- Row 3: Recent Orders -->
<div id="third-row">
    <div id="top">
        <p><strong>Recent Orders</strong></p>
        <div class="sort-dropdown">
            <label for="sort">Sort by:</label>
            <select id="sort">
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
            <p><span>#00123</span><span>John Doe</span><span>₱350</span><span>2</span><span>Delivered</span></p>
        </div>
    </div>
</div>

<!-- Row 4: Inbox -->
<!-- === Table Container === -->
<div id="table-container">
    <p><strong>Recent Messages</strong></p>
    <br>
  <table class="staff-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Type</th>
        <th>Date</th>
        <th style="text-align:center;">Actions</th>
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
              <button
                type="button"
                class="editBtn"
                data-message="<?= htmlspecialchars($msg['message']) ?>"
                data-id="<?= $msg['sender_id'] ?>"
                onclick="viewMessage(this)">
                View
              </button>

              <button
                type="button"
                class="archiveBtn"
                data-id="<?= $msg['sender_id'] ?>">
                <img src="public/assests/archive.png" alt="Archive" style="width:24px; height:24px;">
              </button>
            </td>

          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align:center;">No messages found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- === Modal for Viewing Message === -->
<div id="messageModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <h2>Message</h2>
    <p id="modalMessage"></p>
  </div>
</div>

<!-- ✅ Welcome Modal -->
<?php if ($showWelcome): ?>
    <div id="welcomeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeWelcome()">&times;</span>
            <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
            <p>You're now logged in.</p>
            <button onclick="closeWelcome()">Continue</button>
        </div>
    </div>
<?php endif; ?>

<script>
<?php if ($showWelcome): ?>
    const welcomeModal = document.getElementById('welcomeModal');
    welcomeModal.style.display = 'flex';
    window.closeWelcome = function () {
        welcomeModal.style.display = 'none';
    }
<?php endif; ?>
</script>

<script src="/Leilife/Scripts/admin/components/dashboard.js"></script>
