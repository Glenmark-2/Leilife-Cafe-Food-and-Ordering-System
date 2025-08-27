<style>
    body {
        font-family: Arial, sans-serif;
        background: #E7E0D6;
        margin: 0;
        padding: 20px;
        color: #333;
    }

    .checkout-container {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        max-width: 1000px;
        margin: auto;
    }

    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 15px;
        margin-bottom: 15px;
    }

    .card h3 {
        margin-top: 0;
        border-bottom: 1px solid #ddd;
        padding-bottom: 8px;
        font-size: 1.2rem;
    }

    .form-group {
        margin-bottom: 10px;
    }

    input[type="text"], input[type="tel"] {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background: #f5f5f5;
        transition: background 0.3s ease;
    }

    .options label {
        display: block;
        padding: 5px 0;
    }

    .order-summary img {
        width: 50px;
        height: 50px;
        border-radius: 5px;
        object-fit: cover;
    }

    .order-item {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
    }

    .order-summary table {
        width: 100%;
        margin-top: 10px;
    }

    .order-summary table td {
        padding: 4px 0;
    }

    .total {
        font-weight: bold;
    }

    .place-order-btn {
        width: 100%;
        padding: 10px;
        background: #2d3e40;
        color: #fff;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
    }
</style>

<?php
// SAMPLE DATA (Replace with DB later)
$firstName = "Nico";
$lastName = "Nico";
$phone = "+63 9123456789";
$pickupAddress = "Lunduyan, Langaray Village, Brgy. 14 Caloocan City";
$itemName = "Salt Buoag Golden Chicken Curry Fillet with Extra Rice Meal";
$itemPrice = 103.00;
$deliveryFee = 10.00;
$total = $itemPrice + $deliveryFee;

include "../components/buttonTemplate.php"; 
?>

<div class="checkout-container">
    <!-- LEFT COLUMN -->
    <div>
        <!-- Contact Details -->
        <div class="card">
            <h3>Contact Details</h3>
            <form id="contact-form">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" id="first-name" value="<?php echo $firstName; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" id="last-name" value="<?php echo $lastName; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" value="<?php echo $phone; ?>" readonly>
                </div>
                <?php echo createButton(30,80,"Edit","edit-btn",14); ?>
            </form>
        </div>

        <!-- Delivery Options -->
        <div class="card">
            <h3>Delivery Options</h3>
            <div class="options">
                <!-- Pick-Up Option -->
                <label>
                    <input type="radio" name="delivery" value="pickup" checked> Pick-Up
                </label>
                <div id="pickup-address-container" style="margin-left: 25px; margin-top: 5px;">
                    <label>
                        <input type="radio" name="pickup-location" value="store" checked disabled>
                        • <?php echo $pickupAddress; ?>
                    </label>
                </div>

                <!-- Home Delivery Option -->
                <label style="margin-top: 10px;">
                    <input type="radio" name="delivery" value="home"> Home Delivery
                </label>    
            </div>

            <!-- Home Delivery Form -->
            <div id="home-delivery-form" style="display:none; margin-top:10px;">
                <div class="form-group">
                    <label>Street Address</label>
                    <input type="text" placeholder="123 Esguerra Street">
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" placeholder="Caloocan">
                </div>
                <div class="form-group">
                    <label>Region</label>
                    <input type="text" placeholder="NCR">
                </div>
                <div class="form-group">
                    <label>Postal Code</label>
                    <input type="text" placeholder="1400">
                </div>
                <div class="form-group">
                    <label>Province</label>
                    <input type="text" placeholder="Metro Manila">
                </div>
                <div class="form-group">
                    <textarea placeholder="Notes to store/rider (optional)" 
                              style="width:100%; padding:8px; border:1px solid #ccc; border-radius:5px;"></textarea>
                </div>
                <div style="text-align: right; margin-top: 10px;">
                    <?php echo createButton(30,150,"Save Address","edit-btn",14); ?>
                </div>
            </div>
        </div>

        <!-- Payment Method -->
        <div class="card">
            <h3>Payment Method</h3>
            <div class="options">
                <label><input type="radio" name="payment" checked> Cash</label>
                <label><input type="radio" name="payment"> GCash</label>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div>
        <div class="card order-summary">
            <h3>Order Summary</h3>
            <div class="order-item">
                <img src="../public/assests/sample-product.png" alt="item">
                <div>
                    <p><?php echo $itemName; ?></p>
                    <small>₱<?php echo number_format($itemPrice, 2); ?></small>
                </div>
            </div>
            <table>
                <tr>
                    <td>Subtotal</td>
                    <td style="text-align:right;">₱<span id="subtotal"><?php echo number_format($itemPrice, 2); ?></span></td>
                </tr>
                <tr>
                    <td>Delivery Fee</td>
                    <td style="text-align:right;">₱<span id="delivery-fee"><?php echo number_format($deliveryFee, 2); ?></span></td>
                </tr>
                <tr class="total">
                    <td>Total</td>
                    <td style="text-align:right;">₱<span id="total"><?php echo number_format($total, 2); ?></span></td>
                </tr>
            </table>
            <?php echo createButton(40,280,"Place Order", "place-order-btn",18); ?>
        </div>
    </div>
</div>

<script>
    // Toggle Pickup Address & Home Delivery Form
    const deliveryOptions = document.querySelectorAll("input[name='delivery']");
    const homeDeliveryForm = document.getElementById("home-delivery-form");
    const pickupAddressContainer = document.getElementById("pickup-address-container");

    deliveryOptions.forEach(option => {
        option.addEventListener("change", () => {
            if (option.value === "home") {
                homeDeliveryForm.style.display = "block";
                pickupAddressContainer.style.display = "none";
            } else {
                homeDeliveryForm.style.display = "none";
                pickupAddressContainer.style.display = "block";
            }
        });
    });

    // Toggle Edit Mode
    const editBtn = document.getElementById("edit-btn");
    const contactInputs = document.querySelectorAll("#contact-form input");

    editBtn.addEventListener("click", () => {
        let isEditing = contactInputs[0].readOnly;
        contactInputs.forEach(input => {
            input.readOnly = !isEditing;
            input.style.background = isEditing ? "#fff" : "#f5f5f5";
            input.style.border = isEditing ? "1px solid #333" : "1px solid #ccc";
        });
        editBtn.textContent = isEditing ? "Save" : "Edit";
    });

    // Update Delivery Fee & Total
    const deliveryRadios = document.querySelectorAll("input[name='delivery']");
    const deliveryFeeElement = document.getElementById("delivery-fee");
    const totalElement = document.getElementById("total");
    const subtotalElement = document.getElementById("subtotal");

    deliveryRadios.forEach(radio => {
        radio.addEventListener("change", () => {
            let fee = radio.value === "home" ? 50 : 10; // Example fee
            let subtotal = parseFloat(subtotalElement.textContent.replace(/,/g, ''));
            deliveryFeeElement.textContent = fee.toFixed(2);
            totalElement.textContent = (subtotal + fee).toFixed(2);
        });
    });
</script>
