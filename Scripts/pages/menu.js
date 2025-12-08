document.addEventListener('DOMContentLoaded', () => {
  const catBar = document.getElementById('categoryButtons');
  const catButtons = Array.from(catBar?.querySelectorAll('button[data-category]') || []);
  const sections = Array.from(document.querySelectorAll('.category-section'));
  const scrollContainer = document.getElementById('mobileCategoryScroll');
  const leftBtn = document.getElementById('scrollLeftBtn');
  const rightBtn = document.getElementById('scrollRightBtn');
  const mobileButtons = Array.from(scrollContainer?.querySelectorAll('.mobile-cat-btn') || []);

  /* --- HELPER FUNCTIONS --- */
  const headerOffset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height')) || 56;

  function setActiveCategory(name) {
    if (!name) return;
    [...catButtons, ...mobileButtons].forEach(btn => {
      const match = btn.dataset.category?.trim() === name.trim();
      btn.classList.toggle('active', match);
      if (btn.hasAttribute('aria-selected'))
        btn.setAttribute('aria-selected', match ? 'true' : 'false');
    });
  }

  function showCategory(name) {
    sections.forEach(s => s.style.display = s.dataset.mainCategory === name ? '' : 'none');
  }

  function scrollToCategory(name) {
    const target = sections.find(s => s.dataset.mainCategory === name);
    if (!target) return;
    const top = target.getBoundingClientRect().top + window.scrollY - (headerOffset + 8);
    window.scrollTo({ top, behavior: 'smooth' });
  }

  /* --- INITIALIZE MAIN CATEGORIES --- */
  catButtons.forEach((btn, i) => {
    btn.setAttribute('role', 'tab');
    btn.setAttribute('aria-selected', 'false');
    btn.setAttribute('tabindex', i === 0 ? '0' : '-1');
  });

  if (catButtons.length) {
    const first = catButtons[0];
    showCategory(first.dataset.category);
    setActiveCategory(first.dataset.category);
  }

  /* --- MAIN BUTTON EVENTS --- */
  catButtons.forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const cat = btn.dataset.category;
      showCategory(cat);
      setActiveCategory(cat);
      scrollToCategory(cat);
    });

    btn.addEventListener('keydown', e => {
      if (['Enter', ' '].includes(e.key)) {
        e.preventDefault();
        btn.click();
      }
    });
  });

  /* --- KEYBOARD NAVIGATION --- */
  catBar?.addEventListener('keydown', e => {
    const activeIdx = catButtons.findIndex(b => b.classList.contains('active'));
    const move = dir => {
      const next = catButtons[Math.min(catButtons.length - 1, Math.max(0, activeIdx + dir))];
      next?.focus();
      next?.click();
      next?.scrollIntoView({ inline: 'center', behavior: 'smooth' });
    };
    if (e.key === 'ArrowRight') { e.preventDefault(); move(1); }
    if (e.key === 'ArrowLeft') { e.preventDefault(); move(-1); }
    if (e.key === 'Home') { e.preventDefault(); catButtons[0]?.click(); }
    if (e.key === 'End') { e.preventDefault(); catButtons.at(-1)?.click(); }
  });

  /* --- SCROLL SPY --- */
  if ('IntersectionObserver' in window && sections.length) {
    const io = new IntersectionObserver(entries => {
      const visible = entries.filter(e => e.isIntersecting)
        .sort((a,b) => (b.intersectionRect.height*b.intersectionRect.width) - (a.intersectionRect.height*a.intersectionRect.width));
      if (visible[0]) setActiveCategory(visible[0].target.dataset.mainCategory);
    }, { rootMargin: '-40% 0px -55% 0px' });
    sections.forEach(s => io.observe(s));
  }

  /* --- PULL TO REFRESH --- */
  (function () {
    const header = document.getElementById('menuHeader');
    const sheet = document.getElementById('menuSheet');
    if (!header || !sheet) return;
    const THRESHOLD = 100, MAX_PULL = 160;
    let startY = 0, pulling = false, pointerId = null;

    const applyTransform = (y, instant=false) => {
      const t = instant ? 'none' : 'transform 0.35s ease';
      header.style.transition = sheet.style.transition = t;
      header.style.transform = sheet.style.transform = `translateY(${y}px)`;
    };
    const currentY = el => {
      const s = getComputedStyle(el).transform;
      if (!s || s === 'none') return 0;
      return new DOMMatrixReadOnly(s).m42;
    };
    const reset = (anim=true) => applyTransform(0, !anim);
    const triggerRefresh = () => location.reload(true);

    window.addEventListener('touchstart', e => {
      if (window.scrollY <= 0) { startY = e.touches[0].clientY; pulling = true; }
    }, { passive: true });

    window.addEventListener('touchmove', e => {
      if (!pulling) return;
      const d = e.touches[0].clientY - startY;
      if (d > 0 && window.scrollY <= 0) { e.preventDefault(); applyTransform(Math.min(MAX_PULL, d * 0.6), true); }
    }, { passive: false });

    window.addEventListener('touchend', () => {
      if (!pulling) return; pulling = false;
      currentY(header) >= THRESHOLD ? triggerRefresh() : reset();
    });

    window.addEventListener('scroll', () => { if (currentY(header) > 0 && window.scrollY > 0) reset(); }, { passive: true });
  })();

  /* --- MOBILE CATEGORY BAR --- */
  if (scrollContainer) {
    const updateArrows = () => {
      leftBtn.disabled = scrollContainer.scrollLeft <= 0;
      rightBtn.disabled = scrollContainer.scrollLeft + scrollContainer.clientWidth >= scrollContainer.scrollWidth - 10;
    };

    leftBtn?.addEventListener('click', () => scrollContainer.scrollBy({ left: -150, behavior: 'smooth' }));
    rightBtn?.addEventListener('click', () => scrollContainer.scrollBy({ left: 150, behavior: 'smooth' }));
    scrollContainer.addEventListener('scroll', updateArrows, { passive: true });
    updateArrows();

    // Link mobile buttons to main
    mobileButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const name = btn.dataset.category;
        const main = catButtons.find(b => b.dataset.category === name);
        if (main) main.click();
        else { setActiveCategory(name); showCategory(name); scrollToCategory(name); }
      });
    });

    // Sync when main clicked
    catButtons.forEach(btn => {
      btn.addEventListener('click', () => setActiveCategory(btn.dataset.category));
    });
  }
   const mobileBagButton = document.getElementById('mobileBagButton');
  const cartModal = document.getElementById('cartModal');

  if (mobileBagButton && cartModal) {
    mobileBagButton.addEventListener('click', () => {
      // Toggle show class (same behavior as desktop)
      cartModal.classList.toggle('show');
    });

    // Optional: allow modal close by clicking outside or pressing ESC
    cartModal.addEventListener('click', e => {
      if (e.target === cartModal) {
        cartModal.classList.remove('show');
      }
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') {
        cartModal.classList.remove('show');
      }
    });
  } 
});