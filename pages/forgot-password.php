<div class="page">
  <h1>Create a new password</h1>
  <p class="subtitle">Must be at-least 8 characters.</p>

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

<style>
  body {
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    background-color: #ede9e4; /* beige background */
  }

  .page {
    text-align: center;
    padding-top: 50px;
  }

  h1 {
    font-size: 40px;
    font-weight: 800;
    margin-bottom: 5px;
  }

  .subtitle {
    font-size: 20px;
    color: #333;
    margin-bottom: 25px;
  }

  .card {
    background: white;
    padding: 40px;
    border-radius: 25px;
    width: 420px;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }

  label {
    font-weight: bold;
    display: block;
    margin: 20px 0 8px 0;
    text-align: center;
    font-size: 24px;
  }

  input[type="password"] {
    width: 100%;
    padding: 14px;
    border: none;
    background: #f4f4f4;
    border-radius: 6px;
    font-size: 15px;
    text-align: left;
  }

  /* 🔹 Only handles spacing between input & button */
  .button-wrapper {
    margin-top: 25px;
  }
</style>
