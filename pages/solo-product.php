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

$isDrink = in_array($product['category_id'], [7,8,9,10,11,12,13]);
?>

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
                <?php foreach ($flavors as $flavor): ?>
                    <label class="flavor-option">
                        <input type="<?= $productId == 11 ? 'checkbox' : 'radio' ?>" 
                               name="<?= $productId == 11 ? 'flavors[]' : 'flavor' ?>" 
                               value="<?= htmlspecialchars($flavor['flavor_name']) ?>" 
                               class="<?= $productId == 11 ? 'flavor-checkbox' : '' ?>">
                        <?= htmlspecialchars($flavor['flavor_name']) ?>
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
                    Medium (₱<?= number_format($product['product_price'],2) ?>)
                </label>
                <label class="flavor-option">
                    <input type="radio" name="size" value="large" onclick="updatePrice('large')">
                    Large (₱<?= number_format($product['price_large'],2) ?>)
                </label>
            </div>
        </div>
        <?php endif; ?>

        <p id="price-display" style="font-size:20px; margin-bottom:20px;">₱<?= number_format($product['product_price'],2) ?></p>

        <!-- Quantity Controls: keep original style -->
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
            echo createButton(40, 280, "Add to cart","add-to-cart-btn");
        ?>
    </div>
</div>

<script>
function changeQty(change) {
    const qty = document.getElementById('quantity');
    let val = parseInt(qty.value) || 1;
    val += change;
    if (val < 1) val = 1;
    qty.value = val;
}

function updatePrice(size){
    const priceEl = document.getElementById('price-display');
    <?php if ($isDrink): ?>
    priceEl.textContent = size==='medium' ? "₱<?= number_format($product['product_price'],2) ?>" : "₱<?= number_format($product['price_large'],2) ?>";
    <?php endif; ?>
}

document.addEventListener('DOMContentLoaded', () => {
    // limit 3 flavors
    const checkboxes = document.querySelectorAll('.flavor-checkbox');
    checkboxes.forEach(cb=>{
        cb.addEventListener('change', () => {
            const checked = document.querySelectorAll('.flavor-checkbox:checked');
            if(checked.length > 3) { alert('Select up to 3 flavors only'); cb.checked=false; }
        });
    });

    // Add to cart AJAX
    document.getElementById('add-to-cart-btn').addEventListener('click', () => {
        const productId = <?= $productId ?>;
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const size = document.querySelector('input[name="size"]:checked')?.value || 'medium';
        let flavor = '';
        const multiFlavors = Array.from(document.querySelectorAll('input[name="flavors[]"]:checked'));
        const singleFlavor = document.querySelector('input[name="flavor"]:checked');
        if(multiFlavors.length) flavor = multiFlavors.map(f=>f.value).join(', ');
        else if(singleFlavor) flavor = singleFlavor.value;

        fetch('../backend/add_to_cart.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&quantity=${quantity}&size=${size}&flavor=${encodeURIComponent(flavor)}`
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){
                // update cart dynamically
                const cartBox = document.getElementById('cart-box');
                if(cartBox){
                    cartBox.innerHTML = data.cart_html; // cart.php HTML from backend
                }
            } else {
                console.error('Add to cart error:', data.message);
            }
        });
    });
});
</script>
