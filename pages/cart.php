<style>
    #cart-box {
    position: fixed; 
    top: 75px;  /* push down so it appears just below the header */
    right: 0;   /* stick to right side */
    z-index: 999;   
    display: flex;
    flex-direction: column;
    width: 300px; /* slightly wider for breathing space */
    background: white; 
    font-family: Arial, sans-serif;
    font-size: 13px;
    box-shadow: -2px 0 8px rgba(0,0,0,0.1);
}

    .inside-div {
        padding: 5px;
        display: flex;
        flex-direction: column;
    }

    #first-div{
        height: 65vh;
        margin: 12px;
    }

    #top-div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #d0d0d0;
        padding: 6px;              
        border-radius: 5px;
    }

    #top-div img {
        width: 22px; /* smaller icon */
        margin-right: 8px;
    }

    #top-div p {
        flex-grow: 1;
        margin: 0;
        font-weight: bold;
        font-size: 13px;
    }

    h3 {
        margin-top: 15px;
        margin-bottom: 0;
        text-align: center;
        font-size: 14px;
    }

    #mid-div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 4px 0;
        gap: 6px;
    }

    #mid-div img {
        width: 14px; 
        height: 16px; /* smaller trash icon */
        padding-top: 0;
    }

    .qty-controls {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .qty-controls input {
        width: 40px;
        height: 24px;
        text-align: center;
        border: none;
        font-size: 12px;
        background-color: transparent;
    }

    .qty-controls button {
        border: 1px solid #000; 
        background-color: lightgray; 
        border-radius: 50%; 
        width: 22px; 
        height: 22px; 
        font-size: 13px;
        color: black;
        cursor: pointer;
    }

    .product-name {
        flex-grow: 1;
        text-align: left;
        font-size: 13px;
    }

    .product-price {
        font-weight: bold;
        color: #333;
        min-width: 60px;
        text-align: right;
        font-size: 13px;
    }

    #second-div{
        background-color: #aaa480;
        padding: 12px;
    }

    .second-div-content{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
    }
</style>


<div id="cart-box">
    <div class="inside-div" id="first-div">
        <div id="top-div">
            <img src="../public/assests/motorbike.png" alt="motor" id="motor">
            <p id="mode">Delivery</p>
            <?php include "../components/buttonTemplate.php";
                echo createButton(25,60,"Change", "change",10);
            ?> 
        </div>

        <h3>My Cart</h3>

        <div id="mid-div">
            <img src="../public/assests/trash-bin.png" alt="trash" style="width: 18px; height:20px;">
            
            <div class="qty-controls">
                <input id="quantity" type="number" min=1 value="1" max=10 disabled>
                <button onclick="changeQty(1)">+</button>
            </div>

            <p class="product-name">Product name</p>
            <p class="product-price">P100.00</p>
        </div>
    </div>

    <div class="inside-div" id="second-div">
        <div class="second-div-content" >
            <p>Subtotal</p>
            <p>P100.00</p>
        </div>
        <div class="second-div-content" >
            <p style="margin-top: 0;">Delivery fee</p>
            <p style="margin-top: 0;">P50.00</p>
        </div>

        <div class="second-div-content">
            <p><b>Total</b></p>
            <p><b>P150.00</b></p>
        </div>

        <?php 
        echo createButton(40,280,"Check out", "check-out",18);
        ?>
    </div>
</div>

<script>
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
</script>
