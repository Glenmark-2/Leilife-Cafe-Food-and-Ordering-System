<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';

$productId = $_GET['id'] ?? null;
if (!$productId) die("⚠️ No product ID provided.");

// Fetch product
$stmt = $pdo->prepare("
    SELECT p.product_id, p.category_id, p.product_name, p.product_price, p.price_large,
           p.status, p.product_picture, c.category_name, c.main_category_name,
           p.has_flavor, p.has_size
    FROM products p
    JOIN categories c ON p.category_id = c.category_id
    WHERE p.product_id = :id AND p.status = 'available'
");
$stmt->execute(['id' => $productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) die("⚠️ Product not found or unavailable.");

// Fetch available flavors
$flavors = [];
if ($product['has_flavor'] === '1') {
    $flavorStmt = $pdo->query("SELECT flavor_id, flavor_name FROM product_flavors WHERE status = 'available'");
    $flavors = $flavorStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch available sizes if drink
$isDrink = in_array($product['category_id'], [7,8,9,10,11,12,13]);
$sizes = [];
if ($product['has_size'] === '1' && $isDrink) {
    $sizeStmt = $pdo->query("SELECT size_name FROM drink_size WHERE status = 'available'");
    $sizes = $sizeStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Check favorite
$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $stmtFav = $pdo->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND product_id = ? LIMIT 1");
    $stmtFav->execute([$_SESSION['user_id'], $productId]);
    $isFavorite = (bool)$stmtFav->fetch(PDO::FETCH_ASSOC);
}
?>

<div style="height:70vh;">
    <div class="product-container">
        <img src="../public/products/<?= htmlspecialchars($product['product_picture'] ?? 'default.png') ?>"
             alt="<?= htmlspecialchars($product['product_name']) ?>" class="product-image">

        <div class="product-details">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
                <h2><?= htmlspecialchars(ucwords($product['product_name'])) ?></h2>
                <button type="button" id="heartBtn" class="<?= $isFavorite ? 'active' : '' ?>">❤</button>
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

            <?php if (!empty($sizes)): ?>
            <div class="flavor-section size-section">
                <h3>Choose size:</h3>
                <div class="flavor-options">
                    <?php foreach ($sizes as $size): 
                        $price = $size['size_name'] === 'large' ? $product['price_large'] : $product['product_price'];
                    ?>
                        <label class="flavor-option">
                            <input type="radio" name="size" value="<?= $size['size_name'] ?>" <?= $size['size_name'] === 'medium' ? 'checked' : '' ?> onclick="updatePrice('<?= $size['size_name'] ?>')">
                            <?= ucfirst($size['size_name']) ?> (₱<?= number_format($price ?? 0, 2) ?>)
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <p id="price-display" style="font-size:20px; margin-bottom:20px;">₱<?= number_format($product['product_price'], 2) ?></p>

            <div style="display:flex; align-items:center; margin-bottom:30px;">
                <button onclick="changeQty(-1)">-</button>
                <input id="quantity" type="number" value="1" min="1" readonly style="width:40px; text-align:center;">
                <button onclick="changeQty(1)">+</button>
            </div>

            <?php
            include "../components/buttonTemplate.php";
            echo createButton(40, 280, "Add to cart", "add-to-cart-btn");
            ?>
        </div>
    </div>
</div>

<script>
const isDrink = <?= $isDrink ? 'true' : 'false' ?>;
const priceMedium = <?= (float)$product['product_price'] ?>;
const priceLarge = <?= (float)($product['price_large'] ?? 0) ?>;

function changeQty(delta){
    const qtyInput = document.getElementById('quantity');
    let val = parseInt(qtyInput.value) || 1;
    val += delta;
    if(val<1) val=1;
    qtyInput.value = val;
    updateDisplayedPrice();
}

function updatePrice(size){
    document.querySelector(`input[name="size"][value="${size}"]`).checked = true;
    updateDisplayedPrice();
}

function updateDisplayedPrice(){
    const qty = parseInt(document.getElementById('quantity').value) || 1;
    let unitPrice = priceMedium;
    if(isDrink){
        const size = document.querySelector('input[name="size"]:checked')?.value;
        if(size === 'large') unitPrice = priceLarge;
    }
    document.getElementById('price-display').textContent = `₱${(unitPrice*qty).toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded',()=>{
    updateDisplayedPrice();

    // Flavor max 3
    const checkboxes = document.querySelectorAll('.flavor-checkbox');
    checkboxes.forEach(cb=>{
        cb.addEventListener('change',()=>{
            const checked = document.querySelectorAll('.flavor-checkbox:checked');
            if(checked.length>3){
                alert('Select up to 3 flavors only');
                cb.checked = false;
            }
        });
    });

    // Add to cart
    const addBtn = document.getElementById('add-to-cart-btn');
    addBtn?.addEventListener('click',()=>{
        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        let size = null;
        if(isDrink){
            const sizeInput = document.querySelector('input[name="size"]:checked');
            if(!sizeInput){ alert('Please select a size!'); return; }
            size = sizeInput.value;
        }

        let flavors = [];
        const multiFlavors = Array.from(document.querySelectorAll('input[name="flavors[]"]:checked')).map(f=>f.value);
        const singleFlavor = document.querySelector('input[name="flavor_id"]:checked');
        if(multiFlavors.length>0){
            if(multiFlavors.length>3){ alert('Select up to 3 flavors'); return; }
            flavors = [...new Set(multiFlavors)];
        } else if(singleFlavor){
            flavors = [singleFlavor.value];
        }

        const params = new URLSearchParams();
        params.append('product_id', <?= $productId ?>);
        params.append('quantity', quantity);
        if(size) params.append('size', size);
        if(flavors.length) params.append('flavors', JSON.stringify(flavors));

        fetch('../backend/add_to_cart.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body: params.toString()
        })
        .then(res=>res.json())
        .then(data=>{
            if(data.success){  window.location.href='/Leilife/public/index.php?page=menu'; }
            // else alert(data.message || 'Failed to add item');
        })
        .catch(err=>alert('Error: '+err.message));
    });

    // Favorites
    const heartBtn = document.getElementById('heartBtn');
    const userId = <?= $_SESSION['user_id'] ?? 'null' ?>;
    heartBtn?.addEventListener('click', async()=>{
        if(!userId){ alert('Please log in to add favorites'); return; }
        heartBtn.classList.toggle('active');
        try{
            const resp = await fetch('../backend/favorites.php',{
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:`user_id=${userId}&product_id=<?= $productId ?>`
            });
            const result = await resp.json();
            if(!result.success){ heartBtn.classList.toggle('active'); alert(result.error||'Failed'); return; }
            // alert(result.action==='added'?'Added to favorites':'Removed from favorites');
        }catch(e){ heartBtn.classList.toggle('active'); alert('Network error'); }
    });
});
</script>
