document.addEventListener("DOMContentLoaded", () => {

    // Welcome modal
    if (window.showWelcome) {
        const welcomeModal = document.getElementById('welcomeModal');
        welcomeModal.style.display = 'flex';
        window.closeWelcome = function() { welcomeModal.style.display = 'none'; }
    }

    // Inbox modal
    const modal = document.getElementById('messageModal');
    const modalMsg = document.getElementById('modalMessage');
    const closeBtn = modal.querySelector('.close-btn');
    closeBtn.onclick = () => modal.style.display = "none";
    window.onclick = (e) => { if(e.target == modal) modal.style.display = "none"; };

    document.querySelectorAll('.editBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            const msg = btn.dataset.message;
            const id = btn.dataset.id;
            modalMsg.textContent = msg;
            modal.style.display = "flex";

            fetch(BASE_URL + "backend/admin/archive_message.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `mark_read=1&id=${id}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) document.querySelector(`#row-${data.id}`)?.classList.remove("unread");
            });
        });
    });

    // Load orders
    loadOrders();

    // Sorting
    document.getElementById('sort').addEventListener('change', e => loadOrders(e.target.value));

    // Close dropdowns
    document.addEventListener('click', e => {
        if (!e.target.classList.contains('status-btn')) {
            document.querySelectorAll('.status-menu').forEach(m => m.classList.add('hidden'));
        }
    });
});

// ------------ LOAD ORDERS ------------
async function loadOrders(sortBy='order_date') {
    const tableBody = document.getElementById('table-body');
    tableBody.innerHTML = '';
    try {
        const res = await fetch(`/Leilife/backend/admin/get_orders.php?sort=${encodeURIComponent(sortBy)}`);
        const data = await res.json();
        if (!data.success) return;

        const orders = data.orders || [];
        if (orders.length === 0) {
            tableBody.innerHTML = `<p style="text-align:center; width:100%; padding:15px;">No orders found.</p>`;
            updateCounts(0,0,0); return;
        }

        let pending=0, preparing=0, ready=0;

        orders.forEach(order => {
            if(order.status==='pending') pending++;
            if(order.status==='preparing') preparing++;
            if(order.status==='ready_for_delivery') ready++;

            // Summary row
            const row = document.createElement('div');
            row.classList.add('table-row');
            row.style.display="flex";
            row.style.alignItems="center";
            row.style.justifyContent="space-between";
            row.style.padding="10px 20px";
            row.style.borderBottom="1px solid #ddd";
            row.style.cursor="pointer";
            row.dataset.orderId = order.order_id;

            row.innerHTML = `
                <p style="width:20%;">${escapeHtml(order.order_number)}</p>
                <p style="width:25%;">${escapeHtml(order.customer_name||'Unknown User')}</p>
                <p style="width:20%;">₱${parseFloat(order.total||0).toFixed(2)}</p>
                <p style="width:10%; text-align:left;">${order.items_count}</p>
                <div style="width:20%; position:relative;">
                    <button class="status-btn" data-id="${order.order_id}" data-status="${order.status}">
                        ${formatStatus(order.status)}
                    </button>
                    <div class="status-menu hidden">
                        ${createStatusOptions(order.status)}
                    </div>
                </div>
            `;

            tableBody.appendChild(row);

            // Expandable row
            const expandRow = document.createElement('div');
            expandRow.classList.add('expandable-row');
            expandRow.style.display="none";
            expandRow.style.background="#fafafa";
            expandRow.style.padding="10px 30px";
            expandRow.style.borderBottom="1px solid #ddd";
            expandRow.dataset.orderId = order.order_id;
            tableBody.appendChild(expandRow);

            row.addEventListener('click', async e=>{
                if(e.target.classList.contains('status-btn') || e.target.classList.contains('status-option')) return;

                const wasVisible = expandRow.style.display==='block';
                document.querySelectorAll('.expandable-row').forEach(el=>el.style.display='none');
                if(wasVisible) { expandRow.style.display='none'; return; }

                expandRow.innerHTML=`<p style="color:#888;">Loading items...</p>`;
                expandRow.style.display='block';

                try {
                    const itemsRes = await fetch(`/Leilife/backend/admin/get_order_items.php?order_id=${encodeURIComponent(order.order_id)}`);
                    const itemData = await itemsRes.json();
                    if(itemData.success && Array.isArray(itemData.items)) {
                        expandRow.innerHTML = renderItemTable(itemData.items);
                        attachItemDelegatedListener(expandRow, order.order_id);
                    } else expandRow.innerHTML=`<p style="color:#888;">No items found.</p>`;
                } catch(err) {
                    expandRow.innerHTML=`<p style="color:red;">Error loading items</p>`;
                    console.error(err);
                }
            });
        });

        updateCounts(pending,preparing,ready);
        attachStatusListeners();

    } catch(err) { console.error("Error loading orders:",err); }
}

// ------------ ITEM TABLE ------------
function renderItemTable(items) {
    return `
    <table style="width:100%; border-collapse: collapse;">
        <thead><tr style="background:#eee;">
            <th style="text-align:left; padding:5px;">Item</th>
            <th style="padding:5px;">Qty</th>
            <th style="padding:5px;">Status</th>
        </tr></thead>
        <tbody>
        ${items.map(i=>{
            const st = (i.status||'pending').toLowerCase();
            return `<tr data-item-id="${i.order_item_id}">
                <td style="padding:6px;">${escapeHtml(i.product_name)}</td>
                <td style="padding:6px; text-align:center;">${i.quantity}</td>
                <td style="padding:6px; text-align:center;">
                    <select class="item-status" data-prev="${st}" style="padding:4px;">
                        <option value="pending" ${st==='pending'?'selected':''}>Pending</option>
                        <option value="preparing" ${st==='preparing'?'selected':''}>Preparing</option>
                        <option value="finished" ${st==='finished'?'selected':''}>Finished</option>
                    </select>
                    <span class="status-feedback" style="margin-left:6px;"></span>
                </td>
            </tr>`;
        }).join('')}
        </tbody>
    </table>`;
}

// ------------ ITEM STATUS LISTENER ------------
function attachItemDelegatedListener(container, orderId){
    if(container._itemListener) container.removeEventListener('change', container._itemListener);

    const listener = async function(e){
        if(!e.target.matches('.item-status')) return;
        const select = e.target;
        const tr = select.closest('tr');
        const itemId = tr.dataset.itemId;
        const newStatus = select.value;
        const prev = select.dataset.prev;
        const feedback = tr.querySelector('.status-feedback');

        select.disabled=true; feedback.textContent='⏳';

        try{
            const res = await fetch('/Leilife/backend/admin/update_order_item_status.php',{
                method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({order_item_id:itemId,status:newStatus})
            });
            const result = await res.json();
            if(result.success){ select.dataset.prev=newStatus; feedback.textContent='✅'; setTimeout(()=>feedback.textContent='',700);}
            else { feedback.textContent='❌'; setTimeout(()=>feedback.textContent='',900); if(prev) select.value=prev; }
        }catch(err){ feedback.textContent='⚠️'; setTimeout(()=>feedback.textContent='',900); if(prev) select.value=prev; console.error(err);}
        finally{ select.disabled=false; }
    };

    container._itemListener = listener;
    container.addEventListener('change', listener);
}

// ------------ ORDER STATUS ------------
function createStatusOptions(current){
    const statuses=['pending','preparing','ready_for_delivery','delivered','cancelled'];
    return statuses.map(st=>`<div class="status-option ${st===current?'active':''}" data-status="${st}">${formatStatus(st)}</div>`).join('');
}

function attachStatusListeners(){
    document.querySelectorAll('.status-btn').forEach(btn=>{
        btn.onclick=null;
        btn.addEventListener('click',()=>{
            const menu = btn.nextElementSibling;
            document.querySelectorAll('.status-menu').forEach(m=>{if(m!==menu)m.classList.add('hidden');});
            menu.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('.status-option').forEach(opt=>{
        opt.onclick=null;
        opt.addEventListener('click', async ()=>{
            const newStatus=opt.dataset.status;
            const menu=opt.closest('.status-menu');
            const btn=menu.previousElementSibling;
            const orderId=btn.dataset.id;

            try{
                const res=await fetch("/Leilife/backend/admin/update_order_status.php",{
                    method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({order_id:orderId,status:newStatus})
                });
                const result=await res.json();
                if(result.success){
                    // update main btn
                    btn.innerText=formatStatus(newStatus);
                    btn.dataset.status=newStatus;
                    menu.classList.add('hidden');

                    // update all item selects in expandable row
                    const expandRow = document.querySelector(`.expandable-row[data-order-id="${orderId}"]`);
                    if(expandRow){
                        expandRow.querySelectorAll('.item-status').forEach(async select=>{
                            select.value=mapOrderToItemStatus(newStatus);
                            select.dataset.prev=select.value;

                            // optional: update DB
                            const itemId = select.closest('tr').dataset.itemId;
                            await fetch('/Leilife/backend/admin/update_order_item_status.php',{
                                method:'POST', headers:{'Content-Type':'application/json'},
                                body:JSON.stringify({order_item_id:itemId, status:select.value})
                            });
                        });
                    }

                    loadOrders(document.getElementById('sort').value);
                } else alert('Failed to update status: '+(result.error||'Unknown error'));
            }catch(err){ console.error(err);}
        });
    });
}

// ------------ HELPERS ------------
function mapOrderToItemStatus(orderStatus){
    switch(orderStatus){
        case 'pending': return 'pending';
        case 'preparing': return 'preparing';
        case 'ready_for_delivery': return 'finished';
        case 'delivered': return 'finished';
        case 'cancelled': return 'pending';
        default: return 'pending';
    }
}

function updateCounts(pending,preparing,ready){
    document.getElementById('pending-count').innerText=pending;
    document.getElementById('preparing-count').innerText=preparing;
    document.getElementById('ready-count').innerText=ready;
}

function formatStatus(status){ return status?status.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()):''; }
function escapeHtml(str){ if(str==null) return ''; return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }
