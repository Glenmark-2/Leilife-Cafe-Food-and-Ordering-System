<div class="product-card">
  <img src="../public/products/<?php echo htmlspecialchars($image); ?>" 
       alt="<?php echo htmlspecialchars($title); ?>" 
       class="product-image">
  <div class="product-info">
    <div class="info-wrapper">
      <p class="product-title"><?php echo htmlspecialchars($title); ?></p>
    </div>
    <div class="product-actions">
      <p style="margin-bottom: 0;" class="product-price"><?php echo htmlspecialchars($price); ?></p>
      <button style="margin-bottom: 0;" class="buy-btn">Buy</button>
    </div>
  </div>
</div>
