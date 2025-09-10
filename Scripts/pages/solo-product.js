function changeQty(change) {
    const qtyInput = document.getElementById("quantity");
    let current = parseInt(qtyInput.value) || 1;
    let newValue = current + change;
    if (newValue < 1) newValue = 1; // prevent going below 1
    qtyInput.value = newValue;
}

// ✅ JS: limit to 3 flavors for product_id = 11
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".flavor-checkbox");
    if (checkboxes.length > 0) {
        checkboxes.forEach(cb => {
            cb.addEventListener("change", () => {
                const selected = document.querySelectorAll(".flavor-checkbox:checked");
                if (selected.length > 3) {
                    alert("You can select up to 3 flavors only.");
                    cb.checked = false;
                }
            });
        });
    }
});

