<style>
  /* === HERO (Overlay Section) === */
  .overlay-container {
    position: relative;
    width: 100%;
    height: 60vh;
    /* taller for better hero feel */
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
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
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
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
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
    height: auto;
    /* let the image keep aspect ratio */
    max-height: 400px;
    /* optional: prevent it from being too tall */
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

  /* new css added here */
  #about-us {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    text-align: center;
  }

  #about-us-content {
    margin: 70px 100px;
    display: flex;
    flex-direction: row;
  }

  /* === CONTACT US SECTION === */
.contact-wrapper {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  background: #d9d9d9;
  padding: 60px 80px;
  gap: 50px;
  flex-wrap: wrap;
  border-radius: 12px;
  max-width: 1200px;
  margin: 40px auto;
}

.contact-info {
  flex: 1;
  min-width: 280px;
}

.contact-info .info-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
}

.contact-info .info-header hr {
  flex: 0 0 40px;
  height: 1px;
  border: none;
  background: #000;
}

.contact-info h5 {
  font-size: 20px;
  margin: 0;
  font-weight: bold;
}

.contact-info p {
  margin-bottom: 25px;
  line-height: 1.6;
  font-size: 15px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 15px;
  font-size: 15px;
}

.info-item i {
  font-size: 18px;
  margin-top: 3px;
  color: #22333b;
}

/* Right Form */
.contact-form {
  flex: 1;
  background: #e8ebef;
  padding: 30px;
  border-radius: 12px;
  min-width: 300px;
}

.contact-form h2 {
  margin-bottom: 5px;
  font-size: 24px;
}

.contact-form p {
  margin-bottom: 20px;
  font-size: 14px;
  color: #333;
}

.form-row {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.form-control,
.contact-form input,
.contact-form textarea {
  width: 100%;
  padding: 10px 12px;
  border: none;
  border-radius: 6px;
  background: #f9f7f2;
  font-size: 14px;
  margin-bottom: 15px; /* ✅ Add consistent spacing */
}

.form-row input {
  margin-bottom: 0; /* ✅ Prevent double spacing on name/email row */
}
.contact-form textarea {
  height: 100px;
  resize: none;
  margin-bottom: 15px;
}

.contact-form button {
  background: #132931;
  color: white;
  padding: 10px 24px;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-size: 15px;
  transition: background 0.3s ease;
}

.contact-form button:hover {
  background: #22333b;
}

/* Responsive */
@media (max-width: 820px) {
  .contact-wrapper {
    flex-direction: column;
    padding: 40px 20px;
  }
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
      height: auto;
      /* prevent stretching */
      max-height: 250px;
      /* cap the image height on tablets */
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
      max-height: 180px;
      /* smaller cap for mobile */
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
    .row {
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
      grid-template-columns: repeat(2, 1fr);
      /* force 2 per row */
      gap: 8px;
    }
  }
</style>

<?php
// load the component file ONCE at the top
require_once "../components/product-card.php";
require_once '../partials/intro-card.php';


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
  <div style="display:flex; justify-content:center; padding:40px 20px;">
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
  </div>
  <div style="display: flex; flex-direction: row; justify-content: center; align-content: center;">
    <?php
    echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
    echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
    echo infoCard("🍽️", "Fresh & Flavorful Dishes", "We use only the freshest ingredients...");
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
      <h2>Where Good Food Meets Great Company</h3>
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
    <div style="width: 50%; height:90%; position:absolute; top:10%; left: 50%; background:#355362; z-index: 1;"></div>

    <img src="../public/assests/about us.png"
      alt="photo"
      style="width: 50%; margin-left:150px; position:relative; z-index: 2; left:20%">
  </div>

</div>

<!-- contact us -->

<!-- CONTACT US -->
<h1 style="display: flex; justify-content: center; margin-top:60px;">Contact Us</h1>

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

    <div class="form-row">
      <input type="text" placeholder="Name">
      <input type="email" placeholder="Email Address">
    </div>

    <input type="text" placeholder="Subject" class="form-control">

    <textarea placeholder="Comments/Questions:"></textarea>

    <button type="submit">Submit</button>
  </div>
</div>
