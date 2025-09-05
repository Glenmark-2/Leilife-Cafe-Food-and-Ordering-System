<div id="first-row">
    <h2>Role</h2>
</div>



<div id="third-row">
    <div id="top">
        <form action="search.php" method="get" class="search-bar" role="search">
            <input
                type="search"
                id="search-input"
                name="q"
                placeholder="🔍 Search product"
                aria-label="Search products">
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
            <div id="product-name">
                <img id="profile-photo" src="public/assests/about us.png" alt="profile-photo">
                <div style="align-content: center; width:100%;">
                    <p>Ms. Fried Chicken</p>
                </div>
            </div>

            <div id="price-content">
                <p>Admin/Owner</p>
            </div>

            <div id="category-content">
                <select class="pcategory" disabled>
                    <option selected>Day</option>
                    <option>Night</option>
                </select>
            </div>

            <div id="status-content">
                <button class="statusBtn" id="statusBtn" type="button" disabled>Available</button>
            </div>

            <div id="edit-content">
                <button class="editBtn" id="editBtn" type="button">Edit</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL -->
<div id="modal">
    <div id="new-product-modal">
        <div id="left">
            <img id="new-product-photo" src="public/assests/about us.png" alt="photo">
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
                        <option value="day">Day</option>
                        <option value="night">Night</option>
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
   

    // 🔹 Status button toggle
    document.querySelectorAll('.statusBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!btn.disabled) { // only if editable
                if (btn.textContent === "Available") {
                    btn.textContent = "Unavailable";
                    btn.classList.add('clicked');
                } else {
                    btn.textContent = "Available";
                    btn.classList.remove('clicked');
                }
            }
        });
    });

    // 🔹 Edit / Save toggle per row
    document.querySelectorAll('.editBtn').forEach(editBtn => {
        let isEditing = false;

        editBtn.addEventListener("click", () => {
            const row = editBtn.closest("#products-content"); // find that product row
            const pcategory = row.querySelector(".pcategory");
            const statusBtn = row.querySelector(".statusBtn");

            if (!isEditing) {
                // Enable fields
                pcategory.disabled = false;
                statusBtn.disabled = false;

                // Styling for editable state
                pcategory.style.backgroundColor = "#ffffff";
                pcategory.style.padding = "5px";
                pcategory.style.borderRadius = "10px";
                statusBtn.style.opacity = "1";

                // Change button look
                editBtn.textContent = "Save";
                editBtn.style.backgroundColor = "#75c277";
                editBtn.style.color = "#036d2b";

                isEditing = true;
            } else {
                // Disable again
                pcategory.disabled = true;
                statusBtn.disabled = true;

                // Reset styles
                pcategory.style.backgroundColor = "transparent";
                statusBtn.style.opacity = "0.6";

                // Example: log saved values
                
                // Reset button look
                editBtn.textContent = "Edit";
                editBtn.style.backgroundColor = "#C6C3BD";
                editBtn.style.color = "#22333B";

                isEditing = false;
            }
        });
    });

    // 🔹 Modal controls
    const addMember = document.getElementById("add-member");
    const modal = document.getElementById("modal");
    const cancel = document.getElementById("cancel");

    addMember.addEventListener('click', () => {
        modal.style.display = "flex";
    });

    cancel.addEventListener("click", () => {
        modal.style.display = "none";
    });
</script>
