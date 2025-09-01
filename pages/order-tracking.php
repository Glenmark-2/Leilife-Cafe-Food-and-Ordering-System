<style>
/* Tracking container */
.tracking {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

.your_order_title {
    text-align: center;
    margin-bottom: 20px;
}

/* Progress steps */
.progress-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 40px;
}

.step {
    text-align: center;
    width: fit-content;
}

.circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #ddd;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #555;
}

.step.active .circle {
    background: #24343c;
    color: #fff;
    border-color: #24343c;
}

.label {
    margin-top: 8px;
    font-size: 14px;
    color: #333;
}

/* Order details container */
.order_details {
    display: grid;
    grid-template-columns: auto auto;
    justify-content: center;
    align-items: start;
    gap: 100px;
    width: 100%;
    max-width: 900px;
    margin: 20px auto 0 auto;
}

/* Left details */
.left-details {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 15px;
}

.left-details img {
    width: 300px;
    height: auto;
    display: block;
}

/* Right details */
.right-details {
    display: flex;
    flex-direction: column;
    align-items: center; /* centers titles & button */
    gap: 15px;
    width: 100%;
}

/* Section content (address, payment, product names) */
.right-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* left-aligned content */
    width: 100%;
    gap: 10px;
}

/* Delivery info and payment - horizontal alignment */
.info-row {
    display: flex;
    align-items: center; /* vertically center icon and text */
    justify-content: flex-start; /* left-aligned */
    gap: 10px; /* space between icon and text */
}

.info-row img {
    width: 30px;
    height: 30px;
}

/* Feedback box */
.feedback-box {
    width: 100%;
    max-width: 300px;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: left;
    box-sizing: border-box;
}

.feedback-box h4 {
    margin: 0 0 10px 0;
}

.feedback-box textarea {
    resize: none;
    width: 90%;
    height: 120px;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

/* Buttons */
.submit {
    display: flex;
    justify-content: center;
    margin: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .order_details {
        grid-template-columns: 1fr;
        gap: 20px;
        margin: 10px auto 0 auto;
    }

    .left-details img {
        width: 100%;
    }

    .right-details {
        width: 100%;
    }

    #motor{
        width: 30%;
    }

    .feedback-box{
        width: 100%;
    }

    .progress-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
}
}
</style>

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
