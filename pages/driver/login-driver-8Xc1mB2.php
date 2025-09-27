<?php
session_start();
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Driver Login</title>
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

.login-container {
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

.form-row {
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
    text-align: left;
    position: relative;
}

.form-row label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
}

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

.toggle-password {
    position: absolute;
    right: 12px;
    top: 36px;
    cursor: pointer;
    width: 20px;
    height: 20px;
    fill: none;
    stroke: #888;
    stroke-width: 2;
    transition: stroke 0.2s;
}

.login-button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 8px;
    background-color: #a6927b;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.login-button:hover {
    color: #ddd8ba;
    background-color: #5e4f3e;
}

.error-message {
    color: #f44336;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

#forgot-password-btn {
    margin-top: 10px;
    background: none;
    border: none;
    color: #22333B;
    cursor: pointer;
    font-size: 0.9rem;
    text-decoration: underline;
}

#forgot-password-modal {
    display: none;
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.5);
    justify-content:center;
    align-items:center;
}

#forgot-password-modal .modal-content {
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    text-align:center;
}

#forgot-password-modal input {
    padding: 10px;
    width: 100%;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

#forgot-password-modal button {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
}

#send-reset-btn {
    background: #28a745;
    color: #fff;
}

#close-forgot-modal {
    background: #888;
    color: #fff;
    margin-left: 10px;
}

@media (max-width:500px){
    .login-container { padding: 30px 20px; }
}
</style>
</head>

<body>

<div class="login-container">
    <h2>Driver Login</h2>
    <form method="POST" action="/leilife/backend/driver/driver_login.php">
        <div class="form-row">
            <label for="email">Email or Username</label>
            <input type="text" name="email" id="email" autocomplete="username" required>
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>
            <svg id="togglePassword" class="toggle-password" viewBox="0 0 24 24">
                <path d="M1 12s3-7 11-7 11 7 11 7-3 7-11 7-11-7-11-7z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        </div>

        <?php if($error): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <button type="submit" class="login-button">Login</button>
    </form>
    <button id="forgot-password-btn">Forgot Password?</button>
</div>

<!-- Forgot Password Modal -->
<div id="forgot-password-modal">
    <div class="modal-content">
        <h3>Forgot Password</h3>
        <p>Enter your Email or Username</p>
        <input type="text" id="forgot-identifier" placeholder="Email or Username">
        <div id="forgot-error" style="color:red;margin-bottom:10px;"></div>
        <button id="send-reset-btn">Send Reset Link</button>
        <button id="close-forgot-modal">Cancel</button>
    </div>
</div>

<script>
const loginContainer = document.querySelector('.login-container');
const forgotBtn = document.getElementById('forgot-password-btn');
const forgotModal = document.getElementById('forgot-password-modal');
const closeForgot = document.getElementById('close-forgot-modal');
const sendResetBtn = document.getElementById('send-reset-btn');
const forgotError = document.getElementById('forgot-error');

// Show forgot password modal & hide login
forgotBtn.addEventListener('click', () => {
    forgotModal.style.display = 'flex';
    loginContainer.style.display = 'none';
    forgotError.innerText = '';
    document.getElementById('forgot-identifier').value = '';
});

// Close forgot modal, show login again
closeForgot.addEventListener('click', () => {
    forgotModal.style.display = 'none';
    loginContainer.style.display = 'block';
});

// Send reset link
sendResetBtn.addEventListener('click', () => {
    const identifier = document.getElementById('forgot-identifier').value.trim();
    forgotError.innerText = '';
    if (!identifier) {
        forgotError.style.color = 'red';
        forgotError.innerText = 'Please enter your email or username.';
        return;
    }

    sendResetBtn.disabled = true;
    sendResetBtn.innerText = 'Sending...';

    fetch('/leilife/backend/admin/send_reset_link_admin_driver.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ identifier })
    })
    .then(res => res.json())
    .then(data => {
        sendResetBtn.disabled = false;
        sendResetBtn.innerText = 'Send Reset Link';
        forgotError.style.color = data.success ? 'green' : 'red';
        forgotError.innerText = data.message;
    })
    .catch(err => {
        sendResetBtn.disabled = false;
        sendResetBtn.innerText = 'Send Reset Link';
        forgotError.style.color = 'red';
        forgotError.innerText = 'Error: ' + err.message;
    });
});

// Toggle show/hide password
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
    const isPassword = passwordInput.type === 'password';
    passwordInput.type = isPassword ? 'text' : 'password';

    // Switch icon between eye and eye-off
    if (isPassword) {
        togglePassword.innerHTML = '<path d="M1 12s3-7 11-7 11 7 11 7-3 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>';
    } else {
        togglePassword.innerHTML = '<path d="M17.94 17.94A9.956 9.956 0 0112 19c-7 0-10-7-10-7s1.72-3.32 4.63-5.41M12 5c7 0 10 7 10 7s-1.72 3.32-4.63 5.41M1 1l22 22" stroke="#888" stroke-width="2" fill="none" />';
    }
});

// Optional: close modal if clicking outside
window.addEventListener('click', (e) => {
    if (e.target === forgotModal) {
        forgotModal.style.display = 'none';
        loginContainer.style.display = 'block';
    }
});
</script>

</body>
</html>
