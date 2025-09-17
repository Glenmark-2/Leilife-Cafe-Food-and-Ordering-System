// Scripts/pages/checkout-page.js
const USER_API = '../backend/checkout-page.php';
const CART_API = '../backend/get_cart.php';

document.addEventListener('DOMContentLoaded', () => {
  bindPhoneEdit();
  fetchUserData();
  fetchCartData(); // fetch cart items + totals
});

// ============================
// FETCH USER DATA
// ============================
async function fetchUserData() {
  try {
    const res = await fetch(USER_API, {
      method: 'GET',
      credentials: 'same-origin'
    });
    const payload = await res.json();
    if (!payload.success) {
      console.error('API error:', payload.message);
      return;
    }
    populateFields(payload.data || {});
  } catch (err) {
    console.error('Fetch error:', err);
  }
}

function populateFields(data) {
  // Full name
  const fullName = [data.first_name, data.last_name].filter(Boolean).join(" ");
  document.getElementById('full-name').value = fullName;

  // Phone
  document.getElementById('phone').value = data.phone_number ?? '';

  // ✅ Barangay + fixed city/province/region
  const barangay = data.barangay ? `Barangay ${data.barangay}` : '';

  const fullAddress = [
    data.street_address,
    barangay,
    "Caloocan City",
    "Metro Manila",
    "NCR"
  ].filter(Boolean).join(', ');

  document.getElementById('full-address').value = fullAddress;

  // Notes
  document.getElementById('note').value = data.note_to_rider ?? '';
}




// ============================
// FETCH CART DATA
// ============================
async function fetchCartData() {
  try {
    const res = await fetch(CART_API, {
      method: 'GET',
      credentials: 'same-origin'
    });
    const payload = await res.json();
    console.log("Cart payload:", payload); // 🔎 debug

    if (!payload.success) {
      console.error('Cart error:', payload.message);
      return;
    }
    renderCart(payload.cart || []);
    updateTotals(payload.totals || {});
  } catch (err) {
    console.error('Cart fetch error:', err);
  }
}

function renderCart(items) {
  const container = document.getElementById('order-items');
  container.innerHTML = ''; // clear placeholder

  if (!items.length) {
    container.innerHTML = '<p>Your cart is empty.</p>';
    return;
  }

  items.forEach(item => {
    const div = document.createElement('div');
    div.classList.add('order-item');
    div.style.display = "flex";
    div.style.alignItems = "center";
    div.style.marginBottom = "10px";

    // Safe image path (fallback placeholder if null)
    const imgSrc = item.product_picture 
      ? `../public/products/${item.product_picture}` 
      : '../public/assets/no-image.png';

    div.innerHTML = `
      <img src="${imgSrc}" alt="${item.product_name}" 
           style="width:60px;height:60px;object-fit:cover;margin-right:10px;" />
      <div style="flex:1;">
        <p style="margin:0;font-weight:500;">${item.product_name}${item.size ? ' ('+item.size+')' : ''}</p>
        <small>
          ₱${Number(item.final_price).toFixed(2)} × ${item.quantity}
          ${item.flavor_names ? '<br>Flavors: ' + item.flavor_names : ''}
        </small>
      </div>
      <div style="font-weight:600;">
        ₱${(Number(item.final_price) * Number(item.quantity)).toFixed(2)}
      </div>
    `;
    container.appendChild(div);
  });
}

// ============================
// DELIVERY TOGGLE
// ============================
function toggleDelivery() {
  const pickup = document.getElementById('pickup-options');
  const home = document.getElementById('home-options');
  const selected = document.querySelector('input[name="delivery"]:checked');

  if (!selected) {
    pickup.style.display = 'none';
    home.style.display = 'none';
    return;
  }

  if (selected.value === 'pickup') {
    pickup.style.display = 'block';
    home.style.display = 'none';
    const pickupRadio = pickup.querySelector('input[name="pickup_location"]');
    if (pickupRadio) pickupRadio.checked = true;
  } else {
    pickup.style.display = 'none';
    home.style.display = 'block';
  }
}


// // ============================
// // ADDRESS EDIT
// // ============================
// async function toggleEdit() {
//   const fields = document.querySelectorAll('#home-options textarea');
//   const editBtn = document.querySelector('#home-options .edit-btn');
//   if (!fields || fields.length === 0) return;

//   const isReadonly = fields[0].hasAttribute('readonly');

//   if (isReadonly) {
//     fields.forEach(field => {
//       field.removeAttribute('readonly');
//       field.style.background = '#fff';
//     });
//     editBtn.textContent = 'Save';
//     return;
//   }

//   const payload = {
//     action: 'update_address',
//     full_address: document.getElementById('full-address').value.trim(),
//     note_to_rider: document.getElementById('note').value.trim()
//   };

//   try {
//     const res = await fetch(USER_API, {
//       method: 'POST',
//       credentials: 'same-origin',
//       headers: { 'Content-Type': 'application/json' },
//       body: JSON.stringify(payload)
//     });
//     const data = await res.json();
//     if (data.success) {
//       fields.forEach(field => {
//         field.setAttribute('readonly', true);
//         field.style.background = '#f5f5f5';
//       });
//       editBtn.textContent = 'Edit';
//       showTempMessage('Address updated successfully');
//     } else {
//       showTempMessage('Failed to update address: ' + (data.message || 'unknown'), true);
//     }
//   } catch (err) {
//     showTempMessage('Network error while updating address', true);
//   }
// }


// ============================
// PHONE EDIT
// ============================
function bindPhoneEdit() {
  const btn = document.getElementById('phone-edit-btn');
  const phone = document.getElementById('phone');
  if (!btn || !phone) return;

  btn.addEventListener('click', async () => {
    const isReadonly = phone.hasAttribute('readonly');
    if (isReadonly) {
      phone.removeAttribute('readonly');
      phone.style.background = '#fff';
      btn.textContent = 'Save';
      phone.focus();
      return;
    }

    const payload = {
      action: 'update_phone',
      phone_number: phone.value.trim()
    };

    try {
      const res = await fetch(USER_API, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const data = await res.json();
      if (data.success) {
        phone.setAttribute('readonly', true);
        phone.style.background = '#f5f5f5';
        btn.textContent = 'Edit';
        showTempMessage('Phone updated');
      } else {
        showTempMessage('Failed to update phone: ' + (data.message || ''), true);
      }
    } catch (err) {
      showTempMessage('Network error while updating phone', true);
    }
  });
}

// ============================
// UTILS
// ============================
function showTempMessage(msg, isError = false) {
  alert(msg); // Replace with custom toast/notification if you want
}

