<?php
// keep your original server-side includes exactly as before
include "../components/buttonTemplate.php";
$appData->loadCategories();
$appData->adminloadProducts(false);
include '../components/order_button.php';
?>
<!-- Yellow Pull-to-Reveal Header (kept) -->
<div class="menu-header" id="menuHeader">
  <div class="menu-header-inner">
    <h1 class="menu-title">Our Menu</h1>
    <p class="menu-tagline">Discover delicious meals crafted with love and served fresh daily.</p>
  </div>
</div>

<!-- Menu Sheet -->
<div class="menu" id="menuSheet">

  <!-- Main Category Buttons -->
  <div class="category-buttons category-bar" id="categoryButtons">
    <?php
    $mainCategories = [];
    foreach ($appData->categories as $cat) {
      $mainCatName = $cat['main_category_name'] ?? '';
      if ($mainCatName && !in_array($mainCatName, $mainCategories)) {
        $mainCategories[] = $mainCatName;
        echo createButton(
          45,                
          160,               
          $mainCatName,      
          strtolower(str_replace(' ', '-', $mainCatName)), 
          15,                
          "button",          
          ["data-category" => $mainCatName] 
        );
      }
    }
    ?>
  </div>

  <!-- Subcategories & Products -->
  <?php foreach ($appData->categories as $cat): ?>
    <?php
      $mainCatName = $cat['main_category_name'] ?? '';
      $categoryName = $cat['category_name'] ?? '';
    ?>
    <div class="category-section" data-main-category="<?= htmlspecialchars($mainCatName) ?>">
      <div class="category-title">
        <?= htmlspecialchars(ucwords(str_replace('_', ' ', $categoryName))) ?>
      </div>

      <!-- Left/Right Slide Buttons
      <button class="slide-btn slide-left">&#10094;</button>
      <button class="slide-btn slide-right">&#10095;</button> -->

      <div class="menu-cards">
        <?php foreach ($appData->products as $product): ?>
          <?php if (($product['category_name'] ?? '') === $categoryName): ?>
            <?php
              $name  = $product['product_name'] ?? '';
              $price = $product['product_price'] ?? 0;
              $image = !empty($product['product_picture'] ?? '')
                ? "../public/products/" . trim($product['product_picture'])
                : "../public/assests/image-43.png";

              include '../partials/menu-card.php';
            ?>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<script>
/* Combined enhancement script:
   - category tab keyboard nav & aria
   - scroll-spy highlights
   - smooth scroll on click
   - preserves your pull-to-refresh behavior
*/
document.addEventListener('DOMContentLoaded', () => {
  const catBar = document.getElementById('categoryButtons');
  const catButtons = Array.from(catBar ? catBar.querySelectorAll('button[data-category]') : []);
  const sections = Array.from(document.querySelectorAll('.category-section'));

  catButtons.forEach((btn, idx) => {
    btn.setAttribute('role', 'tab');
    btn.setAttribute('aria-selected', 'false');
    btn.setAttribute('tabindex', idx === 0 ? '0' : '-1');
  });

  (function showDefaultMainCategory() {
    if (!catButtons.length) return;
    const defaultMain = catButtons[0].dataset.category || '';
    if (defaultMain) {
      sections.forEach(s => {
        s.style.display = (s.dataset.mainCategory === defaultMain) ? '' : 'none';
      });
      setActiveButton(catButtons[0], false);
    }
  })();

  function setActiveButton(btn, focus = true) {
    catButtons.forEach(b => {
      b.classList.toggle('active', b === btn);
      b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
      b.setAttribute('tabindex', b === btn ? '0' : '-1');
    });
    if (focus) try { btn.focus({preventScroll: true}); } catch(e){}
  }

  catButtons.forEach(btn => {
    btn.addEventListener('click', (ev) => {
      ev.preventDefault();
      const main = btn.dataset.category || '';
      if (!main) return;
      sections.forEach(s => { s.style.display = (s.dataset.mainCategory === main) ? '' : 'none'; });
      setActiveButton(btn, false);
      const target = sections.find(s => s.dataset.mainCategory === main);
      if (target) {
        const headerOffset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height')) || 56;
        const top = target.getBoundingClientRect().top + window.scrollY - (headerOffset + 8);
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });

    btn.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        btn.click();
      }
    });
  });

  if (catBar) {
    catBar.addEventListener('keydown', (e) => {
      const activeIndex = catButtons.findIndex(b => b.getAttribute('tabindex') === '0');
      if (e.key === 'ArrowRight') {
        e.preventDefault();
        const next = catButtons[Math.min(catButtons.length - 1, Math.max(0, activeIndex + 1))];
        if (next) setActiveButton(next);
        next?.scrollIntoView({ inline: 'center', behavior: 'smooth' });
      } else if (e.key === 'ArrowLeft') {
        e.preventDefault();
        const prev = catButtons[Math.max(0, activeIndex - 1)];
        if (prev) setActiveButton(prev);
        prev?.scrollIntoView({ inline: 'center', behavior: 'smooth' });
      } else if (e.key === 'Home') {
        e.preventDefault();
        setActiveButton(catButtons[0]);
        catButtons[0].scrollIntoView({ inline: 'center', behavior: 'smooth' });
      } else if (e.key === 'End') {
        e.preventDefault();
        setActiveButton(catButtons[catButtons.length - 1]);
        catButtons[catButtons.length - 1].scrollIntoView({ inline: 'center', behavior: 'smooth' });
      }
    });
  }

  if ('IntersectionObserver' in window && sections.length) {
    const options = { root: null, rootMargin: '-40% 0px -55% 0px', threshold: 0 };
    const io = new IntersectionObserver((entries) => {
      const visible = entries.filter(e => e.isIntersecting).sort((a,b) => {
        return (b.intersectionRect.height * b.intersectionRect.width) - (a.intersectionRect.height * a.intersectionRect.width);
      });
      if (visible.length) {
        const main = visible[0].target.dataset.mainCategory;
        const btn = catButtons.find(b => b.dataset.category === main);
        if (btn) setActiveButton(btn, false);
      }
    }, options);
    sections.forEach(s => io.observe(s));
  } else {
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const headerOffset = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--header-height')) || 56;
        let closest = null;
        let closestDelta = Infinity;
        sections.forEach(s => {
          const rect = s.getBoundingClientRect();
          const delta = Math.abs(rect.top - headerOffset - 8);
          if (delta < closestDelta) { closestDelta = delta; closest = s; }
        });
        if (closest) {
          const main = closest.dataset.mainCategory;
          const btn = catButtons.find(b => b.dataset.category === main);
          if (btn) setActiveButton(btn, false);
        }
        ticking = false;
      });
    }, { passive: true });
  }

  (function () {
    const header = document.getElementById('menuHeader');
    const sheet = document.getElementById('menuSheet');
    if (!header || !sheet) return;
    const THRESHOLD = 100;
    const MAX_PULL = 160;
    let startY = 0; let pulling = false; let pointerId = null;

    function applyTransform(y, immediate = false) {
      const t = immediate ? 'none' : 'transform 0.35s ease';
      header.style.transition = t; sheet.style.transition = t;
      header.style.transform = `translateY(${y}px)`; sheet.style.transform = `translateY(${y}px)`;
    }
    function currentTranslateY(elem) {
      const s = getComputedStyle(elem).transform;
      if (!s || s === 'none') return 0;
      const m = new DOMMatrixReadOnly(s);
      return m.m42;
    }
    function setPull(distance) {
      const ratio = Math.min(1, distance / MAX_PULL);
      const eased = MAX_PULL * (1 - Math.pow(1 - ratio, 1.3));
      const pullY = Math.min(MAX_PULL, eased);
      applyTransform(pullY, true);
    }
    function reset(animated = true) { applyTransform(0, !animated); }
    function triggerRefresh() { location.reload(true); }

    window.addEventListener('touchstart', (e) => {
      if (window.scrollY <= 0 && !pulling) { startY = e.touches[0].clientY; pulling = true; }
    }, { passive: true });

    window.addEventListener('touchmove', (e) => {
      if (!pulling) return;
      const distance = e.touches[0].clientY - startY;
      if (distance > 0 && window.scrollY <= 0) {
        e.preventDefault();
        setPull(distance * 0.6);
      }
    }, { passive: false });

    window.addEventListener('touchend', () => {
      if (!pulling) return; pulling = false;
      const currentY = currentTranslateY(header);
      if (currentY >= THRESHOLD) triggerRefresh(); else reset(true);
    });

    window.addEventListener('pointerdown', (e) => {
      if (e.isPrimary && window.scrollY <= 0) { pointerId = e.pointerId; startY = e.clientY; pulling = true; try { e.target.setPointerCapture(pointerId); } catch(_){} }
    }, { passive: true });

    window.addEventListener('pointermove', (e) => {
      if (!pulling || e.pointerId !== pointerId) return;
      const distance = e.clientY - startY;
      if (distance > 0 && window.scrollY <= 0) { e.preventDefault(); setPull(distance * 0.6); }
    }, { passive: false });

    window.addEventListener('pointerup', (e) => {
      if (!pulling || e.pointerId !== pointerId) return; pulling = false; pointerId = null;
      const currentY = currentTranslateY(header);
      if (currentY >= THRESHOLD) triggerRefresh(); else reset(true);
    });

    window.addEventListener('scroll', () => { if (currentTranslateY(header) > 0 && window.scrollY > 0) reset(true); }, { passive: true });
    window.addEventListener('resize', () => reset(true));
  })();

});
// ====== SMART FLOATING CATEGORY BAR BEHAVIOR ======
(function() {
  const catBar = document.querySelector('.category-bar');
  if (!catBar) return;

  let lastScrollY = window.scrollY;
  let ticking = false;
  let isFloating = false;

  function updateBar() {
    const currentY = window.scrollY;
    const scrollingDown = currentY > lastScrollY && currentY > 80;
    const scrollingUp = currentY < lastScrollY - 10;

    if (scrollingDown && !isFloating) {
      catBar.classList.add('floating');
      isFloating = true;
    } else if (scrollingUp && isFloating && currentY < 80) {
      catBar.classList.remove('floating');
      isFloating = false;
    }

    lastScrollY = currentY;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(updateBar);
      ticking = true;
    }
  }, { passive: true });
})();
// ============================
// MOBILE CATEGORY BAR SCROLL BEHAVIOR
// ============================
(function() {
  const catBar = document.querySelector('.category-bar');
  if (!catBar) return;

  let lastScrollY = window.scrollY;
  let ticking = false;
  let hidden = false;

  function updateBar() {
    const currentY = window.scrollY;
    const delta = currentY - lastScrollY;

    // Apply only on mobile view
    if (window.innerWidth <= 768) {
      if (delta > 10 && currentY > 80 && !hidden) {
        // scrolling down -> hide
        catBar.classList.add('hide-on-scroll');
        hidden = true;
      } else if (delta < -10 && hidden) {
        // scrolling up -> show
        catBar.classList.remove('hide-on-scroll');
        hidden = false;
      }
    } else {
      // reset on larger screens
      catBar.classList.remove('hide-on-scroll');
      hidden = false;
    }

    lastScrollY = currentY;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(updateBar);
      ticking = true;
    }
  }, { passive: true });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 768) {
      catBar.classList.remove('hide-on-scroll');
    }
  });
})();

</script>
