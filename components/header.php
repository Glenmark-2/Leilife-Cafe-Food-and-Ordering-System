<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leilife Cafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assests/global.css">

  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #ebe8e2;
    }

    .navbar {
      background-color: #d0b28c;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem 2rem;
      position: relative;
      z-index: 1000;
    }

    /* Logo */
    .navbar-brand img {
      height: 60px;
    }

    /* Desktop menu */
    .navbar-nav {
      list-style: none;
      display: flex;
      gap: 2rem;
      margin: 0;
      padding: 0;
    }

    .navbar-nav .nav-link {
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .navbar-nav .nav-link:hover {
      color: #6c5f46;
    }

    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .btn-link {
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 600;
    }

    .btn-link:hover {
      color: #6c5f46;
    }

    .btn-dark {
      background-color: #2c2c2c;
      color: #fff;
      text-decoration: none;
      padding: 0.4rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      transition: background 0.3s ease;
    }

    .btn-dark:hover {
      background-color: #6c5f46;
    }

    /* Burger */
    .burger {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 6px;
      z-index: 1100;
    }

    .burger div {
      width: 28px;
      height: 3px;
      background-color: #2c2c2c;
      transition: all 0.4s ease;
    }

    /* Burger animation */
    .burger.active div:nth-child(1) {
      transform: rotate(45deg) translate(5px, 5px);
    }
    .burger.active div:nth-child(2) {
      opacity: 0;
    }
    .burger.active div:nth-child(3) {
      transform: rotate(-45deg) translate(5px, -5px);
    }

/* Dropdown Menu (Mobile) */
    .mobile-menu {
      display: none;
      flex-direction: column;
      background: #fff;
      width: 100%;
      text-align: center;
      border-radius: 0 0 40px 40px;
      overflow: hidden;
      animation: slideDown 0.3s ease forwards;
    }

    .mobile-menu.show {
      display: flex;
    }

    .mobile-menu a {
      padding: 1rem;
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 600;
      border-bottom: 1px solid #eee;
    }

    .mobile-menu a:hover {
      background: #f5f3ef;
    }

    /* Style divider between main links and auth links */
    .mobile-menu .auth-links {
      display: flex;
      flexDirection: row;
      justify-content: center;
      align-items: center;
      border-top: 1px solid #ccc;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Responsive rules */
    @media (max-width: 768px) {
      .navbar-nav,
      .navbar-actions {
        display: none; /* hide desktop menus */
      }
      .burger {
        display: flex;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
  <!-- Logo -->
  <a class="navbar-brand" href="index.php?page=home">
    <img src="\Leilife\public\assests\Mask group.png" alt="Logo">
  </a>

  <!-- Burger -->
  <div class="burger" id="burger">
    <div></div>
    <div></div>
    <div></div>
  </div>

  <!-- Desktop Menu -->
  <ul class="navbar-nav desktop-menu">
    <li><a class="nav-link" href="index.php?page=menu">Menu</a></li>
    <li><a class="nav-link" href="index.php?page=about">About</a></li>
    <li><a class="nav-link" href="index.php?page=contact">Contact</a></li>
  </ul>

  <!-- Right side buttons -->
  <div class="navbar-actions">
    <a href="#" id="loginBtn" class="btn-link">Login</a>
    <a href="index.php?page=signUp" class="btn-dark">Sign Up</a>
  </div>
</nav>

<div class="container">

<div id="loginModal"
style="display: flex;";
>
  <?php 
  include "../pages/login.php";
  ?>
</div>

<script>
  const loginBtn = document.getElementById("loginBtn");
  const loginModal = document.getElementById("loginModal");

  loginBtn.addEventListener('click', () => {
    loginModal.style.display = 'flex';
  });
</script>
