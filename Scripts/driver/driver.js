// DRIVER ORDERS FRONTEND — MapLibre (Final Integrated)
// - MapLibre GL JS (no Mapbox billing required)
// - Real-time watchPosition tracking (fallback polling), caching, adaptive update
// - Native map rotation (bearing), zoom-to-driver, follow toggle
// - Route fit, route-based initial heading, dynamic rerouting via ORS
// - Preserves your modal/delivered/directions flow

// --------- CONFIG ---------
const DEFAULT_CENTER = [14.5995, 120.9842]; // [lat, lng]
let ORS_API_KEY = "";

// thresholds (tweak as needed)
const ROUTE_UPDATE_INTERVAL_MS = 8000; // minimum interval between route requests
const ROUTE_UPDATE_DISTANCE_M = 30; // update if moved > this meters from last route query point
const PAN_DISTANCE_THRESHOLD_M = 80; // pan map when driver > this from center
const REROUTE_DEVIATION_METERS = 25; // if driver is this far from route polyline -> re-route
const ADAPTIVE_INTERVAL_MIN_MS = 2000;
const ADAPTIVE_INTERVAL_MAX_MS = 9000;

// smoothing & anti-jitter helpers
let lastDriverPos = null;          // [lat, lng] last applied to marker
let markerAnimation = null;        // cancel handle for animation loop
let markerAnimStartTs = 0;
let markerAnimDuration = 600;      // ms — how long interpolation takes
let markerAnimFrom = null;         // [lat,lng]
let markerAnimTo = null;           // [lat,lng]

const MIN_MOVE_TO_UPDATE_M = 1.5;      // ignore micro-movements smaller than ~1.5m
const MAX_ACCEPTABLE_JUMP_M = 60;      // if GPS suddenly jumps > this, treat as spike
const MIN_TIME_BETWEEN_UPDATES_MS = 350;// throttle tiny updates
let lastPositionApplyTs = 0;


// ---------- APP STATE ----------
let driverCoords = null; // [lat, lng]
let mapInstance = null;
const routeSourceId = "route-source";
let driverMarker = null;
let customerMarker = null;
let routeLatLngs = null; // [[lat,lng], ...]
let lastHeading = 0;
let lastRouteUpdate = 0;
let lastRouteUpdatePos = null; // [lat, lng]
let currentCard = null;
let followDriver = true;
let trackingLoopTimer = null;
let geoWatchId = null;      // smoothed device heading (for marker)
let routeBearing = null;    // current route direction (for map rotation)


// ---------- LOAD ORS API KEY ----------
async function loadORSKey() {
  try {
    const res = await fetch("/Leilife/backend/db_script/get_key.php");
    const data = await res.json();
    ORS_API_KEY = data.ORS_API_KEY;
    console.log("Loaded ORS key:", ORS_API_KEY ? "FOUND" : "MISSING");
    initDriverOrders();
  } catch (err) {
    console.error("Failed to load ORS key:", err);
    initDriverOrders();
  }
}

// === Safe cache helpers (add near top) ===
function saveDriverCoords(coords, verified = true) {
  if (!coords || isNaN(coords[0]) || isNaN(coords[1])) return;
  const payload = { coords, verified };
  localStorage.setItem("lastDriverCoords", JSON.stringify(payload));
}

function loadDriverCoords() {
  try {
    const data = JSON.parse(localStorage.getItem("lastDriverCoords"));
    if (data && Array.isArray(data.coords)) return data;
  } catch (e) {}
  return null;
}


function initDriverOrders() {
  console.log("Driver Orders App starting with key:", ORS_API_KEY ? "FOUND" : "MISSING");
  ensureMapReady();

  // ✅ One-time geolocation for initial fix (fixes reload issue)
  if (navigator.geolocation) {
navigator.geolocation.getCurrentPosition(
  pos => {
    const { latitude, longitude } = pos.coords;
    driverCoords = [latitude, longitude];
    saveDriverCoords(driverCoords, true); // ✅ only verified cache
    updateDriverPosition(driverCoords);
  },
  err => {
    console.warn("Initial GPS fix failed:", err.message);
    // ❌ don't save default fallback here
  },
  { enableHighAccuracy: true, timeout: 8000 }
);

  }

  startAdaptiveTracking();
}


// start
loadORSKey();

// ---------- DOM READY ----------
document.addEventListener("DOMContentLoaded", () => {
  loadDeliveredOrders();
  ensureMapReady(); // initialize map quickly
  // injectFocusButton();
  // injectFollowToggle();
});

// ---------- LOAD ORDERS (unchanged) ----------
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
    const container = document.getElementById("orderList");
    if (container) container.innerHTML = `<p class="error">Error loading orders.</p>`;
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

// ---------- OPEN ORDER MODAL (preserves behaviour) ----------
function openOrderModal(card) {
  currentCard = card;
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

  const modalBody = document.getElementById("modalBody");
  if (modalBody) modalBody.innerHTML = html;
  const orderModal = document.getElementById("orderModal");
  if (orderModal) {
  orderModal.style.display = "flex";
  setTimeout(() => {
    if (mapInstance) mapInstance.resize();
  }, 400);
}


  // Force map resize (modal)
  setTimeout(() => {
    if (mapInstance) mapInstance.resize();
  }, 300);

  const markBtn = document.querySelector(".complete-btn");
  if (markBtn) {
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
          if (orderModal) orderModal.style.display = "none";
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
    }, { once: true });
  }

  showRouteFromCard(card);
}

// ==== DRAGGABLE SHEET FUNCTIONALITY ====
const orderSheet = document.getElementById('orderSheet');
const sheetHeader = document.getElementById('sheetHeader');

let startY = 0, currentY = 0, isDragging = false;
let sheetState = "mid"; // "expanded", "mid", "hidden"

if (sheetHeader) {
  sheetHeader.addEventListener('touchstart', startDrag);
  sheetHeader.addEventListener('mousedown', startDrag);
}

function startDrag(e) {
  isDragging = true;
  startY = e.touches ? e.touches[0].clientY : e.clientY;
  orderSheet.style.transition = 'none';
  document.addEventListener('touchmove', onDrag);
  document.addEventListener('mousemove', onDrag);
  document.addEventListener('touchend', stopDrag);
  document.addEventListener('mouseup', stopDrag);
}

function onDrag(e) {
  if (!isDragging) return;
  currentY = e.touches ? e.touches[0].clientY : e.clientY;
  const deltaY = currentY - startY;

  const sheetHeight = orderSheet.offsetHeight;
  const currentTranslate = sheetState === "expanded" ? 0 :
                           sheetState === "mid" ? sheetHeight * 0.55 :
                           sheetHeight * 0.9;
  let newTranslate = currentTranslate + deltaY;

  newTranslate = Math.min(sheetHeight * 0.9, Math.max(0, newTranslate));
  orderSheet.style.transform = `translateY(${newTranslate}px)`;
}

function stopDrag(e) {
  if (!isDragging) return;
  isDragging = false;
  orderSheet.style.transition = 'transform 0.35s ease';
  const sheetHeight = orderSheet.offsetHeight;
  const percentage = parseFloat(orderSheet.style.transform.match(/(\d+)/)?.[0] || 0) / sheetHeight;

  if (percentage < 0.25) {
    orderSheet.classList.add('expanded');
    orderSheet.classList.remove('hidden');
    sheetState = "expanded";
  } else if (percentage > 0.65) {
    orderSheet.classList.add('hidden');
    orderSheet.classList.remove('expanded');
    sheetState = "hidden";
  } else {
    orderSheet.classList.remove('expanded', 'hidden');
    sheetState = "mid";
  }

  document.removeEventListener('touchmove', onDrag);
  document.removeEventListener('mousemove', onDrag);
  document.removeEventListener('touchend', stopDrag);
  document.removeEventListener('mouseup', stopDrag);
}

// Allow clicking handle to toggle expand/collapse
sheetHeader.addEventListener('click', () => {
  if (sheetState === "expanded") {
    orderSheet.classList.add('hidden');
    orderSheet.classList.remove('expanded');
    sheetState = "hidden";
  } else {
    orderSheet.classList.add('expanded');
    orderSheet.classList.remove('hidden');
    sheetState = "expanded";
  }
});


// ---------- MODAL CLOSE ----------
const closeBtn = document.querySelector(".close-btn");
if (closeBtn) {
  closeBtn.onclick = () => {
    const orderModal = document.getElementById("orderModal");
    if (orderModal) orderModal.style.display = "none";
    currentCard = null;
    clearRoute();
  };
}
window.onclick = (e) => {
  if (e.target && e.target.id === "orderModal") {
    const orderModal = document.getElementById("orderModal");
    if (orderModal) orderModal.style.display = "none";
    currentCard = null;
    clearRoute();
  }
};

// ---------- MAP (MapLibre) INIT ----------
function ensureMapReady() {
  if (mapInstance) return mapInstance;

  // create MapLibre map with performance tweaks
  mapInstance = new maplibregl.Map({
    container: "mapContainer",
    style: 'https://tiles.basemaps.cartocdn.com/gl/positron-gl-style/style.json', // working OSM style
    center: [DEFAULT_CENTER[1], DEFAULT_CENTER[0]],
    zoom: 13,
    pitch: 45, // slight pitch for driving feel
    bearing: 0,
    fadeDuration: 0,
    maxPitch: 60,
    maxZoom: 20,
    reuseTiles: true
  });

  // mapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');

// --- ADD CUSTOM FOLLOW DRIVER CONTROL ---
// --- ADD CUSTOM FOLLOW DRIVER CONTROL ---
// This will always recenter on current live location (like Google Maps)
class FollowDriverControl {
  onAdd(map) {
    this._map = map;
    this._btn = document.createElement('div');
    this._btn.className = 'maplibregl-ctrl my-locate-btn';
    this._btn.innerHTML = `
      <img src="https://cdn-icons-png.flaticon.com/512/565/565949.png" 
           alt="locate" 
           style="width:22px;height:22px;filter:invert(0);transition:transform 0.3s ease;">
    `;
    this._btn.title = "Recenter on my location";

    // 🧭 When user clicks the locate button
    this._btn.onclick = () => {
      const icon = this._btn.querySelector('img');

      if (!navigator.geolocation) {
        alert("⚠️ Geolocation not supported on this device.");
        return;
      }

      // show small spin animation (loading)
      icon.style.transform = "rotate(360deg)";
      this._btn.style.opacity = "0.6";
      this._btn.style.pointerEvents = "none";

      // Always get a fresh GPS fix
      navigator.geolocation.getCurrentPosition(
        pos => {
          const { latitude, longitude } = pos.coords;
          driverCoords = [latitude, longitude];

          // save only verified coordinates
          if (typeof saveDriverCoords === "function") {
            saveDriverCoords(driverCoords, true);
          } else {
            localStorage.setItem("lastDriverCoords", JSON.stringify(driverCoords));
          }

          // Update marker and map
          if (typeof updateDriverPosition === "function") {
            updateDriverPosition(driverCoords);
          }

          this._focus(driverCoords);

          // Restart adaptive tracking if available
          if (typeof stopAdaptiveTracking === "function") stopAdaptiveTracking();
          if (typeof startAdaptiveTracking === "function") startAdaptiveTracking();

          // restore button state
          icon.style.transform = "rotate(0deg)";
          this._btn.style.opacity = "1";
          this._btn.style.pointerEvents = "auto";

          console.log("✅ Manual GPS refresh successful:", driverCoords);
        },
        err => {
          alert("⚠️ Unable to get location: " + err.message);
          icon.style.transform = "rotate(0deg)";
          this._btn.style.opacity = "1";
          this._btn.style.pointerEvents = "auto";
        },
        { enableHighAccuracy: true, timeout: 8000 }
      );
    };

    return this._btn;
  }

  // 🔍 Smoothly focus map on driver coordinates
  _focus(coords) {
    this._map.easeTo({
      center: [coords[1], coords[0]],
      zoom: 17,
      bearing: (routeBearing != null ? routeBearing : lastHeading) || 0,
      pitch: 45,
      duration: 800
    });
    followDriver = true;
    const toggleBtn = document.getElementById("toggleFollowBtn");
    if (toggleBtn) toggleBtn.innerText = "Follow";
  }

  onRemove() {
    this._btn.parentNode.removeChild(this._btn);
    this._map = undefined;
  }
}



// Add to bottom-right corner of map
mapInstance.addControl(new FollowDriverControl(), 'bottom-right');


  // add default controls
  mapInstance.addControl(new maplibregl.NavigationControl({ showCompass: true }), "top-right");

  // create driver marker DOM element
  const el = document.createElement("div");
  el.className = "driver-marker-wrap";
  el.style.width = "46px";
  el.style.height = "46px";
  el.style.backgroundImage = "url('/Leilife/public/assests/rider.png')";
  el.style.backgroundSize = "contain";
  el.style.backgroundRepeat = "no-repeat";
  el.style.transformOrigin = "50% 50%";
  el.style.pointerEvents = "auto"; // allow clicks

  driverMarker = new maplibregl.Marker({ element: el, rotationAlignment: 'map' })
    .setLngLat([DEFAULT_CENTER[1], DEFAULT_CENTER[0]])
    .addTo(mapInstance);

  // click driver marker to focus
  el.addEventListener("click", () => focusDriverOnMap(true));

  // ensure map resizes when shown in modal
  setTimeout(() => {
    if (mapInstance) mapInstance.resize();
  }, 400);

  mapInstance.on('dragstart', () => {
  followDriver = false;
  const toggleBtn = document.getElementById("toggleFollowBtn");
  if (toggleBtn) toggleBtn.innerText = "Free";
});


  return mapInstance;
}

// // ---------- FOCUS CONTROL UI ----------
// function injectFocusButton() {
//   if (document.getElementById("focusDriverBtn")) return;
//   const btn = document.createElement("button");
//   btn.id = "focusDriverBtn";
//   btn.title = "Focus on driver";
//   btn.style = `
//     position: absolute;
//     right: 14px;
//     bottom: 90px;
//     z-index: 99999;
//     width:44px;height:44px;border-radius:22px;
//     background:#fff;border:1px solid #ddd;box-shadow:0 4px 10px rgba(0,0,0,0.12);
//     display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;
//   `;
//   btn.innerHTML = "📍";
//   btn.onclick = (e) => { e.preventDefault(); focusDriverOnMap(true); };
//   document.body.appendChild(btn);
// }

// function injectFollowToggle() {
//   if (document.getElementById("toggleFollowBtn")) return;
//   const btn = document.createElement("button");
//   btn.id = "toggleFollowBtn";
//   btn.title = "Toggle follow driver";
//   btn.style = `
//     position: absolute;
//     right: 14px;
//     bottom: 40px;
//     z-index: 99999;
//     width:74px;height:36px;border-radius:6px;
//     background:#fff;border:1px solid #ddd;box-shadow:0 4px 10px rgba(0,0,0,0.12);
//     display:flex;align-items:center;justify-content:center;font-size:14px;cursor:pointer;
//   `;
//   btn.innerText = followDriver ? "Follow" : "Free";
//   btn.onclick = (e) => {
//     e.preventDefault();
//     followDriver = !followDriver;
//     btn.innerText = followDriver ? "Follow" : "Free";
//   };
//   document.body.appendChild(btn);
// }

// Focus driver (zoom to) — optionally zoom in
function focusDriverOnMap(zoomIn = true) {
  if (!mapInstance || !driverCoords) return;
  const lngLat = [driverCoords[1], driverCoords[0]];
  
  followDriver = true; // ✅ auto re-enable follow when user taps focus
  const toggleBtn = document.getElementById("toggleFollowBtn");
  if (toggleBtn) toggleBtn.innerText = "Follow";

  if (zoomIn) {
    mapInstance.easeTo({ center: lngLat, zoom: Math.max(mapInstance.getZoom(), 17), duration: 600 });
  } else {
    mapInstance.easeTo({ center: lngLat, duration: 800 });
  }
}


// ---------- REALTIME TRACKING: watchPosition (with fallback) ----------
function startAdaptiveTracking() {
  if (!("geolocation" in navigator)) {
    console.warn("Geolocation not supported");
    driverCoords = DEFAULT_CENTER.slice();
    ensureMapReady();
    return;
  }

  stopAdaptiveTracking();
  ensureMapReady();

  // Use cached position instantly (prevents jump)
const cached = loadDriverCoords();

if (cached && cached.coords && cached.verified) {
  driverCoords = cached.coords;
  updateDriverPosition(driverCoords);
} else {
  console.log("⚠️ No verified GPS cache — requesting fresh location...");
  navigator.geolocation.getCurrentPosition(
    pos => {
      const { latitude, longitude } = pos.coords;
      driverCoords = [latitude, longitude];
      saveDriverCoords(driverCoords, true);
      updateDriverPosition(driverCoords);
    },
    err => {
      console.warn("Failed to get GPS; using DEFAULT_CENTER temporarily:", err.message);
      driverCoords = DEFAULT_CENTER.slice();
      updateDriverPosition(driverCoords);
      // ⚠️ Not cached — will retry next time
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
}


  // Start continuous tracking
  geoWatchId = navigator.geolocation.watchPosition(
    pos => {
      const { latitude, longitude, heading, speed, accuracy } = pos.coords;
      const newCoords = [latitude, longitude];
      saveDriverCoords(newCoords, true); // verified GPS


      if (!latitude || !longitude || isNaN(latitude) || isNaN(longitude)) {
      console.warn("⚠️ GPS returned invalid coords, keeping last known position");
      return; // ✅ Skip update to avoid marker disappearing
    }


      // ignore extremely poor accuracy if we have a previous good fix
      if (accuracy && accuracy > 100 && driverCoords) return;

      const prevCoords = driverCoords ? driverCoords.slice() : null;
      driverCoords = newCoords;

      updateDriverPosition(driverCoords);
      const useHeading = (heading != null && !isNaN(heading)) ? heading : computeHeadingFromMovement(prevCoords, driverCoords);
      updateDriverHeading(useHeading);

      // route deviation / maybe update
      if (routeLatLngs && currentCard) {
        const distToRoute = distanceToPolylineMeters(driverCoords, routeLatLngs);
        if (distToRoute > REROUTE_DEVIATION_METERS) {
          const now = Date.now();
          if (now - lastRouteUpdate > 1500) {
            showRouteFromCard(currentCard, true);
          }
        } else {
          maybeUpdateRoute();
        }
      } else {
        maybeUpdateRoute();
      }
    },
    err => {
      console.warn("watchPosition error:", err.message);
      // fallback to polling loop if watch fails
      adaptiveTrackingCycle();
    },
    { enableHighAccuracy: true, maximumAge: 2000, timeout: 10000 }
  );
}

function stopAdaptiveTracking() {
  if (geoWatchId !== null) {
    navigator.geolocation.clearWatch(geoWatchId);
    geoWatchId = null;
  }
  if (trackingLoopTimer) {
    clearTimeout(trackingLoopTimer);
    trackingLoopTimer = null;
  }
}

// Fallback polling loop (keeps existing adaptive timing behavior)
function computeIntervalFromSpeed(speed) {
  if (speed == null || isNaN(speed)) return ADAPTIVE_INTERVAL_MAX_MS;
  const kmh = speed * 3.6;
  if (kmh < 10) return 8000;
  if (kmh < 30) return 4500;
  return 2500;
}

function adaptiveTrackingCycle() {
  navigator.geolocation.getCurrentPosition(pos => {
    const { latitude, longitude, heading, speed, accuracy } = pos.coords;
    const newCoords = [latitude, longitude];

    if (accuracy && accuracy > 100 && driverCoords) {
      // schedule next poll and skip this poor fix
      trackingLoopTimer = setTimeout(adaptiveTrackingCycle, ADAPTIVE_INTERVAL_MAX_MS);
      return;
    }

    const prevCoords = driverCoords ? driverCoords.slice() : null;
    driverCoords = newCoords;

    updateDriverPosition(driverCoords);
    const useHeading = (heading != null && !isNaN(heading)) ? heading : computeHeadingFromMovement(prevCoords, driverCoords);
    updateDriverHeading(useHeading);

    if (routeLatLngs && currentCard) {
      const distToRoute = distanceToPolylineMeters(driverCoords, routeLatLngs);
      if (distToRoute > REROUTE_DEVIATION_METERS) {
        const now = Date.now();
        if (now - lastRouteUpdate > 1500) {
          showRouteFromCard(currentCard, true);
        }
      } else {
        maybeUpdateRoute();
      }
    } else {
      maybeUpdateRoute();
    }

    // schedule next
    const interval = computeIntervalFromSpeed(speed);
    const clamped = Math.max(ADAPTIVE_INTERVAL_MIN_MS, Math.min(ADAPTIVE_INTERVAL_MAX_MS, interval));
    trackingLoopTimer = setTimeout(adaptiveTrackingCycle, clamped);

  }, err => {
    console.warn("Geolocation error (adaptive):", err.message);
    trackingLoopTimer = setTimeout(adaptiveTrackingCycle, ADAPTIVE_INTERVAL_MAX_MS);
  }, { enableHighAccuracy: true, maximumAge: 0, timeout: 8000 });
}

function computeHeadingFromMovement(prev, curr) {
  if (!prev || !curr) return lastHeading;
  const dy = curr[0] - prev[0];
  const dx = curr[1] - prev[1];
  if (Math.abs(dx) < 1e-7 && Math.abs(dy) < 1e-7) return lastHeading;
  const rad = Math.atan2(dx, dy);
  let deg = (rad * 180 / Math.PI);
  deg = (deg + 360) % 360;
  return deg;
}

function updateDriverPosition(latlngArr) {
  if (!mapInstance || !driverMarker) return;

  const nowTs = Date.now();

  // Safety fallback / validation
  if (!latlngArr || isNaN(latlngArr[0]) || isNaN(latlngArr[1])) {
    const cached = localStorage.getItem("lastDriverCoords");
    if (cached) {
      try { latlngArr = JSON.parse(cached); } catch { return; }
    } else return;
  }

  const lat = Number(latlngArr[0]), lng = Number(latlngArr[1]);
  const newPos = [lat, lng];

  // If we have a lastDriverPos, compute distance and filter micro-noise & spikes
  if (lastDriverPos) {
    const moved = haversineMeters(lastDriverPos, newPos);

    // 1) ignore tiny noise when essentially stationary (reduces jitter)
    if (moved < MIN_MOVE_TO_UPDATE_M && (nowTs - lastPositionApplyTs) < MIN_TIME_BETWEEN_UPDATES_MS) {
      // still update heading if needed (we'll keep marker in place)
      lastPositionApplyTs = nowTs;
      driverCoords = newPos;
      return;
    }

    // 2) reject improbable GPS spike: if jump is huge, ignore once (unless you have no other pos)
    if (moved > MAX_ACCEPTABLE_JUMP_M) {
      console.warn("GPS spike ignored (jump:", Math.round(moved), "m)");
      // don't update driverCoords or run animation — keep previous
      return;
    }
  }

  // Accept the sample: store user-facing coords
  driverCoords = newPos;
  lastPositionApplyTs = nowTs;

  // Smooth animation: interpolate marker from lastDriverPos -> newPos
  // If no previous applied position, snap immediately (no animation)
  if (!lastDriverPos) {
    lastDriverPos = newPos.slice();
    // immediately set marker and ensure map pan follows as before
    driverMarker.setLngLat([lng, lat]);
    // set opacity stable
    const el = driverMarker.getElement(); if (el) el.style.opacity = "1";
  } else {
    // start animation from lastDriverPos to newPos
    startMarkerAnimation(lastDriverPos.slice(), newPos.slice(), markerAnimDuration);
  }

  // Pan map only when followDriver is enabled or far from center (preserve previous behavior)
  try {
    const center = mapInstance.getCenter();
    const distToCenter = haversineMeters([center.lat, center.lng], [lat, lng]);

    if (followDriver) {
      if (distToCenter > 20) {
        mapInstance.easeTo({
          center: [lng, lat],
          bearing: routeBearing ?? lastHeading ?? 0,
          duration: 800,
          essential: true
        });
      }
    } else if (distToCenter > PAN_DISTANCE_THRESHOLD_M) {
      mapInstance.easeTo({ center: [lng, lat], duration: 1000, essential: true });
    }
  } catch (e) { /* ignore pan errors */ }
}



// ---------- CLEAR ROUTE ----------
function clearRoute() {
  if (!mapInstance) return;
  try {
    if (mapInstance.getLayer("route-layer")) mapInstance.removeLayer("route-layer");
    if (mapInstance.getSource(routeSourceId)) mapInstance.removeSource(routeSourceId);
  } catch (e) { /* ignore */ }
  routeLatLngs = null;
  if (customerMarker) {
    customerMarker.remove();
    customerMarker = null;
  }
  const panel = document.getElementById("directionsPanel");
  if (panel) panel.innerText = "";
  lastRouteUpdate = 0;
  lastRouteUpdatePos = null;
}

// ---------- UPDATE DRIVER HEADING & MAP ROTATION ----------
function updateDriverHeading(heading) {
  if (heading == null || isNaN(heading)) return;
  heading = Number(heading);

  // Smooth heading
  const delta = ((heading - lastHeading + 540) % 360) - 180;
  lastHeading = (lastHeading + delta * 0.35 + 360) % 360; // smoothed device heading

  // Apply rotation to marker element only (do NOT change translate here)
  try {
    const el = driverMarker.getElement();
    if (!el) return;

    // Quick visual smoothing: CSS transition for rotation (faster than position smoothing)
    el.style.transition = "transform 220ms linear";
    const mapBearing = mapInstance ? mapInstance.getBearing() : 0;
    const rel = ((lastHeading - mapBearing) + 360) % 360;

    // Because marker position is controlled by MapLibre (setLngLat), we must only rotate:
    el.style.transform = `rotate(${rel}deg)`;
    el.style.transformOrigin = "center center"; // keep rotation stable
    el.style.willChange = "transform";
  } catch (e) { /* ignore */ }
}


function startMarkerAnimation(fromLatLng, toLatLng, durationMs = 600) {
  // cancel any running animation but keep lastDriverPos as start
  if (markerAnimation) {
    cancelAnimationFrame(markerAnimation);
    markerAnimation = null;
  }

  markerAnimStartTs = performance.now();
  markerAnimDuration = durationMs;
  markerAnimFrom = fromLatLng;
  markerAnimTo = toLatLng;

  // start loop
  markerAnimation = requestAnimationFrame(animateMarkerFrame);
}

function animateMarkerFrame(ts) {
  if (!markerAnimFrom || !markerAnimTo) return;

  const elapsed = ts - markerAnimStartTs;
  const t = Math.min(1, elapsed / markerAnimDuration);

  // ease in-out cubic for smoother motion
  const eased = (t < 0.5) ? (4 * t * t * t) : (1 - Math.pow(-2 * t + 2, 3) / 2);

  const lat = markerAnimFrom[0] + (markerAnimTo[0] - markerAnimFrom[0]) * eased;
  const lng = markerAnimFrom[1] + (markerAnimTo[1] - markerAnimFrom[1]) * eased;

  // apply directly to marker
  try {
    driverMarker.setLngLat([lng, lat]);
    const el = driverMarker.getElement();
    if (el) {
      el.style.opacity = "1";
      // keep rotation composition handled in updateDriverHeading (we set transform there)
      // but to avoid transform override, only set translate here using map project -> pixel transform if needed.
      // maplibregl.Marker.setLngLat updates DOM position; do not change el.style.transform here.
    }
  } catch (e) { /* ignore */ }

  if (t < 1) {
    markerAnimation = requestAnimationFrame(animateMarkerFrame);
  } else {
    // finished: commit lastDriverPos and clear animation
    lastDriverPos = markerAnimTo.slice();
    markerAnimFrom = markerAnimTo = null;
    markerAnimation = null;
  }
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
    const panel = document.getElementById("directionsPanel");
    if (panel) panel.innerText = "⚠️ This order has no saved coordinates.";
    return;
  }

  if (!silent) {
    const panel = document.getElementById("directionsPanel");
    if (panel) panel.innerText = "Requesting route...";
  }

  try {
    if (!ORS_API_KEY) throw new Error("ORS API key missing");

    // ORS expects [lng,lat]
    const dc = [driverCoords[1], driverCoords[0]];
    const cc = [custCoords[1], custCoords[0]];
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

    const lineCoords = routeJson.features?.[0]?.geometry?.coordinates;
    const steps = routeJson.features?.[0]?.properties?.segments?.[0]?.steps || [];

    if (!lineCoords || !lineCoords.length) throw new Error("Route has no coordinates");

    // convert coords to [lat,lng] array for our checks
    const latlngs = lineCoords.map(c => [c[1], c[0]]);
    routeLatLngs = latlngs.slice();

    // add route to map as source+layer (remove existing first)
    try {
      if (mapInstance.getLayer("route-layer")) mapInstance.removeLayer("route-layer");
      if (mapInstance.getSource(routeSourceId)) mapInstance.removeSource(routeSourceId);
    } catch (e) { /* ignore */ }

    mapInstance.addSource(routeSourceId, {
      type: "geojson",
      data: {
        type: "Feature",
        geometry: { type: "LineString", coordinates: lineCoords }
      }
    });

    mapInstance.addLayer({
      id: "route-layer",
      type: "line",
      source: routeSourceId,
      layout: { "line-join": "round", "line-cap": "round" },
      paint: {
        "line-color": "#1976d2",
        "line-width": 6,
        "line-opacity": 0.95
      }
    });

    // add customer marker
    if (customerMarker) customerMarker.remove();
    customerMarker = new maplibregl.Marker({ color: "#d00" })
      .setLngLat([custCoords[1], custCoords[0]])
      .addTo(mapInstance);

    // fit bounds to show whole route
    try {
      const bounds = new maplibregl.LngLatBounds();
      lineCoords.forEach(c => bounds.extend(c));
      mapInstance.fitBounds(bounds, { padding: 40, duration: 800 });
    } catch (e) {
      const mid = latlngs[Math.floor(latlngs.length / 2)];
      mapInstance.easeTo({ center: [mid[1], mid[0]], zoom: Math.max(13, mapInstance.getZoom()), duration: 600 });
    }

    // set initial route-bearing: rotate map to face route direction
    // set initial route-bearing: rotate map to face route direction
    try {
      if (latlngs.length >= 2) {
        const b = bearingBetweenLatLngs(latlngs[0], latlngs[1]);
        routeBearing = b;                 // store route direction separately
        mapInstance.rotateTo(routeBearing, { duration: 600 });
      }
    } catch (e) {
      console.warn("Could not compute route bearing:", e);
    }


    // render directions
    let html = "<h4>Directions</h4><ol>";
    steps.forEach(s => {
      const dist = s.distance ? ` — ${Math.round(s.distance)} m` : "";
      html += `<li>${s.instruction}${dist}</li>`;
    });
    html += "</ol>";
    const panel = document.getElementById("directionsPanel");
    if (panel) panel.innerHTML = html;

    lastRouteUpdate = Date.now();
    lastRouteUpdatePos = driverCoords ? driverCoords.slice() : null;

  } catch (err) {
    console.error("Routing error:", err);
    if (!silent) {
      const panel = document.getElementById("directionsPanel");
      if (panel) panel.innerText = "Error loading route.";
    }
  }
}

// ---------- AUTO-REFRESH ROUTE WHEN DRIVER DEVIATES ----------
function maybeUpdateRoute() {
  if (!currentCard) return;
  const now = Date.now();

  if (now - lastRouteUpdate < ROUTE_UPDATE_INTERVAL_MS) {
    const movedExtremely = lastRouteUpdatePos ? (haversineMeters(lastRouteUpdatePos, driverCoords) > (ROUTE_UPDATE_DISTANCE_M * 4)) : true;
    if (!movedExtremely) return;
  }

  if (lastRouteUpdatePos) {
    const moved = haversineMeters(lastRouteUpdatePos, driverCoords);
    if (moved < ROUTE_UPDATE_DISTANCE_M) return;
  }

  showRouteFromCard(currentCard, true);
}

// ---------- UTIL: BEARING between two latlngs (deg) ----------
function bearingBetweenLatLngs(a, b) {
  const lat1 = a[0] * Math.PI/180;
  const lat2 = b[0] * Math.PI/180;
  const dLon = (b[1]-a[1]) * Math.PI/180;
  const y = Math.sin(dLon) * Math.cos(lat2);
  const x = Math.cos(lat1)*Math.sin(lat2) - Math.sin(lat1)*Math.cos(lat2)*Math.cos(dLon);
  const br = Math.atan2(y, x) * 180/Math.PI;
  return (br + 360) % 360;
}

// ---------- UTIL: distance from point to polyline (meters) ----------
function distanceToPolylineMeters(pointLatLng, polyLatLngs) {
  if (!polyLatLngs || polyLatLngs.length === 0) return Infinity;
  let minDist = Infinity;
  for (let i = 0; i < polyLatLngs.length - 1; i++) {
    const A = polyLatLngs[i];
    const B = polyLatLngs[i+1];
    const d = distancePointToSegmentMeters(pointLatLng, A, B);
    if (d < minDist) minDist = d;
  }
  return minDist;
}

function distancePointToSegmentMeters(P, A, B) {
  const toRad = v => v * Math.PI / 180;
  const latFactor = Math.cos(toRad((P[0] + A[0] + B[0]) / 3));

  const px = P[1] * latFactor;
  const py = P[0];
  const ax = A[1] * latFactor;
  const ay = A[0];
  const bx = B[1] * latFactor;
  const by = B[0];

  const vx = bx - ax;
  const vy = by - ay;
  const wx = px - ax;
  const wy = py - ay;
  const c1 = vx*wx + vy*wy;
  const c2 = vx*vx + vy*vy;
  let t = c2 === 0 ? 0 : c1 / c2;
  t = Math.max(0, Math.min(1, t));
  const projx = ax + t*vx;
  const projy = ay + t*vy;
  const projLatLng = [projy, projx / latFactor];
  return haversineMeters(P, projLatLng);
}

// haversine metres
function haversineMeters(a, b) {
  const R = 6371000;
  const toRad = x => x * Math.PI / 180;
  const dLat = toRad(b[0]-a[0]);
  const dLon = toRad(b[1]-a[1]);
  const lat1 = toRad(a[0]);
  const lat2 = toRad(b[0]);

  const sinDlat = Math.sin(dLat/2);
  const sinDlon = Math.sin(dLon/2);
  const aa = sinDlat*sinDlat + Math.cos(lat1)*Math.cos(lat2)*sinDlon*sinDlon;
  const c = 2 * Math.atan2(Math.sqrt(aa), Math.sqrt(1-aa));
  return R * c;
}

// ---------- UTILITY: convert [lat,lng] -> ORS [lng,lat]
function latLngToOrs(l) {
  return [l[1], l[0]];
}
