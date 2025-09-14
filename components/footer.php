<footer class="site-footer" style="margin-top: 100px;">
  <div class="footer-logo">
    <img src="\Leilife\public\assests\image-removebg-preview (31) 1.png" alt="Logo">
  </div>

  <div class="footer-bottom">
    <!-- Contact Info -->
    <div class="footer-column">
      <h3>Contact Us</h3>
      <p><img src="fb-icon.png" alt="Facebook"> Leilife Café & Restaurant</p>
      <p><img src="location-icon.png" alt="Address"> Lunduyan Langaray, Brgy 14. Caloocan City</p>
      <p><img src="phone-icon.png" alt="Phone"> 0912345678</p>
      <p><img src="mail-icon.png" alt="Email"> leilifecafe@gmail.com</p>
    </div>

    <!-- Links -->
    <div class="footer-column">
      <h3>Quick Links</h3>
      <p><a href="#">Home</a></p>
      <p><a href="#">About</a></p>
      <p><a href="#">Contact</a></p>
    </div>
  </div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const cartBtn = document.getElementById("cartBtn"); // button in header
  const cartModal = document.getElementById("cartModal"); // modal in index

  if (cartBtn && cartModal) {
    cartBtn.addEventListener("click", (e) => {
      e.preventDefault();
      cartModal.classList.toggle("show");         // adds/removes CSS class
      document.body.classList.toggle("cart-open"); // optional for shifting page content
    });
  }
});

</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>