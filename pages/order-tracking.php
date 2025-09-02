
<?php
require_once "../components/buttonTemplate.php";
?>

<div class="tracking">
    <div class="your_order_title">
        <h3>Your Order</h3>
    </div>

    <!-- Progress steps -->
    <div class="progress-container">
        <div class="step active">
            <div class="circle">1</div>
            <div class="label">Queuing...</div>
        </div>
        <div class="step">
            <div class="circle">2</div>
            <div class="label">Preparing...</div>
        </div>
        <div class="step">
            <div class="circle">3</div>
            <div class="label">Out for delivery...</div>
        </div>
        <div class="step">
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
                    <p style="margin:0;">10880 Malibu Point, Malibu, California, 90265</p>
                </div>

                <div class="info-row">
                    <img src="../public/assests/credit-card.png" alt="cc">
                    <p style="margin:0;">Cash on Delivery</p>
                </div>
            </div>

            <!-- Order details title -->
            <p style="color: #8f8d8dff;">Order details</p>

            <!-- Products (left-aligned) -->
            <div class="right-content">
                <p style="margin: 0;">1pc. Original Chicken and Rice</p>
                <p style="margin: 0;">1pc. Frappe na Masarap</p>
            </div>

            <!-- Cancel Order button -->
            <div class="submit">
                <?php echo createButton(45, 150, "Cancel Order"); ?>
            </div>

            <!-- Feedback box under Cancel Order -->
            <div class="feedback-box">
                <h4>Give a Feedback</h4>
                <textarea placeholder="Write your feedback here..."></textarea>
            </div>

            <!-- Submit Feedback button -->
            <div class="submit">
                <?php echo createButton(45, 150, "Submit"); ?>
            </div>
        </div>
    </div>
</div>
