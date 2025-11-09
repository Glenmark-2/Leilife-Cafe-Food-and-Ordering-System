<?php
// pages/checkout.php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
include "../components/buttonTemplate.php";
include "../components/modal.php";
createModal();
require_once __DIR__ . '/../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';

$appData = new AppData($pdo);
$payment_methods = $appData->getPaymentMethods();
$delivery_methods = $appData->getDeliveryMethods();
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




<div class="card">
  <h3>Delivery Options</h3>

  <?php 
  $firstEnabled = true; // track first enabled option for default selection
  foreach ($delivery_methods as $dm):
      if ($dm['status'] !== 'enabled') continue;

      // Map option_name to JS-compatible value
      $value = strtolower(str_replace([' ', '-'], '_', $dm['option_name'])); 
      $checked = $firstEnabled ? 'checked' : '';
  ?>

      <?php if (strtolower($dm['option_name']) === "pick-up" || strtolower($dm['option_name']) === "pickup"): ?>
          
          <label class="options">
            <input type="radio" name="delivery" value="pickup" onchange="toggleDelivery()" <?= $checked ?>>
            <span class="label"><?= htmlspecialchars($dm['option_name']) ?></span>
          </label>

          <div id="pickup-options" style="display: <?= $checked ? 'block' : 'none' ?>; margin-left: 20px; margin-top: 10px;">
            <label class="options sub-option">
              <input type="radio" name="pickup_location" value="store1" <?= $checked ? 'checked' : '' ?>>
              <span class="label">Lunduyan Langaray Village, Barangay 14 Caloocan City</span>
            </label>
          </div>

      <?php elseif (strtolower($dm['option_name']) === "home delivery"): ?>

          <label class="options">
            <input type="radio" name="delivery" value="home" onchange="toggleDelivery()" <?= $checked ?>>
            <span class="label"><?= htmlspecialchars($dm['option_name']) ?></span>
          </label>

          <div id="home-options" style="display: <?= $checked ? 'block' : 'none' ?>; margin-left: 20px; margin-top: 10px;">
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

      <?php endif; ?>

  <?php 
      if ($firstEnabled) $firstEnabled = false; // only first option gets checked
  endforeach; 
  ?>

</div>



    <!-- Payment Method -->
    <div class="card" style="margin-bottom: 0;">
      <h3>Payment Method</h3>
<?php foreach ($payment_methods as $pm): ?>
  <?php if ($pm['status'] == "enabled"): 
    $id = 'pm-' . strtolower(str_replace(' ', '-', $pm['method']));
    $method = strtolower($pm['method']);

    if ($method === 'cash') {
        $label = 'Cash on Delivery';
    } elseif ($method === 'gcash') {
        $label = 'E-wallet (GCash)';
    } else {
        $label = ucwords($pm['method']);
    }
  ?>
    <label class="options">
      <input 
        type="radio" 
        name="payment_method" 
        id="<?= $id ?>" 
        value="<?= htmlspecialchars($method) ?>"
      >
      <span class="label"><?= htmlspecialchars($label) ?></span>
    </label>
  <?php endif; ?>
<?php endforeach; ?>


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