<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leilife Cafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="public/assests/global.css">

  <!-- Internal CSS -->
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background-color: #ebe8e2;
    }

    .navbar {
      background-color: #d0b28c;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem 3rem;
    }

    /* Logo */
    .navbar-brand img {
      height: 80px;
    }

    /* Center menu */
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

    /* Right side buttons */
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

    /* Responsive */
    @media (max-width: 768px) {
      .navbar {
        flex-wrap: wrap;
      }
      .navbar-nav {
        width: 100%;
        justify-content: center;
        margin: 0.5rem 0;
      }
      .navbar-actions {
        width: 100%;
        justify-content: center;
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

  <!-- Center navigation -->
  <ul class="navbar-nav">
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
