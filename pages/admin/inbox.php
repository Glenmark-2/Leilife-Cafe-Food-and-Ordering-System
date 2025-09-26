<?php
session_start();
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';


if (!isset($_SESSION['admin_id'])) {
  header('Location: /leilife/pages/admin/login-x9P2kL7zQ.php');
  exit;
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$messages = $appData->loadInbox($archived);

?>

<!-- === First Row (Title + Archive Button) === -->
<div id="first-row">
  <h2>Inbox</h2>
  <button id="view-archive"><?= $archived ? "View Inbox" : "View Archive" ?></button>
</div>

<!-- === Search + Sort Controls === -->
<div id="search_add">
  <div class="search-bar">
    <input type="text" id="searchInbox" placeholder="Search messages...">
  </div>
  <select id="sortInbox">
    <option value="unread">Unread</option>
    <option value="date">Newest</option>
    <option value="mail">Mail</option>
    <option value="feedback">Feedback</option>
  </select>
</div>

<!-- === Table Container === -->
<div id="table-container">
  <table class="staff-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Type</th>
        <th>Date</th>
        <th style="text-align:center;">Actions</th>
      </tr>
    </thead>
    <tbody id="inboxTableBody">
      <?php if ($messages && count($messages) > 0): ?>
        <?php foreach ($messages as $msg): ?>
          <tr id="row-<?= $msg['sender_id'] ?>" class="<?= $msg['status'] == 0 ? 'unread' : '' ?>">
            <td><?= htmlspecialchars($msg['name'] ?? 'Guest') ?></td>
            <td><?= htmlspecialchars($msg['email'] ?? '-') ?></td>
            <td><?= htmlspecialchars($msg['subject'] ?? '(No Subject)') ?></td>
            <td><?= ucfirst(htmlspecialchars($msg['type'])) ?></td>
            <td><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
            <td class="actions">
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
                <img src="public/assests/archive.png" alt="Archive" style="width:24px; height:24px;">
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

<!-- === Modal for Viewing Message === -->
<div id="messageModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <h2>Message</h2>
    <p id="modalMessage"></p>
  </div>
</div>


<script>
  const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? "https" : "http")
                      . "://$_SERVER[HTTP_HOST]/leilife/", "/") ?>/";
  console.log("BASE_URL = ", BASE_URL);


  // const BASE_URL = "http://localhost/Leilife/";

  const modal = document.getElementById("messageModal");
  const closeBtn = document.querySelector(".close-btn");
  const modalMsg = document.getElementById("modalMessage");

  function viewMessage(btn) {
    const msg = btn.getAttribute("data-message");
    const id = btn.getAttribute("data-id");

    modalMsg.textContent = msg;
    modal.style.display = "flex";

    fetch(BASE_URL + "backend/admin/archive_message.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `mark_read=1&id=${id}`
      })
      .then(res => res.text())
      .then(() => {
        document.querySelector(`#row-${id}`)?.classList.remove("unread");
      })
      .catch(err => console.error("Fetch error:", err));
  }

  closeBtn.onclick = () => modal.style.display = "none";
  window.onclick = (e) => {
    if (e.target == modal) modal.style.display = "none";
  };

  // Search
  document.getElementById("searchInbox").addEventListener("input", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#inboxTableBody tr").forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
  });

  // Sort
  // Sort
  document.getElementById("sortInbox").addEventListener("change", function() {
    const sortBy = this.value;
    const tbody = document.getElementById("inboxTableBody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((a, b) => {
      if (sortBy === "unread") {
        // Put unread on top
        return b.classList.contains("unread") - a.classList.contains("unread");
      }
      if (sortBy === "date") {
        // Newest first
        return new Date(b.cells[4].textContent) - new Date(a.cells[4].textContent);
      }
      if (sortBy === "mail" || sortBy === "feedback") {
        // Filter by type
        const typeA = a.cells[3].textContent.toLowerCase();
        const typeB = b.cells[3].textContent.toLowerCase();
        if (typeA === sortBy && typeB !== sortBy) return -1;
        if (typeA !== sortBy && typeB === sortBy) return 1;
        return 0;
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
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
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