<?php
session_start();
require_once __DIR__ . '/../../backend/db_script/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
    exit;
}

// fetch the logged-in admin info
$stmt = $pdo->prepare("SELECT username FROM admin_accounts WHERE admin_id = :id");
$stmt->execute(['id' => $_SESSION['admin_id']]);
$currentAdmin = $stmt->fetch(PDO::FETCH_ASSOC);

$isMainAdmin = $currentAdmin && $currentAdmin['username'] === 'mAdmin';

$showArchived = $_GET['archived'] ?? 0; // 0 = active, 1 = archived
$stmt = $pdo->prepare("SELECT * FROM staff_roles WHERE is_archive = :archived");
$stmt->execute(['archived' => $showArchived]);
$staffRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="first-row">
    <h2>Staff</h2>
    <button type="button" id="view-archive"><span><?= $showArchived ? "View Active" : "View Archive" ?></span></button>
</div>

<div id="third-row">
    <div id="search_add">
        <form class="search-bar" role="search">
            <input type="search" id="search-input" placeholder="🔍 Search staff" aria-label="Search staff">
        </form>
        <div>
            <button type="button" class="add" id="add-member"><span>+ Add new member</span></button>
            <!--<button type="button" class="add" id="add-admin"><span>+ Add new admin</span></button> -->
            <?php if ($isMainAdmin): ?>
                <button type="button" class="add" id="add-admin"><span>+ Add account</span></button>
            <?php else: ?>
                <button type="button" class="add" id="add-admin" disabled
                    style="opacity:0.5;cursor:not-allowed"><span>+ Add account</span></button>
            <?php endif; ?>
        </div>
    </div>

    <div id="table-container">
        <table class="staff-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th style="text-align:center">Actions</th>
                </tr>
            </thead>
            <tbody id="staff-content">
                <?php foreach ($staffRoles as $staff): ?>
                    <tr class="staff-row" data-id="<?= isset($staff['staff_id']) ? htmlspecialchars($staff['staff_id']) : '' ?>">
                        <td>
                            <div class="name-cell">
                                <label class="photo-wrapper">
                                    <img class="profile-photo"
                                        src="<?= !empty($staff['staff_image']) ? "public/staffs/" . $staff['staff_image'] : "public/assests/about us.png" ?>"
                                        alt="profile-photo">
                                    <input type="file" class="photoInput" accept="image/*" style="display:none;" disabled>
                                </label>
                                <div>
                                    <input type="text" class="inputData" value="<?= htmlspecialchars($staff['staff_name']) ?>" disabled>
                                    <p class="staff-id">#<?= htmlspecialchars($staff['staff_id']) ?></p>
                                </div>
                            </div>
                        </td>

                        <td>
                            <input type="text" class="inputData" value="<?= htmlspecialchars($staff['staff_role']) ?>" disabled>
                        </td>

                        <td>
                            <select class="pcategory" disabled>
                                <option value="Day" <?= $staff['shift'] == 'Day' ? 'selected' : '' ?>>Day</option>
                                <option value="Night" <?= $staff['shift'] == 'Night' ? 'selected' : '' ?>>Night</option>
                            </select>
                        </td>

                        <td>
                            <button type="button"
                                class="statusBtn <?= strtolower($staff['status']) === 'active' ? 'active' : 'inactive' ?>"
                                disabled>
                                <?= ucfirst($staff['status']) ?>
                            </button>
                        </td>

                        <td class="actions-cell">
                            <button class="editBtn" type="button">Edit</button>
                            <img src="public/assests/archive.png" alt="Archive" class="archive-icon">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Add Staff Modal -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/uploadImg.jpg" alt="photo">
            <input type="file" id="uploadInput" style="display:none;" accept="image/*">
            <button id="uploadBtn">Upload Photo</button>
        </div>

        <div id="right">
            <form id="staff-form">
                <div class="form-row">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-row">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="">Select position</option>
                        <option value="Manager">Manager</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Chef">Chef</option>
                        <option value="Cleaner">Cleaner</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="category">Shift:</label>
                    <select id="category" name="category" required>
                        <option value="">Select shift</option>
                        <option value="Day">Day</option>
                        <option value="Night">Night</option>
                    </select>
                </div>

                <div class="form-row status-row">
                    <label>Status:</label>
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
            <input type="file" id="admin-upload-input" name="photo" accept="image/*" style="display:none;" >
            <button type="button" id="admin-upload-btn">Upload Photo</button>
        </div>

        <div class="form-section">
            <h2>Add New Account</h2>
            <form id="admin-form">
                <div class="form-row">
                    <label for="admin-name">Full Name</label>
                    <input type="text" id="admin-name" placeholder="John Doe" required>
                </div>

                <div class="form-row">
                    <label for="account-role">Role</label>
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
        <button type="button" id="resend-otp-btn" disabled>Resend OTP (30s)</button>
        <button type="button" id="cancel-otp-btn-2">Cancel</button>
      </div>
    </form>
  </div>
</div>


<script>
    const BASE_URL = "http://localhost/Leilife/";

    // --- Staff Modal ---
    const staffModal = document.getElementById("modal");
    const addMember = document.getElementById("add-member");
    const cancel = document.getElementById("cancel");
    addMember.addEventListener('click', () => staffModal.style.display = "flex");
    cancel.addEventListener('click', () => staffModal.style.display = "none");

    document.getElementById("uploadBtn").addEventListener("click", () => document.getElementById("uploadInput").click());
    document.getElementById("uploadInput").addEventListener("change", e => {
        const file = e.target.files[0];
        if (file) document.getElementById("new-product-photo").src = URL.createObjectURL(file);
    });

    // Add Staff
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

        fetch(BASE_URL + "backend/admin/add_staff.php", {
                method: "POST",
                body: formData
            })
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

    // --- Admin Modal ---
    const adminModal = document.getElementById('admin-modal');
    document.getElementById('add-admin').addEventListener('click', () => adminModal.style.display = 'flex');
    document.getElementById('cancel-admin-btn').addEventListener('click', () => adminModal.style.display = 'none');
    document.getElementById('cancel-admin-btn-2').addEventListener('click', () => adminModal.style.display = 'none');

    const adminUploadBtn = document.getElementById('admin-upload-btn');
    const adminUploadInput = document.getElementById('admin-upload-input');
    const adminPhotoPreview = document.getElementById('admin-photo-preview');
    adminUploadBtn.addEventListener('click', () => adminUploadInput.click());
    adminUploadInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (file) adminPhotoPreview.src = URL.createObjectURL(file);
    });

    // Password strength
    const passwordInput = document.getElementById('admin-password');
    const strengthMeter = document.getElementById('password-strength-bar');
    const strengthText = document.getElementById('password-strength-text');

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

   // Add Account (Admin/Driver)
document.getElementById('admin-form').addEventListener('submit', e => {
    e.preventDefault();

    const name = document.getElementById('admin-name').value.trim();
    const role = document.getElementById('account-role').value;
    const shift = document.getElementById('admin-shift').value;
    const username = document.getElementById('admin-username').value.trim();
    const email = document.getElementById('admin-email').value.trim();
    const password = passwordInput.value;
    const photo = adminUploadInput.files[0];

    // --- Basic empty check ---
    if (!name || !role || !shift || !username || !email || !password || !photo) {
        showModal("Please fill all fields and upload a photo.", "error");
        return;
    }

    // --- Name validation: letters only ---
    if (!/^[A-Za-z\s]+$/.test(name)) {
        showModal("Name can only contain letters and spaces.", "error");
        return;
    }

    // --- Username validation ---
    if (username.length < 8 || /\s/.test(username)) {
        showModal("Username must be at least 8 characters with no spaces.", "error");
        return;
    }

    // --- Email validation ---
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        showModal("Invalid email format.", "error");
        return;
    }

    // --- Password validation ---
    if (password.length < 8) {
        showModal("Password must be at least 8 characters.", "error");
        return;
    }

    // --- Everything validated, prepare FormData ---
    const formData = new FormData();
    formData.append("name", name);
    formData.append("role", role);
    formData.append("shift", shift);
    formData.append("username", username);
    formData.append("email", email);
    formData.append("password", password);
    formData.append("photo", photo);

    // --- Close Add Account modal instantly ---
    adminModal.style.display = 'none';

    // --- Open OTP modal instantly ---
    openOtpModal();
    document.getElementById("otpStatus").innerText = "Sending OTP to email...";

    // --- Send OTP request ---
    fetch(BASE_URL + "backend/admin/request_account_otp.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.otp_required) {
            document.getElementById("otpStatus").innerText =
                "Enter the 6-digit OTP sent to your email.";
        } else {
            document.getElementById("otpStatus").innerText = "Error: " + data.message;
        }
    })
    .catch(err => {
        document.getElementById("otpStatus").innerText = "Fetch error: " + err.message;
    });
});

// --- OTP Modal ---
const otpModal = document.getElementById('otp-modal');
const otpCancel1 = document.getElementById('cancel-otp-btn');
const otpCancel2 = document.getElementById('cancel-otp-btn-2');
const resendBtn = document.getElementById('resend-otp-btn');
const otpStatus = document.getElementById('otp-status'); // make sure <p id="otp-status"></p> exists in modal

let resendTimer; // timer reference

function openOtpModal() {
    otpModal.style.display = 'flex';
    if (otpStatus) otpStatus.innerText = "We sent a 6-digit code to your email.";

    // Disable resend button initially
    resendBtn.disabled = true;
    resendBtn.textContent = "Resend OTP (5:00)";

    // Start 5-minute countdown
    let timeLeft = 5 * 60; // 5 minutes in seconds
    resendTimer = setInterval(() => {
        timeLeft--;
        const minutes = Math.floor(timeLeft / 60).toString().padStart(2, '0');
        const seconds = (timeLeft % 60).toString().padStart(2, '0');
        resendBtn.textContent = `Resend OTP (${minutes}:${seconds})`;

        if (timeLeft <= 0) {
            clearInterval(resendTimer);
            resendBtn.disabled = false;
            resendBtn.textContent = "Resend OTP";
        }
    }, 1000);
}

function closeOtpModal() {
    otpModal.style.display = 'none';
    clearInterval(resendTimer);
}

otpCancel1.addEventListener('click', closeOtpModal);
otpCancel2.addEventListener('click', closeOtpModal);

// Submit OTP
document.getElementById('otp-form').addEventListener('submit', e => {
    e.preventDefault();
    const otp = document.getElementById('otp-code').value.trim();

    if (!otp) {
        showModal("Please enter the OTP.", "error");
        return;
    }

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
        } else {
            showModal("Error: " + data.message, "error");
        }
    })
    .catch(err => showModal("Fetch error: " + err.message, "error"));
});

// Resend OTP handler
resendBtn.addEventListener('click', () => {
    resendBtn.disabled = true;
    resendBtn.textContent = "Sending...";

    const formData = new FormData(document.getElementById('admin-form'));

    fetch(BASE_URL + "backend/admin/request_account_otp.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.otp_required) {
            otpStatus.innerText = "New OTP sent to your email!";
            openOtpModal(); // restart 5-min countdown
        } else {
            otpStatus.innerText = "Error: " + data.message;
            resendBtn.disabled = false;
        }
    })
    .catch(err => {
        otpStatus.innerText = "Fetch error: " + err.message;
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
        modal.onclick = e => {
            if (e.target === modal) closeModal();
        };
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

    // --- Edit / Save ---
    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.staff-row');
            const nameInput = row.querySelector('.name-cell .inputData');
            const photo = row.querySelector('.profile-photo');
            const photoInput = row.querySelector('.photoInput');
            const archiveIcon = row.querySelector('.archive-icon');
            const statusBtn = row.querySelector('.statusBtn');
            const shiftSelect = row.querySelector('.pcategory');
            const roleCell = row.querySelector('td:nth-child(2) .inputData');

            const isEditing = !nameInput.disabled;

            if (!isEditing) {
                // Enter edit mode
                nameInput.disabled = false;
                photoInput.disabled = false;
                archiveIcon.style.filter = 'brightness(0)';
                statusBtn.disabled = false;
                shiftSelect.disabled = false;

                const currentRole = roleCell.value;

                // Only allow role change if NOT Admin or Driver
                if (currentRole.toLowerCase() !== 'admin' && currentRole.toLowerCase() !== 'driver') {
                    const roleDropdown = document.createElement('select');
                    roleDropdown.className = 'roleDropdown';
                    ['Manager', 'Cashier', 'Chef', 'Cleaner'].forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r;
                        opt.textContent = r;
                        if (r === currentRole) opt.selected = true;
                        roleDropdown.appendChild(opt);
                    });
                    roleCell.replaceWith(roleDropdown);
                }

                photo.onclick = () => {
                    if (!photoInput.disabled) photoInput.click();
                };
                photoInput.onchange = e => {
                    const file = e.target.files[0];
                    if (file) photo.src = URL.createObjectURL(file);
                };

                btn.textContent = 'Save';
                btn.classList.add('editing');
                document.querySelectorAll('.editBtn').forEach(b => {
                    if (b !== btn) {
                        b.disabled = true;
                        b.style.opacity = 0.5;
                        b.style.cursor = 'not-allowed';
                    }
                });
                document.getElementById('add-member').disabled = true;
                document.getElementById('add-member').style.opacity = 0.5;

            } else {
                // Save
                nameInput.disabled = true;
                photoInput.disabled = true;
                archiveIcon.style.filter = 'brightness(0.5)';
                statusBtn.disabled = true;
                shiftSelect.disabled = true;

                const currentRole = roleCell.value || row.querySelector('.roleDropdown')?.value;

                // If role was editable dropdown, replace with readonly input again
                const roleDropdown = row.querySelector('.roleDropdown');
                if (roleDropdown) {
                    const newRole = roleDropdown.value;
                    const roleInput = document.createElement('input');
                    roleInput.type = 'text';
                    roleInput.className = 'inputData';
                    roleInput.value = newRole;
                    roleInput.disabled = true;
                    roleDropdown.replaceWith(roleInput);
                }

                btn.textContent = 'Edit';
                btn.classList.remove('editing');
                document.querySelectorAll('.editBtn').forEach(b => {
                    b.disabled = false;
                    b.style.opacity = 1;
                    b.style.cursor = 'pointer';
                });
                document.getElementById('add-member').disabled = false;
                document.getElementById('add-member').style.opacity = 1;

                const staffId = row.dataset.id;
                const updatedData = {
                    id: staffId,
                    name: nameInput.value,
                    role: currentRole, // either from dropdown or readonly input
                    shift: shiftSelect.value,
                    status: statusBtn.textContent.trim()
                };
                const photoFile = photoInput.files[0];
                const formData = new FormData();
                Object.entries(updatedData).forEach(([k, v]) => formData.append(k, v));
                if (photoFile) formData.append("photo", photoFile);

                fetch(BASE_URL + "backend/admin/update_staff.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => showModal(data.success ? "Staff updated successfully!" : "Error: " + data.message, data.success ? "success" : "error"))
                    .catch(err => showModal("Fetch error: " + err.message, "error"));
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

    const viewArchiveBtn = document.getElementById('view-archive');

    // Toggle between archived/active using URL search params
    viewArchiveBtn.addEventListener('click', () => {
        const url = new URL(window.location.href);
        if (url.searchParams.get('archived') === '1') {
            url.searchParams.set('archived', '0');
        } else {
            url.searchParams.set('archived', '1');
        }
        window.location.href = url.toString();
    });

    // Archive staff
    document.querySelectorAll('.archive-icon').forEach(icon => {
        icon.addEventListener('click', () => {
            const row = icon.closest('.staff-row');
            const staffId = row.dataset.id;

            if (!staffId) {
                showModal("Error: Missing staff ID", "error");
                return;
            }

            const formData = new FormData();
            formData.append('staff_id', staffId);
            formData.append('is_archive', 1); // mark as archived

            fetch(BASE_URL + "backend/admin/archive_staff.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showModal("Staff archived!", "success");
                        row.remove(); // remove from table immediately
                    } else {
                        showModal("Error: " + data.message, "error");
                    }
                })
                .catch(err => showModal("Fetch error: " + err.message, "error"));
        });
    });
</script>