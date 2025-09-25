<?php
// Mockup rider and orders
$rider = [
    "name" => "Tony Rider",
    "shift" => "9:00 AM - 5:00 PM"
];

$orders = [
    ["id" => "001", "customer" => "John Smith", "address" => "123 Main St", "number" => "09123456789", "status" => "Preparing", "items" => ["2x Burger", "1x Fries", "1x Soda"]],
    ["id" => "002", "customer" => "Jane Doe", "address" => "456 Oak Ave", "number" => "09129876543", "status" => "Pending", "items" => ["1x Pizza", "2x Iced Tea"]],
    ["id" => "003", "customer" => "Mike Chan", "address" => "789 Pine Rd", "number" => "09124561234", "status" => "Completed", "items" => ["3x Pasta", "1x Garlic Bread"]],
    ["id" => "004", "customer" => "Alice Cruz", "address" => "101 Elm St", "number" => "09120001111", "status" => "Cancelled", "items" => ["1x Salad"]],
    ["id" => "005", "customer" => "David Lee", "address" => "22 Palm Blvd", "number" => "09128889999", "status" => "Pending", "items" => ["2x Chicken Meal", "1x Coke"]]
];

// Count order stats
$stats = ["Pending"=>0,"Preparing"=>0,"Completed"=>0,"Cancelled"=>0];
foreach ($orders as $o) { $stats[$o["status"]]++; }
?>
<style>
body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }

.app-header {
  background: linear-gradient(135deg, #4facfe, #00f2fe);
  color: #fff; padding: 15px;
  display: flex; justify-content: space-between; align-items: center;
  position: sticky; top: 0; z-index: 100;
  box-shadow: 0 3px 6px rgba(0,0,0,0.1);
}
.app-header h1 { margin: 0; font-size: 1.3rem; }
.app-header .profile { font-size: 1.5rem; }

.container { max-width: 1200px; margin: auto; padding: 20px; }

/* Rider Info */
.rider-card { background: #fff; padding: 15px; border-radius: 14px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.08); margin-bottom: 20px; }
.rider-card h2 { font-size: 1.2rem; margin: 0; }
.rider-card p { font-size: 0.9rem; color: #777; }

/* Summary */
.summary { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; margin-bottom: 25px; }
.summary .box { border-radius: 14px; padding: 20px 12px; text-align: center; color: #fff; font-weight: 600;
  box-shadow: 0 3px 8px rgba(0,0,0,0.15); }
.summary .box span { display: block; font-size: 0.9rem; }
.summary .box h2 { margin: 0; font-size: 1.4rem; }
.summary .pending { background: linear-gradient(135deg, #667db6, #0082c8); }
.summary .preparing { background: linear-gradient(135deg, #f7971e, #ffd200); }
.summary .completed { background: linear-gradient(135deg, #56ab2f, #a8e063); }
.summary .cancelled { background: linear-gradient(135deg, #cb2d3e, #ef473a); }

/* Orders grid */
.orders-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.orders-header h3 { margin: 0; font-size: 1rem; }
.orders-header select { padding: 6px; font-size: 0.9rem; border-radius: 6px; border: 1px solid #ddd; }

#orderList {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 900px) {
  #orderList { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  #orderList { grid-template-columns: 1fr; }
}

.order-card { background: #fff; padding: 14px; border-radius: 12px;
  box-shadow: 0 3px 8px rgba(0,0,0,0.08); transition: 0.2s; }
.order-card:hover { transform: translateY(-3px); }
.order-header { display: flex; justify-content: space-between; align-items: center; }
.order-header h4 { margin: 0; font-size: 1rem; }
.badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; color: #fff; font-weight: 600; }
.badge.pending { background: #f39c12; }
.badge.preparing { background: #2980b9; }
.badge.completed { background: #27ae60; }
.badge.cancelled { background: #c0392b; }
.order-card p { margin: 6px 0; font-size: 0.85rem; color: #555; }

.btn-view { display: inline-block; margin-top: 10px; padding: 8px 14px;
  background: linear-gradient(135deg, #4facfe, #00f2fe); color: #fff;
  font-size: 0.85rem; border-radius: 8px; text-decoration: none;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
.btn-view:hover { opacity: 0.9; }

/* Modal */
.modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  display: none; background: rgba(0,0,0,0.6);
  justify-content: center; align-items: center; z-index: 200; }
.modal-content { background: #fff; width: 90%; max-width: 600px;
  border-radius: 12px; padding: 20px; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.modal-header { display: flex; justify-content: space-between; align-items: center; }
.modal-header h3 { margin: 0; }
.close-btn { font-size: 1.4rem; cursor: pointer; }
.order-items { margin: 10px 0; }
.order-items li { font-size: 0.9rem; margin: 5px 0; }
.contact-btn, .complete-btn {
  display: block; margin-top: 12px; padding: 10px; text-align: center;
  border-radius: 10px; text-decoration: none; font-weight: bold; }
.contact-btn { background: linear-gradient(135deg, #00c6ff, #0072ff); color: #fff; }
.complete-btn { background: linear-gradient(135deg, #56ab2f, #a8e063); color: #fff; }


@media screen and (max-width: 600px) {
  .container {
    grid-template-columns: 1fr;
    padding: 15px;
  }
  .summary {
    grid-template-columns: repeat(2,1fr);
  }
  .modal-content {
    border-radius: 16px 16px 0 0;
    width: 100%;
    max-width: 480px;
    animation: slideUp 0.3s ease;
  }
  @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
}
</style>



<div class="container">
  <div class="rider-card">
    <h2><?= $rider["name"] ?></h2>
    <p>Shift: <?= $rider["shift"] ?></p>
  </div>

  <div class="summary">
    <div class="box pending"><span>Pending</span><h2><?= $stats["Pending"] ?></h2></div>
    <div class="box preparing"><span>Preparing</span><h2><?= $stats["Preparing"] ?></h2></div>
    <div class="box completed"><span>Completed</span><h2><?= $stats["Completed"] ?></h2></div>
    <div class="box cancelled"><span>Cancelled</span><h2><?= $stats["Cancelled"] ?></h2></div>
  </div>

  <div class="orders">
    <div class="orders-header">
      <h3>Recent Orders</h3>
      <select id="sortSelect">
        <option value="id">Sort by: Order</option>
        <option value="status">Sort by: Status</option>
      </select>
    </div>
    
    <div id="orderList">
      <?php foreach ($orders as $o): ?>
        <div class="order-card <?= strtolower($o["status"]) ?>" 
             data-id="<?= $o["id"] ?>"
             data-customer="<?= $o["customer"] ?>"
             data-address="<?= $o["address"] ?>"
             data-number="<?= $o["number"] ?>"
             data-status="<?= $o["status"] ?>"
             data-items='<?= json_encode($o["items"]) ?>'>
          <div class="order-header">
            <h4>#<?= $o["id"] ?></h4>
            <span class="badge <?= strtolower($o["status"]) ?>"><?= $o["status"] ?></span>
          </div>
          <p><b>👤</b> <?= $o["customer"] ?></p>
          <p><b>📍</b> <?= $o["address"] ?></p>
          <p><b>📞</b> <?= $o["number"] ?></p>
          <a href="#" class="btn-view">View</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="modal" id="orderModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Order Details</h3>
      <span class="close-btn">&times;</span>
    </div>
    <div id="modalBody"></div>
  </div>
</div>

<script>
let currentCard = null;

document.querySelectorAll(".btn-view").forEach(btn=>{
  btn.addEventListener("click", function(e){
    e.preventDefault();
    let card = this.closest(".order-card");
    currentCard = card;
    let id = card.dataset.id;
    let customer = card.dataset.customer;
    let address = card.dataset.address;
    let number = card.dataset.number;
    let status = card.dataset.status;
    let items = JSON.parse(card.dataset.items);

    let html = `
      <p><b>Order #:</b> ${id}</p>
      <p><b>Customer:</b> ${customer}</p>
      <p><b>Address:</b> ${address}</p>
      <p><b>Contact:</b> ${number}</p>
      <p><b>Status:</b> <span class="badge ${status.toLowerCase()}">${status}</span></p>
      <h4>Items:</h4>
      <ul class="order-items">${items.map(i=>`<li>${i}</li>`).join("")}</ul>
    `;

    if(status !== "Completed"){
      html += `<a href="tel:${number}" class="contact-btn">📞 Contact Customer</a>`;
      if(status === "Pending" || status === "Preparing"){
        html += `<a href="#" class="complete-btn" id="completeBtn">✅ Mark as Completed</a>`;
      }
    }

    document.getElementById("modalBody").innerHTML = html;
    document.getElementById("orderModal").style.display = "flex";

    let completeBtn = document.getElementById("completeBtn");
    if(completeBtn){
      completeBtn.onclick = function(ev){
        ev.preventDefault();
        currentCard.dataset.status = "Completed";
        currentCard.querySelector(".badge").innerText = "Completed";
        currentCard.querySelector(".badge").className = "badge completed";
        document.getElementById("orderModal").style.display="none";
      }
    }
  });
});

document.querySelector(".close-btn").onclick = ()=>{ document.getElementById("orderModal").style.display="none"; };
window.onclick = (e)=>{ if(e.target.id==="orderModal"){ document.getElementById("orderModal").style.display="none"; } };

// Sorting
document.getElementById("sortSelect").addEventListener("change", function() {
  let orderList = document.getElementById("orderList");
  let cards = Array.from(orderList.getElementsByClassName("order-card"));
  if (this.value === "status") {
    cards.sort((a,b) => a.dataset.status.localeCompare(b.dataset.status));
  } else {
    cards.sort((a,b) => a.dataset.id.localeCompare(b.dataset.id));
  }
  orderList.innerHTML = "";
  cards.forEach(c => orderList.appendChild(c));
});
</script>
