<?php
require_once __DIR__ . '/../backend/db_script/db.php';
include "../components/buttonTemplate.php";


if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

$user_id = $_SESSION['user_id'] ?? null;
$userInfo = $appData->loadUserInfo($user_id);
$userAddress = $appData->loadUserAddress($user_id);
// $orders = $appData->loadUserOrders($user_id) ?? [];

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
                    <!-- switched email and phone -->
                    <div class="info">
                        <p>Phone</p>
                        <h4 class="display-value"><?= htmlspecialchars($userInfo["phone_number"] ?? '') ?></h4>
                        <input class="edit-input" type="text" name="phone_number" value="<?= htmlspecialchars($userInfo["phone_number"] ?? '') ?>" placeholder="+63" style="display:none;">
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
                            <input class="edit-input" type="text" name="street_address"
                                value="<?= htmlspecialchars($userAddress["street_address"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Barangay</p>
                            <h4 class="display-value"><?= htmlspecialchars($userAddress["barangay"] ?? '') ?></h4>
                            <input class="edit-input" type="text" name="barangay"
                                value="<?= htmlspecialchars($userAddress["barangay"] ?? '') ?>" style="display:none;">
                        </div>
                        <!-- FOR NOOOOW -->
                        <div class="info">
                            <p>City</p>
                            <h4 class="display-value">Caloocan City</h4>
                            <input class="edit-input" type="text" name="city"
                                value="<?= htmlspecialchars($userAddress["city"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Province</p>
                            <h4 class="display-value">Metro Manila</h4>
                            <input class="edit-input" type="text" name="province"
                                value="<?= htmlspecialchars($userAddress["province"] ?? '') ?>" style="display:none;">
                        </div>
                        <div class="info">
                            <p>Region</p>
                            <h4 class="display-value">NCR (National Capital Region)</h4>
                            <input class="edit-input" type="text" name="region"
                                value="<?= htmlspecialchars($userAddress["region"] ?? '') ?>" style="display:none;">
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
                            <tr>
                                <td>#<?= htmlspecialchars($order["id"]) ?></td>
                                <td><?= htmlspecialchars($order["date"]) ?></td>
                                <td><?= htmlspecialchars($order["status"]) ?></td>
                                <td>₱<?= htmlspecialchars(number_format($order["total"], 2)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No orders yet.</p>
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
        <button class="tab-btn <?= $activeTab === 'orders' ? 'active' : '' ?>" data-tab="orders">Order History</button>
        <button class="tab-btn <?= $activeTab === 'settings' ? 'active' : '' ?>" data-tab="settings">Settings</button>
    </aside>
</div>

<?php include "../components/admin/set-address-modal.php"; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // -------------------------
    // Toast notification
    // -------------------------
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

    // -------------------------
    // Tab switching
    // -------------------------
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

    tabButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            activateTab(btn.dataset.tab);
        });
    });

    const urlParams = new URLSearchParams(window.location.search);
    activateTab(urlParams.get("tab") || "personal");

    // -------------------------
    // Personal Info Edit/Save
    // -------------------------
    const editBtn = document.getElementById("edit-info");
    const personalForm = document.getElementById("personal-form");

    if (editBtn && personalForm) {
        editBtn.addEventListener("click", async (e) => {
            e.preventDefault();
            const state = editBtn.getAttribute("data-state");
            const infos = personalForm.querySelectorAll(".info");

            if (state === "edit") {
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

            const fd = new FormData(personalForm);
            try {
                const resp = await fetch(personalForm.action, { method: "POST", body: fd });
                const result = await resp.json();

                if (result.success) {
                    showToast(result.message || "Profile updated!", "success");

                    // update displayed values
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

    // -------------------------
    // Address Edit Modal
    // -------------------------
    const addressBtn = document.getElementById("edit-address");
    const modalOverlay = document.getElementById("modalOverlay");
    const addressModalForm = modalOverlay?.querySelector("form");

    if (addressBtn && modalOverlay) {
        addressBtn.addEventListener("click", e => {
            e.preventDefault();
            modalOverlay.style.display = "flex";
        });
    }

    if (addressModalForm) {
        addressModalForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const fd = new FormData(addressModalForm);

            try {
                const resp = await fetch(addressModalForm.action, { method: "POST", body: fd });
                const result = await resp.json();

                if (result.success) {
                    showToast(result.message || "Address updated!", "success");
                    modalOverlay.style.display = "none";

                    // update displayed address
                    const fields = ["street_address","barangay","city","province","region"];
                    fields.forEach(f => {
                        const input = document.querySelector(`#address .info input[name="${f}"]`);
                        if (input) {
                            const displayElem = input.closest(".info").querySelector(".display-value");
                            displayElem.textContent = input.value;
                        }
                    });
                } else {
                    showToast(result.error || "Save failed", "error");
                }
            } catch (err) {
                showToast("Request error: " + err.message, "error");
            }
        });
    }

    // -------------------------
    // Profile photo upload
    // -------------------------
    const profileBtn = document.getElementById("profile-btn");
    const profileInput = document.getElementById("profile-input");

    if (profileBtn && profileInput) {
        profileBtn.addEventListener("click", () => profileInput.click());
        profileInput.addEventListener("change", () => {
            if (!profileInput.files.length) return;
            document.getElementById("submit-photo").click();
        });
    }

    // -------------------------
    // Profile hover effect
    // -------------------------
    const profilePic = document.querySelector(".profile-pic");
    if (profilePic) {
        profilePic.addEventListener("mouseenter", () => {
            profilePic.setAttribute("title", "Click to change photo");
        });
    }
});
</script>



<style>
    /* Ensure tab contents have a base min height */
    .tab-content {
        min-height: 300px;
    }
</style>