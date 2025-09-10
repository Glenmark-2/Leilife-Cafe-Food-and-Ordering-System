<?php
$cardClass = ($product['status'] ?? '') === 'unavailable' ? 'card unavailable' : 'card';
?>

<div class="<?= $cardClass ?>">
    <img src="<?= $image ?>" alt="<?= $name; ?>">
    <div class="card-body">
        <h3><?= ucwords($name); ?></h3>

        <div class="price-actions">
            <div class="price">₱<?= $price ?></div>

            <button class="buy-btn" 
                <?= ($product['status'] ?? '') === 'unavailable' ? 'disabled' : '' ?>
                onclick="window.location.href = 'index.php?page=solo-product&id=<?= $product['product_id'] ?>'">
                <?= ($product['status'] ?? '') === 'unavailable' ? 'Unavailable' : 'Buy' ?>
            </button>
        </div>
    </div>
</div>
