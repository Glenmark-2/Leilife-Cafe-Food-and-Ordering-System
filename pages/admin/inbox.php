<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (!isset($_SESSION['admin_id'])) {
  header('Location: /leilife/public/index.php');
  exit;
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$messages = $appData->loadInbox($archived);
?>
<div class="container"> 
  <div id="first-row">
  <h2>Inbox</h2>
  <button id="view-archive"><?= $archived ? "View Inbox" : "View Archive" ?></button>
</div>

<div id="search_add">
  <div class="search-bar">
    <input type="text" id="searchInbox" placeholder="Search messages">
  </div>
  <select id="sortInbox">
    <option value="unread">Unread</option>
    <option value="date">Newest</option>
    <option value="type">By Type</option>
  </select>
</div>

<!-- === Table Container === -->
<div id="table-container">
  <div class="table-wrapper">
  <table class="staff-table">
    <thead>
      <tr>
        <th style="width: 15%;">Name</th>
        <th style="width: 15%;">Email</th>
        <th style="width: 20%;">Subject</th>
        <th style="width: 10%;">Type</th>
        <th style="width: 15%;">Date</th>
        <th style="width: 15%;">Actions</th>
      </tr>
    </thead>
    <tbody id="inboxTableBody">
      <?php if ($messages && count($messages) > 0): ?>
        <?php foreach ($messages as $msg): ?>
          <tr id="row-<?= $msg['sender_id'] ?>" class="<?= $msg['status'] == 0 ? 'unread' : '' ?>">
            <td style="width: 15%;"><?= htmlspecialchars($msg['name'] ?? 'Guest') ?></td>
            <td style="width: 15%;"><?= htmlspecialchars($msg['email'] ?? '-') ?></td>
            <td style="width: 20%;"><?= htmlspecialchars($msg['subject'] ?? '(No Subject)') ?></td>
            <td style="width: 15%;"><?= ucfirst(htmlspecialchars($msg['type'])) ?></td>
            <td style="width: 15%;"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
            <td class="actions" style="flex:1">
              <button
                type="button"
                class="editBtn"
                data-message="<?= htmlspecialchars($msg['message']) ?>"
                data-id="<?= $msg['sender_id'] ?>"
                onclick="viewMessage(this)">
                View
              </button>

              <button
                type="button"
                class="archiveBtn"
                data-id="<?= $msg['sender_id'] ?>">
                <img src="public/assests/archive.png" alt="Archive" style="width:24px; height:24px;" title="Archive">
              </button>
            </td>

          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="6" style="text-align:center;">No messages found</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- === Message Details Modal === -->
<div id="messageModal" class="modal">
  <div class="modal-card">
    <div class="modal-header">
      <h2>Message Details</h2>
      <button class="modal-close">&times;</button>
    </div>

    <div class="modal-info">
      <p><strong>Name:</strong> <span id="modalName"></span></p>
      <p><strong>Email:</strong> <span id="modalEmail"></span></p>
      <p><strong>Type:</strong> <span id="modalType"></span></p>
      <p><strong>Date:</strong> <span id="modalDate"></span></p>
      <p><strong>Subject:</strong> <span id="modalSubject"></span></p>

    </div>

    <div class="modal-body">
      <p id="modalMessage"></p>
    </div>

    <div class="modal-buttons">
      <button id="closeMessageBtn">Close</button>
    </div>
  </div>
</div>

</div>







<script>
  const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? "https" : "http")
                      . "://$_SERVER[HTTP_HOST]/leilife/", "/") ?>/";


  const modal = document.getElementById("messageModal");
  const closeBtn = document.querySelector(".close-btn");
  const modalMsg = document.getElementById("modalMessage");

function viewMessage(btn) {
  const id = btn.getAttribute("data-id");
  
  // get data attributes from button row
  const row = document.querySelector(`#row-${id}`);
  const name = row.cells[0].textContent;
  const email = row.cells[1].textContent;
  const subject = row.cells[2].textContent;
  const type = row.cells[3].textContent;
  const date = row.cells[4].textContent;
  const message = btn.getAttribute("data-message");

  // populate modal
  document.getElementById("modalName").textContent = name;
  document.getElementById("modalEmail").textContent = email;
  document.getElementById("modalSubject").textContent = subject;
  document.getElementById("modalType").textContent = type;
  document.getElementById("modalDate").textContent = date;
  document.getElementById("modalMessage").textContent = message;

  // show modal
  modal.style.display = "flex";

  // mark message as read
  fetch(BASE_URL + "backend/admin/archive_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `mark_read=1&id=${id}`
    })
    .then(() => {
      row.classList.remove("unread");
    })
    .catch(err => console.error("Fetch error:", err));
}

// Close modal
document.querySelector(".modal-close").onclick = () => modal.style.display = "none";
document.getElementById("closeMessageBtn").onclick = () => modal.style.display = "none";
window.onclick = (e) => { if(e.target == modal) modal.style.display = "none"; };


  // Search
  document.getElementById("searchInbox").addEventListener("input", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#inboxTableBody tr").forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
  });

  // Sort
  document.getElementById("sortInbox").addEventListener("change", function() {
    const sortBy = this.value;
    const tbody = document.getElementById("inboxTableBody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((a, b) => {
      if (sortBy === "unread") {
        return b.classList.contains("unread") - a.classList.contains("unread");
      }
      if (sortBy === "date") {
        return new Date(b.cells[4].textContent) - new Date(a.cells[4].textContent);
      }
      if (sortBy === "type") {
        return a.cells[3].textContent.localeCompare(b.cells[3].textContent);
      }
      return 0;
    });

    rows.forEach(row => tbody.appendChild(row));
  });

  // View Archive toggle
  document.getElementById("view-archive").addEventListener("click", () => {
    const url = new URL(window.location.href);
    url.searchParams.set("archived", <?= $archived ? '0' : '1' ?>);
    window.location.href = url.toString();
  });

  // Archive toggle
document.querySelectorAll(".archiveBtn").forEach(btn => {
  btn.addEventListener("click", () => {
    const id = btn.getAttribute("data-id");

    fetch(BASE_URL + "backend/admin/archive_message.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `toggle_archive=${id}`
    })
    .then(res => res.text())
    .then(data => {
      console.log("Archive response:", data);
      // Remove row from table
      document.querySelector(`#row-${id}`)?.remove();
    })
    .catch(err => console.error("Fetch error:", err));
  });
});

</script>