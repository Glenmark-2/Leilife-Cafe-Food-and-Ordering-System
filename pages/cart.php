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
            echo createButton(25, 60, "Change", "change", 10);
            ?>
        </div>

        <h3>My Cart</h3>
        <div id="mid-div"></div>
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
            <?php
            if (!empty($_SESSION['user_id'])) {
                echo createButton(
                    40,
                    280,
                    "Check out",
                    "check-out",
                    18,
                    "button",
                    ["onclick" => "window.location.href='index.php?page=checkout-page'"]
                );
            } else {
                echo createButton(
                    40,
                    280,
                    "Check out",
                    "check-out",
                    18,
                    "button",
                    ["onclick" => "document.getElementById('loginModal').style.display='block';"]
                );
            }
            ?>
        </div>
    </div>
</div>

<script>
    // ✅ Modal function (for deletion)
    function showModal(message, type = "success", autoClose = true, duration = 2500) {
        let modal = document.getElementById("notif-modal");
        if (!modal) {
            modal = document.createElement("div");
            modal.id = "notif-modal";
            modal.style.cssText = `
                display:none; position:fixed; z-index:10000; left:0; top:0;
                width:100%; height:100%; background:rgba(0,0,0,0.4);
                justify-content:center; align-items:center;
            `;
            modal.innerHTML = `
                <div class="notif-content" style="
                    background:white; padding:20px 30px; border-radius:10px;
                    text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
                    min-width:250px; animation:popin .3s ease;
                ">
                    <p id="notif-message" style="margin-bottom:15px; font-size:16px;"></p>
                    <button id="notif-close" style="
                        padding:6px 16px; border:none; border-radius:6px;
                        cursor:pointer; font-size:14px; color:white;
                    ">OK</button>
                </div>
            `;
            document.body.appendChild(modal);

            const style = document.createElement("style");
            style.innerHTML = `
                @keyframes popin { from{transform:scale(0.8);opacity:0;} to{transform:scale(1);opacity:1;} }
            `;
            document.head.appendChild(style);
        }

        document.getElementById("notif-message").textContent = message;
        const closeBtn = document.getElementById("notif-close");

        if (type === "success") closeBtn.style.background = "#4caf50";
        else if (type === "error") closeBtn.style.background = "#f44336";
        else if (type === "warning") closeBtn.style.background = "#ff9800";

        modal.style.display = "flex";

        const closeModal = () => modal.style.display = "none";
        closeBtn.onclick = closeModal;
        modal.onclick = (e) => { if (e.target === modal) closeModal(); };

        if (autoClose) setTimeout(closeModal, duration);
    }

    const changeBtn = document.getElementById("change");
    let mode = document.getElementById("mode");
    let motor = document.getElementById("motor");

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

    let cart = [];

    async function fetchCart() {
        try {
            const res = await fetch("../backend/get_cart.php");
            const data = await res.json();
            if (data.success) {
                cart = data.cart;
                renderCart();
                updateTotals(data.totals);
            }
        } catch (err) {
            console.error("Failed to fetch cart:", err);
        }
    }

    function renderCart() {
        const midDiv = document.getElementById("mid-div");
        midDiv.innerHTML = "";

        if (!cart || cart.length === 0) {
            midDiv.innerHTML = `
                <div style="display:flex; justify-content:center; align-items:center; height:80px; width:100%;">
                    <p style="color:gray; margin:0;">Your cart is empty</p>
                </div>
            `;
            return;
        }

        cart.forEach((item, index) => {
            const itemDiv = document.createElement("div");
            itemDiv.classList.add("cart-item");

            const price = item.final_price;

            const minusOrTrash = item.quantity > 1 ?
                `<button class="qty-btn" onclick="changeItemQty(${index}, -1)">−</button>` :
                `<button class="qty-btn" onclick="removeItem(${index})">
                    <img src="../public/assests/trash-bin.png" alt="trash" class="trash-icon">
                </button>`;

            itemDiv.innerHTML = `
                <div class="qty-controls">
                    ${minusOrTrash}
                    <input type="number" value="${item.quantity}" readonly>
                    <button class="qty-btn" onclick="changeItemQty(${index}, 1)">+</button>
                </div>
                <p class="product-name">
                    ${item.product_name || "Unknown Product"} 
                    ${item.size ? ' (' + item.size + ')' : ''} 
                    ${item.flavor_names ? ' - ' + item.flavor_names : ''}
                </p>
                <p class="product-price">₱${(price * item.quantity).toFixed(2)}</p>
            `;

            midDiv.appendChild(itemDiv);
        });
    }

    function changeItemQty(index, change) {
        cart[index].quantity += change;
        if (cart[index].quantity < 1) cart[index].quantity = 1;
        updateSession();
        renderCart();
    }

    function removeItem(index) {
        const removedItem = cart[index];
        cart.splice(index, 1);
        updateSession();
        renderCart();

        // ✅ Show modal only when item is deleted
        if (removedItem) {
            showModal(`Removed "${removedItem.product_name}" from cart`, "success");
        }
    }

    function updateTotals(totals) {
        if (!totals) return;
        document.getElementById("subtotal").textContent = `₱${totals.subtotal.toFixed(2)}`;
        document.getElementById("delivery-fee").textContent = `₱${totals.delivery_fee.toFixed(2)}`;
        document.getElementById("total").textContent = `₱${totals.total.toFixed(2)}`;
    }

    function updateSession() {
        fetch('../backend/update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cart)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) updateTotals(data.totals);
            else console.error("Failed to sync cart:", data.message);
        })
        .catch(err => console.error("Error updating cart:", err));
    }

    fetchCart();
</script>

