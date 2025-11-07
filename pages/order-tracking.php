<?php
require_once "../components/buttonTemplate.php";
require_once __DIR__ . '/../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';
include "../components/modal.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

// ✅ Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>You must be logged in to view orders.</p>";
    exit;
}

// ✅ Get order number from query
$orderNumber = $_GET['num'] ?? null;
if (!$orderNumber) {
    echo "<p>No order selected.</p>";
    exit;
}

// ✅ Fetch order & ensure it belongs to user
$order = $appData->getOrderByNumber($user_id, $orderNumber);
if (!$order || count($order) === 0) {
    echo "<p>Order not found or access denied.</p>";
    exit;
}

$orderInfo = $order[0]; // first row general info
$userAddress = $appData->loadUserAddress($user_id);
$delivery = ($orderInfo['delivery_method'] === "home") || ($orderInfo['order_status'] === 'delivered');
$review = $appData->getReviewMessage($orderInfo['order_number']);
?>

<?= createModal(); ?>

<div class="tracking">
  <div class="your_order_title">
    <h3>Your Order #<?= htmlspecialchars($orderInfo['order_number']) ?></h3>
  </div>

  <?php if ($delivery): ?>
    <!-- ==================== DELIVERY SECTION ==================== -->
    <?php
    $steps = [
      'pending'            => 1,
      'preparing'          => 2,
      'ready_for_delivery' => 3,
      'delivered'          => 4,
    ];
    $activeStep = $steps[$orderInfo['order_status']] ?? 1;
    ?>

    <?php if ($orderInfo['order_status'] !== "cancelled"): ?>
      <div class="progress-container">
        <?php foreach (['Queuing...', 'Preparing...', 'Out for delivery...', 'Delivered'] as $i => $label): ?>
          <div class="step <?= $activeStep >= ($i + 1) ? 'active' : '' ?>">
            <div class="circle"><?= $i + 1 ?></div>
            <div class="label"><?= $label ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="order_details">
      <!-- LEFT -->
      <div class="left-details">
        <?php if ($orderInfo['order_status'] === "cancelled"): ?>
          <p><strong>Your order has been cancelled.</strong></p>
          <img src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
        <?php elseif ($orderInfo['order_status'] === "delivered"): ?>
          <p><strong>Thanks for ordering!</strong></p>
          <img src="/Leilife/public/assests/success-order.png" alt="Delivered">
        <?php else: ?>
          <p style="color:#8f8d8dff;">Estimated time of delivery</p>
          <p id="live-eta"><strong>Fetching ETA...</strong></p>
          <img id="motor" src="/Leilife/public/assests/emojione_motorcycle.png" alt="Delivery">
        <?php endif; ?>
      </div>

      <!-- RIGHT -->
      <div class="right-details">
        <p style="color:#8f8d8dff; margin:0">Delivery details</p>
        <div class="right-content">
          <div class="info-row">
            <img src="../public/assests/pin.png" alt="location">
            <p>
              <?= htmlspecialchars(ucwords($userAddress["street_address"]) ?? 'No address') ?>,
              Barangay <?= htmlspecialchars($userAddress["barangay"] ?? '') ?>,
              <?= htmlspecialchars(ucwords($userAddress["city_name"]) ?? '') ?>,
              <?= htmlspecialchars(ucwords($userAddress["province_name"]) ?? '') ?>,
              <?= htmlspecialchars(ucwords($userAddress["region_name"]) ?? '') ?>
            </p>
          </div>
          <div class="info-row">
            <img src="../public/assests/credit-card.png" alt="payment">
            <p><?= ucfirst($orderInfo['payment_method'] ?? 'N/A') ?></p>
          </div>
        </div>

        <p style="color:#8f8d8dff; margin:0;">Order details</p>
        <div class="right-content">
          <div class="order-items-list">
            <?php foreach ($order as $item): ?>
              <?php $isCancelled = strtolower($item['item_status'] ?? '') === 'cancelled'; ?>
              <div class="order-item <?= $isCancelled ? 'cancelled' : '' ?>">
                <p>
                  <?= (int)$item['quantity'] ?> × <?= htmlspecialchars(ucwords($item['product_name'])) ?>
                  <?php if (!empty($item['size'])): ?><br><small>Size: <?= htmlspecialchars(ucwords($item['size'])) ?></small><?php endif; ?>
                  <?php if (!empty($item['flavors'])): ?><br><small>Flavors: <?= htmlspecialchars(implode(", ", array_map('ucwords', $item['flavors']))) ?></small><?php endif; ?>
                  — ₱<?= number_format($item['price'], 2) ?>
                </p>
                <?php if ($isCancelled): ?><p class="cancelled-label">Cancelled item</p><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <hr>
          <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
        </div>

        <!-- Cancel / Reorder -->
        <?php if ($orderInfo['order_status'] === 'cancelled'): ?>
          <div id="reorder-btns">
            <form id="reorderForm" action="../backend/reorder.php" method="POST">
              <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderInfo['order_id']) ?>">
              <?= createButton(45, 150, "Reorder", "reorderBtn", 16, "submit"); ?>
            </form>
            <a href="/Leilife/public/index.php?page=menu">
              <?= createButton(45, 150, "Go to menu"); ?>
            </a>
          </div>
        <?php elseif ($orderInfo['order_status'] !== 'delivered'): ?>
          <div class="submit">
            <?php
            $attrs = [];
            if ($orderInfo['order_status'] !== 'pending') {
              $attrs['disabled'] = 'disabled';
              $attrs['title'] = 'Cannot cancel while preparing';
            }
            echo createButton(45, 150, "Cancel Order", "cancelOrderBtn", 16, "button", $attrs);
            ?>
          </div>
        <?php endif; ?>

        <?php if ($review): ?>
          <p style="color:#8f8d8dff; margin:0">Order Review</p>
          <div class="right-content">
            <p><strong>Feedback:</strong> <?= $review ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php else: ?>
    <!-- ==================== PICKUP SECTION ==================== -->
    <?php
    $steps = [
      'pending'   => 1,
      'preparing' => 2,
      'picked_up' => 3,
    ];
    $activeStep = $steps[$orderInfo['order_status']] ?? 1;
    ?>

    <?php if ($orderInfo['order_status'] !== "cancelled"): ?>
      <div class="progress-container">
        <?php foreach (['Pending...', 'Preparing...', 'Picked up'] as $i => $label): ?>
          <div class="step <?= $activeStep >= ($i + 1) ? 'active' : '' ?>">
            <div class="circle"><?= $i + 1 ?></div>
            <div class="label"><?= $label ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="order_details">
      <!-- LEFT -->
      <div class="left-details">
        <?php if ($orderInfo['order_status'] === "cancelled"): ?>
          <p><strong>Your order has been cancelled.</strong></p>
          <img src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
        <?php elseif ($orderInfo['order_status'] === "picked_up"): ?>
          <p><strong>Thanks for ordering!</strong></p>
          <img src="/Leilife/public/assests/success-order.png" alt="Picked up">
        <?php elseif ($orderInfo['payment_status'] === "unpaid"): ?>
          <p>Time remaining to pick up your order:</p>
          <p><strong>
            <span id="pickup-timer"
              data-order-number="<?= htmlspecialchars($orderInfo['order_number']) ?>"
              data-order-date="<?= htmlspecialchars(date('Y-m-d\TH:i:s', strtotime($orderInfo['order_date'] ?? date('Y-m-d H:i:s')))) ?>">
              00:10
            </span>
          </strong></p>
          <img id="motor" src="/Leilife/public/assests/walk.png" alt="Walk">
        <?php else: ?>
          <p>Go to store now!</p>
          <img id="motor" src="/Leilife/public/assests/walk.png" alt="Walk">
        <?php endif; ?>
      </div>

      <!-- RIGHT -->
      <div class="right-details">
        <p style="color:#8f8d8dff; margin:0">Pickup details</p>
        <div class="right-content">
          <div class="info-row">
            <img src="../public/assests/pin.png" alt="location">
            <p>Lunduyan Langaray, Brgy 14, Caloocan City</p>
          </div>
          <div class="info-row">
            <img src="../public/assests/phone.png" alt="phone">
            <p>09123456789</p>
          </div>
        </div>

        <p style="color:#8f8d8dff; margin:0">Order details</p>
        <div class="right-content">
          <?php foreach ($order as $item): ?>
            <?php $isCancelled = strtolower($item['item_status'] ?? '') === 'cancelled'; ?>
            <div class="order-item <?= $isCancelled ? 'cancelled' : '' ?>">
              <p>
                <?= (int)$item['quantity'] ?> × <?= htmlspecialchars(ucwords($item['product_name'])) ?>
                <?php if (!empty($item['size'])): ?><br>Size: <?= htmlspecialchars(ucwords($item['size'])) ?><?php endif; ?>
                <?php if (!empty($item['flavors'])): ?><br>Flavors: <?= htmlspecialchars(implode(", ", array_map('ucwords', $item['flavors']))) ?><?php endif; ?>
                — ₱<?= number_format($item['price'], 2) ?>
              </p>
              <?php if ($isCancelled): ?><p class="cancelled-label">Cancelled item</p><?php endif; ?>
            </div>
          <?php endforeach; ?>
          <hr>
          <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
        </div>

        <?php if ($orderInfo['order_status'] === 'cancelled'): ?>
          <div id="reorder-btns">
            <form id="reorderForm" action="../backend/reorder.php" method="POST">
              <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderInfo['order_id']) ?>">
              <?= createButton(45, 150, "Reorder", "reorderBtn", 16, "submit"); ?>
            </form>
            <a href="/Leilife/public/index.php?page=menu">
              <?= createButton(45, 150, "Go to menu"); ?>
            </a>
          </div>
        <?php elseif ($orderInfo['order_status'] !== 'picked_up'): ?>
          <div class="submit">
            <?php
            $attrs = [];
            if ($orderInfo['order_status'] !== 'pending') {
              $attrs['disabled'] = 'disabled';
              $attrs['title'] = 'Cannot cancel while preparing';
            }
            echo createButton(45, 150, "Cancel Order", "cancelOrderBtn", 16, "button", $attrs);
            ?>
          </div>
        <?php endif; ?>

        <?php if ($review): ?>
          <p style="color:#8f8d8dff; margin:0">Order Review</p>
          <div class="right-content">
            <p><strong>Feedback:</strong> <?= $review ?></p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- REVIEW SECTION -->
  <?php if (!$review && in_array($orderInfo['order_status'], ['delivered', 'picked_up'])): ?>
    <div class="review-section">
      <h3>Leave a Review</h3>
      <form id="reviewForm" method="POST">
        <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
        <input type="hidden" name="subject" value="Order Review #<?= htmlspecialchars($orderInfo['order_number']) ?>">
        <input type="hidden" name="type" value="feedback">
        <div class="comment">
          <label>Comment:</label><br>
          <textarea name="message" rows="4" placeholder="Write your review..." required></textarea>
        </div>
        <div class="submit">
          <?= createButton(40, 120, "Submit Review", "submitReviewBtn", 14, "submit"); ?>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

  /* ========== CANCEL ORDER ========== */
  const cancelBtn = document.getElementById("cancelOrderBtn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", async (e) => {
      e.preventDefault();
      const orderNumber = "<?= $orderInfo['order_number'] ?>";
      showConfirmModal("Are you sure you want to cancel this order?", async () => {
        showModal("Cancelling your order...", "warning", false);
        try {
          const res = await fetch("/Leilife/backend/auto_cancel_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "order_number=" + encodeURIComponent(orderNumber),
          });
          const data = await res.json();
          if (data.success) {
            showModal("Order cancelled successfully!", "success", true, 2500);
            setTimeout(() => location.reload(), 2000);
          } else {
            showModal(data.message || "Failed to cancel order", "error", true, 4000);
          }
        } catch {
          showModal("Network error. Please try again.", "error", true, 4000);
        }
      });
    });
  }

  /* ========== ETA FETCHER (for delivery) ========== */
  const etaEl = document.getElementById("live-eta");
  if (etaEl) {
    const PREP_TIME_MIN = 10;
    const storeLat = 14.6543, storeLng = 120.9721;
    const userLat = "<?= $userAddress['latitude'] ?? '' ?>";
    const userLng = "<?= $userAddress['longitude'] ?? '' ?>";

    if (!userLat || !userLng) {
      etaEl.textContent = "ETA unavailable";
      return;
    }

    async function fetchETA() {
      try {
        const res = await fetch(`/Leilife/backend/get_eta.php?from=${storeLng},${storeLat}&to=${userLng},${userLat}`);
        const data = await res.json();
        if (!data.success) {
          etaEl.innerHTML = "<strong>ETA unavailable</strong>";
          return;
        }
        const driverMin = Math.round((data.duration || 0) / 60);
        const minETA = PREP_TIME_MIN;
        const maxETA = Math.ceil((PREP_TIME_MIN + driverMin) / 5) * 5;
        etaEl.innerHTML = `<strong>${minETA}–${maxETA} mins</strong>`;
      } catch {
        etaEl.innerHTML = "<strong>ETA unavailable</strong>";
      }
    }
    fetchETA();
    setInterval(fetchETA, 30000);
  }

/* ========== PICKUP TIMER (FIXED) ========== */
const timerEl = document.getElementById("pickup-timer");
if (timerEl) {
  const orderNumber = timerEl.dataset.orderNumber;

  // ✅ We ignore MySQL's ambiguous timestamp and start countdown fresh on page load.
  const AUTO_CANCEL_DURATION = 30 * 1000; // 30 seconds
  const endTime = Date.now() + AUTO_CANCEL_DURATION;

  const updateTimer = async () => {
    const now = Date.now();
    const timeLeft = Math.floor((endTime - now) / 1000);

    if (timeLeft <= 0) {
      clearInterval(countdown);
      timerEl.textContent = "00:00";

      try {
        const res = await fetch("/Leilife/backend/auto_cancel_order.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: "order_number=" + encodeURIComponent(orderNumber),
        });
        const data = await res.json();
        if (data.success) {
          showModal("Order automatically cancelled after 30 seconds timeout.", "warning", true, 3000);
          setTimeout(() => location.reload(), 2500);
        } else {
          showModal(data.message || "Failed to auto-cancel order", "error", true, 4000);
        }
      } catch {
        showModal("Network error during auto-cancel.", "error", true, 4000);
      }
      return;
    }

    const m = Math.floor(timeLeft / 60);
    const s = timeLeft % 60;
    timerEl.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
  };

  updateTimer();
  const countdown = setInterval(updateTimer, 1000);
}


});
</script>

<script src="/Leilife/Scripts/pages/order-tracking.js" defer></script>
