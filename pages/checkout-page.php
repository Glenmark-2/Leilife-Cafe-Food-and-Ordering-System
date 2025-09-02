


<div class="checkout-container">
  <!-- LEFT COLUMN -->
  <div>
    <!-- Contact Details -->
    <div class="card">
      <h3>Contact Details</h3>
      <div class="name-row">
        <div>
          <label for="">First Name</label>
          <input type="text" value="Nico" readonly>
        </div>
        <div>
          <label for="">Last Name</label>
          <input type="text" value="Flores" readonly>
        </div>
      </div>
      <label for="">Contact Number</label>
      <div class="contact-row">
        <input type="tel" value="+63 9123456789" readonly>
        <button class="edit-btn">Edit</button>
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
            <input type="text" id="street" value="Sample Street" readonly>
          </div>
          <div>
            <label for="city">City</label>
            <input type="text" id="city" value="Sample City" readonly>
          </div>
          <div>
            <label for="region">Region</label>
            <input type="text" id="region" value="Sample Region" readonly>
          </div>
          <div>
            <label for="postal">Postal Code</label>
            <input type="text" id="postal" value="1234" readonly>
          </div>
          <div style="grid-column: span 2;">
            <label for="province">Province</label>
            <input type="text" id="province" value="Sample Province" readonly>
          </div>
        </div>

        <div style="margin-top: 10px;">
          <label for="note">Notes to Rider</label>
          <textarea id="note" rows="2" readonly>Leave at the door</textarea>
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
    <div class="order-item">
      <img src="https://via.placeholder.com/50" alt="Item">
      <div>
        <p>Salt Buoag Golden Chicken Curry Fillet with Extra Rice Meal</p>
        <small>₱103.00</small>
      </div>
    </div>
    <table>
      <tr>
        <td>Subtotal</td>
        <td style="text-align:right;">₱103.00</td>
      </tr>
      <tr>
        <td>Delivery Fee</td>
        <td style="text-align:right;">₱10.00</td>
      </tr>
      <tr class="total">
        <td>Total</td>
        <td style="text-align:right;">₱113.00</td>
      </tr>
    </table>
    <button class="place-btn">Place Order</button>
  </div>
</div>

<script src="../Scripts/pages/checkout-page.js"></script>

