<style>
 .overlay-container {
  position: relative;
  width: 100%;
  height: 50vh;
  overflow: hidden;
}

.full-width-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Dark overlay */
.overlay-container::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5); /* adjust darkness (0.3–0.7) */
  z-index: 1;
}

.overlay-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  z-index: 2; /* Make sure text stays above the dark overlay */
}

.overlay-content h1 {
  font-size: 3rem;
  font-weight: bold;
}

.overlay-content p {
  font-size: 1.2rem;
}

.btn {
  margin-top: 20px;
  padding: 12px 24px;
  border-radius: 8px;
  background-color: #22333b; /* Coffee brown */
  color: white;
  text-decoration: none;
}

.btn:hover {
  background-color: #415863ff;
}
.section {
  display: flex;
  flex-direction: column; /* stack text and row vertically */
  margin: 40px 300px;
  border-radius: 20px;
  background-color: #f2f2f2; /* lighter background */
  border: 1px solid #e0e0e0;
  padding: 20px 30px;
  box-shadow: 0px 4px 12px rgba(0,0,0,0.1); /* soft shadow */
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
  gap: 20px; /* space between cards */
  flex-wrap: wrap; /* responsive: wrap cards if screen is small */
}
.section2{
  text-align: center;
  margin: 10px 0px;
}
.reco-card{
  display: flex;
  justify-content: center;
}
  .reco-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* 2 columns */
    gap: 20px; /* space between cards */
    justify-content: center;
    align-items: center;
    max-width: 800px; /* keeps grid centered */
    margin: 0 auto; /* center horizontally */
    padding: 20px 0;
  }

  .reco-card {
    display: flex;
    justify-content: center;
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
