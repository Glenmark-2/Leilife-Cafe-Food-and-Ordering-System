// DRIVER ORDERS FRONTEND — MapLibre (Reliable Delivery Style)

// --------- CONFIG ---------
const DEFAULT_CENTER = [14.5995, 120.9842]; // Fallback [lat, lng]
let ORS_API_KEY = "";

// Tweak thresholds for "Delivery App" feel
const REROUTE_THRESHOLD_METERS = 40;   // deviate > 40m -> recalibrate route
const REROUTE_COOLDOWN_MS = 5000;      // max 1 reroute every 5s
const GPS_MAX_ACCURACY_M = 200;        // Accept points up to 200m accuracy initially, then tighten
const MAP_ZOOM_LEVEL_FOLLOW = 17;
const MAP_PITCH = 50;

// Internal state
let driverCoords = null; // [lat, lng]
let mapInstance = null;
let navControl = null;
let geoWatchId = null;
let lastHeading = 0;
let isFollowing = true;
let isDraggingInteraction = false;

// Route state
let routeSourceId = "route-source";
let routeLayerId = "route-layer";
let activeRoutePoints = null; // for deviation calc
let lastRerouteTime = 0;
let customerMarker = null;
let driverMarker = null;
let currentOrderCard = null;

// Draggable Sheet State
let startY = 0, currentY = 0, isDragging = false;
let sheetState = "mid";

// ----------------------------------------------------------------------------
// 1. INIT & PERMISSIONS
// ----------------------------------------------------------------------------

document.addEventListener("DOMContentLoaded", () => {
  console.log("🚀 Driver App Init");
  checkSecureContext();
  loadORSKey();
  loadDeliveredOrders();
  initSheetLogic();
  startGPS(); // Start tracking immediately
});

function checkSecureContext() {
  if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
    alert("⚠️ Geolocation requires HTTPS. Your location may not work on this network (HTTP).");
  }
}

async function loadORSKey() {
  try {
    const res = await fetch("/Leilife/backend/db_script/get_key.php");
    const data = await res.json();
    ORS_API_KEY = data.ORS_API_KEY;
    console.log("🔑 ORS Key loaded:", ORS_API_KEY ? "Yes" : "No");
  } catch (err) {
    console.error("Failed to load ORS key:", err);
  }
}

// ----------------------------------------------------------------------------
// 2. MAP INITIALIZATION (MapLibre)
// ----------------------------------------------------------------------------

function initMap() {
  if (mapInstance) return;

  // Use current driver coords if available, else default
  const center = driverCoords ? [driverCoords[1], driverCoords[0]] : [DEFAULT_CENTER[1], DEFAULT_CENTER[0]];

  const styleUrl = 'https://tiles.basemaps.cartocdn.com/gl/positron-gl-style/style.json';

  mapInstance = new maplibregl.Map({
    container: 'mapContainer', // Must be visible when this runs!
    style: styleUrl,
    center: center,
    zoom: 13,
    pitch: 0,
    bearing: 0,
    attributionControl: false
  });

  mapInstance.addControl(new maplibregl.AttributionControl({ compact: true }));
  navControl = new maplibregl.NavigationControl({ showCompass: true, showZoom: true });
  mapInstance.addControl(navControl, 'top-right');

  // Add "Recenter / Follow" Button (Restored Image Icon)
  class RecenterControl {
    onAdd(map) {
      this.div = document.createElement('button');
      this.div.className = 'maplibregl-ctrl maplibregl-ctrl-group my-locate-btn';

      this.div.innerHTML = `
                <img src="https://cdn-icons-png.flaticon.com/512/565/565949.png" 
                     alt="locate" 
                     style="width:20px;height:20px;">
            `;
      this.div.title = "Recenter on my location";
      this.div.onclick = () => {
        isFollowing = true;
        if (driverCoords) {
          flyToDriver(true);
        } else {
          forceGPSRefresh();
        }
      };
      return this.div;
    }
    onRemove() { this.div.parentNode.removeChild(this.div); }
  }
  mapInstance.addControl(new RecenterControl(), 'bottom-right');

  // Create Driver Marker
  const el = document.createElement('div');
  el.style.backgroundImage = 'url(/Leilife/public/assests/rider.png)';
  el.style.width = '50px';
  el.style.height = '50px';
  el.style.backgroundSize = 'contain';
  el.style.backgroundRepeat = 'no-repeat';
  el.style.zIndex = '500';

  driverMarker = new maplibregl.Marker({ element: el, rotationAlignment: 'map' }) // ensure marker is created even if we don't know where yet (will use center)
    .setLngLat(center)
    .addTo(mapInstance);

  mapInstance.on('dragstart', () => { isFollowing = false; });
  mapInstance.on('touchstart', () => { isFollowing = false; });
}

// ----------------------------------------------------------------------------
// 3. ROBUST GPS TRACKING
// ----------------------------------------------------------------------------

function startGPS() {
  if (!navigator.geolocation) {
    alert("Geolocation not supported by this browser.");
    return;
  }

  if (geoWatchId !== null) navigator.geolocation.clearWatch(geoWatchId);

  const options = {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 10000
  };

  geoWatchId = navigator.geolocation.watchPosition(
    handleGPSUpdate,
    handleGPSError,
    options
  );

  navigator.geolocation.getCurrentPosition(handleGPSUpdate, handleGPSError, options);
}

function forceGPSRefresh() {
  console.log("Force refreshing GPS...");
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      console.log("Forced Fix Acquired");
      handleGPSUpdate(pos);
      isFollowing = true;
      flyToDriver(true);
    },
    (err) => console.warn("Forced fix failed", err),
    { enableHighAccuracy: true, maximumAge: 0, timeout: 5000 }
  );
}

function handleGPSUpdate(pos) {
  const { latitude, longitude, accuracy, heading } = pos.coords;

  if (!latitude || !longitude) return;

  if (driverCoords && accuracy > GPS_MAX_ACCURACY_M) {
    // console.warn(`GPS skipped: low accuracy (${Math.round(accuracy)}m)`);
    // Relaxing this: if we have NO valid marker updates recently, maybe accept it?
    // But for now, stick to the rule to avoid jumping.
    // return; 
  }

  const newCoords = [latitude, longitude];
  driverCoords = newCoords;

  updateDriverMarker(newCoords, heading);

  if (isFollowing) {
    flyToDriver(false, heading);
  }

  if (activeRoutePoints && activeRoutePoints.length > 0) {
    checkRouteDeviation(newCoords);
  }
}

function handleGPSError(err) {
  console.warn("GPS Error:", err.code, err.message);
  if (err.code === 1) {
    alert("Please allow location access to use the driver app.");
  }
}

function updateDriverMarker(coords, heading) {
  if (!mapInstance || !driverMarker) return;

  driverMarker.setLngLat([coords[1], coords[0]]);

  if (heading && !isNaN(heading)) {
    driverMarker.setRotation(heading);
    lastHeading = heading;
  }
}

function flyToDriver(forceZoom = false, heading = null) {
  if (!mapInstance || !driverCoords) return;

  const cameraOptions = {
    center: [driverCoords[1], driverCoords[0]],
    pitch: MAP_PITCH
  };

  if (forceZoom) {
    cameraOptions.zoom = MAP_ZOOM_LEVEL_FOLLOW;
  }

  mapInstance.easeTo({
    ...cameraOptions,
    duration: 800
  });
}

// ----------------------------------------------------------------------------
// 4. ROUTING & REROUTING
// ----------------------------------------------------------------------------

async function drawRoute(destLat, destLng, autoRetry = false) {
  if (!driverCoords) {
    if (!autoRetry) alert("Waiting for driver location...");
    return;
  }
  if (!ORS_API_KEY) {
    console.error("No ORS Key");
    return;
  }

  const panel = document.getElementById("directionsPanel");
  if (panel) panel.innerText = "Calculating route...";

  try {
    const body = {
      coordinates: [
        [driverCoords[1], driverCoords[0]],
        [destLng, destLat]
      ],
      profile: "driving-car",
      format: "geojson"
    };

    const res = await fetch(`https://api.openrouteservice.org/v2/directions/driving-car/geojson`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': ORS_API_KEY
      },
      body: JSON.stringify({
        coordinates: body.coordinates,
        instructions: true
      })
    });

    if (!res.ok) throw new Error("Route fetch failed");

    const json = await res.json();
    const routeGeoJSON = json.features[0];

    activeRoutePoints = routeGeoJSON.geometry.coordinates.map(c => [c[1], c[0]]);

    const sourceData = { type: "Feature", geometry: routeGeoJSON.geometry };

    if (mapInstance.getSource(routeSourceId)) {
      mapInstance.getSource(routeSourceId).setData(sourceData);
    } else {
      mapInstance.addSource(routeSourceId, { type: "geojson", data: sourceData });
    }

    if (!mapInstance.getLayer(routeLayerId)) {
      mapInstance.addLayer({
        id: routeLayerId,
        type: "line",
        source: routeSourceId,
        layout: {
          "line-join": "round",
          "line-cap": "round"
        },
        paint: {
          "line-color": "#0066FF",
          "line-width": 6,
          "line-opacity": 0.8
        }
      }, driverMarker ? undefined : undefined);
    }

    if (customerMarker) customerMarker.remove();
    customerMarker = new maplibregl.Marker({ color: "#FF0000" })
      .setLngLat([destLng, destLat])
      .addTo(mapInstance);

    const segments = routeGeoJSON.properties.segments;
    if (panel && segments && segments.length > 0) {
      const steps = segments[0].steps;
      const dist = (segments[0].distance / 1000).toFixed(2);
      const dur = Math.round(segments[0].duration / 60);

      let html = `<strong>${dist} km • ${dur} min</strong><br><small>Next: ${steps[0].instruction}</small>`;
      panel.innerHTML = html;
    }

    if (!autoRetry) {
      const bounds = new maplibregl.LngLatBounds();
      activeRoutePoints.forEach(p => bounds.extend([p[1], p[0]]));
      mapInstance.fitBounds(bounds, { padding: 80 });
      setTimeout(() => { isFollowing = true; }, 3000);
    }

    lastRerouteTime = Date.now();

  } catch (e) {
    console.error("Routing Error:", e);
    if (panel) panel.innerText = "Routing failed. " + e.message;
  }
}

function checkRouteDeviation(currentPos) {
  if (!activeRoutePoints) return;

  const dist = getDistanceFromPolyline(currentPos, activeRoutePoints);

  if (dist > REROUTE_THRESHOLD_METERS) {
    const now = Date.now();
    if (now - lastRerouteTime > REROUTE_COOLDOWN_MS) {
      console.log(`⚠️ Off route by ${Math.round(dist)}m. Rerouting...`);

      if (currentOrderCard) {
        const destLat = parseFloat(currentOrderCard.dataset.lat);
        const destLng = parseFloat(currentOrderCard.dataset.lng);
        drawRoute(destLat, destLng, true);
      }
    }
  }
}

function getDistanceFromPolyline(pt, poly) {
  let min = Infinity;
  for (let i = 0; i < poly.length - 1; i++) {
    const d = distToSegment(pt, poly[i], poly[i + 1]);
    if (d < min) min = d;
  }
  return min;
}

function distToSegment(p, v, w) {
  const degToM = 111139;
  const x = p[1], y = p[0];
  const x1 = v[1], y1 = v[0];
  const x2 = w[1], y2 = w[0];
  const A = x - x1;
  const B = y - y1;
  const C = x2 - x1;
  const D = y2 - y1;
  const dot = A * C + B * D;
  const len_sq = C * C + D * D;
  let param = -1;
  if (len_sq !== 0) param = dot / len_sq;
  let xx, yy;
  if (param < 0) { xx = x1; yy = y1; }
  else if (param > 1) { xx = x2; yy = y2; }
  else { xx = x1 + param * C; yy = y1 + param * D; }
  const dx = x - xx;
  const dy = y - yy;
  return Math.sqrt(dx * dx + dy * dy) * degToM;
}

// ----------------------------------------------------------------------------
// 5. MODAL & ORDER LOGIC
// ----------------------------------------------------------------------------

async function loadDeliveredOrders() {
  try {
    const res = await fetch("/Leilife/backend/driver/get_my_orders.php");
    const data = await res.json();

    const list = document.getElementById("orderList");
    if (list) list.innerHTML = "";

    if (!data.success || !data.orders || data.orders.length === 0) {
      if (list) list.innerHTML = "<p class='no-orders'>No active deliveries.</p>";
      return;
    }

    data.orders.forEach(o => {
      const card = document.createElement('div');
      card.className = 'order-card';
      card.dataset.id = o.order_id;
      card.dataset.lat = o.latitude;
      card.dataset.lng = o.longitude;

      card.innerHTML = `
                <div style="display:flex;justify-content:space-between;">
                    <strong>Order #${o.order_number}</strong>
                    <span class="badge ${o.status}">${o.status}</span>
                </div>
                <p>Address: ${o.street_address}, ${o.barangay}, ${o.city}</p>
                <div style="margin-top:8px;">
                     <button class="btn-api btn-view">VIEW & NAVIGATE</button>
                </div>
            `;

      card.querySelector('.btn-view').onclick = () => openOrderModal(card, o);
      if (list) list.appendChild(card);
    });

  } catch (e) {
    console.error("Load Orders Error", e);
  }
}

function openOrderModal(card, orderData) {
  currentOrderCard = card;
  const modal = document.getElementById("orderModal");
  modal.style.display = "flex";

  // Fill modal info
  const body = document.getElementById("modalBody");
  body.innerHTML = `
        <h3>${orderData.first_name} ${orderData.last_name}</h3>
        <p><strong>Phone:</strong> ${orderData.phone_number}</p>
        <p><strong>Address:</strong> ${orderData.street_address}, ${orderData.barangay}, ${orderData.city}</p>
        <p style="margin-top:10px;"><strong>Items:</strong></p>
        <ul>
            ${(orderData.items || []).map(i => `<li>${i.product_name} x ${i.quantity}</li>`).join('')
    }
        </ul>
        <hr>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
             <a href="tel:${orderData.phone_number}" class="contact-btn" style="text-align:center;">📞 Call</a>
             <button id="markDeliveredBtn" class="complete-btn" style="background:#2ecc71;">✅ Delivered</button>
        </div>
    `;

  document.getElementById("markDeliveredBtn").onclick = async () => {
    if (!confirm("Are you sure this order is delivered?")) return;
    try {
      const res = await fetch("/Leilife/backend/driver/mark_delivered.php", {
        method: 'POST', body: JSON.stringify({ order_id: orderData.order_id }),
        headers: { 'Content-Type': 'application/json' }
      });
      const d = await res.json();
      if (d.success) {
        alert("Success! Order marked delivered.");
        modal.style.display = "none";
        clearRoute();
        loadDeliveredOrders();
      } else {
        alert(d.message);
      }
    } catch (e) { alert("Network Error"); }
  };

  // Trigger Layer Init & Routing
  if (orderData.latitude && orderData.longitude) {
    // Init map if first time
    if (!mapInstance) initMap();

    // Wait for modal transition/render then resize
    setTimeout(() => {
      if (mapInstance) mapInstance.resize();
      drawRoute(orderData.latitude, orderData.longitude);
    }, 300);
  } else {
    alert("This order has no GPS coordinates preserved.");
  }
}

function clearRoute() {
  if (mapInstance && mapInstance.getSource(routeSourceId)) {
    mapInstance.getSource(routeSourceId).setData({ type: "FeatureCollection", features: [] });
  }
  if (customerMarker) customerMarker.remove();
  activeRoutePoints = null;
  currentOrderCard = null;
  const p = document.getElementById("directionsPanel");
  if (p) p.innerHTML = "";
}

// ----------------------------------------------------------------------------
// 6. BOTTOM SHEET LOGIC
// ----------------------------------------------------------------------------

function initSheetLogic() {
  const orderSheet = document.getElementById('orderSheet');
  const sheetHeader = document.getElementById('sheetHeader');

  if (!orderSheet || !sheetHeader) return;

  sheetHeader.addEventListener('touchstart', startDrag);
  sheetHeader.addEventListener('mousedown', startDrag);

  // Toggle click
  sheetHeader.addEventListener('click', () => {
    if (sheetState === "expanded") updateSheet("hidden");
    else updateSheet("expanded");
  });
}

function updateSheet(state) {
  const orderSheet = document.getElementById('orderSheet');
  const sheetHeight = orderSheet.offsetHeight;
  sheetState = state;

  orderSheet.classList.remove('expanded', 'hidden');
  orderSheet.style.transition = 'transform 0.3s ease';

  if (state === "expanded") {
    orderSheet.classList.add('expanded');
    orderSheet.style.transform = `translateY(0px)`;
  } else if (state === "mid") {
    orderSheet.style.transform = `translateY(${sheetHeight * 0.55}px)`;
  } else {
    orderSheet.classList.add('hidden');
    orderSheet.style.transform = `translateY(${sheetHeight * 0.9}px)`;
  }
}

function startDrag(e) {
  if (e.target.closest("button")) return; // ignore buttons
  isDragging = true;
  startY = e.touches ? e.touches[0].clientY : e.clientY;
  const orderSheet = document.getElementById('orderSheet');
  orderSheet.style.transition = 'none';

  document.addEventListener('touchmove', onDrag);
  document.addEventListener('mousemove', onDrag);
  document.addEventListener('touchend', stopDrag);
  document.addEventListener('mouseup', stopDrag);
}

function onDrag(e) {
  if (!isDragging) return;
  const orderSheet = document.getElementById('orderSheet');
  currentY = e.touches ? e.touches[0].clientY : e.clientY;
  const deltaY = currentY - startY;
  const sheetHeight = orderSheet.offsetHeight;

  let baseOffset = 0;
  if (sheetState === "mid") baseOffset = sheetHeight * 0.55;
  if (sheetState === "hidden") baseOffset = sheetHeight * 0.9;

  let newTranslate = baseOffset + deltaY;
  newTranslate = Math.max(0, Math.min(sheetHeight * 0.95, newTranslate));

  orderSheet.style.transform = `translateY(${newTranslate}px)`;
}

function stopDrag(e) {
  if (!isDragging) return;
  isDragging = false;
  const orderSheet = document.getElementById('orderSheet');
  const sheetHeight = orderSheet.offsetHeight;

  const transform = orderSheet.style.transform;
  const match = transform.match(/translateY\(([\d.]+)px\)/);
  const currentPos = match ? parseFloat(match[1]) : 0;
  const pct = currentPos / sheetHeight;

  if (pct < 0.25) updateSheet("expanded");
  else if (pct > 0.75) updateSheet("hidden");
  else updateSheet("mid");

  document.removeEventListener('touchmove', onDrag);
  document.removeEventListener('mousemove', onDrag);
  document.removeEventListener('touchend', stopDrag);
  document.removeEventListener('mouseup', stopDrag);
}

// Modal Global Close
document.addEventListener('click', (e) => {
  if (e.target.id === "orderModal") document.getElementById("orderModal").style.display = "none";
});
const cBtn = document.querySelector(".close-btn");
if (cBtn) cBtn.onclick = () => document.getElementById("orderModal").style.display = "none";
