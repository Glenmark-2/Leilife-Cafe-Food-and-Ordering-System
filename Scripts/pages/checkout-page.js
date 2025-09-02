function toggleDelivery() {
    const isPickup = document.querySelector('input[name="delivery"][value="pickup"]').checked;
    document.getElementById('pickup-options').style.display = isPickup ? 'block' : 'none';
    document.getElementById('home-options').style.display = isPickup ? 'none' : 'block';
  }

  function toggleEdit() {
    const fields = document.querySelectorAll('#home-options input, #home-options textarea');
    const editBtn = document.querySelector('#home-options .edit-btn');
    const isReadonly = fields[0].hasAttribute('readonly');

    fields.forEach(field => {
      if (isReadonly) {
        field.removeAttribute('readonly');
        field.style.background = '#fff';
      } else {
        field.setAttribute('readonly', true);
        field.style.background = '#f5f5f5';
      }
    });

    editBtn.textContent = isReadonly ? 'Save' : 'Edit';
  }