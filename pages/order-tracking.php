<?php
require_once "../components/buttonTemplate.php";
require_once __DIR__ . '/../backend/db_script/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

$user_id = $_SESSION['user_id'] ?? null;
$userAddress = $appData->loadUserAddress($user_id);

// get the latest order with items
$order = $appData->getOrderOfUser($user_id);
?>

<div class="tracking">
    <div class="your_order_title">
        <h3>Your Order</h3>
    </div>

    <!-- Progress steps -->
    <div class="progress-container">
        <?php
        // figure out which step is active based on order status
        $steps = [
            'pending'            => 1,
            'preparing'          => 2,
            'ready_for_delivery' => 3,
            'delivered'          => 4,
        ];
        $activeStep = isset($order[0]['status']) ? ($steps[$order[0]['status']] ?? 1) : 1;
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

    <!-- Order details -->
    <div class="order_details">
        <!-- Left -->
        <div class="left-details">
            <p style="color: #8f8d8dff;">Estimated time of delivery</p>
            <p><strong>15 - 20 mins</strong></p>
            <img id="motor" src="\Leilife\public\assests\emojione_motorcycle.png" alt="Logo">
        </div>

        <!-- Right -->
        <div class="right-details">
            <!-- Section title -->
            <p style="color: #8f8d8dff;">Delivery details</p>

            <!-- Section content (left-aligned) -->
            <div class="right-content">
                <div class="info-row">
                    <img src="../public/assests/pin.png" alt="location">
                    <?php if ($userAddress): ?>
                        <p style="margin:0;">
                            <?= htmlspecialchars($userAddress["street_address"] ?? '') ?>, 
                            <?= htmlspecialchars($userAddress["barangay"] ?? '') ?>, 
                            <?= htmlspecialchars($userAddress["city"] ?? '') ?>
                        </p>
                    <?php else: ?>
                        <p style="margin:0;">No address set.</p>
                    <?php endif; ?>
                </div>

                <div class="info-row">
                    <img src="../public/assests/credit-card.png" alt="cc">
                    <p style="margin:0;">
                        <?= isset($order[0]['payment_method']) 
                            ? ucfirst($order[0]['payment_method']) 
                            : "N/A" ?>
                    </p>
                </div>
            </div>

            <!-- Order details title -->
            <p style="color: #8f8d8dff;">Order details</p>

            <!-- Products (left-aligned) -->
            <div class="right-content">
                <?php if ($order && count($order) > 0): ?>
                    <?php foreach ($order as $item): ?>
                        <p style="margin:0;">
                            <?= (int)$item['quantity'] ?> × <?= htmlspecialchars($item['product_name']) ?>
                            — ₱<?= number_format($item['price'], 2) ?>
                        </p>
                    <?php endforeach; ?>
                    <hr>
                    <p><strong>Total:</strong> ₱<?= number_format($order[0]['total'], 2) ?></p>
                <?php else: ?>
                    <p>No active orders.</p>
                <?php endif; ?>
            </div>

            <!-- Cancel Order button -->
            <?php if ($order && count($order) > 0 && $order[0]['status'] !== 'delivered' && $order[0]['status'] !== 'cancelled'): ?>
                <div class="submit">
                    <?php echo createButton(45, 150, "Cancel Order"); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
