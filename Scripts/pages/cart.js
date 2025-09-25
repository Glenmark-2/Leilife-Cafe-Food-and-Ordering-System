// ===============================
// Delivery / Pickup toggle
// ===============================
const changeBtn = document.getElementById("change");
let mode = document.getElementById("mode");
let motor = document.getElementById("motor");

if (changeBtn) {
    changeBtn.addEventListener("click", () => {
        if (mode.textContent === "Delivery") {
            motor.src = "../public/assests/walk.png";
            mode.textContent = "Pick up";
        } else {
            motor.src = "../public/assests/motorbike.png";
            mode.textContent = "Delivery";
        }
    });
}

// ===============================
// Modal notification (success, error, warning)
// ===============================
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

// ===============================
// Cart state
// ===============================
let cart = [];

// ===============================
// Fetch cart from backend
// ===============================
async function fetchCart() {
    try {
        const res = await fetch("../backend/get_cart.php");
        const data = await res.json();
        if (data.success) {
            cart = data.cart;
            renderCart();
            updateTotals(data.totals);

            // 🔔 Notify other pages/components
            document.dispatchEvent(new CustomEvent("cart:updated", {
                detail: { cart: cart, totals: data.totals }
            }));
            toggleCheckoutButton();
        }
    } catch (err) {
        console.error("Failed to fetch cart:", err);
    }
}

// ===============================
// Render cart items
// ===============================
function renderCart() {
    const midDiv = document.getElementById("mid-div");
    midDiv.innerHTML = "";

    if (!cart || cart.length === 0) {
        midDiv.innerHTML = `
            <div style="display:flex; justify-content:center; align-items:center; height:80px; width:100%;">
                <p style="color:gray; margin:0;">Your cart is empty</p>
            </div>
        `;
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
    toggleCheckoutButton(); // ✅ check after rendering
}

// ===============================
// Update item quantity
// ===============================
function changeItemQty(index, change) {
    const item = cart[index];
    const newQty = item.quantity + change;
    if (newQty < 1) return;

    item.quantity = newQty;
    renderCart();

    updateSession({
        action: "update",
        cart_item_id: item.cart_item_id,
        quantity: newQty
    });

    // 🔔 Notify locally right away
    document.dispatchEvent(new CustomEvent("cart:updated", {
        detail: { cart: cart }
    }));
    toggleCheckoutButton(); // ✅ check after rendering
}

// ===============================
// Remove item
// ===============================
function removeItem(index) {
    const removedItem = cart[index];
    cart.splice(index, 1);
    renderCart();

    updateSession({
        action: "remove",
        cart_item_id: removedItem.cart_item_id
    });

    if (removedItem) {
        showModal(`Removed "${removedItem.product_name}" from cart`, "success");
    }

    // 🔔 Notify listeners
    document.dispatchEvent(new CustomEvent("cart:updated", {
        detail: { cart: cart }
    }));
    toggleCheckoutButton(); // ✅ check after rendering
}
document.addEventListener("DOMContentLoaded", () => {
    if (window.currentPage === "checkout-page") {
        // hide all texts inside second-div
        const contents = document.getElementsByClassName("second-div-content");
        Array.from(contents).forEach(el => {
            el.style.display = "none";
        });

        // hide the checkout button wrapper too
        const checkoutWrapper = document.querySelector(".checkout-wrapper");
        if (checkoutWrapper) {
            checkoutWrapper.style.display = "none";
        }
    }
});

function toggleCheckoutButton() {
    const checkoutBtn = document.getElementById("check-out");
    if (!checkoutBtn) return; // not on all pages

    if (!cart || cart.length === 0) {
        checkoutBtn.disabled = true;
        checkoutBtn.classList.add("disabled"); // optional for styling
    } else {
        checkoutBtn.disabled = false;
        checkoutBtn.classList.remove("disabled");
    }
}

// ===============================
// Update totals (subtotal, fee, total)
// ===============================
function updateTotals(totals) {
    if (!totals) return;
    document.getElementById("subtotal").textContent = `₱${totals.subtotal.toFixed(2)}`;
    document.getElementById("delivery-fee").textContent = `₱${totals.delivery_fee.toFixed(2)}`;
    document.getElementById("total").textContent = `₱${totals.total.toFixed(2)}`;
}

// ===============================
// Sync session cart with backend
// ===============================
function updateSession(payload) {
    fetch("../backend/update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateTotals(data.totals);

            // 🔔 Notify with fresh totals
            document.dispatchEvent(new CustomEvent("cart:updated", {
                detail: { cart: cart, totals: data.totals }
            }));
        } else {
            console.error("Failed to sync cart:", data.message);
        }
    })
    .catch(err => console.error("Error updating cart:", err));
}

// ===============================
// Init
// ===============================
fetchCart();
