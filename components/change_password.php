<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

include "buttonTemplate.php";
?>

<div class="modal" id="password-modal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          <?php echo $hasPassword ? "Change Password" : "Set Password"; ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <?php if ($hasPassword): ?>
          <!-- 🔑 Change Password Form -->
          <form id="change-password-form" method="POST" action="backend/change_password.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="mb-3">
              <label for="change-current-password" class="form-label">Current Password</label>
              <input type="password" class="form-control" id="change-current-password" name="current_password" required>
            </div>

            <div class="mb-3">
              <label for="change-new-password" class="form-label">New Password</label>
              <input type="password" class="form-control" id="change-new-password" name="new_password" required minlength="8">
            </div>

            <div class="mb-3">
              <label for="change-confirm-password" class="form-label">Confirm New Password</label>
              <input type="password" class="form-control" id="change-confirm-password" name="confirm_password" required>
            </div>

            <div id="change-password-error" class="text-danger small mb-2"></div>
            <div id="change-password-success" class="text-success small mb-2"></div>

            <!-- <button type="submit" class="btn btn-primary w-100">Update Password</button> -->
            <?php
            echo createButton(
              45,               
              400,              
              "Update Password",  
              "btn btn-primary w-100",   
              15,              
              "submit",        
              ["class" => "btn btn-primary w-100", "name" => "btn btn-primary w-100"] 
            );
            ?>
          </form>
        <?php else: ?>

          <!-- 🆕 Set Password Form -->
          <form id="set-password-form" method="POST" action="backend/set_password.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="mb-3">
              <label for="set-new-password" class="form-label">Password</label>
              <input type="password" class="form-control" id="set-new-password" name="new_password" required minlength="8">
            </div>

            <div class="mb-3">
              <label for="set-confirm-password" class="form-label">Confirm Password</label>
              <input type="password" class="form-control" id="set-confirm-password" name="confirm_password" required>
            </div>

            <div id="set-password-error" class="text-danger small mb-2"></div>
            <div id="set-password-success" class="text-success small mb-2"></div>

            <!-- <button type="submit" class="btn btn-submit">Set Password</button> -->
            <?php
            echo createButton(
              45,
              400,
              "Set Password",
              "set-password",
              15,
              "submit",
              ["class" => "btn btn-submit", "name" => "set_password"]
            );
            ?>
          </form>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<script src="/Leilife/Scripts/components/change_password.js"></script>