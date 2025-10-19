<?php
require_once __DIR__ . '/../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// ensure $appData exists
if (!isset($appData)) {
    $appData = new AppData($pdo);
}





$user_id = $_SESSION['user_id'] ?? null;
$orders_raw = $user_id ? $appData->getActiveOrdersOfUser($user_id) : [];

// ✅ Group by order_id and exclude cancelled
$orders = [];
foreach ($orders_raw as $row) {
    if ($row['status'] === 'cancelled') {
        continue; // ❌ skip cancelled entirely
    }

    $oid = $row['order_id'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'order_id' => $row['order_id'],
            'order_number' => $row['order_number'],
            'order_date' => $row['order_date'],
            'status' => $row['status'],
            'payment_method' => $row['payment_method'],
            'total' => $row['total'],
            'items' => [],
        ];
    }

    // add item names (for preview, max 2)
    if (count($orders[$oid]['items']) < 2) {
        $orders[$oid]['items'][] = $row['product_name'];
    }
}

$orders = array_values($orders); // reset to numeric index
$orderCount = count($orders);

// Add formatted info
foreach ($orders as &$order) {
    $order['display_order_number'] = $order['order_number'];
    if ($order['items']) {
        $order['order_preview'] = $order['items'][0];
        if (count($order['items']) > 1) {
            $order['order_preview'] .= " + more";
        }
    } else {
        $order['order_preview'] = "(No items)";
    }
    $order['order_date_formatted'] = date("M j, Y", strtotime($order['order_date']));
}
unset($order);

$orders_json = json_encode($orders, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
if ($orders_json === false) $orders_json = '[]';
?>


<!-- Floating Button -->
<button id="order-btn" class="order-btn hidden">
  View Order
</button>

<!-- Orders Modal -->
<div id="orders-modal" class="orders-modal hidden" aria-hidden="true">
  <div class="orders-modal-content" role="dialog" aria-modal="true" aria-labelledby="orders-modal-title">
    <button id="close-modal" class="close" aria-label="Close">&times;</button>
    <h2 id="orders-modal-title">Your Active Orders</h2>
    <ul id="orders-list">
      <?php foreach ($orders as $order): ?>
        <li>
          <a class="order-link" 
             href="/Leilife/public/index.php?page=order-tracking&num=<?= urlencode($order['order_number']) ?>">
            <strong><?= htmlspecialchars($order['display_order_number']) ?></strong><br>
            <?= htmlspecialchars($order['order_preview']) ?><br>
            <small>Placed on <?= htmlspecialchars($order['order_date_formatted']) ?></small>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- ✅ Pass PHP data into JS config -->
<script>
  window.OrdersConfig = {
    orderCount: <?= $orderCount ?>,
    orders: <?= $orders_json ?>
  };
</script>
<!-- ✅ Load external JS -->
<script src="/Leilife/Scripts/components/order_button.js"></script>
