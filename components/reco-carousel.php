<?php
include "../backend/db_script/db.php";
include "../backend/db_script/appData.php";

$appData = new AppData($pdo);
$products = $appData->loadFeaturedProducts();

// Group products into chunks of 4 (2x2 per slide)
$slides = array_chunk($products, 4);
?>

<div class="carousel">
  <button class="carousel-arrow left">&#10094;</button>
  
  <div class="carousel-track">
    <?php foreach ($slides as $index => $slide): ?>
      <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
        <div class="reco-grid">
          <?php foreach ($slide as $product): ?>
            <div class="reco-card">
              <?php 
                // pass product data to reco-card
                $title = $product['product_name'];
                $price = $product['product_price'];
                $image = $product['product_picture'];
                include 'reco-card.php'; 
              ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="carousel-arrow right">&#10095;</button>
</div>


<script>
  document.querySelectorAll('.carousel').forEach(carousel => {
  const slides = carousel.querySelectorAll('.carousel-slide');
  const prevBtn = carousel.querySelector('.carousel-arrow.left');
  const nextBtn = carousel.querySelector('.carousel-arrow.right');
  let currentIndex = 0;

  function showSlide(index) {
    slides.forEach((slide, i) => {
      slide.classList.toggle('active', i === index);
    });
  }

  prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + slides.length) % slides.length;
    showSlide(currentIndex);
  });

  nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % slides.length;
    showSlide(currentIndex);
  });

  showSlide(currentIndex);
});
</script>
<!-- <script src="../Scripts/components/reco-carousel.js"></script> -->
