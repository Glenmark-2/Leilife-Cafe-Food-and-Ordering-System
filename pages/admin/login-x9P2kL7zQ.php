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
<title>Admin Login</title>
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
    fill: #888;
    transition: fill 0.2s;
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
    <h2>Admin Login</h2>
    <form method="POST" action="/leilife/backend/admin/admin_login.php">
        <div class="form-row">
            <label for="email">Email or Username</label>
            <input type="text" name="email" id="email" autocomplete="username" required>
        </div>

        <div class="form-row">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>
            <svg id="togglePassword" class="toggle-password" viewBox="0 0 24 24">
                <path d="M17.94 17.94A9.956 9.956 0 0112 19c-7 0-10-7-10-7s1.72-3.32 4.63-5.41M12 5c7 0 10 7 10 7s-1.72 3.32-4.63 5.41M1 1l22 22" stroke="#888" stroke-width="2" fill="none" />
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

<!-- Reset Password Modal -->
<div id="reset-password-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div class="modal-content">
        <h3>Reset Password</h3>
        <input type="password" id="new-password" placeholder="New Password">
        <input type="password" id="confirm-password" placeholder="Confirm Password">
        <div id="reset-error" style="color:red;margin-bottom:10px;"></div>
        <button id="update-password-btn">Update Password</button>
    </div>
</div>

<script>
const loginContainer = document.querySelector('.login-container');
const forgotBtn = document.getElementById('forgot-password-btn');
const forgotModal = document.getElementById('forgot-password-modal');
const closeForgot = document.getElementById('close-forgot-modal');
const sendResetBtn = document.getElementById('send-reset-btn');
const forgotError = document.getElementById('forgot-error');

const resetModal = document.getElementById('reset-password-modal');
const updatePasswordBtn = document.getElementById('update-password-btn');
const resetError = document.getElementById('reset-error');

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

        // **DO NOT hide the forgot password modal**
        // The modal stays open so user can see message
    })
    .catch(err => {
        sendResetBtn.disabled = false;
        sendResetBtn.innerText = 'Send Reset Link';
        forgotError.style.color = 'red';
        forgotError.innerText = 'Error: ' + err.message;
    });
});


// Check if URL has ?token=xxx for reset
const urlParams = new URLSearchParams(window.location.search);
const token = urlParams.get('token');
if(token) {
    resetModal.style.display = 'flex';
    loginContainer.style.display = 'none';
}

// Update password
updatePasswordBtn.addEventListener('click', () => {
    const newPass = document.getElementById('new-password').value.trim();
    const confirmPass = document.getElementById('confirm-password').value.trim();
    resetError.innerText = '';

    if(!newPass || !confirmPass){
        resetError.innerText = 'Please fill in both fields.';
        return;
    }
    if(newPass !== confirmPass){
        resetError.innerText = 'Passwords do not match.';
        return;
    }

    updatePasswordBtn.disabled = true;
    updatePasswordBtn.innerText = 'Updating...';

    fetch('/leilife/backend/admin/driver_update_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ token, password: newPass })
    })
    .then(res => res.json())
    .then(data => {
        updatePasswordBtn.disabled = false;
        updatePasswordBtn.innerText = 'Update Password';
        resetError.style.color = data.success ? 'green' : 'red';
        resetError.innerText = data.message;

        if(data.success){
            setTimeout(()=> window.location.href = '/leilife/pages/admin/login-x9P2kL7zQ.php', 2000);
        }
    })
    .catch(err => {
        updatePasswordBtn.disabled = false;
        updatePasswordBtn.innerText = 'Update Password';
        resetError.style.color = 'red';
        resetError.innerText = 'Error: ' + err.message;
    });
});

// Optional: close modal if clicking outside
window.addEventListener('click', (e) => {
    if (e.target === forgotModal) forgotModal.style.display = 'none';
    if (e.target === resetModal) resetModal.style.display = 'none';
});
</script>
