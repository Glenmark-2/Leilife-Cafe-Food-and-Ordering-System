document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("login-form");
  const errorContainer = document.getElementById("login-error-container");

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

  if (!form) return;

  // --- AJAX submit ---
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (errorContainer) {
      errorContainer.innerHTML = "";
      errorContainer.style.display = "none";
    }

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
          window.location.href = result.redirect;
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
    }
  });
});

// --- Close button logic ---
document.getElementById("close-btn")?.addEventListener("click", () => {
  document.getElementById("box-container").style.display = "none";
});
