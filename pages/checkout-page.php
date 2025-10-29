<?php
// pages/checkout.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "../components/buttonTemplate.php";
include "../components/modal.php";
createModal();
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
        <div class="contact-field ">
          <label for="phone">Phone Number</label>
          <div class="phone-wrapper">
            <input type="tel" id="phone" readonly>
            <!-- <button type="button" id="phone-edit-btn" class="edit-btn">Edit</button> -->
            
          </div>
          
        </div>
        
      </div>
      <div id="edit-div">
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


    <!-- Delivery Options -->
    <div class="card">
      <h3>Delivery Options</h3>

      <label class="options">
        <input type="radio" name="delivery" value="pickup" onchange="toggleDelivery()">
        <span class="label">Pick-Up</span>
      </label>

      <div id="pickup-options" style="display: none; margin-left: 20px; margin-top: 10px;">
        <label class="options sub-option">
          <input type="radio" name="pickup_location" value="store1">
          <span class="label">Lunduyan Langaray Village, Barangay 14 Caloocan City</span>
        </label>
      </div>

      <label class="options">
        <input type="radio" name="delivery" value="home" onchange="toggleDelivery()">
        <span class="label">Home Delivery</span>
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
    <div class="card" style="margin-bottom: 0;">
      <h3>Payment Method</h3>
      <label class="options">
        <input type="radio" name="payment_method" id="pm-cash" value="cash" checked>
        <span class="label">Cash</span>
      </label>
      <label class="options">
        <input type="radio" name="payment_method" id="pm-gcash" value="gcash">
        <span class="label">GCash</span>
      </label>
    </div>
  </div>


  <!-- RIGHT COLUMN -->
  <div class="card order-summary" style="margin: 0;">
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
      <div style="display: flex; justify-content:center">
        <?php 
       echo createButton(
              40,
              300,
              "Place Order",
              "place-order-btn",
              16,
              "button",
              ['data-state' => 'edit']
            );
      ?>
      </div>
    </div>
  </div>

</div>

<?php include "../components/admin/set-address-modal.php"; ?>
<script src="../Scripts/pages/cart.js"></script>
<script src="../Scripts/pages/checkout-page.js"></script>
