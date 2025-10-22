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

$delivery = ($orderInfo['delivery_method'] === "home") || ($orderInfo['status'] === 'delivered');
$review = $appData->getReviewMessage($orderInfo['order_number']);
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
        <?php if ($orderInfo['status'] !== "cancelled"): ?>
            <div class="progress-container">
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

        <?php endif; ?>

        <div class="order_details">
            <!-- LEFT -->
            <div class="left-details">
                <?php if ($orderInfo['status'] === "cancelled"): ?>
                    <p><strong>Your order has been cancelled.</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
                <?php elseif ($orderInfo['status'] === "delivered"): ?>
                    <p><strong>Thanks for ordering!</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/success-order.png" alt="Cancelled">
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
                        <p>
                            <?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?>
                            <?php if (!empty($item['size'])): ?>
                                <br>
                                Size: <?= htmlspecialchars(ucfirst($item['size'])) ?>
                            <?php endif; ?>
                            <?php if (!empty($item['flavors'])): ?>
                                <br>
                                Flavors: <?= htmlspecialchars(implode(", ", $item['flavors'])) ?>
                            <?php endif; ?>
                            — ₱<?= number_format($item['price'], 2) ?>
                        </p>
                    <?php endforeach; ?>
                    <hr>
                    <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
                </div>


                <!-- CANCELLED or SUCCESSFUL -->
                <?php if ($orderInfo['status'] === 'cancelled'): ?>
                    <div id="reorder-btns">
                        <form id="reorderForm" action="../backend/reorder.php" method="POST">
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

                <?php if ($review): ?>
                    <p style="color:#8f8d8dff;">Order Review</p>
                    <div class="right-content">

                        <p><strong>Feedback:</strong> <?= $review ?></p>
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
        <?php if ($orderInfo['status'] !== "cancelled"): ?>
            <div class="progress-container">
                <div class="step <?= $activeStep >= 1 ? 'active' : '' ?>">
                    <div class="circle">1</div>
                    <div class="label">Pending...</div>
                </div>
                <div class="step <?= $activeStep >= 2 ? 'active' : '' ?>">
                    <div class="circle">2</div>
                    <div class="label">Preparing...</div>
                </div>
                <div class="step <?= $activeStep >= 3 ? 'active' : '' ?>">
                    <div class="circle">3</div>
                    <div class="label">Picked up</div>
                </div>
            </div>
        <?php endif; ?>

        <div class="order_details">
            <!-- LEFT -->
            <div class="left-details">
                <?php if ($orderInfo['status'] === "cancelled"): ?>
                    <p><strong>Your order has been cancelled.</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/cancel-order.png" alt="Cancelled">
                <?php elseif ($orderInfo['status'] === "picked_up"): ?>
                    <p><strong>Thanks for ordering!</strong></p>
                    <img id="cancel-order-pic" src="/Leilife/public/assests/success-order.png" alt="Cancelled">
                <?php elseif ($orderInfo['payment_status'] === "unpaid"): ?>
                    <p>Time remaining to pick up your order:</p>
                    <p>
                        <strong>
                            <span
                                id="pickup-timer"
                                data-order-number="<?= htmlspecialchars($orderInfo['order_number']) ?>"
                                data-order-date="<?= htmlspecialchars($orderInfo['order_date'] ?? date('Y-m-d H:i:s')) ?>">
                                00:10
                            </span>


                        </strong>
                    </p>
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
                        <p>
                            <?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?>
                            <?php if (!empty($item['size'])): ?>
                                <br>
                                Size: <?= htmlspecialchars(ucfirst($item['size'])) ?>
                            <?php endif; ?>
                            <?php if (!empty($item['flavors'])): ?>
                                <br>
                                Flavors: <?= htmlspecialchars(implode(", ", $item['flavors'])) ?>
                            <?php endif; ?>
                            — ₱<?= number_format($item['price'], 2) ?>
                        </p>
                    <?php endforeach; ?>
                    <hr>
                    <p><strong>Total:</strong> ₱<?= number_format($orderInfo['total'], 2) ?></p>
                </div>

                <!-- CANCELLED or SUCCESSFUL -->
                <?php if ($orderInfo['status'] === 'cancelled'): ?>
                    <div id="reorder-btns">
                        <form id="reorderForm" action="../backend/reorder.php" method="POST">
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
                <?php if ($review): ?>
                    <p style="color:#8f8d8dff;">Order Review</p>
                    <div class="right-content">

                        <p><strong>Feedback:</strong> <?= $review ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- REVIEW SECTION -->
    <?php if (!$review): ?>
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
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
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



        const timerEl = document.getElementById('pickup-timer');
        if (timerEl) {
            const orderNumber = timerEl.dataset.orderNumber;
            const orderDate = timerEl.dataset.orderDate;
            const orderTimestamp = new Date(orderDate.replace(" ", "T")).getTime();

            // PHP server time sync
            const serverNow = <?= round(microtime(true) * 1000) ?>; // milliseconds
            const clientNow = Date.now();
            const offset = serverNow - clientNow; // difference between server and client

            // Auto-cancel duration (example: 10 minutes)
            const AUTO_CANCEL_DURATION = 5 * 1000; // 10 mins in ms
            const endTime = orderTimestamp + AUTO_CANCEL_DURATION;

            const updateTimer = async () => {
                const now = Date.now() + offset;
                const timeLeft = Math.floor((endTime - now) / 1000);

                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerEl.textContent = "00:00";

                    // 🔄 Auto-cancel when time expires
                    try {
                        const res = await fetch("/leilife/backend/auto_cancel_order.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "order_number=" + encodeURIComponent(orderNumber),
                        });
                        const data = await res.json();

                        if (data.success) {
                            showModal("Order automatically cancelled after timeout.", "warning", true, 3000);
                            setTimeout(() => location.reload(), 2500);
                        } else {
                            showModal(data.message || "Failed to auto-cancel order", "error", true, 4000);
                        }
                    } catch (err) {
                        console.error("Auto-cancel error:", err);
                        showModal("Network error during auto-cancel.", "error", true, 4000);
                    }
                    return;
                }

                // Format time as MM:SS
                const m = Math.floor(timeLeft / 60);
                const s = timeLeft % 60;
                timerEl.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
            };

            updateTimer(); // run immediately
            const countdown = setInterval(updateTimer, 1000);
        }





        // ✅ Review form
        const reviewForm = document.getElementById("reviewForm");
        const submitBtn = document.getElementById("submitReviewBtn");
        if (reviewForm && submitBtn) {
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

    document.addEventListener("DOMContentLoaded", () => {
        const reorderForm = document.getElementById("reorderForm");
        const reorderBtn = document.getElementById("reorderBtn");

        if (reorderForm && reorderBtn) {
            reorderBtn.addEventListener("click", async (e) => {
                e.preventDefault();

                const confirmed = await showConfirm(
                    "Reordering will remove all current items in your cart. Do you want to continue?"
                );

                if (!confirmed) return;

                const formData = new FormData(reorderForm);
                const orderId = formData.get("order_id");

                showModal("Processing reorder...", "warning", false);
                reorderBtn.disabled = true; // prevent double-clicks

                try {
                    const res = await fetch("../backend/reorder.php", {
                        method: "POST",
                        body: formData,
                    });

                    const text = await res.text();
                    console.log("Raw response:", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch {
                        throw new Error("Invalid JSON: " + text);
                    }

                    if (data.success) {
                        showModal(data.message || "Order reordered successfully!", "success", true, 2000);

                        // ✅ Use redirect path from PHP if provided
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 1500);
                        }
                    } else {
                        showModal(data.message || "Failed to reorder", "error", true, 4000);
                    }
                } catch (err) {
                    console.error("Reorder error:", err);
                    showModal("Network error while reordering.", "error", true, 4000);
                } finally {
                    reorderBtn.disabled = false;
                }
            });
        }
    });




    function showConfirm(message) {
        return new Promise((resolve) => {
            let modal = document.getElementById("confirm-modal");
            if (!modal) {
                modal = document.createElement("div");
                modal.id = "confirm-modal";
                modal.style.cssText = `
                display:none; position:fixed; z-index:10000; left:0; top:0;
                width:100%; height:100%; background:rgba(0,0,0,0.4);
                justify-content:center; align-items:center;
            `;
                modal.innerHTML = `
                <div class="confirm-content" style="
                    background:white; padding:20px 30px; border-radius:10px;
                    text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
                    min-width:280px; animation:popin .3s ease;
                ">
                    <p id="confirm-message" style="margin-bottom:20px; font-size:16px;"></p>
                    <div style="display:flex; gap:15px; justify-content:center;">
                        <button id="confirm-yes" style="
                            padding:6px 16px; border:none; border-radius:6px;
                            cursor:pointer; font-size:14px; color:white; background:#4caf50;
                        ">Yes</button>
                        <button id="confirm-no" style="
                            padding:6px 16px; border:none; border-radius:6px;
                            cursor:pointer; font-size:14px; color:white; background:#f44336;
                        ">No</button>
                    </div>
                </div>
            `;
                document.body.appendChild(modal);
            }

            document.getElementById("confirm-message").textContent = message;
            const yesBtn = document.getElementById("confirm-yes");
            const noBtn = document.getElementById("confirm-no");

            modal.style.display = "flex";

            const closeModal = () => {
                modal.style.display = "none";
            };

            yesBtn.onclick = () => {
                closeModal();
                resolve(true);
            };
            noBtn.onclick = () => {
                closeModal();
                resolve(false);
            };
            modal.onclick = (e) => {
                if (e.target === modal) {
                    closeModal();
                    resolve(false);
                }
            };
        });
    }
</script>