<?php
include "../components/buttonTemplate.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get token from query string (if present)
$token = $_GET['token'] ?? '';
?>
<div class="page">
  <div class="card">
    
    <!-- Request Reset Form (shown if no token) -->
    <?php if (!$token): ?>
      <form id="request-form" method="POST">
        <h1>Forgot your password?</h1>
        <p class="subtitle">Enter your Gmail to receive a reset link.</p>
        
        <label for="reset-email">Email</label>
        <input type="email" id="reset-email" name="email" placeholder="Enter your Gmail" required>
      
        <div class="button-wrapper">
          <?php 
            echo createButton(45, 360, "Send Verification", "reset-btn", 16, "submit");
          ?>
        </div>
      </form>
    <?php endif; ?>

    <!-- Reset Password Form (shown if token exists) -->
    <?php if ($token): ?>
      <form id="reset-form" method="POST">
        <h1>Create a new password</h1>
        <p class="subtitle">Must be at least 8 characters.</p>

        <input type="hidden" id="token" name="token" 
               value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">

        <label for="new_password">Enter new password</label>
        <input type="password" id="new_password" name="new_password" placeholder="New password" required>

        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>

        <div class="button-wrapper">
          <?php 
            echo createButton(45, 360, "Change Password", "reset-btn", 16, "submit");
          ?>
        </div>
      </form>
    <?php endif; ?>

    <!-- Shared message container -->
    <div id="message-container"></div>
  </div>
</div>

<script src="/Leilife/Scripts/pages/forgot-password.js"></script>
