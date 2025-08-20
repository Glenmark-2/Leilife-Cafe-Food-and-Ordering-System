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

.section p:first-child {
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 5px;
}

.section p:nth-child(2) {
  font-size: 1rem;
  margin-bottom: 20px;
  color: #555;
}

.row {
  display: flex;
  justify-content: space-evenly;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
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

  .row {
    flex-direction: column;
    gap: 15px;
  }
}


</style>


<div class="overlay-container">
 <img src="\Leilife\public\assests\image 37.png" alt="Background" class="full-width-img">


  <div class="overlay-content">
    <h1>Welcome to Leilife Cafe</h1>
    <p>Your perfect spot for coffee and meals</p>
    <a href="index.php?page=menu" class="btn">Order Now</a>
  </div>
</div>

<div class="section">
    <p>Good Afternoon!</p>
    <p>Take a break and enjoy the flavors of Leilife Cafe and Resto!</p>
  <div class="row">
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
    <?php include '../partials/card.php'; ?>
  </div>
</div>

<div class="section2">
<div class="recommendations">
  <?php include '../components/reco-carousel.php'; ?>
</div>

</div>
