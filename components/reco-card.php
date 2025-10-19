<?php
$status = $product['status'] ?? 'available';
$cardClass = $status === 'unavailable' ? 'product-card unavailable' : 'product-card';
$productLink = "index.php?page=solo-product&id=" . $product['product_id'];
?>

<div class="<?= $cardClass ?>" <?= $status === 'available' ? "onclick=\"window.location.href='{$productLink}'\"" : "" ?>>
  <img 
    src="../public/products/<?php echo htmlspecialchars($image); ?>" 
    alt="<?php echo htmlspecialchars($title); ?>" 
    class="product-image"
  >

  <div class="product-info">
    <p class="product-title"><?php echo htmlspecialchars($title); ?></p>
    <div class="product-actions">
      <p class="product-price">₱<?php echo htmlspecialchars($price); ?></p>
      <?php if ($status === 'available'): ?>
        <button class="buy-btn">Buy</button>
      <?php else: ?>
        <span class="unavailable-label">Unavailable</span>
      <?php endif; ?>
    </div>
  </div>
</div>

