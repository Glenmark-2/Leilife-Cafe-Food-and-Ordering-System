<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: /leilife/public/index.php');
  exit;
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$btnText = $archived === '1' ? 'View Products' : 'View Archive';
$appData->adminloadProducts($archived);
$appData->loadCategories();
$flavors = $appData->loadFlavors();
$sizes = $appData->loadSizes();
$flavorSets = $appData->getFlavorsBySet();
$mainCategories = $appData->mainCategories();


$subCategories = array_unique(
    array_map(fn($c) => $c['category_name'] ?? '', $appData->categories)
);
$subCategories = array_values($subCategories);
// $product_info = $appData->getProductInfo($appData->products['product_id']); 

?>


<style>
    .dropdown {
        position: relative;
        display: inline-block;
        width: 220px;
        margin: 5px 0;
    }

    .dropdown-button {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        cursor: pointer;
        background: #fff;
        text-align: left;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        border: 1px solid #ccc;
        background: #fff;
        z-index: 1000;
    }

    .dropdown-item {
        padding: 8px;
        cursor: pointer;
        position: relative;
    }

    .dropdown-item:hover {
        background: #f0f0f0;
    }

    /* Submenu for flavor names */
    .submenu {
        display: none;
        position: absolute;
        left: 100%;
        top: 0;
        border: 1px solid #ccc;
        background: #fff;
        white-space: nowrap;
        z-index: 1001;
    }

    .dropdown-item:hover .submenu {
        display: block;
    }

    .submenu div {
        padding: 5px 10px;
        color: #555;
        pointer-events: none;
    }

    #new-flavors input,
    #new-category-input {
        display: block;
        margin: 5px 0;
        width: 95%;
        padding: 5px;
        border: 1px solid #ccc;
    }

    #new-flavors button {
        margin-bottom: 5px;
    }
</style>

<div id="first-row"> 
    <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
  </button>
    <h2>Products</h2>
</div>
<div class="action-btns">
        <button type="button" id="edit-flavor-size-btn"><span>Edit Flavors/Sizes</span></button>
        <button type="button" id="view-archive"><span><?= $btnText ?></span></button>
    </div>
<div id="second-row">
    <button type="button" class="box-row clicked" data-category="all">All</button>
    <?php foreach ($subCategories as $sub): ?>
        <button type="button"
            class="box-row"
            data-category="<?= htmlspecialchars(strtolower($sub)) ?>">
            <?= htmlspecialchars($sub) ?>
        </button>
    <?php endforeach; ?>
</div>

<hr>

<div id="third-row">
    <div id="top">
        <form class="search-bar" role="search" onsubmit="return false;">
            <input type="search" id="search-input" placeholder="🔍 Search product" aria-label="Search products">
        </form>
        <button type="button" id="add-product"><span>+ Add new product</span></button>
    </div>

    <div id="table-container">
        <table class="product-table" role="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="products-content">
                <?php foreach ($appData->products as $product): ?>
                    <tr class="product-row"
                        data-id="<?= $product['product_id'] ?>"
                        data-sub="<?= htmlspecialchars(strtolower($product['category_name'])) ?>">
                        <!-- Name -->
                        <td>
                            <div class="name-cell">
                                <img class="product-photo"
                                    src="<?= !empty($product['product_picture'])
                                                ? "public/products/" . trim($product['product_picture'])
                                                : "public/assests/image-43.png" ?>"
                                    alt="product-photo">
                                <input type="file" class="edit-upload" style="display:none;" accept="image/*">
                                <div>
                                    <input type="text" id="pname" class="inputData"
                                        value="<?= htmlspecialchars($product['product_name']) ?>" disabled>
                                </div>
                            </div>
                        </td>

                        <!-- Price -->
                        <td>
                            <?php if ($product['main_category_id'] == 2): ?>
                                <div style="display: flex; flex-direction: column; gap:5px">
                                    <div style="display: flex; justify-content:space-between; gap:10px">
                                        <label>Medium</label>
                                        <input type="number" id="pprice" class="inputData" min=0
                                            value="<?= isset($product['product_price']) ? number_format($product['product_price'], 2) : '' ?>" disabled>
                                    </div>
                                    <div style="display: flex; justify-content:space-between; gap:10px">
                                        <label>Large</label>
                                        <input type="number" id="pprice_large" class="inputData" min=0
                                            value="<?= isset($product['price_large']) ? number_format($product['price_large'], 2) : '' ?>" disabled>
                                    </div>
                                </div>
                            <?php else: ?>
                                <input type="number" id="pprice" class="inputData" min=0
                                    value="<?= number_format($product['product_price'] ?? 0, 2) ?>" disabled>
                            <?php endif; ?>
                        </td>

                        <!-- Category -->
                        <td>
                            <select name="category" class="pcategory" disabled>
                                <?php foreach ($appData->categories as $cat): ?>
                                    <option value="<?= (int)$cat['category_id'] ?>"
                                        <?= $product['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>

                        <!-- Status -->
                        <td>
                            <?php
                            $status = ucfirst(strtolower($product['status'] ?? 'Available'));
                            $statusClass = $status === 'Unavailable' ? 'Unavailable' : 'Available';
                            ?>
                            <button id="statusBtn" type="button" disabled
                                class="statusBtn <?= $statusClass ?>">
                                <?= $status ?>
                            </button>
                        </td>

                        <td class="actions-cell">
                            <button id="editBtn" class="editBtn" type="button">Edit</button>
                            <button class="viewBtn" data-product-id="<?= $product['product_id'] ?>">View</button>
                            <img src="public/assests/archive.png" alt="Archive" class="archive-icon">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination-container">
        <button class="pagination-btn" id="prev-page" disabled>←</button>
        <span id="page-info"></span>
        <button class="pagination-btn" id="next-page">→</button>
    </div>
</div>


<!-- Add New Product Modal -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/upload-food-img.png" alt="photo">
            <input type="file" id="uploadInput" style="display:none;" accept="image/*">
            <button id="uploadBtn">Upload Photo</button>
        </div>

        <div id="right">
            <form onsubmit="return false;">
                <div class="form-row">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-row">
                    <label for="price">Price :</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>

                <div class="form-row">
                    <label for="price_large">Price (Large, if applicable):</label>
                    <input type="number" id="price_large" name="price_large" step="0.01">
                </div>

                <div class="form-row">
                    <label>Category:</label>
                    <!-- Main Category Dropdown -->
                    <div class="dropdown" id="main-category-dropdown">
                        <div class="dropdown-button">-- Choose Main Category --</div>
                        <div class="dropdown-content">
                            <div class="dropdown-item" id="add-new-main-category-btn">+ Add new main category</div>
                            <?php foreach ($mainCategories as $cat): ?>
                                <div class="dropdown-item" data-id="<?= (int)$cat['main_category_id'] ?>">
                                    <?= htmlspecialchars($cat['main_category_name']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" id="new-main-category-input" placeholder="Enter new main category name" style="display: none;">
                    </div>

                    <!-- Subcategory Dropdown -->
                    <div class="dropdown" id="subcategory-dropdown" style="margin-top: 5px;">
                        <div class="dropdown-button">-- Choose Category --</div>
                        <div class="dropdown-content">
                            <div class="dropdown-item" id="add-new-subcategory-btn">+ Add new category</div>
                            <?php foreach ($appData->categories as $cat): ?>
                                <div class="dropdown-item" data-id="<?= (int)$cat['category_id'] ?>">
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="text" id="new-subcategory-input" placeholder="Enter new category name" style="display: none;">
                    </div>
                </div>

                <div class="form-row">
                    <label><input type="checkbox" id="has-flavor"> This product has flavors</label>
                </div>

                <div class="form-row">
                    <div class="dropdown" id="flavor-dropdown" style="display:none;">
                        <div class="dropdown-button">-- Choose Flavor Set --</div>
                        <div class="dropdown-content">
                            <?php foreach ($flavorSets as $set): ?>
                                <div class="dropdown-item" data-id="<?= $set['flavor_set_id'] ?>">
                                    Flavor Set <?= $set['flavor_set_id'] ?>
                                    <div class="submenu">
                                        <?php foreach ($set['flavor_names'] as $flavor): ?>
                                            <div><?= htmlspecialchars($flavor) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="dropdown-item" id="add-new-flavor-btn">+ Add new flavor set</div>
                        </div>
                    </div>

                    <div id="new-flavors" style="display:none;">
                        <input type="text" class="flavor-input" placeholder="Enter flavor name">
                        <button type="button" id="add-flavor-input">+ Add another flavor</button>
                    </div>
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


<!-- Edit Flavors/Sizes Modal -->
<div id="flavor-size-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit Flavors & Sizes</h3>
        <form id="flavor-size-form" onsubmit="return false;">
            <div class="form-section">
                <h4>Flavors</h4>
                <table id="flavors-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- JS will populate flavors here -->
                    </tbody>
                </table>
                <button type="button" id="add-flavor">+ Add Flavor</button>
            </div>

            <div class="form-section">
                <h4>Sizes</h4>
                <table id="sizes-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- JS will populate sizes here -->
                    </tbody>
                </table>
                <button type="button" id="add-size">+ Add Size</button>
            </div>

            <div class="modal-buttons">
                <button type="button" id="save-flavors-sizes">Save Changes</button>
                <button type="button" id="cancel-flavors-sizes">Cancel</button>
            </div>
        </form>
    </div>
</div>



<div id="product-modal" class="modal hidden">
  <div class="modal-card">
    <button class="modal-close" id="close-product-modal">&times;</button>

    <header class="modal-header">
      <h2 id="modal-title">Product Info</h2>
    </header>

    <section id="modal-body" class="modal-body">
      <!-- Fetched product data dynamically inserted here -->
    </section>

    <footer class="modal-footer">
      <button id="edit-btn" class="btn-primary">Edit</button>
      <button id="cancel-btn" class="btn-outline">Close</button>
    </footer>
  </div>
</div>

<!-- ===================== MODERN STYLES ===================== -->
<style>
  :root {
    --bg-overlay: rgba(0, 0, 0, 0.5);
    --white: #fff;
    --border: #e5e7eb;
    --primary: #2563eb;
    --primary-hover: #1e40af;
    --text-dark: #1f2937;
    --text-muted: #6b7280;
    --shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  }

  body {
    font-family: 'Poppins', sans-serif;
  }

  /* ===== Modal Base ===== */
  .modal {
    position: fixed;
    inset: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: var(--bg-overlay);
    z-index: 9999;
    animation: fadeIn 0.25s ease;
  }
  .modal.hidden { display: none; }

  .modal-card {
    background: var(--white);
    border-radius: 14px;
    width: 92%;
    max-width: 480px;
    max-height: 85vh;
    overflow-y: auto;
    padding: 20px 24px;
    box-shadow: var(--shadow);
    position: relative;
    animation: popUp 0.25s ease;
  }

  @keyframes popUp {
    from { transform: scale(0.95); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
  }

  @keyframes fadeIn {
    from { background: rgba(0, 0, 0, 0); }
    to { background: var(--bg-overlay); }
  }

  /* ===== Header ===== */
  .modal-header {
    border-bottom: 1px solid var(--border);
    padding-bottom: 10px;
    margin-bottom: 15px;
  }
  .modal-header h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text-dark);
  }

  /* ===== Body ===== */
  .modal-body {
    color: var(--text-dark);
    font-size: 15px;
    line-height: 1.6;
  }
  .modal-body p {
    display: flex;
    justify-content: space-between;
    border-bottom: 1px dashed var(--border);
    padding: 6px 0;
    margin: 0;
  }
  .modal-body p strong {
    color: var(--text-muted);
    flex: 1;
    font-weight: 500;
  }
  .modal-body p span {
    flex: 1;
    text-align: right;
    color: var(--text-dark);
  }
  .modal-body img {
    display: block;
    margin: 10px auto;
    border-radius: 8px;
    max-width: 180px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  /* ===== Footer Buttons ===== */
  .modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
  }

  .btn-primary, .btn-outline {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .btn-primary {
    background: var(--primary);
    color: #fff;
    border: none;
  }
  .btn-primary:hover { background: var(--primary-hover); }

  .btn-outline {
    border: 1px solid var(--border);
    background: #f9fafb;
    color: var(--text-dark);
  }
  .btn-outline:hover { background: #f3f4f6; }

  /* ===== Close Button ===== */
  .modal-close {
    position: absolute;
    top: 14px;
    right: 16px;
    font-size: 22px;
    color: var(--text-muted);
    background: none;
    border: none;
    cursor: pointer;
    transition: 0.2s;
  }
  .modal-close:hover { color: var(--text-dark); }

  /* ===== Responsive ===== */
  @media (max-width: 480px) {
    .modal-card { padding: 18px; }
    .modal-body p { flex-direction: column; text-align: left; }
    .modal-body p strong, .modal-body p span {
      flex: none; text-align: left;
    }
    .modal-body img { max-width: 150px; }
  }
</style>

<!-- ===================== BACKEND + LOGIC (Unchanged) ===================== -->
<script>
  const productModal = document.getElementById('product-modal');
  const closeProductModal = document.getElementById('close-product-modal');
  const modalEditBtn = document.getElementById('edit-btn');
  const modalCancelBtn = document.getElementById('cancel-btn');

  function disableAllButtons() {
    document.querySelectorAll('button:not(#edit-btn):not(#cancel-btn), .archive-icon').forEach(btn => {
      btn.disabled = true;
      btn.style.opacity = '0.5';
      btn.style.cursor = 'not-allowed';
    });
  }

  function enableAllButtons() {
    document.querySelectorAll('button, .archive-icon').forEach(btn => {
      btn.disabled = false;
      btn.style.opacity = '1';
      btn.style.cursor = 'pointer';
    });
  }

  // ==== Fetch Product Info ====
  document.querySelectorAll('.viewBtn').forEach(viewBtn => {
    viewBtn.addEventListener('click', async () => {
      const productId = viewBtn.dataset.productId;
      disableAllButtons();

      try {
        const response = await fetch(`${BASE_URL}backend/admin/fetch_product_info.php?product_id=${productId}`);
        const product = await response.json();
        if (!product) throw new Error("No product data found");

        const modalBody = document.getElementById('modal-body');
        modalBody.innerHTML = `
          <p><strong>Main Category:</strong><span>${product.main_category_name || 'N/A'}</span></p>
          <p><strong>Category:</strong><span>${product.category_name || 'N/A'}</span></p>
          <p><strong>Product Name:</strong><span>${product.product_name}</span></p>
          <p><strong>Price:</strong><span>₱${product.product_price || 'N/A'}</span></p>
          ${product.price_large ? `<p><strong>Large Price:</strong><span>₱${product.price_large}</span></p>` : ''}
          <p><strong>Status:</strong><span>${product.status}</span></p>
          <p><strong>Flavors:</strong><span>${product.flavors || 'N/A'}</span></p>
          <p><strong>Sizes:</strong><span>${product.sizes || 'N/A'}</span></p>
          ${product.product_picture ? `
            <img src="public/products/${product.product_picture}" alt="Product Image" id="p_image">
          ` : ''}
        `;

        document.getElementById('modal-title').textContent = 'Product Info';
        modalEditBtn.style.display = 'inline-block';
        modalCancelBtn.textContent = 'Close';
        modalCancelBtn.disabled = false;
        modalEditBtn.disabled = false;

        productModal.classList.remove('hidden');
      } catch (err) {
        console.error(err);
        alert("Failed to fetch product info.");
        enableAllButtons();
      }
    });
  });

  // ==== Modal Control ====
  function closeModal() {
    productModal.classList.add('hidden');
    enableAllButtons();
  }

  closeProductModal.addEventListener('click', closeModal);
  modalCancelBtn.addEventListener('click', closeModal);
  productModal.addEventListener('click', (e) => {
    if (e.target === productModal) closeModal();
  });
</script>









<!-- add product js -->
<script>
        /* ----- Flavors ----- */
        const hasFlavorCheckbox = document.getElementById("has-flavor");
        const flavorDropdown = document.getElementById("flavor-dropdown");
        const flavorButton = flavorDropdown.querySelector('.dropdown-button');
        const flavorContent = flavorDropdown.querySelector('.dropdown-content');
        const addNewFlavorBtn = document.getElementById("add-new-flavor-btn");
        const newFlavorsDiv = document.getElementById("new-flavors");
        const addFlavorInputBtn = document.getElementById("add-flavor-input");
        let selectedFlavorSetId = null;

        hasFlavorCheckbox.addEventListener('change', () => {
            flavorDropdown.style.display = hasFlavorCheckbox.checked ? 'inline-block' : 'none';
            if (!hasFlavorCheckbox.checked) {
                newFlavorsDiv.style.display = 'none';
                flavorButton.textContent = "-- Choose Flavor Set --";
                selectedFlavorSetId = null;
            }
        });

        flavorButton.addEventListener('click', () => {
            flavorContent.style.display = flavorContent.style.display === 'block' ? 'none' : 'block';
        });

        document.querySelectorAll('#flavor-dropdown .dropdown-item').forEach(item => {
            if (item.id === 'add-new-flavor-btn') return;
            item.addEventListener('click', (e) => {
                if (e.target.closest('.submenu')) return;
                selectedFlavorSetId = item.getAttribute('data-id');
                flavorButton.textContent = item.firstChild.textContent.trim();
                flavorContent.style.display = 'none';
                newFlavorsDiv.style.display = 'none';
            });
        });

        addNewFlavorBtn.addEventListener('click', () => {
            selectedFlavorSetId = null;
            flavorButton.textContent = '+ Add new flavor set';
            newFlavorsDiv.style.display = 'block';
            newFlavorsDiv.querySelectorAll('.flavor-input').forEach((input, i) => i > 0 ? input.remove() : input.value = '');
            flavorContent.style.display = 'none';
        });

        addFlavorInputBtn.addEventListener('click', () => {
            const input = document.createElement("input");
            input.type = "text";
            input.className = "flavor-input";
            input.placeholder = "Enter flavor name";
            newFlavorsDiv.insertBefore(input, addFlavorInputBtn);
        });

        /* ----- Main Category ----- */
        const mainDropdown = document.getElementById("main-category-dropdown");
        const mainButton = mainDropdown.querySelector('.dropdown-button');
        const mainContent = mainDropdown.querySelector('.dropdown-content');
        const addNewMainBtn = document.getElementById("add-new-main-category-btn");
        const newMainInput = document.getElementById("new-main-category-input");
        let selectedMainCategoryId = null;

        mainButton.addEventListener('click', () => {
            mainContent.style.display = mainContent.style.display === 'block' ? 'none' : 'block';
        });

        mainContent.querySelectorAll('.dropdown-item[data-id]').forEach(item => {
            item.addEventListener('click', () => {
                selectedMainCategoryId = item.getAttribute('data-id');
                mainButton.textContent = item.textContent;
                mainContent.style.display = 'none';
                newMainInput.style.display = 'none';
            });
        });

        addNewMainBtn.addEventListener('click', () => {
            selectedMainCategoryId = null;
            mainButton.textContent = '+ Add new main category';
            newMainInput.style.display = 'block';
            newMainInput.value = '';
            mainContent.style.display = 'none';
        });

        /* ----- Subcategory ----- */
        const subDropdown = document.getElementById("subcategory-dropdown");
        const subButton = subDropdown.querySelector('.dropdown-button');
        const subContent = subDropdown.querySelector('.dropdown-content');
        const addNewSubBtn = document.getElementById("add-new-subcategory-btn");
        const newSubInput = document.getElementById("new-subcategory-input");
        let selectedSubCategoryId = null;

        subButton.addEventListener('click', () => {
            subContent.style.display = subContent.style.display === 'block' ? 'none' : 'block';
        });

        subContent.querySelectorAll('.dropdown-item[data-id]').forEach(item => {
            item.addEventListener('click', () => {
                selectedSubCategoryId = item.getAttribute('data-id');
                subButton.textContent = item.textContent;
                subContent.style.display = 'none';
                newSubInput.style.display = 'none';
            });
        });

        addNewSubBtn.addEventListener('click', () => {
            selectedSubCategoryId = null;
            subButton.textContent = '+ Add new category';
            newSubInput.style.display = 'block';
            newSubInput.value = '';
            subContent.style.display = 'none';
        });

        /* ----- Close dropdowns when clicking outside ----- */
        document.addEventListener('click', e => {
            if (!flavorDropdown.contains(e.target)) flavorContent.style.display = 'none';
            if (!mainDropdown.contains(e.target)) mainContent.style.display = 'none';
            if (!subDropdown.contains(e.target)) subContent.style.display = 'none';
        });

        /* ----- Add Product AJAX ----- */
        document.getElementById("add").addEventListener("click", () => {
            const name = document.getElementById("name").value.trim();
            const price = parseFloat(document.getElementById("price").value.trim());
            const priceLargeInput = document.getElementById("price_large").value.trim();
            const priceLarge = priceLargeInput ? parseFloat(priceLargeInput) : null;
            const status = "Available";
            const file = document.getElementById("uploadInput").files[0];

            // Name validation
            if (!name) {
                showModal("Please enter a product name", "error");
                return;
            }

            // Price validation
            if (isNaN(price) || price <= 0) {
                showModal("Please enter a valid price", "error");
                return;
            }

            // Price large validation
            if (priceLargeInput && (isNaN(priceLarge) || priceLarge <= 0)) {
                showModal("Price (Large) must valid if provided", "error");
                return;
            }

            // Main Category validation
            let mainCategory = selectedMainCategoryId || newMainInput.value.trim();
            if (!mainCategory) {
                showModal("Please select or enter a main category", "error");
                return;
            }

            // Subcategory validation
            let subCategory = selectedSubCategoryId || newSubInput.value.trim();
            if (!subCategory) {
                showModal("Please select or enter a category", "error");
                return;
            }

            // Image validation
            if (!file) {
                showModal("Please upload a product image", "error");
                return;
            }

            // Flavors validation
            let flavors = null;
            if (hasFlavorCheckbox.checked) {
                if (selectedFlavorSetId) {
                    flavors = { existing_flavor_set_id: selectedFlavorSetId };
                } else {
                    const flavorInputs = Array.from(document.querySelectorAll('.flavor-input'));
                    const flavorNames = flavorInputs.map(f => f.value.trim()).filter(f => f);
                    if (flavorNames.length === 0) {
                        showModal("Please enter at least one flavor", "error");
                        return;
                    }
                    flavors = { new_flavors: flavorNames };
                }
            }

            // AJAX FormData
            const formData = new FormData();
            formData.append("product_name", name);
            formData.append("product_price", price);
            if (priceLargeInput) formData.append("price_large", priceLarge);
            formData.append("main_category", mainCategory);
            formData.append("category", subCategory);
            formData.append("status", status);
            formData.append("photo", file);
            if (flavors) formData.append("flavors", JSON.stringify(flavors));

            // Send via fetch
            fetch(BASE_URL + "backend/admin/add_product.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showModal("Product added successfully!", "success");
                    document.getElementById("modal").style.display = "none";

                    // Reset form
                    document.getElementById("name").value = "";
                    document.getElementById("price").value = "";
                    document.getElementById("price_large").value = "";
                    newMainInput.value = "";
                    newSubInput.value = "";
                    newMainInput.style.display = 'none';
                    newSubInput.style.display = 'none';
                    mainButton.textContent = "-- Choose Main Category --";
                    subButton.textContent = "-- Choose Category --";
                    selectedMainCategoryId = null;
                    selectedSubCategoryId = null;
                    hasFlavorCheckbox.checked = false;
                    flavorButton.textContent = "-- Choose Flavor Set --";
                    newFlavorsDiv.style.display = 'none';
                    newFlavorsDiv.querySelectorAll('.flavor-input').forEach((input, i) => i > 0 ? input.remove() : input.value = '');
                    selectedFlavorSetId = null;
                    document.getElementById("uploadInput").value = "";
                    document.getElementById("new-product-photo").src = "public/assests/image-43.png";

                    setTimeout(() => location.reload(), 1000);
                } else {
                    showModal("Error: " + data.message, "error");
                }
            })
            .catch(err => showModal("Fetch error: " + err.message, "error"));
        });

</script>

<script>
const BASE_URL = "http://localhost/Leilife/";

// --- Search & Filter ---
const searchInput = document.getElementById('search-input');
const categoryButtons = document.querySelectorAll('.box-row');
const rows = Array.from(document.querySelectorAll('.product-row'));
const prevBtn = document.getElementById('prev-page');
const nextBtn = document.getElementById('next-page');
const pageInfo = document.getElementById('page-info');

let itemsPerPage = 8;
let currentPage = 1;

// --- Original filter function retained ---
function filterProducts() {
  const search = searchInput.value.toLowerCase();
  const activeCategoryBtn = document.querySelector('.box-row.clicked');
  const category = activeCategoryBtn ? (activeCategoryBtn.dataset.category || 'all').toLowerCase() : 'all';

  document.querySelectorAll('.product-row').forEach(row => {
    const nameEl = row.querySelector('#pname');
    const name = nameEl ? (nameEl.value || '').toLowerCase() : '';
    const prodSub = row.dataset.sub ? row.dataset.sub.toLowerCase() : '';
    const matchesSearch = name.includes(search);
    const matchesCategory = category === 'all' || prodSub === category;

    if (matchesSearch && matchesCategory) {
      row.classList.remove('hidden');
    } else {
      row.classList.add('hidden');
    }
  });

  // sync pagination after filtering
  currentPage = 1;
  renderPagination();
}

// --- Pagination logic ---
function getVisibleRows() {
  return rows.filter(r => !r.classList.contains('hidden'));
}

function renderPagination() {
  const visibleRows = getVisibleRows();
  const totalPages = Math.max(1, Math.ceil(visibleRows.length / itemsPerPage));
  currentPage = Math.min(currentPage, totalPages);

  visibleRows.forEach((row, index) => {
    const start = (currentPage - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    row.style.display = (index >= start && index < end) ? '' : 'none';
  });

  pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
  prevBtn.disabled = currentPage === 1;
  nextBtn.disabled = currentPage === totalPages || totalPages === 0;
}

// --- Event bindings ---
searchInput.addEventListener('input', filterProducts);

categoryButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    categoryButtons.forEach(b => b.classList.remove('clicked'));
    btn.classList.add('clicked');
    filterProducts();
  });
});

prevBtn.addEventListener('click', () => {
  if (currentPage > 1) {
    currentPage--;
    renderPagination();
  }
});

nextBtn.addEventListener('click', () => {
  const visibleRows = getVisibleRows();
  const totalPages = Math.ceil(visibleRows.length / itemsPerPage);
  if (currentPage < totalPages) {
    currentPage++;
    renderPagination();
  }
});

// --- Initialize ---
filterProducts();



        // --- Edit & Save ---
        document.querySelectorAll('.editBtn').forEach(btn => btn.addEventListener('click', () => toggleEdit(btn)));

        function toggleEdit(btn) {
            const row = btn.closest('.product-row');
            const productId = row.dataset.id;
            const nameInput = row.querySelector('#pname');
            const priceInput = row.querySelector('#pprice');
            const priceLargeInput = row.querySelector('#pprice_large');
            const categorySelect = row.querySelector('.pcategory');
            const statusBtn = row.querySelector('#statusBtn');
            const photo = row.querySelector('.product-photo');
            const fileInput = row.querySelector('.edit-upload');
            const isEditing = !nameInput.disabled;

            if (!isEditing) {
                // Enable edit mode
                [nameInput, priceInput, priceLargeInput, categorySelect, statusBtn].forEach(el => {
                    if (el) el.disabled = false;
                });
                row.classList.add('editing');
                btn.textContent = "Save";
                btn.style.backgroundColor = "#75c277";
                btn.style.color = "#036d2b";

                // Store original values
                row.dataset.originalName = nameInput.value;
                row.dataset.originalPrice = priceInput ? priceInput.value : '';
                if (priceLargeInput) row.dataset.originalPriceLarge = priceLargeInput.value;
                row.dataset.originalCategory = categorySelect.value;
                row.dataset.originalStatus = statusBtn.textContent;

                // Photo upload
                photo.style.cursor = "pointer";
                photo.onclick = () => fileInput.click();
                fileInput.onchange = e => {
                    const file = e.target.files[0];
                    if (file) photo.src = URL.createObjectURL(file);
                };

                // Status toggle
                statusBtn.onclick = () => {
                    const newStatus = statusBtn.textContent === "Available" ? "Unavailable" : "Available";
                    statusBtn.textContent = newStatus;
                    statusBtn.classList.remove("Available", "Unavailable");
                    statusBtn.classList.add(newStatus);
                };

                // Disable other edit buttons
                document.querySelectorAll('.editBtn').forEach(b => {
                    if (b !== btn) {
                        b.disabled = true;
                        b.style.opacity = "0.5";
                        b.style.cursor = "not-allowed";
                    }
                });

                // Disable ALL view buttons while editing
                document.querySelectorAll('.viewBtn').forEach(v => {
                    v.disabled = true;
                    v.style.opacity = "0.5";
                    v.style.cursor = "not-allowed";
                });

        const editFlavorBtn = document.getElementById("edit-flavor-size-btn");
        const viewArchiveBtn = document.getElementById("view-archive");

        if (editFlavorBtn) {
            editFlavorBtn.disabled = true;
            editFlavorBtn.style.opacity = "0.5";
            editFlavorBtn.style.cursor = "not-allowed";
        }

        if (viewArchiveBtn) {
            viewArchiveBtn.disabled = true;




            
            viewArchiveBtn.style.opacity = "0.5";
            viewArchiveBtn.style.cursor = "not-allowed";
        }



                    const addBtn = document.getElementById("add-product");
                    addBtn.disabled = true;
                    addBtn.style.opacity = "0.5";

                } else {
                    // Save changes
                    const changed =
                        row.dataset.originalName !== nameInput.value ||
                        row.dataset.originalPrice !== (priceInput ? priceInput.value : '') ||
                        (priceLargeInput && row.dataset.originalPriceLarge !== priceLargeInput.value) ||
                        row.dataset.originalCategory !== categorySelect.value ||
                        row.dataset.originalStatus !== statusBtn.textContent ||
                        (fileInput && fileInput.files && fileInput.files.length > 0);

                    if (!changed) {
                        disableRow(row, btn);
                        showModal("No changes made.", "warning");
                        return;
                    }

                    // Build FormData (send both prices, empty strings if blank)
                    const formData = new FormData();
                    formData.append("product_id", productId);
                    formData.append("product_name", nameInput.value);
                    formData.append("product_price", priceInput ? priceInput.value.trim() : '');
                    if (priceLargeInput) {
                        formData.append("price_large", priceLargeInput.value.trim());
                    }
                    formData.append("category_id", categorySelect.value);
                    formData.append("status", statusBtn.textContent.trim());
                    if (fileInput && fileInput.files[0]) formData.append("photo", fileInput.files[0]);

                    // Send to backend
                    fetch(BASE_URL + 'backend/admin/update_product.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                const updated = data.product;
                                const statusText = updated.status === "Unavailable" ? "Unavailable" : "Available";
                                statusBtn.textContent = statusText;
                                statusBtn.classList.remove("Available", "Unavailable");
                                statusBtn.classList.add(statusText);

                                if (updated.product_picture) {
                                    row.querySelector(".product-photo").src = BASE_URL + "public/products/" + updated.product_picture;
                                }

                                disableRow(row, btn);
                                showModal("Product updated successfully!", "success");
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                showModal(data.message || "Failed to update product.", "error");
                                console.error(data.message);
                            }
                        })
                        .catch(() => showModal("Error saving product.", "error"));
                }
            }

            function disableRow(row, btn) {
                row.querySelectorAll('#pname, #pprice, #pprice_large, .pcategory, #statusBtn').forEach(el => {
                    if (el) {
                        el.disabled = true;
                        el.onclick = null;
                    }
                });
                row.classList.remove('editing');
                btn.textContent = "Edit";
                btn.style.backgroundColor = "#ffc107";
                btn.style.color = "#22333B";

                document.querySelectorAll('.editBtn').forEach(b => {
                    b.disabled = false;
                    b.style.opacity = "1";
                    b.style.cursor = "pointer";
                });

                document.querySelectorAll('.viewBtn').forEach(v => {
                    v.disabled = false;
                    v.style.opacity = "1";
                    v.style.cursor = "pointer";
                });

                const editFlavorBtn = document.getElementById("edit-flavor-size-btn");
        const viewArchiveBtn = document.getElementById("view-archive");

        if (editFlavorBtn) {
            editFlavorBtn.disabled = false;
            editFlavorBtn.style.opacity = "1";
            editFlavorBtn.style.cursor = "pointer";
        }

        if (viewArchiveBtn) {
            viewArchiveBtn.disabled = true;
            viewArchiveBtn.style.opacity = "1";
            viewArchiveBtn.style.cursor = "pointer";
        }


                const addBtn = document.getElementById("add-product");
                addBtn.disabled = false;
                addBtn.style.opacity = "1";
            }

            // --- Modal Add Product ---
            document.getElementById("add-product").addEventListener("click", () => {
                document.getElementById("modal").style.display = "flex";
            });
            document.getElementById("cancel").addEventListener("click", () => {
                document.getElementById("modal").style.display = "none";
            });
            document.getElementById("uploadBtn").addEventListener("click", () => {
                document.getElementById("uploadInput").click();
            });
            document.getElementById("uploadInput").addEventListener("change", (e) => {
                const file = e.target.files[0];
                if (file) {
                    document.getElementById("new-product-photo").src = URL.createObjectURL(file);
                }
            });

            // document.getElementById("add").addEventListener("click", () => {
            //     const name = document.getElementById("name").value.trim();
            //     const price = document.getElementById("price").value.trim();
            //     const priceLarge = document.getElementById("price_large").value.trim();
            //     const category = document.getElementById("add-category").value;
            //     const status = "Available";
            //     const file = document.getElementById("uploadInput").files[0];

            //     if (!name || !price || !category) {
            //         showModal("Please fill all fields", "error");
            //         return;
            //     }

            //     const formData = new FormData();
            //     formData.append("product_name", name);
            //     formData.append("product_price", price);
            //     if (priceLarge) formData.append("price_large", priceLarge);
            //     formData.append("category_id", category);
            //     formData.append("status", status);
            //     if (file) formData.append("photo", file);

            //     fetch(BASE_URL + "backend/admin/add_product.php", {
            //             method: "POST",
            //             body: formData
            //         })
            //         .then(res => res.json())
            //         .then(data => {
            //             if (data.success) {
            //                 showModal("Product added successfully!", "success");
            //                 document.getElementById("modal").style.display = "none";
            //                 document.getElementById("name").value = "";
            //                 document.getElementById("price").value = "";
            //                 document.getElementById("add-category").value = "";
            //                 document.getElementById("uploadInput").value = "";
            //                 document.getElementById("new-product-photo").src = "public/assests/image-43.png";
            //                 setTimeout(() => location.reload(), 1000);
            //             } else {
            //                 showModal("Error: " + data.message, "error");
            //             }
            //         })
            //         .catch(err => showModal("Fetch error: " + err.message, "error"));
            // });

            // --- Notifications ---
            function showModal(message, type = "success", autoClose = true, duration = 3000) {
                let modal = document.getElementById("notif-modal");
                if (!modal) {
                    modal = document.createElement("div");
                    modal.id = "notif-modal";
                    modal.className = "notif-modal";
                    modal.innerHTML = `
                <div class="notif-content">
                    <p id="notif-message"></p>
                    <button id="notif-close">OK</button>
                </div>`;
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
                .notif-content button.warning { background:#ff9800; }
                @keyframes popin { from{transform:scale(0.8);opacity:0;} to{transform:scale(1);opacity:1;} }
                `;
                    document.head.appendChild(style);
                }

                document.getElementById("notif-message").textContent = message;
                const closeBtn = document.getElementById("notif-close");
                closeBtn.className = type;

                modal.style.display = "flex";
                const closeModal = () => modal.style.display = "none";
                closeBtn.onclick = closeModal;
                modal.onclick = (e) => {
                    if (e.target === modal) closeModal();
                };

                if (autoClose) setTimeout(closeModal, duration);
            }

            function formatCategoryName(str) {
                if (!str) return "";
                return str
                    .toLowerCase()
                    .split(/[_\s]+/)
                    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                    .join(" ");
            }
            document.querySelectorAll(".box-row").forEach(btn => {
                btn.textContent = formatCategoryName(btn.textContent);
            });
            document.querySelectorAll(".pcategory option").forEach(opt => {
                opt.textContent = formatCategoryName(opt.textContent);
            });
            document.querySelectorAll("#add-category option").forEach(opt => {
                opt.textContent = formatCategoryName(opt.textContent);
            });

            const viewArchiveBtn = document.getElementById('view-archive');
            viewArchiveBtn.addEventListener('click', () => {
                resetEditButtons();
                const url = new URL(window.location.href);
                if (url.searchParams.get('archived') === '1') {
                    url.searchParams.set('archived', '0');
                } else {
                    url.searchParams.set('archived', '1');
                }
                window.location.href = url.toString();
            });

            // document.querySelectorAll('.archive-icon').forEach(icon => {
            //     icon.addEventListener('click', () => {
            //         const row = icon.closest('.product-row');
            //         const productId = row.dataset.id;

            //         // check current "archived" state from URL
            //         const url = new URL(window.location.href);
            //         const archivedMode = url.searchParams.get('archived') === '1';

            //         // if we are viewing archive, unarchive on click
            //         const isArchive = archivedMode ? 0 : 1;

            //         const formData = new FormData();
            //         formData.append('product_id', productId);
            //         formData.append('is_archive', isArchive);

            //         fetch(BASE_URL + "backend/admin/archive_product.php", {
            //                 method: "POST",
            //                 body: formData
            //             })
            //             .then(res => res.json())
            //             .then(data => {
            //                 if (data.success) {

            //                     showModal(isArchive ? "Product archived!" : "Product restored!", "success");
            //                     disableRow(row, row.querySelector('.editBtn'));
            //                     row.remove(); // remove from table since it no longer belongs in this view
            //                 } else {
            //                     showModal("Error: " + data.message, "error");
            //                 }
            //             })
            //             .catch(err => showModal("Fetch error: " + err.message, "error"));
            //     });
            // });


            document.querySelectorAll('.archive-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const row = icon.closest('.product-row');
            const productId = row.dataset.id;

            // check current "archived" state from URL
            const url = new URL(window.location.href);
            const archivedMode = url.searchParams.get('archived') === '1';

            // if we are viewing archive, unarchive on click
            const isArchive = archivedMode ? 0 : 1;

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('is_archive', isArchive);

            fetch(BASE_URL + "backend/admin/archive_product.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showModal(isArchive ? "Product archived!" : "Product restored!", "success");
                        disableRow(row, row.querySelector('.editBtn'));
                        row.remove(); // remove from table since it no longer belongs in this view

                        // AUTO RELOAD after a short delay so user sees the modal
                        setTimeout(() => {
                            window.location.reload();
                        }, 500); // 0.5s delay
                    } else {
                        showModal("Error: " + data.message, "error");
                    }
                })
                .catch(err => showModal("Fetch error: " + err.message, "error"));
        });
    });


            // --- Image validation ---
            const allowedTypes = ["image/png", "image/jpeg", "image/webp"];
            const uploadInput = document.getElementById("uploadInput");
            const newProductPhoto = document.getElementById("new-product-photo");

            const fileErrorModal = document.createElement("div");
            fileErrorModal.id = "file-error-modal";
            fileErrorModal.className = "notif-modal";
            fileErrorModal.innerHTML = `
        <div class="notif-content">
            <p id="file-error-message"></p>
            <button id="file-error-close">OK</button>
        </div>
        `;
            document.body.appendChild(fileErrorModal);

            document.getElementById("file-error-close").onclick = () => {
                fileErrorModal.style.display = "none";
                uploadInput.value = "";
            };

            uploadInput.addEventListener("change", (e) => {
                const file = e.target.files[0];
                if (file) {
                    if (!allowedTypes.includes(file.type)) {
                        document.getElementById("file-error-message").textContent =
                            "Invalid file type. Allowed types: PNG, JPG, JPEG, WEBP.";
                        fileErrorModal.style.display = "flex";
                        return;
                    }
                    newProductPhoto.src = URL.createObjectURL(file);
                }
            });

            // initial filter to apply (in case some rows are hidden by server-side logic)
            document.addEventListener('DOMContentLoaded', () => {
                filterProducts();
            });



            // --- Flavor & Size Modal ---
            const FLAVORS_DATA = <?= json_encode($flavors) ?>;
            const SIZES_DATA = <?= json_encode($sizes) ?>;
            const flavorSizeBtn = document.getElementById('edit-flavor-size-btn');
            const flavorSizeModal = document.getElementById('flavor-size-modal');
            const cancelFlavorSizeBtn = document.getElementById('cancel-flavors-sizes');
            const saveFlavorSizeBtn = document.getElementById('save-flavors-sizes');
            const flavorsTable = document.querySelector('#flavors-table tbody');
            const sizesTable = document.querySelector('#sizes-table tbody');
            const addFlavorBtn = document.getElementById('add-flavor');
            const addSizeBtn = document.getElementById('add-size');

            // Open modal
            flavorSizeBtn.addEventListener('click', () => {
                flavorSizeModal.style.display = 'flex';
                loadFlavorsSizes(); // fetch current flavors/sizes for product
            });

            // Close modal
            cancelFlavorSizeBtn.addEventListener('click', () => {
                flavorSizeModal.style.display = 'none';
            });

            // Add new row
            addFlavorBtn.addEventListener('click', () => addRow(flavorsTable));
            addSizeBtn.addEventListener('click', () => addRow(sizesTable));

            function addRow(table, id = '', name = '', status = 'Available', isSize = false) {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td>
                    <input type="text" value="${name}" placeholder="Enter name" ${isSize ? 'disabled' : ''}>
                </td>
                <td>
                    <select>
                        <option value="Available" ${status.toLowerCase() === 'available' ? 'selected' : ''}>Available</option>
                        <option value="Unavailable" ${status.toLowerCase() === 'unavailable' ? 'selected' : ''}>Unavailable</option>
                    </select>
                </td>
            `;
                if (id) row.dataset.id = id;
                table.appendChild(row);
            }


            function loadFlavorsSizes() {
                flavorsTable.innerHTML = '';
                sizesTable.innerHTML = '';

                FLAVORS_DATA.forEach(f => addRow(flavorsTable, f.flavor_id, f.flavor_name, f.status));
                SIZES_DATA.forEach(s => addRow(sizesTable, s.size_id, s.size_name, s.status, true));
            }



            // Save changes (with detailed debugging)
            saveFlavorSizeBtn.addEventListener('click', () => {
                const flavors = Array.from(flavorsTable.querySelectorAll('tr')).map(row => ({
                    id: row.dataset.id || '',
                    name: row.querySelector('input').value.trim(),
                    status: row.querySelector('select').value
                }));

                const sizes = Array.from(sizesTable.querySelectorAll('tr')).map(row => ({
                    id: row.dataset.id || '',
                    name: row.querySelector('input').value.trim(),
                    status: row.querySelector('select').value
                }));

                console.log('🔹 Sending to backend:', {
                    flavors,
                    sizes
                });

                fetch(BASE_URL + 'backend/admin/edit_flavors_sizes.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            flavors,
                            sizes
                        })
                    })

                    .then(async (res) => {
                        const text = await res.text(); // get raw text for debugging
                        console.log('🔹 Raw server response:', text);

                        try {
                            const data = JSON.parse(text);
                            console.log('✅ Parsed JSON:', data);

                            if (data.success) {
                                showModal('Flavors & sizes updated!', 'success');
                                flavorSizeModal.style.display = 'none';
                            } else {
                                showModal(data.message || 'Failed to update flavors/sizes', 'error');
                            }
                        } catch (err) {
                            console.error('❌ JSON parse failed:', err);
                            showModal(
                                'Server did not return valid JSON:\n' + text,
                                'error'
                            );
                        }
                    })
                    .catch(err => {
                        console.error('❌ Fetch error:', err);
                        showModal('Fetch error: ' + err.message, 'error');
                    });
            });


            // Close modal if clicked outside content
            flavorSizeModal.addEventListener('click', (e) => {
                if (e.target === flavorSizeModal) flavorSizeModal.style.display = 'none';
            });
</script>