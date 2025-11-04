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


<div class="container">
  <div id="first-row">
    <h2>Dashboard</h2>
    <!-- you can keep the sales filter at header if desired -->

  </div>


  <!-- stats row -->
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
    <!-- <label for="orderSearch" style="font-weight:600;">Search Order #:</label> -->
    <input type="text" id="orderSearch" placeholder="Enter order number" style="padding:6px 10px; border-radius:6px; border:1px solid #ccc; flex:1;">
  </div>


  <!-- Recent orders -->
  <div id="third-row" class="recent">
    <div class="recent-top">
      <p><strong>Recent Orders</strong></p>
      <div class="sort-dropdown" style="margin-left:auto;">
        <label for="sort">Sort by:</label>
        <select id="sort" style="margin-left:6px;">
          <option value="order_date">Order Date</option>
          <option value="status">Status</option>
          <option value="total">Total</option>
          <option value="pickup">Pick up</option>
          <option value="home_delivery">Home Delivery</option>

        </select>
      </div>
    </div>

    <div id="table">
      <div id="table-title">
        <p style="width: 23%;">Order #</p>
        <p style="width: 18%;">Customer</p>
        <p style="width: 13%;">Amount</p>
        <p style="width: 8%;">Item</p>
        <p style="width: 18%;">Status</p>
        <p style="width: 15%;">Payment Status</p>
        <p style="width: 13%;">Method</p>
        <p style="width: 15%;">Download Receipt</p>
      </div>

      <div id="table-body">
        <!-- rows are injected by JS (loadOrders) -->
      </div>
    </div>
  </div>

  <!-- Inbox -->
  <div id="table-container">
    <p style="font-weight:700; margin-bottom:8px;">Recent Messages</p>
    <table class="staff-table" aria-live="polite">
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
                <button type="button" class="editBtn" data-message="<?= htmlspecialchars($msg['message']) ?>" data-id="<?= $msg['sender_id'] ?>">View</button>
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

  <!-- Message modal -->
  <div id="messageModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-content" role="document">
      <span class="close-btn" aria-label="Close">&times;</span>
      <h2 style="margin-top:0;">Message</h2>
      <p id="modalMessage" style="white-space:pre-wrap; color:var(--muted);"></p>
    </div>
  </div>

  <?php if ($showWelcome): ?>
    <div id="welcomeModal" class="modal" aria-hidden="true" role="dialog" aria-modal="true">
      <div class="modal-content">
        <span class="close" onclick="closeWelcome()" aria-hidden="true">&times;</span>
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


<!-- External JS base url -->
<script>
  const BASE_URL = "<?= rtrim((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]/Leilife/", "/") ?>/";
</script>

<!-- ============================
     CLEANED & ORGANIZED JS
     (keeps all function names / ids unchanged)
     ============================ -->
<script>
  /* -------------------------------
   ROW SECTIONS (Mobile-friendly)
--------------------------------*/

  let currentView = 'active'; // "active" or "completed"

  document.addEventListener("DOMContentLoaded", () => {
    // Welcome modal
    if (window.showWelcome) {
      const welcomeModal = document.getElementById('welcomeModal');
      if (welcomeModal) {
        welcomeModal.style.display = 'flex';
        window.closeWelcome = function() {
          welcomeModal.style.display = 'none';
        };
      }
    }
    document.querySelectorAll('.status-btn').forEach(btn => {
      updateStatusButtonColor(btn, btn.dataset.status);
    });

    // Inbox modal
    initInboxModal();

    // Order view toggle buttons (optional)
    const btnActive = document.getElementById('btnActiveOrders');
    const btnCompleted = document.getElementById('btnCompletedOrders');

    if (btnActive) {
      btnActive.addEventListener('click', () => {
        currentView = 'active';
        btnActive.classList.add('active');
        if (btnCompleted) btnCompleted.classList.remove('active');
        const sortEl = document.getElementById('sort');
        loadOrders(sortEl ? sortEl.value : 'order_date');
      });
    }

    if (btnCompleted) {
      btnCompleted.addEventListener('click', () => {
        currentView = 'completed';
        btnCompleted.classList.add('active');
        if (btnActive) btnActive.classList.remove('active');
        loadOrders('order_date');
      });
    }

    // Sorting
    const sortEl = document.getElementById('sort');
    if (sortEl) {
      sortEl.addEventListener('change', e => {
        const selected = e.target.value;
        if (selected === 'completed') {
          currentView = 'completed';
          if (btnCompleted) {
            btnCompleted.classList.add('active');
          }
          if (btnActive) {
            btnActive.classList.remove('active');
          }
          loadOrders('order_date');
        } else {
          currentView = 'active';
          if (btnActive) {
            btnActive.classList.add('active');
          }
          if (btnCompleted) {
            btnCompleted.classList.remove('active');
          }
          loadOrders(selected);
        }
      });
    }

    // Load orders initially
    loadOrders(sortEl ? sortEl.value : 'order_date');

    //search bar
    const orderSearch = document.getElementById('orderSearch');
    if (orderSearch) {
      orderSearch.addEventListener('input', () => {
        loadOrders(sortEl ? sortEl.value : 'order_date', orderSearch.value.trim());
      });
    }


    // Close status menus when clicking outside
    document.addEventListener('click', e => {
      if (!e.target.classList.contains('status-btn')) {
        document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
      }
    });

    // Auto refresh counts and orders every 60s (optional)
    setInterval(() => {
      const s = document.getElementById('sort');
      loadOrders(s ? s.value : 'order_date');
    }, 60000);
  });

  // --------------------
  // Inbox modal logic
  // --------------------
  function initInboxModal() {
    const modal = document.getElementById('messageModal');
    if (!modal) {
      // still attach edit buttons defensively
      document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
          const id = btn.dataset.id;
          fetch(BASE_URL + "backend/admin/archive_message.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `mark_read=1&id=${id}`
          }).catch(() => {});
        });
      });
      return;
    }

    const modalMsg = document.getElementById('modalMessage');
    const closeBtn = modal.querySelector('.close-btn');
    if (closeBtn) closeBtn.onclick = () => (modal.style.display = "none");
    window.onclick = (e) => {
      if (e.target == modal) modal.style.display = "none";
    };

    document.querySelectorAll('.editBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        const msg = btn.dataset.message;
        const id = btn.dataset.id;
        if (modalMsg) modalMsg.textContent = msg;
        modal.style.display = "flex";

        fetch(BASE_URL + "backend/admin/archive_message.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded"
            },
            body: `mark_read=1&id=${id}`
          })
          .then(res => res.json())
          .then(data => {
            if (data.success) document.querySelector(`#row-${data.id}`)?.classList.remove("unread");
          })
          .catch(err => console.error('archive_message error', err));
      });
    });
  }


  // ==================================================
  // LOAD ORDERS
  // ==================================================
  async function loadOrders(sortBy = 'order_date', searchTerm = '') {
    const tableBody = document.getElementById('table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '';

    try {
      const res = await fetch(`/Leilife/backend/admin/get_orders.php?view=${encodeURIComponent(currentView)}&sort=${encodeURIComponent(sortBy)}&order_number=${encodeURIComponent(searchTerm)}`);
      const data = await res.json();
      if (!data.success) {
        console.warn('get_orders returned success=false', data.error);
        tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
        updateCounts(0, 0, 0);
        return;
      }

      const orders = data.orders || [];
      if (orders.length === 0) {
        tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No ${currentView === 'active' ? 'active' : 'completed'} orders found.</p>`;
        updateCounts(0, 0, 0);
        return;
      }

      let pending = 0,
        preparing = 0,
        ready = 0;

      orders.forEach(order => {
        if (order.status === 'pending') pending++;
        if (order.status === 'preparing') preparing++;
        if (order.status === 'ready_for_delivery') ready++;

        // Main order row
        const row = document.createElement('div');
        row.classList.add('table-row');
        row.dataset.orderId = order.order_id;

        row.innerHTML = `
  <p style="width:23%;">${escapeHtml(order.order_number)}</p>
  <p style="width:18%;">${escapeHtml(order.customer_name || 'Unknown User')}</p>
  <p id="order-total-${order.order_id}" style="width:13%;">₱${parseFloat(order.total || 0).toFixed(2)}</p>
  <p style="width:8%; text-align:left;">${order.items_count}</p>
  <div style="width:18%; position:relative;">
      <button class="status-btn" data-id="${order.order_id}" data-status="${order.status}">
          ${formatStatus(order.status)}
      </button>
      <div class="status-menu hidden">
          ${createStatusOptions(order.status, order.delivery_method)}
      </div>
  </div>
  <p style="width:15%;">${escapeHtml(order.payment_status)}</p>
  <p style="width:13%;">${escapeHtml(order.delivery_method)}</p>
  <div style="width:15%; display:flex; justify-content:center">
      <button class="dlBtn" style="background:transparent; border:0;">
          <img src="/leilife/public/assests/downloads.png" alt="Download" style="width:20px;">
      </button>
  </div>
`;

        tableBody.appendChild(row);


        const dlBtn = row.querySelector('.dlBtn');
        dlBtn.addEventListener('click', e => {
          e.stopPropagation(); // prevent row click
          downloadReceipt(order.order_number, order.user_id);
        });


        // Expandable item row
        const expandRow = document.createElement('div');
        expandRow.classList.add('expandable-row');
        expandRow.style.display = "none";
        expandRow.style.background = "#fafafa";
        expandRow.style.padding = "10px 30px";
        expandRow.style.borderBottom = "1px solid #ddd";
        expandRow.dataset.orderId = order.order_id;
        tableBody.appendChild(expandRow);



        row.addEventListener('click', async e => {
          if (e.target.classList.contains('status-btn') || e.target.classList.contains('status-option')) return;

          const wasVisible = expandRow.style.display === 'block';
          document.querySelectorAll('.expandable-row').forEach(el => el.style.display = 'none');
          if (wasVisible) {
            expandRow.style.display = 'none';
            return;
          }

          expandRow.innerHTML = `<p style="color:#888;">Loading items...</p>`;
          expandRow.style.display = 'block';

          try {
            const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`);
            const itemData = await itemsRes.json();
            if (itemData.success && Array.isArray(itemData.items)) {
              expandRow.innerHTML = renderItemTable(itemData.items);
              attachItemDelegatedListener(expandRow, order.order_id);
            } else {
              expandRow.innerHTML = `<p style="color:#888;">No items found.</p>`;
            }
          } catch (err) {
            expandRow.innerHTML = `<p style="color:red;">Error loading items</p>`;
            console.error(err);
          }
        });
      });

      updateCounts(pending, preparing, ready);
      attachStatusListeners();

    } catch (err) {
      console.error("Error loading orders:", err);
      tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">Error loading orders.</p>`;
      updateCounts(0, 0, 0);
    }
  }

  function downloadReceipt(order_number, user_id) {
    if (!order_number || !user_id) return;

    const url = `/Leilife/public/admin.php?page=pos-receipt&download=1&order_number=${encodeURIComponent(order_number)}&user_id=${encodeURIComponent(user_id)}`;
    window.open(url, '_blank'); // triggers receipt download in new tab
  }



  // ==================================================
  // ITEM TABLE
  // ==================================================
  function renderItemTable(items) {
    return `
  <table class="item-table">
    <thead>
      <tr>
        <th>Item</th>
        <th>Qty</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      ${items.map(i => {
        const st = (i.status || 'pending').toLowerCase();
        return `
          <tr data-item-id="${i.order_item_id}" class="status-${st}">
            <td>${escapeHtml(i.product_name)}</td>
            <td class="text-center">${i.quantity}</td>
            <td class="text-center">
              <select class="item-status" data-prev="${st}">
                <option value="pending" ${st === 'pending' ? 'selected' : ''}>Pending</option>
                <option value="preparing" ${st === 'preparing' ? 'selected' : ''}>Preparing</option>
                <option value="finished" ${st === 'finished' ? 'selected' : ''}>Finished</option>
                <option value="cancelled" ${st === 'cancelled' ? 'selected' : ''}>Cancelled</option>
              </select>
              <span class="status-feedback"></span>
            </td>
          </tr>`;
      }).join('')}
    </tbody>
  </table>`;
  }


  // ==================================================
  // ITEM STATUS LISTENER (fixed, complete, robust)
  // ==================================================
  function attachItemDelegatedListener(container, orderId) {
    // Remove old listener if any
    if (container._itemListener)
      container.removeEventListener('change', container._itemListener);

    const listener = async (e) => {
      if (!e.target.matches('.item-status')) return;

      const select = e.target;
      const tr = select.closest('tr');
      const itemId = tr?.dataset?.itemId;
      if (!itemId) return;

      // orderId parameter passed when attaching listener is the fallback
      const fallbackOrderId = orderId;

      const newStatus = select.value;
      const prevStatus = select.dataset.prev;
      const feedback = tr.querySelector('.status-feedback');

      select.disabled = true;
      if (feedback) feedback.textContent = '⏳';

      try {
        const res = await fetch('/Leilife/backend/admin/update_order_item_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            order_item_id: itemId,
            status: newStatus
          })
        });

        // Always inspect what the server returned for quick debugging
        let result;
        try {
          result = await res.json();
        } catch (jsonErr) {
          console.error('Failed to parse JSON from update_order_item_status:', jsonErr);
          result = {
            success: false,
            _rawStatus: res.status
          };
        }
        console.log('update_order_item_status result:', result);

        if (result.success) {
          // ✅ Backend success — update dataset + visuals
          select.dataset.prev = newStatus;
          tr.className = `status-${newStatus}`; // change row color by status
          if (feedback) {
            feedback.textContent = '✅';
            setTimeout(() => feedback.textContent = '', 800);
          }

          // Determine order id to update UI: prefer server value, else fallback
          const effectiveOrderId =
            result.order_id ||
            fallbackOrderId ||
            tr.closest('.expandable-row')?.dataset?.orderId;

          // 🪄 Update the main order row’s status immediately if backend returned it
          if (result.order_status && effectiveOrderId) {
            const orderStatusBtn = document.querySelector(`.status-btn[data-id="${effectiveOrderId}"]`);
            if (orderStatusBtn) {
              orderStatusBtn.textContent = formatStatus(result.order_status);
              orderStatusBtn.dataset.status = result.order_status;
              updateStatusButtonColor(orderStatusBtn, result.order_status);
            }
          }

          // 🪄 Update UI total if backend returned a new_total or fallback to asking server again
          if (result.new_total !== undefined && effectiveOrderId) {
            const totalEl = document.querySelector(`#order-total-${effectiveOrderId}`);
            if (totalEl) {
              totalEl.textContent = `₱${parseFloat(result.new_total).toFixed(2)}`;
              totalEl.classList.add('updated-total');
              setTimeout(() => totalEl.classList.remove('updated-total'), 1000);
            }
          } else if (effectiveOrderId) {
            // If server didn't return new_total, fetch the latest order (safe fallback)
            try {
              const r2 = await fetch(
                `/Leilife/backend/admin/get_orders.php?view=${encodeURIComponent(currentView)}&sort=order_date&order_number=`
              );
              const d2 = await r2.json();
              if (d2.success && Array.isArray(d2.orders)) {
                const updatedOrder = d2.orders.find(
                  (o) => String(o.order_id) === String(effectiveOrderId)
                );
                if (updatedOrder) {
                  const totalEl = document.querySelector(`#order-total-${effectiveOrderId}`);
                  if (totalEl) {
                    totalEl.textContent = `₱${parseFloat(updatedOrder.total || 0).toFixed(2)}`;
                  }

                  // Also update order status if changed
                  const orderStatusBtn = document.querySelector(`.status-btn[data-id="${effectiveOrderId}"]`);
                  if (orderStatusBtn && updatedOrder.status) {
                    orderStatusBtn.textContent = formatStatus(updatedOrder.status);
                    orderStatusBtn.dataset.status = updatedOrder.status;
                    updateStatusButtonColor(orderStatusBtn, updatedOrder.status);
                  }
                }
              }
            } catch (e) {
              console.warn('Fallback fetch of orders failed:', e);
            }
          }
        } else {
          // ❌ Backend rejected — revert UI
          select.value = prevStatus;
          if (feedback) {
            feedback.textContent = '❌';
            setTimeout(() => (feedback.textContent = ''), 900);
          }
          console.warn('update_order_item_status returned success=false', result);
        }
      } catch (err) {
        // ⚠️ Network error — revert UI
        console.error('Status update failed (network):', err);
        select.value = prevStatus;
        if (feedback) {
          feedback.textContent = '⚠️';
          setTimeout(() => (feedback.textContent = ''), 900);
        }
      } finally {
        select.disabled = false;
      }
    };

    container._itemListener = listener;
    container.addEventListener('change', listener);
  }


  // ==================================================
  // ORDER STATUS
  // ==================================================
  function createStatusOptions(current, deliveryMethod) {
    let statuses = [];

    if (deliveryMethod === 'pickup') {
      statuses = ['pending', 'preparing', 'picked_up', 'cancelled'];
    } else if (deliveryMethod === 'home') {
      statuses = ['pending', 'preparing', 'ready_for_delivery', 'cancelled'];
    } else {
      // fallback
      statuses = ['pending', 'preparing', 'ready_for_delivery', 'delivered', 'cancelled'];
    }

    return statuses
      .map(st => `<div class="status-option ${st === current ? 'active' : ''}" data-status="${st}">${formatStatus(st)}</div>`)
      .join('');
  }


  function attachStatusListeners() {
    // Close all menus when clicking outside
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.status-btn') && !e.target.closest('.status-menu')) {
        document.querySelectorAll('.status-menu').forEach(menu => menu.classList.add('hidden'));
      }
    });

    // Status button click
    document.querySelectorAll('.status-btn').forEach(btn => {
      btn.onclick = null;
      btn.addEventListener('click', e => {
        e.stopPropagation();

        const menu = btn.nextElementSibling;
        document.querySelectorAll('.status-menu').forEach(m => {
          if (m !== menu) m.classList.add('hidden');
        });

        if (menu) menu.classList.toggle('hidden');
      });
    });

    // Option click
    document.querySelectorAll('.status-option').forEach(option => {
      option.onclick = null;
      option.addEventListener('click', async e => {
        e.stopPropagation();

        const menu = option.closest('.status-menu');
        const btn = menu?.previousElementSibling;
        const orderId = btn?.dataset.id;
        const newStatus = option.dataset.status;

        if (!orderId) return;

        const handleStatusChange = async () => {
          btn.textContent = formatStatus(newStatus);
          btn.dataset.status = newStatus;
          updateStatusButtonColor(btn, newStatus);
          menu.classList.add('hidden');

          const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
          if (expandRow) {
            expandRow.querySelectorAll('.item-status').forEach(select => {
              select.value = mapOrderToItemStatus(newStatus);
              select.dataset.prev = select.value;
            });
          }

          try {
            const res = await fetch('/Leilife/backend/admin/update_order_status.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                order_id: orderId,
                status: newStatus
              })
            });
            const data = await res.json();
            console.log('🧾 update_order_status.php response:', data);

            if (data.success) {
              if (newStatus === "cancelled") {
                const mainRow = btn.closest('.table-row');
                if (mainRow) mainRow.remove();
                const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
                if (expandRow) expandRow.remove();
              }
            } else {
              alert('Failed to update status');
            }

          } catch (err) {
            console.error('Status update error:', err);
          }
        };

        if (newStatus === "cancelled") {
          showConfirmModal("Are you sure you want to cancel this order?", handleStatusChange);

        } else {
          handleStatusChange();
        }
      });
    });


  }

  // ==================================================
  // ORDER STATUS BUTTON COLOR UPDATER (uses CSS variables)
  // ==================================================
  function updateStatusButtonColor(btn, status) {
    if (!btn) return;

    // Normalize status
    const normalized = status?.toLowerCase() || 'pending';
    btn.dataset.status = normalized; // this connects to CSS attribute selectors

    // Optional: Smooth transition
    btn.style.transition = 'background-color 0.25s ease, color 0.25s ease';
  }
  // ==================================================
  // HELPERS
  // ==================================================
  function mapOrderToItemStatus(orderStatus) {
    switch (orderStatus) {
      case 'pending':
        return 'pending';
      case 'preparing':
        return 'preparing';
      case 'ready_for_delivery':
        return 'finished';
      case 'delivered':
        return 'finished';
      case 'cancelled':
        return 'pending';
      default:
        return 'pending';
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
    return status ? status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '';

  }

  function escapeHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }


  function showConfirmModal(message, onConfirm) {
    let modal = document.getElementById("notif-modal");
    if (!modal) {
      showModal();
      modal = document.getElementById("notif-modal");
      modal.style.display = "none";
    }

    const content = modal.querySelector(".notif-content");
    const originalHTML = content.innerHTML;

    content.innerHTML = `
        <p>${message}</p>
        <div style="display:flex; justify-content:center; gap:10px;">
            <button id="confirm-yes" class="success">Yes</button>
            <button id="confirm-no" class="error">Cancel</button>
        </div>
    `;

    modal.style.display = "flex";
    document.getElementById("confirm-yes").onclick = () => {
      modal.style.display = "none";
      content.innerHTML = originalHTML;
      onConfirm();
    };
    document.getElementById("confirm-no").onclick = () => {
      modal.style.display = "none";
      content.innerHTML = originalHTML;
    };
  }
</script>