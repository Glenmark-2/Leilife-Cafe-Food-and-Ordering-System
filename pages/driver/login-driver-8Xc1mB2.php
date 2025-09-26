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
        rgba(0, 0, 0, 0.3); /* dark overlay with opacity */
    background-blend-mode: overlay; /* blends color + image */
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

        @media (max-width: 500px) {
            .login-container {
                padding: 30px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>Driver Login</h2>
        <form method="POST" action="/leilife/backend/admin/driver_login.php">
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

            <?php if ($error): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <button type="submit" class="login-button">Login</button>
        </form>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        function updateIcon() {
            if (passwordInput.type === 'password') {
                togglePassword.innerHTML = '<path d="M1 12s3-7 11-7 11 7 11 7-3 7-11 7-11-7-11-7zm11 3a3 3 0 100-6 3 3 0 000 6z" fill="currentColor"/>';
            } else {
                togglePassword.innerHTML = '<path d="M17.94 17.94A9.956 9.956 0 0112 19c-7 0-10-7-10-7s1.72-3.32 4.63-5.41M12 5c7 0 10 7 10 7s-1.72 3.32-4.63 5.41M1 1l22 22" stroke="currentColor" stroke-width="2" fill="none"/>';
            }
            togglePassword.style.fill = passwordInput.value ? '#000' : '#888';
            togglePassword.style.stroke = passwordInput.value ? '#000' : '#888';
        }

        togglePassword.addEventListener('click', () => {
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            updateIcon();
        });
        passwordInput.addEventListener('input', updateIcon);
        updateIcon();
    </script>

</body>

</html>
