<?php
include __DIR__ . "/../buttonTemplate.php";

?>
<div class="modal-overlay" id="modalOverlay">
  <div id="setAddressModal">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <h2>Edit Address</h2>
    <form method="POST" action="/leilife/backend/update_user_address.php">

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

      <!-- <input type="submit" name="update_address" value="Save Address"> -->
      <div style="display: flex; justify-content:center;">
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

<script>
  const southBarangays = [
    1, 2, 3, 4,
    77, 78, 79, 80, 81, 82, 83, 84, 85,
    132, 133, 134, 135, 136, 137, 138, 139, 140,
    141, 142, 143, 144, 145, 146, 147, 148, 149, 150,
    151, 152, 153, 154, 155, 156, 157, 158, 159, 160,
    161, 162, 163, 164
  ];

  function resetDropdown(id) {
    const sel = document.getElementById(id);
    sel.innerHTML = "<option value=''>-- Select --</option>";
  }

  function onRegionChange() {
    const r = document.getElementById("region").value;
    resetDropdown("province");
    resetDropdown("city");
    resetDropdown("barangay");
    if (r === "130000000") {
      const pSel = document.getElementById("province");
      let opt = document.createElement("option");
      opt.value = "137500000";
      opt.text = "Metro Manila";
      pSel.add(opt);
    }
  }

  function onProvinceChange() {
    const p = document.getElementById("province").value;
    resetDropdown("city");
    resetDropdown("barangay");
    if (p === "137500000") {
      const cSel = document.getElementById("city");
      let opt = document.createElement("option");
      opt.value = "137501";
      opt.text = "Caloocan City";
      cSel.add(opt);
    }
  }

  function onCityChange() {
    const c = document.getElementById("city").value;
    resetDropdown("barangay");
    if (c === "137501") {
      const bSel = document.getElementById("barangay");
      southBarangays.forEach(num => {
        let opt = document.createElement("option");
        opt.value = num;
        opt.text = "Barangay " + num;
        bSel.add(opt);
      });
    }
  }

  function closeModal() {
    document.getElementById("modalOverlay").style.display = "none";
  }
document.getElementById("saveAddressBtn").addEventListener("click", () => {
    location.reload(true);
});


</script>