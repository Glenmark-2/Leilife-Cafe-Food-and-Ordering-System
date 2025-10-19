<?php
$cardClass = ($product['status'] ?? '') === 'unavailable' ? 'card unavailable' : 'card';
?>

<div class="<?= $cardClass ?>">
  <img src="<?= $image ?>" alt="<?= htmlspecialchars($name); ?>">
  <div class="card-body">
    <h3 class="product-name"><?= ucwords($name); ?></h3>

    <div class="price-actions">
      <div class="price">₱<?= htmlspecialchars($price); ?></div>

      <button class="buy-btn"
        <?= ($product['status'] ?? '') === 'unavailable' ? 'disabled' : '' ?>
        onclick="window.location.href = 'index.php?page=solo-product&id=<?= $product['product_id'] ?>'">
        <?= ($product['status'] ?? '') === 'unavailable' ? 'Unavailable' : 'Buy' ?>
      </button>
    </div>
  </div>

  <?php if (($product['status'] ?? '') === 'unavailable'): ?>
    <div class="unavailable-overlay">Unavailable</div>
  <?php endif; ?>
</div>
