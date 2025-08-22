<style>
/* === HERO (Overlay Section) === */
.overlay-container {
  position: relative;
  width: 100%;
  height: 60vh; /* taller for better hero feel */
  overflow: hidden;
}

.full-width-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.overlay-container::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1;
}

.overlay-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  z-index: 2;
  padding: 0 1rem;
}

.overlay-content h1 {
  font-size: 2.5rem;
  font-weight: bold;
}

.overlay-content p {
  font-size: 1.2rem;
}

.btn {
  margin-top: 20px;
  padding: 12px 24px;
  border-radius: 8px;
  background-color: #22333b;
  color: white;
  text-decoration: none;
  display: inline-block;
}
.btn:hover {
  background-color: #415863;
}

/* === SECTION (Good Afternoon) === */
.section {
  display: flex;
  flex-direction: column;
  margin: 40px auto;
  border-radius: 20px;
  background-color: #f2f2f2;
  border: 1px solid #e0e0e0;
  padding: 20px 30px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
  max-width: 1200px;
}

.greating {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 5px;
}

.statementGreating {
  font-size: 1rem;
  margin-bottom: 20px;
}

.row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
  justify-content: center;
}


/* === RECOMMENDATIONS SECTION === */
.section2 {
  text-align: center;
  margin: 20px 0px;
}

.reco-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  justify-content: center;
  align-items: center;
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px 0;
}

.reco-card {
  display: flex;
  justify-content: center;
}
.product-item {
  display: flex;
  flex-direction: column;
  background: #e0e0e0;
  width: fit-content;
  height: auto; 
  margin: 10px auto; 
  padding: 20px;
  border: none;
  border-radius: 20px;
}

.product-item-title {
  display: flex;
  align-self: flex-start;
  font-weight: 700;
  font-size: 20px;
}
/* === FEATURED BOX === */
.featured-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  margin: 30px 20px;
}

.featured-box {
  display: flex;
  background: #e0e0e0;
  border-radius: 12px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
  max-width: 1000px;
  width: 100%;
  overflow: hidden;
}

.featured-image {
  flex: 1;
  min-width: 40%;
  display: flex;
  align-items: center;
  justify-content: left;
  background: #1e2d33;
}
.featured-image img {
  width: 100%;
  height: auto;              /* let the image keep aspect ratio */
  max-height: 400px;         /* optional: prevent it from being too tall */
  object-fit: cover;
}

.featured-content {
  flex: 1.2;
  padding: 30px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.featured-content h2 {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 30px;
  color: #000;
  text-align: left;
}

.featured-content p {
  font-size: 16px;
  line-height: 1.6;
  margin-bottom: 24px;
  color: #333;
  text-align: justify;
}

.featured-btn {
  display: flex;
  justify-content: flex-end;
}

/* === RESPONSIVENESS === */
@media (max-width: 992px) {
  .featured-box {
    flex-direction: column;
  }
  .featured-image {
    min-width: 100%;
  }
  .featured-image img {
    width: 100%;
    height: auto;       /* prevent stretching */
    max-height: 250px;  /* cap the image height on tablets */
  }
  .featured-content {
    padding: 20px;
  }
  .featured-content h2 {
    font-size: 20px;
    text-align: center;
  }
  .featured-content p {
    font-size: 14px;
    text-align: center;
  }
  .featured-btn {
    justify-content: center;
    margin-top: 20px;
  }
    .row {
    justify-content: flex-start;
  }
}

@media (max-width: 600px) {
  .featured-image img {
    max-height: 180px; /* smaller cap for mobile */
  }
  .featured-content h2 {
    font-size: 18px;
  }
  .featured-content p {
    font-size: 13px;
  }
}


/* === RESPONSIVENESS === */

/* Tablets */
@media (max-width: 992px) {
  .overlay-content h1 {
    font-size: 2rem;
  }
  .overlay-content p {
    font-size: 1rem;
  }
  .section {
    margin: 30px 40px;
    padding: 20px;
  }
}
@media (max-width: 821px) {
  .row   {
    justify-content: center;
  }
      .about-section {
      flex-direction: column;
      gap: 24px;
    }
    .about-section h5 {
      font-size: 24px;
      text-align: center;
    }
    .about-section p {
      font-size: 15px;
      text-align: center;
      max-width: 100%;
    }
}
/* Mobile */
@media (max-width: 600px) {
  .overlay-container {
    height: 40vh;
  }
  .overlay-content h1 {
    font-size: 1.6rem;
  }
  .overlay-content p {
    font-size: 0.9rem;
  }
  .btn {
    padding: 10px 18px;
    font-size: 0.9rem;
  }

  .section {
    margin: 20px 15px;
    padding: 15px;
  }
  .section p:first-child {
    font-size: 1.2rem;
  }
  .section p:nth-child(2) {
    font-size: 0.9rem;
  }
}
@media (max-width: 520px) {
  .row {
    grid-template-columns: repeat(2, 1fr); /* force 2 per row */
    gap: 8px;
  }
}
</style>

<?php 
// load the component file ONCE at the top
require_once "../components/product-card.php"; 


?>

<!-- HERO -->
<div class="overlay-container">
  <img src="\Leilife\public\assests\image 37.png" alt="Background" class="full-width-img">

  <div class="overlay-content">
    <h1>Welcome to Leilife Cafe</h1>
    <p>Your perfect spot for coffee and meals</p>
    <a href="index.php?page=menu" class="btn">Order Now</a>
  </div>
</div>


<!-- GREETING SECTION -->
<div class="section">
  <p class="greating">Good Afternoon!</p>
  <p class="statementGreating">Take a break and enjoy the flavors of Leilife Cafe and Resto!</p>

  <div class="row">
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
  </div>
</div>

<!-- RECOMMENDATIONS SECTION -->
<div class="section2">
  <div class="recommendations">
    <?php include '../components/reco-carousel.php'; ?>
  </div>

<!-- FEATURED CONTENT BOX -->
<div class="featured-wrapper">
  <div class="featured-box">
    <!-- Left Image -->
    <div class="featured-image">
      <img src="\Leilife\public\assests\image 41.png" alt="Food">
    </div>

    <!-- Right Content -->
    <div class="featured-content">
      <div>
        <h2>
          When Coffee Meets Good Food, Great Conversations Begin.
        </h2>
        <p>
          At Leilife Cafe and Resto, we believe every meal should be a moment to savor. 
          From freshly brewed coffee to hearty meals, we combine quality ingredients, 
          skilled preparation, and a warm ambiance to create the perfect dining experience 
          for every guest.
        </p>
      </div>

      <div class="featured-btn">
        <?php 
          include "../components/buttonTemplate.php"; 
          echo createButton(40, 120, "Explore", "exploreBtn"); 
        ?>
      </div>
    </div>
  </div>
</div>


  <!-- PRODUCT ITEM SECTION -->
<div class="product-item">
  <div class="product-item-title">
    <p>You may choose from</p>
  </div>
  <div style="display: flex;
  flex-wrap: wrap;          /* allow wrapping on small screens */
  justify-content: center;  /* center items */
  gap: 16px;                /* spacing between cards */
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;">
    <?php 
      echo productCard("Meal","image.png",120,"#837575","White");
      echo productCard("Meal","image.png",120,"#837575","White");
      echo productCard("Meal","image.png",120,"#837575","White");
      echo productCard("Meal","image.png",120,"#837575","White");
      echo productCard("Meal","image.png",120,"#837575","White");
      echo productCard("Meal","image.png",120,"#837575","White");

    ?>
  </div>
</div>
<!-- <div style="display:flex; justify-content:center; width:100%; padding:40px 20px;">
  <div style="display:flex; align-items:flex-start; width:90%; max-width:1200px; gap:72px;">
    <h5 style="flex:1; margin:0; font-size:32px; line-height:1.35; text-align:left;">
      Savor Every Bite & Sip at<br>Leilife Cafe and Resto!
    </h5>
    <p style="flex:1; margin:0; font-size:16px; line-height:1.7; text-align:left; max-width:65ch;">
      At Leilife Cafe and Resto, we take pride in serving delicious meals
      and perfectly brewed coffee. From freshly prepared dishes to expertly crafted beverages, 
      every bite and sip is made to give you a warm and memorable dining experience.
    </p>
  </div>
</div> -->

</div>
