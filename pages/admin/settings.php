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

$currentAdmin =  $appData->getCurrentAdmin();
$isMainAdmin = $currentAdmin['isMainAdmin'];

if(!$isMainAdmin){
  header('Location: /leilife/public/index.php');
  exit;
}

$payment_info = $appData->payment_info();
$payment_methods = $appData->getPaymentMethods();
?>


<section class="white-box settings-section">
    <div class="title-info">
        <h3>Payment Settings</h3>
        <p class="subtitle">Manage your store’s GCash information and available payment options.</p>
    </div>
    <hr>

    <form id="payment-settings-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="gcash-name">GCash Name</label>
                <input type="text" id="gcash-name" name="gcash_name"
                    placeholder="Enter GCash name"
                    value="<?= htmlspecialchars($payment_info['gcash_name'] ?? '') ?>"
                    readonly required>
            </div>

            <div class="form-group">
                <label for="gcash-number">GCash Number</label>
                <input type="text" id="gcash-number" name="gcash_number"
                    placeholder="09XXXXXXXXX"
                    value="<?= htmlspecialchars($payment_info['gcash_number'] ?? '') ?>"
                    readonly required>
            </div>

            <div class="form-group">
                <label for="gcash-email">GCash Email</label>
                <input type="email" id="gcash-email" name="gcash_email"
                    placeholder="your@email.com"
                    value="<?= htmlspecialchars($payment_info['gcash_email'] ?? '') ?>"
                    readonly required>
            </div>
        </div>

        <div class="toggle-group">
            <label class="toggle-label">Payment Methods</label>
            <div class="toggle-options" id="payment-toggles">
                <?php foreach ($payment_methods as $pm): ?>
                    <div class="toggle-item">
                        <label class="switch">
                            <input
                                type="checkbox"
                                class="payment-toggle"
                                data-id="<?= htmlspecialchars($pm['payment_id']) ?>"
                                <?= $pm['status'] === 'enabled' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                        <span class="method-label"><?= htmlspecialchars($pm['method']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="save-btn-container">
            <button type="button" id="edit-save-btn">Edit</button>
        </div>
    </form>
</section>



<script>
    const BASE_URL = "http://localhost/Leilife/";

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
        }
        document.getElementById("notif-message").textContent = message;
        const closeBtn = document.getElementById("notif-close");
        closeBtn.className = type;
        modal.style.display = "flex";
        const closeModal = () => modal.style.display = "none";
        closeBtn.onclick = closeModal;
        modal.onclick = e => {
            if (e.target === modal) closeModal();
        };
        if (autoClose) setTimeout(closeModal, duration);
    }

    document.addEventListener("DOMContentLoaded", () => {
        const editBtn = document.getElementById("edit-save-btn");
        const inputs = document.querySelectorAll("#gcash-name, #gcash-number, #gcash-email");
        const toggles = document.querySelectorAll(".payment-toggle");
        let isEditing = false;

        editBtn.addEventListener("click", async () => {
            if (!isEditing) {
                isEditing = true;
                editBtn.textContent = "Save";
                editBtn.style.backgroundColor = "#28a745";
                inputs.forEach(i => i.removeAttribute("readonly"));
                inputs[0].focus();
                return;
            }

            const toggles = document.querySelectorAll(".payment-toggle");
            let name = document.getElementById("gcash-name").value.trim();
            let number = document.getElementById("gcash-number").value.trim();
            let email = document.getElementById("gcash-email").value.trim();
            const enabledCount = Array.from(toggles).filter(t => t.checked).length;

            // Auto-prepend 0 if missing
            if (/^9\d{9}$/.test(number)) {
                number = "0" + number;
                document.getElementById("gcash-number").value = number;
            }

            // Validations
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

            const payload = {
                action: "save_payment_settings",
                name,
                number,
                email,
                methods: methodsArray
            };

            try {
                const res = await fetch(BASE_URL + "backend/admin/save_payment_settings.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (data.success) {
                    showModal("Settings saved successfully!", "success");
                    isEditing = false;
                    editBtn.textContent = "Edit";
                    editBtn.style.backgroundColor = "";
                    inputs.forEach(i => i.setAttribute("readonly", true));
                } else {
                    showModal(data.message || "Failed to save settings.", "error");
                }
            } catch (err) {
                console.error(err);
                showModal("Network error: " + err.message, "error");
            }
        });




    });
</script>

<style>
    .white-box.settings-section {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .title-info h3 {
        margin-bottom: 5px;
        font-size: 1.3rem;
        color: #333;
    }

    .subtitle {
        color: #777;
        font-size: 0.9rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    .form-group label {
        font-weight: 500;
        margin-bottom: 6px;
        display: block;
    }

    .form-group input {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 15px;
        background: #f9f9f9;
    }

    .form-group input:not([readonly]) {
        background: #fff;
        border-color: #007bff;
    }

    .toggle-group {
        margin-top: 25px;
    }

    .toggle-options {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 10px;
    }

    .toggle-item {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 45px;
        height: 22px;
    }

    .switch input {
        display: none;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #28a745;
    }

    input:checked+.slider:before {
        transform: translateX(22px);
    }

    .save-btn-container {
        margin-top: 25px;
        text-align: right;
    }

    #edit-save-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 15px;
        cursor: pointer;
        transition: 0.2s ease;
    }

    #edit-save-btn:hover {
        background: #0056b3;
    }

    .notif-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: none; /* hidden by default */
    justify-content: center;
    align-items: center;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
}

.notif-content {
    background: #fff;
    padding: 20px 30px;
    border-radius: 10px;
    text-align: center;
    max-width: 400px;
    width: 90%;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.notif-content p {
    margin-bottom: 15px;
    font-size: 16px;
}

.notif-content button {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.notif-content button.success {
    background-color: #28a745;
    color: #fff;
}

.notif-content button.error {
    background-color: #dc3545;
    color: #fff;
}

</style>