

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

            <!-- Continue with Google -->
            <button type="button" class="google-btn">
                <img id="google-logo"  src="../public/assests/google.logo.webp" alt="Google Logo"
                style="width: 25px; height:25px; max-width:30px; max-height:30px"
                >
                Continue with Google
            </button>
        </form>

        <button id="forgot-pass">Forgot your password?</button>

        <div class="terms">
            <p style="margin-top: 0;">By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
        </div>

        <div class="signup" style="margin-top: 0;">
            <p style="margin:0;">Don't have an account? <button>Sign up</button></p>
        </div>
    </div>
</div>
</center>

<script src="../Scripts/pages/login.js"></script>
