// ===============================
// Delivery / Pickup toggle + Cart logic (fixed)
// ===============================
const changeBtn = document.getElementById("change");
const mode = document.getElementById("mode");
const motor = document.getElementById("motor");

let currentMode = "delivery"; // fallback default
let cart = [];

// -------------------------------
// Helper: Update mode UI (label + icon)
function updateModeUI(modeType) {
  const newLabel = modeType === "delivery" ? "Delivery" : "Pick up";
  const newIcon =
    modeType === "delivery"
      ? "../public/assests/motorbike.png"
      : "../public/assests/walk.png";

  if (mode) mode.textContent = newLabel;
  if (motor) motor.src = newIcon;
}

// -------------------------------
// Helper: Update totals UI (single canonical function)
// Element IDs used: #subtotal, #delivery-fee, #total
function updateTotals(totals) {
  if (!totals) return;
  const subtotalEl = document.getElementById("subtotal");
  const deliveryEl = document.getElementById("delivery-fee");
  const totalEl = document.getElementById("total");

  if (subtotalEl && totals.subtotal !== undefined) {
    subtotalEl.textContent = `₱${Number(totals.subtotal).toFixed(2)}`;
  }
  if (deliveryEl && totals.delivery_fee !== undefined) {
    deliveryEl.textContent = `₱${Number(totals.delivery_fee).toFixed(2)}`;
  }
  if (totalEl && totals.total !== undefined) {
    totalEl.textContent = `₱${Number(totals.total).toFixed(2)}`;
  }
}

// -------------------------------
// Fetch cart (items + totals + option_type) and render
async function fetchCart() {
  try {
    const res = await fetch("../backend/get_cart.php");
    const data = await res.json();
    if (!data.success) return console.error("Failed to fetch cart");

    cart = data.cart || [];
    renderCart();
    updateTotals(data.totals);
    currentMode = data.option_type || "delivery";
    updateModeUI(currentMode);

    // Notify other components
    document.dispatchEvent(new CustomEvent("cart:updated", {
      detail: { cart: cart, totals: data.totals }
    }));
    toggleCheckoutButton();
  } catch (err) {
    console.error("Failed to fetch cart:", err);
  }
}

// -------------------------------
// Render cart items (keeps your original markup)
function renderCart() {
  const midDiv = document.getElementById("mid-div");
  if (!midDiv) return;

  midDiv.innerHTML = "";

  if (!cart || cart.length === 0) {
    midDiv.innerHTML = `
      <div style="display:flex; justify-content:center; align-items:center; height:80px; width:100%;">
        <p style="color:gray; margin:0;">Your cart is empty</p>
      </div>
    `;
    toggleCheckoutButton();
    return;
  }

  cart.forEach((item, index) => {
    const itemDiv = document.createElement("div");
    itemDiv.classList.add("cart-item");

    const price = item.final_price;

    const minusOrTrash = item.quantity > 1
      ? `<button class="qty-btn" onclick="changeItemQty(${index}, -1)">−</button>`
      : `<button class="qty-btn" onclick="removeItem(${index})">
          <img src="../public/assests/trash-bin.png" alt="trash" class="trash-icon">
        </button>`;

    itemDiv.innerHTML = `
      <div class="qty-controls">
        ${minusOrTrash}
        <input type="number" value="${item.quantity}" readonly>
        <button class="qty-btn" onclick="changeItemQty(${index}, 1)">+</button>
      </div>
      <p class="product-name">
        ${item.product_name || "Unknown Product"}
        ${item.size ? ' (' + item.size + ')' : ''}
        ${item.flavor_names ? ' - ' + item.flavor_names : ''}
      </p>
      <p class="product-price">₱${(price * item.quantity).toFixed(2)}</p>
    `;

    midDiv.appendChild(itemDiv);
  });
  toggleCheckoutButton();
}

// -------------------------------
// Update item quantity (local + server)
function changeItemQty(index, change) {
  const item = cart[index];
  const currentQty = parseInt(item.quantity, 10);
  const newQty = currentQty + change;
  if (newQty < 1) return;

  item.quantity = newQty;
  renderCart();

  updateSession({
    action: "update",
    cart_item_id: item.cart_item_id,
    quantity: newQty
  });

  document.dispatchEvent(new CustomEvent("cart:updated", {
    detail: { cart: cart }
  }));
  toggleCheckoutButton();
}

// -------------------------------
// Remove item
async function removeItem(index) {
  const confirmed = await showConfirm("Are you sure you want to remove this item?");
  if (!confirmed) return;

  const removedItem = cart[index];
  cart.splice(index, 1);
  renderCart();

  updateSession({
    action: "remove",
    cart_item_id: removedItem.cart_item_id
  });

  showModal(`Item removed from the cart.`, "success");
  document.dispatchEvent(new CustomEvent("cart:updated", {
    detail: { cart: cart }
  }));
  toggleCheckoutButton();
}

// -------------------------------
// Toggle checkout button
function toggleCheckoutButton() {
  const checkoutBtn = document.getElementById("check-out");
  if (!checkoutBtn) return;
  if (!cart || cart.length === 0) {
    checkoutBtn.disabled = true;
    checkoutBtn.classList.add("disabled");
  } else {
    checkoutBtn.disabled = false;
    checkoutBtn.classList.remove("disabled");
  }
}

// -------------------------------
// Sync session cart with backend
// Accepts payload; backend returns totals in response
function updateSession(payload) {
  fetch("../backend/update_cart.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(payload)
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Backend should return totals; use them
      if (data.totals) {
        updateTotals(data.totals);
        // keep local option state in sync if backend returned it
        if (data.option_type) {
          currentMode = data.option_type;
          updateModeUI(currentMode);
        }
      } else {
        // fallback: re-fetch full cart if totals not returned
        fetchCart();
      }

      document.dispatchEvent(new CustomEvent("cart:updated", {
        detail: { cart: cart, totals: data.totals || null }
      }));
    } else {
      console.error("Failed to sync cart:", data.message);
    }
  })
  .catch(err => console.error("Error updating cart:", err));
}

// -------------------------------
// Toggle button click handler (uses update_cart.php and updates UI)
if (changeBtn) {
  changeBtn.addEventListener("click", async () => {
    // Toggle locally and update UI immediately
    currentMode = currentMode === "delivery" ? "pickup" : "delivery";
    updateModeUI(currentMode);

    try {
      const res = await fetch("../backend/update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "update_option_type",
          option_type: currentMode,
        }),
      });

      const data = await res.json();

      if (data.success) {
        // Prefer totals returned by update_cart.php (fast)
        if (data.totals) {
          updateTotals(data.totals);
        } else {
          // fallback: fetch cart again
          await fetchCart();
        }
        // ensure UI reflects confirmed option_type
        currentMode = data.option_type || currentMode;
        updateModeUI(currentMode);
      } else {
        console.error("⚠️ Failed to update option type:", data.message);
        // revert UI on failure: re-fetch authoritative cart state
        await fetchCart();
      }
    } catch (err) {
      console.error("❌ Network error updating option_type:", err);
      // on network error, re-fetch cart to ensure UI accuracy
      await fetchCart();
    }
  });
}

// -------------------------------
// Modal helpers (unchanged from your original script)
function showModal(message, type = "success", autoClose = true, duration = 2500) {
  let modal = document.getElementById("notif-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "notif-modal";
    modal.style.cssText = `
      display:none; position:fixed; z-index:10000; left:0; top:0;
      width:100%; height:100%; background:rgba(0,0,0,0.4);
      justify-content:center; align-items:center;
    `;
    modal.innerHTML = `
      <div class="notif-content" style="
        background:white; padding:20px 30px; border-radius:10px;
        text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
        min-width:250px; animation:popin .3s ease;
      ">
        <p id="notif-message" style="margin-bottom:15px; font-size:16px;"></p>
        <button id="notif-close" style="
          padding:6px 16px; border:none; border-radius:6px;
          cursor:pointer; font-size:14px; color:white;
        ">OK</button>
      </div>
    `;
    document.body.appendChild(modal);

    const style = document.createElement("style");
    style.innerHTML = `
      @keyframes popin {
        from { transform:scale(0.8); opacity:0; }
        to { transform:scale(1); opacity:1; }
      }
    `;
    document.head.appendChild(style);
  }

  document.getElementById("notif-message").textContent = message;
  const closeBtn = document.getElementById("notif-close");

  if (type === "success") closeBtn.style.background = "#4caf50";
  else if (type === "error") closeBtn.style.background = "#f44336";
  else if (type === "warning") closeBtn.style.background = "#ff9800";

  modal.style.display = "flex";

  const closeModal = () => modal.style.display = "none";
  closeBtn.onclick = closeModal;
  modal.onclick = (e) => { if (e.target === modal) closeModal(); };

  if (autoClose) setTimeout(closeModal, duration);
}

function showConfirm(message) {
  return new Promise((resolve) => {
    let modal = document.getElementById("confirm-modal");
    if (!modal) {
      modal = document.createElement("div");
      modal.id = "confirm-modal";
      modal.style.cssText = `
        display:none; position:fixed; z-index:10000; left:0; top:0;
        width:100%; height:100%; background:rgba(0,0,0,0.4);
        justify-content:center; align-items:center;
      `;
      modal.innerHTML = `
        <div class="confirm-content" style="
          background:white; padding:20px 30px; border-radius:10px;
          text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
          min-width:280px; animation:popin .3s ease;
        ">
          <p id="confirm-message" style="margin-bottom:20px; font-size:16px;"></p>
          <div style="display:flex; gap:15px; justify-content:center;">
            <button id="confirm-yes" style="
              padding:6px 16px; border:none; border-radius:6px;
              cursor:pointer; font-size:14px; color:white; background:#4caf50;
            ">Yes</button>
            <button id="confirm-no" style="
              padding:6px 16px; border:none; border-radius:6px;
              cursor:pointer; font-size:14px; color:white; background:#f44336;
            ">No</button>
          </div>
        </div>
      `;
      document.body.appendChild(modal);
    }

    document.getElementById("confirm-message").textContent = message;
    const yesBtn = document.getElementById("confirm-yes");
    const noBtn = document.getElementById("confirm-no");

    modal.style.display = "flex";

    const closeModal = () => { modal.style.display = "none"; };

    yesBtn.onclick = () => { closeModal(); resolve(true); };
    noBtn.onclick = () => { closeModal(); resolve(false); };
    modal.onclick = (e) => { if (e.target === modal) { closeModal(); resolve(false); } };
  });
}

// -------------------------------
// Misc UI modal / drag logic (keep existing DOMContentLoaded handler from your file)
// Keep your large DOMContentLoaded block exactly as-is (no changes required).
// ...

// -------------------------------
// Init: fetch cart and populate UI on load
fetchCart();