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
$page_styles = include __DIR__ . '/../backend/config/style_config.php';
if (isset($page_styles[$page])) {
    foreach ($page_styles[$page] as $css_file) {
        echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
    }
}

  // if (isset($page_styles[$page])) {
  //     foreach ($page_styles[$page] as $css_file) {
  //         echo '<link rel="stylesheet" href="' . $css_file . '">' . PHP_EOL;
  //     }
  // }
  ?>


</head>
<body class="has-fixed-nav">

<!-- Navbar -->
<nav class="navbar" id="siteNav">
  <!-- Logo -->
  <a class="navbar-brand" id="logo" href="index.php?page=home">
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

  <!-- Right side buttons (dynamic desktop) -->
  <div class="navbar-actions">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="index.php?page=user-profile" class="btn-link">Profile</a>
        <a href="index.php?page=cart" class="btn-dark">Cart</a>
    <?php else: ?>
        <a href="#" id="loginBtn" class="btn-link">Login</a>
        <a href="index.php?page=signUp" class="btn-dark">Sign Up</a>
    <?php endif; ?>
  </div>
</nav>

<!-- Mobile Dropdown Menu -->
<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <a href="index.php?page=menu">Menu</a>
  <a href="index.php?page=home#about-us">About</a>
  <a href="index.php?page=home#contact-section">Contact</a>

  <!-- Dynamic auth links (mobile) -->
  <div class="auth-links">
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="index.php?page=user-profile">Profile</a>
        <a href="index.php?page=cart">Cart</a>
    <?php else: ?>
        <a id="loginBtn" href="index.php?page=login">Login</a>
        <a href="index.php?page=signUp">Sign Up</a>
    <?php endif; ?>
  </div>
</div>

<div id="loginModal" style="display: none;">
  <?php include "../pages/login.php" ?>
</div>


<!-- Page container -->
<!-- cart here  -->


<script src="../Scripts/components/header.js"></script>
