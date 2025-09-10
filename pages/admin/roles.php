<?php
require_once __DIR__ . '/../../backend/db_script/db.php';

// Fetch all staff roles
$stmt = $pdo->query("SELECT * FROM staff_roles");
$staffRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div id="first-row">
    <h2>Role</h2>
</div>

<div id="third-row">
    <div id="top">
        <form class="search-bar" role="search">
            <input
                type="search"
                id="search-input"
                placeholder="🔍 Search staff"
                aria-label="Search staff">
        </form>

        <button type="button" id="add-member"><span>+ Add new member</span></button>
    </div>

    <div id="table-wrapper">
        <div id="mid">
            <p style="width: 26%;">Name</p>
            <p style="width: 15%;">Role</p>
            <p style="width: 15%;">Shift</p>
            <p style="width: 15%;">Status</p>
        </div>

        <div id="products-content">
            <?php foreach ($staffRoles as $staff): ?>
            <div class="product-row" data-id="<?= $staff['staff_id'] ?>">
                <div id="product-name">
                    <img id="profile-photo" src="<?= !empty($staff['staff_image']) ? "public/staffs/" . $staff['staff_image'] : "public/assests/about us.png" ?>" alt="profile-photo">
                    <div style="align-content: center; width:100%;">
                        <input type="text" class="inputData" value="<?= htmlspecialchars($staff['staff_name']) ?>" disabled>
                    </div>
                </div>

                <div id="price-content">
                    <input type="text" class="inputData" value="<?= htmlspecialchars($staff['staff_role']) ?>" disabled>
                </div>

                <div id="category-content">
                    <select class="pcategory" disabled>
                        <option value="Day" <?= $staff['shift']=='Day'?'selected':'' ?>>Day</option>
                        <option value="Night" <?= $staff['shift']=='Night'?'selected':'' ?>>Night</option>
                    </select>
                </div>

                <div id="status-content">
                    <button id="statusBtn" class="<?= strtolower($staff['status'] ?? 'Available') === 'unavailable' ? 'clicked' : '' ?>" type="button" disabled>
                        <?= ucfirst($staff['status'] ?? 'Available') ?>
                    </button>
                </div>

                <div id="edit-content">
                    <button id="editBtn" class="editBtn" type="button">Edit</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/about us.png" alt="photo">
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
const searchInput = document.getElementById('search-input');
const staffRows = document.querySelectorAll('.product-row');

searchInput.addEventListener('input', () => {
    const search = searchInput.value.toLowerCase();
    staffRows.forEach(row => {
        const name = row.querySelector('#product-name .inputData').value.toLowerCase();
        row.style.display = name.includes(search) ? 'flex' : 'none';
    });
});

// ---- Edit & Save ----
document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        const row = btn.closest('.product-row');
        const nameInput = row.querySelector('#product-name .inputData');
        const roleInput = row.querySelector('#price-content .inputData');
        const shiftSelect = row.querySelector('.pcategory');
        const statusBtn = row.querySelector('#statusBtn');
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

            // Apply edit styles (same as products.php)
            [nameInput, roleInput, shiftSelect].forEach(el => {
                el.style.padding = '5px 10px';
                el.style.border = '1px solid black';
                el.style.borderRadius = '10px';
                el.style.backgroundColor = '#ffffff';
            });

            // Change button to Save
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

    // ✅ Send update to backend
    // ✅ Send update to backend
const staffId = row.dataset.id;
const updatedData = {
    id: staffId,                               // dati: staff_id
    name: nameInput.value,                     // dati: staff_name
    role: roleInput.value,                     // dati: staff_role
    shift: shiftSelect.value,
    status: statusBtn.textContent
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
document.querySelectorAll('#statusBtn').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!btn.disabled) {
            btn.textContent = btn.textContent === 'Available' ? 'Unavailable' : 'Available';
            btn.classList.toggle('clicked');
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


</script>
