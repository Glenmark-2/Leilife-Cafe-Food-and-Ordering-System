function changeQty(change) {
    const qtyInput = document.getElementById("quantity");
    let current = parseInt(qtyInput.value) || 1;
    let newValue = current + change;
    if (newValue < 1) newValue = 1; // prevent going below 1
    qtyInput.value = newValue;
}