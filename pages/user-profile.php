<?php
require_once __DIR__ . '/../backend/db_script/db.php';
include "../components/buttonTemplate.php";
include "../components/modal.php"; // <-- use your modal

createModal(); // ensure modal JS is included

if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

$user_id = $_SESSION['user_id'] ?? null;
$userInfo = $appData->loadUserInfo($user_id);
$userAddress = $appData->loadUserAddress($user_id);
$userFavorites = $appData->loadUsersFave($user_id);
$orders = $appData->loadUserOrders($user_id) ?? [];


// compute hasPassword
$hasPassword = false;
if ($user_id) {
    $hasPassword = $appData->userHasPassword((int) $user_id);
}

include __DIR__ . '/../components/change_password.php';

function isUrl($value)
{
    if (empty($value)) return false;
    $value = trim($value);
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
}

// detect tab from query param (default: personal)
$activeTab = $_GET['tab'] ?? 'personal';
?>

<link rel="stylesheet" href="../public/css/profile.css">

<div class="profile-container">
    <!-- Main Content -->
    <main class="profile-main">
        <!-- Header -->
        <div class="white-box profile-header">
            <form action="../backend/update_user_photo.php" method="POST" enctype="multipart/form-data" id="photo-form">
                <input type="file" name="profile_photo" id="profile-input" accept="image/*" hidden>
                <button type="button" id="profile-btn" class="profile-photo-wrapper" title="Change profile photo">
                    <?php if (!empty($userInfo['profile_picture'])): ?>
                        <?php if (isUrl($userInfo['profile_picture'])): ?>
                            <img src="<?= htmlspecialchars(trim($userInfo['profile_picture'])) ?>" alt="profile-photo" class="profile-pic" />
                        <?php else: ?>
                            <img src="../public/profile_photos/<?= htmlspecialchars($userInfo['profile_picture']) ?>" alt="profile-photo" class="profile-pic" />
                        <?php endif; ?>
                    <?php else: ?>
                        <img src="../public/assests/uploadImg.jpg" alt="profile-photo" class="profile-pic" />
                    <?php endif; ?>
                </button>
                <button type="submit" id="submit-photo" hidden>Upload</button>
            </form>

            <div class="profile-info">
                <h3><?= htmlspecialchars($userInfo["first_name"] ?? 'Unknown') . ' ' . htmlspecialchars($userInfo["last_name"] ?? 'Unknown'); ?></h3>
                <p class="role" style="margin-bottom: 0;">Customer</p>
            </div>
        </div>

        <!-- Personal Info -->
        <section id="personal" class="tab-content white-box <?= $activeTab === 'personal' ? 'active' : '' ?>">
            <form id="personal-form" method="POST" action="../backend/update_user_profile.php">
                <div class="title-info">
                    <h3>Personal Information</h3>
                </div>
                <hr>
                <div class="row-info">
                    <div class="info">
                        <p>First Name</p>
                        <h4 class="display-value"><?= htmlspecialchars($userInfo["first_name"] ?? '') ?></h4>
                        <input class="edit-input" type="text" name="first_name" value="<?= htmlspecialchars($userInfo["first_name"] ?? '') ?>" style="display:none;">
                    </div>
                    <div class="info">
                        <p>Last Name</p>
                        <h4 class="display-value"><?= htmlspecialchars($userInfo["last_name"] ?? '') ?></h4>
                        <input class="edit-input" type="text" name="last_name" value="<?= htmlspecialchars($userInfo["last_name"] ?? '') ?>" style="display:none;">
                    </div>
                    <div class="info">
                        <p>Phone</p>
                        <h4 class="display-value"><?= htmlspecialchars($userInfo["phone_number"] ?? '') ?></h4>
                        <input class="edit-input" type="number" name="phone_number" value="<?= htmlspecialchars($userInfo["phone_number"] ?? '') ?>" style="display:none;">
                    </div>
                    <div class="info">
                        <p>Email</p>
                        <h4><?= htmlspecialchars($userInfo["email"] ?? '') ?></h4>
                    </div>
                </div>
                <div class="editBtn">
                    <?php
                    echo createButton(
                        30,
                        70,
                        "Edit",
                        "edit-info",
                        16,
                        "button",
                        ['data-state' => 'edit', 'name' => 'update_info']
                    );
                    ?>
                </div>
            </form>
        </section>

        <!-- Address -->
        <section id="address" class="tab-content white-box <?= $activeTab === 'address' ? 'active' : '' ?>">
            <form id="address-form" action="../backend/update_user_address.php" method="POST">
                <div class="title-info">
                    <h3>Address</h3>
                </div>
                <hr>
                <?php if (!$userAddress): ?>
                    <p>No address set.</p>
                <?php else: ?>
                    <div class="row-info">
                        <div class="info">
                            <p>Street</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["street_address"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="street_address" value="<?= htmlspecialchars($userAddress["street_address"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Barangay</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["barangay"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="barangay" value="<?= htmlspecialchars($userAddress["barangay"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>City</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["city_name"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="city" value="<?= htmlspecialchars($userAddress["city"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Province</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["province_name"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="province" value="<?= htmlspecialchars($userAddress["province"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Region</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["region_name"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="region" value="<?= htmlspecialchars($userAddress["region"] ?? '') ?>" style="display:none;">
                        </div>
                    </div>
                <?php endif; ?>
                <div class="editBtn">
                    <?php
                    echo createButton(
                        30,
                        70,
                        "Edit",
                        "edit-address",
                        16,
                        "button",
                        ['data-state' => 'edit', 'name' => 'update_address']
                    );
                    ?>
                </div>
            </form>
        </section>

        <!-- favorites -->
        <section id="favorites" class="tab-content white-box <?= $activeTab === 'favorites' ? 'active' : '' ?>">
            <h3>Favorites</h3>
            <hr>
            <?php if (!empty($userFavorites)): ?>
                <div class="favorites-scroll-container">
                    <div class="favorites-grid">
                        <?php foreach ($userFavorites as $item):
                            $product = $appData->getProductById($item['product_id']);
                            if (!$product) continue;
                            $title = $product['product_name'];
                            $price = $product['product_price'];
                            $image = $product['product_picture'];
                        ?>
                            <div class="small-reco-card">
                                <?php include '../components/reco-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <p>No favorites yet.</p>
            <?php endif; ?>
        </section>

        <!-- Order History -->
        <section id="orders" class="tab-content white-box <?= $activeTab === 'orders' ? 'active' : '' ?>">
            <h3>Order History</h3>
            <hr>
            <?php if (!empty($orders)): ?>
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                            if (!isset($order['user_id'])) {
                                $order['user_id'] = $_SESSION['user_id'] ?? null;
                            }
                            ?>
                            <tr class="order-row"
                                data-order='<?= json_encode($order, JSON_HEX_APOS | JSON_HEX_QUOT) ?>'>
                                <td>#<?= htmlspecialchars($order["order_number"]) ?></td>
                                <td><?= htmlspecialchars(date('M j, Y', strtotime($order['date']))) ?></td>

                                <td><?= ucfirst(htmlspecialchars($order["status"] ?? 'Undefined')) ?></td>
                                <td>₱<?= number_format($order["total"] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-orders">You have not placed any orders yet.</p>
            <?php endif; ?>
        </section>

        <!-- Settings -->
        <section id="settings" class="tab-content white-box <?= $activeTab === 'settings' ? 'active' : '' ?>">
            <h3>Account Settings</h3>
            <hr>
            <?php
            echo createButton(
                30,
                250,
                $hasPassword ? "Change Password" : "Set Password",
                "open-password-modal"
            );
            ?>
        </section>
    </main>

    <!-- Sidebar Tabs on the right -->
    <aside class="profile-sidebar right">
        <button class="tab-btn <?= $activeTab === 'personal' ? 'active' : '' ?>" data-tab="personal">Personal Info</button>
        <button class="tab-btn <?= $activeTab === 'address' ? 'active' : '' ?>" data-tab="address">Address</button>
        <button class="tab-btn <?= $activeTab === 'orders' ? 'active' : '' ?>" data-tab="favorites">Favorites</button>
        <button class="tab-btn <?= $activeTab === 'orders' ? 'active' : '' ?>" data-tab="orders">Order History</button>
        <button class="tab-btn <?= $activeTab === 'settings' ? 'active' : '' ?>" data-tab="settings">Settings</button>
    </aside>
</div>

<?php include "../components/admin/set-address-modal.php"; ?>

<!-- Order Details Modal -->
<div id="orderDetailsModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000;">
    <div style="background:white; padding:20px; border-radius:10px; width:90%; max-width:500px; position:relative;">
        <button onclick="closeOrderModal()" style="position:absolute; top:10px; right:10px; font-size:18px; background:none; border:none; cursor:pointer;">&times;</button>
        <div id="modalOrderContent"></div>
    </div>
</div>

<?php
// Detect base URL dynamically (works on localhost and production)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$BASE_URL = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/Leilife/";
?>


<script>
    document.addEventListener("DOMContentLoaded", () => {

        // Toast helper (keeps your existing helper)
        function showToast(message, type = "success", duration = 2500) {
            let toast = document.getElementById("toast-notif");
            if (!toast) {
                toast = document.createElement("div");
                toast.id = "toast-notif";
                toast.style.cssText = `
                position: fixed; bottom: 20px; right: 20px;
                padding: 12px 20px; border-radius: 8px;
                color: white; font-size: 14px; opacity: 0;
                transition: opacity 0.3s ease; z-index: 10000;
            `;
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            if (type === "success") toast.style.background = "#4caf50";
            else if (type === "error") toast.style.background = "#f44336";
            else if (type === "warning") toast.style.background = "#ff9800";
            toast.style.opacity = 1;
            setTimeout(() => toast.style.opacity = 0, duration);
        }

        // Tab switching (kept)
        const tabButtons = document.querySelectorAll(".tab-btn");
        const tabContents = document.querySelectorAll(".tab-content");

        function activateTab(tabId) {
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active"));
            const activeBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
            const activeContent = document.getElementById(tabId);
            if (activeBtn) activeBtn.classList.add("active");
            if (activeContent) activeContent.classList.add("active");
            const url = new URL(window.location.href);
            url.searchParams.set("tab", tabId);
            window.history.replaceState({}, "", url);
        }
        tabButtons.forEach(btn => btn.addEventListener("click", () => activateTab(btn.dataset.tab)));
        const urlParams = new URLSearchParams(window.location.search);
        activateTab(urlParams.get("tab") || "personal");

        // Personal edit (with validation for 09xxxxxxxxx)
        const editBtn = document.getElementById("edit-info");
        const personalForm = document.getElementById("personal-form");

        if (editBtn && personalForm) {
            editBtn.addEventListener("click", async (e) => {
                e.preventDefault();
                const state = editBtn.getAttribute("data-state");
                const infos = personalForm.querySelectorAll(".info");

                if (state === "edit") {
                    // Switch to edit mode
                    editBtn.textContent = "Save";
                    editBtn.style.backgroundColor = "#28a745";
                    editBtn.setAttribute("data-state", "save");

                    infos.forEach(info => {
                        const disp = info.querySelector(".display-value");
                        const input = info.querySelector(".edit-input");
                        if (disp && input) {
                            disp.style.display = "none";
                            input.style.display = "block";
                        }
                    });
                    return;
                }

                const firstName = personalForm.querySelector('[name="first_name"]').value.trim();
                const lastName = personalForm.querySelector('[name="last_name"]').value.trim();
                const phone = personalForm.querySelector('[name="phone_number"]').value.trim();

                // Only PH format 09XXXXXXXXX (11 digits)
                const phonePattern = /^09\d{9}$/;

                if (firstName === "" || lastName === "") {
                    showToast("First name and last name cannot be empty.", "error");
                    return;
                }

                if (phone === "") {
                    showToast("Please enter your phone number.", "error");
                    return;
                }

                if (!phonePattern.test(phone)) {
                    showToast("Invalid phone number. It must start with 09 and be 11 digits long.", "error");
                    return;
                }

                const fd = new FormData(personalForm);

                try {
                    const resp = await fetch(personalForm.action, {
                        method: "POST",
                        body: fd
                    });
                    const result = await resp.json();

                    if (result.success) {
                        showToast(result.message || "Profile updated!", "success");

                        infos.forEach(info => {
                            const disp = info.querySelector(".display-value");
                            const input = info.querySelector(".edit-input");
                            if (disp && input) {
                                disp.textContent = input.value;
                                input.style.display = "none";
                                disp.style.display = "block";
                            }
                        });

                        editBtn.textContent = "Edit";
                        editBtn.style.backgroundColor = "";
                        editBtn.setAttribute("data-state", "edit");
                    } else {
                        showToast(result.error || "Save failed", "error");
                    }
                } catch (err) {
                    showToast("Request error: " + err.message, "error");
                }
            });
        }

        // Order row click -> show order details and reorder button creation (kept)
        const orderRows = document.querySelectorAll('.order-row');
        orderRows.forEach(row => {
            row.style.cursor = "pointer";
            row.addEventListener('click', () => {
                const order = JSON.parse(row.getAttribute('data-order'));
                const modalContent = document.getElementById('modalOrderContent');
                const items = Array.isArray(order.items) ? order.items : [];
                let html = `
                <p><strong>Order #:</strong> ${order.order_number || 'Undefined'}</p>
                <p><strong>Date:</strong> ${order.date ? new Date(order.date).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
                }) : 'Undefined'}</p>

                <p><strong>Status:</strong> ${order.status ? order.status.charAt(0).toUpperCase() + order.status.slice(1).toLowerCase() : 'Undefined'}</p>
                <p><strong>Payment:</strong> ${order.payment_method ? order.payment_method.charAt(0).toUpperCase() + order.payment_method.slice(1).toLowerCase() : 'Undefined'}</p>
                <p><strong>Total:</strong> ₱${parseFloat(order.total || 0).toFixed(2)}</p>
                <p><strong>Items:</strong></p>
                <ul>
                    ${items.map(i => {
                        let itemText = `${i.product_name || 'Undefined'} × ${i.quantity || 1}`;
                        if (i.size) itemText += ` (${i.size})`;
                        if (i.flavors && i.flavors.length > 0) itemText += ` — Flavors: ${i.flavors.join(', ')}`;
                        return `<li>${itemText}</li>`;
                    }).join('')}
                </ul>
            `;

                if (order.review) {
                    html += `<p><strong>Feedback:</strong> ${order.review}</p>`;
                }

                if (order.status === "delivered" || order.status === "picked_up") {
                    if (!order.review) {
                        html += `
                            <div id="reorder-receipt-btn">
                                <?= createButton(35, 150, "Write a review", "writeReviewBtn", 16, "button"); ?>

                                <form class="reorder-form" method="POST">
                                    <input type="hidden" name="order_id" value="${order.order_id}">
                                    <?= createButton(35, 100, "Reorder", "reorderBtn", 16, "button", ['class' => 'reorderBtn']); ?>
                                </form>

                                <?= createButton(35, 200, "Download Receipt", "dlReceipt", 16, "button"); ?>
                            </div>
                        `;
                    } else {
                        // Delivered/picked up AND has review → reorder + download receipt
                        html += `
                            <div id="reorder-receipt-btn">
                                <form class="reorder-form" method="POST">
                                    <input type="hidden" name="order_id" value="${order.order_id}">
                                    <?= createButton(35, 100, "Reorder", "reorderBtn", 16, "button", ['class' => 'reorderBtn']); ?>
                                </form>

                                <?= createButton(35, 200, "Download Receipt", "dlReceipt", 16, "button"); ?>
                            </div>
                        `;
                    }
                } else if (order.status === "cancelled") {
                    // Cancelled → only reorder
                    html += `
                        <div id="reorder-receipt-btn">
                            <form class="reorder-form" method="POST">
                                <input type="hidden" name="order_id" value="${order.order_id}">
                                <?= createButton(35, 100, "Reorder", "reorderBtn", 16, "button", ['class' => 'reorderBtn']); ?>
                            </form>
                        </div>
                    `;
                } else {
    const baseUrl = "<?= $BASE_URL ?>"; // PHP injected once, safe and dynamic

    html += `
        <div id="reorder-receipt-btn">
            <button
                style="height:35px; width:150px; font-size:16px; border:none; border-radius:8px; background:#4caf50; color:white; cursor:pointer;"
                onclick="window.location.href='${baseUrl}public/index.php?page=order-tracking&num=${order.order_number}'">
                Track my order
            </button>
        </div>
    `;
}







                modalContent.innerHTML = html;

                const writeBtn = document.getElementById("writeReviewBtn");
                if (writeBtn) {
                    writeBtn.addEventListener("click", () => {
                        window.location.href = `index.php?page=order-tracking&num=${order.order_number}`;
                    });
                }

                const dlReceipt = document.getElementById("dlReceipt");
                if (dlReceipt) {
                    dlReceipt.addEventListener("click", () => {
                        const url = `index.php?page=user-receipt&order_number=${order.order_number}&user_id=${order.user_id}`;
                        window.open(url, "_blank");
                    });
                }



                document.getElementById('orderDetailsModal').style.display = 'flex';
            });
        });

        // close order modal
        function closeOrderModal() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }
        window.closeOrderModal = closeOrderModal;

        // close clicking outside
        window.addEventListener('click', e => {
            const modal = document.getElementById('orderDetailsModal');
            if (e.target === modal) modal.style.display = 'none';
        });

        function bindAddressModal() {
            const addressBtn = document.getElementById("edit-address");
            const modalOverlay = document.getElementById("modalOverlay");
            const addressModalForm = modalOverlay?.querySelector("form");

            if (addressBtn && modalOverlay) {
                addressBtn.addEventListener("click", (e) => {
                    e.preventDefault();
                    modalOverlay.style.display = "flex";
                });
            }

            if (addressModalForm) {
                addressModalForm.addEventListener("submit", async (e) => {
                    e.preventDefault();
                    const fd = new FormData(addressModalForm);

                    try {
                        const resp = await fetch(addressModalForm.action, {
                            method: "POST",
                            body: fd
                        });
                        const result = await resp.json();

                        if (result.success) {
                            showModal(result.message || "Address updated!", "success");
                            modalOverlay.style.display = "none";
                            setTimeout(() => {
                                window.location.href = "index.php?page=user-profile&tab=address";
                            }, 1000);
                        } else {
                            showModal(result.error || "Failed to save address.", "error");
                        }
                    } catch (err) {
                        showModal("Error updating address.", "error");
                    }
                });
            }
        }
        bindAddressModal();


        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('reorderBtn')) {
                e.preventDefault();
                const form = e.target.closest('form');
                if (!form) return;
                const orderId = form.querySelector('input[name="order_id"]').value;
                closeOrderModal();
                // Show warning before reordering
                showModal("Warning: All current products in your cart will be removed. Click OK to continue.", "warning", false);

                const closeBtn = document.getElementById('notif-close');
                if (!closeBtn) {
                    proceedReorder(orderId);
                    return;
                }

                const handler = () => {
                    closeBtn.removeEventListener('click', handler);
                    closeBtn.disabled = true;
                    proceedReorder(orderId);
                };

                closeBtn.addEventListener('click', handler);
            }
        });

        function proceedReorder(orderId) {
            const fd = new FormData();
            fd.append('order_id', orderId);
            fd.append('delete_cart', 1);

            fetch('../backend/reorder.php', {
                    method: 'POST',
                    body: fd,
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(result => {
                    if (!result) {
                        showModal('Unexpected server response.', 'error', true, 3000);
                        return;
                    }

                    if (!result.success) {
                        // ❌ all items unavailable
                        showModal(result.message || 'All items in your previous order are unavailable.', 'warning', false);
                        return;
                    }

                    // ✅ partial success (some unavailable)
                    if (result.availableCount < result.totalItems) {
                        showModal(result.message || 'Some items were unavailable and skipped.', 'warning', false);

                        // ⏳ delay redirect to let modal show
                        if (result.redirect) {
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 3000); // wait 3 seconds before redirect
                        }
                        return; // stop here so it doesn’t run the next redirect
                    }

                    // ✅ all items available
                    if (result.redirect) {
                        showModal(result.message || 'Reorder successful! Redirecting...', 'success', true, 1500);
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1500);
                    } else {
                        showModal(result.message || 'Reorder successful.', 'success', true, 2000);
                    }
                })
                .catch(err => {
                    showModal('AJAX error: ' + err.message, 'error', true, 3000);
                });
        }


    }); // DOMContentLoaded
</script>