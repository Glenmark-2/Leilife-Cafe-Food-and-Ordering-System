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
$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
  $showWelcome = true;
  unset($_SESSION['show_welcome']);
}

$appData = new AppData($pdo);
$archived = $_GET['archived'] ?? 0;
$messages = $appData->loadMessagesToday($archived);
$topProducts = $appData->topProducts();
$orderCounts = $appData->getTodayOrdersByStatus();
$totalActiveAdmin = $appData->activeAdmin();
$totalActiveDriver = $appData->activeDriver();

?>

<div class="surface">
<div id="first-row">
  <button class="hamburger" id="hamburger" onclick="toggleSidebar()">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <h2>Dashboard</h2>
</div>

  <div class="stats-row" style="margin-bottom:14px;">
    <div class="stat stat--pending">
      <div>
        <p>Pending</p>
        <h3 id="pending-count"><?= $orderCounts['pending'] ?></h3>
      </div>
    </div>
    <div class="stat stat--preparing">
      <div>
        <p>Preparing</p>
        <h3 id="preparing-count"><?= $orderCounts['preparing'] ?></h3>
      </div>
    </div>
    <div class="stat stat--ready">
      <div>
        <p>Ready to Deliver</p>
        <h3 id="ready-count"><?= $orderCounts['ready_for_delivery'] ?></h3>
      </div>
    </div>
    <div class="stat stat--delivered">
      <div>
        <p>Delivered</p>
        <h3 id="delivered-count"><?= $orderCounts['delivered'] ?></h3>
      </div>
    </div>
    <div class="stat stat--cancelled">
      <div>
        <p>Cancelled</p>
        <h3 id="cancelled-count"><?= $orderCounts['cancelled'] ?></h3>
      </div>
    </div>
    <div class="stat stat--admin">
      <div>
        <p>Active Admins</p>
        <h3><?= $totalActiveAdmin ?></h3>
      </div>
    </div>
    <div class="stat stat--driver">
      <div>
        <p>Active Drivers</p>
        <h3><?= $totalActiveDriver ?></h3>
      </div>
    </div>
  </div>

  <div style="margin-bottom:12px; display:flex; align-items:center; gap:8px;">
    <input type="text" id="orderSearch" placeholder="Enter order number" style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; flex:1;">
  </div>

  <!-- Recent orders -->
  <div id="third-row" class="recent" aria-live="polite"> <div class="recent-top"> <p><strong>Recent Orders</strong></p> <div class="sort-dropdown" style="margin-left:auto;"> <label for="sort">Sort by:</label> <select id="sort" style="margin-left:6px;"> <option value="order_date">Order Date</option> <option value="status">Status</option> <option value="total">Total</option> <option value="pickup">Pick up</option> <option value="home_delivery">Home Delivery</option> </select> </div> </div> <div id="table" role="region" aria-label="Recent Orders Table"> <div id="table-title" aria-hidden="true"> <p class="col-order">Order #</p> <p class="col-customer">Customer</p> <p class="col-amount">Amount</p> <p class="col-items">Item</p> <p class="col-status">Status</p> <p class="col-payment">Payment Status</p> <p class="col-method">Method</p> <p class="col-receipt">Download Receipt</p> </div> <div id="table-body"> <!-- rows injected by JS --> </div> </div> <!-- pagination -->
   <div id="table-container" style="margin-top:12px;">
  <p style="font-weight:700; margin-bottom:8px;">Recent Messages</p>

  <div class="table-wrapper">
    <table class="staff-table" aria-live="polite">
      <thead>
        <tr style="background:#fbfdff;">
          <th style="padding:10px; text-align:left;">Name</th>
          <th style="padding:10px; text-align:left;">Email</th>
          <th style="padding:10px; text-align:left;">Subject</th>
          <th style="padding:10px; text-align:left;">Type</th>
          <th style="padding:10px; text-align:left;">Date</th>
          <th style="text-align:center; padding:10px;">Actions</th>
        </tr>
      </thead>
      <tbody id="inboxTableBody">
        <?php if ($messages && count($messages) > 0): ?>
          <?php foreach ($messages as $msg): ?>
            <tr id="row-<?= $msg['sender_id'] ?>" class="<?= $msg['status'] == 0 ? 'unread' : '' ?>">
              <td style="padding:10px;"><?= htmlspecialchars($msg['name'] ?? 'Guest') ?></td>
              <td style="padding:10px;"><?= htmlspecialchars($msg['email'] ?? '-') ?></td>
              <td style="padding:10px;"><?= htmlspecialchars($msg['subject'] ?? '(No Subject)') ?></td>
              <td style="padding:10px;"><?= ucfirst(htmlspecialchars($msg['type'])) ?></td>
              <td style="padding:10px;"><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
              <td class="actions" style="padding:10px; text-align:center;">
                <button type="button" class="editBtn"
                        data-message="<?= htmlspecialchars($msg['message']) ?>"
                        data-id="<?= $msg['sender_id'] ?>">View</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center; padding:18px;">No messages found</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

  <div id="messageModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none; position:fixed; inset:0; justify-content:center; align-items:center; z-index:60;">
    <div class="modal-content" role="document" style="background:var(--card); padding:18px; border-radius:10px; width:90%; max-width:600px; border:1px solid var(--surface-border);">
      <span class="close-btn" aria-label="Close" style="float:right; cursor:pointer; font-size:22px;">&times;</span>
      <h2 style="margin-top:0;">Message</h2>
      <p id="modalMessage" style="white-space:pre-wrap; color:var(--muted);"></p>
    </div>
  </div>

  <?php if ($showWelcome): ?>
    <div id="welcomeModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true" style="display:none; position:fixed; inset:0; justify-content:center; align-items:center; z-index:60;">
      <div class="modal-content" style="background:var(--card); padding:18px; border-radius:10px; width:90%; max-width:420px; border:1px solid var(--surface-border);">
        <span class="close" onclick="closeWelcome()" aria-hidden="true" style="float:right; cursor:pointer;">&times;</span>
        <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?>!</h2>
        <p style="color:var(--muted)">You're now logged in.</p>
        <button onclick="closeWelcome()" style="margin-top:12px; padding:8px 12px; border-radius:10px; border:none; background:var(--accent); color:white; cursor:pointer;">Continue</button>
      </div>
    </div>
  <?php endif; ?>
</div>

<div id="notif-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
  background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:9999;">
  <div class="notif-content" style="background:white; padding:20px; border-radius:12px; text-align:center; max-width:300px;">
  </div>
</div>
</div>

<script>
const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://$_SERVER[HTTP_HOST]/Leilife/', '/') ?>/";
let currentPage = 1;
const rowsPerPage = 10;
let allOrders = [];
let currentView = 'active';

document.addEventListener('DOMContentLoaded', () => {
  // keep your welcome modal logic unchanged
  if (<?= $showWelcome ? 'true' : 'false' ?>) {
    const welcomeModal = document.getElementById('welcomeModal');
    if (welcomeModal) { welcomeModal.style.display = 'flex'; window.closeWelcome = () => welcomeModal.style.display = 'none'; }
  }

  initInboxModal();

  const sortEl = document.getElementById('sort');
  const searchEl = document.getElementById('orderSearch');

  if (sortEl) {
    sortEl.addEventListener('change', () => {
      currentPage = 1;
      loadOrders(sortEl.value, searchEl ? searchEl.value.trim() : '');
    });
  }

  if (searchEl) {
    let t;
    searchEl.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        currentPage = 1;
        loadOrders(sortEl ? sortEl.value : 'order_date', searchEl.value.trim());
      }, 300);
    });
  }

  loadOrders(sortEl ? sortEl.value : 'order_date', searchEl ? searchEl.value.trim() : '');

  // close status menus when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.status-btn') && !e.target.closest('.status-menu')) {
      document.querySelectorAll('.status-menu.visible').forEach(m => m.classList.remove('visible'));
    }
  });

  periodic refresh if you want
  setInterval(() => {
    const s = document.getElementById('sort');
    loadOrders(s ? s.value : 'order_date', document.getElementById('orderSearch')?.value || '');
  }, 60000);
});

/* -------------------------
   Robust loadOrders() (keeps your original structure)
   ------------------------- */
async function loadOrders(sortBy = 'order_date', searchTerm = '') {
  const tableBody = document.getElementById('table-body');
  if (!tableBody) return;
  tableBody.innerHTML = '';

  try {
    const res = await fetch(`/Leilife/backend/admin/get_orders.php?view=${encodeURIComponent(currentView)}&sort=${encodeURIComponent(sortBy)}&order_number=${encodeURIComponent(searchTerm)}`, {cache: 'no-store'});
    const data = await res.json();

    if (!data.success) {
      tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
      updateCounts(0,0,0);
      return;
    }

    const orders = data.orders || [];
    if (orders.length === 0) {
      tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No ${currentView==='active'?'active':'completed'} orders found.</p>`;
      updateCounts(0,0,0);
      return;
    }

    let pending = 0, preparing = 0, ready = 0;

    // Build rows exactly like your original code but with consistent classes
    orders.forEach(order => {
      if (order.status === 'pending') pending++;
      if (order.status === 'preparing') preparing++;
      if (order.status === 'ready_for_delivery') ready++;

      const row = document.createElement('div');
      row.classList.add('table-row');
      row.dataset.orderId = order.order_id;

     row.innerHTML = `
  <p class="col-order">${escapeHtml(order.order_number)}</p>
  <p class="col-customer">${escapeHtml(order.customer_name || 'Unknown User')}</p>
  <p class="col-amount">₱${parseFloat(order.total || 0).toFixed(2)}</p>
  <p class="col-items">${order.items_count}</p>
  <div class="col-status">
    <button class="status-btn" data-id="${order.order_id}" data-status="${escapeAttr(order.status)}">
      <span class="badge s-${escapeCss(order.status)}">${formatStatus(order.status)}</span>
    </button>
    <div class="status-menu" aria-hidden="true">
      ${createStatusOptions(order.status, order.delivery_method)}
    </div>
  </div>
  <p class="col-payment">${escapeHtml(order.payment_status)}</p>
  <p class="col-method">${escapeHtml(order.delivery_method)}</p>
  <div class="col-receipt">
    <button class="dlBtn">
      <img src="/leilife/public/assests/downloads.png" alt="Download" style="width:20px;">
    </button>
  </div>
`;

      tableBody.appendChild(row);

      // download button handler
      const dlBtn = row.querySelector('.dlBtn');
      if (dlBtn) dlBtn.addEventListener('click', (e) => { e.stopPropagation(); downloadReceipt(order.order_number, order.user_id); });

      // expandable row element (inserted after row)
      const expandRow = document.createElement('div');
      expandRow.classList.add('expandable-row');
      expandRow.dataset.orderId = order.order_id;
      expandRow.style.display = 'none';
      tableBody.appendChild(expandRow);

      // click handler for expansion (careful with event targets)
      row.addEventListener('click', async (e) => {
        // ignore clicks inside status button/menu
        if (e.target.closest('.status-btn') || e.target.closest('.status-menu') || e.target.closest('.status-option') || e.target.closest('.dlBtn')) return;

        const wasVisible = expandRow.classList.contains('show');
        // hide all expandables
        document.querySelectorAll('.expandable-row.show').forEach(r => { r.classList.remove('show'); r.style.display = 'none'; });
        if (wasVisible) {
          expandRow.classList.remove('show'); expandRow.style.display = 'none';
          return;
        }

        expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">Loading items...</p>`;
        expandRow.classList.add('show'); expandRow.style.display = 'block';

        // robust fetch and parse
        try {
          const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`, {cache: 'no-store'});
          // Try parse JSON safely
          let itemData;
          try { itemData = await itemsRes.json(); } catch (jsonErr) {
            console.error('get_order_items returned non-JSON:', jsonErr);
            const txt = await itemsRes.text();
            console.warn('Raw response for get_order_items:', txt.substring(0,1000));
            expandRow.innerHTML = `<p style="color:red; padding:10px;">Error loading items (invalid response).</p>`;
            return;
          }

          if (!itemData || !itemData.success || !Array.isArray(itemData.items)) {
            console.warn('get_order_items structure unexpected:', itemData);
            expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">No items found.</p>`;
            return;
          }

          expandRow.innerHTML = renderItemTable(itemData.items);
          attachItemDelegatedListener(expandRow, order.order_id);
        } catch (err) {
          console.error('Error fetching order items:', err);
          expandRow.innerHTML = `<p style="color:red; padding:10px;">Error loading items.</p>`;
        }
      });
    }); // end foreach orders

    // update counts & global store
    updateCounts(pending, preparing, ready);
    allOrders = orders;

    // attach status handlers after DOM exists
    attachStatusListeners();

  } catch (err) {
    console.error('Error loading orders:', err);
    tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
    updateCounts(0,0,0);
  }
}

/* -------------------------
   renderOrders() and pagination code preserved below if you use it elsewhere
   (kept intact from your previous version — not required if you don't call it)
   ------------------------- */

//   function renderOrders() { 
//   const tableBody = document.getElementById('table-body'); 
//   tableBody.innerHTML = ''; 
//   const total = allOrders.length; 
//   const totalPages = Math.max(1, Math.ceil(total / rowsPerPage)); 
//   if (currentPage > totalPages) currentPage = totalPages; 
//   const start = (currentPage - 1) * rowsPerPage; 
//   const slice = allOrders.slice(start, start + rowsPerPage); 
  
//   // create rows
//   slice.forEach(order => { 
//     const row = document.createElement('div'); 
//     row.classList.add('table-row'); 
//     row.dataset.orderId = order.order_id; 
    
//     // build status badge + button
//     const statusBadge = `<span class="badge s-${escapeCss(order.status)}">${formatStatus(order.status)}</span>`; 
//     row.innerHTML = `
//       <div class="col-order"><p>${escapeHtml(order.order_number)}</p></div> 
//       <div class="col-customer"><p>${escapeHtml(order.customer_name || 'Unknown User')}</p></div> 
//       <div class="col-amount"><p id="order-total-${order.order_id}">₱${parseFloat(order.total || 0).toFixed(2)}</p></div> 
//       <div class="col-items"><p style="text-align:left;">${order.items_count ?? 0}</p></div> 
//       <div class="col-status">
//         <div style="display:flex; gap:8px; align-items:center;"> 
//           <button class="status-btn" data-id="${escapeAttr(order.order_id)}" data-status="${escapeAttr(order.status)}">${statusBadge}</button> 
//         </div> 
//         <div class="status-menu" aria-hidden="true"> 
//           ${createStatusOptions(order.status, order.delivery_method)} 
//         </div> 
//       </div> 
//       <div class="col-payment"><p>${escapeHtml(order.payment_status)}</p></div> 
//       <div class="col-method"><p>${escapeHtml(order.delivery_method)}</p></div> 
//       <div class="col-receipt"> 
//         <button class="dlBtn" aria-label="Download Receipt" style="background:transparent; border:0; padding:4px; cursor:pointer;"> 
//           <img src="/leilife/public/assests/downloads.png" alt="Download" style="width:20px;"> 
//         </button> 
//       </div>
//     `; 

//     tableBody.appendChild(row); 

//     // downloadable receipt
//     const dlBtn = row.querySelector('.dlBtn'); 
//     if (dlBtn) { 
//       dlBtn.addEventListener('click', (e) => { 
//         e.stopPropagation(); 
//         downloadReceipt(order.order_number, order.user_id); 
//       }); 
//     } 

//     // expandable area for items
//     const expandRow = document.createElement('div'); 
//     expandRow.classList.add('expandable-row'); 
//     expandRow.dataset.orderId = order.order_id; 
//     expandRow.style.display = 'none'; 
//     tableBody.appendChild(expandRow); 

//     // click to expand row (except when clicking status button or status menu)
//     row.addEventListener('click', async (e) => { 
//       if (e.target.closest('.status-btn') || e.target.closest('.status-menu') || e.target.closest('.status-option')) return; 
//       const wasVisible = expandRow.style.display === 'block'; 
//       document.querySelectorAll('.expandable-row').forEach(r => r.style.display = 'none'); 
//       if (wasVisible) { 
//         expandRow.style.display = 'none'; 
//         return; 
//       } 
//       expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">Loading items...</p>`; 
//       expandRow.style.display = 'block'; 
//       try { 
//         const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`); 
//         const itemData = await itemsRes.json(); 
//         if (itemData.success && Array.isArray(itemData.items)) { 
//           expandRow.innerHTML = renderItemTable(itemData.items); 
//           attachItemDelegatedListener(expandRow, order.order_id); 
//         } else { 
//           expandRow.innerHTML = `<p style="color:var(--muted); padding:10px;">No items found.</p>`; 
//         } 
//       } catch (err) { 
//         expandRow.innerHTML = `<p style="color:${'red'}; padding:10px;">Error loading items</p>`; 
//         console.error(err); 
//       } 
//     }); 
//   }); 
  
//   attachStatusListeners(); 
// } 



/* -------------------------
   Status menu generation & listeners
   ------------------------- */
function createStatusOptions(current, deliveryMethod) {
  let statuses = [];
  const dm = (deliveryMethod || '').toLowerCase();
  if (dm.includes('pickup')) statuses = ['pending','preparing','picked_up','cancelled'];
  else statuses = ['pending','preparing','ready_for_delivery','delivered','cancelled'];
  return statuses.map(s => `<div class="status-option" data-status="${s}">${formatStatus(s)}</div>`).join('');
}

function attachStatusListeners() {
  // Remove duplicate listeners and rebind menu toggle
  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.replaceWith(btn.cloneNode(true));
  });

  document.querySelectorAll('.status-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const menu = btn.parentElement.querySelector('.status-menu');
      if (!menu) return;
      document.querySelectorAll('.status-menu.visible').forEach(m => {
        if (m !== menu) m.classList.remove('visible');
      });
      menu.classList.toggle('visible');
    });
  });

  // Remove duplicate listeners on options
  document.querySelectorAll('.status-option').forEach(opt => {
    opt.replaceWith(opt.cloneNode(true));
  });

  document.querySelectorAll('.status-option').forEach(opt => {
    opt.addEventListener('click', async (e) => {
      e.stopPropagation();
      const menu = opt.closest('.status-menu');
      const btn = menu ? menu.parentElement.querySelector('.status-btn') : null;
      const orderId = btn ? btn.dataset.id : null;
      const newStatus = opt.dataset.status;
      if (!orderId || !newStatus) return;

      const confirmAndDo = async () => {
        // Immediate visual update
        btn.innerHTML = `<span class="badge s-${escapeCss(newStatus)}">${formatStatus(newStatus)}</span>`;
        btn.dataset.status = newStatus;
        menu.classList.remove('visible');

        try {
          const res = await fetch('/Leilife/backend/admin/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: newStatus })
          });
          const data = await res.json();

          if (data.success) {
            console.log('Status updated:', orderId, '→', newStatus);

            // Update expandable items if visible
            const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
            if (expandRow) {
              expandRow.querySelectorAll('.item-status').forEach(sel => {
                sel.value = mapOrderToItemStatus(newStatus);
                sel.dataset.prev = sel.value;
              });
            }

            // Reattach listeners to keep menus working
            attachStatusListeners();

          } else {
            alert('Failed to update status on server.');
            loadOrders(document.getElementById('sort')?.value || 'order_date', document.getElementById('orderSearch')?.value || '');
          }

        } catch (err) {
          console.error('Status update error:', err);
          alert('Network error while updating. Please try again.');
        }
      };

      if (newStatus === 'cancelled') {
        showConfirmModal("Are you sure you want to cancel this order?", confirmAndDo);
      } else {
        confirmAndDo();
      }
    });
  });

  // Close any open menu when clicking outside
  document.addEventListener('click', () => {
    document.querySelectorAll('.status-menu.visible').forEach(m => m.classList.remove('visible'));
  });
}


/* -------------------------
   ITEM table render & item-status handling (kept near-original)
   ------------------------- */
function renderItemTable(items) {
  return `
    <table class="item-table" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left; padding:8px;">Item</th>
          <th style="text-align:center; padding:8px;">Qty</th>
          <th style="text-align:center; padding:8px;">Status</th>
        </tr>
      </thead>
      <tbody>
        ${items.map(i => {
          const st = (i.status || 'pending').toLowerCase();
          return `
            <tr data-item-id="${i.order_item_id}" class="status-${escapeCss(st)}">
              <td style="padding:8px;">${escapeHtml(i.product_name)}</td>
              <td style="padding:8px; text-align:center;">${i.quantity}</td>
              <td style="padding:8px; text-align:center;">
                <select class="item-status" data-prev="${st}">
                  <option value="pending" ${st==='pending'?'selected':''}>Pending</option>
                  <option value="preparing" ${st==='preparing'?'selected':''}>Preparing</option>
                  <option value="finished" ${st==='finished'?'selected':''}>Finished</option>
                  <option value="cancelled" ${st==='cancelled'?'selected':''}>Cancelled</option>
                </select>
                <span class="status-feedback"></span>
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
}

function attachItemDelegatedListener(container, orderId) {
  if (!container) return;
  if (container._itemListener) container.removeEventListener('change', container._itemListener);

  const listener = async (e) => {
    if (!e.target.matches('.item-status')) return;
    const select = e.target;
    const tr = select.closest('tr');
    const itemId = tr?.dataset?.itemId;
    if (!itemId) {
      console.warn('No itemId found in row:', tr);
      return;
    }

    const prev = select.dataset.prev;
    const newStatus = select.value;
    const feedback = tr.querySelector('.status-feedback');
    select.disabled = true;
    if (feedback) feedback.textContent = '⏳ Updating...';

    try {
      const res = await fetch('/Leilife/backend/admin/update_order_item_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_item_id: itemId, status: newStatus })
      });

      let resultText = await res.text();
      console.log('Raw response:', resultText);

      let result;
      try {
        result = JSON.parse(resultText);
      } catch {
        console.error('Invalid JSON response:', resultText);
        result = { success: false };
      }

      if (result.success) {
        console.log('✅ Item status updated successfully:', itemId, newStatus);

        // Update this row visually
        select.dataset.prev = newStatus;
        tr.className = `status-${escapeCss(newStatus)}`;

        // Update badge style instantly
        select.closest('td').style.background = 'var(--card)';
        if (feedback) {
          feedback.textContent = '✅';
          setTimeout(() => feedback.textContent = '', 800);
        }

        // Update main order badge (even without result.order_status)
        const effOrderId = orderId || tr.closest('.expandable-row')?.dataset?.orderId;
        const orderStatusBtn = document.querySelector(`.status-btn[data-id="${effOrderId}"]`);
        if (orderStatusBtn) {
          orderStatusBtn.innerHTML = `<span class="badge s-${escapeCss(newStatus)}">${formatStatus(newStatus)}</span>`;
          orderStatusBtn.dataset.status = newStatus;
        }

      } else {
        console.warn('❌ Backend returned failure:', result);
        select.value = prev;
        if (feedback) {
          feedback.textContent = '❌ Failed';
          setTimeout(() => feedback.textContent = '', 900);
        }
      }
    } catch (err) {
      console.error('⚠️ Network or script error:', err);
      select.value = prev;
      if (feedback) {
        feedback.textContent = '⚠️';
        setTimeout(() => feedback.textContent = '', 900);
      }
    } finally {
      select.disabled = false;
    }
  };

  container._itemListener = listener;
  container.addEventListener('change', listener);
}



/* -------------------------
   Helpers (kept simple)
   ------------------------- */
function downloadReceipt(order_number, user_id) {
  if (!order_number || !user_id) return;
  const url = `/Leilife/public/admin.php?page=pos-receipt&download=1&order_number=${encodeURIComponent(order_number)}&user_id=${encodeURIComponent(user_id)}`;
  window.open(url, '_blank');
}
function mapOrderToItemStatus(orderStatus) {
  switch (orderStatus) {
    case 'pending': return 'pending';
    case 'preparing': return 'preparing';
    case 'ready_for_delivery': return 'finished';
    case 'delivered': return 'finished';
    case 'cancelled': return 'pending';
    default: return 'pending';
  }
}
function updateCounts(pending, preparing, ready) {
  const p = document.getElementById('pending-count');
  const pr = document.getElementById('preparing-count');
  const r = document.getElementById('ready-count');
  if (p) p.innerText = pending;
  if (pr) pr.innerText = preparing;
  if (r) r.innerText = ready;
}
function formatStatus(status) {
  if (!status) return '';
  return String(status).replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}
function escapeHtml(str) {
  if (str == null) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function escapeAttr(v){ return String(v).replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
function escapeCss(v){ return String(v||'').replace(/[^a-z0-9_-]/gi,''); }

/* Confirm modal and Inbox preserved (assumes your HTML exists) */
function showConfirmModal(message, onConfirm) {
  let modal = document.getElementById("notif-modal");
  if (!modal) { onConfirm(); return; }
  const content = modal.querySelector(".notif-content");
  const originalHTML = content.innerHTML;
  content.innerHTML = `
    <p style="margin-bottom:12px;">${escapeHtml(message)}</p>
    <div style="display:flex; gap:10px; justify-content:center;">
      <button id="confirm-yes" style="padding:8px 12px; border-radius:8px; background:var(--accent); color:#fff; border:none;">Yes</button>
      <button id="confirm-no" style="padding:8px 12px; border-radius:8px; border:1px solid var(--surface-border); background:var(--card);">Cancel</button>
    </div>
  `;
  modal.style.display = 'flex';
  document.getElementById('confirm-yes').onclick = () => { modal.style.display='none'; content.innerHTML = originalHTML; onConfirm(); };
  document.getElementById('confirm-no').onclick = () => { modal.style.display='none'; content.innerHTML = originalHTML; };
}
function initInboxModal() {
  const modal = document.getElementById('messageModal');
  const modalMsg = document.getElementById('modalMessage');
  const closeBtn = modal?.querySelector('.close-btn');
  if (closeBtn) closeBtn.onclick = () => modal.style.display = 'none';
  window.onclick = (e) => { if (e.target === modal) modal.style.display = 'none'; };

  // attach existing editBtn logic if present
  document.querySelectorAll('.editBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      const msg = btn.dataset.message;
      const id = btn.dataset.id;
      if (modalMsg) modalMsg.textContent = msg;
      modal.style.display = 'flex';

      fetch(BASE_URL + "backend/admin/archive_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `mark_read=1&id=${id}`
      }).then(res => res.json()).then(data => {
        if (data.success) document.querySelector(`#row-${data.id}`)?.classList.remove("unread");
      }).catch(err => console.error('archive_message error', err));
    });
  });
}

</script>
