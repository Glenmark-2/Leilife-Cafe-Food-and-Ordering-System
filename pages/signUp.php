<!-- pages/signUp.php (or wherever you render the page) -->
<div id="body-container">
  <div id="title">
    <h1>Ready to sign up to Leilife?</h1>
    <p id="subtitle">Tell us more about you so we can give you a better delivery experience.</p>
  </div>

  <div id="box">
    <?php
      if (session_status() === PHP_SESSION_NONE) session_start();
      if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
    ?>

        <!-- Error container -->
        <div id="error-container" class="error-messages" style="display: none;">
            <?php
            if (!empty($_SESSION['signup_errors'])) {
                foreach ($_SESSION['signup_errors'] as $error) {
                    echo '<p class="error">' . htmlspecialchars($error) . '</p>';
                }
                // force display if PHP errors exist
                echo "<script>document.getElementById('error-container').style.display = 'block';</script>";
                unset($_SESSION['signup_errors']);
            }
            ?>
        </div>


    <form id="signup-form"
          action="/Leilife/backend/signup.php"
          method="POST"
          novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

      <legend>User Details</legend>
      <div class="sign-up-form">
        <label for="fname">First Name <span class="required">*</span></label>
        <input type="text" id="fname" name="fname" maxlength="100" required placeholder="First name" autocomplete="given-name">
            
        <label for="lname">Last Name <span class="required">*</span></label>
        <input type="text" id="lname" name="lname" maxlength="100" required placeholder="Last name" autocomplete="family-name">
      </div>

      <legend>Login & Contact Details</legend>
      <div class="sign-up-form">
        <label for="email">Email <span class="required">*</span></label>
        <input type="email" id="email" name="email" required placeholder="Email address" autocomplete="email">

        <label for="phone_number">Phone Number <span class="required">*</span></label>
        <input type="tel" id="phone_number" name="phone_number" required placeholder="+1234567890" pattern="^\+?\d{7,15}$" autocomplete="tel">

        <label for="password">Password <span class="required">*</span></label>
        <input type="password" id="password" name="password" required placeholder="Password" minlength="8" autocomplete="new-password">

        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm password" minlength="8" autocomplete="new-password">
      </div>

      <div class="checkbox-container">
        <input type="checkbox" name="terms" id="terms" required>
        <label for="terms">
          By registering your details, you agree with our
          <a href="/Leilife/public/index.php?page=terms" target="_blank" rel="noopener noreferrer">Terms & Conditions</a>.
        </label>
      </div>

      <center>
        <?php
          include "../components/buttonTemplate.php";
          echo createButton(45, 360, "Create your Account", "create-btn", 16, "submit");
        ?>
      </center>
    </form>
  </div>
</div>

<style>
  /* Hide the error box if empty */
  #error-container:empty { display: none; }
</style>

<!-- Watchdog: set a flag; the JS must flip it to "ready" -->
<script>
  window.__signupJSReady = false;
</script>

<!-- Use an ABSOLUTE path + cache-buster -->
<script src="/Leilife/Scripts/pages/sign-up.js?v=4" defer></script>

<!-- If the JS never flips the flag, surface a clear console message -->
<script>
  window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
      if (!window.__signupJSReady) {
        console.error('[signup] sign-up.js did not initialize. Check script path, 404s, or console errors.');
      }
    }, 0);
  });
</script>
