document.addEventListener('DOMContentLoaded', () => {
  const orderBtn = document.getElementById("order-btn");
  const modal = document.getElementById("orders-modal");
  const closeModal = document.getElementById("close-modal");

  const orderCount = window.OrdersConfig?.orderCount || 0;
  const orders = window.OrdersConfig?.orders || [];

  if (orderCount > 0) {
    orderBtn.textContent = orderCount === 1 ? "View Order" : `View Orders (${orderCount})`;
    orderBtn.classList.remove("hidden");
  }

  orderBtn.addEventListener("click", () => {
    if (orderCount === 1) {
      // ✅ Use the href from the PHP-rendered anchor
      const firstLink = document.querySelector('#orders-list a.order-link');
      if (firstLink && firstLink.href) {
        window.location.href = firstLink.href;
        return;
      }
    }

    modal.classList.remove("hidden");
    modal.setAttribute('aria-hidden', 'false');
  });

  closeModal.addEventListener("click", () => {
    modal.classList.add("hidden");
    modal.setAttribute('aria-hidden', 'true');
  });

  window.addEventListener("click", (e) => {
    if (e.target === modal) {
      modal.classList.add("hidden");
      modal.setAttribute('aria-hidden', 'true');
    }
  });

  document.querySelectorAll('#orders-modal a.order-link').forEach(a => {
    a.addEventListener('click', (e) => {
      e.preventDefault();
      const href = a.getAttribute('href');
      if (href) window.location.href = href;
    });
  });
});
