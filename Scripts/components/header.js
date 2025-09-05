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
    if (loginBtn && loginModal || isset($_SESSION['user_id'])) {
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