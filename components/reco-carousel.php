


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

<script src="../Scripts/components/reco-carousel.js"></script>
