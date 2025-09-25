<?php
function renderRandomFeaturedProducts($appData)
{
    $products = $appData->loadFeaturedProducts();

    if (!empty($products)) {
        shuffle($products);
        $randomFeatured = array_slice($products, 0, 4);

        foreach ($randomFeatured as $product) {
            $image = $product['product_picture'] ?? "placeholder.png"; 
            $name  = $product['product_name'] ?? "Unnamed Product";
            $price = $product['product_price'] ?? "0";
?>
<div class="menu-card">
    <img src="../public/products/<?= htmlspecialchars($image) ?>" class="menu-card-img" alt="<?= htmlspecialchars($name) ?>">
    <div class="menu-card-body">
        <p class="menu-card-title"><?= htmlspecialchars($name) ?></p>
        <p class="menu-card-price">₱<?= htmlspecialchars($price) ?></p>
    </div>
</div>
<?php
        }
    } else {
        echo "<p>No featured products available.</p>";
    }
}
?>
