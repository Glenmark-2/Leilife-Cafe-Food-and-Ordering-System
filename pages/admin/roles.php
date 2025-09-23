<?php
require_once __DIR__ . '/../../backend/db_script/db.php';

// Fetch all staff roles
$stmt = $pdo->query("SELECT * FROM staff_roles");
$staffRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="first-row">
    <h2>Staff</h2>
</div>

<div id="third-row">
    <div id="search_add">
        <form class="search-bar" role="search">
            <input
                type="search"
                id="search-input"
                placeholder="🔍 Search staff"
                aria-label="Search staff">
        </form>

        <button type="button" id="add-member"><span>+ Add new member</span></button>
    </div>

    <div id="table-container">
        <table class="staff-table">
            <thead>
                <tr>
                    <th><input type="checkbox" class="checkbox"></th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Shift</th>
                    <th></th>
                    <th>Status</th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="staff-content">
                <?php foreach ($staffRoles as $staff): ?>
                <tr class="staff-row" data-id="<?= $staff['staff_id'] ?>">
                    <td><input type="checkbox" class="checkbox"></td>

                    <!-- Name -->
                   <!-- Name -->
<td>
    <div class="name-cell">
        <label class="photo-wrapper">
            <img class="profile-photo"
                src="<?= !empty($staff['staff_image'])
                        ? "public/staffs/" . $staff['staff_image']
                        : "public/assests/about us.png" ?>"
                alt="profile-photo">
            <input type="file" class="photoInput" accept="image/*" style="display:none;" disabled>
        </label>
        <div>
            <input type="text" class="inputData"
                value="<?= htmlspecialchars($staff['staff_name']) ?>" disabled>
            <p class="staff-id">#<?= htmlspecialchars($staff['staff_id']) ?></p>
        </div>
    </div>
</td>


                    <!-- Role -->
                    <td>
                        <input type="text" class="inputData"
                            value="<?= htmlspecialchars($staff['staff_role']) ?>" disabled>
                    </td>

                    <!-- Shift -->
                    <td>
                        <select class="pcategory" disabled>
                            <option value="Day" <?= $staff['shift']=='Day'?'selected':'' ?>>Day</option>
                            <option value="Night" <?= $staff['shift']=='Night'?'selected':'' ?>>Night</option>
                        </select>
                    </td>

                    <!-- Status -->
                    <td>
                       <td>
    <button type="button"
        class="statusBtn <?= strtolower($staff['status']) === 'available' ? 'available' : 'unavailable' ?>"
        disabled>
        <?= ucfirst($staff['status']) ?>
    </button>
</td>
                    </td>
                    <!-- Edit -->
                    <td class="actions-cell">
    <button class="editBtn" type="button">Edit</button>
</td>
<td>
    <img src="public/assests/trash-bin.png"
         alt="Delete"
         class="trash-icon hidden"
         onclick="deleteRow(<?= $staff['staff_id'] ?>)">
</td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- Modal -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/uploadImg.jpg" alt="photo">
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
                    <label for="role">Role:</label>
                    <input type="text" id="role" name="role" required>
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

// ---- Live search ----
const searchInput = document.getElementById('search-input');
const staffRows = document.querySelectorAll('.staff-row');
searchInput.addEventListener('input', () => {
    const search = searchInput.value.toLowerCase();
    staffRows.forEach(row => {
        const name = row.querySelector('.name-cell .inputData').value.toLowerCase();
        row.style.display = name.includes(search) ? 'table-row' : 'none';
    });
});

// ---- Edit & Save ----
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.staff-row');
        const nameInput = row.querySelector('.name-cell .inputData');
        const roleInput = row.querySelector('td:nth-child(3) .inputData');
        const shiftSelect = row.querySelector('.pcategory');
        const statusBtn = row.querySelector('.statusBtn');
        const photo = row.querySelector('.profile-photo');
        const photoInput = row.querySelector('.photoInput');
        const trashIcon = row.querySelector('.trash-icon');
        const isEditing = !nameInput.disabled;

        if (!isEditing) {
            // --- ENTER EDIT MODE ---
            [nameInput, roleInput, shiftSelect, statusBtn, photoInput].forEach(el => el.disabled = false);
            photo.classList.add('editable');
            trashIcon.classList.remove("hidden");

            btn.textContent = 'Save';
            btn.style.backgroundColor = '#75c277';
            btn.style.color = '#036d2b';

            // 🚫 disable all other edit buttons + add-member
            document.querySelectorAll('.editBtn').forEach(b => {
                if (b !== btn) {
                    b.disabled = true;
                    b.style.opacity = "0.5";
                    b.style.cursor = "not-allowed";
                }
            });
            document.getElementById("add-member").disabled = true;
            document.getElementById("add-member").style.opacity = "0.5";

            // Preview photo when selected
            photoInput.addEventListener('change', e => {
                const file = e.target.files[0];
                if (file) {
                    photo.src = URL.createObjectURL(file);
                    photo.dataset.newFile = file.name;
                }
            });

            // Clicking image opens file picker
            photo.addEventListener('click', () => {
                if (!photoInput.disabled) photoInput.click();
            });

        } else {
            // --- SAVE MODE ---
            [nameInput, roleInput, shiftSelect, statusBtn, photoInput].forEach(el => el.disabled = true);
            photo.classList.remove('editable');
            trashIcon.classList.add("hidden");

            btn.textContent = 'Edit';
            btn.style.backgroundColor = '#C6C3BD';
            btn.style.color = '#22333B';

            // ✅ re-enable all buttons
            document.querySelectorAll('.editBtn').forEach(b => {
                b.disabled = false;
                b.style.opacity = "1";
                b.style.cursor = "pointer";
            });
            document.getElementById("add-member").disabled = false;
            document.getElementById("add-member").style.opacity = "1";

            // Save to backend
            const staffId = row.dataset.id;
            const formData = new FormData();
            formData.append("id", staffId);
            formData.append("name", nameInput.value);
            formData.append("role", roleInput.value);
            formData.append("shift", shiftSelect.value);
            formData.append("status", statusBtn.textContent.trim());
            if (photoInput.files[0]) formData.append("photo", photoInput.files[0]);

            fetch(BASE_URL + "backend/admin/update_staff.php", {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showModal("Staff updated successfully!", "success");
                } else {
                    showModal("Update failed: " + data.message, "error");
                }
            })
            .catch(err => showModal("Fetch error: " + err.message, "error"));
        }
    });
});

// ---- Status toggle ----
document.querySelectorAll('.statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.disabled) {
            if (btn.textContent.trim() === 'Available') {
                btn.textContent = 'Unavailable';
                btn.classList.remove('available');
                btn.classList.add('unavailable');
            } else {
                btn.textContent = 'Available';
                btn.classList.remove('unavailable');
                btn.classList.add('available');
            }
        }
    });
});

// ---- Modal (Add new member) ----
const addMember = document.getElementById("add-member");
const modal = document.getElementById("modal");
const cancel = document.getElementById("cancel");

addMember.addEventListener('click', () => modal.style.display = "flex");
cancel.addEventListener('click', () => modal.style.display = "none");

// File upload preview inside modal
document.getElementById("uploadBtn").addEventListener("click", () => {
    document.getElementById("uploadInput").click();
});
document.getElementById("uploadInput").addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
        document.getElementById("new-product-photo").src = URL.createObjectURL(file);
    }
});

// ---- Add staff ----
document.getElementById("add").addEventListener("click", () => {
    const name   = document.getElementById("name").value.trim();
    const role   = document.getElementById("role").value.trim();
    const shift  = document.getElementById("category").value;
    const status = "Available";
    const file   = document.getElementById("uploadInput").files[0];

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
            modal.style.display = "none";
            setTimeout(() => location.reload(), 1000); // refresh after add
        } else {
            showModal("Error: " + data.message, "error");
        }
    })
    .catch(err => showModal("Fetch error: " + err.message, "error"));
});

// ---- Delete staff ----
function deleteRow(staffId) {
    if (!confirm("Are you sure you want to delete this staff?")) return;
    fetch(BASE_URL + "backend/admin/delete_staff.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "id=" + encodeURIComponent(staffId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showModal("Staff deleted successfully!", "success");
            document.querySelector(`tr[data-id="${staffId}"]`).remove();
        } else {
            showModal("Delete failed: " + data.message, "error");
        }
    })
    .catch(err => showModal("Fetch error: " + err.message, "error"));
}

// ---- Notification modal ----
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
      </div>
    `;
    document.body.appendChild(modal);
    const style = document.createElement("style");
    style.innerHTML = `
      .notif-modal { display:none; position:fixed; z-index:10000; left:0; top:0;
        width:100%; height:100%; background:rgba(0,0,0,0.4); justify-content:center; align-items:center; }
      .notif-content { background:white; padding:20px 30px; border-radius:10px; text-align:center;
        box-shadow:0 4px 10px rgba(0,0,0,0.3); min-width:250px; animation:popin 0.3s ease; }
      .notif-content p { margin-bottom:15px; font-size:16px; }
      .notif-content button { padding:6px 16px; border:none; border-radius:6px; cursor:pointer; font-size:14px; color:white; }
      .notif-content button.success { background:#4caf50; }
      .notif-content button.error { background:#f44336; }
      @keyframes popin { from { transform:scale(0.8); opacity:0; } to { transform:scale(1); opacity:1; } }
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
