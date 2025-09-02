<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leilife Cafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assests/global.css">

  <!-- always needed -->
  <link rel="stylesheet" href="../CSS/components/header.css">
  <link rel="stylesheet" href="../CSS/components/footer.css">
  <!-- cart and login modal are global, i still cant include this below, but it doesnt effect other displays -->
  <link rel="stylesheet" href="../CSS/pages/cart.css">
  <link rel="stylesheet" href="../CSS/pages/login.css">
<!-- Page-Specific -->
  <?php
  $page_styles = [
      'home' => [
          '../CSS/pages/home.css',
          '../CSS/partials/card.css',
          '../CSS/components/reco-card.css',
          '../CSS/components/reco-carousel.css'
      ],
      'menu' => [
          '../CSS/pages/menu.css',
          '../CSS/partials/menu-card.css'
      ],
      'cart' => ['../CSS/pages/cart.css'],
      'checkout-page' => ['../CSS/pages/checkout-page.css'],
      'signUp' => ['../CSS/pages/signUp.css'],
      'login' => ['../CSS/pages/login.css'],
      'solo-product' => ['../CSS/pages/solo-product.css'],
      'user-profile' => ['../CSS/pages/user-profile.css'],
      'order-tracking' => ['../CSS/pages/order-tracking.css'],
      'forgot-password' => ['../CSS/pages/forgot-password.css']
  ];

  if (isset($page_styles[$page])) {
      foreach ($page_styles[$page] as $css_file) {
          echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
      }
  }
  ?>


</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="siteNav">
  <!-- Logo -->
  <a class="navbar-brand" href="index.php?page=home">
    <img src="/Leilife/public/assests/Mask group.png" alt="Logo">
  </a>

  <!-- Burger -->
  <button class="burger" id="burger" aria-label="Toggle menu">
    <div></div><div></div><div></div>
  </button>

  <!-- Desktop Menu -->
  <ul class="navbar-nav desktop-menu">
    <li><a class="nav-link" href="index.php?page=menu">Menu</a></li>
    <li><a class="nav-link" href="index.php?page=home#about-us">About</a></li>
    <li><a class="nav-link" href="index.php?page=home#contact-section">Contact</a></li>
  </ul>

  <!-- Right side buttons -->
  <div class="navbar-actions">
    <a href="#" id="loginBtn" class="btn-link">Login</a>
    <a href="index.php?page=signUp" class="btn-dark">Sign Up</a>
  </div>
</nav>

<!-- Mobile Dropdown (kept outside the nav so it can be positioned under the fixed header) -->
<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <a href="index.php?page=menu">Menu</a>
  <a href="index.php?page=home#about-us-content">About</a>
  <a href="index.php?page=home#contact-section">Contact</a>
  <div class="auth-links">
    <a id="loginBtn" href="index.php?page=login">Login</a>
    <a href="index.php?page=signUp">Sign Up</a>
  </div>
</div>

<div id="loginModal" style="display: none;">
  <?php include "../pages/login.php" ?>
</div>

<!-- Page container -->
<!-- cart here  -->

<script src="../Scripts/components/header.js"></script>
