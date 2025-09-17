<?php 
include "../components/buttonTemplate.php";
?>
<div class="menu">
  <!-- Main Category Buttons -->
  <div class="category-buttons">
    <?php
    $mainCategories = [];
    foreach ($appData->categories as $cat) {
      $mainCatName = $cat['main_category_name'] ?? '';
      if ($mainCatName && !in_array($mainCatName, $mainCategories)) {
        $mainCategories[] = $mainCatName;
        echo createButton(
          45,                
          160,               
          $mainCatName,      
          strtolower(str_replace(' ', '-', $mainCatName)), // id (slugified)
          15,               
          "button",          
          ["data-category" => $mainCatName] 
        );
      }
    }
    ?>
  </div>

  <!-- Subcategories & Products -->
  <?php foreach ($appData->categories as $cat): ?>
    <?php
    $mainCatName = $cat['main_category_name'] ?? '';
    $categoryName = $cat['category_name'] ?? '';
    ?>
    <div class="category-section" data-main-category="<?= htmlspecialchars($mainCatName) ?>">
      <div class="category-title">
        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $categoryName))) ?>
      </div>

      <div class="menu-cards">
        <?php foreach ($appData->products as $product): ?>
          <?php if (($product['category_name'] ?? '') == $categoryName): ?>
            <?php
            // Debug in HTML comments (check in View Source)
            echo "\n<!-- DEBUG PRODUCT DATA:\n" . print_r($product, true) . "\n-->\n";

            $name  = $product['product_name'] ?? '';
            $price = $product['product_price'] ?? 0;
            $image = !empty($product['product_picture'] ?? '')
              ? "../public/products/" . trim($product['product_picture'])
              : "../public/assests/image-43.png";

            include '../partials/menu-card.php';
            ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- JS for filtering subcategories by main category -->
<script>
  const buttons = document.querySelectorAll('.category-buttons button'); // adjust if your button.php uses <button>
  const sections = document.querySelectorAll('.category-section');

  const defaultMainCategory = 'Meal'; // <-- change this to your default main category

  function showCategory(mainCat) {
    sections.forEach(section => {
      section.style.display = (section.dataset.mainCategory === mainCat) ? '' : 'none';
    });
  }

  // Show default main category on page load
  showCategory(defaultMainCategory);

  // Handle button clicks
  buttons.forEach(btn => {
    btn.addEventListener('click', () => {
      const mainCat = btn.textContent.trim();
      showCategory(mainCat);
    });
  });
</script>
