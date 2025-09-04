<div class="menu">
  <div class="category-buttons">
    <?php foreach ($appData->categories as $cat): ?>
      <?php $Text = $cat['category_name']; include '../components/button.php'; ?>
    <?php endforeach; ?>
  </div>

  <?php foreach ($appData->categories as $cat): ?>
    <div class="category-title"><?= htmlspecialchars($cat['category_name']) ?></div>
    <div class="menu-cards">
      <?php foreach ($appData->products as $product): ?>
        <?php if ($product['category_name'] === $cat['category_name']): ?>
          <?php 
            $name  = $product['product_name'];
            $price = $product['product_price'];
            $image = !empty($product['product_picture'])
              ? "../public/assests/" . $product['product_picture']
              : "../public/assests/image-43.png";
            include '../partials/menu-card.php';
          ?>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
</div>
