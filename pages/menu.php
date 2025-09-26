<?php 
include "../components/buttonTemplate.php";

// --- Load categories and only active products ---
$appData->loadCategories();
$appData->adminloadProducts(false); // false = only active products

// Group products by category for quick lookup
$productsByCategory = [];
foreach ($appData->products as $product) {
  $catName = $product['category_name'] ?? '';
  if ($catName) {
    $productsByCategory[$catName][] = $product;
  }
}

// Group subcategories under main categories
$mainCategories = [];
foreach ($appData->categories as $cat) {
  $mainCatName   = $cat['main_category_name'] ?? '';
  $categoryName  = $cat['category_name'] ?? '';

  if ($mainCatName) {
    $mainCategories[$mainCatName][] = $categoryName;
  }
}
?>

<div class="menu">
  <!-- Main Category Buttons -->
  <div class="category-buttons">
    <?php foreach ($mainCategories as $mainCatName => $subcats): ?>
      <?php 
        echo createButton(
          45,                
          160,               
          $mainCatName,      
          strtolower(str_replace(' ', '-', $mainCatName)), // id (slugified)
          15,               
          "button",          
          ["data-category" => $mainCatName] 
        );
      ?>
    <?php endforeach; ?>
  </div>

  <!-- Subcategories & Products -->
  <?php foreach ($mainCategories as $mainCatName => $subcats): ?>
    <?php foreach ($subcats as $categoryName): ?>
      <?php if (empty($productsByCategory[$categoryName])) continue; // skip empty subcategory ?>
      <div class="category-section" data-main-category="<?= htmlspecialchars($mainCatName) ?>">
        <div class="category-title">
          <?= htmlspecialchars(ucwords(str_replace('_', ' ', $categoryName))) ?>
        </div>

        <div class="menu-cards">
          <?php foreach ($productsByCategory[$categoryName] as $product): ?>
            <?php
            $name  = $product['product_name'] ?? '';
            $price = $product['product_price'] ?? 0;
            $image = !empty($product['product_picture'] ?? '')
              ? "../public/products/" . trim($product['product_picture'])
              : "../public/assests/image-43.png";

            include '../partials/menu-card.php';
            ?>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
</div>

<!-- JS for filtering subcategories by main category -->
<script>
  const buttons = document.querySelectorAll('.category-buttons button');
  const sections = document.querySelectorAll('.category-section');

  function showCategory(mainCat) {
    sections.forEach(section => {
      section.style.display = (section.dataset.mainCategory === mainCat) ? '' : 'none';
    });
  }

  // Show first available main category by default
  if (buttons.length > 0) {
    const firstCat = buttons[0].textContent.trim();
    showCategory(firstCat);
  }

  // Handle button clicks
  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const mainCat = btn.textContent.trim();
      showCategory(mainCat);
    });
  });
</script>
