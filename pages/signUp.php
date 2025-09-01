<style>
#body-container {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  padding: 20px;
}

#title {
  text-align: center;
}

#title h1 {
  font-size: 28px; /* desktop heading */
  margin-bottom: 10px;
}

#subtitle {
  font-size: 16px;
  margin: 0 auto 20px auto;
  max-width: 480px;
  text-align: center;
}

#box {
  background-color: white;
  padding: 40px;
  border-radius: 25px;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  max-width: 800px;
  width: 100%;
  box-sizing: border-box; /* add this */
}

/* Mobile adjustments */
@media (max-width: 768px) {
  #box {
    padding: 20px;       /* internal spacing */
    border-radius: 0;    /* remove rounded corners */
    width: 100vw;        /* full viewport width */
    max-width: 100vw;    /* ensure it doesn't exceed screen */
    box-sizing: border-box; /* include padding inside width */
    margin: 0;           /* remove any default margins */
  }
}



.input-box {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
  align-items: flex-start;
}

#box input:not([type="checkbox"]) {
  flex: 1 1 calc(50% - 20px);
  min-width: 300px;
  width: 100%;
  height: 52px;
  background-color: #f4f4f4;
  border: none;
  border-radius: 5px;
  padding: 0 15px;
  font-size: 16px;
  box-sizing: border-box;
}

.label-input {
  font-weight: bold;
  margin: 20px 0 10px 0;
}

.checkbox-container {
  display: flex;
  align-items: flex-start;
  margin: 20px 0;
  font-size: 14px;
  line-height: 1.5;
}

.checkbox-container input[type="checkbox"] {
  margin-right: 10px;
  margin-top: 3px;
  height: 18px;
  width: 18px;
}

.checkbox-container a {
  color: #243238;
  text-decoration: none;
  font-weight: bold;
}

.checkbox-container a:hover {
  text-decoration: underline;
}
</style>

<div id="body-container">
    <div id="title">
        <h1>Ready to sign up to Leilife?</h1>
        <p id="subtitle">Tell us more about you so we can give you a better delivery experience.</p>
    </div>

    <div id="box">
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
            <input type="checkbox" id="terms">
            <label for="terms">
                By registering your details, you agree with our
                <a href="#">Terms & Conditions</a>.
            </label>
        </div>

        <center>
        <?php
        include "../components/buttonTemplate.php";
        echo createButton(45, 360, "Create your Account","create-btn");
        ?>
        </center>
    </div>
</div>

<script>
  // Apply inline styles to inputs on mobile
  if (window.innerWidth <= 768) {
    const inputs = document.querySelectorAll('#box input:not([type="checkbox"])');
    inputs.forEach(input => {
      // input.style.flex = 'none';
      // input.style.width = '80%';
      input.style.height = '52px'; // mobile-friendly height
      input.style.boxSizing = 'border-box';
      input.style.padding = '0';
      input.style.fontSize = '16px';
    });
  }
</script>
