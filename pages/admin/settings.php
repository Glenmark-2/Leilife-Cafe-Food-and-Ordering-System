<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_id'])) {
    header('Location: /leilife/public/index.php');
    exit;
}

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);

$currentAdmin = $appData->getCurrentAdmin();
$isMainAdmin = $currentAdmin['isMainAdmin'];

if (!$isMainAdmin) {
    header('Location: /leilife/public/index.php');
    exit;
}

$payment_info = $appData->payment_info();
$payment_methods = $appData->getPaymentMethods();
$delivery_methods = $appData->getDeliveryMethods();
?>

<div class="container">
     <div id="first-row">
    <div class="top-left">
      <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <h2>Payment</h2>
    </div>
        <button id="edit-save-btn">Edit payment</button>
    </div>

    <div class="settings-card">
        <div class="form-grid">
            <div class="form-group">
                <label for="gcash-name">GCash Name</label>
                <input type="text" id="gcash-name" name="gcash_name"
                    value="<?= htmlspecialchars($payment_info['gcash_name'] ?? '') ?>"
                    readonly required>
            </div>
            <div class="form-group">
                <label for="gcash-number">GCash Number</label>
                <input type="number" id="gcash-number" name="gcash_number"
                    value="<?= htmlspecialchars($payment_info['gcash_number'] ?? '') ?>"
                    readonly required>
            </div>
            <div class="form-group">
                <label for="gcash-email">GCash Email</label>
                <input type="email" id="gcash-email" name="gcash_email"
                    value="<?= htmlspecialchars($payment_info['gcash_email'] ?? '') ?>"
                    readonly required>
            </div>
        </div>

        <div class="toggle-group" style="margin-top: 25px;">
            <label class="toggle-label">Payment Methods</label>
            <div class="toggle-options">
                <?php foreach ($payment_methods as $pm): ?>
                    <div class="toggle-item">
                        <label class="switch">
                            <input type="checkbox"
                                class="payment-toggle"
                                data-id="<?= htmlspecialchars($pm['payment_id']) ?>"
                                <?= $pm['status'] === 'enabled' ? 'checked' : '' ?>
                                disabled>
                            <span class="slider"></span>
                        </label>
                        <span class="method-label"><?= htmlspecialchars($pm['method']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <br>

    <div id="first-row">
        <h2>Delivery</h2>
        <button id="edit-delivery-btn">Edit delivery</button>
    </div>
<div class="settings-card">
    <div class="toggle-group">
        <label class="toggle-label">Delivery Methods</label>
        <div class="toggle-options">
            <?php foreach ($delivery_methods as $dm): ?>
                <div class="toggle-item">
                    <label class="switch">
                        <input type="checkbox"
                               class="delivery-toggle"
                               data-id="<?= htmlspecialchars($dm['delivery_id']) ?>"
                               <?= $dm['status'] === 'enabled' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <span class="method-label"><?= htmlspecialchars($dm['option_name']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
        
    </div>
</div>

<script>
const BASE_URL = "http://localhost/Leilife/";

document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.getElementById("edit-save-btn");
    const editDeliveryBtn = document.getElementById("edit-delivery-btn");
    const inputs = document.querySelectorAll("#gcash-name, #gcash-number, #gcash-email");
    const toggles = document.querySelectorAll(".payment-toggle");
    const deliveryToggles = document.querySelectorAll(".delivery-toggle");

    let isEditing = false;
    let isEditingDelivery = false;

    toggles.forEach(t => t.disabled = true);
    deliveryToggles.forEach(t => t.disabled = true);

    editBtn.addEventListener("click", async () => {
        if (!isEditing) {
            editDeliveryBtn.disabled = true;
            editDeliveryBtn.style.opacity = "0.6";
            editDeliveryBtn.style.cursor = "not-allowed";

            isEditing = true;
            editBtn.textContent = "Save";
            editBtn.style.backgroundColor = "#28a745";
            inputs.forEach(i => i.removeAttribute("readonly"));
            toggles.forEach(t => t.disabled = false);
            inputs[0].focus();
            return;
        }

        const name = document.getElementById("gcash-name").value.trim();
        let number = document.getElementById("gcash-number").value.trim();
        const email = document.getElementById("gcash-email").value.trim();
        const enabledCount = Array.from(toggles).filter(t => t.checked).length;

        const digitsOnly = number.replace(/\D/g, "");
        if (digitsOnly.length !== 11) {
            showModal("GCash number is incomplete. It must be 11 digits.", "error");
            return;
        }

        if (/^9\d{9}$/.test(number)) {
            number = "0" + number;
            document.getElementById("gcash-number").value = number;
        }

        if (!name || !number || !email) {
            showModal("All fields are required.", "error");
            return;
        }
        if (!/^09\d{9}$/.test(number)) {
            showModal("Invalid GCash number format. Use 09XXXXXXXXX.", "error");
            return;
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showModal("Invalid email address.", "error");
            return;
        }
        if (enabledCount < 1) {
            showModal("At least one payment method must be enabled.", "error");
            return;
        }

        const methodsArray = Array.from(toggles).map(t => ({
            id: t.dataset.id,
            status: t.checked ? 1 : 0
        }));

        const payload = { action: "save_payment_settings", name, number, email, methods: methodsArray };

        try {
            const res = await fetch(BASE_URL + "backend/admin/save_payment_settings.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.success) {
                showModal("Settings saved successfully!", "success");
                // isEditing = false;
                // editBtn.textContent = "Edit payment";
                // editBtn.style.backgroundColor = "";
                // inputs.forEach(i => i.setAttribute("readonly", true));
                // toggles.forEach(t => t.disabled = true);

                // // Re-enable delivery edit button
                // editDeliveryBtn.disabled = false;
                // editDeliveryBtn.style.opacity = "";
                // editDeliveryBtn.style.cursor = "pointer";
                setTimeout(() => location.reload(), 1000);
            } else {
                showModal(data.message || "Failed to save settings.", "error");
            }
        } catch (err) {
            showModal("Network error: " + err.message, "error");
        }
    });

    // deliverr
    editDeliveryBtn.addEventListener("click", async () => {
        if (!isEditingDelivery) {
            editBtn.disabled = true;
            editBtn.style.opacity = "0.6";
            editBtn.style.cursor = "not-allowed";

            isEditingDelivery = true;
            editDeliveryBtn.textContent = "Save";
            editDeliveryBtn.style.backgroundColor = "#28a745";
            deliveryToggles.forEach(t => t.disabled = false);
            deliveryToggles[0]?.focus();
            return;
        }

        const methodsArray = Array.from(deliveryToggles).map(t => ({
            id: t.dataset.id,
            status: t.checked ? 1 : 0
        }));

        const enabledCount = methodsArray.filter(m => m.status === 1).length;
        if (enabledCount < 1) {
            showModal("At least one delivery method must be enabled.", "error");
            return;
        }

        const payload = { action: "save_delivery_settings", methods: methodsArray };
// console.log(methodsArray); return;

        try {
            const res = await fetch(BASE_URL + "backend/admin/save_delivery_settings.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });

            const data = await res.json();
            if (data.success) {
                showModal("Delivery settings saved successfully!", "success");
                setTimeout(() => location.reload(), 1000);
            } else {
                showModal(data.message || "Failed to save delivery settings.", "error");
            }
        } catch (err) {
            showModal("Network error: " + err.message, "error");
        }
    });
});

function showModal(message, type = "success", autoClose = true, duration = 3000) {
    let modal = document.getElementById("notif-modal");
    if (!modal) {
        modal = document.createElement("div");
        modal.id = "notif-modal";
        modal.className = "notif-modal";
        modal.innerHTML = `<div class="notif-content"><p id="notif-message"></p><button id="notif-close">OK</button></div>`;
        document.body.appendChild(modal);
        const style = document.createElement("style");
        style.innerHTML = `
    .notif-modal{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4);justify-content:center;align-items:center;}
    .notif-content{background:white;padding:20px 30px;border-radius:10px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,0.3);min-width:250px;animation:popin 0.3s ease;}
    .notif-content p{margin-bottom:15px;font-size:16px;}
    .notif-content button{padding:6px 16px;border:none;border-radius:6px;cursor:pointer;font-size:14px;color:white;}
    .notif-content button.success{background:#4caf50;}
    .notif-content button.error{background:#f44336;}
    @keyframes popin{from{transform:scale(0.8);opacity:0;}to{transform:scale(1);opacity:1;}}
    `;
        document.head.appendChild(style);
    }
    document.getElementById("notif-message").textContent = message;
    const closeBtn = document.getElementById("notif-close");
    closeBtn.className = type === "success" ? "success" : "error";
    modal.style.display = "flex";
    const closeModal = () => modal.style.display = "none";
    closeBtn.onclick = closeModal;
    modal.onclick = e => {
        if (e.target === modal) closeModal();
    };
    if (autoClose) setTimeout(closeModal, duration);
}
</script>
