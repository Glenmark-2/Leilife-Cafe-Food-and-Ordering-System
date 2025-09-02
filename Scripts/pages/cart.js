const changeBtn = document.getElementById("change");
    let mode = document.getElementById("mode");
    let motor = document.getElementById("motor");

    changeBtn.addEventListener('click', () => {
        if (mode.textContent === "Delivery") {
            motor.src = "../public/assests/walk.png";
            mode.textContent = "Pick up";
        } else {
            motor.src="../public/assests/motorbike.png";
            mode.textContent = "Delivery";
        }
    });

    function changeQty(change) {
        let qtyInput = document.getElementById("quantity");
        let current = parseInt(qtyInput.value) || 1;
        let newValue = current + change;
        if (newValue < 1) newValue = 1; 
        qtyInput.value = newValue;
    }

    // 🗑️ Trash bin clear mid-div
    const trashBin = document.querySelector("#mid-div img"); 
    const midDiv = document.getElementById("mid-div");

    trashBin.addEventListener("click", () => {
        midDiv.innerHTML = ""; // clears all content inside mid-div
    });