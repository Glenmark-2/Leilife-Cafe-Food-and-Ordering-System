
<div class="page">
  <h1>Create a new password</h1>
  <p class="subtitle">Must be at least 8 characters.</p>

  <div class="card">
    <form method="POST">
      <label for="new_password">Enter new password</label>
      <input type="password" id="new_password" name="new_password" placeholder="New password" required>

      <label for="confirm_password">Confirm new password</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>

      <div class="button-wrapper">
        <?php 
          include "../components/buttonTemplate.php";
          echo createButton(45, 360, "Change Password","create-btn"); 
        ?>
      </div>
    </form>
  </div>
</div>

