// DRIVER ORDERS FRONTEND — Fixed & Improved
// - Correct coords order
// - Stable marker updates
// - Auto-refresh route when deviating
// - Map rotates with heading; controls/popups counter-rotate
// - Preserves all existing features (modal, marking delivered, directions panel)

// --------- CONFIG ---------
const DEFAULT_CENTER = [14.5995, 120.9842]; // [lat, lng]
let ORS_API_KEY = "";

// thresholds (tweak as needed)
const ROUTE_UPDATE_INTERVAL_MS = 8000; // minimum interval between route requests
const ROUTE_UPDATE_DISTANCE_M = 30; // also update if moved > this meters
const PAN_DISTANCE_THRESHOLD_M = 80; // pan map when driver > this from center

// ---------- APP STATE ----------
let driverCoords = null; // [lat, lng]
let mapInstance = null;
let markersGroup = null;
let routeLayer = null;
let driverMarker = null;
let customerMarker = null;
let lastHeading = 0;
let lastRouteUpdate = 0;
let lastRouteUpdatePos = null; // [lat, lng]
let currentCard = null; // the order card currently shown (for routing)
let mapRotated = false;

// ---------- LOAD ORS API KEY ----------
async function loadORSKey() {
  try {
    const res = await fetch("/Leilife/backend/db_script/get_key.php");
    const data = await res.json();
    ORS_API_KEY = data.ORS_API_KEY;
    console.log("Loaded ORS key:", ORS_API_KEY);
    initDriverOrders();
  } catch (err) {
    console.error("Failed to load ORS key:", err);
    // Still initialize app without ORS key (will show route errors if used)
    initDriverOrders();
  }
}

function initDriverOrders() {
  console.log("Driver Orders App starting with key:", ORS_API_KEY);
}

// start
loadORSKey();

// ---------- DOM READY ----------
document.addEventListener("DOMContentLoaded", () => {
  loadDeliveredOrders();
  ensureMapReady(); // create map immediately so modal's map works smoothly
});

// ---------- LOAD ORDERS ----------
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
      const status = (order.status || "unknown").toLowerCase();

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
      card.dataset.items = JSON.stringify(order.items || []);
      card.dataset.total = order.total || 0;

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

// ---------- BIND ORDER BUTTONS ----------
function bindOrderButtons() {
  document.querySelectorAll(".btn-view").forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      const card = btn.closest(".order-card");
      openOrderModal(card);
    };
  });
}

// ---------- OPEN ORDER MODAL ----------
function openOrderModal(card) {
  currentCard = card; // store for auto-route updates
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

  // Force Leaflet to recalculate sizes (fix blank map in modal)
  setTimeout(() => {
    if (mapInstance) mapInstance.invalidateSize();
  }, 300);

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
        const cardEl = document.querySelector(`.order-card[data-id="${id}"]`);
        if (cardEl) cardEl.remove();
        loadDeliveredOrders();
        currentCard = null;
        clearRoute();
      } else {
        alert("❌ " + data.message);
      }
    } catch (err) {
      alert("⚠️ Error: " + err.message);
    }
  });

  showRouteFromCard(card);
}

// ---------- MODAL CLOSE ----------
document.querySelector(".close-btn").onclick = () => {
  document.getElementById("orderModal").style.display = "none";
  currentCard = null;
  clearRoute();
};
window.onclick = (e) => {
  if (e.target.id === "orderModal") {
    document.getElementById("orderModal").style.display = "none";
    currentCard = null;
    clearRoute();
  }
};

// ---------- MAP INIT ----------
function ensureMapReady() {
  if (mapInstance) return mapInstance;

  mapInstance = L.map("mapContainer", {
    zoomControl: true,
    center: DEFAULT_CENTER,
    zoom: 13,
    worldCopyJump: true // helps when crossing antimeridian (just in case)
  }).setView(DEFAULT_CENTER, 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "© OpenStreetMap contributors"
  }).addTo(mapInstance);

  markersGroup = L.layerGroup().addTo(mapInstance);

  // create driver marker once (use a simple divIcon so rotation via CSS is easy)
  const driverHtml = `<div class="driver-icon-wrap"><img class="driver-icon-img" src="/Leilife/public/assests/rider.png" alt="driver" style="width:46px;height:46px;"/></div>`;
  const driverDivIcon = L.divIcon({
    className: "driver-div-icon",
    html: driverHtml,
    iconSize: [46, 46],
    iconAnchor: [23, 23]
  });

  driverMarker = L.marker(DEFAULT_CENTER, { icon: driverDivIcon, interactive: false }).addTo(mapInstance);
  driverMarker.setZIndexOffset(9999);

  // style adjustments: ensure map container has transform-origin center
  const container = mapInstance.getContainer();
  container.style.transformOrigin = "50% 50%";

  // Counter-rotate leaflet UI elements when map rotated:
  // We'll add / remove a CSS class to the map root to manage counter-rotation
  mapInstance._container.classList.add("map-root");

  // Force render correction when map becomes visible (modal usage)
  setTimeout(() => {
    mapInstance.invalidateSize();
  }, 400);

  return mapInstance;
}

// ---------- DRIVER POSITION WATCH ----------
if (navigator.geolocation) {
  navigator.geolocation.watchPosition(pos => {
    // pos.coords: latitude, longitude, heading
    const { latitude, longitude, heading } = pos.coords;

    // store as [lat, lng]
    driverCoords = [latitude, longitude];

    if (mapInstance) {
      updateDriverPosition(driverCoords);
      updateDriverHeading(heading);
      maybeUpdateRoute(); // automatically refresh route when needed
    }
  }, err => {
    console.warn("Geolocation error:", err.message);
    if (!driverCoords) driverCoords = DEFAULT_CENTER.slice(); // fallback
  }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 10000 });
} else {
  console.warn("Geolocation not supported in this browser.");
}

// ---------- UPDATE DRIVER POSITION (stable) ----------
function updateDriverPosition(latlngArr) {
  if (!mapInstance || !driverMarker) return;
  const lat = latlngArr[0], lng = latlngArr[1];
  const latlng = L.latLng(lat, lng);

  // Smooth update: move marker
  driverMarker.setLatLng(latlng);

  // pan only if driver far from center (avoid constant re-centering)
  const center = mapInstance.getCenter();
  const distance = mapInstance.distance(center, latlng);
  if (distance > PAN_DISTANCE_THRESHOLD_M) {
    mapInstance.panTo(latlng, { animate: true, duration: 0.8 });
  }
}

// ---------- CLEAR ROUTE ----------
function clearRoute() {
  if (routeLayer) {
    routeLayer.remove();
    routeLayer = null;
  }
  if (customerMarker) {
    customerMarker.remove();
    customerMarker = null;
  }
  document.getElementById("directionsPanel").innerText = "";
  lastRouteUpdate = 0;
  lastRouteUpdatePos = null;
}

// ---------- UPDATE DRIVER HEADING & MAP ROTATION ----------
function updateDriverHeading(heading) {
  // heading may be null; we accept numeric 0-359
  if (heading == null || isNaN(heading)) {
    // keep lastHeading but don't attempt to rotate if not available
    return;
  }

  // Normalize and smooth heading change (small smoothing factor to reduce jitter)
  heading = Number(heading);
  const delta = ((heading - lastHeading + 540) % 360) - 180;
  lastHeading = (lastHeading + delta * 0.35) % 360;

  // Rotate the driver icon itself too (so it points correctly even if map rotation fails)
  try {
    const el = driverMarker.getElement();
    if (el) {
      const iconImg = el.querySelector(".driver-icon-img");
      if (iconImg) {
        iconImg.style.transition = "transform 0.25s linear";
        iconImg.style.transformOrigin = "50% 50%";
        iconImg.style.transform = `rotate(${lastHeading}deg)`;
      }
    }
  } catch (e) {
    // element might not be created yet
  }

  // Rotate the map container so "forward" is facing up
  // We'll rotate the map by -heading so marker heading visually aligns with route direction
  const mapEl = mapInstance.getContainer();
  mapEl.style.transition = "transform 0.3s linear";
  mapEl.style.transform = `rotate(${-lastHeading}deg)`;
  mapRotated = true;

  // To keep UI readable, counter-rotate controls/popups (all common leaflet controls)
  // We apply the inverse transform to elements with .leaflet-control and .leaflet-popup
  const controls = document.querySelectorAll(".leaflet-control, .leaflet-popup");
  controls.forEach(c => {
    c.style.transition = "transform 0.3s linear";
    c.style.transformOrigin = "50% 50%";
    c.style.transform = `rotate(${lastHeading}deg)`;
  });
}



// ---------- ROUTE HANDLING (ORS) ----------
async function showRouteFromCard(card, silent = false) {
  ensureMapReady();
  clearRoute();

  if (!driverCoords) {
    if (!silent) alert("Driver GPS not available.");
    return;
  }

  const latStr = card.dataset.lat;
  const lngStr = card.dataset.lng;
  let custCoords = null;

  if (latStr && lngStr) {
    const lat = parseFloat(latStr), lng = parseFloat(lngStr);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      custCoords = [lat, lng]; // [lat, lng]
    }
  }

  if (!custCoords) {
    document.getElementById("directionsPanel").innerText =
      "⚠️ This order has no saved coordinates.";
    return;
  }

  if (!silent) document.getElementById("directionsPanel").innerText = "Requesting route...";

  try {
    // ORS expects coordinates in [lng, lat] order
    const dc = [driverCoords[1], driverCoords[0]];
    const cc = [custCoords[1], custCoords[0]];

    if (!ORS_API_KEY) {
      throw new Error("ORS API key missing (ORS_API_KEY is empty)");
    }

    const body = { coordinates: [dc, cc], instructions: true };
    const routeRes = await fetch("https://api.openrouteservice.org/v2/directions/driving-car/geojson", {
      method: "POST",
      headers: {
        "Authorization": "Bearer " + ORS_API_KEY,
        "Content-Type": "application/json"
      },
      body: JSON.stringify(body)
    });

    if (!routeRes.ok) {
      let txt = await routeRes.text();
      throw new Error("ORS failed: " + routeRes.status + " - " + txt);
    }
    const routeJson = await routeRes.json();

    let lineCoords = null;
    let steps = [];

    if (routeJson.features && routeJson.features.length && routeJson.features[0].geometry) {
      lineCoords = routeJson.features[0].geometry.coordinates;
      steps = routeJson.features[0].properties?.segments?.[0]?.steps || [];
    }

    if (!lineCoords || !lineCoords.length) throw new Error("Route has no coordinates");

    const latlngs = lineCoords.map(c => [c[1], c[0]]);
    routeLayer = L.polyline(latlngs, { color: "#1976d2", weight: 6, opacity: 0.95 }).addTo(mapInstance);

    customerMarker = L.marker([custCoords[0], custCoords[1]], { title: "Delivery Location" }).addTo(markersGroup);
    customerMarker.bindPopup("Delivery Location");

    // Fit bounds but preserve orientation by computing bounds and centering rather than fitBounds (fitBounds will still work with rotated container visually)
    try {
      mapInstance.fitBounds(routeLayer.getBounds(), { padding: [40, 40] });
    } catch (e) {
      // fallback to center on route mid-point if fitBounds fails
      const mid = latlngs[Math.floor(latlngs.length / 2)];
      mapInstance.setView(mid, Math.max(13, mapInstance.getZoom()));
    }

    // render directions steps
    let html = "<h4>Directions</h4><ol>";
    steps.forEach(s => {
      const dist = s.distance ? ` — ${Math.round(s.distance)} m` : "";
      html += `<li>${s.instruction}${dist}</li>`;
    });
    html += "</ol>";
    document.getElementById("directionsPanel").innerHTML = html;

    // remember last route update time & position
    lastRouteUpdate = Date.now();
    lastRouteUpdatePos = driverCoords.slice();

  } catch (err) {
    console.error("Routing error:", err);
    if (!silent) document.getElementById("directionsPanel").innerText = "Error loading route.";
  }
}

// ---------- AUTO-REFRESH ROUTE WHEN DRIVER DEVIATES ----------
function maybeUpdateRoute() {
  if (!currentCard) return;
  const now = Date.now();

  // time throttle
  if (now - lastRouteUpdate < ROUTE_UPDATE_INTERVAL_MS) return;

  // distance throttle — update if moved enough from lastRouteUpdatePos
  if (lastRouteUpdatePos) {
    const moved = mapInstance ? mapInstance.distance(L.latLng(lastRouteUpdatePos[0], lastRouteUpdatePos[1]), L.latLng(driverCoords[0], driverCoords[1])) : Infinity;
    if (moved < ROUTE_UPDATE_DISTANCE_M) return;
  }

  // ok — update route silently (no extra "Requesting route..." message)
  showRouteFromCard(currentCard, true);
}

// ---------- UTILITY: convert [lat,lng] -> ORS [lng,lat]
function latLngToOrs(l) {
  return [l[1], l[0]];
}
