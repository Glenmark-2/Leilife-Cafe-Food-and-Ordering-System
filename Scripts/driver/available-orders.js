// =======================
// AVAILABLE ORDERS FRONTEND
// =======================

// =======================
// INIT LOAD
// =======================
document.addEventListener("DOMContentLoaded", () => {
  loadAvailableOrders();
});

// =======================
// LOAD ORDERS FROM BACKEND
// =======================
async function loadAvailableOrders() {
  const container = document.getElementById("availableOrderList");
  try {
    const res = await fetch("/Leilife/backend/driver/get_available_orders.php");
    const data = await res.json();

    container.innerHTML = "";

    if (!data.success) {
      container.innerHTML = `<p class="error">${data.message || "Failed to load orders."}</p>`;
      return;
    }

    if (!data.orders || data.orders.length === 0) {
      container.innerHTML = `<p class="no-orders">No available deliveries right now.</p>`;
      return;
    }

    data.orders.forEach(order => {
      const customer = `${order.first_name || ""} ${order.last_name || ""}`.trim();
      const address = [order.street_address, order.barangay, order.city].filter(Boolean).join(", ");
      const number = order.phone_number || "N/A";
      const total = parseFloat(order.total || 0).toFixed(2);
      const itemsHtml = order.items && order.items.length
        ? order.items.map(it => `<li>${it.product_name} (x${it.quantity})</li>`).join("")
        : "<li>No items</li>";

      const card = document.createElement("div");
      card.className = "order-card";
      card.dataset.id = order.order_id;
      card.dataset.customer = customer;
      card.dataset.address = address;
      card.dataset.number = number;
      card.dataset.total = total;
      card.dataset.items = JSON.stringify(order.items);

      card.innerHTML = `
        <p><b>Customer:</b> ${customer}</p>
        <p><b>Address:</b> ${address}</p>
        <p><b>Contact:</b> ${number}</p>
        <p><b>Total:</b> ₱${total}</p>
        <a href="#" class="btn-view">👁 View</a>
      `;
      container.appendChild(card);
    });

    bindViewButtons();
  } catch (err) {
    console.error("Error loading available orders:", err);
    container.innerHTML = `<p class="error">Failed to load available orders.</p>`;
  }
}

// =======================
// BIND VIEW BUTTONS
// =======================
function bindViewButtons() {
  document.querySelectorAll(".btn-view").forEach(btn => {
    btn.onclick = e => {
      e.preventDefault();
      const card = btn.closest(".order-card");
      openAvailableModal(card);
    };
  });
}

// =======================
// OPEN MODAL
// =======================
function openAvailableModal(card) {
  const id = card.dataset.id;
  const customer = card.dataset.customer;
  const address = card.dataset.address;
  const number = card.dataset.number;
  const total = card.dataset.total;
  const items = JSON.parse(card.dataset.items || "[]");

  const html = `
    <p><b>Customer:</b> ${customer}</p>
    <p><b>Address:</b> ${address}</p>
    <p><b>Contact:</b> ${number}</p>
    <h4>Items:</h4>
    <ul>${items.map(i => `<li>${i.product_name} (x${i.quantity})</li>`).join("")}</ul>
    <p><b>Total:</b> ₱${total}</p>
    <a href="tel:${number}" class="contact-btn">📞 Contact</a>
    <button class="complete-btn" data-id="${id}">✅ Claim Delivery</button>
  `;

  document.getElementById("availableModalBody").innerHTML = html;
  document.getElementById("availableOrderModal").style.display = "flex";

  // bind claim button
  document.querySelector(".complete-btn").onclick = async () => {
    if (!confirm("Are you sure you want to claim this order?")) return;
    await claimOrder(id);
  };
}

// =======================
// CLAIM ORDER
// =======================
async function claimOrder(orderId) {
  try {
    const res = await fetch("/Leilife/backend/driver/assign_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order_id: orderId }),
    });
    const data = await res.json();

    if (data.success) {
      alert("✅ Order claimed successfully!");
      window.location.href = "/Leilife/public/driver.php?page=driver";
    } else {
      alert("❌ " + (data.message || "Failed to claim order"));
    }
  } catch (err) {
    alert("⚠️ Error claiming order: " + err.message);
  } finally {
    document.getElementById("availableOrderModal").style.display = "none";
    loadAvailableOrders();
  }
}

// =======================
// CLOSE MODAL
// =======================
document.querySelectorAll(".close-btn").forEach(btn => {
  btn.onclick = () => {
    btn.closest(".modal").style.display = "none";
  };
});

window.onclick = e => {
  if (e.target.id === "availableOrderModal") {
    document.getElementById("availableOrderModal").style.display = "none";
  }
};
