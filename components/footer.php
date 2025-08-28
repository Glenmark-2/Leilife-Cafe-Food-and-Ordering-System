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

<style>
.site-footer {
  display: flex;
  justify-content: space-evenly;
  background: #22333b;
  color: #fff;
  padding: 30px 20px;
  text-align: center;
}

/* Logo centered */
.footer-logo img {
  max-width: 150px;
  height: auto;
  margin-bottom: 25px;
}

/* Bottom row (Contact + Quick Links) */
.footer-bottom {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 100px;
}

/* Add divider only between the two */
.footer-bottom > div:first-child {
  padding-right: 40px;
  border-right: 2px solid rgba(255, 255, 255, 0.2);
  padding-left: 40px;
  border-left: 2px solid rgba(255, 255, 255, 0.2);

}
.footer-bottom > div:last-child {
  padding-left: 40px;
}

.footer-column h3 {
  font-size: 18px;
  margin-bottom: 12px;
  font-weight: 600;
}

.footer-column p {
  display: flex;
  align-items: left;
  gap: 8px;
  margin: 6px 0;
  font-size: 14px;
}

.footer-column img {
  width: 18px;
  height: 18px;
}

.footer-column a {
  color: #fff;
  text-decoration: none;
  transition: color 0.2s ease;
}

.footer-column a:hover {
  color: #ffcc00;
}

/* ✅ Responsive: stack sections under each other */
/* Large devices (desktops ≤1280px) */
@media (max-width: 1280px) {
  .footer-bottom {
    gap: 60px;
  }
  .footer-column h3 {
    font-size: 17px;
  }
  .footer-column p {
    font-size: 13px;
  }
}

/* Medium devices (tablets portrait & small laptops ≤1024px) */
@media (max-width: 1024px) {
  .footer-bottom {
    gap: 40px;
  }
  .footer-column h3 {
    font-size: 16px;
  }
  .footer-column p {
    font-size: 13px;
  }
  .footer-logo img {
    max-width: 120px;
  }
}

/* Small devices (phones landscape & small tablets ≤768px) */
@media (max-width: 768px) {
  .site-footer {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  .footer-bottom {
    flex-direction: row;
    align-items: center;
    gap: 30px;
  }

  .footer-bottom > div:first-child {
    border: none;
    padding: 0;
  }

  .footer-column p {
    justify-content: left;
  }
}

/* Extra small devices (phones portrait ≤480px) */
@media (max-width: 480px) {
  .footer-logo img {
    max-width: 100px;
  }

  .footer-column h3 {
    font-size: 15px;
  }

  .footer-column p {
    font-size: 12px;
    gap: 6px;
    align-items: left;
  }

  .footer-column img {
    width: 16px;
    height: 16px;
  }
}
</style>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>