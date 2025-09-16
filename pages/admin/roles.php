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
                </tr>
            </thead>
            <tbody id="staff-content">
                <?php foreach ($staffRoles as $staff): ?>
                <tr class="staff-row" data-id="<?= $staff['staff_id'] ?>">
                    <td><input type="checkbox" class="checkbox"></td>

                    <!-- Name -->
                    <td>
                        <div class="name-cell">
                            <img class="profile-photo"
                                src="<?= !empty($staff['staff_image'])
                                        ? "public/staffs/" . $staff['staff_image']
                                        : "public/assests/about us.png" ?>"
                                alt="profile-photo">
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
                    <td>
                        <button id="editBtn" class="editBtn" type="button">Edit</button>
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
// ---- Live search ----
const BASE_URL = "http://localhost/Leilife/";
// live search staff
const searchInput = document.getElementById('search-input');
const staffRows = document.querySelectorAll('.staff-row');

searchInput.addEventListener('input', () => {
    const search = searchInput.value.toLowerCase();
    staffRows.forEach(row => {
        const name = row.querySelector('.name-cell .inputData').value.toLowerCase();
        row.style.display = name.includes(search) ? 'table-row' : 'none'; // 🔑 table-row instead of flex
    });
});


// ---- Edit & Save ----
// ---- Edit & Save ----
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.staff-row'); // ✅ dati .product-row
        const nameInput = row.querySelector('.name-cell .inputData'); // ✅ tama sa markup
        const roleInput = row.querySelector('td:nth-child(3) .inputData'); // ✅ Role column
        const shiftSelect = row.querySelector('.pcategory'); // ✅ Shift select
        const statusBtn = row.querySelector('button'); // or add .statusBtn class sa button
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

            // Apply edit styles
            [nameInput, roleInput, shiftSelect].forEach(el => {
                el.style.padding = '5px 10px';
                el.style.border = '1px solid black';
                el.style.borderRadius = '10px';
                el.style.backgroundColor = '#ffffff';
            });

            // Set initial status color
            if (statusBtn.textContent.trim() === 'Available') {
                statusBtn.classList.add('available');
                statusBtn.classList.remove('unavailable');
            } else {
                statusBtn.classList.add('unavailable');
                statusBtn.classList.remove('available');
            }

            // Change Edit button to Save
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

            // Send update to backend
            const staffId = row.dataset.id;
            const updatedData = {
                id: staffId,
                name: nameInput.value,
                role: roleInput.value,
                shift: shiftSelect.value,
                status: statusBtn.textContent.trim()
            };

            fetch(BASE_URL + "backend/admin/update_staff.php", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updatedData)
            })
            .then(res => res.json())
            .then(data => {
                console.log("Response from server:", data);
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


// ---- Modal ----
const addMember = document.getElementById("add-member");
const modal = document.getElementById("modal");
const cancel = document.getElementById("cancel");

addMember.addEventListener('click', () => modal.style.display = "flex");
cancel.addEventListener('click', () => modal.style.display = "none");



// open file picker
document.getElementById("uploadBtn").addEventListener("click", () => {
    document.getElementById("uploadInput").click();
});

// preview image sa modal
document.getElementById("uploadInput").addEventListener("change", (e) => {
    const file = e.target.files[0];
    if (file) {
        document.getElementById("new-product-photo").src = URL.createObjectURL(file);
    }
});

// ADD STAFF
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

        // close modal
        document.getElementById("modal").style.display = "none";

        // clear fields
        document.getElementById("name").value = "";
        document.getElementById("role").value = "";
        document.getElementById("category").value = "";
        document.getElementById("uploadInput").value = "";
        document.getElementById("new-product-photo").src = "public/assests/about us.png";

        // 🔹 Append new row directly
        const tbody = document.getElementById("staffTableBody");
        const newRow = document.createElement("tr");
        newRow.innerHTML = `
            <td>${data.inserted_id}</td>
            <td>${document.getElementById("name").value}</td>
            <td>${document.getElementById("role").value}</td>
            <td>${document.getElementById("category").value}</td>
            <td>Available</td>
            <td><img src="public/staffs/${data.staff_image}" width="40"></td>
        `;
        loadStaffTable();

    } else {
        showModal("Error: " + data.message, "error");
    }
    })
    .catch(err => showModal("Fetch error: " + err.message, "error"));
});

function loadStaffTable() {
    fetch(BASE_URL + "backend/admin/get_staff.php")
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById("staffTableBody");
            tbody.innerHTML = ""; // clear table

            data.forEach(staff => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${staff.staff_id}</td>
                    <td>${staff.staff_name}</td>
                    <td>${staff.staff_role}</td>
                    <td>${staff.shift}</td>
                    <td>${staff.status}</td>
                    <td><img src="public/staffs/${staff.staff_image}" width="40"></td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(err => {
            console.error("Error loading staff:", err);
        });
}

function showModal(message, type = "success", autoClose = true, duration = 3000) {
  // check if modal already exists
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

    // basic styles
    const style = document.createElement("style");
    style.innerHTML = `
      .notif-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background: rgba(0,0,0,0.4);
        justify-content: center;
        align-items: center;
      }
      .notif-content {
        background: white;
        padding: 20px 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        min-width: 250px;
        animation: popin 0.3s ease;
      }
      .notif-content p { margin-bottom: 15px; font-size: 16px; }
      .notif-content button {
        padding: 6px 16px; border: none; border-radius: 6px;
        cursor: pointer; font-size: 14px; color: white;
      }
      .notif-content button.success { background: #4caf50; }
      .notif-content button.error { background: #f44336; }
      @keyframes popin {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  }

  // set message
  document.getElementById("notif-message").textContent = message;

  // set button color based on type
  const closeBtn = document.getElementById("notif-close");
  closeBtn.className = type === "success" ? "success" : "error";

  // show modal
  modal.style.display = "flex";

  // close handlers
  const closeModal = () => modal.style.display = "none";
  closeBtn.onclick = closeModal;
  modal.onclick = (e) => { if (e.target === modal) closeModal(); };

  // auto close after duration
  if (autoClose) {
    setTimeout(() => {
      closeModal();
    }, duration);
  }
}
// Open modal
addMember.addEventListener('click', () => {
  modal.style.display = "flex";
});

// Close modal
cancel.addEventListener('click', () => {
  modal.style.display = "none";
});

// Optional: close when clicking outside modal content
window.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.style.display = "none";
  }
});

</script>
