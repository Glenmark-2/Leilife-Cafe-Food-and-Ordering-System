<?php
// keep your original server-side includes exactly as before
include "../components/buttonTemplate.php";
$appData->loadCategories();
$appData->adminloadProducts(false);
?>
<!-- Yellow Pull-to-Reveal Header (kept) -->
<div class="menu-header" id="menuHeader">
  <div class="menu-header-inner">
    <h1 class="menu-title">Our Menu</h1>
    <p class="menu-tagline">Discover delicious meals crafted with love and served fresh daily.</p>
  </div>
</div>

<!-- Menu Sheet -->
<div class="menu" id="menuSheet">

  <!-- Main Category Buttons -->
  <div class="category-buttons category-bar" id="categoryButtons">
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
          strtolower(str_replace(' ', '-', $mainCatName)), 
          15,                
          "button",          
          ["data-category" => $mainCatName] 
        );
      }
    }
    ?>
  </div>
    <!-- Compact Mobile Category Header -->
<div class="mobile-category-header" id="mobileCategoryHeader">
  <button class="scroll-btn left" id="scrollLeftBtn">&#10094;</button>
  <div class="mobile-category-scroll" id="mobileCategoryScroll">
    <?php
    $mainCategories = [];
    foreach ($appData->categories as $cat) {
      $mainCatName = $cat['main_category_name'] ?? '';
      if ($mainCatName && !in_array($mainCatName, $mainCategories)) {
        $mainCategories[] = $mainCatName;
        echo "<button class='mobile-cat-btn' data-category='$mainCatName'>$mainCatName</button>";
      }
    }
    ?>
  </div>
  <button class="scroll-btn right" id="scrollRightBtn">&#10095;</button>
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

      <!-- Left/Right Slide Buttons
      <button class="slide-btn slide-left">&#10094;</button>
      <button class="slide-btn slide-right">&#10095;</button> -->

      <div class="menu-cards">
        <?php foreach ($appData->products as $product): ?>
          <?php if (($product['category_name'] ?? '') === $categoryName): ?>
            <?php
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
<?php include '../components/order_button.php'; ?>

<!-- Mobile Floating "View My Bag" Button -->
<div class="mobile-bag-footer">
  <button id="mobileBagButton">View my bag</button>
</div>

<script src="/Leilife/Scripts/pages/menu.js" defer></script>

