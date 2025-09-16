<?php
// pages/checkout.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<div class="checkout-container">
  <!-- LEFT COLUMN -->
  <div>
    <!-- Contact Details -->
    <div class="card">
      <h3>Contact Details</h3>
      <div class="name-row">
        <div>
          <label for="first-name">First Name</label>
          <input type="text" id="first-name" readonly>
        </div>
        <div>
          <label for="last-name">Last Name</label>
          <input type="text" id="last-name" readonly>
        </div>
      </div>

      <label for="phone">Phone Number</label>
      <div class="contact-row">
        <input type="tel" id="phone" readonly>
        <button type="button" id="phone-edit-btn" class="edit-btn">Edit</button>
      </div>
    </div>

    <!-- Delivery Options -->
    <div class="card">
      <h3>Delivery Options</h3>
      <label class="options">
        <input type="radio" name="delivery" value="pickup" checked onchange="toggleDelivery()">
        <span>Pick-Up</span>
      </label>

      <div id="pickup-options" style="margin-left: 20px;">
        <label class="options">
          <input type="radio" name="pickup_location" value="store1" checked>
          <span>Lunduyan Langaray Village, Barangay 14 Caloocan City</span>
        </label>
      </div>

      <label class="options">
        <input type="radio" name="delivery" value="home" onchange="toggleDelivery()">
        <span>Home Delivery</span>
      </label>

      <div id="home-options" style="display: none; margin-left: 20px; margin-top: 10px;">
        <div class="address-grid">
          <div>
            <label for="street">Street Address</label>
            <input type="text" id="street" readonly>
          </div>
          <div>
            <label for="barangay">Barangay</label>
            <input type="text" id="barangay" readonly>
          </div>
          <div>
            <label for="city">City</label>
            <input type="text" id="city" readonly>
          </div>
          <div>
            <label for="region">Region</label>
            <input type="text" id="region" readonly>
          </div>
          <div>
            <label for="postal">Postal Code</label>
            <input type="text" id="postal" readonly>
          </div>
          <div style="grid-column: span 2;">
            <label for="province">Province</label>
            <input type="text" id="province" readonly>
          </div>
        </div>

        <div style="margin-top: 10px;">
          <label for="note">Notes to Rider</label>
          <textarea id="note" rows="2" readonly></textarea>
        </div>

        <button type="button" class="edit-btn" style="margin-top: 10px;" onclick="toggleEdit()">Edit</button>
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
  <div id="order-items">
    <!-- Cart items will be injected here by JS -->
  </div>

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
  <button class="place-btn">Place Order</button>
</div>
</div>

<script src="../Scripts/pages/checkout-page.js" defer></script>
