<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout Page</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #E7E0D6;
      margin: 0;
      padding: 20px;
      color: #333;
    }

    /* Main Container */
    .checkout-container {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      max-width: 1000px;
      margin: auto;
    }

    /* Card Styles */
    .card {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      padding: 20px;
      height: fit-content;
      margin-bottom: 10px;
    }

    .card h3 {
      margin: 0 0 15px;
      padding-bottom: 10px;
      border-bottom: 1px solid #ddd;
      font-size: 1.2rem;
    }

    /* Inputs */
    input[type="text"], input[type="tel"], textarea {
      width: 80%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background: #f5f5f5;
    }

    textarea {
      resize: none;
    }

    /* Name row */
    .name-row {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .name-row div {
      flex: 1;
    }

    /* Contact row */
    .contact-row {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .contact-row input {
      flex: 1;
    }

    /* Buttons */
    .edit-btn, .place-btn {
      padding: 10px 16px;
      background: #2d3e40;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
    }

    .place-btn {
      width: 100%;
      margin-top: 15px;
    }

    /* Delivery Options */
    .options {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      gap: 10px;
      cursor: pointer;
    }

    .options input[type="radio"] {
      margin: 0;
    }

    /* Order Summary */
    .order-summary {
      max-width: 320px; /* ✅ Keeps summary narrow on desktop */
      margin-left: auto;
    }

    .order-item {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }

    .order-item img {
      width: 50px;
      height: 50px;
      border-radius: 6px;
      object-fit: cover;
    }

    .order-summary table {
      width: 100%;
      margin-top: 10px;
    }

    .order-summary table td {
      padding: 5px 0;
    }

    .total {
      font-weight: bold;
    }

    /* Home delivery address grid */
    .address-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }

    #home-options input, #home-options textarea {
      background: #f5f5f5;
    }

    #home-options label {
      font-size: 0.9rem;
      margin-bottom: 3px;
      display: block;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .checkout-container {
        grid-template-columns: 1fr;
      }

      .order-summary {
        max-width: 100%;
        margin: 0;
        order: 1;
      }
    }
  </style>
</head>
<body>

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

<script>
  function toggleDelivery() {
    const isPickup = document.querySelector('input[name="delivery"][value="pickup"]').checked;
    document.getElementById('pickup-options').style.display = isPickup ? 'block' : 'none';
    document.getElementById('home-options').style.display = isPickup ? 'none' : 'block';
  }

  function toggleEdit() {
    const fields = document.querySelectorAll('#home-options input, #home-options textarea');
    const editBtn = document.querySelector('#home-options .edit-btn');
    const isReadonly = fields[0].hasAttribute('readonly');

    fields.forEach(field => {
      if (isReadonly) {
        field.removeAttribute('readonly');
        field.style.background = '#fff';
      } else {
        field.setAttribute('readonly', true);
        field.style.background = '#f5f5f5';
      }
    });

    editBtn.textContent = isReadonly ? 'Save' : 'Edit';
  }
</script>

</body>
</html>
