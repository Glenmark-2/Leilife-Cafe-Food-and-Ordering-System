document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("verify-modal");
  const closeBtn = document.getElementById("close-modal");
  const loginLink = document.getElementById("login-link");

  // Ensure modal is visible
  modal.style.display = "flex"; // or "block", depending on your CSS

  // Close button hides modal
  closeBtn?.addEventListener("click", () => {
    modal.style.display = "none";
    window.location.replace("index.php?page=home");

  });

  // Replace history when going to login (so back doesn’t go back here)
  loginLink?.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.replace("index.php?page=home");
  });

  // Optional: Auto-redirect after delay
  // setTimeout(() => {
  //   window.location.replace("index.php?page=login");
  // }, 3000);
});
