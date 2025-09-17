<?php
// pages/checkout.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "../components/buttonTemplate.php";
?>
<div class="checkout-container">
  <!-- LEFT COLUMN -->
  <div>
    <!-- Contact Details -->
    <div class="card">
      <h3>Contact Details</h3>

      <div class="contact-row-two">
        <!-- Full Name -->
        <div class="contact-field">
          <label for="full-name">Full Name</label>
          <input type="text" id="full-name" readonly>
        </div>

        <!-- Phone Number -->
        <div class="contact-field">
          <label for="phone">Phone Number</label>
          <div class="phone-wrapper">
            <input type="tel" id="phone" readonly>
            <!-- <button type="button" id="phone-edit-btn" class="edit-btn">Edit</button> -->
            <?php
            echo createButton(
              30,
              70,
              "Edit",
              "phone-edit-btn",
              16,
              "button",
              ['data-state' => 'edit']
            );
            ?>
          </div>
        </div>
      </div>
    </div>


    <!-- Delivery Options -->
    <div class="card">
      <h3>Delivery Options</h3>

      <label class="options">
        <input type="radio" name="delivery" value="pickup" onchange="toggleDelivery()">
        <span>Pick-Up</span>
      </label>

      <div id="pickup-options" style="display: none; margin-left: 20px; margin-top: 10px;">
        <label class="options sub-option">
          <input type="radio" name="pickup_location" value="store1">
          <span>Lunduyan Langaray Village, Barangay 14 Caloocan City</span>
        </label>
      </div>

      <label class="options">
        <input type="radio" name="delivery" value="home" onchange="toggleDelivery()">
        <span>Home Delivery</span>
      </label>

      <div id="home-options" style="display: none; margin-left: 20px; margin-top: 10px;">
        <div>
          <label for="full-address">Full Address</label>
          <textarea id="full-address" rows="2" readonly></textarea>
        </div>

        <div style="margin-top: 10px;">
          <label for="note">Notes to Rider</label>
          <textarea id="note" rows="2" readonly></textarea>
        </div>

        <div style="display: flex; justify-content:flex-end;">
          <?php
          echo createButton(
            30,
            70,
            "Edit",
            "edit-address",
            16,
            "button",
            ['data-state' => 'edit', 'name' => 'update_address']
          );

          ?>


        </div>
      </div>

    </div>



    <!-- Payment Method -->
    <div class="card">
      <h3>Payment Method</h3>
      <label class="options">
        <input type="radio" name="payment" checked>
        <span>Cash</span>
      </label>
      <label class="options">
        <input type="radio" name="payment">
        <span>GCash</span>
      </label>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="card order-summary">
    <h3>Order Summary</h3>

    <!-- Scrollable product list -->
    <div class="order-summary-items">
      <div id="order-items"></div>
    </div>

    <!-- Fixed totals + button -->
    <div class="order-summary-footer">
      <table>
        <tr>
          <td>Subtotal</td>
          <td id="subtotal" style="text-align:right;">₱0.00</td>
        </tr>
        <tr>
          <td>Delivery Fee</td>
          <td id="delivery-fee" style="text-align:right;">₱0.00</td>
        </tr>
        <tr class="total">
          <td>Total</td>
          <td id="total" style="text-align:right;">₱0.00</td>
        </tr>
      </table>
      <?php 
       echo createButton(
              40,
              300,
              "Place Order",
              "place-order-bt",
              16,
              "button",
              ['data-state' => 'edit']
            );
      ?>
    </div>
  </div>

</div>

<?php include "../components/admin/set-address-modal.php"; ?>

<script>
  // -------------------------
  // Address Edit (Open Modal)
  // -------------------------
  const addressBtn = document.getElementById("edit-address");
  const modalOverlay = document.getElementById("modalOverlay");
  const addressModalForm = modalOverlay?.querySelector("form");

  if (addressBtn && modalOverlay) {
    addressBtn.addEventListener("click", (e) => {
      e.preventDefault();
      modalOverlay.style.display = "flex";
    });
  }

  // -------------------------
  // Address Modal Submit via AJAX
  // -------------------------
  if (addressModalForm) {
    addressModalForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const fd = new FormData(addressModalForm);

      try {
        const resp = await fetch(addressModalForm.action, {
          method: "POST",
          body: fd
        });
        const result = await resp.json();

        if (result.success) {
          alert(result.message || "Address updated!");
          modalOverlay.style.display = "none";
          console.log("Opening modal...");
          window.location.href = "index.php?page=checkout-page";
        } else {
          alert(result.error || "Save failed");
        }
      } catch (err) {
        alert("Request error: " + err.message);
      }
    });
  }
</script>


<script src="../Scripts/pages/checkout-page.js" defer></script>