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

$currentAdmin =  $appData->getCurrentAdmin();
$isMainAdmin = $currentAdmin['isMainAdmin'];


$showArchived = $_GET['archived'] ?? 0; // 0 = active, 1 = archived
$stmt = $pdo->prepare("SELECT * FROM staff_roles WHERE is_archive = :archived");
$stmt->execute(['archived' => $showArchived]);
$staffRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<style>

</style>

<div class="container1">

    <div id="first-row">
        <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <h2>Staff Management</h2>
        <button type="button" id="view-archive">
            <span><?= $showArchived ? "View Active" : "View Archive" ?></span>
        </button>
    </div>

    <div id="search_add">
        <form class="search-bar" role="search" style="margin-bottom: 0;">
            <!-- <label for="search-input">Search Staff :</label> -->
            <input type="search" id="search-input" placeholder="Search staff name" aria-label="Search staff">
        </form>
        <div class="add-container">
            <button id="add-new">+ Add Staff</button>
        </div>

    </div>

    <div id="table-container">
        <div class="table-wrapper">
            <table class="staff-table" aria-live="polite">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Shift</th>
                        <th>Status</th>
                        <th style="text-align:center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-content">
                    <?php foreach ($staffRoles as $staff): ?>
                        <tr class="staff-row" data-id="<?= isset($staff['staff_id']) ? htmlspecialchars($staff['staff_id']) : '' ?>">

                            <!-- Name -->
                            <td data-label="Name">
                                <div class="name-cell">
                                    <label class="photo-wrapper">
                                        <img class="profile-photo"
                                            src="<?= !empty($staff['staff_image']) ? "public/staffs/" . $staff['staff_image'] : "public/assests/about us.png" ?>"
                                            alt="profile-photo">
                                        <input type="file" class="photoInput" accept="image/*" style="display:none;" disabled>
                                    </label>
                                    <div>
                                        <input type="text" class="inputData"
                                            value="<?= htmlspecialchars($staff['staff_name']) ?>" disabled>
                                    </div>
                                </div>
                            </td>

                            <!-- Position -->
                            <td data-label="Position">
                                <input type="text" class="inputData"
                                    value="<?= htmlspecialchars($staff['staff_role']) ?>" disabled>
                            </td>

                            <!-- Shift -->
                            <td data-label="Shift">
                                <select class="pcategory" disabled>
                                    <option value="Day" <?= $staff['shift'] == 'Day' ? 'selected' : '' ?>>Day</option>
                                    <option value="Night" <?= $staff['shift'] == 'Night' ? 'selected' : '' ?>>Night</option>
                                </select>
                            </td>

                            <!-- Status -->
                            <td data-label="Status">
                                <button type="button"
                                    class="statusBtn <?= strtolower($staff['status']) === 'active' ? 'active' : 'inactive' ?>"
                                    disabled>
                                    <?= ucfirst($staff['status']) ?>
                                </button>
                            </td>

                            <!-- Actions -->
                            <td data-label="Actions" class="actions-cell">
                                <button class="editBtn" type="button">Edit</button>
                                <img src="public/assests/archive.png" alt="Archive"
                                    class="archive-icon" title="Archive">
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>


<div id="role-select-modal" class="modal" style="display:none;">
    <div class="modal-content role-selection">
        <h2>Select Role</h2>
        <form id="role-select-form">
            <div class="radio-group">
                <label class="radio-btn">
                    <input type="radio" name="roleType" value="staff" required>
                    <span class="radio-label">Staff</span>
                </label>
                <label class="radio-btn">
                    <input type="radio" name="roleType" value="driver" <?= $isMainAdmin ? '' : 'disabled' ?>>
                    <span class="radio-label">Admin/Driver</span>
                </label>
            </div>
            <div class="btn-group">
                <button type="submit" class="btn-confirm">Continue</button>
                <button type="button" class="btn-cancel" id="cancel-role">Cancel</button>
            </div>
        </form>
    </div>
</div>


<!-- Add Staff Modal -->
<div id="modal" style="display: flex;">

    <div id="new-product-modal">

        <div id="right">
            <div id="left">
            <img id="new-product-photo" src="public/assests/uploadImg.jpg" alt="photo">
            <input type="file" id="uploadInput" style="display:none;" accept="image/*">
            <button id="uploadBtn">Upload Photo</button>
        </div>
            <h2>Add New Staff</h2>

            <form id="staff-form">
                <div class="form-row">
                    <label for="name" >Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Maria Mercedes"  required>
                </div>

                <div class="form-row">
                    <label for="role">Position</label>
                    <select id="role" name="role" required>
                        <option value="">Select position</option>
                        <option value="Manager">Manager</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Chef">Chef</option>
                        <option value="Cleaner">Cleaner</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="category">Shift</label>
                    <select id="category" name="category" required>
                        <option value="">Select shift</option>
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                    </select>
                </div>

                <div class="form-row status-row">
                    <label>Status</label>
                    <div id="status-default">Active</div>
                </div>

                <div id="buttons">
                    <button type="button" id="add">Add</button>
                    <button type="button" id="cancel">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div> 

<!-- Add Account Modal -->
<div id="admin-modal">
    <div class="modal-card">
        <button class="modal-close" id="cancel-admin-btn">&times;</button>

        <div class="photo-section">
            <img id="admin-photo-preview" src="public/assests/uploadImg.jpg" alt="Photo">
            <input type="file" id="admin-upload-input" name="photo" accept="image/*" style="display:none;">
            <button type="button" id="admin-upload-btn">Upload Photo</button>
        </div>

        <div class="form-section">
            <h2>Add New Account</h2>
            <form id="admin-form">
                <div>
                    <div>
                        <div class="form-row">
                            <label for="admin-name">Full Name</label>
                            <input type="text" id="admin-name" placeholder="Maria Mercedes" required>
                        </div>

                        <div class="form-row">
                            <label for="account-role">Position</label>
                            <select id="account-role" required>
                                <option value="">Select role</option>
                                <option value="Admin">Admin</option>
                                <option value="Driver">Driver</option>
                            </select>
                        </div>

                        <div class="form-row">
                            <label>Status</label>
                            <input type="text" value="Active" disabled>
                        </div>

                        <div class="form-row">
                            <label for="admin-shift">Shift</label>
                            <select id="admin-shift" required>
                                <option value="">Select shift</option>
                                <option value="Day">Day</option>
                                <option value="Night">Night</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="form-row">
                            <label for="admin-username">Username</label>
                            <input type="text" id="admin-username" placeholder="username123" required>
                        </div>

                        <div class="form-row">
                            <label for="admin-email">Email</label>
                            <input type="email" id="admin-email" placeholder="example@example.com" required>
                        </div>

                        <div class="form-row">
                            <label for="admin-password">Password</label>
                            <input type="password" id="admin-password" placeholder="Enter password" required>
                            <div class="password-meter">
                                <div id="password-strength-bar"></div>
                            </div>
                            <p id="password-strength-text">Weak</p>
                        </div>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="submit" id="add-admin-btn">Add Account</button>
                    <button type="button" id="cancel-admin-btn-2">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>



<!-- OTP Modal -->
<div id="otp-modal" style="display:none;">
    <div class="modal-card">
        <button class="modal-close" id="cancel-otp-btn">&times;</button>
        <h2>Email Verification</h2>
        <p id="otpStatus">We sent a 6-digit code to your email. Please enter it below:</p>

        <form id="otp-form">
            <div class="form-row">
                <label for="otp-code">Enter OTP</label>
                <input type="text" id="otp-code" name="otp" maxlength="6" required>
            </div>
            <div class="modal-buttons">
                <button type="submit" id="verify-otp-btn">Verify</button>
                <button type="button" id="cancel-otp-btn-2">Cancel</button>
            </div>
            <button type="button" id="resend-otp-btn" disabled>Resend OTP (30s)</button>

        </form>
    </div>
</div>


<script>
const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/Leilife/', '/') ?>/";

// --- Modals & Buttons ---
const addNewBtn = document.getElementById('add-new');
const roleSelectModal = document.getElementById('role-select-modal');
const cancelRoleBtn = document.getElementById("cancel-role");
const roleSelectForm = document.getElementById("role-select-form");
const staffModal = document.getElementById("modal");
const addMember = document.getElementById("admin-modal");
const cancelStaffBtn = document.getElementById("cancel");
const viewArchiveBtn = document.getElementById('view-archive');
const addAccBtn = document.getElementById('add-admin');
const cancelMemberBtn = document.getElementById("cancel-admin-btn-2");

// Open role selection modal
addNewBtn.addEventListener('click', () => roleSelectModal.style.display = "flex");

// Cancel role selection
cancelRoleBtn.addEventListener("click", () => {
    roleSelectModal.style.display = "none";
    roleSelectForm.reset();
});

// Role selection submission
roleSelectForm.addEventListener("submit", e => {
    e.preventDefault();
    const selected = document.querySelector("input[name='roleType']:checked").value;
    roleSelectModal.style.display = "none";
    roleSelectForm.reset();

    if (selected === "staff") staffModal.style.display = "flex";
    else addMember.style.display = "flex";
});

// --- Staff Modal Upload ---
document.getElementById("uploadBtn").addEventListener("click", () => document.getElementById("uploadInput").click());
document.getElementById("uploadInput").addEventListener("change", e => {
    const file = e.target.files[0];
    if (file) document.getElementById("new-product-photo").src = URL.createObjectURL(file);
});

// Add new staff
document.getElementById("add").addEventListener("click", () => {
    const name = document.getElementById("name").value.trim();
    const role = document.getElementById("role").value.trim();
    const shift = document.getElementById("category").value;
    const status = "Active";
    const file = document.getElementById("uploadInput").files[0];

    if (!name || !role || !shift) {
        showModal("Please fill all fields", "error");
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("role", role);
    formData.append("shift", shift);
    formData.append("status", status);
    if (file) formData.append("photo", file);

    fetch(BASE_URL + "backend/admin/add_staff.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showModal("Staff added successfully!", "success");
                staffModal.style.display = "none";
                setTimeout(() => location.reload(), 1000);
            } else showModal("Error: " + data.message, "error");
        })
        .catch(err => showModal("Fetch error: " + err.message, "error"));
});

// Cancel staff modal
cancelStaffBtn.addEventListener('click', () => staffModal.style.display = "none");

// --- Admin Modal ---
const adminModal = document.getElementById('admin-modal');
const adminUploadBtn = document.getElementById('admin-upload-btn');
const adminUploadInput = document.getElementById('admin-upload-input');
const adminPhotoPreview = document.getElementById('admin-photo-preview');
const passwordInput = document.getElementById('admin-password');
const strengthMeter = document.getElementById('password-strength-bar');
const strengthText = document.getElementById('password-strength-text');

// Admin upload photo
adminUploadBtn.addEventListener('click', () => adminUploadInput.click());
adminUploadInput.addEventListener('change', e => {
    const file = e.target.files[0];
    if (file) adminPhotoPreview.src = URL.createObjectURL(file);
});

// Password strength meter
passwordInput.addEventListener('input', () => {
    const val = passwordInput.value;
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[\W]/.test(val)) strength++;

    const percent = (strength / 4) * 100;
    strengthMeter.style.width = percent + "%";
    const colors = ['#e74c3c', '#f39c12', '#f1c40f', '#2ecc71', '#27ae60'];
    strengthMeter.style.backgroundColor = colors[strength];
    const strengthTextMap = ["Weak", "Fair", "Good", "Strong", "Very Strong"];
    strengthText.textContent = strengthTextMap[strength];
});

cancelMemberBtn.addEventListener('click', () => addMember.style.display = "none");
// Add account form
document.getElementById('admin-form').addEventListener('submit', e => {
    e.preventDefault();

    const name = document.getElementById('admin-name').value.trim();
    const role = document.getElementById('account-role').value;
    const shift = document.getElementById('admin-shift').value;
    const username = document.getElementById('admin-username').value.trim();
    const email = document.getElementById('admin-email').value.trim();
    const password = passwordInput.value;
    const photo = adminUploadInput.files[0];

    if (!name || !role || !shift || !username || !email || !password || !photo) {
        showModal("Please fill all fields and upload a photo.", "error");
        return;
    }

    if (!/^[A-Za-z\s]+$/.test(name)) { showModal("Name can only contain letters and spaces.", "error"); return; }
    if (username.length < 8 || /\s/.test(username)) { showModal("Username must be at least 8 characters with no spaces.", "error"); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showModal("Invalid email format.", "error"); return; }
    if (password.length < 8) { showModal("Password must be at least 8 characters.", "error"); return; }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("role", role);
    formData.append("shift", shift);
    formData.append("username", username);
    formData.append("email", email);
    formData.append("password", password);
    formData.append("photo", photo);

    document.getElementById("otpStatus").innerText = "Sending OTP to email...";

    fetch(BASE_URL + "backend/admin/request_account_otp.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                adminModal.style.display = 'none';
                openOtpModal();
                document.getElementById("otpStatus").innerText = "Enter the 6-digit OTP sent to your email.";
            } else showModal(data.message, "error");
        })
        .catch(err => showModal("Fetch error: " + err.message, "error"));
});

// --- OTP Modal ---
const otpModal = document.getElementById('otp-modal');
const otpCancel1 = document.getElementById('cancel-otp-btn');
const otpCancel2 = document.getElementById('cancel-otp-btn-2');
const resendBtn = document.getElementById('resend-otp-btn');
const otpStatus = document.getElementById('otpStatus');
let resendTimer;

function startResendTimer() {
    clearInterval(resendTimer);
    resendBtn.disabled = true;
    let timeLeft = 20;
    resendBtn.textContent = `Resend OTP (${timeLeft}s)`;

    resendTimer = setInterval(() => {
        timeLeft--;
        resendBtn.textContent = `Resend OTP (${timeLeft}s)`;
        if (timeLeft <= 0) {
            clearInterval(resendTimer);
            resendBtn.disabled = false;
            resendBtn.textContent = "Resend OTP";
        }
    }, 1000);
}

function openOtpModal() {
    otpModal.style.display = 'flex';
    otpStatus.innerText = "We sent a 6-digit code to your email.";
    startResendTimer();
}

function closeOtpModal() {
    otpModal.style.display = 'none';
    clearInterval(resendTimer);
}

otpCancel1.addEventListener('click', closeOtpModal);
otpCancel2.addEventListener('click', closeOtpModal);

// OTP form submit
document.getElementById('otp-form').addEventListener('submit', e => {
    e.preventDefault();
    const otp = document.getElementById('otp-code').value.trim();
    if (!otp) { showModal("Please enter the OTP.", "error"); return; }

    fetch(BASE_URL + "backend/admin/verify_account_otp.php", {
        method: "POST",
        body: new URLSearchParams({ otp })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showModal("Account verified and created!", "success");
            closeOtpModal();
            setTimeout(() => location.reload(), 1200);
        } else showModal(data.message, "error");
    })
    .catch(err => showModal("Fetch error: " + err.message, "error"));
});

// Resend OTP
resendBtn.addEventListener('click', () => {
    resendBtn.disabled = true;
    resendBtn.textContent = "Sending...";
    otpStatus.innerText = "";
    const formData = new FormData();
    formData.append("resend", true);

    fetch(BASE_URL + "backend/admin/request_account_otp.php", { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                otpStatus.innerText = "New OTP sent. Please check your email.";
                resendBtn.textContent = "OTP Sent!";
                setTimeout(() => startResendTimer(), 1500);
            } else {
                otpStatus.innerText = data.message;
                resendBtn.textContent = "Resend OTP";
                resendBtn.disabled = false;
            }
        })
        .catch(err => {
            otpStatus.innerText = "Fetch error: " + err.message;
            resendBtn.textContent = "Resend OTP";
            resendBtn.disabled = false;
        });
});

// --- Notifications ---
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
    modal.onclick = e => { if (e.target === modal) closeModal(); };

    if (autoClose) setTimeout(closeModal, duration);
}

// --- Search Staff ---
const searchInput = document.getElementById('search-input');
const staffRows = document.querySelectorAll('.staff-row');
searchInput.addEventListener('input', () => {
    const search = searchInput.value.toLowerCase();
    staffRows.forEach(row => {
        const name = row.querySelector('.name-cell .inputData').value.toLowerCase();
        row.style.display = name.includes(search) ? 'table-row' : 'none';
    });
});

// --- Edit / Save Staff ---
const editButtons = document.querySelectorAll('.editBtn');
const archiveIcons = document.querySelectorAll('.archive-icon');

editButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.staff-row');
        const inputs = row.querySelectorAll('.inputData, .pcategory');
        const photo = row.querySelector('.profile-photo');
        const photoInput = row.querySelector('.photoInput');
        const statusBtn = row.querySelector('.statusBtn');
        const archiveIcon = row.querySelector('.archive-icon');
        const roleCell = row.querySelector('td:nth-child(2)');
        let roleInput = roleCell.querySelector('.inputData');

        const isEditing = btn.classList.contains('editing');

        if (!isEditing) {
            // Enter edit mode
            btn.textContent = 'Save';
            btn.style.setProperty("background-color", "#5f9861ff", "important");
            btn.style.setProperty("color", "#ffffffff", "important");
            btn.classList.add('editing');

            inputs.forEach(i => i.disabled = false);
            statusBtn.disabled = false;
            photoInput.disabled = false;
            archiveIcon.style.filter = 'brightness(0)';
            editButtons.forEach(other => { if (other !== btn) other.disabled = true; });
            archiveIcons.forEach(icon => { if (icon !== archiveIcon) { icon.style.pointerEvents = 'none'; icon.style.opacity = 0.4; }});
            [addMember, viewArchiveBtn, addAccBtn].forEach(b => { b.disabled = true; b.style.opacity = 0.5; });

            // Convert role to select if editable
            const currentRole = roleInput.value.toLowerCase();
            if (currentRole !== 'admin' && currentRole !== 'driver') {
                const positions = ['Manager', 'Cashier', 'Chef', 'Cleaner'];
                const select = document.createElement('select');
                select.className = 'inputData';
                positions.forEach(pos => {
                    const option = document.createElement('option');
                    option.value = pos;
                    option.text = pos;
                    if (pos === roleInput.value) option.selected = true;
                    select.appendChild(option);
                });
                roleInput.replaceWith(select);
                roleInput = select;
            } else roleInput.disabled = true;

            // Enable photo click
            photo.onclick = () => { if (!photoInput.disabled) photoInput.click(); };
            photoInput.onchange = e => { const file = e.target.files[0]; if(file) photo.src = URL.createObjectURL(file); };

        } else {
            // Save mode
            btn.textContent = 'Edit';
            btn.style.setProperty("background-color", "#fbf5ca", "important");
            btn.style.setProperty("color", "#79722b", "important");
            btn.classList.remove('editing');

            inputs.forEach(i => i.disabled = true);
            statusBtn.disabled = true;
            photoInput.disabled = true;
            archiveIcon.style.filter = 'brightness(0.5)';
            editButtons.forEach(b => b.disabled = false);
            archiveIcons.forEach(icon => { icon.style.pointerEvents = 'auto'; icon.style.opacity = 1; });
            [addMember, viewArchiveBtn, addAccBtn].forEach(b => { b.disabled = false; b.style.opacity = 1; });

            const staffId = row.dataset.id;
            const updatedData = {
                id: staffId,
                name: row.querySelector('.name-cell .inputData').value,
                role: roleInput.value,
                shift: row.querySelector('.pcategory').value,
                status: statusBtn.textContent.trim(),
            };
            const photoFile = photoInput.files[0];

            const formData = new FormData();
            Object.entries(updatedData).forEach(([k, v]) => formData.append(k, v));
            if (photoFile) formData.append('photo', photoFile);

            fetch(BASE_URL + 'backend/admin/update_staff.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => showModal(data.success ? 'Staff updated successfully!' : 'Error: ' + data.message, data.success ? 'success' : 'error'))
                .catch(err => showModal('Fetch error: ' + err.message, 'error'));
        }
    });
});

// --- Toggle Status ---
document.querySelectorAll('.statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.disabled) {
            if (btn.textContent.trim() === 'Active') {
                btn.textContent = 'Inactive';
                btn.classList.replace('active', 'inactive');
            } else {
                btn.textContent = 'Active';
                btn.classList.replace('inactive', 'active');
            }
        }
    });
});

// --- Toggle Archived / Active ---
viewArchiveBtn.addEventListener('click', () => {
    const url = new URL(window.location.href);
    url.searchParams.set('archived', url.searchParams.get('archived') === '1' ? '0' : '1');
    window.location.href = url.toString();
});

// --- Archive Staff ---
archiveIcons.forEach(icon => {
    icon.addEventListener('click', () => {
        const row = icon.closest('.staff-row');
        const staffId = row.dataset.id;
        if (!staffId) { showModal("Error: Missing staff ID", "error"); return; }

        const formData = new FormData();
        formData.append('staff_id', staffId);
        formData.append('is_archive', 1);

        fetch(BASE_URL + "backend/admin/archive_staff.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) row.remove();
                showModal(data.message, data.success ? "success" : "error");
            })
            .catch(err => showModal("Fetch error: " + err.message, "error"));
    });
});
</script>
