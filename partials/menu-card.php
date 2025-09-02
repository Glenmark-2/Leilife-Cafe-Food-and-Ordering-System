

<div class="card">
    <img src="<?php echo $image ?>" alt="<?php echo $name; ?>">
    <div class="card-body">
        <h3><?php echo $name; ?></h3>

        <div class="price-actions">
            <div class="price">₱<?php echo $price; ?></div>

            <!-- <div class="actions">
                <span class="cart-icon">🛒</span>
                <form method="post" action="order.php">
                    <input type="hidden" name="product" value="<?php echo $name; ?>">
                    <input type="hidden" name="price" value="<?php echo $price; ?>">
                    <button type="submit" class="buy-btn" id="buy-btn" onclick="window.location.href='./pages/soloProduct.php'">Buy</button>
                </form>
            </div> -->
            <button class="buy-btn" id="buy-btn" onclick="window.location.href = 'index.php?page=solo-product'" >buy</button>
        </div>
    </div>
</div>

<!-- <script>
    const buyBtn = document.getElementById("buy-btn");

    buyBtn.addEventListener('click', () => {
        // navigate through router
        window.location.href = "index.php?page=solo-product";
    });
</script> -->
