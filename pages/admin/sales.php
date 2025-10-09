

<style>
  :root {
    --bg-light: #f5f1eb;
    --primary: #8b6f47;
    --secondary: #d2b48c;
    --accent: #a67c52;
    --text-dark: #3e2f1c;
    --white: #fff;
  }



  h2 {
    color: var(--primary);
    margin-bottom: 20px;
  }

  /* Filters */
  .filters {
    background-color: var(--white);
    padding: 15px 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .filter-group label {
    font-weight: 600;
  }

  select, input[type="date"] {
    padding: 7px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 14px;
  }

  /* Table */
  table {
    width: 100%;
    border-collapse: collapse;
    background-color: var(--white);
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  th, td {
    padding: 12px;
    text-align: left;
  }

  th {
    background-color: var(--primary);
    color: var(--white);
  }

  tr:nth-child(even) {
    background-color: #f9f6f1;
  }

  tr:hover {
    background-color: #f1e9df;
  }

  .actions button {
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 600;
  }

  .view-btn {
    background-color: var(--secondary);
    color: var(--text-dark);
  }

  .update-btn {
    background-color: var(--accent);
    color: var(--white);
  }

  .cancel-btn {
    background-color: #b84a39;
    color: var(--white);
  }

  /* Chart Section */
  .chart-section {
    margin-top: 40px;
    background-color: var(--white);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  .chart-container {
    display: flex;
    justify-content: space-around;
    flex-wrap: wrap;
    gap: 30px;
  }

  canvas {
    max-width: 600px;
    height: 300px;
  }

  .export-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 15px;
  }

  .export-buttons button {
    padding: 8px 14px;
    background-color: var(--primary);
    color: var(--white);
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
  }

  .export-buttons button:hover {
    background-color: var(--accent);
  }

  /* Modal */
  .modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
  }

  .modal-content {
    background: var(--white);
    padding: 20px;
    border-radius: 10px;
    width: 400px;
    position: relative;
  }

  .modal-content h3 {
    color: var(--primary);
    margin-bottom: 10px;
  }

  .close-btn {
    position: absolute;
    top: 10px; right: 10px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
  }
</style>
</head>
<body>

<h2>Sales Management</h2>

<!-- Filters -->
<div class="filters">
  <div class="filter-group">
    <label>Status:</label>
    <select id="statusFilter">
      <option value="All">All</option>
      <option value="Pending">Pending</option>
      <option value="Delivered">Delivered</option>
      <option value="Cancelled">Cancelled</option>
    </select>
  </div>

  <div class="filter-group">
    <label>Date Range:</label>
    <input type="date" id="fromDate"> - <input type="date" id="toDate">
  </div>

  <div class="filter-group">
    <label>Driver:</label>
    <select id="driverFilter">
      <option value="All">All</option>
      <option value="Driver 1">Driver 1</option>
      <option value="Driver 2">Driver 2</option>
    </select>
  </div>

  <div class="filter-group">
    <label>Payment:</label>
    <select id="paymentFilter">
      <option value="All">All</option>
      <option value="Cash">Cash</option>
      <option value="Card">Card</option>
      <option value="Gcash">Gcash</option>
    </select>
  </div>
</div>

<!-- Orders Table -->
<table>
  <thead>
    <tr>
      <th>Order ID</th>
      <th>Customer</th>
      <th>Driver</th>
      <th>Total</th>
      <th>Status</th>
      <th>Payment</th>
      <th>Date</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody id="ordersTableBody"></tbody>
</table>

<div class="export-buttons">
  <button onclick="alert('Exported to CSV')">Export CSV</button>
  <button onclick="alert('Exported to Excel')">Export Excel</button>
  <button onclick="alert('Exported to PDF')">Export PDF</button>
</div>



<!-- Modal -->
<div class="modal" id="detailsModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✖</button>
    <h3>Order Details</h3>
    <div id="orderDetails"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Fake order data
  const orders = [
    { id: 1001, customer: "Maria Santos", driver: "Driver 1", total: 450, status: "Delivered", payment: "Cash", date: "2025-10-09", items: ["Iced Latte", "Brownie"] },
    { id: 1002, customer: "Juan Dela Cruz", driver: "Driver 2", total: 320, status: "Pending", payment: "Card", date: "2025-10-09", items: ["Burger", "Iced Tea"] },
    { id: 1003, customer: "Liza Dizon", driver: "Driver 1", total: 280, status: "Delivered", payment: "Gcash", date: "2025-10-08", items: ["Cappuccino", "Muffin"] },
    { id: 1004, customer: "Mark Reyes", driver: "Driver 2", total: 400, status: "Cancelled", payment: "Cash", date: "2025-10-07", items: ["Espresso", "Donut"] },
    { id: 1005, customer: "Anna Cruz", driver: "Driver 1", total: 500, status: "Delivered", payment: "Card", date: "2025-10-06", items: ["Mocha", "Cookie"] }
  ];

  const tbody = document.getElementById("ordersTableBody");

  function renderTable(filtered = orders) {
    tbody.innerHTML = "";
    filtered.forEach(order => {
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>#${order.id}</td>
        <td>${order.customer}</td>
        <td>${order.driver}</td>
        <td>₱${order.total.toFixed(2)}</td>
        <td>${order.status}</td>
        <td>${order.payment}</td>
        <td>${order.date}</td>
        <td class="actions">
          <button class="view-btn" onclick="viewDetails(${order.id})">View</button>
          <button class="update-btn">Update</button>
          <button class="cancel-btn">Cancel</button>
        </td>`;
      tbody.appendChild(row);
    });
  }

  function viewDetails(id) {
    const order = orders.find(o => o.id === id);
    const modal = document.getElementById("detailsModal");
    const details = document.getElementById("orderDetails");
    details.innerHTML = `
      <p><strong>Customer:</strong> ${order.customer}</p>
      <p><strong>Driver:</strong> ${order.driver}</p>
      <p><strong>Total:</strong> ₱${order.total}</p>
      <p><strong>Status:</strong> ${order.status}</p>
      <p><strong>Payment:</strong> ${order.payment}</p>
      <p><strong>Date:</strong> ${order.date}</p>
      <p><strong>Items:</strong></p>
      <ul>${order.items.map(i => `<li>${i}</li>`).join('')}</ul>
    `;
    modal.style.display = "flex";
  }

  function closeModal() {
    document.getElementById("detailsModal").style.display = "none";
  }

  // Filters
  document.querySelectorAll('#statusFilter, #driverFilter, #paymentFilter, #fromDate, #toDate')
    .forEach(el => el.addEventListener('change', filterOrders));

  function filterOrders() {
    const status = document.getElementById('statusFilter').value;
    const driver = document.getElementById('driverFilter').value;
    const payment = document.getElementById('paymentFilter').value;
    const from = document.getElementById('fromDate').value;
    const to = document.getElementById('toDate').value;

    const filtered = orders.filter(o => {
      const matchStatus = (status === "All" || o.status === status);
      const matchDriver = (driver === "All" || o.driver === driver);
      const matchPayment = (payment === "All" || o.payment === payment);
      const matchDate = (!from || o.date >= from) && (!to || o.date <= to);
      return matchStatus && matchDriver && matchPayment && matchDate;
    });
    renderTable(filtered);
  }

  renderTable();

  // Charts
  const revCtx = document.getElementById('revenueChart');
  new Chart(revCtx, {
    type: 'bar',
    data: {
      labels: ['Oct 3', 'Oct 4', 'Oct 5', 'Oct 6', 'Oct 7', 'Oct 8', 'Oct 9'],
      datasets: [{
        label: 'Revenue (₱)',
        data: [400, 350, 500, 700, 400, 800, 950],
        backgroundColor: '#a67c52'
      }]
    },
    options: { responsive: true }
  });

  const payCtx = document.getElementById('paymentChart');
  new Chart(payCtx, {
    type: 'pie',
    data: {
      labels: ['Cash', 'Card', 'Gcash'],
      datasets: [{
        data: [3, 2, 1],
        backgroundColor: ['#d2b48c', '#8b6f47', '#a67c52']
      }]
    },
    options: { responsive: true }
  });
</script>

