<div id="body-container">
    <div id="title">
        <h1>Ready to sign up to Leilife?</h1>
        <p id="subtitle">Tell us more about you so we can give you a better delivery experience.</p>
    </div>

    <div id="box">
        <!-- ✅ Wrap inputs inside a form -->
        <form action="../backend/signUp.php" method="POST" style="width: 100%;">
            <p class="label-input">User Details</p>
            <div class="sign-up-form">
                <input type="text" name="fname" required placeholder="First name">
                <input type="text" name="lname" required placeholder="Last name">
            </div>

            <p class="label-input">Login & Contact Details</p>
            <div class="sign-up-form">
                <input type="email" name="email" required placeholder="Email address">
                <input type="tel" name="phone_number" required placeholder="Phone number">
                <input type="password" name="password" required placeholder="Password">
                <input type="password" name="confirm_password" required placeholder="Confirm password">
            </div>

            <!-- Checkbox with modal trigger -->
            <div class="checkbox-container">
                <input type="checkbox" name="terms" id="terms" required>
                <label for="terms">
                    By registering your details, you agree with our
                    <span id="termsLink" style="color:#243238; cursor:pointer; font-weight:bold;">Terms & Conditions</span>.
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

<!-- Terms & Conditions Modal -->
<div id="termsModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Terms and Conditions</h2>
        <div class="modal-body">
            <p id="terms-text">Welcome! By signing up, you agree to the following terms:</p>
            <ol>
                <li><strong>Acceptance of Terms:</strong> You agree to abide by these Terms and Conditions when using our services.</li>
                <li><strong>Eligibility:</strong> You must be at least 18 years old to create an account.</li>
                <li><strong>Account Responsibilities:</strong> Keep your login credentials secure and provide accurate information.</li>
                <li><strong>Privacy:</strong> Your data will be collected and used according to our Privacy Policy.</li>
                <li><strong>Acceptable Use:</strong> You may not use the service for illegal activities or to harm others.</li>
                <li><strong>Intellectual Property:</strong> All content belongs to us and cannot be copied without permission.</li>
                <li><strong>Termination:</strong> Accounts violating these terms may be suspended or terminated.</li>
                <li><strong>Disclaimer:</strong> We are not liable for any damages or loss caused by using the service.</li>
                <li><strong>Changes to Terms:</strong> We may update these terms anytime; continued use constitutes acceptance.</li>
                <li><strong>Governing Law:</strong> These terms are governed by the laws of [Your Country].</li>
            </ol>
        </div>
        <?php 
        echo createButton(40, 150, "I Accept", "acceptBtn", 16);
        ?>
    </div>
</div>


<script>
// Modal elements
const modal = document.getElementById("termsModal");
const termsLink = document.getElementById("termsLink");
const closeBtn = document.querySelector(".modal-content .close");
const acceptBtn = document.getElementById("acceptBtn");
const checkbox = document.getElementById("terms");

// Show modal
termsLink.onclick = () => modal.style.display = "flex";

// Close modal
closeBtn.onclick = () => modal.style.display = "none";

// Accept button: check checkbox + close modal
acceptBtn.onclick = () => {
    checkbox.checked = true;
    modal.style.display = "none";
};

// Click outside modal to close
window.onclick = e => {
    if (e.target == modal) modal.style.display = "none";
};

</script>

<script src="../Scripts/pages/sign-up.js"></script>