<style>
/* Modal container */
#box-container {
    position: fixed; /* make it float above the page */
    top: 50%;         /* vertical center */
    left: 50%;        /* horizontal center */
    transform: translate(-50%, -50%); /* truly center */
    display: flex;
    justify-content: center;
    background-color: white;
    padding: 40px 50px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    width: fit-content;
    border-radius: 20px;
    font-family: Arial, sans-serif;
    z-index: 1000; /* ensure it stays on top */
    margin-top: 40px; /* remove old margin */
}

/* Close button */
#close-btn {
    position: absolute;
    top: 15px;
    right: 15px;
    background: transparent;
    border: none;
    font-size: 24px;
    font-weight: bold;
    cursor: pointer;
    color: #333;
}

#close-btn:hover {
    color: red;
}

/* Content inside the modal */
#box-content {
    display: flex;
    flex-direction: column;
    align-items: center; /* center image and heading */
    gap: 20px;
    width: 100%;
}

/* Image and heading */
#box-content img {
    height: 100px;
    width: 100px;
}

#box-content h1 {
    text-align: center;
    margin: 0;
}

/* Form */
form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 300px;
    align-items: flex-start; /* left-align labels and inputs */
}

label {
    font-weight: bold;
}

input {
    width: 100%;
    height: 40px;
    background-color: #f4f4f4;
    border: none;
    border-radius: 5px;
    padding-left: 10px;
    font-size: 14px;
    box-sizing: border-box;
}

/* Forgot password button */
#forgot-pass {
    border: none;
    background-color: transparent;
    color: red;
    padding: 0;
    margin-top: -10px;
    font-size: 13px;
    cursor: pointer;
}

/* Terms & Conditions */
.terms {
    text-align: center;
    font-size: 12px;
    color: #666;
    margin-top: 10px;
    line-height: 1.4;
    max-width: 300px;
}

/* Login button */
button.login-btn {
    width: 100%;
    padding: 10px 0;
    background-color: #22333c;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
}

button.login-btn:hover {
    background-color: #445566;
}

/* Sign up section */
.signup {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

.signup button {
    background: transparent;
    border: none;
    color: red;
    cursor: pointer;
    font-weight: bold;
}

.signup button:hover {
    text-decoration: underline;
}
</style>
<center>
<div id="box-container">
    <!-- Close button -->
    <button id="close-btn">&times;</button>

    <div id="box-content">
        <img src="\Leilife\public\assests\Mask group.png" alt="Logo">
        <h1>Welcome back!</h1>

        <form action="login.php" method="POST">
            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <button id="forgot-pass">Forgot your password?</button>

        <div class="terms">
            <p>By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
        </div>

        <div class="signup">
            <p>Don't have an account? <button>Sign up</button></p>
        </div>
    </div>
</div>
</center>
<script>
document.getElementById('close-btn').addEventListener('click', () => {
    document.getElementById('loginModal').style.display = 'none';
});
</script>
