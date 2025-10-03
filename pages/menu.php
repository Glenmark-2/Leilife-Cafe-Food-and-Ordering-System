<?php 
include "../components/buttonTemplate.php";

// --- Load categories and only active products ---
$appData->loadCategories();
$appData->adminloadProducts(false); // false = only active products
include '../components/order_button.php';

?>

<style>
/* --- Sliding Styles with Snap --- */
.menu-cards {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scroll-snap-type: x mandatory; 
  padding: 10px 0;
  cursor: grab;
  scrollbar-width: none;   
}
.menu-cards::-webkit-scrollbar {
  display: none;          
}
.menu-cards:active {
  cursor: grabbing;
}

.menu-cards > * {
  flex: 0 0 auto;
  scroll-snap-align: start; 
}

.category-section {
  position: relative;
  margin-bottom: 40px;
}

.slide-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  border: none;
  background: rgba(0,0,0,0.5);
  color: white;
  font-size: 24px;
  cursor: pointer;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  z-index: 5;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.3s ease, opacity 0.3s ease;
  opacity: 0; /* ✅ hidden by default */
  pointer-events: none;
}
.slide-btn:hover {
  background: rgba(0,0,0,0.8);
}
.slide-left { left: -15px; }
.slide-right { right: -15px; }

.slide-btn.visible {
  opacity: 1;
  pointer-events: auto;
}

@media (max-width: 768px) {
  .slide-btn {
    font-size: 20px;
    width: 35px;
    height: 35px;
  }
  .slide-left { left: -10px; }
  .slide-right { right: -10px; }
}
</style>

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
          strtolower(str_replace(' ', '-', $mainCatName)), 
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

      <!-- Left/Right Slide Buttons -->
      <button class="slide-btn slide-left">&#10094;</button>
      <button class="slide-btn slide-right">&#10095;</button>

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


<!-- JS for filtering subcategories by main category -->
<script>
// --- Filter by main category ---
const buttons = document.querySelectorAll('.category-buttons button');
const sections = document.querySelectorAll('.category-section');
const defaultMainCategory = 'Meal';

function showCategory(mainCat) {
  sections.forEach(section => {
    section.style.display = (section.dataset.mainCategory === mainCat) ? '' : 'none';
  });
}
showCategory(defaultMainCategory);

buttons.forEach(btn => {
  btn.addEventListener('click', () => {
    const mainCat = btn.textContent.trim();
    showCategory(mainCat);
  });
});

// --- Sliding functionality ---
document.querySelectorAll('.category-section').forEach(section => {
  const cards = section.querySelector('.menu-cards');
  const btnLeft = section.querySelector('.slide-left');
  const btnRight = section.querySelector('.slide-right');

  function updateArrows() {
    const canScroll = cards.scrollWidth > cards.clientWidth;
    if (!canScroll) {
      btnLeft.classList.remove("visible");
      btnRight.classList.remove("visible");
      return;
    }
    btnLeft.classList.toggle("visible", cards.scrollLeft > 0);
    btnRight.classList.toggle(
      "visible", 
      Math.ceil(cards.scrollLeft + cards.clientWidth) < cards.scrollWidth
    );
  }

  // ✅ Run only when content fully loaded
  window.addEventListener("load", updateArrows);
  setTimeout(updateArrows, 500); // fallback if images load late

  cards.addEventListener("scroll", updateArrows);
  window.addEventListener("resize", updateArrows);

  // Buttons
  btnLeft.addEventListener('click', () => {
    cards.scrollBy({ left: -cards.clientWidth, behavior: 'smooth' });
  });
  btnRight.addEventListener('click', () => {
    cards.scrollBy({ left: cards.clientWidth, behavior: 'smooth' });
  });

  // Horizontal scroll with mouse wheel
  cards.addEventListener("wheel", (e) => {
    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
      e.preventDefault();
      cards.scrollBy({
        left: e.deltaY,
        behavior: "smooth"
      });
    }
  }, { passive: false });
});
</script>
