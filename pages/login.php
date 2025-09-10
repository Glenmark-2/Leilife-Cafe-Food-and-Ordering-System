<center>
   <div id="box-container">
      <!-- Close button -->
      <button id="close-btn">&times;</button>

      <div id="box-content">
         <img src="\Leilife\public\assests\Mask group.png" alt="Logo">
         <h1>Welcome back!</h1>

         <?php
         if (session_status() === PHP_SESSION_NONE) {
             session_start();
         }
         if (!isset($_SESSION['csrf_token'])) {
             $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
         }
         ?>

         <!-- Error container (hidden by default, shown by JS or fallback PHP) -->
         <div id="login-error-container" class="error-messages">
            <?php
            if (!empty($_SESSION['login_errors'])) {
                foreach ($_SESSION['login_errors'] as $error) {
                    echo '<p class="error">' . htmlspecialchars($error) . '</p>';
                }
                unset($_SESSION['login_errors']); // clear after showing
            }
            ?>
         </div>

         <!-- Local Login Form -->
         <form action="/Leilife/backend/login.php" method="POST" id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="email">Email <span style="color: red;">*</span></label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>

            <label for="password">Password <span style="color: red;">*</span></label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>

            <button type="submit" class="login-btn">Login</button>
         </form>

         <!-- Continue with Google -->
         <button type="button" class="google-btn" onclick="window.location.href='/Leilife/backend/google_login.php'">
            <img id="google-logo" src="/Leilife/public/assests/google.logo.webp" alt="Google Logo"
                 style="width: 25px; height:25px; max-width:30px; max-height:30px">
            Continue with Google
         </button>

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

<!-- Hide error box if empty -->
<style>
   #login-error-container:empty {
      display: none;
   }
</style>

<!-- ✅ Correct JS path -->
<script src="../Scripts/pages/login.js"></script>
