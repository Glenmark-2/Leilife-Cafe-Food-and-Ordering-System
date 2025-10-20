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

// ✅ Fetch order number from router (?num=ORD-...)
$orderNumber = $_GET['num'] ?? null;
if (!$orderNumber) {
    echo "<p>No order selected.</p>";
    exit;
}

// ✅ Fetch the order with items, making sure it belongs to this user
$order = $appData->getOrderByNumber($user_id, $orderNumber);

if (!$order || count($order) === 0) {
    echo "<p>Order not found or access denied.</p>";
    exit;
}


// First row contains general order info
$orderInfo = $order[0];

// ✅ Fetch user address
$userAddress = $appData->loadUserAddress($user_id);

$delivery = isset($orderInfo['delivery_method']) && $orderInfo['delivery_method'] === "home";



?>
<?= createModal(); ?>
<div class="tracking">
    <div class="your_order_title">
        <h3>Your Order #<?= htmlspecialchars($orderInfo['order_number']) ?></h3>
    </div>

    <?php if ($delivery): ?>
        <!-- DELIVERY SECTION -->
        <?php
        $steps = [
            'pending'            => 1,
            'preparing'          => 2,
            'ready_for_delivery' => 3,
            'delivered'          => 4,
        ];
        $activeStep = $steps[$orderInfo['status']] ?? 1;
        ?>
        <!-- Progress -->
        <div class="progress-container">
            <div class="step <?= $activeStep >= 1 ? 'active' : '' ?>"><div class="circle">1</div><div class="label">Queuing...</div></div>
            <div class="step <?= $activeStep >= 2 ? 'active' : '' ?>"><div class="circle">2</div><div class="label">Preparing...</div></div>
            <div class="step <?= $activeStep >= 3 ? 'active' : '' ?>"><div class="circle">3</div><div class="label">Out for delivery...</div></div>
            <div class="step <?= $activeStep >= 4 ? 'active' : '' ?>"><div class="circle">4</div><div class="label">Delivered</div></div>
        </div>

        <div class="order_details">
            <!-- LEFT -->
            <div class="left-details">
                <?php if ($orderInfo['status'] === "cancelled"): ?>
                    <p><strong>Your order has been cancelled.</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
                <?php else: ?>
                    <p style="color:#8f8d8dff;">Estimated time of delivery</p>
                    <p><strong>15 - 20 mins</strong></p>
                    <img id="motor" src="/Leilife/public/assests/emojione_motorcycle.png" alt="Delivery">
                <?php endif; ?>
            </div>

            <!-- RIGHT -->
            <div class="right-details">
                <p style="color:#8f8d8dff;">Delivery details</p>
                <div class="right-content">
                    <div class="info-row">
                        <img src="../public/assests/pin.png" alt="location">
                        <p>
                            <?= htmlspecialchars($userAddress["street_address"] ?? 'No address') ?>,
                            <?= htmlspecialchars($userAddress["barangay"] ?? '') ?>,
                            <?= htmlspecialchars($userAddress["city"] ?? '') ?>
                        </p>
                    </div>
                    <div class="info-row">
                        <img src="../public/assests/credit-card.png" alt="payment">
                        <p><?= ucfirst($orderInfo['payment_method'] ?? 'N/A') ?></p>
                    </div>
                </div>

                <p style="color:#8f8d8dff;">Order details</p>
                <div class="right-content">
                    <?php foreach ($order as $item): ?>
                        <p><?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?> — ₱<?= number_format($item['price'], 2) ?></p>
                    <?php endforeach; ?>
                    <hr>
                    <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
                </div>

                <!-- CANCELLED or SUCCESSFUL -->
                <?php if ($orderInfo['status'] === 'cancelled'): ?>
                    <div id="reorder-btns">
                        <form action="../backend/reorder.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderInfo['order_id']) ?>">
                            <?= createButton(45, 150, "Reorder", "reorderBtn", 16, "submit"); ?>
                        </form>
                        <a href="/leilife/public/index.php?page=menu">
                            <?= createButton(45, 150, "Go to menu"); ?>
                        </a>
                    </div>
                <?php elseif ($orderInfo['status'] !== 'delivered'): ?>
                    <div class="submit">
                        <?php
                        $attrs = [];
                        if ($orderInfo['status'] !== 'pending') {
                            $attrs['disabled'] = 'disabled';
                            $attrs['title'] = 'Cannot cancel while preparing';
                        }
                        echo createButton(45, 150, "Cancel Order", "cancelOrderBtn", 16, "button", $attrs);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- PICKUP SECTION -->
        <?php
        $steps = [
            'pending'   => 1,
            'preparing' => 2,
            'picked_up' => 3,
        ];
        $activeStep = $steps[$orderInfo['status']] ?? 1;
        ?>
        <!-- Progress -->
        <div class="progress-container">
            <div class="step <?= $activeStep >= 1 ? 'active' : '' ?>"><div class="circle">1</div><div class="label">Pending...</div></div>
            <div class="step <?= $activeStep >= 2 ? 'active' : '' ?>"><div class="circle">2</div><div class="label">Preparing...</div></div>
            <div class="step <?= $activeStep >= 3 ? 'active' : '' ?>"><div class="circle">3</div><div class="label">Picked up</div></div>
        </div>

        <div class="order_details">
            <!-- LEFT -->
            <div class="left-details">
                <?php if ($orderInfo['status'] === "cancelled"): ?>
                    <p><strong>Your order has been cancelled.</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
                <?php elseif ($orderInfo['payment_status'] === "unpaid"): ?>
                    <p>Time remaining to pick up your order:</p>
                    <p><strong><span id="pickup-timer" data-order-number="<?= $orderInfo['order_number'] ?>">00:10</span></strong></p>
                    <img id="motor" src="/Leilife/public/assests/walk.png" alt="Walk">
                <?php elseif ($orderInfo['payment_status'] === "paid"): ?>
                    <p>Go to store now!</p>
                    <img id="motor" src="/Leilife/public/assests/walk.png" alt="Walk">
                <?php endif; ?>
            </div>

            <!-- RIGHT -->
            <div class="right-details">
                <p style="color:#8f8d8dff;">Pickup details</p>
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

                <p style="color:#8f8d8dff;">Order details</p>
                <div class="right-content">
                    <?php foreach ($order as $item): ?>
                        <p><?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?> — ₱<?= number_format($item['price'], 2) ?></p>
                    <?php endforeach; ?>
                    <hr>
                    <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
                </div>

                <!-- CANCELLED or SUCCESSFUL -->
                <?php if ($orderInfo['status'] === 'cancelled'): ?>
                    <div id="reorder-btns">
                        <form action="../backend/reorder.php" method="POST">
                            <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderInfo['order_id']) ?>">
                            <?= createButton(45, 150, "Reorder", "reorderBtn", 16, "submit"); ?>
                        </form>
                        <a href="/leilife/public/index.php?page=menu">
                            <?= createButton(45, 150, "Go to menu"); ?>
                        </a>
                    </div>
                <?php elseif ($orderInfo['status'] !== 'picked_up'): ?>
                    <div class="submit">
                        <?php
                        $attrs = [];
                        if ($orderInfo['status'] !== 'pending') {
                            $attrs['disabled'] = 'disabled';
                            $attrs['title'] = 'Cannot cancel while preparing';
                        }
                        echo createButton(45, 150, "Cancel Order", "cancelOrderBtn", 16, "button", $attrs);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- REVIEW SECTION -->
    <?php if (in_array($orderInfo['status'], ['delivered', 'picked_up'])): ?>
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
    const cancelBtn = document.getElementById("cancelOrderBtn");
    if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const orderNumber = "<?= $orderInfo['order_number'] ?>";

            showConfirmModal("Are you sure you want to cancel this order?", async () => {
                showModal("Cancelling your order...", "warning", false);
                try {
                    const res = await fetch("/leilife/backend/auto_cancel_order.php", {
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
                        console.warn("Debug:", data.debug);
                    }
                } catch (err) {
                    console.error(err);
                    showModal("Network error. Please try again.", "error", true, 4000);
                }
            });
        });
    }

    // ✅ Timer
    const timerEl = document.getElementById('pickup-timer');
    if (timerEl) {
        const PICKUP_DURATION = 10;
        const STORAGE_KEY = "pickupTimer_<?= $orderInfo['order_number'] ?>";
        const orderNumber = timerEl.dataset.orderNumber;
        const endTime = Date.now() + PICKUP_DURATION * 1000;
        localStorage.setItem(STORAGE_KEY, endTime);

        const countdown = setInterval(() => {
            const timeLeft = Math.floor((endTime - Date.now()) / 1000);
            if (timeLeft <= 0) {
                clearInterval(countdown);
                localStorage.removeItem(STORAGE_KEY);
                fetch('/leilife/backend/auto_cancel_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `order_number=${encodeURIComponent(orderNumber)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        timerEl.textContent = "Order Cancelled";
                        showModal("Order cancelled automatically", "info");
                        setTimeout(() => location.reload(), 2000);
                    } else console.error('❌ Cancel failed:', data);
                })
                .catch(err => console.error('⚠️ AJAX error:', err));
                return;
            }
            const m = Math.floor(timeLeft / 60);
            const s = timeLeft % 60;
            timerEl.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }, 1000);
    }

    // ✅ Review form
    const reviewForm = document.getElementById("reviewForm");
    const submitBtn = document.getElementById("submitReviewBtn");
    if (reviewForm && submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const formData = new FormData(reviewForm);
            fetch("/leilife/backend/mail.php", { method: "POST", body: formData })
                .then(res => res.text())
                .then(text => {
                    console.log("Mail response:", text);
                    return JSON.parse(text);
                })
                .then(data => {
                    if (data.success) {
                        showModal(data.message, "success");
                        setTimeout(() => window.location.href = "/leilife/public/index.php?page=home", 2000);
                    } else showModal(data.message || "Your review did not send!", "error");
                })
                .catch(err => {
                    console.error("Fetch error:", err);
                    showModal("Network error. Please try again.", "error");
                });
        });
    }
});

// ✅ Confirmation modal built on top of showModal()
function showConfirmModal(message, onConfirm) {
    let modal = document.getElementById("notif-modal");
    if (!modal) {
        showModal();
        modal = document.getElementById("notif-modal");
        modal.style.display = "none";
    }

    const content = modal.querySelector(".notif-content");
    const originalHTML = content.innerHTML;

    content.innerHTML = `
        <p>${message}</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button id="confirm-yes" class="success">Yes</button>
            <button id="confirm-no" class="error">Cancel</button>
        </div>
    `;

    modal.style.display = "flex";
    document.getElementById("confirm-yes").onclick = () => {
        modal.style.display = "none";
        content.innerHTML = originalHTML;
        onConfirm();
    };
    document.getElementById("confirm-no").onclick = () => {
        modal.style.display = "none";
        content.innerHTML = originalHTML;
    };
}
</script>