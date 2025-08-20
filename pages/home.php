<style>
  .overlay-container {
    position: relative;
    width: 100%;
    height: 60vh;
    overflow: hidden;
  }

  .full-width-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .overlay-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    width: min-width;
  }

  .overlay-content h1 {
    font-size: 3rem;
    font-weight: bold;
  }

  .overlay-content p {
    font-size: 1.2rem;
  }

  .btn {
    margin-top: 400px;
    padding: 12px 24px;
    border-radius: 8px;
    background-color: #6f4e37; /* Coffee brown */
    color: white;
    text-decoration: none;
  }

  .btn-global:hover {
    background-color: #5a3f2d;
  }

  .section {
    margin-top: 50px;
  }
  .row {
    display: flex;
    flexDirection: row;
    justifyContent: center;
    alignContent: center;
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
  <div class="row">
    <div class="card"><?php include '../partials/card.php'; ?></div>
    <div class="card"><?php include '../partials/card.php'; ?></div>
    <div class="card"><?php include '../partials/card.php'; ?></div>
    <div class="card"><?php include '../partials/card.php'; ?></div>

  </div>
</div>
