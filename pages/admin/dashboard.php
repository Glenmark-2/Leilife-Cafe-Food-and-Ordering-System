<?php
// session_start();

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

// if (!isset($_SESSION['admin_id'])) {
//     header('Location: /Leilife/pages/admin/login-x9P2kL7zQ.php');
//     exit;
// }

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

<div id="first-row">
    <h2>Dashboard</h2>
</div>

<!-- Row 1: Total Sales + Top Products -->
<div class="dashboard-row">
    <div class="dashboard-box sales-box">
        <div class="sales-header">
            <h3>Total Sales</h3>
            <form method="GET" id="salesFilterForm">
                <select id="sales-period" name="period" onchange="document.getElementById('salesFilterForm').submit()">
                    <option value="today" <?= (!isset($_GET['period']) || $_GET['period'] === 'today') ? 'selected' : '' ?>>Today</option>
                    <option value="week" <?= (isset($_GET['period']) && $_GET['period'] === 'week') ? 'selected' : '' ?>>This Week</option>
                    <option value="month" <?= (isset($_GET['period']) && $_GET['period'] === 'month') ? 'selected' : '' ?>>This Month</option>
                    <option value="year" <?= (isset($_GET['period']) && $_GET['period'] === 'year') ? 'selected' : '' ?>>This Year</option>
                </select>
            </form>
        </div>

        <?php
        $period = $_GET['period'] ?? 'today';
        switch ($period) {
            case 'week': $sales = $appData->getSalesThisWeek(); break;
            case 'month': $sales = $appData->getSalesThisMonth(); break;
            case 'year': $sales = $appData->getSalesThisYear(); break;
            default: $sales = $appData->getSalesToday(); break;
        }
        ?>

        <h1 style="font-size:40px; margin-top:10px;" id="sales">
            ₱<?= number_format($sales, 2); ?>
        </h1>
    </div>

    <div class="dashboard-box top-products-box">
        <h3>Top 3 Selling Products</h3>
        <?php if ($topProducts && count($topProducts) > 0): ?>
            <ol style="margin-top:10px; font-size:18px;">
                <?php foreach ($topProducts as $product): ?>
                    <li><?= htmlspecialchars($product['product_name']) . " - " . htmlspecialchars($product['total_sold']) ?></li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p>No products sold</p>
        <?php endif; ?>
    </div>
</div>

<!-- Row 2: Order counts + Active Admins/Drivers -->
<div class="dashboard-row">
    <div class="dashboard-box pending-box"><p>Pending</p><h3 id="pending-count"><?= $orderCounts['pending'] ?></h3></div>
    <div class="dashboard-box preparing-box"><p>Preparing</p><h3 id="preparing-count"><?= $orderCounts['preparing'] ?></h3></div>
    <div class="dashboard-box ready-box"><p>Ready to Deliver</p><h3 id="ready-count"><?= $orderCounts['ready_for_delivery'] ?></h3></div>
    <div class="dashboard-box delivered-box"><p>Delivered</p><h3 id="delivered-count"><?= $orderCounts['delivered'] ?></h3></div>
    <div class="dashboard-box cancelled-box"><p>Cancelled</p><h3 id="cancelled-count"><?= $orderCounts['cancelled'] ?></h3></div>
    <div class="dashboard-box admin-box"><p>Active Admins</p><h3><?= $totalActiveAdmin?></h3></div>
    <div class="dashboard-box driver-box"><p>Active Drivers</p><h3><?= $totalActiveDriver?></h3></div>
</div>

<!-- Row 3: Recent Orders -->
<div id="third-row">
    <div id="top">
        <p><strong>Recent Orders</strong></p>
        <div class="sort-dropdown">
            <label for="sort">Sort by:</label>
           <div id="orderFilterControls" style="margin-bottom:8px;">
  <button id="btnActiveOrders" class="btn-tab active">Active Orders</button>
  <button id="btnCompletedOrders" class="btn-tab">Completed Orders</button>
</div>

<select id="sort">
  <option value="order_date">Order Date</option>
  <option value="status">Status</option>
  <option value="total">Total</option>
  <!-- this special option switches view to completed orders -->
  <option value="completed">Completed Orders</option>
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
        <div id="table-body"></div>
    </div>
</div>

<!-- Row 4: Inbox -->
<div id="table-container">
    <p><strong>Recent Messages</strong></p><br>
    <table class="staff-table">
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
                <tr><td colspan="6" style="text-align:center;">No messages found</td></tr>
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

<!-- External JS -->
<script>
    const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/Leilife/", "/") ?>/";
</script>
<script src="/Leilife/Scripts/admin/components/dashboard.js"></script>
