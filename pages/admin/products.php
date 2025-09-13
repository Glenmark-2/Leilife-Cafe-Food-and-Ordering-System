<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);
$appData->loadCategories();
$appData->adminloadProducts();

// Extract unique main categories (Meals, Drinks)
$mainCategories = array_unique(
    array_map(fn($c) => $c['main_category_name'] ?? '', $appData->categories)
);
$mainCategories = array_values($mainCategories);
?>

<div id="first-row">
    <h2>Products</h2>
</div>

<div id="second-row">
    <button type="button" class="box-row clicked" data-category="All">All</button>
    <?php foreach ($mainCategories as $cat): ?>
        <button type="button" class="box-row" data-category="<?= htmlspecialchars($cat) ?>">
            <?= htmlspecialchars($cat) ?>
        </button>
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

            <!-- Product name -->
           <!-- Product name -->
<div id="product-name">
    <img id="product-photo" 
         src="<?= !empty($product['product_picture']) 
                ? "public/products/" . trim($product['product_picture']) 
                : "public/assests/image-43.png" ?>" 
         alt="product-photo">

    <!-- hidden file input for photo upload -->
    <input type="file" id="photoInput-<?= $product['product_id'] ?>" 
           class="photoInput" accept="image/*" style="display:none;">

    <div style="align-content: center; width:100%;">
        <input type="text" id="pname" class="inputData" 
               value="<?= htmlspecialchars($product['product_name']) ?>" disabled>
        <p style="font-size: .5em; color:gray; margin-left:3px;">
            #<?= htmlspecialchars($product['product_id']) ?>
        </p>
    </div>
</div>


            <!-- Price -->
            <div id="price-content">
                <input type="number" id="pprice" class="inputData" 
                       value="<?= number_format($product['product_price'],2) ?>" disabled>
            </div>

            <!-- Category -->
            <div id="category-content">
                <select id="pcategory" disabled>
                    <?php foreach ($mainCategories as $main): ?>
                        <option value="<?= htmlspecialchars($main) ?>"
                            <?= $product['main_category_name'] === $main ? 'selected' : '' ?>>
                            <?= htmlspecialchars($main) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status -->
            <div id="status-content">
                <button id="statusBtn" type="button" disabled 
                        class="<?= strtolower($product['status'] ?? 'available') === 'unavailable' ? 'clicked' : '' ?>">
                        <?= ucfirst($product['status'] ?? 'Available') ?>
                </button>
            </div>

            <!-- Edit -->
            <div id="edit-content">
                <button id="editBtn" class="editBtn" type="button">Edit</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add New Product Modal -->
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
                        <?php foreach ($mainCategories as $main): ?>
                            <option value="<?= htmlspecialchars($main) ?>"><?= htmlspecialchars($main) ?></option>
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

<script>
const BASE_URL = "http://localhost/Leilife/";

// --- Search & Filter ---
const searchInput = document.getElementById('search-input');
const categoryButtons = document.querySelectorAll('.box-row');
const productRows = document.querySelectorAll('.product-row');

function filterProducts() {
    const search = searchInput.value.toLowerCase();
    const activeCategoryBtn = document.querySelector('.box-row.clicked');
    const category = activeCategoryBtn ? activeCategoryBtn.dataset.category : 'All';

    const rows = document.querySelectorAll('.product-row');  

    rows.forEach(row => {
        const name = row.querySelector('#pname').value.toLowerCase();
        const prodCategory = row.querySelector('#pcategory option:checked').textContent;

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

// --- Edit & Save ---
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => toggleEdit(btn));
});

function toggleEdit(btn) {
    const row = btn.closest('.product-row');
    const productId = row.dataset.id;
    const nameInput = row.querySelector('#pname');
    const priceInput = row.querySelector('#pprice');
    const categorySelect = row.querySelector('#pcategory');
    const statusBtn = row.querySelector('#statusBtn');

    const isEditing = !nameInput.disabled;

    if (!isEditing) {
        // === ENTER EDIT MODE ===
        nameInput.disabled = false;
        priceInput.disabled = false;
        categorySelect.disabled = false;
        statusBtn.disabled = false;

        // Lagyan ng visual indicator na editable
        nameInput.style.border = "1px solid #888";
        priceInput.style.border = "1px solid #888";
        categorySelect.style.border = "1px solid #888";
        statusBtn.style.opacity = "1"; // mas malinaw

        btn.textContent = "Save";
        btn.style.backgroundColor = "#75c277";
        btn.style.color = "#036d2b";

        // Store original values sa dataset para ma-compare later
        row.dataset.originalName = nameInput.value;
        row.dataset.originalPrice = priceInput.value;
        row.dataset.originalCategory = categorySelect.value;
        row.dataset.originalStatus = statusBtn.textContent;

    } else {
        // === SAVE MODE ===
        const originalName = row.dataset.originalName;
        const originalPrice = row.dataset.originalPrice;
        const originalCategory = row.dataset.originalCategory;
        const originalStatus = row.dataset.originalStatus;

        const newName = nameInput.value;
        const newPrice = priceInput.value;
        const newCategory = categorySelect.value;
        const newStatus = statusBtn.textContent;

        if (
            originalName === newName &&
            originalPrice === newPrice &&
            originalCategory === newCategory &&
            originalStatus === newStatus
        ) {
            // Walang binago -> balik agad sa non-editable state
            nameInput.disabled = true;
            priceInput.disabled = true;
            categorySelect.disabled = true;
            statusBtn.disabled = true;

            // Reset styles
            nameInput.style.border = "none";
            priceInput.style.border = "none";
            categorySelect.style.border = "none";
            statusBtn.style.opacity = "0.7";

            btn.textContent = "Edit";
            btn.style.backgroundColor = "#C6C3BD";
            btn.style.color = "#22333B";

            showModal("No changes made.", "warning");
            return;
        }

        // Kung may binago → proceed sa update
        const updatedData = {
            product_id: productId,
            product_name: newName,
            product_price: newPrice,
            main_category_name: newCategory,
            status: newStatus
        };

        fetch(BASE_URL + 'backend/admin/update_product.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(updatedData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const updated = data.product;
                nameInput.value = updated.product_name;
                priceInput.value = updated.product_price;

                [...categorySelect.options].forEach(opt => {
                    opt.selected = (opt.value === updated.main_category_name);
                });
                statusBtn.textContent = updated.status;

                // balik sa non-edit mode
                nameInput.disabled = true;
                priceInput.disabled = true;
                categorySelect.disabled = true;
                statusBtn.disabled = true;

                // Reset styles
                nameInput.style.border = "none";
                priceInput.style.border = "none";
                categorySelect.style.border = "none";
                statusBtn.style.opacity = "0.7";

                btn.textContent = "Edit";
                btn.style.backgroundColor = "#C6C3BD";
                btn.style.color = "#22333B";

                showModal("Product updated successfully!", "success");
            } else {
                showModal("Failed to update product.", "error");
            }
        })
        .catch(() => showModal("Error saving product.", "error"));
    }
}


// --- Status toggle ---
document.querySelectorAll('#statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.disabled) {
            btn.textContent = btn.textContent === "Available" ? "Unavailable" : "Available";
            btn.classList.toggle("clicked");
        }
    });
});

// --- Modal ---
const addItem = document.getElementById("add-product");
const modal = document.getElementById("modal");
const cancel = document.getElementById("cancel");

addItem.addEventListener('click', () => modal.style.display = "flex");
cancel?.addEventListener('click', () => modal.style.display = "none");
document.getElementById("add").addEventListener("click", () => {
    const name = document.getElementById("name").value;
    const price = document.getElementById("price").value;
    const mainCategoryName = document.getElementById("category").value; 
    const status = "Available"; // default

    const data = {
        name: name,
        price: price,
        main_category_name: mainCategoryName,
        status: status
    };

    fetch(BASE_URL + "backend/admin/add_product.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showModal("Product added successfully!", "success");
            modal.style.display = "none";
        } else {
            showModal(data.message || "Failed to add product.", "error");
        }
    })
    .catch(err => showModal("Error: " + err, "error"));
});

function showModal(message, type = "success", autoClose = true, duration = 4000) {
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

        const style = document.createElement("style");
        style.innerHTML = `
          .notif-modal { display:none; position:fixed; z-index:10000; left:0; top:0;
                         width:100%; height:100%; background:rgba(0,0,0,0.4);
                         justify-content:center; align-items:center; }
          .notif-content { background:white; padding:20px 30px; border-radius:10px;
                           text-align:center; box-shadow:0 4px 10px rgba(0,0,0,0.3);
                           min-width:250px; animation:popin .3s ease; }
          .notif-content p { margin-bottom:15px; font-size:16px; }
          .notif-content button { padding:6px 16px; border:none; border-radius:6px;
                                  cursor:pointer; font-size:14px; color:white; }
          .notif-content button.success { background:#4caf50; }
          .notif-content button.error { background:#f44336; }
          @keyframes popin { from{transform:scale(0.8);opacity:0;} to{transform:scale(1);opacity:1;} }
        `;
        document.head.appendChild(style);
    }

    document.getElementById("notif-message").textContent = message;
    const closeBtn = document.getElementById("notif-close");
    closeBtn.className = type === "success" ? "success" : "error";

    modal.style.display = "flex";
    const closeModal = () => modal.style.display = "none";
    closeBtn.onclick = closeModal;
    modal.onclick = (e) => { if (e.target === modal) closeModal(); };

    if (autoClose) setTimeout(closeModal, duration);
}
</script>
