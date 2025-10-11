// =======================
// DRIVER ORDERS FRONTEND
// =======================
// --------- CONFIG ---------
const DEFAULT_CENTER = [14.5995, 120.9842]; // Manila fallback [lat,lon]
// Load ORS API key dynamically
let ORS_API_KEY = "";

async function loadORSKey() {
  try {
    const res = await fetch("/Leilife/backend/db_script/get_key.php"); 
    const data = await res.json();
    ORS_API_KEY = data.ORS_API_KEY;
    console.log("Loaded ORS key:", ORS_API_KEY);

    // ✅ Continue initializing the map or routes here
    initDriverOrders();
  } catch (err) {
    console.error("Failed to load ORS key:", err);
  }
}


// Example init function (put your map/logic here)
function initDriverOrders() {
  console.log("Driver Orders App starting with key:", ORS_API_KEY);
  // e.g. initialize Leaflet map, call ORS API, etc.
}

// Kickstart
loadORSKey();

// --------- STATE ---------
let driverCoords = null;        
let mapInstance = null;
let markersGroup = null;
let routeLayer = null;
let driverMarker = null;
let customerMarker = null;

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
    // ✅ Fixed path
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
      card.dataset.total = order.total; // 👈 add this


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
  document.querySelectorAll('.btn-view').forEach(btn => {
    btn.onclick = (e) => {
      e.preventDefault();
      const card = btn.closest('.order-card');
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
  const items = JSON.parse(card.dataset.items || '[]');
  const total = card.dataset.total;

  let html = `
    <p><b>Customer:</b> ${customer}</p>
    <p><b>Address:</b> ${address}</p>
    <p><b>Contact:</b> ${number}</p>
    <p><b>Status:</b> <span class="badge ${status.toLowerCase()}">${status}</span></p>
    <h4>Items:</h4>
    <ul>${items.map(i => `<li>${i.product_name} (x${i.quantity})</li>`).join('')}</ul>
    <p><b>Total:</b> ₱${total}</p>
    <a href="tel:${number}" class="contact-btn">📞 Contact Customer</a>
    <button class="complete-btn" data-id="${id}">✅ Mark as Delivered</button>
  `;

  document.getElementById('modalBody').innerHTML = html;
  document.getElementById('orderModal').style.display = 'flex';

  // bind button click
  const markBtn = document.querySelector('.complete-btn');
  markBtn.addEventListener('click', async () => {
    if (!confirm("Mark this order as delivered?")) return;

    try {
      const res = await fetch('/Leilife/backend/driver/mark_delivered.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: id })
      });
      const data = await res.json();

      if (data.success) {
        alert(data.message);
        document.getElementById('orderModal').style.display = 'none';
        loadDeliveredOrders(); // refresh order list
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
document.querySelector('.close-btn').onclick = () => { 
  document.getElementById('orderModal').style.display = 'none'; 
};
window.onclick = (e) => { 
  if (e.target.id === 'orderModal') document.getElementById('orderModal').style.display = 'none'; 
};

// =======================
// DRIVER COORDS HANDLING
// =======================
function waitForDriverCoords(timeout = 8000) {
  return new Promise((resolve) => {
    const start = Date.now();
    (function check() {
      if (driverCoords) return resolve(driverCoords);
      if (Date.now() - start > timeout) return resolve(null);
      setTimeout(check, 200);
    })();
  });
}

if (navigator.geolocation) {
  navigator.geolocation.watchPosition(pos => {
    driverCoords = [pos.coords.longitude, pos.coords.latitude]; 
    if (mapInstance) setDriverMarker(driverCoords);
  }, err => {
    console.warn("Geolocation watch error:", err && err.message);
    if (!driverCoords) driverCoords = [120.9842, 14.5995]; 
  }, { enableHighAccuracy: true, maximumAge: 3000, timeout: 8000 });
} else {
  console.warn("Geolocation not supported");
  driverCoords = [120.9842, 14.5995];
}

// =======================
// MAP UTILS
// =======================
function ensureMapReady() {
  if (mapInstance) return mapInstance;
  mapInstance = L.map('mapContainer', { zoomControl: true }).setView(DEFAULT_CENTER, 13);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(mapInstance);
  markersGroup = L.layerGroup().addTo(mapInstance);
  return mapInstance;
}

function clearRoute() {
  if (routeLayer) {
    routeLayer.remove();
    routeLayer = null;
  }
  if (customerMarker) {
    customerMarker.remove();
    customerMarker = null;
  }
}

function setDriverMarker(lonLat) {
  if (!lonLat || isNaN(lonLat[0]) || isNaN(lonLat[1])) return;
  const latlon = [lonLat[1], lonLat[0]];
  if (!driverMarker) {
    driverMarker = L.marker(latlon).addTo(markersGroup).bindPopup("You (Driver)");
  } else {
    driverMarker.setLatLng(latlon);
  }
}

// =======================
// SHOW ROUTE (ORS)
// =======================
// =======================
// SHOW ROUTE (ORS)
// =======================
async function showRouteFromCard(card) {
  const dc = await waitForDriverCoords(8000);
  if (!dc) {
    alert('Driver GPS not available. Enable location & use HTTPS.');
    return;
  }
  driverCoords = dc;

  ensureMapReady();
  clearRoute();
  setDriverMarker(driverCoords);

  const latStr = card.dataset.lat;
  const lngStr = card.dataset.lng;
  let custCoords = null; 

  if (latStr && lngStr) {
    const lat = parseFloat(latStr), lng = parseFloat(lngStr);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
      custCoords = [lng, lat]; // ORS expects [lon, lat]
    }
  }

  if (!custCoords) {
    document.getElementById('directionsPanel').innerText =
      "⚠️ This order has no saved coordinates. Please ask the customer to pin their location in the app.";
    return;
  }

  // Request ORS route
  document.getElementById('directionsPanel').innerText = 'Requesting route...';
  try {
    const body = {
      coordinates: [driverCoords, custCoords],
      instructions: true
    };

    const routeRes = await fetch('https://api.openrouteservice.org/v2/directions/driving-car/geojson', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + ORS_API_KEY,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(body)
    });

    if (!routeRes.ok) {
      const txt = await routeRes.text();
      console.error('ORS returned error', routeRes.status, txt);
      throw new Error('ORS request failed: ' + routeRes.status);
    }

    const routeJson = await routeRes.json();
    let lineCoords = null;
    let steps = [];

    if (routeJson.features && routeJson.features.length && routeJson.features[0].geometry) {
      lineCoords = routeJson.features[0].geometry.coordinates;
      steps = routeJson.features[0].properties?.segments?.[0]?.steps || [];
    }

    if (!lineCoords || !lineCoords.length) throw new Error('Route has no coordinates');

    const latlngs = lineCoords.map(c => [c[1], c[0]]);
    routeLayer = L.polyline(latlngs, { color: '#1976d2', weight: 6, opacity: 0.9 }).addTo(mapInstance);

    setDriverMarker(driverCoords);
    customerMarker = L.marker([custCoords[1], custCoords[0]]).addTo(markersGroup).bindPopup('Delivery Location');
    mapInstance.fitBounds(routeLayer.getBounds(), { padding: [40, 40] });

    let html = '<h4>Directions</h4><ol>';
    steps.forEach(s => {
      const dist = s.distance ? ` — ${Math.round(s.distance)} m` : '';
      html += `<li>${s.instruction}${dist}</li>`;
    });
    html += '</ol>';
    document.getElementById('directionsPanel').innerHTML = html;

  } catch (err) {
    console.error('Routing error:', err);
    document.getElementById('directionsPanel').innerText = 'Error loading route.';
  }
}

