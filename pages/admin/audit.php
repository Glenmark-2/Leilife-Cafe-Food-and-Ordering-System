<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Audit / Activity Logs</title>
  <style>
    /* === GENERAL LAYOUT === */
    .page-container {
      padding: 20px;
      font-family: 'Poppins', sans-serif;
      background-color: #f9fafb;
      color: #333;
    }

    h2 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #222;
    }

    /* === FILTERS BOX === */
    .filters {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      background: #fff;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      margin-bottom: 20px;
      align-items: center;
    }

    .filters label {
      font-weight: 500;
      margin-right: 5px;
    }

    .filters select,
    .filters input[type="date"],
    .filters input[type="text"] {
      padding: 8px 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      min-width: 160px;
      outline: none;
    }

    .filters button {
      background: #007bff;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
    }

    .filters button:hover {
      background: #0056b3;
    }

    /* === TABLE === */
    .table-container {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      overflow: hidden;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #007bff;
      color: #fff;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    tbody tr:hover {
      background: #f2f6ff;
    }

    .status-badge {
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 13px;
      color: #fff;
    }

    .status-success { background: #28a745; }
    .status-failed { background: #dc3545; }
    .status-warning { background: #ffc107; color: #333; }

    /* === EXPORT BUTTONS === */
    .export-section {
      margin-top: 15px;
      display: flex;
      gap: 10px;
    }

    .export-section button {
      padding: 8px 15px;
      border: none;
      border-radius: 6px;
      background: #28a745;
      color: #fff;
      cursor: pointer;
      transition: 0.3s;
    }

    .export-section button:hover {
      background: #1e7e34;
    }

    /* === ALERT BOX === */
    .alert-box {
      margin-top: 20px;
      background: #fff3cd;
      padding: 15px;
      border-radius: 8px;
      border-left: 6px solid #ffcc00;
    }

    .alert-box h4 {
      margin-bottom: 8px;
      color: #856404;
    }

  </style>
</head>
<body>

<div class="page-container">
  <h2>Audit / Activity Logs</h2>

  <!-- === FILTERS === -->
  <div class="filters">
    <div>
      <label for="userFilter">User:</label>
      <select id="userFilter">
        <option value="">All</option>
        <option value="Admin1">Admin1</option>
        <option value="Admin2">Admin2</option>
        <option value="Driver1">Driver1</option>
      </select>
    </div>

    <div>
      <label for="actionFilter">Action Type:</label>
      <select id="actionFilter">
        <option value="">All</option>
        <option value="Login">Login</option>
        <option value="Delete">Delete</option>
        <option value="Update">Update</option>
      </select>
    </div>

    <div>
      <label for="dateFilter">Date:</label>
      <input type="date" id="dateFilter">
    </div>

    <div>
      <label for="searchInput">Search:</label>
      <input type="text" id="searchInput" placeholder="Search logs...">
    </div>

    <button onclick="applyFilters()">Apply</button>
    <button onclick="resetFilters()">Reset</button>
  </div>

  <!-- === TABLE === -->
  <div class="table-container">
    <table id="logsTable">
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Target</th>
          <th>Status</th>
          <th>Date/Time</th>
        </tr>
      </thead>
      <tbody id="logTableBody"></tbody>
    </table>
  </div>

  <!-- === EXPORT BUTTONS === -->
  <div class="export-section">
    <button>Export CSV</button>
    <button>Export Excel</button>
    <button>Export PDF</button>
  </div>

  <!-- === ALERTS === -->
  <div class="alert-box">
    <h4>Suspicious Activity Alerts:</h4>
    <ul id="alertList">
      <li>⚠️ Failed login attempt by Admin2 on 2025-10-09 09:10 AM</li>
      <li>⚠️ Product #23 deleted by Driver1 unexpectedly</li>
    </ul>
  </div>
</div>

<script>
  // === FAKE DATA ===
  const logs = [
    { user: "Admin1", action: "Login", target: "-", status: "Success", date: "2025-10-09 08:45 AM" },
    { user: "Driver1", action: "Delete", target: "Product #23", status: "Failed", date: "2025-10-09 09:10 AM" },
    { user: "Admin2", action: "Update", target: "Order #1023", status: "Success", date: "2025-10-08 04:32 PM" },
    { user: "Admin1", action: "Delete", target: "User #12", status: "Success", date: "2025-10-08 01:05 PM" },
    { user: "Driver1", action: "Login", target: "-", status: "Failed", date: "2025-10-07 07:50 AM" },
  ];

  function loadLogs(data) {
    const tbody = document.getElementById("logTableBody");
    tbody.innerHTML = "";
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No logs found</td></tr>`;
      return;
    }
    data.forEach(log => {
      const statusClass =
        log.status === "Success" ? "status-success" :
        log.status === "Failed" ? "status-failed" : "status-warning";

      const row = `
        <tr>
          <td>${log.user}</td>
          <td>${log.action}</td>
          <td>${log.target}</td>
          <td><span class="status-badge ${statusClass}">${log.status}</span></td>
          <td>${log.date}</td>
        </tr>`;
      tbody.innerHTML += row;
    });
  }

  function applyFilters() {
    const user = document.getElementById("userFilter").value;
    const action = document.getElementById("actionFilter").value;
    const date = document.getElementById("dateFilter").value;
    const search = document.getElementById("searchInput").value.toLowerCase();

    const filtered = logs.filter(log => {
      return (
        (user === "" || log.user === user) &&
        (action === "" || log.action === action) &&
        (date === "" || log.date.includes(date)) &&
        (search === "" || Object.values(log).some(v => v.toLowerCase().includes(search)))
      );
    });
    loadLogs(filtered);
  }

  function resetFilters() {
    document.getElementById("userFilter").value = "";
    document.getElementById("actionFilter").value = "";
    document.getElementById("dateFilter").value = "";
    document.getElementById("searchInput").value = "";
    loadLogs(logs);
  }

  // Initialize table
  loadLogs(logs);
</script>

</body>
</html>
