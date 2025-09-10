<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$cart = $_SESSION['cart'] ?? [];

?>


<div id="cart-box">
    <div class="inside-div" id="first-div">
        <div id="top-div">
            <img src="../public/assests/motorbike.png" alt="motor" id="motor">
            <p id="mode">Delivery</p>
            <?php 
                include "../components/buttonTemplate.php";
                echo createButton(25,60,"Change", "change",10);
            ?> 
        </div>

        <h3>My Cart</h3>
        <div id="mid-div"></div> <!-- JS will render cart items -->
    </div>

    <div class="inside-div" id="second-div">
        <div class="second-div-content">
            <p>Subtotal</p>
            <p id="subtotal">₱0.00</p>
        </div>
        <div class="second-div-content">
            <p style="margin-top: 0;">Delivery fee</p>
            <p id="delivery-fee" style="margin-top: 0;">₱50.00</p>
        </div>
        <div class="second-div-content">
            <p><b>Total</b></p>
            <p id="total"><b>₱0.00</b></p>
        </div>

        <div class="checkout-wrapper">
        <?php echo createButton(40,280,"Check out", "check-out",18); ?>
    </div>
    </div>
</div>

<script>
const changeBtn = document.getElementById("change");
let mode = document.getElementById("mode");
let motor = document.getElementById("motor");
const deliveryFee = 50;

// Toggle Delivery / Pickup
changeBtn.addEventListener('click', () => {
    if (mode.textContent === "Delivery") {
        motor.src = "../public/assests/walk.png";
        mode.textContent = "Pick up";
    } else {
        motor.src = "../public/assests/motorbike.png";
        mode.textContent = "Delivery";
    }
});

// Cart items (from PHP session at load)
let cart = <?php echo json_encode(array_values($cart ?? [])); ?>;


// Render cart items
function renderCart() {
    const midDiv = document.getElementById("mid-div");
    midDiv.innerHTML = "";
    let subtotal = 0;
if (cart.length === 0) {
    midDiv.innerHTML = `
        <div style="display:flex; justify-content:center; align-items:center; height:80px; width:100%;">
            <p style="color:gray; margin:0;">Your cart is empty</p>
        </div>
    `;
}
    cart.forEach((item, index) => {
        const itemDiv = document.createElement("div");
        itemDiv.classList.add("cart-item");

        itemDiv.innerHTML = `
              <button onclick="removeItem(${index})" class="trash-btn">
              <img src="../public/assests/trash-bin.png" alt="trash" class="trash-icon">
            </button>
            <div class="qty-controls">

                <input type="number" value="${item.quantity}" readonly>
                <button onclick="changeItemQty(${index}, 1)">+</button>
            </div>

            <p class="product-name">${item.name} ${item.flavor ? '('+item.flavor+')' : ''} [${item.size}]</p>
            <p class="product-price">₱${(item.price * item.quantity).toFixed(2)}</p>
        `;

        midDiv.appendChild(itemDiv);
        subtotal += item.price * item.quantity;
    });

    document.getElementById("subtotal").textContent = `₱${subtotal.toFixed(2)}`;
    document.getElementById("total").textContent = `₱${(subtotal + deliveryFee).toFixed(2)}`;

    updateSession();
}



// Change item quantity
function changeItemQty(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity < 1) cart[index].quantity = 1;
    renderCart();
}

// Remove item
function removeItem(index) {
    // Remove from local cart
    cart.splice(index, 1);
    renderCart();

    // Tell PHP session to remove the item
    fetch('../backend/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(cart)
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.error("Failed to sync after remove:", data.message);
        }
    });
}

// Sync cart to PHP session
function updateSession() {
    fetch('../backend/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(cart)
    });
}

// Initial render
renderCart();
</script>
