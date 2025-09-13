document.addEventListener("DOMContentLoaded", () => {
  console.log("✅ forgot-password.js loaded");

  const requestForm = document.getElementById("request-form");
  const resetForm   = document.getElementById("reset-form");
  const msgContainer = document.getElementById("message-container");

  /* ---------------- Config ---------------- */
  const RESEND_LIMIT = 3; 
  const RESEND_DELAY = 30; 

  /* ---------------- State ---------------- */
  let resendCount = 0;

  /* ---------------- Utility ---------------- */
  const showMessage = (msg, type = "success") => {
    const msgBox = document.getElementById("status-msg");
    if (msgBox) {
      msgBox.innerHTML = `<p class="${type}">${msg}</p>`;
      msgBox.style.display = "block";
    } else {
      msgContainer.innerHTML = `<div id="status-msg"><p class="${type}">${msg}</p></div>`;
    }
  };

  const clearMessage = () => {
    const msgBox = document.getElementById("status-msg");
    if (msgBox) {
      msgBox.innerHTML = "";
      msgBox.style.display = "none";
    }
  };

  /* ---------------- Resend Countdown ---------------- */
  const startCountdown = (btn, duration, onFinish) => {
    let remaining = duration;
    btn.textContent = `Resend Link (${remaining}s)`;
    btn.disabled = true;

    const timer = setInterval(() => {
      remaining--;
      btn.textContent = `Resend Link (${remaining}s)`;

      if (remaining <= 0) {
        clearInterval(timer);
        btn.textContent = "Resend Link";
        btn.disabled = false;
        if (onFinish) onFinish();
      }
    }, 1000);

    return timer;
  };

  /* ---------------- Link Sent UI ---------------- */
  const showLinkSentUI = (email) => {
    requestForm.style.display = "none";
    msgContainer.innerHTML = `
      <p class="success">✅ A reset link has been sent to <strong>${email}</strong>.</p>
      <button id="resend-btn" disabled>Resend Link (${RESEND_DELAY}s)</button>
      <p id="resend-info" style="margin-top:8px; font-size:0.9em; color:#555;">
        You can resend up to ${RESEND_LIMIT} times.
      </p>
      <div id="status-msg" style="margin-top:10px;"></div>
    `;
    msgContainer.style.display = "block";

    const resendBtn = document.getElementById("resend-btn");
    const resendInfo = document.getElementById("resend-info");

    let countdown = startCountdown(resendBtn, RESEND_DELAY);

    resendBtn.addEventListener("click", async () => {
      if (resendCount >= RESEND_LIMIT) {
        resendBtn.disabled = true;
        resendBtn.textContent = "Resend Disabled";
        resendInfo.textContent = "⚠️ You have reached the maximum resend limit.";
        return;
      }

      resendBtn.textContent = "Sending...";
      resendBtn.disabled = true;

      try {
        const formData = new FormData();
        formData.append("email", email);

        const res = await fetch("/Leilife/backend/send_mail.php", {
          method: "POST",
          body: formData,
          headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
        });

        const result = await res.json();
        if (result.success) {
          resendCount++;

          if (resendCount === RESEND_LIMIT) {
            resendBtn.disabled = true;
            resendBtn.style.display = "none";
            resendInfo.textContent = "⚠️ You have reached the maximum resend limit.";
          } else {
            if (countdown) clearInterval(countdown);
            countdown = startCountdown(resendBtn, RESEND_DELAY);
          }
        } else {
          throw new Error(result.error || "❌ Failed to resend link.");
        }
      } catch (err) {
        console.error("Error:", err);
        showMessage(err.message || "❌ Network error.", "error");
        resendBtn.textContent = "Resend Link";
        resendBtn.disabled = false;
      }
    });
  };

  /* ---------------- Request Reset Form ---------------- */
  requestForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearMessage();

    const email = document.getElementById("reset-email").value.trim();
    if (!email) return showMessage("❌ Please enter your email.", "error");

    const btn = requestForm.querySelector("button[type='submit']");
    btn.disabled = true;
    btn.textContent = "Sending...";

    try {
      const res = await fetch("/Leilife/backend/send_reset_link.php", {
        method: "POST",
        body: new FormData(requestForm),
        headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
      });

      const result = await res.json();
      if (result.success) {
        resendCount = 0;
        showLinkSentUI(email);
      } else {
        throw new Error(result.error || "❌ Failed to send reset link.");
      }
    } catch (err) {
      console.error("Error:", err);
      showMessage(err.message || "❌ Network error.", "error");
      btn.disabled = false;
      btn.textContent = "Send Verification";
    }
  });

  /* ---------------- Reset Password Form ---------------- */
  resetForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    clearMessage();

    const newPass = document.getElementById("new_password").value.trim();
    const confirm = document.getElementById("confirm_password").value.trim();
    const token   = document.getElementById("token").value;

    if (newPass.length < 8) return showMessage("❌ Password must be at least 8 characters.", "error");
    if (newPass !== confirm) return showMessage("❌ Passwords do not match.", "error");

    try {
      const res = await fetch("/Leilife/backend/verify_send_link.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", Accept: "application/json" },
        body: JSON.stringify({ token, password: newPass })
      });

      const result = await res.json();
      if (result.success) {
        showMessage("✅ Password changed successfully! Redirecting...", "success");
        setTimeout(() => (window.location.href = "/Leilife/public/index.php?page=home"), 2000);
      } else {
        throw new Error(result.error || "❌ Failed to reset password.");
      }
    } catch (err) {
      console.error("Error:", err);
      showMessage(err.message || "❌ Network error.", "error");
    }
  });
});
