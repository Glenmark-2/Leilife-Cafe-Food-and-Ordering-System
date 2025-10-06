    document.addEventListener("DOMContentLoaded", () => {

        // initial load
        loadOrders();

        // sort handler
        document.getElementById('sort').addEventListener('change', e => {
            loadOrders(e.target.value);
        });

        // close order status menus when clicking outside
        document.addEventListener('click', e => {
            if (!e.target.classList.contains('status-btn')) {
                document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
            }
        });
    });

    // ------------ MAIN: load orders ------------
    async function loadOrders(sortBy = 'order_date') {
        try {
            const res = await fetch(`/Leilife/backend/admin/get_orders.php?sort=${encodeURIComponent(sortBy)}`);
            const data = await res.json();

            if (!data.success) {
                console.error("Backend error:", data.error);
                return;
            }

            const orders = data.orders || [];
            const tableBody = document.getElementById('table-body');
            tableBody.innerHTML = '';

            let pending = 0, preparing = 0, ready = 0;

            if (orders.length === 0) {
                tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No orders found.</p>`;
                updateCounts(0, 0, 0);
                return;
            }

            orders.forEach(order => {
                if (order.status === 'pending') pending++;
                if (order.status === 'preparing') preparing++;
                if (order.status === 'ready_for_delivery') ready++;

                // create summary row
                const row = document.createElement('div');
                row.classList.add('table-row');
                row.style.display = "flex";
                row.style.alignItems = "center";
                row.style.justifyContent = "space-between";
                row.style.padding = "10px 20px";
                row.style.borderBottom = "1px solid #ddd";
                row.style.cursor = "pointer";
                row.dataset.orderId = order.order_id;

                row.innerHTML = `
                  <p style="width: 20%;">${escapeHtml(order.order_number)}</p>
                  <p style="width: 25%;">${escapeHtml(order.customer_name || 'Unknown User')}</p>
                  <p style="width: 20%;">₱${parseFloat(order.total || 0).toFixed(2)}</p>
                   <p style="width: 10%; text-align: left;">${order.items_count}</p>

                  <div style="width: 20%; position: relative;">
                    <button class="status-btn" data-id="${order.order_id}" data-status="${order.status}">
                      ${formatStatus(order.status)}
                    </button>
                    <div class="status-menu hidden">
                      ${createStatusOptions(order.status)}
                    </div>
                  </div>
                `;

                tableBody.appendChild(row);

                // expandable container (created per order)
                const expandRow = document.createElement('div');
                expandRow.classList.add('expandable-row');
                expandRow.style.display = "none";
                expandRow.style.background = "#fafafa";
                expandRow.style.padding = "10px 30px";
                expandRow.style.borderBottom = "1px solid #ddd";
                expandRow.dataset.orderId = order.order_id;
                tableBody.appendChild(expandRow);

                // expand/collapse behavior
                row.addEventListener('click', async (e) => {
                    // don't toggle when clicking order-status controls
                    if (e.target.classList.contains('status-btn') || e.target.classList.contains('status-option')) return;

                    const wasVisible = expandRow.style.display === "block";
                    // close others
                    document.querySelectorAll('.expandable-row').forEach(el => el.style.display = "none");

                    if (wasVisible) {
                        expandRow.style.display = "none";
                        return;
                    }

                    // show loading
                    expandRow.innerHTML = `<p style="color:#888;">Loading items...</p>`;
                    expandRow.style.display = "block";

                    try {
                        const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`);
                        const itemData = await itemsRes.json();

                        if (itemData.success && Array.isArray(itemData.items) && itemData.items.length > 0) {
                            expandRow.innerHTML = renderItemTable(itemData.items);
                            // attach a single delegated listener on expandRow to handle item status changes
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
            attachStatusListeners(); // order-level status dropdowns

        } catch (err) {
            console.error("Error loading orders:", err);
        }
    }

    // ------------ RENDER item table HTML (each select has data-prev) ------------
    function renderItemTable(items) {
        return `
    <table style="width:100%; border-collapse: collapse;">
      <thead>
        <tr style="background:#eee;">
          <th style="text-align:left; padding:5px;">Item</th>
          <th style="padding:5px;">Qty</th>
          <th style="padding:5px;">Status</th>
        </tr>
      </thead>
      <tbody>
        ${items.map(i => {
            const st = (i.status || 'pending').toLowerCase();
            // ensure product_name and quantity are escaped
            return `
            <tr data-item-id="${i.order_item_id}">
              <td style="padding:6px;">${escapeHtml(i.product_name)}</td>
              <td style="padding:6px; text-align:center;">${i.quantity}</td>
              <td style="padding:6px; text-align:center;">
                <select class="item-status" data-prev="${st}" style="padding:4px;">
                  <option value="pending" ${st === 'pending' ? 'selected' : ''}>Pending</option>
                  <option value="preparing" ${st === 'preparing' ? 'selected' : ''}>Preparing</option>
                  <option value="finished" ${st === 'finished' ? 'selected' : ''}>Finished</option>
                </select>
                <span class="status-feedback" style="margin-left:6px;"></span>
              </td>
            </tr>
          `;
        }).join('')}
      </tbody>
    </table>
  `;
    }

    // ------------ Attach delegated listener for item status changes ------------
    function attachItemDelegatedListener(container, orderId) {
        // remove existing listener if any (prevents double-binding when reusing same container)
        if (container._itemListener) {
            container.removeEventListener('change', container._itemListener);
            container._itemListener = null;
        }

        const listener = async function (e) {
            if (!e.target.matches('.item-status')) return;
            const select = e.target;
            const tr = select.closest('tr');
            const itemId = tr.dataset.itemId;
            const newStatus = select.value;
            const prev = select.dataset.prev || select.getAttribute('data-prev') || null;
            const feedback = tr.querySelector('.status-feedback');

            // optimistic UI: disable control while saving
            select.disabled = true;
            feedback.textContent = '⏳';

            try {
                const res = await fetch('/Leilife/backend/admin/update_order_item_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_item_id: itemId, status: newStatus })
                });

                const result = await res.json();

                if (result.success) {
                    // success: keep new value and update data-prev
                    select.dataset.prev = newStatus;
                    feedback.textContent = '✅';
                    setTimeout(() => { feedback.textContent = ''; }, 700);

                    // OPTIONAL: If you want to automatically set order status when ALL items finished,
                    // you can call a dedicated endpoint here to check and update order status.
                    // For now: we do not re-run loadOrders(), so the select will not revert.
                } else {
                    // failed: revert UI to previous value
                    feedback.textContent = '❌';
                    setTimeout(() => { feedback.textContent = ''; }, 900);
                    if (prev) select.value = prev;
                    console.error('Update failed:', result.error);
                }
            } catch (err) {
                // network or unexpected error: revert and show warning
                feedback.textContent = '⚠️';
                setTimeout(() => { feedback.textContent = ''; }, 900);
                if (prev) select.value = prev;
                console.error('Error updating item status:', err);
            } finally {
                select.disabled = false;
            }
        };

        // store listener reference so we can remove later if needed
        container._itemListener = listener;
        container.addEventListener('change', listener);
    }

    // ------------ Order-level status helpers ------------
    function createStatusOptions(current) {
        const statuses = ['pending', 'preparing', 'ready_for_delivery', 'delivered', 'cancelled'];
        return statuses.map(st => `
    <div class="status-option ${st === current ? 'active' : ''}" data-status="${st}">
      ${formatStatus(st)}
    </div>`).join('');
    }

    function attachStatusListeners() {
        // remove previously bound click handlers by cloning nodes (simple way to remove)
        document.querySelectorAll('.status-btn').forEach(btn => {
            // ensure no duplicates — we can rely on re-binding after each load
            btn.onclick = null;
            btn.addEventListener('click', e => {
                const menu = btn.nextElementSibling;
                document.querySelectorAll('.status-menu').forEach(m => {
                    if (m !== menu) m.classList.add('hidden');
                });
                menu.classList.toggle('hidden');
            });
        });

        // bind status-option clicks (these elements are recreated each load)
        document.querySelectorAll('.status-option').forEach(opt => {
            opt.onclick = null;
            opt.addEventListener('click', async (e) => {
                const newStatus = opt.dataset.status;
                const menu = opt.closest('.status-menu');
                const btn = menu.previousElementSibling;
                const orderId = btn.dataset.id;

                try {
                    const res = await fetch("/Leilife/backend/admin/update_order_status.php", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ order_id: orderId, status: newStatus })
                    });
                    const result = await res.json();

                    if (result.success) {
                        btn.innerText = formatStatus(newStatus);
                        btn.dataset.status = newStatus;
                        menu.classList.add('hidden');
                        // refresh list to show new order order-level status and counts
                        loadOrders(document.getElementById('sort').value);
                    } else {
                        alert('Failed to update status: ' + (result.error || 'Unknown error'));
                    }
                } catch (err) {
                    console.error("Error updating status:", err);
                }
            });
        });
    }

    // ------------ Helpers ------------
    function updateCounts(pending, preparing, ready) {
        document.getElementById('pending-count').innerText = pending;
        document.getElementById('preparing-count').innerText = preparing;
        document.getElementById('ready-count').innerText = ready;
    }

    function formatStatus(status) {
        return status ? status.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '';
    }

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }