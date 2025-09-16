<?php
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);
$messages = $appData->loadInbox();


?>

<div id="first-row">
  <h2>Inbox</h2>
</div>



<div id="box">
  <!-- Search bar -->
  <div class="search-container">
    <input type="text" id="searchInbox" placeholder="Search messages...">
    <select id="sortInbox">
      <option value="unread">Unread</option>
      <option value="date">Newest</option>
      <option value="type">By Type</option>
    </select>

  </div>

  <!-- Inbox Table -->
  <table class="inbox-table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Subject</th>
        <th>Type</th>
        <th>Date</th>
        <th></th>
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
            <td>
              <button
                type="button"
                class="view-btn"
                data-message="<?= htmlspecialchars($msg['message']) ?>"
                data-id="<?= $msg['sender_id'] ?>"
                onclick="viewMessage(this)">
                View
              </button>

              <form method="POST" action="/leilife/backend/inbox.php" style="display:inline;">
                <input type="hidden" name="delete" value="<?= $msg['sender_id'] ?>">
                <button type="submit" class="delete-btn">Delete</button>
              </form>

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

<!-- Modal for viewing message -->
<div id="messageModal" class="modal">
  <div class="modal-content">
    <span class="close-btn">&times;</span>
    <h2>Message</h2>
    <p id="modalMessage"></p>
  </div>
</div>

<script>
  const modal = document.getElementById("messageModal");
  const closeBtn = document.querySelector(".close-btn");
  const modalMsg = document.getElementById("modalMessage");

  function viewMessage(btn) {
    const msg = btn.getAttribute("data-message");
    const id = btn.getAttribute("data-id");

    modalMsg.textContent = msg;
    modal.style.display = "flex";

    // Send AJAX request to mark message as read
    fetch("/leilife/backend/inbox.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body: `mark_read=1&id=${id}` //value that are beeing pass 
      })
      .then(res => res.text())
      .then(data => {
        console.log("Server response:", data);
        // Update row visually: remove "unread" class
        document.querySelector(`#row-${id}`)?.classList.remove("unread");
      })
      .catch(err => console.error("Fetch error:", err));
  }


  closeBtn.onclick = () => modal.style.display = "none";
  window.onclick = (e) => {
    if (e.target == modal) modal.style.display = "none";
  }

  // Search filter
  document.getElementById("searchInbox").addEventListener("input", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll("#inboxTableBody tr").forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(filter) ? "" : "none";
    });
  });

  // Sorting function
  document.getElementById("sortInbox").addEventListener("change", function() {
    const sortBy = this.value;
    const tbody = document.getElementById("inboxTableBody");
    const rows = Array.from(tbody.querySelectorAll("tr"));

    rows.sort((a, b) => {
      if (sortBy === "unread") {
        // unread messages (class="unread") go first
        return b.classList.contains("unread") - a.classList.contains("unread");
      }
      if (sortBy === "date") {
        // compare dates (5th column = created_at)
        const dateA = new Date(a.cells[4].textContent.trim());
        const dateB = new Date(b.cells[4].textContent.trim());
        return dateB - dateA; // newest first
      }
      if (sortBy === "type") {
        // sort alphabetically by type column (4th column)
        return a.cells[3].textContent.localeCompare(b.cells[3].textContent);
      }
      return 0;
    });

    // Re-append rows in new order
    rows.forEach(row => tbody.appendChild(row));
  });
</script>