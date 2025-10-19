<div class="cart-modal" data-stick-below="#site-header">
  <div class="cart-header">
    <div class="delivery-info">
      <span class="icon">🛵</span>
      <span>Deliver <small>(Today, 6:00pm)</small></span>
      <button class="change-btn">Change</button>
    </div>
    <h2>My Cart</h2>
  </div>

  <div class="cart-items">
    <div class="cart-item">
      <button class="delete-btn">🗑</button>
      <span class="qty">1</span>
      <button class="add-btn">+</button>
      <span class="item-name">1pc. Original Chicken and Rice</span>
      <span class="price">₱123.00</span>
    </div>
  </div>

  <div class="cart-footer">
    <div class="summary">
      <div class="row"><span>Sub Total</span><span>₱99</span></div>
      <div class="row"><span>Delivery Fee</span><span>₱30</span></div>
      <div class="row total"><strong>Total</strong><strong>₱129</strong></div>
    </div>
    <button class="checkout-btn">Proceed To Check Out</button>
  </div>
</div>

<style>
:root { --header-offset: 0px; }

.cart-modal{
  position: fixed;
  right: 0;
  top: var(--header-offset);                /* always below header */
  height: calc(100vh - var(--header-offset));
  width: 380px;
  max-width: 100%;
  background: #f5f5f5;
  box-shadow: -4px 0 12px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  z-index: 9999;
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
  overflow: hidden;                         /* internal areas handle scroll */
}

/* Header stays visible while items scroll */
.cart-header{
  padding: 16px;
  border-bottom: 1px solid #ddd;
  background: #f5f5f5;
}

.cart-header h2{
  text-align:center; margin-top:10px; font-weight:700;
}

.delivery-info{ display:flex; justify-content:space-between; align-items:center; }
.change-btn{ background:#2f3c3d; color:#fff; border:none; padding:4px 10px; border-radius:8px; font-size:12px; }

/* Only the items list scrolls */
.cart-items{ flex:1; padding:16px; overflow-y:auto; }

.cart-item{ display:flex; align-items:center; gap:8px; margin-bottom:12px; }
.cart-item .item-name{ flex:1; }

.cart-footer{
  background:#b0a472; padding:16px; border-top:1px solid #ddd;
}
.summary .row{ display:flex; justify-content:space-between; margin-bottom:6px; }
.summary .total{ font-size:1.1em; }
.checkout-btn{
  width:100%; padding:12px; background:#2f3c3d; color:#fff; border:none;
  border-radius:20px; font-size:16px; margin-top:12px; cursor:pointer;
}
</style>

<script>
/* Dynamically set the offset to your header's height.
   Change the selector via data-stick-below on .cart-modal (default: '#site-header'). */
(function () {
  const modal = document.querySelector('.cart-modal');
  const sel = modal?.getAttribute('data-stick-below') || '#site-header';
  const header = document.querySelector(sel) || document.querySelector('.site-header') || document.querySelector('header');

  function setOffset() {
    const h = header ? Math.round(header.getBoundingClientRect().height) : 0;
    document.documentElement.style.setProperty('--header-offset', h + 'px');
  }

  setOffset();
  window.addEventListener('resize', setOffset);
  if ('ResizeObserver' in window) {
    const ro = new ResizeObserver(setOffset);
    ro.observe(document.documentElement);
    if (header) ro.observe(header);
  }
})();
</script>
