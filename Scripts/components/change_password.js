document.addEventListener("DOMContentLoaded", () => {
  const passwordModal = document.getElementById("password-modal");
  if (!passwordModal) return;

  // Bootstrap modal instance
  const modal = new bootstrap.Modal(passwordModal);

  // Forms
  const changeForm = document.getElementById("change-password-form");
  const setForm = document.getElementById("set-password-form");

  // ✅ Show message (error/success)
  function showMessage(containerId, message, type = "error") {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.textContent = message;
    el.classList.remove("text-danger", "text-success");
    if (message) {
      el.classList.add(type === "success" ? "text-success" : "text-danger");
    }
  }

  // ✅ Clear message
  function clearMessage(containerId) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.textContent = "";
    el.classList.remove("text-danger", "text-success");
  }

  // ✅ Handle form submission
  async function handleFormSubmit(form, url, errorId, successId) {
    const formData = new FormData(form);

    const newPass = formData.get("new_password");
    const confirmPass = formData.get("confirm_password");

    // --- Client-side validation ---
    if (newPass !== confirmPass) {
      showMessage(errorId, "Passwords do not match.");
      return;
    }
    if (newPass.length < 8) {
      showMessage(errorId, "Password must be at least 8 characters.");
      return;
    }

    try {
      const response = await fetch(url, {
        method: "POST",
        body: formData,
        credentials: "same-origin",
      });

      const result = await response.json();

      if (result.success) {
        showMessage(successId, result.message || "Password updated!", "success");
        clearMessage(errorId);
        form.reset();

        // Close modal after short delay
        setTimeout(() => modal.hide(), 1500);
      } else {
        showMessage(errorId, result.message || "Something went wrong.");
      }
    } catch (err) {
      console.error("❌ Error submitting form:", err);
      showMessage(errorId, "Network error. Try again.");
    }
  }

  // ✅ Attach form listeners
  if (changeForm) {
    changeForm.addEventListener("submit", (e) => {
      e.preventDefault();
      handleFormSubmit(
        changeForm,
        "/Leilife/backend/change_password.php",
        "change-password-error",
        "change-password-success"
      );
    });
  }

  if (setForm) {
    setForm.addEventListener("submit", (e) => {
      e.preventDefault();
      handleFormSubmit(
        setForm,
        "/Leilife/backend/set_password.php",
        "set-password-error",
        "set-password-success"
      );
    });
  }

  // ✅ Modal trigger buttons
  const triggers = document.querySelectorAll("#open-password-modal, .open-password-modal");
  triggers.forEach(btn => {
    btn.addEventListener("click", (e) => {
      if (e) e.preventDefault();

      // Clear messages
      clearMessage("change-password-error");
      clearMessage("change-password-success");
      clearMessage("set-password-error");
      clearMessage("set-password-success");

      // Reset forms
      if (changeForm) changeForm.reset();
      if (setForm) setForm.reset();

      modal.show();
    });
  });
});
