<div id="body-container">
    <div id="title">
        <h1>Ready to sign up to Leilife?</h1>
        <p id="subtitle">Tell us more about you so we can give you a better delivery experience.</p>
    </div>

    <div id="box">
        <!-- ✅ Wrap inputs inside a form -->
        <form action="../backend/signUp.php" method="POST">
            <p class="label-input">User Details</p>
            <div class="input-box">
                <input type="text" name="fname" required placeholder="First name">
                <input type="text" name="lname" required placeholder="Last name">
            </div>

            <p class="label-input">Login & Contact Details</p>
            <div class="input-box">
                <input type="email" name="email" required placeholder="Email address">
                <input type="tel" name="phone_number" required placeholder="Phone number">
                <input type="password" name="password" required placeholder="Password">
                <input type="password" name="confirm_password" required placeholder="Confirm password">
            </div>

            <div class="checkbox-container">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">
                    By registering your details, you agree with our
                    <a href="#">Terms & Conditions</a>.
                </label>
            </div>

            <center>
            <?php
            include "../components/buttonTemplate.php";
            // ✅ type="submit" to ensure form submits
            echo createButton(45, 360, "Create your Account", "create-btn", 16, "submit");
            ?>
            </center>
        </form>
    </div>
</div>

<script src="../Scripts/pages/sign-up.js"></script>
