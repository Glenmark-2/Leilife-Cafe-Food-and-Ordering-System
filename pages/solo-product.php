

<div class="product-container">
    <img src="../public/assests/about us.png" class="product-image">

    <div class="product-details">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 100px;">
            <h2 style="margin:0;">Product name Product name</h2>
            <button id="heartBtn" 
                    style="border:2px solid black; background-color:transparent; border-radius:50%; width:35px; height:35px; font-size:16px; color:black; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                ❤
            </button>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <p style="font-size:20px; margin:0;">₱100.00</p>

            <div style="display:flex; align-items:center;">
    <button onclick="changeQty(-1)" style="border:1px solid black; background-color:transparent; border-radius:50%; width:30px; height:30px; font-size:16px; color:black; cursor:pointer; margin-right:10px;">-</button>
    
    <input id="quantity" type="number" value="1" min="1" 
           style="width:40px; text-align:center; padding:4px; border:none; border-radius:6px; font-size:14px; margin-right:10px; background-color:transparent"
           readonly> <!-- make it read-only -->
    
    <button onclick="changeQty(1)" style="border:1px solid black; background-color:transparent; border-radius:50%; width:30px; height:30px; font-size:16px; color:black; cursor:pointer;">+</button>
</div>
        </div>

        <?php
            include "../components/buttonTemplate.php";
            echo createButton(40, 280, "Add to cart","add-to-cart-btn");
        ?>
    </div>
</div>

<script src="../Scripts/pages/solo-product.js"></script>
