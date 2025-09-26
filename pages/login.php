<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<style>


/* Fullscreen dark overlay */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.65);
  z-index: 2000;

  display: flex;
  align-items: center;   /* vertical center */
  justify-content: center; /* horizontal center */
}

#box-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%); /* ✅ perfect centering */
  
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  max-width: 420px;
  width: 90%;
  box-shadow: 0 8px 30px rgba(0,0,0,0.3);
}
/* Close button */
#close-btn {
  position: absolute;
  top: 10px;
  right: 12px;
  background: transparent;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
}

.error { color: red; margin: 5px 0; }
.success { color: green; margin: 5px 0; }

.spinner {
  display: none;
  margin: 10px auto;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 25px;
  height: 25px;
  animation: spin 1s linear infinite;
}
@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
body.modal-open {
  overflow: hidden; /* disables scrolling */
}

#login-error-container:empty { display: none; }
</style>
</head>
<body>
   <!-- Remove <center>, not needed -->
   <div id="login-overlay" class="overlay" >
      <div id="box-container">
         <!-- Close button -->
         <button id="close-btn">&times;</button>

         <div id="box-content">
            <img src="/Leilife/public/assests/Mask group.png" alt="Logo">
            <h1>Welcome back!</h1>

            <!-- Local Login Form -->
            <form action="/Leilife/backend/login.php" method="POST" id="login-form">
               <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

               <label for="email">Email <span style="color: red;">*</span></label>
               <input type="email" id="email" name="email" placeholder="Enter your email" required>

               <label for="password">Password <span style="color: red;">*</span></label>
               <input type="password" id="password" name="password" placeholder="Enter your password" required>

               <button type="submit" class="login-btn">Login</button>
            </form>

            <!-- Spinner -->
            <div id="spinner" class="spinner"></div>

            <!-- Error container -->
            <div id="login-error-container" class="error-messages"></div>

            <!-- Continue with Google -->
            <button type="button" class="google-btn" onclick="window.location.href='/Leilife/backend/google_login.php'">
               <img id="google-logo" src="/Leilife/public/assests/google.logo.webp" alt="Google Logo"
                    style="width: 25px; height:25px; max-width:30px; max-height:30px;">
               Continue with Google
            </button>

            <button id="forgot-pass">Forgot your password?</button>

            <div class="terms">
               <p>By continuing, you agree to our updated Terms & Conditions and Privacy Policy.</p>
            </div>

            <div class="signup">
               <p>Don't have an account? <a href="/Leilife/pages/signup.php">Sign up</a></p>
            </div>
         </div>
      </div>
   </div>
<!-- ✅ Absolute path to ensure JS loads -->
<script src="/Leilife/Scripts/pages/login.js" defer>
   
</script>
</body>
