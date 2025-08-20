<style>
    .carousel {
  position: relative;
  width: 100%;
  max-width: 900px;
  margin: auto;
  overflow: hidden;
}

.carousel-track {
  display: flex;
  transition: transform 0.5s ease;
}

.carousel-slide {
  min-width: 100%;
  display: none;
}

.carousel-slide.active {
  display: block;
}

.reco-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.2rem;
  justify-content: center;
  align-items: center;
  padding: 1rem;
}

.carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: #f8f8f8ff;
  border: none;
  padding: 10px;
  cursor: pointer;
  font-size: 1.5rem;
  border-radius: 50%;
}

.carousel-arrow.left { left: 10px; }
.carousel-arrow.right { right: 10px; }

</style>


<?php
// Example data (in real case this could come from a database)
$products = [
  ["title" => "Spanish Latte + Classic Tiramisu", "price" => "₱ 99.00", "image" => "/Leilife/public/assests/image 3 (2).png"],
  ["title" => "Iced Americano + Croissant", "price" => "₱ 120.00", "image" => "/Leilife/public/assests/image 5.png"],
  ["title" => "Cappuccino + Blueberry Muffin", "price" => "₱ 140.00", "image" => "/Leilife/public/assests/image 6.png"],
  ["title" => "Mocha Latte + Brownie", "price" => "₱ 130.00", "image" => "/Leilife/public/assests/image 7.png"],
  ["title" => "Vanilla Latte + Donut", "price" => "₱ 115.00", "image" => "/Leilife/public/assests/image 8.png"],
  ["title" => "Matcha Latte + Cheesecake", "price" => "₱ 150.00", "image" => "/Leilife/public/assests/image 9.png"]
];

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
                $title = $product['title'];
                $price = $product['price'];
                $image = $product['image'];
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
