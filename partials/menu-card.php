<style>
.card {
    width: 180px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    font-family: Arial, sans-serif;
    background: #DBD8BA;
    margin: 2.5px;   /* half margin → total gap = 5px */
    display: inline-block;
    vertical-align: top;
}

.card img {
    width: 100%;
    height: 120px;             /* smaller image height */
    object-fit: cover;
}

.card-body {
    padding: 8px;              /* reduced padding */
}

.card-body h3 {
    font-size: 14px;           /* smaller text */
    margin: 0 0 6px;
    font-weight: 500;
    text-align: left;
}

/* Row for price + actions */
.price-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.price {
    font-size: 13px;
    font-weight: bold;
}

.actions {
    display: flex;
    align-items: center;
    gap: 8px; /* space between cart & button */
}

.cart-icon {
    font-size: 16px;           /* smaller icon */
    cursor: pointer;
}

.buy-btn {
    background: #2c3e50;
    color: #fff;
    border: none;
    padding: 4px 10px;         /* smaller button */
    border-radius: 16px;
    cursor: pointer;
    font-size: 12px;           /* smaller text */
    transition: background 0.3s;
}

.buy-btn:hover {
    background: #1a242f;
}
</style>

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
