<?php
require_once '../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';

$productId = $_GET['id'] ?? null;
if (!$productId) die("⚠️ No product ID provided in URL.");

$stmt = $pdo->prepare("
    SELECT p.product_id, p.category_id, p.product_name, p.product_price, p.price_large,
           p.status, p.product_picture, c.category_name, c.main_category_name
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = :id
");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("⚠️ No product found.");

$flavorStmt = $pdo->prepare("SELECT flavor_id, flavor_name FROM product_flavors WHERE product_id = :id");
$flavorStmt->execute(['id' => $productId]);
$flavors = $flavorStmt->fetchAll(PDO::FETCH_ASSOC);

$isDrink = in_array($product['category_id'], [7, 8, 9, 10, 11, 12, 13]);
?>
<div style="height: 70vh;">
    <div class="product-container">
        <img src="../public/products/<?= htmlspecialchars($product['product_picture'] ?? 'default.png') ?>"
            alt="<?= htmlspecialchars($product['product_name']) ?>"
            class="product-image">

        <div class="product-details">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <h2 style="margin:0;"><?= htmlspecialchars(ucwords($product['product_name'])) ?></h2>
                <button id="heartBtn" style="border:2px solid black; background:transparent; border-radius:50%; width:35px; height:35px; font-size:16px; display:flex; align-items:center; justify-content:center; cursor:pointer;">❤</button>
            </div>

            <?php if (!empty($flavors)): ?>
                <div class="flavor-section">
                    <h3>Choose flavor<?= ($productId == 11 ? "s (up to 3)" : "") ?>:</h3>
                    <div class="flavor-options">
                        <?php foreach ($flavors as $fl): ?>
                            <label class="flavor-option">
                                <input type="<?= $productId == 11 ? 'checkbox' : 'radio' ?>"
                                    name="<?= $productId == 11 ? 'flavors[]' : 'flavor_id' ?>"
                                    value="<?= (int)$fl['flavor_id'] ?>"
                                    class="<?= $productId == 11 ? 'flavor-checkbox' : '' ?>">
                                <?= htmlspecialchars($fl['flavor_name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($productId == 11): ?>
                        <p style="font-size:12px; color:gray; margin-top:10px;">(Select up to 3 flavors)</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($isDrink): ?>
                <div class="flavor-section size-section">
                    <h3>Choose size:</h3>
                    <div class="flavor-options">
                        <label class="flavor-option">
                            <input type="radio" name="size" value="medium" checked onclick="updatePrice('medium')">
                            Medium (₱<?= number_format($product['product_price'], 2) ?>)
                        </label>
                        <label class="flavor-option">
                            <input type="radio" name="size" value="large" onclick="updatePrice('large')">
                            Large (₱<?= number_format($product['price_large'], 2) ?>)
                        </label>
                    </div>
                </div>
            <?php endif; ?>

            <p id="price-display" style="font-size:20px; margin-bottom:20px;">₱<?= number_format($product['product_price'], 2) ?></p>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <div style="display:flex; align-items:center;">
                    <button onclick="changeQty(-1)"
                        style="border:1px solid black; background-color:transparent; border-radius:50%; width:30px; height:30px; font-size:16px; cursor:pointer; margin-right:10px;">-</button>
                    <input id="quantity" type="number" value="1" min="1" readonly
                        style="width:40px; text-align:center; border:none; background:transparent; font-size:14px; margin-right:10px;">
                    <button onclick="changeQty(1)"
                        style="border:1px solid black; background-color:transparent; border-radius:50%; width:30px; height:30px; font-size:16px; cursor:pointer;">+</button>
                </div>
            </div>

            <?php
            include "../components/buttonTemplate.php";
            echo createButton(40, 280, "Add to cart", "add-to-cart-btn");
            ?>
        </div>
    </div>
</div>

<script>
    // ✅ Modal function
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

    // ✅ Quantity handler
    function changeQty(delta) {
        const qtyInput = document.getElementById('quantity');
        let current = parseInt(qtyInput.value) || 1;
        current += delta;
        if (current < 1) current = 1;
        qtyInput.value = current;
        updateDisplayedPrice();
    }

    // ✅ Size handler (drinks only)
    function updatePrice(size) {
        const input = document.querySelector(`input[name="size"][value="${size}"]`);
        if (input) input.checked = true;
        updateDisplayedPrice();
    }

    // ✅ Price recalculation
    function updateDisplayedPrice() {
        const qty = parseInt(document.getElementById('quantity').value) || 1;
        const isDrink = <?= $isDrink ? 'true' : 'false' ?>;

        const priceMedium = <?= (float)$product['product_price'] ?>;
        const priceLarge  = <?= (float)($product['price_large'] ?? 0) ?>;

        let unitPrice = priceMedium;

        if (isDrink) {
            const size = document.querySelector('input[name="size"]:checked')?.value;
            if (size === "large") unitPrice = priceLarge;
        }

        const total = unitPrice * qty;
        document.getElementById('price-display').textContent = `₱${total.toFixed(2)}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const isDrink = <?= $isDrink ? 'true' : 'false' ?>;
        const addBtn = document.getElementById('add-to-cart-btn');

        if (!addBtn) {
            console.error("Add to Cart button not found!");
            return;
        }

        updateDisplayedPrice();

        // Flavor limit check (max 3)
        const checkboxes = document.querySelectorAll('.flavor-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = document.querySelectorAll('.flavor-checkbox:checked');
                if (checked.length > 3) {
                    showModal('Select up to 3 flavors only', 'warning');
                    cb.checked = false;
                }
            });
        });

        // Add to cart
        addBtn.addEventListener('click', () => {
            const productId = <?= $productId ?>;
            const quantity = parseInt(document.getElementById('quantity').value) || 1;

            let size = null;
            if (isDrink) {
                const sizeInput = document.querySelector('input[name="size"]:checked');
                if (!sizeInput) {
                    showModal("Please select a size!", "warning");
                    return;
                }
                size = sizeInput.value;
            }

            const multiFlavors = Array.from(document.querySelectorAll('input[name="flavors[]"]:checked')).map(f => f.value);
            const singleFlavor = document.querySelector('input[name="flavor_id"]:checked');
            let flavors = [];

            const checkboxInputs = document.querySelectorAll('input[name="flavors[]"]');
            const radioInputs = document.querySelectorAll('input[name="flavor_id"]');

            if (checkboxInputs.length > 0) {
                if (multiFlavors.length === 0 || multiFlavors.length > 3) {
                    showModal("Please select 1 to 3 flavors.", "warning");
                    return;
                }
                flavors = [...new Set(multiFlavors)];
            } else if (radioInputs.length > 0) {
                if (!singleFlavor) {
                    showModal("Please select a flavor!", "warning");
                    return;
                }
                flavors = [singleFlavor.value];
            }

            const params = new URLSearchParams();
            params.append('product_id', productId);
            params.append('quantity', quantity);
            if (size) params.append('size', size);
            if (flavors.length) params.append('flavors', JSON.stringify(flavors));

            fetch('../backend/add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof fetchCart === "function") fetchCart();
                    showModal("Item added to cart!", "success");
                } else {
                    showModal(data.message || "Failed to add item", "error");
                }
            })
            .catch(err => showModal("Error adding item: " + err.message, "error"));
        });
    });
</script>
