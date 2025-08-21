<style>
    #body-container {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        
    }

    #title {
        text-align: center;
        font-size: 20px;
    }

    #subtitle {
        margin-top: -20px;
        font-size: 15px;
    }

    #box {

        background-color: white;
        padding: 40px;
        border-radius: 25px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);

    }

    .input-box {
        display: flex;
        flex-wrap: wrap;
        gap: 30px;
    }

#box input:not([type="checkbox"]) {
    width: 15vw;
    height: 6vh;
    background-color: #f4f4f4;
    border: none;
    border-radius: 5px;
    padding-left: 10px;
}

/* Mobile view (example: screens below 768px) */
@media (max-width: 768px) {
    .input-box {
        flex-direction: column;
        gap: 20px; /* smaller gap for mobile */
    }

    .input-box input {
        width: 100%;
        height: 55px; /* increase input height */
        font-size: 16px;
        padding: 12px 15px;
        box-sizing: border-box;
    }

    #title{
        font-size: 12px;
    }

    #subtitle{
        font-size: 15px;
        padding-left: 30px;
        padding-right: 30px;
        margin-top: 15px;
    }
}


    .input-box input {
        flex: 1 1 calc(50% - 30px);
        box-sizing: border-box;
    }

    

    .label-input {
        font-weight: bolder;
    }

.checkbox-container {
  display: flex;
  margin: 20px 0;
  font-size: 14px;
  line-height: 1.5;
  

}

.checkbox-container input[type="checkbox"] {
background-color: red;
  margin-top: 4px;
  height: .5cm;
  width: fit-content;
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

