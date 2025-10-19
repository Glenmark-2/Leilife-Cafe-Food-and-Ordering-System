// =======================
// DRIVER ORDERS FRONTEND (ENHANCED WITH MAP ROTATION)
// =======================

// --------- CONFIG ---------
const DEFAULT_CENTER = [14.5995, 120.9842]; // Manila fallback [lat,lon]
let ORS_API_KEY = "";

// =======================
// LOAD ORS KEY
// =======================
async function loadORSKey() {
  try {
    const res = await fetch("/Leilife/backend/db_script/get_key.php");
    const data = await res.json();
    ORS_API_KEY = data.ORS_API_KEY;
    console.log("Loaded ORS key:", ORS_API_KEY);
    initDriverOrders();
  } catch (err) {
    console.error("Failed to load ORS key:", err);
  }
}

function initDriverOrders() {
  console.log("Driver Orders App starting with key:", ORS_API_KEY);
}

loadORSKey();

// --------- STATE ---------
let driverCoords = null;
let mapInstance = null;
let markersGroup = null;
let routeLayer = null;
let driverMarker = null;
let customerMarker = null;
let lastHeading = 0;

// =======================
// INIT LOAD
// =======================
document.addEventListener("DOMContentLoaded", () => {
  loadDeliveredOrders();
});

// =======================
// LOAD ORDERS FROM BACKEND
// =======================
async function loadDeliveredOrders() {
  try {
    const res = await fetch("/Leilife/backend/driver/get_my_orders.php");
    const data = await res.json();
    if (!data.success) throw new Error(data.message || "Failed to load");

    const container = document.getElementById("orderList");
    container.innerHTML = "";

    if (!data.orders || data.orders.length === 0) {
      container.innerHTML = `<p class="no-orders">No orders to be delivered.</p>`;
      return;
    }

    data.orders.forEach(order => {
      const customer = (order.first_name || "") + " " + (order.last_name || "");
      const address = [order.street_address, order.barangay, order.city].filter(Boolean).join(", ");
      const number = order.phone_number || "N/A";
      const status = order.status || "unknown";

      const itemsHtml = order.items && order.items.length > 0
        ? order.items.map(it => `<li>${it.product_name} (x${it.quantity})</li>`).join("")
        : "<li>No items</li>";

      const card = document.createElement("div");
      card.className = "order-card";
      card.dataset.id = order.order_id;
      card.dataset.customer = customer;
      card.dataset.address = address;
      card.dataset.number = number;
      card.dataset.status = status;
      card.dataset.lat = order.latitude || "";
      card.dataset.lng = order.longitude || "";
      card.dataset.items = JSON.stringify(order.items);
      card.dataset.total = order.total;

      card.innerHTML = `
        <p><b>Customer:</b> ${customer}</p>
        <p><b>Address:</b> ${address}</p>
        <p><b>Contact:</b> ${number}</p>
        <span class="badge ${status}">${status}</span>
        <h4>Items:</h4>
        <ul>${itemsHtml}</ul>
        <a href="#" class="btn-view">👁 View</a>
      `;
      container.appendChild(card);
    });

    bindOrderButtons();

  } catch (err) {
    console.error("Order load error:", err);
    document.getElementById("orderList").innerHTML = `<p class="error">Error loading orders.</p>`;
  }
}

// =======================
// BIND VIEW BUTTONS
// =======================
function bindOrderButtons() {
  document.querySelectorAll(".btn-view").forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      const card = btn.closest(".order-card");
      openOrderModal(card);
    };
  });
}

// =======================
// OPEN ORDER MODAL
// =======================
function openOrderModal(card) {
  const id = card.dataset.id;
  const customer = card.dataset.customer;
  const address = card.dataset.address;
  const number = card.dataset.number;
  const status = card.dataset.status;
  const items = JSON.parse(card.dataset.items || "[]");
  const total = card.dataset.total;

  const html = `
    <p><b>Customer:</b> ${customer}</p>
    <p><b>Address:</b> ${address}</p>
    <p><b>Contact:</b> ${number}</p>
    <p><b>Status:</b> <span class="badge ${status.toLowerCase()}">${status}</span></p>
    <h4>Items:</h4>
    <ul>${items.map(i => `<li>${i.product_name} (x${i.quantity})</li>`).join("")}</ul>
    <p><b>Total:</b> ₱${total}</p>
    <a href="tel:${number}" class="contact-btn">📞 Contact Customer</a>
    <button class="complete-btn" data-id="${id}">✅ Mark as Delivered</button>
  `;

  document.getElementById("modalBody").innerHTML = html;
  document.getElementById("orderModal").style.display = "flex";

// ✅ Fix Leaflet blank issue when inside modal
setTimeout(() => {
  if (mapInstance) mapInstance.invalidateSize();
}, 400);


  const markBtn = document.querySelector(".complete-btn");
  markBtn.addEventListener("click", async () => {
    if (!confirm("Mark this order as delivered?")) return;

    try {
      const res = await fetch("/Leilife/backend/driver/mark_delivered.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: id })
      });
      const data = await res.json();
    if (data.success) {
      alert(data.message);
      document.getElementById("orderModal").style.display = "none";
    
      // 🔥 remove the order card immediately
      const cardEl = document.querySelector(`.order-card[data-id="${id}"]`);
      if (cardEl) cardEl.remove();
    
      // (optional) still reload to refresh data
      loadDeliveredOrders();
    } else {
        alert("❌ " + data.message);
      }
    } catch (err) {
      alert("⚠️ Error: " + err.message);
    }
  });

  showRouteFromCard(card);
}

// =======================
// MODAL CLOSE
// =======================
document.querySelector(".close-btn").onclick = () => { 
  document.getElementById("orderModal").style.display = "none"; 
};
window.onclick = (e) => { 
  if (e.target.id === "orderModal") document.getElementById("orderModal").style.display = "none"; 
};

// =======================
// DRIVER MARKER INIT
// =======================
ensureMapReady();

const icon = L.icon({
  iconUrl: "/Leilife/public/assests/rider.png", // use a motorcycle or arrow image
  iconSize: [50, 50],
  iconAnchor: [25, 25]
});
driverMarker = L.marker(mapInstance.getCenter(), { icon, rotationAngle: 0 }).addTo(mapInstance);
driverMarker.setZIndexOffset(9999);

// =======================
// DRIVER COORDS HANDLING WITH HEADING
// =======================
if (navigator.geolocation) {
  navigator.geolocation.watchPosition(pos => {
    const { latitude, longitude, heading } = pos.coords;
    driverCoords = [longitude, latitude];

    if (mapInstance) {
      updateDriverPosition(driverCoords);
      updateDriverHeading(heading); // ✅ rotate icon only
    }
  }, err => {
    console.warn("Geolocation error:", err.message);
    if (!driverCoords) driverCoords = [120.9842, 14.5995];
  }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 8000 });
}

// =======================
// MAP INIT & UTILS
// =======================
function ensureMapReady() {
  if (mapInstance) return mapInstance;

  mapInstance = L.map("mapContainer", { zoomControl: true })
    .setView(DEFAULT_CENTER, 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors"
  }).addTo(mapInstance);

  markersGroup = L.layerGroup().addTo(mapInstance);

  const icon = L.icon({
    iconUrl: "/Leilife/public/assets/driver-arrow.png",
    iconSize: [40, 40],
    iconAnchor: [20, 20]
  });

  driverMarker = L.marker(DEFAULT_CENTER, { icon }).addTo(mapInstance);
  driverMarker.setZIndexOffset(9999);

  // ✅ Force render correction when map becomes visible
  setTimeout(() => {
    mapInstance.invalidateSize();
  }, 500);

  return mapInstance;
}


function updateDriverPosition(coords) {
  if (!mapInstance || !driverMarker) return;
  const latlon = [coords[1], coords[0]];
  driverMarker.setLatLng(latlon);
  mapInstance.panTo(latlon, { animate: true });
}


function clearRoute() {
  if (routeLayer) { routeLayer.remove(); routeLayer = null; }
  if (customerMarker) { customerMarker.remove(); customerMarker = null; }
}

// =======================
// DRIVER HEADING & MAP ROTATION
// =======================
function updateDriverHeading(heading) {
  if (!driverMarker || heading == null || isNaN(heading)) return;

  // Smooth transition to avoid jerky rotation
  const delta = ((heading - lastHeading + 540) % 360) - 180;
  lastHeading = (lastHeading + delta * 0.3) % 360;

  const iconEl = driverMarker.getElement();
  if (iconEl) {
    iconEl.style.transition = "transform 0.3s linear";
    iconEl.style.transformOrigin = "center center";
    iconEl.style.transform = `rotate(${lastHeading}deg)`;
  }
}


// =======================
// SHOW ROUTE (ORS)
// =======================
async function showRouteFromCard(card) {
  ensureMapReady();
  clearRoute();

  const dc = driverCoords;
  if (!dc) {
    alert("Driver GPS not available.");
    return;
  }

  const latStr = card.dataset.lat;
  const lngStr = card.dataset.lng;
  let custCoords = null;

  if (latStr && lngStr) {
    const lat = parseFloat(latStr), lng = parseFloat(lngStr);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      custCoords = [lng, lat];
    }
  }

  if (!custCoords) {
    document.getElementById("directionsPanel").innerText =
      "⚠️ This order has no saved coordinates.";
    return;
  }

  document.getElementById("directionsPanel").innerText = "Requesting route...";

  try {
    const body = { coordinates: [dc, custCoords], instructions: true };
    const routeRes = await fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
      method: "POST",
      headers: {
        "Authorization": "Bearer " + ORS_API_KEY,
        "Content-Type": "application/json"
      },
      body: JSON.stringify(body)
    });

    if (!routeRes.ok) throw new Error("ORS failed: " + routeRes.status);
    const routeJson = await routeRes.json();

    let lineCoords = null;
    let steps = [];

    if (routeJson.features && routeJson.features.length && routeJson.features[0].geometry) {
      lineCoords = routeJson.features[0].geometry.coordinates;
      steps = routeJson.features[0].properties?.segments?.[0]?.steps || [];
    }

    if (!lineCoords || !lineCoords.length) throw new Error("Route has no coordinates");

    const latlngs = lineCoords.map(c => [c[1], c[0]]);
    routeLayer = L.polyline(latlngs, { color: "#1976d2", weight: 6, opacity: 0.9 }).addTo(mapInstance);

    customerMarker = L.marker([custCoords[1], custCoords[0]]).addTo(markersGroup).bindPopup("Delivery Location");
    mapInstance.fitBounds(routeLayer.getBounds(), { padding: [40, 40] });

    let html = "<h4>Directions</h4><ol>";
    steps.forEach(s => {
      const dist = s.distance ? ` — ${Math.round(s.distance)} m` : "";
      html += `<li>${s.instruction}${dist}</li>`;
    });
    html += "</ol>";
    document.getElementById("directionsPanel").innerHTML = html;

  } catch (err) {
    console.error("Routing error:", err);
    document.getElementById("directionsPanel").innerText = "Error loading route.";
  }
}
