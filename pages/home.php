

<?php
// load the component file ONCE at the top
require_once "../components/product-card.php";
require_once '../partials/intro-card.php';
// require_once '../pages/cart.php';

// include "../pages/cart.php"
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
  <!-- <div class="product-item">
  <div class="product-item-title">
    <p>You may choose from</p>
  </div>
  <div style="display: flex; flex-direction: row;">
  </div>
</div> -->
  <div class="info-section" style="display:flex; justify-content:center; padding:40px 20px;">
  <div style="display:flex; align-items:center; width:90%; max-width:1200px; gap:72px;">
    <h5 style="flex:1; margin:0; font-size:32px; line-height:1.35; text-align:left;">
      Savor Every Bite & Sip at<br>Leilife Cafe and Resto!
    </h5>
    <p style="flex:1; margin:0; font-size:16px; line-height:1.7; text-align:left; max-width:65ch;">
      At Leilife Cafe and Resto, we take pride in serving delicious meals
      and perfectly brewed coffee. From freshly prepared dishes to expertly
      crafted beverages, every bite and sip is made to give you a warm and
      memorable dining experience.
    </p>
  </div>
</div>

<div class="info-cards" style="display: flex; flex-direction: row; justify-content: center; align-content: center;">
  <?php
  echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
  echo infoCard("☕", "Perfectly Brewed Coffee", "Our skilled baristas ensure each cup...");
  echo infoCard("❤️", "A Taste to Remember", "Enjoy hearty meals and comforting drinks...");
  ?>
</div>



</div>

<!-- about us part -->
<div id="about-us">
  <h1>About us</h1>
  <div style="width: 70%;">
    <p>
      At Leilife Cafe and Resto, we believe every meal should be a moment to savor.
      From freshly brewed coffee to hearty meals, we combine quality ingredients,
      skilled preparation, and a warm ambiance to create the perfect dining experience
      for every guest.
    </p>
  </div>
</div>


<div id="about-us-content">
  <div id="left-about-us">
    <div style="width: 23vw;">
      <h2>Where Good Food Meets Great Company</h2>
    </div>

    <div style="width: 40vw;">
      <p>Enjoy the perfect blend of flavors in our menu — from aromatic coffee to delicious comfort food. Whether you're here for a quick coffee break or a full meal, our passion for great taste shines through in every bite and sip.</p>
    </div>
    <br>

    <div style="position: relative; width: 40vw; height: 7vh;">
      <!-- gray background -->
      <div style="background-color:#d9d9d9; width:23vw; height: 7vh;"></div>
      <!-- blue box overlapping -->
      <div style="position:absolute; top:-50%; left:00%; background-color:#355361; width:20vw; height: 7vh;"></div>

      <h2 style="position: absolute; top:-20%; left: 10%; color:white; text-shadow: 1px 1px 3px black;">OPENING HOURS</h2>
    </div>

    <div style="width: 23vw;">
      <p>Monday – Friday: 8:00 AM – 10:00 PM <br> Saturday – Sunday: 7:00 AM – 11:00 PM</p>
    </div>
  </div>

  <div id="right-about-us" style="position:relative;">
    <div style="width: 50%; height:80%; position:absolute; top:20%; left: 50%; background:#355362; z-index: 1;"></div>

    <img src="../public/assests/about us.png"
      alt="photo"
      style="width: 55%; margin-left:150px; position:relative; z-index: 2; left:10%; top:10%;"> 
  </div>

</div>

<!-- contact us -->

<!-- CONTACT US -->
<h1 id="contact-section" style="display: flex; justify-content: center; margin-top:60px;">Contact Us</h1>

<div class="contact-wrapper">
  <!-- LEFT SIDE -->
  <div class="contact-info">
    <div class="info-header" >
      <h5 >Get In Touch</h5>
    </div>
    <p>
      If you want, I can also design this in the same style and color layout as the sample image so it matches your website’s aesthetic. I can make it visually similar but with Leilife Cafe and Resto branding.
    </p>

    <div class="info-item">
      <i class="fas fa-envelope"></i>
      <span>leilifecafe&resto@gmail.com</span>
    </div>

    <div class="info-item">
      <i class="fas fa-map-marker-alt"></i>
      <span>Lunduyan Langaray, Brgy 14. Caloocan City</span>
    </div>

    <div class="info-item">
      <i class="fas fa-phone"></i>
      <span>0912345678</span>
    </div>
<br>
    <div class="info-item">
      <i class="fas fa-clock"></i>
      <span>
        Monday – Friday: 8:00 AM – 10:00 PM <br>
        Saturday – Sunday: 7:00 AM – 11:00 PM
      </span>
    </div>
  </div>

  <!-- RIGHT SIDE -->
  <div class="contact-form">
    <h2>Your Details</h2>
    <p>Let us know how we get back to you</p>
    <form method="POST" action="../backend/inbox.php">
    <div class="form-row">
      <input class="contact-input" type="text" placeholder="Name" name="name" required>
      <input class="contact-input" type="email" placeholder="Email Address" name="email" required>
    </div>

    <input class="contact-input"  type="text" placeholder="Subject" class="form-control" name="subject">

    <textarea style="width: max-width; height:100px" placeholder="Comments/Questions:" name="message" required></textarea>

    <div style="display: flex; justify-content:flex-end; margin-top:10px">
      <?php
          echo createButton(40, 100, "Submit", "submitBtn",16,"submit");
          ?>
    </div>
    </form>
  </div>
</div>
