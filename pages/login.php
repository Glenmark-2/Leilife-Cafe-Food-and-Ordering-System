<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

</head>
<body>
   <div id="login-overlay" class="overlay" >
      <div id="box-container">
         <button id="close-btn">&times;</button>

         <div id="box-content">
            <img src="/Leilife/public/assests/Mask group.png" alt="Logo">
            <h1>Welcome back!</h1>

            <form action="/Leilife/backend/login.php" method="POST" id="login-form">
               <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

               <label for="login">Email or Username <span style="color: red;">*</span></label>
               <input type="text" id="login" name="login" placeholder="Enter your email or username" required>

               <label for="password">Password <span style="color: red;">*</span></label>
               <input type="password" id="password" name="password" placeholder="Enter your password" required>

               <button type="submit" class="login-btn">Login</button>
               <button type="button" class="google-btn" onclick="window.location.href='/Leilife/backend/google_login.php'">
               <img id="google-logo" src="/Leilife/public/assests/google.logo.webp" alt="Google Logo"
                    style="width: 25px; height:25px;">
               Continue with Google
            </button>
            </form>

            <div id="spinner" class="spinner"></div>
            <div id="login-error-container" class="error-messages"></div>

            

            <button id="forgot-pass">Forgot your password?</button>

            <div class="terms">
               <p>By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
            </div>

            <div class="signup">
               <p>Don't have an account? <a href="../public/index.php?page=signUp">Sign up</a></p>
            </div>
         </div>
      </div>
   </div>
<script src="/Leilife/Scripts/pages/login.js" defer></script>
</body>
