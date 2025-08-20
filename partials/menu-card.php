<style>
    .card {
        width: 250px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        font-family: Arial, sans-serif;
        background: #f2f0d8;
        margin: 5px;
        display: inline-block;
        vertical-align: top;
    }
    .card img { width: 100%; height: 180px; object-fit: cover; }
    .card-body { padding: 12px; }
    .card-body h3 { font-size: 16px; margin: 0 0 6px; font-weight: 500; }
    .price { font-size: 15px; font-weight: bold; margin-bottom: 10px; }
    .actions { display: flex; align-items: center; justify-content: space-between; }
    .cart-icon { font-size: 20px; cursor: pointer; }
    .buy-btn {
        background: #2c3e50;
        color: #fff;
        border: none;
        padding: 6px 14px;
        border-radius: 20px;
        cursor: pointer;
        transition: background 0.3s;
    }
    .buy-btn:hover { background: #1a242f; }
</style>

<div class="card">
    <img src="<?php echo $image ?>" alt="<?php echo $name; ?>">
    <div class="card-body">
        <h3><?php echo $name; ?></h3>
        <div class="price">₱<?php echo $price; ?></div>
        <div class="actions">
            <span class="cart-icon">🛒</span>
            <form method="post" action="order.php">
                <input type="hidden" name="product" value="<?php echo $name; ?>">
                <input type="hidden" name="price" value="<?php echo $price; ?>">
                <button type="submit" class="buy-btn">Buy</button>
            </form>
        </div>
    </div>
</div>