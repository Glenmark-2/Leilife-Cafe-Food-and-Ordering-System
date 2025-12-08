<footer class="site-footer">
  <div class="footer-logo">
    <img src="\Leilife\public\assests\image-removebg-preview (31) 1.png" alt="Logo">
  </div>

  <div class="footer-bottom">
    <!-- Contact Info -->
    <div class="footer-column">
      <h3>Contact Us</h3>
      <p><img src="../public/assests/facebook.png" alt="Facebook"> Leilife Café & Restaurant</p>
      <p><img src="../public/assests/white-pin.png" alt="Address"> Lunduyan Langaray, Brgy 14. Caloocan City</p>
      <p><img src="../public/assests/white-call.png"  alt="Phone"> 0912345678</p>
      <p><img src="../public/assests/white-messages.png"alt="Email"> leilifecafe@gmail.com</p>
    </div>

    <!-- Links -->
    <div class="footer-column">
      <h3>Quick Links</h3>
      <p><a href="../public/index.php?page=home">Home</a></p>
      <p><a href="index.php?page=home#about-us">About</a></p>
      <p><a href="index.php?page=home#contact-section">Contact</a></p>
    </div>
  </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const cartBtn = document.getElementById("cartBtn");
  const cartModal = document.getElementById("cartModal");

  if (cartBtn && cartModal) {
    cartBtn.addEventListener("click", (e) => {
      e.preventDefault();
      cartModal.classList.toggle("show");
      document.body.classList.toggle("cart-open");
    });
  }

  // ✅ Auto-open cart if previous page set the flag
  if (sessionStorage.getItem("openCartOnLoad") === "true") {
    sessionStorage.removeItem("openCartOnLoad");

    // Dispatch a custom event for consistency
    document.dispatchEvent(new Event("cart:autoOpen"));

    // Actually open the cart modal
    if (cartModal) {
      cartModal.classList.add("show");
      document.body.classList.add("cart-open");
    }
  }
});


</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
