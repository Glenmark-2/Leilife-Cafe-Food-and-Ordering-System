<?php
session_start();
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$btnText = $archived === '1' ? 'View Products' : 'View Archive';
$appData->adminloadProducts($archived);
$appData->loadCategories();


$subCategories = array_unique(
    array_map(fn($c) => $c['category_name'] ?? '', $appData->categories)
);
$subCategories = array_values($subCategories);


?>
<div id="first-row">
    <h2>Products</h2>
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
        <form class="search-bar" role="search">
            <input type="search" id="search-input" placeholder="🔍 Search product" aria-label="Search products">
        </form>
        <button type="button" id="add-product"><span>+ Add new product</span></button>
    </div>

    <div id="table-container">
        <table class="product-table">
            <thead>
                <tr>
                    <!-- <th><input type="checkbox" class="checkbox"></th> -->
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
                        <!-- <td><input type="checkbox" class="checkbox"></td> -->

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
                                    <p class="product-id">#<?= htmlspecialchars($product['product_id']) ?></p>
                                </div>
                            </div>
                        </td>

                        <!-- Price -->
                        <td>
                            <?php if ($product['main_category_id'] == 2): ?>
                                <div style="display: flex; flex-direction: column; justify-content:space-between; gap:5px">


                                    <div style="display: flex; flex-direction:row; justify-content:space-between; gap:10px">
                                        <label>Medium</label>
                                        <input type="number" id="pprice" class="inputData" min=0
                                            value="<?= isset($product['product_price']) ? number_format($product['product_price'], 2) : '' ?>" disabled>
                                    </div>

                                    <div style="display: flex; flex-direction:row; justify-content:space-between; gap:10px">
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

                        <td class="actions-cell" style="display: flex; justify-content: center; align-items: center;">
                            <button id="editBtn" class="editBtn" type="button">Edit</button>
                            <img src="public/assests/archive.png" alt="Archive" class="archive-icon">
                        </td>

                        <!-- <td>
                        <button id="editBtn" class="editBtn" type="button">Edit</button>
                    </td> -->
                        <!-- <td>
                        <img src="public/assests/trash-bin.png"
                            alt="Delete"
                            class="trash-icon"
                            onclick="deleteRow(<?= $product['product_id'] ?>, this)">
                    </td> -->
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
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
            <form>
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
                    <label for="category">Category:</label>
                    <select name="category_id" id="add-category" required>
                        <option value="">Select category</option>
                        <?php foreach ($appData->categories as $cat): ?>
                            <option value="<?= (int)$cat['category_id'] ?>">
                                <?= htmlspecialchars($cat['category_name']) ?>
                            </option>
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

    function filterProducts() {
        const search = searchInput.value.toLowerCase();
        const activeCategoryBtn = document.querySelector('.box-row.clicked');
        const category = activeCategoryBtn ? activeCategoryBtn.dataset.category.toLowerCase() : 'all';

        document.querySelectorAll('.product-row').forEach(row => {
            const name = row.querySelector('#pname').value.toLowerCase();
            const prodSub = row.dataset.sub ? row.dataset.sub.toLowerCase() : '';
            const matchesSearch = name.includes(search);
            const matchesCategory = category === 'all' || prodSub === category;
            row.style.display = (matchesSearch && matchesCategory) ? 'table-row' : 'none';
        });
    }

    function resetEditButtons() {
        document.querySelectorAll('.editBtn').forEach(b => {
            b.disabled = false;
            b.style.opacity = "1";
            b.style.cursor = "pointer";
            b.textContent = "Edit";
            b.style.backgroundColor = "#C6C3BD";
            b.style.color = "#22333B";
        });
        const addBtn = document.getElementById("add-product");
        if (addBtn) {
            addBtn.disabled = false;
            addBtn.style.opacity = "1";
        }
        // disable all inputs back
        document.querySelectorAll('#pname, #pprice, #pprice_large, .pcategory, #statusBtn').forEach(el => {
            el.disabled = true;
            el.onclick = null;
        });
        document.querySelectorAll('.product-row').forEach(r => r.classList.remove('editing'));
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            categoryButtons.forEach(b => b.classList.remove('clicked'));
            btn.classList.add('clicked');
            filterProducts();
            resetEditButtons(); // reset edit state
        });
    });

    searchInput.addEventListener("input", () => {
        filterProducts();
        resetEditButtons(); // reset edit state
    });

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
            row.dataset.originalPrice = priceInput.value;
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
        fileInput.files.length > 0;

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
    if (fileInput.files[0]) formData.append("photo", fileInput.files[0]);

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
        btn.style.backgroundColor = "#C6C3BD";
        btn.style.color = "#22333B";

        document.querySelectorAll('.editBtn').forEach(b => {
            b.disabled = false;
            b.style.opacity = "1";
            b.style.cursor = "pointer";
        });
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

    document.getElementById("add").addEventListener("click", () => {
        const name = document.getElementById("name").value.trim();
        const price = document.getElementById("price").value.trim();
        const priceLarge = document.getElementById("price_large").value.trim();
        const category = document.getElementById("add-category").value;
        const status = "Available";
        const file = document.getElementById("uploadInput").files[0];

        if (!name || !price || !category) {
            showModal("Please fill all fields", "error");
            return;
        }

        const formData = new FormData();
        formData.append("product_name", name);
        formData.append("product_price", price);
        if (priceLarge) formData.append("price_large", priceLarge);
        formData.append("category_id", category);
        formData.append("status", status);
        if (file) formData.append("photo", file);

        fetch(BASE_URL + "backend/admin/add_product.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showModal("Product added successfully!", "success");
                    document.getElementById("modal").style.display = "none";
                    document.getElementById("name").value = "";
                    document.getElementById("price").value = "";
                    document.getElementById("add-category").value = "";
                    document.getElementById("uploadInput").value = "";
                    document.getElementById("new-product-photo").src = "public/assests/image-43.png";
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showModal("Error: " + data.message, "error");
                }
            })
            .catch(err => showModal("Fetch error: " + err.message, "error"));
    });

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
                        row.remove(); // remove from table since it no longer belongs in this view
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
</script>