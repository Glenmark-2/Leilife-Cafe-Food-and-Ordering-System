<div style="margin:50px 200px;
        display:flex;
        flex-direction:row;
        gap:20px;">
    
    <!-- Product image -->
    <img src="../public/assests/about us.png"
         style="width: 30%; border-radius:15px;">

    <!-- Product details -->
    <div style="background-color:aliceblue; 
                width:100%; 
                padding: 30px;
                border-radius:20px;">
        
        <!-- Name + heart -->
        <div style="width: 100%; 
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom: 100px;">
            
            <h2 style="margin:0;">Product name Product name</h2>
            
            <button id="heartBtn" 
                    style="border:2px solid black; 
                           background-color:transparent; 
                           border-radius:50%; 
                           width:35px; 
                           height:35px; 
                           font-size:16px;
                           color:black;
                           cursor:pointer;
                           display:flex;
                           align-items:center;
                           justify-content:center;">
                ❤
            </button>
        </div>

        <!-- Price + Quantity -->
        <div style="width: 100%; 
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    margin-bottom:30px;">
            
            <p style="font-size:20px; margin:0;">₱100.00</p>
            
            <!-- Quantity Selector -->
            <div style="display:flex; align-items:center;">

                <button onclick="changeQty(-1)" 
                        style="border:1px solid black; 
                               background-color:lightgray; 
                               border-radius:50%; 
                               width:30px; 
                               height:30px; 
                               font-size:16px;
                               color:black;
                               cursor:pointer;
                               margin-right:10px;">
                    -
                </button>

                <input id="quantity" type="number" value="1" min="1"
                       style="width:50px; 
                              text-align:center; 
                              padding:4px; 
                              border:1px solid #ccc; 
                              border-radius:6px; 
                              font-size:14px; 
                              margin-right:10px;">
                
                <button onclick="changeQty(1)" 
                        style="border:1px solid black; 
                               background-color:lightgray; 
                               border-radius:50%; 
                               width:30px; 
                               height:30px; 
                               font-size:16px;
                               color:black;
                               cursor:pointer;">
                    +
                </button>
            </div>
        </div>

        <!-- Add to Cart button -->
        <?php
            include "../components/buttonTemplate.php";
            echo createButton(40, 300, "Add to cart","add-to-cart-btn");
        ?>
    </div>
</div>

<script>
function changeQty(change) {
    let qtyInput = document.getElementById("quantity");
    let current = parseInt(qtyInput.value) || 1;
    let newValue = current + change;
    if (newValue < 1) newValue = 1; // prevent going below 1
    qtyInput.value = newValue;
}

// Toggle heart button
const heartBtn = document.getElementById("heartBtn");
heartBtn.addEventListener("click", () => {
    if (heartBtn.style.backgroundColor === "rgb(34, 51, 60)") {
        // back to default
        heartBtn.style.backgroundColor = "transparent";
        heartBtn.style.color = "black";
    } else {
        // clicked state
        heartBtn.style.backgroundColor = "#22333c";
        heartBtn.style.color = "white";
    }
});
</script>
