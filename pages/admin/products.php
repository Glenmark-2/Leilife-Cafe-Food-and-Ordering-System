<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);
$appData->loadCategories();
$appData->loadProducts();

$mainCategories = array_unique(array_map(fn($p) => $p['main_category_name'] ?? '', $appData->products));
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
                    <option value="<?= htmlspecialchars($product['main_category_name']) ?>" selected><?= htmlspecialchars($product['main_category_name']) ?></option>
                    <?php foreach ($mainCategories as $cat): ?>
                        <?php if($cat !== $product['main_category_name']): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
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

<script>
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
        const nameInput = row.querySelector('#product-name .inputData');
        const roleInput = row.querySelector('#price-content .inputData');
        const shiftSelect = row.querySelector('.pcategory');
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
            nameInput.disabled = roleInput.disabled = shiftSelect.disabled = statusBtn.disabled = false;

            // Style editable fields same as products.php
            [nameInput, roleInput, shiftSelect].forEach(el => {
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
            // Disable fields again
            nameInput.disabled = roleInput.disabled = shiftSelect.disabled = statusBtn.disabled = true;

            // Reset field styles
            [nameInput, roleInput, shiftSelect].forEach(el => {
                el.style.padding = '0';
                el.style.border = 'none';
                el.style.borderRadius = '0';
                el.style.backgroundColor = 'transparent';
            });

            // Reset button look
            btn.textContent = 'Edit';
            btn.style.backgroundColor = '#C6C3BD';
            btn.style.color = '#22333B';

            // Enable other edit buttons
            document.querySelectorAll('.editBtn').forEach(otherBtn => {
                otherBtn.disabled = false;
                otherBtn.style.opacity = '1';
                otherBtn.style.cursor = 'pointer';
            });
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
</script>
