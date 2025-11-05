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
      <p class="success"> A reset link has been sent to <strong>${email}</strong>.</p>
      <button style="padding:5px; backgrounD-color:#ab917b;COLOR:WHITE; BORDER-RADIUS:5PX;" id="resend-btn" disabled>Resend Link (${RESEND_DELAY}s)</button>
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

        const res = await fetch("/Leilife/backend/send_reset_link.php", {
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

  // --- Basic validations ---
  if (newPass.length < 8) {
    console.log("Validation failed: Password too short");
    return showMessage("❌ Password must be at least 8 characters.", "error");
  }
  if (newPass !== confirm) {
    console.log("Validation failed: Passwords do not match");
    return showMessage("❌ Passwords do not match.", "error");
  }

  try {
    console.log("Sending request to backend with token:", token);

    const res = await fetch("/Leilife/backend/verify_send_link.php", {
      method: "POST",
      headers: { 
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify({ token, password: newPass })
    });

    console.log("Raw response status:", res.status);

    const result = await res.json(); // parse JSON
    console.log("Parsed JSON result:", result);

    if (result.success) {
      console.log("Password changed successfully");
      showMessage("✅ Password changed successfully! Redirecting...", "success");
      setTimeout(() => window.location.replace("/Leilife/public/index.php?page=home"), 2000);
    } else {
      console.error("Backend returned error:", result.message || result.error);
      showMessage(result.message || result.error || "❌ Failed to reset password.", "error");
    }

  } catch (err) {
    console.error("Network or parsing error:", err);
    showMessage(err.message || "❌ Network error.", "error");
  }
});


});

function togglePassword(fieldId, el) {
  const input = document.getElementById(fieldId);
  const isPassword = input.type === "password";

  // Switch input type
  input.type = isPassword ? "text" : "password";

  // Swap the icon
  el.innerHTML = isPassword
    ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640">
    <path d="M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z"/></svg>`
    : `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="black" viewBox="0 0 24 24">
         <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 
         12c-2.761 0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z"/>
         <circle cx="12" cy="12" r="2.5"/>
       </svg>`;
}


const passwordInput = document.getElementById("new_password");
const strengthMsg = document.getElementById("strength-message");
const strengthBar = document.querySelector("#strength-bar span");

if (passwordInput) {
  passwordInput.addEventListener("input", () => {
    const val = passwordInput.value;
    let strength = 0;
    if (val.length >= 8) strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const colors = ["#e74c3c", "#e67e22", "#f1c40f", "#2ecc71"];
    const texts = ["Weak", "Fair", "Good", "Strong"];

    strengthBar.style.width = (strength * 25) + "%";
    strengthBar.style.backgroundColor = colors[strength - 1] || "#ddd";
    strengthMsg.innerText = texts[strength - 1] || "";
  });
}

