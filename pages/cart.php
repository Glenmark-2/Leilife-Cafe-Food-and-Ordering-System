<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cart = $_SESSION['cart'] ?? [];
?>

<div id="cart-box">
    <div class="inside-div" id="first-div">
        <div id="top-div">
            <img src="../public/assests/motorbike.png" alt="motor" id="motor">
            <p id="mode">Delivery</p>
            <?php
            include "../components/buttonTemplate.php";
            echo createButton(25, 60, "Change", "change", 10);
            ?>
        </div>
<!-- test -->
        <h3>My Carttttttttttttttttttttttttttttttttttttttttttt</h3>
        <div id="mid-div"></div>
    </div>

    <div class="inside-div" id="second-div">
        <div class="second-div-content">
            <p>Subtotal</p>
            <p id="subtotal">₱0.00</p>
        </div>
        <div class="second-div-content">
            <p style="margin-top: 0;">Delivery fee</p>
            <p id="delivery-fee" style="margin-top: 0;">₱50.00</p>
        </div>
        <div class="second-div-content">
            <p><b>Total</b></p>
            <p id="total"><b>₱0.00</b></p>
        </div>

        <div class="checkout-wrapper">
            <?php
            if (!empty($_SESSION['user_id'])) {
                echo createButton(
                    40,
                    280,
                    "Check out",
                    "check-out",
                    18,
                    "button",
                    ["onclick" => "window.location.href='index.php?page=checkout-page'"]
                );
            } else {
                echo createButton(
                    40,
                    280,
                    "Check out",
                    "check-out",
                    18,
                    "button",
                    ["onclick" => "document.getElementById('loginModal').style.display='block';"]
                );
            }
            ?>
        </div>
    </div>
</div>

<!-- External JS for cart logic -->
<script src="../Scripts/pages/cart.js"></script>
