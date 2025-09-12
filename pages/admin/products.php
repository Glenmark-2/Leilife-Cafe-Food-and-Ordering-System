<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);
$appData->loadCategories();
$appData->loadProducts();
$mainCategories = array_unique(
    array_map(fn($c) => $c['main_category_name'] ?? '', $appData->categories)
);

// Para maayos ang order (optional)
$mainCategories = array_values($mainCategories);
?>

<div id="first-row">
    <h2>Products</h2>
</div>

<div id="second-row">
    <button type="button" class="box-row clicked" data-category="All">All</button>
    <?php foreach ($mainCategories as $cat): ?>
        <button type="button" class="box-row" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
    <?php endforeach; ?>
</div>

<hr>

<div id="third-row">
    <div id="top">
        <form class="search-bar" role="search">
            <input type="search" id="search-input" placeholder="🔍 Search product" aria-label="Search products">
        </form>
        <button type="button" id="add-product"><span>+Add new product</span></button>
    </div>

    <div id="mid">
        <input type="checkbox" class="checkbox">
        <p>Name</p>
        <p>Price</p>
        <p>Category</p>
        <p>Status</p>
        <p></p>
    </div>

    <div id="products-content">
        <?php foreach ($appData->products as $product): ?>
        <div class="product-row" data-id="<?= $product['product_id'] ?>">
            <input type="checkbox" class="checkbox">

            <div id="product-name">
                <img id="product-photo" src="<?= !empty($product['product_picture']) ? "public/products/" . trim($product['product_picture']) : "public/assests/image-43.png" ?>" alt="product-photo">
                <div style="align-content: center; width:100%;">
                    <input type="text" id="pname" class="inputData" value="<?= htmlspecialchars($product['product_name']) ?>" disabled>
                    <p style="font-size: .5em; color:gray; margin-left:3px;">#<?= htmlspecialchars($product['product_id']) ?></p>
                </div>
            </div>

            <div id="price-content">
                <input type="number" id="pprice" class="inputData" value="<?= number_format($product['product_price'],2) ?>" disabled>
            </div>

            <div id="category-content">
    <select id="pcategory" disabled>
        <option value="<?= htmlspecialchars($product['category_id']) ?>" selected>
            <?= htmlspecialchars($product['main_category_name']) ?>
        </option>
        <?php foreach ($appData->categories as $cat): ?>
            <?php if ($cat['category_id'] != $product['category_id']): ?>
                <option value="<?= htmlspecialchars($cat['category_id']) ?>">
                    <?= htmlspecialchars($cat['main_category_name']) ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
</div>


            <div id="status-content">
                <button id="statusBtn" type="button" disabled class="<?= strtolower($product['status'] ?? 'available') === 'unavailable' ? 'clicked' : '' ?>"><?= ucfirst($product['status'] ?? 'Available') ?></button>
            </div>

            <div id="edit-content">
                <button id="editBtn" class="editBtn" type="button">Edit</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal from original code -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/about us.png" alt="photo">
            <button id="uploadBtn">Upload Photo</button>
        </div>

        <div id="right">
            <form>
                <div class="form-row">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-row">
                    <label for="price">Price:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-row">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        <?php foreach ($mainCategories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-row">
                    <label>Status:</label>
                    <div id="available">Available</div>
                </div>

                <div id="buttons">
                    <button type="button" id="add">Add</button>
                    <button type="button" id="cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script >
const BASE_URL = "http://localhost/Leilife/";
// ---- Live filtering ----
const searchInput = document.getElementById('search-input');
const categoryButtons = document.querySelectorAll('.box-row');
const productRows = document.querySelectorAll('.product-row');

function filterProducts() {
    const search = searchInput.value.toLowerCase();
    const activeCategoryBtn = document.querySelector('.box-row.clicked');
    const category = activeCategoryBtn ? activeCategoryBtn.dataset.category : 'All';

    productRows.forEach(row => {
        const name = row.querySelector('#pname').value.toLowerCase();
        const prodCategory = row.querySelector('#pcategory').value;

        const matchesSearch = name.includes(search);
        const matchesCategory = category === 'All' || prodCategory === category;

        row.style.display = matchesSearch && matchesCategory ? 'flex' : 'none';
    });
}

searchInput.addEventListener('input', filterProducts);
categoryButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        categoryButtons.forEach(b => b.classList.remove('clicked'));
        btn.classList.add('clicked');
        filterProducts();
    });
});

// ---- Edit & Save ----
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.product-row');
        const productId = row.dataset.id;
        const nameInput = row.querySelector('#pname');
        const priceInput = row.querySelector('#pprice');
        const categorySelect = row.querySelector('#pcategory');
        const statusBtn = row.querySelector('#statusBtn');

        const isEditing = !nameInput.disabled;

        if (!isEditing) {
            // Disable other edit buttons
            document.querySelectorAll('.editBtn').forEach(otherBtn => {
                if (otherBtn !== btn) {
                    otherBtn.disabled = true;
                    otherBtn.style.opacity = '0.5';
                    otherBtn.style.cursor = 'not-allowed';
                }
            });

            // Enable current row fields
            nameInput.disabled = false;
            priceInput.disabled = false;
            categorySelect.disabled = false;
            statusBtn.disabled = false;

            // Style editable fields
            [nameInput, priceInput, categorySelect].forEach(el => {
                el.style.padding = '5px 10px';
                el.style.border = '1px solid black';
                el.style.borderRadius = '10px';
                el.style.backgroundColor = '#ffffff';
            });

            // Change button to Save
            btn.textContent = 'Save';
            btn.style.backgroundColor = '#75c277';
            btn.style.color = '#036d2b';

        } else {
            // Collect updated data
            const updatedData = {
    product_id: productId,
    product_name: nameInput.value,
    product_price: priceInput.value,
    category_id: categorySelect.value,  // ✅ category_id not name
    status: statusBtn.textContent
};


            // Send to backend
            fetch(BASE_URL+'backend/admin/update_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(updatedData)
            })
            .then(res => res.json())
            .then(data => {
    if (data.success) {
        // refresh row values with updated product (kasama main_category_name)
        const updated = data.product;
        nameInput.value = updated.product_name;
        priceInput.value = updated.product_price;
        categorySelect.value = updated.category_id;
        // refresh visible text ng <option>
        categorySelect.querySelector(`option[value="${updated.category_id}"]`).textContent = updated.main_category_name;
        statusBtn.textContent = updated.status;

        // disable edit mode UI
        nameInput.disabled = true;
        priceInput.disabled = true;
        categorySelect.disabled = true;
        statusBtn.disabled = true;

        btn.textContent = 'Edit';
        btn.style.backgroundColor = '#C6C3BD';
        btn.style.color = '#22333B';

        document.querySelectorAll('.editBtn').forEach(otherBtn => {
            otherBtn.disabled = false;
            otherBtn.style.opacity = '1';
            otherBtn.style.cursor = 'pointer';
        });

        showModal('Product updated successfully!', 'success');

    } else {
        showModal('Failed to update product.', 'error');
    }
})

            .catch(err => {
                console.error(err);
                showModal('Error saving product.', 'error');
            });
        }
    });
});

// ---- Status toggle ----
document.querySelectorAll('#statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.disabled) {
            btn.textContent = btn.textContent === 'Available' ? 'Unavailable' : 'Available';
            btn.classList.toggle('clicked');
        }
    });
});


// ---- Status toggle ----
document.querySelectorAll('#statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.textContent = btn.textContent === 'Available' ? 'Unavailable' : 'Available';
        btn.classList.toggle('clicked');
    });
});

// ---- Modal ----
const addItem = document.getElementById("add-product");
const modal = document.getElementById("modal");
const cancel = document.getElementById("cancel");

addItem.addEventListener('click', () => modal.style.display = "flex");
cancel?.addEventListener('click', () => modal.style.display = "none");


function showModal(message, type = "success", autoClose = true, duration = 3000) {
  // check if modal already exists
  let modal = document.getElementById("notif-modal");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "notif-modal";
    modal.className = "notif-modal";
    modal.innerHTML = `
      <div class="notif-content">
        <p id="notif-message"></p>
        <button id="notif-close">OK</button>
      </div>
    `;
    document.body.appendChild(modal);

    // basic styles
    const style = document.createElement("style");
    style.innerHTML = `
      .notif-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.4);
        justify-content: center;
        align-items: center;
      }
      .notif-content {
        background: white;
        padding: 20px 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        min-width: 250px;
        animation: popin 0.3s ease;
      }
      .notif-content p { margin-bottom: 15px; font-size: 16px; }
      .notif-content button {
        padding: 6px 16px; border: none; border-radius: 6px;
        cursor: pointer; font-size: 14px; color: white;
      }
      .notif-content button.success { background: #4caf50; }
      .notif-content button.error { background: #f44336; }
      @keyframes popin {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  }

  // set message
  document.getElementById("notif-message").textContent = message;

  // set button color based on type
  const closeBtn = document.getElementById("notif-close");
  closeBtn.className = type === "success" ? "success" : "error";

  // show modal
  modal.style.display = "flex";

  // close handlers
  const closeModal = () => modal.style.display = "none";
  closeBtn.onclick = closeModal;
  modal.onclick = (e) => { if (e.target === modal) closeModal(); };

  // auto close after duration
  if (autoClose) {
    setTimeout(() => {
      closeModal();
    }, duration);
  }
}
</script>
