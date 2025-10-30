<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../backend/db_script/db.php';
require_once __DIR__ . '/../backend/db_script/appData.php';

$productId = $_GET['id'] ?? null;
if (!$productId) die("⚠️ No product ID provided.");

$appData = new AppData($pdo);
$product = $appData->product($productId);
if (!$product) die("⚠️ Product not found or unavailable.");

$flavors = [];
if ($product['has_flavor'] === '1' && !empty($product['flavor_set_id'])) {
    $flavors = $appData->flavors($product['flavor_set_id']);
}


$isDrink = in_array($product['category_id'], [7,8,9,10,11,12,13]);
$sizes = [];
if ($product['has_size'] === '1' && $isDrink) {
    $sizes = $appData->sizes();
}

$isFavorite = false;
if (isset($_SESSION['user_id'])) {
    $isFavorite = $appData->isFavorite($_SESSION['user_id'], $productId);
}
?>

<section class="product-section">
  <div class="product-container">
    
    <!-- 🖼 Product Image -->
    <div class="product-image-wrapper">
      <img 
        src="../public/products/<?= htmlspecialchars($product['product_picture'] ?? 'default.png') ?>"
        alt="<?= htmlspecialchars($product['product_name']) ?>"
        class="product-image">
    </div>

    <!-- 📦 Product Details -->
    <div class="product-details">
      <div class="product-header">
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
            <?= htmlspecialchars(ucfirst($fl['flavor_name'])) ?>
          </label>
          <?php endforeach; ?>
        </div>
        <?php if ($productId == 11): ?>
        <p class="flavor-note">(Select up to 3 flavors)</p>
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
            <input type="radio" 
                   name="size" 
                   value="<?= $size['size_name'] ?>" 
                   <?= $size['size_name'] === 'medium' ? 'checked' : '' ?> 
                   onclick="updatePrice('<?= $size['size_name'] ?>')">
            <?= ucfirst($size['size_name']) ?> (₱<?= number_format($price ?? 0, 2) ?>)
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <p id="price-display">₱<?= number_format($product['product_price'], 2) ?></p>

      <div class="quantity-container">
        <button onclick="changeQty(-1)">-</button>
        <input id="quantity" type="number" value="1" min="1" readonly>
        <button onclick="changeQty(1)">+</button>
      </div>

      <?php
      include "../components/buttonTemplate.php";
      echo createButton(40, 280, "Add to cart", "add-to-cart-btn");
      ?>
    </div>
  </div>

  <!-- 🟡 Confirmation Modal -->
<div id="confirmAddModal" class="modal-overlay">
  <div class="modal-box">
    <h3>Confirm Add to Cart</h3>
    <p>Are you sure you want to add this item to your cart?</p>
    <div class="modal-actions">
      <button id="confirmYes" class="yes">Yes</button>
      <button id="confirmNo" class="no">No</button>
    </div>
  </div>
</div>

<!-- ✅ Success Modal -->
<div id="successModal" class="modal-overlay">
  <div class="modal-box success">
    <div class="checkmark">✔</div>
    <h3>Item Added!</h3>
    <p>Your product was successfully added to your cart.</p>
  </div>
</div>

</section>


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
        document.getElementById('price-display').textContent = `₱${(unitPrice).toFixed(2)}`;
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
           const confirmModal = document.getElementById('confirmAddModal');
  const successModal = document.getElementById('successModal');

  confirmModal.style.display = 'flex'; // show confirmation

  // Handle "No"
  document.getElementById('confirmNo').onclick = () => {
    confirmModal.style.display = 'none';
  };

   document.getElementById('confirmYes').onclick = () => {
    confirmModal.style.display = 'none'; // close confirm
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
                if(data.success){  
                   successModal.style.display = 'flex';

        // auto-close both after 2s
        setTimeout(() => {
          successModal.style.display = 'none';window.location.href='/Leilife/public/index.php?page=menu';
        }, 1000);
                  
                 }
                // else alert(data.message || 'Failed to add item');
            })
            .catch(err=>alert('Error: '+err.message));
        };
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