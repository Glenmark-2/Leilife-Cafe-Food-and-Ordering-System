const changeBtn = document.getElementById("change");
let mode = document.getElementById("mode");
let motor = document.getElementById("motor");

changeBtn.addEventListener('click', () => {
    if (mode.textContent === "Delivery") {
        motor.src = "../public/assests/walk.png";
        mode.textContent = "Pick up";
    } else {
        motor.src = "../public/assests/motorbike.png";
        mode.textContent = "Delivery";
    }
});

// Cart items stored in memory
let cart = [];

// Example: addItem({id, name, flavor, size, price, image})
function addItem(item) {
    // check if item already exists in cart
    const existingIndex = cart.findIndex(c => 
        c.id === item.id && c.flavor === item.flavor && c.size === item.size
    );

    if (existingIndex !== -1) {
        cart[existingIndex].quantity += item.quantity;
    } else {
        cart.push(item);
    }

    renderCart();
}

// Render cart items
function renderCart() {
    const midDiv = document.getElementById("mid-div");
    midDiv.innerHTML = ""; // clear current items

    let subtotal = 0;

    cart.forEach((item, index) => {
        const itemDiv = document.createElement("div");
        itemDiv.classList.add("cart-item");

        itemDiv.innerHTML = `
            <img src="../public/products/${item.image}" alt="${item.name}" class="cart-img">
            <div class="qty-controls">
                <button onclick="changeItemQty(${index}, -1)">-</button>
                <input type="number" value="${item.quantity}" readonly>
                <button onclick="changeItemQty(${index}, 1)">+</button>
            </div>
            <p class="product-name">${item.name} ${item.flavor ? '('+item.flavor+')' : ''} [${item.size}]</p>
            <p class="product-price">₱${(item.price*item.quantity).toFixed(2)}</p>
            <button onclick="removeItem(${index})">🗑️</button>
        `;

        midDiv.appendChild(itemDiv);
        subtotal += item.price * item.quantity;
    });

    // Update totals
    const secondDiv = document.getElementById("second-div");
    const subtotalP = secondDiv.querySelectorAll(".second-div-content p")[1];
    const deliveryP = secondDiv.querySelectorAll(".second-div-content p")[3];
    const totalP = secondDiv.querySelectorAll(".second-div-content p")[5];

    subtotalP.textContent = `₱${subtotal.toFixed(2)}`;
    const deliveryFee = 50;
    deliveryP.textContent = `₱${deliveryFee.toFixed(2)}`;
    totalP.textContent = `₱${(subtotal + deliveryFee).toFixed(2)}`;
}

// Change quantity of a specific cart item
function changeItemQty(index, change) {
    cart[index].quantity += change;
    if (cart[index].quantity < 1) cart[index].quantity = 1;
    renderCart();
}

// Remove specific cart item
function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
}

function changeQty(btn, change) {
    const input = btn.parentElement.querySelector('input[type="number"]');
    let current = parseInt(input.value) || 1;
    let newValue = current + change;
    if (newValue < 1) newValue = 1;
    input.value = newValue;

    // optionally update session/cart via AJAX
}
