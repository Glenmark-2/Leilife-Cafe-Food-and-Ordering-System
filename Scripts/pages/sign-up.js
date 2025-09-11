document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("signup-form");
  const errorContainer = document.getElementById("error-container");

  const showError = (messages) => {
    if (!errorContainer) return;
    let html = "";
    if (Array.isArray(messages)) {
      html = messages.map(err => `<p class="error">${err}</p>`).join("");
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

  // Mobile-friendly inputs
  if (window.innerWidth <= 768) {
    const inputs = document.querySelectorAll('#box input:not([type="checkbox"])');
    inputs.forEach(input => {
      input.style.height = "52px";
      input.style.boxSizing = "border-box";
      input.style.padding = "0";
      input.style.fontSize = "16px";
    });
  }

  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const submitBtn = form.querySelector("button[type=submit]");
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = "Creating account...";
    }

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
          "Accept": "application/json",
          "X-Requested-With": "XMLHttpRequest"
        }
      });

      const result = await response.json();

      if (!result.success) {
        showError(result.errors || "Unknown error occurred.");
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = "Create Account";
        }
      } else {
        if (result.redirect) {
          window.location.href = result.redirect;
        } else {
          showSuccess("Account created successfully! Redirecting...");
          setTimeout(() => {
            window.location.href = "/Leilife/public/index.php?page=verify_notice";
          }, 1500);
        }
      }
    } catch (err) {
      console.error("Signup error:", err);
      showError("A network error occurred. Please try again.");
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = "Create Account";
      }
    }
  });
});
