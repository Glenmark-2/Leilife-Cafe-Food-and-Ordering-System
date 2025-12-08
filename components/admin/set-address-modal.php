<?php
include __DIR__ . "/../buttonTemplate.php";
// <?= htmlspecialchars($userAddress["street_address"] ?? '') ?>
<div class="modal-overlay" id="modalOverlay">
  <div id="setAddressModal">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h2>Edit Address</h2>
    <form method="POST" action="/Leilife/backend/update_user_address.php">

      <div class="row">
        <select id="region" onchange="onRegionChange()" name="region">
          <option value="">-- Select Region --</option>
          <option value="130000000">NCR (National Capital Region)</option>
        </select>
        <label>Region</label>
      </div>

      <div class="row">
        <select id="province" onchange="onProvinceChange()" name="province">
          <option value="">-- Select Province --</option>
        </select>
        <label>Province</label>
      </div>

      <div class="row">
        <select id="city" onchange="onCityChange()" name="city">
          <option value="">-- Select City/Municipality --</option>
        </select>
        <label>City/Municipality</label>
      </div>

      <div class="row">
        <select id="barangay" name="barangay">
          <option value="">-- Select Barangay --</option>
        </select>
        <label>Barangay</label>
      </div>

      <div class="row">
        <input type="text" name="street_address" placeholder="Enter street address" maxlength="255" required>
        <label>Street</label>
      </div>


      <input type="hidden" name="region_name" id="region_name">
      <input type="hidden" name="province_name" id="province_name">
      <input type="hidden" name="city_name" id="city_name">


      <!-- 📍 Pin on Map Section -->
      <div class="row" style="align-items:center; gap:8px;">
        <button type="button" id="openMapModal" class="btn btn-secondary">📍 Pin on Map</button>
        <span id="pinStatus" style="font-size:0.95rem;color:#666;">No location pinned</span>
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
      </div>

      <div style="display: flex; justify-content:center; margin-top: 15px;">
        <?php
        echo createButton(
          45,
          430,
          "Save Address",
          "saveAddressBtn",
          16,
          "submit",
          ["name" => "update_address"]
        );
        ?>
      </div>

    </form>
  </div>
</div>

<!-- 📍 Map Modal -->
<div id="mapModal" class="modal-overlay" style="display:none;">
  <div id="mapContainer" style="background:#fff; padding:15px; border-radius:8px; max-width:800px; width:90%; margin:auto; margin-top:5%; box-shadow:0 6px 24px rgba(0,0,0,0.2);">
    <span class="close-btn" onclick="closeMapModal()">&times;</span>
    <h3>Pin Your Exact Location</h3>
    <p style="margin:6px 0 0;color:#555;">Click the map to place the pin, or drag the pin to adjust. Then press <b>Confirm Location</b>.</p>
    <div id="map" style="width:100%; height:420px; margin-top:10px; border-radius:6px; overflow:hidden;"></div>
    <div style="text-align:center; margin-top:10px;">
      <button type="button" class="btn btn-primary" id="confirmPinBtn">Confirm Location</button>
      <button type="button" class="btn btn-secondary" onclick="closeMapModal()">Cancel</button>
    </div>
  </div>
</div>

<!-- ✅ MapLibre -->
<link href="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.css" rel="stylesheet" />
<script src="https://unpkg.com/maplibre-gl@3.6.1/dist/maplibre-gl.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const southBarangays = [
      1, 2, 3, 4, 77, 78, 79, 80, 81, 82, 83, 84, 85,
      132, 133, 134, 135, 136, 137, 138, 139, 140,
      141, 142, 143, 144, 145, 146, 147, 148, 149, 150,
      151, 152, 153, 154, 155, 156, 157, 158, 159, 160,
      161, 162, 163, 164
    ];

    function resetDropdown(id) {
      const sel = document.getElementById(id);
      sel.innerHTML = "<option value=''>-- Select --</option>";
    }

    window.onRegionChange = function() {
      const regionSelect = document.getElementById("region");
      const regionCode = regionSelect.value;
      const regionName = regionSelect.options[regionSelect.selectedIndex]?.text || "";
      document.getElementById("region_name").value = regionName;

      resetDropdown("province");
      resetDropdown("city");
      resetDropdown("barangay");

      if (regionCode === "130000000") {
        const pSel = document.getElementById("province");
        let opt = document.createElement("option");
        opt.value = "137500000";
        opt.text = "Metro Manila";
        pSel.add(opt);
      }
    };

    window.onProvinceChange = function() {
      const provinceSelect = document.getElementById("province");
      const provinceCode = provinceSelect.value;
      const provinceName = provinceSelect.options[provinceSelect.selectedIndex]?.text || "";
      document.getElementById("province_name").value = provinceName;

      resetDropdown("city");
      resetDropdown("barangay");

      if (provinceCode === "137500000") {
        const cSel = document.getElementById("city");
        let opt = document.createElement("option");
        opt.value = "137501";
        opt.text = "Caloocan City";
        cSel.add(opt);
      }
    };

    window.onCityChange = function() {
      const citySelect = document.getElementById("city");
      const cityCode = citySelect.value;
      const cityName = citySelect.options[citySelect.selectedIndex]?.text || "";
      document.getElementById("city_name").value = cityName;

      resetDropdown("barangay");

      if (cityCode === "137501") {
        const bSel = document.getElementById("barangay");
        southBarangays.forEach(num => {
          let opt = document.createElement("option");
          opt.value = num;
          opt.text = "Barangay " + num;
          bSel.add(opt);
        });
      }
    };

    window.closeModal = function() {
      document.getElementById("modalOverlay").style.display = "none";
    };

    // --- Map + Pin setup ---
    const openMapBtn = document.getElementById("openMapModal");
    const pinStatus = document.getElementById("pinStatus");
    const latInput = document.getElementById("latitude");
    const lngInput = document.getElementById("longitude");
    const mapModal = document.getElementById("mapModal");
    const confirmBtn = document.getElementById("confirmPinBtn");
    const addressForm = document.querySelector('#setAddressModal form');

    addressForm.addEventListener('submit', function(e) {
      const lat = latInput.value.trim();
      const lng = lngInput.value.trim();
      if (!lat || !lng) {
        e.preventDefault();
        pinStatus.style.color = 'red';
        pinStatus.textContent = '❌ You must pin your location before saving.';
        return false;
      }
    });

    let map, markerEl, marker;
    const DEFAULT_CENTER = [120.9842, 14.5995]; // [lng, lat]
    const DEFAULT_ZOOM = 12;

    function openMapModal() {
      mapModal.style.display = "block";

      if (!map) {
        map = new maplibregl.Map({
          container: 'map',
          style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
          center: DEFAULT_CENTER,
          zoom: DEFAULT_ZOOM,
        });

        map.addControl(new maplibregl.NavigationControl(), 'top-right');

        map.on('click', function(e) {
          placeMarker(e.lngLat.lat, e.lngLat.lng);
        });
      } else {
        setTimeout(() => map.resize(), 200);
      }

      const existingLat = parseFloat(latInput.value) || null;
      const existingLng = parseFloat(lngInput.value) || null;

      if (existingLat && existingLng) {
        placeMarker(existingLat, existingLng, true);
        map.setCenter([existingLng, existingLat]);
        map.setZoom(16);
      } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          pos => {
            const { latitude, longitude } = pos.coords;
            map.setCenter([longitude, latitude]);
            map.setZoom(16);
            placeMarker(latitude, longitude);
          },
          () => map.setCenter(DEFAULT_CENTER),
          { enableHighAccuracy: true, timeout: 5000 }
        );
      } else {
        map.setCenter(DEFAULT_CENTER);
      }
    }

    function placeMarker(lat, lng, skipInputUpdate = false) {
      if (!markerEl) {
        markerEl = document.createElement('div');
        markerEl.className = 'map-marker';
        markerEl.style.width = '24px';
        markerEl.style.height = '24px';
        markerEl.style.background = 'url(https://cdn-icons-png.flaticon.com/512/684/684908.png) no-repeat center';
        markerEl.style.backgroundSize = 'contain';
        markerEl.style.cursor = 'grab';
      }

      if (marker) {
        marker.setLngLat([lng, lat]);
      } else {
        marker = new maplibregl.Marker({ element: markerEl, draggable: true })
          .setLngLat([lng, lat])
          .addTo(map);

        marker.on('dragend', function() {
          const pos = marker.getLngLat();
          latInput.value = pos.lat.toFixed(6);
          lngInput.value = pos.lng.toFixed(6);
          updatePinStatus();
        });
      }

      if (!skipInputUpdate) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
        updatePinStatus();
      }
    }

    function updatePinStatus() {
      const lat = latInput.value;
      const lng = lngInput.value;
      if (lat && lng) {
        pinStatus.textContent = `Pinned: ${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
        pinStatus.style.color = "#1e7e34";
      } else {
        pinStatus.textContent = "No location pinned";
        pinStatus.style.color = "#666";
      }
    }

    function closeMapModal() {
      mapModal.style.display = "none";
    }

    function confirmPin() {
      const lat = latInput.value;
      const lng = lngInput.value;
      if (!lat || !lng) {
        alert("Please click on the map to select your location.");
        return;
      }
      closeMapModal();
      updatePinStatus();
    }

    openMapBtn && openMapBtn.addEventListener('click', openMapModal);
    confirmBtn && confirmBtn.addEventListener('click', confirmPin);

    window.closeMapModal = closeMapModal;
    window.confirmPin = confirmPin;
    updatePinStatus();
  });
</script>
