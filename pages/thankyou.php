<?php
// pages/thankyou.php

$order_id = $_GET['order_id'] ?? null;
$payment_intent = $_GET['payment_intent_id'] ?? null;
?>

<div class="thankyou-container" style="text-align:center; padding:50px;">
  <h1>✅ Thank you for your order!</h1>
  <p>Your order has been placed successfully.</p>

  <?php if ($order_id): ?>
    <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
  <?php endif; ?>

  <?php if ($payment_intent): ?>
    <p><small>Payment Intent ID: <?= htmlspecialchars($payment_intent) ?></small></p>
  <?php endif; ?>

  <p>You will be redirected to the order tracking page shortly...</p>
  <a href="index.php?page=order-tracking&order_id=<?= urlencode($order_id) ?>" 
     class="btn" 
     style="display:inline-block;margin-top:20px;padding:10px 20px;background:#28a745;color:#fff;border-radius:5px;text-decoration:none;">
     Go to Order Tracking
  </a>
</div>

<script>
// Auto-redirect after 5 seconds
setTimeout(() => {
  window.location.href = "index.php?page=order-tracking&order_id=<?= urlencode($order_id) ?>";
}, 5000);
</script>
