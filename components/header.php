<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Leilife Cafe</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="public/assests/global.css">

  <style>
    /* base */
    :root { --nav-h: 64px; }
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background-color: #ebe8e2;
    }

    /* keep content below the fixed header */
    body.has-fixed-nav {
      padding-top: var(--nav-h);
    }

    /* NAVBAR - fixed + animation */
    .navbar{
      background-color: #d0b28c;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0.5rem 2rem;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      transform: translateY(0);
      transition: transform .28s cubic-bezier(.2,.9,.3,1), box-shadow .25s ease;
      will-change: transform;
      z-index: 1000;
    }

    /* hidden state (slides up) */
    .navbar.navbar--hidden {
      transform: translateY(-100%);
    }

    /* subtle shadow when scrolled */
    .navbar.navbar--scrolled {
      box-shadow: 0 6px 20px rgba(0,0,0,.12);
    }

    /* Logo */
    .navbar-brand img {
      height: 60px;
      display: block;
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
    .navbar-nav .nav-link:hover { color: #6c5f46; }

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
    .btn-link:hover { color: #6c5f46; }

    .btn-dark {
      background-color: #2c2c2c;
      color: #fff;
      text-decoration: none;
      padding: 0.4rem 1rem;
      border-radius: 50px;
      font-weight: 600;
      transition: background 0.3s ease;
    }
    .btn-dark:hover { background-color: #6c5f46; }

    /* Burger */
    .burger {
      display: none;
      flex-direction: column;
      cursor: pointer;
      gap: 6px;
      z-index: 1100;
      background: transparent;
      border: 0;
      padding: 6px;
    }
    .burger div {
      width: 28px;
      height: 3px;
      background-color: #2c2c2c;
      transition: all 0.4s ease;
    }
    .burger.active div:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
    .burger.active div:nth-child(2) { opacity: 0; transform: scaleX(0); }
    .burger.active div:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }

    /* Dropdown Menu (Mobile) - positioned under the fixed nav */
    .mobile-menu {
      display: none;
      flex-direction: column;
      background: #fff;
      width: 100%;
      text-align: center;
      border-radius: 0 0 40px 40px;
      overflow: hidden;
      animation: slideDown 0.22s ease forwards;
      position: fixed;
      top: var(--nav-h);
      left: 0;
      right: 0;
      z-index: 950;
    }
    .mobile-menu.show { display: flex; }

    .mobile-menu a {
      padding: 1rem;
      text-decoration: none;
      color: #2c2c2c;
      font-weight: 600;
      border-bottom: 1px solid #eee;
    }
    .mobile-menu a:hover { background: #f5f3ef; }

    .mobile-menu .auth-links {
      display:flex;
      flex-direction: row;
      justify-content:center;
      align-items:center;
      border-top: 1px solid #ccc;
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-6px); }
      to { opacity: 1; transform: translateY(0); }
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

    /* a simple container for testing */
    .container {
      max-width: 1100px;
      margin: 0 auto;
      padding: 2rem;
    }

    /* small demo content style so you can test scrolling */
    .demo-block { height: 900px; background: linear-gradient(180deg,#fff0,#eee); margin-bottom:1rem; border-radius:8px; padding:1rem; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="siteNav">
  <!-- Logo -->
  <a class="navbar-brand" href="index.php?page=home">
    <img src="\Leilife\public\assests\Mask group.png" alt="Logo">
  </a>

  <!-- Burger -->
  <button class="burger" id="burger" aria-label="Toggle menu">
    <div></div><div></div><div></div>
  </button>

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

<!-- Mobile Dropdown (kept outside the nav so it can be positioned under the fixed header) -->
<div class="mobile-menu" id="mobileMenu" aria-hidden="true">
  <a href="index.php?page=menu">Menu</a>
  <a href="index.php?page=about">About</a>
  <a href="index.php?page=contact">Contact</a>
  <div class="auth-links">
    <a href="index.php?page=login">Login</a>
    <a href="index.php?page=signUp">Sign Up</a>
  </div>
</div>

<!-- Page container -->

<script>
  (function () {
    const navbar = document.querySelector('.navbar');
    const burger = document.getElementById('burger');
    const mobileMenu = document.getElementById('mobileMenu');
    const loginBtn = document.getElementById('loginBtn');
    const loginModal = document.getElementById('loginModal');

    /* --- Mobile burger toggle (keeps header visible when menu is open) --- */
    burger && burger.addEventListener('click', () => {
      burger.classList.toggle('active');
      if (!mobileMenu) return;
      mobileMenu.classList.toggle('show');
      // Accessibility attribute
      const isShown = mobileMenu.classList.contains('show');
      mobileMenu.setAttribute('aria-hidden', !isShown);
      // When the mobile menu opens, keep header visible
      if (isShown) {
        navbar.classList.remove('navbar--hidden');
      }
    });

    /* --- Login modal toggle (simple) --- */
    if (loginBtn && loginModal) {
      loginBtn.addEventListener('click', (e) => {
        e.preventDefault();
        // toggle display
        loginModal.style.display = loginModal.style.display === 'flex' ? 'none' : 'flex';
        // make sure header remains visible when modal opens
        navbar.classList.remove('navbar--hidden');
      });
    }

    /* --- Measure header height and set CSS variable & body padding --- */
    function setNavHeight() {
      const h = navbar ? Math.round(navbar.getBoundingClientRect().height) : 64;
      document.documentElement.style.setProperty('--nav-h', h + 'px');
      document.body.classList.add('has-fixed-nav');
    }
    setNavHeight();
    window.addEventListener('resize', setNavHeight);

    // keep updating var if logo/image/font causes resize
    if (typeof ResizeObserver !== 'undefined' && navbar) {
      const ro = new ResizeObserver(setNavHeight);
      ro.observe(navbar);
    }

    /* --- Hide on scroll down, show on scroll up (robust) --- */
    let lastY = window.pageYOffset || document.documentElement.scrollTop || 0;
    let ticking = false;
    const threshold = 8;     // ignore tiny shakes
    const dontHideUntil = 60; // don't hide tiny initial scrolls near top

    function handleScroll() {
      if (ticking) return;
      ticking = true;
      window.requestAnimationFrame(() => {
        const currentY = window.pageYOffset || document.documentElement.scrollTop || 0;

        // Keep header visible when mobile menu is open
        const menuOpen = mobileMenu && mobileMenu.classList.contains('show');

        // if at very top, always show
        if (currentY <= 0) {
          navbar.classList.remove('navbar--hidden');
          navbar.classList.remove('navbar--scrolled');
          lastY = 0;
          ticking = false;
          return;
        }

        // add subtle shadow once we scroll a little
        if (currentY > 5) navbar.classList.add('navbar--scrolled');
        else navbar.classList.remove('navbar--scrolled');

        // If menu open, do not hide
        if (menuOpen) {
          navbar.classList.remove('navbar--hidden');
          lastY = currentY;
          ticking = false;
          return;
        }

        // ignore tiny moves
        if (Math.abs(currentY - lastY) <= threshold) {
          ticking = false;
          return;
        }

        if (currentY > lastY && currentY > dontHideUntil) {
          // scrolling down
          navbar.classList.add('navbar--hidden');
        } else {
          // scrolling up
          navbar.classList.remove('navbar--hidden');
        }

        lastY = currentY <= 0 ? 0 : currentY;
        ticking = false;
      });
    }

    window.addEventListener('scroll', handleScroll, { passive: true });
  })();
</script>
