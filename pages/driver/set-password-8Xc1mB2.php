<?php
$token = $_GET['token'] ?? null;

if (!$token) {
    header("Location: /leilife/pages/driver/login-driver.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver Reset Password</title>
    <style>
        body {
            font-family: "Segoe UI", Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: 
                url("../../public/assests/bg.jpg") no-repeat center center/cover,
                rgba(0, 0, 0, 0.3);
            background-blend-mode: overlay;
        }

        .reset-container {
            background: #fff;
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            width: 400px;
            max-width: 90%;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #22333B;
        }

        .form-row { margin-bottom: 20px; text-align: left; position: relative; }
        .form-row label { font-weight: 600; margin-bottom: 6px; color: #333; }
        .form-row input {
            padding: 10px 40px 10px 14px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
            width: 100%;
            box-sizing: border-box;
        }
        .form-row input:focus {
            border-color: #75c277;
            box-shadow: 0 0 5px rgba(117, 194, 119, 0.3);
        }

        .toggle-password { position: absolute; right: 12px; top: 36px; cursor: pointer; width: 20px; height: 20px; fill: #888; }

        .reset-button {
            width: 100%; padding: 12px; border: none; border-radius: 8px;
            background-color: #a6927b; color: #fff; font-size: 16px; font-weight: 600;
            cursor: pointer; transition: 0.2s;
        }
        .reset-button:hover { color: #ddd8ba; background-color: #5e4f3e; }

        .message { margin-top: 15px; font-weight: 600; }

        .resend-btn {
            background: none; border: none; color: #22333c; cursor: pointer;
            text-decoration: underline; font-size: 14px; margin-top: 10px;
        }

        /* Password strength */
        .strength { font-size: 0.85rem; margin-top: 5px; font-weight: 600; }
        .strength-bar { height: 6px; border-radius: 4px; margin-top: 4px; background: #ddd; }
        .strength-bar span {
            display: block; height: 100%; width: 0%; border-radius: 4px;
            transition: width 0.3s ease-in-out, background 0.3s;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <h2>Driver - Set New Password</h2>
        <form id="resetDriverForm">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="form-row">
                <label for="password">New Password:</label>
                <input type="password" id="password" name="new_password" required>
                <svg id="togglePassword" class="toggle-password" viewBox="0 0 24 24">
                    <path d="M17.94 17.94A9.956 9.956 0 0112 19c-7 0-10-7-10-7s1.72-3.32 4.63-5.41M12 5c7 0 10 7 10 7s-1.72-3.32-4.63-5.41M1 1l22 22" 
                          stroke="#888" stroke-width="2" fill="none" />
                </svg>
                <div class="strength" id="strength-text"></div>
                <div class="strength-bar"><span id="strength-bar"></span></div>
            </div>

            <div class="form-row">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <svg id="toggleConfirmPassword" class="toggle-password" viewBox="0 0 24 24">
                    <path d="M17.94 17.94A9.956 9.956 0 0112 19c-7 0-10-7-10-7s1.72-3.32 4.63-5.41M12 5c7 0 10 7 10 7s-1.72-3.32-4.63-5.41M1 1l22 22" 
                          stroke="#888" stroke-width="2" fill="none" />
                </svg>
            </div>

            <button type="submit" class="reset-button">Update Password</button>
            <div class="message" id="message"></div>
        </form>
    </div>

    <script>
        // Toggle password
        function setupTogglePassword(toggleId, inputId) {
            const toggle = document.getElementById(toggleId);
            const input = document.getElementById(inputId);
            toggle.addEventListener('click', () => {
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        }
        setupTogglePassword("togglePassword", "password");
        setupTogglePassword("toggleConfirmPassword", "confirm_password");

        // Strength check
        const passwordInput = document.getElementById("password");
        const strengthText = document.getElementById("strength-text");
        const strengthBar = document.getElementById("strength-bar");
        passwordInput.addEventListener("input", () => {
            const value = passwordInput.value;
            let strength = 0;
            if (value.length >= 8) strength++;
            if (/[A-Z]/.test(value)) strength++;
            if (/[0-9]/.test(value)) strength++;
            if (/[^A-Za-z0-9]/.test(value)) strength++;

            let text = "", color = "#ddd";
            switch (strength) {
                case 1: text = "Weak"; color = "#f44336"; break;
                case 2: text = "Fair"; color = "#ff9800"; break;
                case 3: text = "Good"; color = "#4caf50"; break;
                case 4: text = "Strong"; color = "#2e7d32"; break;
                default: text = "Min. of 8 characters."; 
            }
            strengthText.textContent = text;
            strengthText.style.color = color;
            strengthBar.style.width = (strength * 25) + "%";
            strengthBar.style.background = color;
        });

// Submit form
const resetForm = document.getElementById("resetDriverForm");
const messageDiv = document.getElementById("message");

resetForm.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(resetForm);

    fetch('/leilife/backend/driver/set_driver_password.php', {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Success → redirect to login
            messageDiv.textContent = data.message;
            messageDiv.style.color = "green";
            setTimeout(() => {
                window.location.href = "login-driver-8Xc1mB2.php";
            }, 2000);
        } else {
            const msg = data.message.toLowerCase();

            if (msg.includes("expired")) {
    // Clear previous children
    messageDiv.textContent = data.message;
    messageDiv.style.color = "#555";

    // Create resend button
    const resendBtn = document.createElement("button");
    resendBtn.id = "resendLinkBtn";
    resendBtn.type = "button";
    resendBtn.className = "resend-btn";
    resendBtn.textContent = "Resend Reset Link";

    messageDiv.appendChild(document.createElement("br"));
    messageDiv.appendChild(resendBtn);

    resendBtn.addEventListener("click", async () => {
        const token = new URLSearchParams(window.location.search).get("token");
        messageDiv.textContent = "Sending new reset link... Please wait.";
        messageDiv.style.color = "#555";

        try {
            const res = await fetch("/leilife/backend/admin/resend_reset_link.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ token })
            });

            const result = await res.json();
            messageDiv.textContent = result.message;
            messageDiv.style.color = result.success ? "green" : "red";
        } catch (err) {
            messageDiv.textContent = "Something went wrong. Please try again.";
            messageDiv.style.color = "red";
        }
    });
}
else if (msg.includes("may have already been changed")) {
                // Already used or invalid → back to login
                messageDiv.innerHTML = `
                    ${data.message}<br>
                    <a href="login-driver-8Xc1mB2.php" class="resend-btn">Go back to Login</a>
                `;
                messageDiv.style.color = "#555";

            } else {
                // Other errors
                messageDiv.textContent = data.message;
                messageDiv.style.color = "red";
            }
        }
    })
    .catch(() => {
        messageDiv.textContent = "Something went wrong. Please try again.";
        messageDiv.style.color = "red";
    });
});

    </script>
</body>
</html>
