

<div id="cart-box">
    <div class="inside-div" id="first-div">
        <div id="top-div">
            <img src="../public/assests/motorbike.png" alt="motor" id="motor">
            <p id="mode">Delivery</p>
            <?php include "../components/buttonTemplate.php";
                echo createButton(25,60,"Change", "change",10);
            ?> 
        </div>

        <h3>My Cart</h3>

        <div id="mid-div">
            <img src="../public/assests/trash-bin.png" alt="trash" style="width: 18px; height:20px;">
            
            <div class="qty-controls">
                <input id="quantity" type="number" min=1 value="1" max=10 disabled>
                <button onclick="changeQty(1)">+</button>
            </div>

            <p class="product-name">Product name</p>
            <p class="product-price">P100.00</p>
        </div>
    </div>

    <div class="inside-div" id="second-div">
        <div class="second-div-content" >
            <p>Subtotal</p>
            <p>P100.00</p>
        </div>
        <div class="second-div-content" >
            <p style="margin-top: 0;">Delivery fee</p>
            <p style="margin-top: 0;">P50.00</p>
        </div>

        <div class="second-div-content">
            <p><b>Total</b></p>
            <p><b>P150.00</b></p>
        </div>

        <?php 
        echo createButton(40,280,"Check out", "check-out",18);
        ?>
    </div>
</div>

<script src="../Scripts/pages/cart.js"></script>
