// ============================
// CHECKOUT SCRIPT (complete, consolidated)
// ============================

// ----------------------------
// CONFIG / ENDPOINTS
// ----------------------------
const API = {
  USER: '../backend/checkout-page.php',
  CART: '../backend/get_cart.php',
  PAYMENT_METHODS: '../backend/get_payment_methods.php',
  UPDATE_CART: '../backend/update_cart.php',
  PLACE_ORDER: '../backend/place_order.php' // <-- change if your backend uses another path
};

// ----------------------------
// STATE
// ----------------------------
let availablePaymentMethods = [];
let paymentMethodsLoaded = false;

// ----------------------------
// BOOT (single DOMContentLoaded)
// ----------------------------
document.addEventListener('DOMContentLoaded', async () => {
  try {
    // Kick off independent loads in parallel
    await Promise.all([
      fetchUserData(),
      fetchCartData(),
      loadDeliveryOption(),   // selects radio + toggles UI
      loadPaymentMethods()
    ]);

    // Bind UI handlers (do after initial loads)
    bindAddressModal();
    attachDeliveryPaymentSync();
    applyPaymentMethodRules(); // ensure UI consistent
    bindPhoneEdit();
    bindPlaceOrderHandler();

    // Global cart update listener (keeps in sync with other pages)
    document.addEventListener('cart:updated', async (e) => {
      console.log('🔔 cart:updated event received');
      if (e.detail?.cart) {
        cp_renderCart(e.detail.cart || []);
        updateTotals(e.detail.totals || {});
      } else {
        await fetchCartData();
      }
    });
    document.getElementById('place-order-btn')?.addEventListener('click', (e) => {
    if (e.target.classList.contains('btn-unavailable')) {
    e.preventDefault();
    showModal('Payment unavailable for selected delivery method.', 'warning');
  }
});
  } catch (err) {
    console.error('Initialization error', err);
  }
});

// ----------------------------
// SHOW MODAL / TOAST (simple, reusable)
// ----------------------------
function showModal(message = '', type = 'info', autoClose = true, closeMs = 3000) {
  // Look for an existing modal container, otherwise create one
  let toast = document.getElementById('global-toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'global-toast';
    toast.style.position = 'fixed';
    toast.style.right = '20px';
    toast.style.top = '20px';
    toast.style.zIndex = 9999;
    toast.style.minWidth = '220px';
    toast.style.padding = '12px 16px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
    toast.style.fontFamily = 'sans-serif';
    toast.style.color = '#fff';
    toast.style.transition = 'opacity 240ms ease, transform 240ms ease';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-6px)';
    document.body.appendChild(toast);
  }

  // Styling by type
  const bg = (type === 'success') ? '#2e7d32' : (type === 'error') ? '#c62828' : '#333';
  toast.style.background = bg;
  toast.textContent = message;

  // Show
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';

  // Auto-hide
  if (autoClose) {
    clearTimeout(toast._hideTimer);
    toast._hideTimer = setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-6px)';
    }, closeMs);
  }
}

// ----------------------------
// ADDRESS MODAL HANDLING
// ----------------------------
function bindAddressModal() {
  const addressBtn = document.getElementById('edit-address');
  const modalOverlay = document.getElementById('modalOverlay');
  const addressModalForm = modalOverlay?.querySelector('form');

  if (addressBtn && modalOverlay) {
    addressBtn.addEventListener('click', (e) => {
      e.preventDefault();
      modalOverlay.style.display = 'flex';
    });
  }

  if (!addressModalForm) return;

  addressModalForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(addressModalForm);

    try {
      const resp = await fetch(addressModalForm.action, { method: 'POST', body: fd, credentials: 'same-origin' });
      const result = await resp.json();

      if (result?.success) {
        showModal(result.message || 'Address updated!', 'success');
        modalOverlay.style.display = 'none';
        // keep original redirect behavior
        setTimeout(() => (window.location.href = 'index.php?page=checkout-page'), 1000);
      } else {
        showModal(result?.error || 'Failed to save address.', 'error');
      }
    } catch (err) {
      console.error('Address save error', err);
      showModal('Error updating address.', 'error');
    }
  });
}

// ----------------------------
// PAYMENT METHODS (load + apply rules)
// ----------------------------
async function loadPaymentMethods() {
  try {
    const res = await fetch(API.PAYMENT_METHODS, { credentials: 'same-origin' });
    const data = await res.json();
    if (!data?.success) {
      console.warn('Failed to load payment methods:', data?.message);
      // still mark loaded false; UI will handle it
      paymentMethodsLoaded = false;
      return;
    }
    availablePaymentMethods = data.methods || [];
    paymentMethodsLoaded = true;
    console.log('Payment methods loaded:', availablePaymentMethods);
    applyPaymentMethodRules();
  } catch (err) {
    console.error('Error loading payment methods', err);
    paymentMethodsLoaded = false;
  }
}

function applyPaymentMethodRules() {
  const deliveryRadio = document.querySelector('input[name="delivery"]:checked');
  const deliveryMode = deliveryRadio ? deliveryRadio.value : 'home';

  const cashRadio = document.getElementById('pm-cash');
  const gcashRadio = document.getElementById('pm-gcash');
  const placeOrderBtn = document.getElementById('place-order-btn');

  if (!placeOrderBtn || (!cashRadio && !gcashRadio)) return;

  if (!paymentMethodsLoaded) {
    console.log('Waiting for payment methods to load...');
    // temporarily disable place order until we know
    // (but don't permanently block — if you want different behavior adjust here)
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = 'Loading payment...';
    return;
  }

  // default disable
  if (cashRadio) cashRadio.disabled = true;
  if (gcashRadio) gcashRadio.disabled = true;

  let hasEnabled = false;
  const prevCheckedId = document.querySelector('input[name="payment_method"]:checked')?.id;

  availablePaymentMethods.forEach((m) => {
    const method = (m.method || '').toLowerCase();
    const enabled = m.status === 'enabled';
    const allowedFor = (m.allowed_for || 'both').toLowerCase();

    const isAllowedForThisDelivery = (allowedFor === 'both' || allowedFor === deliveryMode);

    if (method === 'cash' && cashRadio && enabled && isAllowedForThisDelivery) {
      cashRadio.disabled = false;
      hasEnabled = true;
    }
    if (method === 'gcash' && gcashRadio && enabled && isAllowedForThisDelivery) {
      gcashRadio.disabled = false;
      hasEnabled = true;
    }
  });

  if (!hasEnabled) {
    placeOrderBtn.disabled = false; // keep clickable for consistent styling
    placeOrderBtn.textContent = 'Payment Unavailable';
    placeOrderBtn.classList.add('btn-unavailable');
  } else {
    placeOrderBtn.disabled = false;
    placeOrderBtn.textContent = 'Place Order';
    placeOrderBtn.classList.remove('btn-unavailable');
  }

  // cleanup selection if disabled
  const checked = document.querySelector('input[name="payment_method"]:checked');
  if (checked && checked.disabled) checked.checked = false;

  // restore previous if valid
  if (prevCheckedId && document.getElementById(prevCheckedId) && !document.getElementById(prevCheckedId).disabled) {
    document.getElementById(prevCheckedId).checked = true;
  } else {
    // auto-select first enabled
    const firstEnabled = document.querySelector('input[name="payment_method"]:not([disabled])');
    if (firstEnabled) firstEnabled.checked = true;
  }
}

// Reapply rules when delivery option changes
function attachDeliveryPaymentSync() {
  document.querySelectorAll('input[name="delivery"]').forEach((radio) => {
    radio.addEventListener('change', async (e) => {
      toggleDelivery();
      await updateDeliveryOption(e.target.value);
      applyPaymentMethodRules();
    });
  });
}

// ----------------------------
// FETCH USER DATA
// ----------------------------
async function fetchUserData() {
  try {
    const res = await fetch(API.USER, { method: 'GET', credentials: 'same-origin' });
    const payload = await res.json();
    if (!payload?.success) {
      console.error('API error fetching user:', payload?.message);
      return;
    }
    populateFields(payload.data || {});
  } catch (err) {
    console.error('Fetch user error', err);
  }
}

function populateFields(data = {}) {
  try {
    const fullNameEl = document.getElementById('full-name');
    const phoneEl = document.getElementById('phone');
    const addressEl = document.getElementById('full-address');
    const noteEl = document.getElementById('note');

    const fullName = [data.first_name, data.last_name].filter(Boolean).join(' ');
    if (fullNameEl) fullNameEl.value = fullName;
    if (phoneEl) phoneEl.value = data.phone_number ?? '';
    if (noteEl) noteEl.value = data.note_to_rider ?? '';

    const barangay = data.barangay ? `Barangay ${data.barangay}` : '';
    const fullAddress = [
      data.street_address,
      barangay,
      data.city_name,
      data.province_name,
      data.region_name
    ].filter(Boolean).join(', ');

    if (addressEl) addressEl.value = fullAddress;
  } catch (err) {
    console.error('populateFields error', err);
  }
}

// ----------------------------
// CART RENDER + FETCH
// ----------------------------
function cp_renderCart(items = []) {
  const container = document.getElementById('order-items');
  if (!container) return;

  container.innerHTML = '';

  if (!items || items.length === 0) {
    container.innerHTML = '<p>Your cart is empty. Redirecting to menu...</p>';
    setTimeout(() => window.location.replace('/Leilife/public/index.php?page=menu'), 2000);
    return;
  }

  items.forEach((item) => {
    const imgSrc = item.product_picture ? `../public/products/${item.product_picture}` : '../public/assets/no-image.png';
    const finalPrice = Number(item.final_price ?? 0);
    const qty = Number(item.quantity ?? 0);
    const total = (finalPrice * qty).toFixed(2);

    const el = document.createElement('div');
    el.className = 'order-item';
    el.style.display = 'flex';
    el.style.alignItems = 'center';
    el.style.marginBottom = '10px';

    el.innerHTML = `
      <img src="${imgSrc}" alt="${escapeHtml(item.product_name || '')}" style="width:60px;height:60px;object-fit:cover;margin-right:10px;" />
      <div style="flex:1;">
        <p style="margin:0;font-weight:500;">${escapeHtml(item.product_name || '')}${item.size ? ' (' + escapeHtml(item.size) + ')' : ''}</p>
        <small>₱${finalPrice.toFixed(2)} × ${qty}${item.flavor_names ? '<br>Flavors: ' + escapeHtml(item.flavor_names) : ''}</small>
      </div>
      <div style="font-weight:600;">₱${total}</div>
    `;
    container.appendChild(el);
  });
}

async function fetchCartData() {
  try {
    const res = await fetch(API.CART, { method: 'GET', credentials: 'same-origin' });
    const payload = await res.json();
    console.log('Cart payload:', payload);

    if (!payload?.success) {
      console.error('Cart error:', payload?.message);
      return;
    }
    cp_renderCart(payload.cart || []);
    updateTotals(payload.totals || {});
  } catch (err) {
    console.error('Cart fetch error', err);
  }
}

// ----------------------------
// UPDATE TOTALS (subtotal/delivery/total)
// ----------------------------
function updateTotals(totals = {}) {
  try {
    const subtotalEl = document.getElementById('subtotal');
    const deliveryFeeEl = document.getElementById('delivery-fee');
    const totalEl = document.getElementById('total');

    // Accept either {subtotal, delivery_fee, total} or {subtotal, delivery_fee, grand_total}
    const subtotal = Number(totals.subtotal ?? totals.sub_total ?? 0);
    const deliveryFee = Number(totals.delivery_fee ?? totals.delivery ?? 0);
    const total = Number(totals.total ?? totals.grand_total ?? subtotal + deliveryFee);

    if (subtotalEl) subtotalEl.textContent = `₱${subtotal.toFixed(2)}`;
    if (deliveryFeeEl) deliveryFeeEl.textContent = `₱${deliveryFee.toFixed(2)}`;
    if (totalEl) totalEl.textContent = `₱${total.toFixed(2)}`;
  } catch (err) {
    console.error('updateTotals error', err);
  }
}

// ----------------------------
// DELIVERY OPTION SYNC
// ----------------------------
function toggleDelivery() {
  const pickup = document.getElementById('pickup-options');
  const home = document.getElementById('home-options');
  const selected = document.querySelector('input[name="delivery"]:checked');

  if (!pickup || !home) return;

  if (!selected) {
    pickup.style.display = 'none';
    home.style.display = 'none';
    return;
  }

  if (selected.value === 'pickup') {
    pickup.style.display = 'block';
    home.style.display = 'none';
    const pickupRadio = pickup.querySelector('input[name="pickup_location"]');
    if (pickupRadio) pickupRadio.checked = true;
  } else {
    pickup.style.display = 'none';
    home.style.display = 'block';
  }
}

async function loadDeliveryOption() {
  try {
    const res = await fetch(API.CART, { credentials: 'same-origin' });
    const data = await res.json();

    let radioValue = 'home'; // default to delivery/home
    if (data?.success && data.option_type) {
      const mode = String(data.option_type).toLowerCase(); // 'delivery' or 'pickup'
      radioValue = (mode === 'delivery') ? 'home' : 'pickup';
    }

    const radio = document.querySelector(`input[name="delivery"][value="${radioValue}"]`);
    if (radio) {
      radio.checked = true;
      toggleDelivery();
    }
  } catch (err) {
    console.error('Failed to load delivery option', err);
  }
}

async function updateDeliveryOption(newMode) {
  const dbValue = newMode === 'home' ? 'delivery' : 'pickup';
  try {
    const res = await fetch(API.UPDATE_CART, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ action: 'update_option_type', option_type: dbValue })
    });
    const data = await res.json();
    if (!data?.success) {
      console.error('Failed to update delivery option:', data?.message);
      return;
    }
    console.log(`Delivery option updated to ${dbValue}`);
    if (data.totals) updateTotals(data.totals);
  } catch (err) {
    console.error('Network error updating delivery option', err);
  }
}

// ----------------------------
// PHONE EDIT BINDING
// ----------------------------
function bindPhoneEdit() {
  const btn = document.getElementById('phone-edit-btn');
  const phone = document.getElementById('phone');
  if (!btn || !phone) return;

  btn.addEventListener('click', async () => {
    const isReadonly = phone.hasAttribute('readonly');
    if (isReadonly) {
      // enable editing
      phone.removeAttribute('readonly');
      phone.style.background = '#fff';
      btn.textContent = 'Save';
      phone.focus();
      return;
    }

    // Save path
    const phoneValue = phone.value.trim();
    const phonePattern = /^(09)\d{9}$/;

    if (!phoneValue) {
      showModal('Please enter your phone number.', 'error');
      phone.focus();
      return;
    }
    if (!phonePattern.test(phoneValue)) {
      showModal('Invalid phone number format. Should be 11 digits and start with 09.', 'error');
      phone.focus();
      return;
    }

    const payload = { action: 'update_phone', phone_number: phoneValue };

    try {
      const res = await fetch(API.USER, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();

      if (data?.success) {
        phone.setAttribute('readonly', true);
        phone.style.background = '#f5f5f5';
        btn.textContent = 'Edit';
        showModal('Phone number updated successfully!', 'success');
      } else {
        showModal('Failed to update phone: ' + (data?.message || ''), 'error');
      }
    } catch (err) {
      console.error('Network error updating phone', err);
      showModal('Network error while updating phone', 'error');
    }
  });
}

// ----------------------------
// PLACE ORDER HANDLER (full logic)
// ----------------------------
function bindPlaceOrderHandler() {
  const placeBtn = document.getElementById('place-order-btn');
  if (!placeBtn) return;

  placeBtn.addEventListener('click', async (e) => {
    e.preventDefault();

    // Basic checks
    const placeDisabled = placeBtn.disabled;
    if (placeDisabled) {
      showModal('Cannot place order: payment unavailable or form incomplete.', 'error');
      return;
    }

    // Ensure cart is not empty by checking #order-items children
    const orderItems = document.getElementById('order-items');
    if (!orderItems || orderItems.children.length === 0) {
      showModal('Your cart is empty.', 'error');
      return;
    }

    // Validate payment method
    const paymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
    if (!paymentMethodEl || paymentMethodEl.disabled) {
      showModal('Please choose a valid payment method.', 'error');
      return;
    }
    const paymentMethod = paymentMethodEl.value;

    // Get delivery choice
    const deliveryEl = document.querySelector('input[name="delivery"]:checked');
    if (!deliveryEl) {
      showModal('Please choose delivery or pickup.', 'error');
      return;
    }

    // If address required (home/delivery) ensure there is an address
    if (deliveryEl.value === 'home') {
      const addressField = document.getElementById('full-address');
      if (!addressField || !addressField.value.trim()) {
        showModal('Please set your delivery address.', 'error');
        return;
      }
    }

    // Collect optional fields from form if present (keeps functionality if you have more inputs)
    const checkoutForm = document.getElementById('checkout-form'); // optional
    let body;
    let headers;
    let isFormData = false;

    if (checkoutForm) {
      // If developer placed a form with inputs, send its FormData (preserves server expectations)
      body = new FormData(checkoutForm);
      body.set('action', 'place_order');
      body.set('payment_method', paymentMethod);
      body.set('delivery_option', deliveryEl.value);
      isFormData = true;
    } else {
      // Fallback: JSON minimal payload
      body = JSON.stringify({
        action: 'place_order',
        payment_method: paymentMethod,
        delivery_option: deliveryEl.value,
        note: (document.getElementById('note')?.value || '').trim()
      });
      headers = { 'Content-Type': 'application/json' };
    }

    // Disable button to prevent double clicks
    placeBtn.disabled = true;
    const originalText = placeBtn.textContent;
    placeBtn.textContent = 'Placing order...';

    try {
      const res = await fetch(API.PLACE_ORDER, {
        method: 'POST',
        credentials: 'same-origin',
        headers,
        body
      });
      const result = await res.json();

      if (result?.success) {
        showModal(result.message || 'Order placed successfully!', 'success');
        // Optionally redirect if server sends a redirect or order id
        if (result.redirect) {
          setTimeout(() => (window.location.href = result.redirect), 900);
        } else {
          // reload cart & totals
          await fetchCartData();
          // small delay to show success
          setTimeout(() => location.reload(), 800);
        }
      } else {
        showModal(result?.message || 'Failed to place order.', 'error');
        // If server indicates payment unavailable (keep consistent with UI)
        if (result?.payment_unavailable) {
          applyPaymentMethodRules();
        }
      }
    } catch (err) {
      console.error('Place order error', err);
      showModal('Network error while placing order.', 'error');
    } finally {
      placeBtn.disabled = false;
      placeBtn.textContent = originalText;
    }
  });
}

// ----------------------------
// UTIL: simple HTML escape
// ----------------------------
function escapeHtml(str = '') {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
