// ============================
// CONSTANTS
// ============================
const USER_API = '../backend/checkout-page.php';
const CART_API = '../backend/get_cart.php';

// ============================
// MAIN INITIALIZER
// ============================
document.addEventListener('DOMContentLoaded', async () => {
  await bindPhoneEdit();
  await fetchUserData();
  await fetchCartData();

  bindAddressModal();      // ✅ merged modal logic
  bindPlaceOrderHandler(); // ✅ merged place order + showModal validation

  // ✅ Listen for global cart updates
  document.addEventListener("cart:updated", async (e) => {
    console.log("🔔 Cart updated event received, re-fetching cart...");
    if (e.detail?.cart) {
      cp_renderCart(e.detail.cart || []);
      updateTotals(e.detail.totals || {});
    } else {
      await fetchCartData();
    }
  });
});

// ============================
// ADDRESS MODAL HANDLING
// ============================
function bindAddressModal() {
  const addressBtn = document.getElementById("edit-address");
  const modalOverlay = document.getElementById("modalOverlay");
  const addressModalForm = modalOverlay?.querySelector("form");

  if (addressBtn && modalOverlay) {
    addressBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modalOverlay.style.display = "flex";
    });
  }

  if (addressModalForm) {
    addressModalForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(addressModalForm);

      try {
        const resp = await fetch(addressModalForm.action, { method: "POST", body: fd });
        const result = await resp.json();

        if (result.success) {
          showModal(result.message || "Address updated!", "success");
          modalOverlay.style.display = "none";
          setTimeout(() => {
            window.location.href = "index.php?page=checkout-page";
          }, 1000);
        } else {
          showModal(result.error || "Failed to save address.", "error");
        }
      } catch (err) {
        showModal("Error updating address.", "error");
      }
    });
  }
}

// ============================
// PLACE ORDER HANDLER (Updated)
// ============================
function bindPlaceOrderHandler() {
  const placeOrderBtn = document.getElementById("place-order-btn");
  if (!placeOrderBtn) return;

  placeOrderBtn.addEventListener("click", async (e) => {
    e.preventDefault();

    // --- Validate delivery option ---
    const delivery = document.querySelector('input[name="delivery"]:checked');
    if (!delivery) {
      showModal("Please select a delivery option before placing your order.", "warning");
      return;
    }

    const numberInput = document.getElementById("phone");
    const phone = numberInput?.value.trim();

    if (!phone) {
      showModal("Please provide your contact number.", "warning");
      return;
    }

    // Optional: validate format (Philippine numbers example)
    const phoneRegex = /^(09|\+639)\d{9}$/;
    if (!phoneRegex.test(phone)) {
      showModal("Please enter a valid Philippine contact number (e.g. 09123456789).", "warning");
      return;
    }


    // --- Get delivery method ---
    const deliveryMethod = delivery.value; // 'pickup' or 'home'

    // --- Validate address for home delivery ---
    if (deliveryMethod === "home") {
      const address = document.getElementById("full-address").value.trim();
      if (address === "") {
        showModal("Please provide your full delivery address.", "warning");
        return;
      }
    }

    // --- Validate payment method ---
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
    if (!paymentMethod) {
      showModal("Please select a payment method before placing your order.", "warning");
      return;
    }

    // --- Prevent multiple clicks ---
    placeOrderBtn.disabled = true;
    placeOrderBtn.textContent = "Processing...";

    try {
      const response = await fetch("../backend/place_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          payment_method: paymentMethod,
          delivery_method: deliveryMethod
        })
      });

      const result = await response.json();

      if (result.success) {
        if (paymentMethod === "gcash" && result.checkout_url) {
          window.location.replace(result.checkout_url);
        } else if (result.order_number) {
          window.location.replace(`/Leilife/public/index.php?page=order-tracking&num=${encodeURIComponent(result.order_number)}`);
        } else {
          window.location.replace("/Leilife/public/index.php?page=orders");
        }
      } else {
        showModal(result.message || "Something went wrong.", "error");
      }
    } catch (err) {
      console.error("Place order error:", err);
      showModal("Error placing order.", "error");
    } finally {
      placeOrderBtn.disabled = false;
      placeOrderBtn.textContent = "Place Order";
    }
  });
}

// ============================
// FETCH USER DATA
// ============================
async function fetchUserData() {
  try {
    const res = await fetch(USER_API, { method: 'GET', credentials: 'same-origin' });
    const payload = await res.json();
    if (!payload.success) {
      console.error('API error:', payload.message);
      return;
    }
    populateFields(payload.data || {});
  } catch (err) {
    console.error('Fetch error:', err);
  }
}

function populateFields(data) {
  const fullName = [data.first_name, data.last_name].filter(Boolean).join(" ");
  document.getElementById('full-name').value = fullName;
  document.getElementById('phone').value = data.phone_number ?? '';

  const barangay = data.barangay ? `Barangay ${data.barangay}` : '';
  const fullAddress = [
    data.street_address,
    barangay,
    data.city_name,
    data.province_name,
    data.region_name
  ].filter(Boolean).join(', ');
  document.getElementById('full-address').value = fullAddress;
  document.getElementById('note').value = data.note_to_rider ?? '';
}

// ============================
// CART RENDER
// ============================
function cp_renderCart(items) {
  const container = document.getElementById('order-items');
  container.innerHTML = '';

  if (!items.length) {
    container.innerHTML = '<p>Your cart is empty. Redirecting to menu...</p>';
    setTimeout(() => {
      window.location.replace("/Leilife/public/index.php?page=menu");
    }, 2000);
    return;
  }

  items.forEach(item => {
    const div = document.createElement('div');
    div.classList.add('order-item');
    div.style.display = "flex";
    div.style.alignItems = "center";
    div.style.marginBottom = "10px";

    const imgSrc = item.product_picture 
      ? `../public/products/${item.product_picture}` 
      : '../public/assets/no-image.png';

    div.innerHTML = `
      <img src="${imgSrc}" alt="${item.product_name}" 
           style="width:60px;height:60px;object-fit:cover;margin-right:10px;" />
      <div style="flex:1;">
        <p style="margin:0;font-weight:500;">${item.product_name}${item.size ? ' ('+item.size+')' : ''}</p>
        <small>
          ₱${Number(item.final_price).toFixed(2)} × ${item.quantity}
          ${item.flavor_names ? '<br>Flavors: ' + item.flavor_names : ''}
        </small>
      </div>
      <div style="font-weight:600;">
        ₱${(Number(item.final_price) * Number(item.quantity)).toFixed(2)}
      </div>
    `;
    container.appendChild(div);
  });
}

// ============================
// FETCH CART DATA
// ============================
async function fetchCartData() {
  try {
    const res = await fetch(CART_API, { method: 'GET', credentials: 'same-origin' });
    const payload = await res.json();
    console.log("Cart payload:", payload);

    if (!payload.success) {
      console.error('Cart error:', payload.message);
      return;
    }
    cp_renderCart(payload.cart || []);
    updateTotals(payload.totals || {});
  } catch (err) {
    console.error('Cart fetch error:', err);
  }
}

// ============================
// DELIVERY TOGGLE
// ============================
function toggleDelivery() {
  const pickup = document.getElementById('pickup-options');
  const home = document.getElementById('home-options');
  const selected = document.querySelector('input[name="delivery"]:checked');

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

// ============================
// PHONE EDIT
// ============================
function bindPhoneEdit() {
  const btn = document.getElementById('phone-edit-btn');
  const phone = document.getElementById('phone');
  if (!btn || !phone) return;

  btn.addEventListener('click', async () => {
    const isReadonly = phone.hasAttribute('readonly');

    if (isReadonly) {
      // Enable edit mode
      phone.removeAttribute('readonly');
      phone.style.background = '#fff';
      btn.textContent = 'Save';
      phone.focus();
      return;
    }

    const phoneValue = phone.value.trim();
    const phonePattern = /^(09)\d{9}$/; // PH format: 09XXXXXXXXX (11 digits)

    if (phoneValue === '') {
      showModal('Please enter your phone number.', 'error');
      phone.focus();
      return;
    }

    if (!phonePattern.test(phoneValue)) {
      showModal('Invalid phone number format. It should be 11 digits and start with 09.', 'error');
      phone.focus();
      return;
    }

    const payload = {
      action: 'update_phone',
      phone_number: phoneValue
    };

    try {
      const res = await fetch(USER_API, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (data.success) {
        phone.setAttribute('readonly', true);
        phone.style.background = '#f5f5f5';
        btn.textContent = 'Edit';
        showModal('Phone number updated successfully!', 'success');
      } else {
        showModal('Failed to update phone: ' + (data.message || ''), 'error');
      }
    } catch (err) {
      showModal('Network error while updating phone', 'error');
    }
  });
}

