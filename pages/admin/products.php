<div id="first-row">
    <h2>Products</h2>
</div>

<div id="second-row">
    <button type="button" class="box-row">All</button>
    <button type="button" class="box-row">Rice Meals</button>
    <button type="button" class="box-row">Beverages</button>
    <button type="button" class="box-row">Featured</button>

</div>
<hr>

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

        <button type="button" id="add-product"><span>+Add new product</span></button>
    </div>

    <div id="mid">
        <input type="checkbox" id="select-all" class="checkbox">
        <p style="width: 26%;">Name</p>
        <p style="width: 15%;">Price</p>
        <p style="width: 15%;">Category</p>
        <p style="width: 15%;">Status</p>
    </div>

    <div id="products-content">
        <input type="checkbox" id="select-all" class="checkbox" style="margin-left:5px;">
        <div id="product-name">
            <img id="product-photo" src="public/assests/about us.png" alt="product-photo">
            <div style="align-content: center; width:100%;">
                <input type="text" name="" value="Fried Chicken" disabled id="pname" class="inputData">
                <p style="font-size: .5em; color:gray; margin-left:3px;">#ewanKoAnoTo</p>
            </div>
        </div>

        <div id="price-content">
            <input type="number" name="" value="100.00" disabled id="pprice" class="inputData">
        </div>

        <div id="category-content">
            <select id="pcategory" name="category" disabled>
                <option value="rice-meal" selected>Rice Meal</option>
                <option value="beverage">Beverage</option>
                <option value="featured">Featured</option>
            </select>
        </div>

        <div id="status-content">
            <button id="statusBtn" type="button" disabled>Available</button>
        </div>

        <div id="edit-content">
            <button id="editBtn" class="editBtn" type="button">Edit</button>
        </div>

    </div>


</div>





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
                <label for="price">Price:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>

            <div class="form-row">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="">Select category</option>
                    <option value="rice-meal">Rice Meal</option>
                    <option value="beverage">Beverage</option>
                    <option value="featured">Featured</option>
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
    document.querySelectorAll('.box-row').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.box-row').forEach(btn => btn.classList.remove('clicked'));
            this.classList.add('clicked');
        });
    });

    const btn = document.getElementById("statusBtn");
    btn.addEventListener('click', () => {
        if (btn.textContent === "Available") {
            btn.textContent = "Unavailable";
            btn.classList.add('clicked');
        } else {
            btn.textContent = "Available";
            btn.classList.remove('clicked');
        }
    });

    const editBtn = document.getElementById("editBtn");
    const pname = document.getElementById("pname");
    const pprice = document.getElementById("pprice");
    const pcategory = document.getElementById("pcategory");
    const statusBtn = document.getElementById("statusBtn");

    let isEditing = false;

    // Toggle Edit/Save
    editBtn.addEventListener("click", () => {
        if (!isEditing) {
            // Enable fields
            pname.disabled = false;
            pprice.disabled = false;
            pcategory.disabled = false;
            statusBtn.disabled = false;

            // White background for inputs
            pname.style.backgroundColor = "#ffffff";
            pprice.style.backgroundColor = "#ffffff";
            pcategory.style.backgroundColor = "#ffffff";

            pname.style.padding = "5px";
            pprice.style.padding = "5px";
            pcategory.style.padding = "5px";

            pname.style.borderRadius = "10px";
            pprice.style.borderRadius = "10px";
            pcategory.style.borderRadius = "10px";

            statusBtn.style.opacity = "1";

            editBtn.textContent = "Save";
            editBtn.style.backgroundColor = "#75c277";
            editBtn.style.color = "#036d2b";

            isEditing = true;
        } else {
            // Disable fields again
            pname.disabled = true;
            pprice.disabled = true;
            pcategory.disabled = true;
            statusBtn.disabled = true;

            // Reset styles
            pname.style.backgroundColor = "transparent";
            pprice.style.backgroundColor = "transparent";
            pcategory.style.backgroundColor = "transparent";
            statusBtn.style.opacity = "0.6";

            console.log("Saved values:", {
                name: pname.value,
                price: pprice.value,
                category: pcategory.value,
                status: statusBtn.textContent
            });

            editBtn.textContent = "Edit";
            editBtn.style.backgroundColor = "#C6C3BD";
            editBtn.style.color = "#22333B";

            isEditing = false;
        }
    });

    const addItem = document.getElementById("add-product");
    const modal = document.getElementById("modal");
    const cancel = document.getElementById("cancel");
    addItem.addEventListener('click', () => {
        modal.style.display ="flex";
    });

    cancel.addEventListener("click",() => {
        modal.style.display ="none";
    });
</script>