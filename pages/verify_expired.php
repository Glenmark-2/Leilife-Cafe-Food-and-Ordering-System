<?php
// public/pages/verify_expired.php

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : null;
?>

<div style="text-align:center; margin-top:50px;">
    <h2>Verification Link Expired</h2>
    <p>Your verification link has expired. Don’t worry, you can request a new one.</p>

    <?php if ($email): ?>
        <form action="/Leilife/public/index.php?page=resend_verification" method="POST">
            <input type="hidden" name="email" value="<?php echo $email; ?>">
            <button type="submit" style="padding:10px 20px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                Resend Verification Email
            </button>
        </form>
    <?php else: ?>
        <p>Please enter your email to resend a new link:</p>
        <form action="/Leilife/public/index.php?page=resend_verification" method="POST">
            <input type="email" name="email" required placeholder="Enter your email" style="padding:8px;width:250px;">
            <button type="submit" style="padding:10px 20px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;">
                Resend Verification Email
            </button>
        </form>
    <?php endif; ?>
</div>
