<?php
include "../components/buttonTemplate.php";
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$token = $_GET['token'] ?? '';
?>
<div class="reset-page">
  <div class="reset-card">
    <!-- Request Reset Form (no token) -->
    <?php if (!$token): ?>
      <form id="request-form" method="POST">
        <h1>Forgot your password?</h1>
        <p class="subtitle">Enter your Gmail to receive a reset link.</p>

        <label for="reset-email">Email</label>
        <input type="email" id="reset-email" name="email" placeholder="Enter your Gmail" required>

        <div class="button-wrapper">
          <?php echo createButton(45, 360, "Send Verification", "reset-btn", 16, "submit"); ?>
        </div>
      </form>
    <?php endif; ?>

    <!-- Reset Password Form (with token) -->
    <?php if ($token): ?>
      <form id="reset-form" method="POST">
        <h1>Create a new password</h1>
        <p class="subtitle">Must be at least 8 characters.</p>

        <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">

        <label for="new_password">New password</label>
        <div class="password-wrapper">
          <input type="password" id="new_password" name="new_password" placeholder="New password" required>
          <span class="toggle-eye" onclick="togglePassword('new_password', this)">
            <!-- Closed eye icon (default, since input is password) -->
            <svg xmlns="http://www.w3.org/2000/svg" id="closed-eye" width="20" height="20" viewBox="0 0 24 24" fill="black">
              <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12c-2.761 
      0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z" />
              <circle cx="12" cy="12" r="2.5" />
            </svg>
          </span>
        </div>

        <div id="strength-message"></div>
        <div id="strength-bar"><span></span></div>

        <label for="confirm_password">Confirm password</label>
        <div class="password-wrapper">
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
          <span class="toggle-eye" onclick="togglePassword('confirm_password', this)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="black">
              <path d="M12 5c-7.633 0-11 7-11 7s3.367 7 11 7 11-7 11-7-3.367-7-11-7zm0 12c-2.761 
      0-5-2.239-5-5s2.239-5 5-5 5 2.239 5 5-2.239 5-5 5z" />
              <circle cx="12" cy="12" r="2.5" />
            </svg>
          </span>
        </div>


        <div class="button-wrapper">
          <?php echo createButton(45, 360, "Change Password", "reset-btn", 16, "submit"); ?>
        </div>
      </form>
    <?php endif; ?>

    <!-- Messages -->
    <div id="message-container"></div>
  </div>
</div>

<script src="/Leilife/Scripts/pages/forgot-password.js"></script>