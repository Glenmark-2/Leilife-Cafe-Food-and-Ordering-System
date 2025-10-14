<?php
require_once "../components/buttonTemplate.php";
require_once __DIR__ . '/../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';
include "../components/modal.php";

if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

// Ensure user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>You must be logged in to view orders.</p>";
    exit;
}

// Fetch order number from query
$orderNumber = $_GET['num'] ?? null;
if (!$orderNumber) {
    echo "<p>No order selected.</p>";
    exit;
}

// Fetch the order for this user
$order = $appData->getOrderByNumber($user_id, $orderNumber);
if (!$order || count($order) === 0) {
    echo "<p>Order not found or access denied.</p>";
    exit;
}

$orderInfo = $order[0];

// Fetch user address
$userAddress = $appData->loadUserAddress($user_id);

// Check if delivery
$delivery = isset($orderInfo['delivery_option']) && $orderInfo['delivery_option'] === "home";
?>
<?= createModal(); ?>

<div class="tracking">
    <div class="your_order_title">
        <h3>Your Order #<?= htmlspecialchars($orderInfo['order_number']) ?></h3>
    </div>

    <!-- Progress Steps -->
    <?php if ($delivery): ?>
        <div class="progress-container">
            <?php
            $steps = [
                'pending'            => 1,
                'preparing'          => 2,
                'ready_for_delivery' => 3,
                'delivered'          => 4,
            ];
            $activeStep = $steps[$orderInfo['status']] ?? 1;
            ?>
            <div class="step <?= $activeStep >= 1 ? 'active' : '' ?>">
                <div class="circle">1</div>
                <div class="label">Queuing...</div>
            </div>
            <div class="step <?= $activeStep >= 2 ? 'active' : '' ?>">
                <div class="circle">2</div>
                <div class="label">Preparing...</div>
            </div>
            <div class="step <?= $activeStep >= 3 ? 'active' : '' ?>">
                <div class="circle">3</div>
                <div class="label">Out for delivery...</div>
            </div>
            <div class="step <?= $activeStep >= 4 ? 'active' : '' ?>">
                <div class="circle">4</div>
                <div class="label">Delivered</div>
            </div>
        </div>
    <?php else: ?>
        <div class="progress-container">
            <?php
            $pickupSteps = [
                'pending'   => 1,
                'preparing' => 2,
                'picked_up' => 3,
            ];
            $activePickupStep = $pickupSteps[$orderInfo['status']] ?? 1;
            ?>
            <div class="step <?= $activePickupStep >= 1 ? 'active' : '' ?>">
                <div class="circle">1</div>
                <div class="label">Pending...</div>
            </div>
            <div class="step <?= $activePickupStep >= 2 ? 'active' : '' ?>">
                <div class="circle">2</div>
                <div class="label">Preparing...</div>
            </div>
            <div class="step <?= $activePickupStep >= 3 ? 'active' : '' ?>">
                <div class="circle">3</div>
                <div class="label">Picked up</div>
            </div>
        </div>

        <!-- Pickup messages -->
        <?php
        if ($orderInfo['status'] !== "cancelled" && $orderInfo['payment_status'] === "unpaid") {
            ?>
            <p>Time remaining to pick up your order:</p>
            <p><strong><span id="pickup-timer" data-order-number="<?= $orderInfo['order_number'] ?>">00:10</span></strong></p>
            <img id="motor" src="/Leilife/public/assests/walk.png" alt="Logo">
            <?php
        } elseif ($orderInfo['status'] !== "cancelled" && $orderInfo['payment_status'] === "paid") {
            ?>
            <p>Go to store now!</p>
            <img id="motor" src="/Leilife/public/assests/walk.png" alt="Logo">
            <?php
        } elseif ($orderInfo['status'] === "cancelled") {
            ?>
            <p id="cancelled-order-msg"><strong>Your order has been cancelled.</strong></p>
            <img id="cancel-order-pic" src="/Leilife/public/assests/cancel-order.png" alt="Logo">
            <?php
        }
        ?>
    <?php endif; ?>

    <!-- Order Details -->
    <div class="order_details">
        <div class="left-details">
            <?php if ($delivery): ?>
                <p style="color: #8f8d8dff;">Estimated time of delivery</p>
                <p><strong>15 - 20 mins</strong></p>
                <img id="motor" src="/Leilife/public/assests/emojione_motorcycle.png" alt="Logo">
            <?php endif; ?>
        </div>

        <div class="right-details">
            <?php if ($delivery): ?>
                <p style="color: #8f8d8dff;">Delivery details</p>
                <div class="right-content">
                    <div class="info-row">
                        <img src="../public/assests/pin.png" alt="location">
                        <p style="margin:0;">
                            <?= htmlspecialchars($userAddress["street_address"] ?? '') ?>,
                            <?= htmlspecialchars($userAddress["barangay"] ?? '') ?>,
                            <?= htmlspecialchars($userAddress["city"] ?? '') ?>
                        </p>
                    </div>
                    <div class="info-row">
                        <img src="../public/assests/credit-card.png" alt="cc">
                        <p style="margin:0;"><?= ucfirst($orderInfo['payment_method'] ?? "N/A") ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <p style="color: #8f8d8dff;">Order details</p>
            <div class="right-content">
                <?php foreach ($order as $item): ?>
                    <p style="margin:0;">
                        <?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?>
                        — ₱<?= number_format($item['price'], 2) ?>
                    </p>
                <?php endforeach; ?>
                <hr>
                <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
            </div>

            <?php if ($orderInfo['status'] !== 'delivered' && $orderInfo['status'] !== 'cancelled'): ?>
                <div class="submit">
                    <?php
                    $attrs = [];
                    if ($orderInfo['status'] === 'preparing') {
                        $attrs['disabled'] = 'disabled';
                        $attrs['title'] = 'Cannot cancel while preparing';
                    }
                    echo createButton(45, 150, "Cancel Order", "cancelOrderBtn", 16, "button", $attrs);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Section -->
    <?php if ($orderInfo['status'] === 'delivered'): ?>
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
                    <?= createButton(40, 120, "Submit Review", "submitReviewBtn", 14, "submit") ?>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<script>
const reviewForm = document.getElementById("reviewForm");
const submitBtn = document.getElementById("submitReviewBtn");

if(submitBtn) {
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const formData = new FormData(reviewForm);

        fetch("/leilife/backend/mail.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.text())
        .then(text => {
            console.log("Mail response:", text);
            return JSON.parse(text);
        })
        .then(data => {
            if (data.success) {
                showModal(data.message, "success");
                setTimeout(() => {
                    window.location.href = "/leilife/public/index.php?page=home";
                }, 2000);
            } else {
                showModal(data.message || "Your review did not send!", "error");
            }
        })
        .catch(err => {
            console.error("Fetch error:", err);
            showModal("Network error. Please try again.", "error");
        });
    });
}

const cancelBtn = document.getElementById("cancelOrderBtn");
if(cancelBtn) {
    cancelBtn.addEventListener("click", function() {
        const orderNumber = "<?= $orderInfo['order_number'] ?>";
        fetch("/leilife/backend/cancel_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "order_number=" + encodeURIComponent(orderNumber)
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.success) window.location.reload(); 
        })
        .catch(err => console.error(err));
    });
}
</script>
