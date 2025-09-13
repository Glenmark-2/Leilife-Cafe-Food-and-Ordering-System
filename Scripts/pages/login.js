document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ login.js loaded");

  const form = document.getElementById("login-form");
  const errorContainer = document.getElementById("login-error-container");
  const spinner = document.getElementById("spinner");
  const forgotPassBtn = document.getElementById("forgot-pass");

  // --- Forgot Password redirect ---
  if (forgotPassBtn) {
    forgotPassBtn.addEventListener("click", (e) => {
      e.preventDefault(); // stop any default form/button behavior
      window.location.href = "/Leilife/public/index.php?page=forgot-password";
      // adjust path if you’re routing through index.php?page=forgot_password
    });
  }

  // --- rest of your login.js (unchanged) ---
  if (!form) {
    console.error("❌ Login form not found!");
    return;
  }
  // --- Utility functions ---
  const showError = (messages) => {
    if (!errorContainer) return;
    let html = "";
    if (Array.isArray(messages)) {
      html = messages.map((err) => `<p class="error">${err}</p>`).join("");
    } else if (typeof messages === "string") {
      html = `<p class="error">${messages}</p>`;
    }
    errorContainer.innerHTML = html;
    errorContainer.style.display = html ? "block" : "none";
  };

  const showSuccess = (message) => {
    if (!errorContainer) return;
    errorContainer.innerHTML = `<p class="success">${message}</p>`;
    errorContainer.style.display = "block";
  };

  const toggleSpinner = (show) => {
    if (!spinner) return;
    spinner.style.display = show ? "block" : "none";
  };

  // --- AJAX submit ---
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (errorContainer) {
      errorContainer.innerHTML = "";
      errorContainer.style.display = "none";
    }

    toggleSpinner(true);

    try {
      const formData = new FormData(form);

      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      });

      let result;
      try {
        result = await response.json();
      } catch {
        throw new Error("Invalid server response (not JSON).");
      }

      if (!result.success) {
        showError(result.errors || "Invalid email or password.");
      } else {
        if (result.redirect) {
          showSuccess("Login successful! Redirecting...");
          setTimeout(() => {
            window.location.href = result.redirect;
          }, 1000);
        } else {
          showSuccess("Login successful! Redirecting...");
          setTimeout(() => {
            window.location.href = "/Leilife/public/index.php?page=home";
          }, 1000);
        }
      }
    } catch (err) {
      console.error("Login error:", err);
      showError("A network error occurred. Please try again.");
    } finally {
      toggleSpinner(false);
    }
  });
});

// --- Close button logic ---
document.getElementById("close-btn")?.addEventListener("click", () => {
  document.getElementById("box-container").style.display = "none";
});
